<?php

namespace App\Http\Controllers;

use App\Enums\CandidatureStatut;
use App\Enums\DocumentStatutValidation;
use App\Mail\ComplementCandidatureRecuMail;
use App\Models\Candidature;
use App\Models\CandidatureDocument;
use App\Models\TypeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Illuminate\View\View;
use Throwable;

class CandidatureComplementController extends Controller
{
    public function edit(Candidature $candidature, string $token): View
    {
        $this->autoriser($candidature, $token);

        $candidature->load([
            'programme',
            'programmeNiveau.niveau',
            'programmeNiveau.typesDocuments',
            'documents.typeDocument',
        ]);

        return view('candidatures.complements', [
            'candidature' => $candidature,
            'documentsRequis' => $this->documentsDemandes($candidature),
            'documentsActuels' => $candidature->documents->keyBy('type_document_id'),
            'demandeComplement' => $candidature->historiques()
                ->where('nouveau_statut', $candidature->statut->value)
                ->latest()
                ->first(),
            'token' => $token,
        ]);
    }

    public function update(
        Request $request,
        Candidature $candidature,
        string $token,
    ): RedirectResponse {
        $this->autoriser($candidature, $token);

        $documentsRequis = $this->documentsDemandes($candidature)
            ->keyBy('code');
        abort_if($documentsRequis->isEmpty(), 422);
        $validator = $this->validator($request, $documentsRequis);
        $validator->validate();

        $ancienStatut = $candidature->statut;
        $nouveauStatut = match ($ancienStatut) {
            CandidatureStatut::COMPLEMENT_ADMISSION => CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            CandidatureStatut::COMPLEMENT_JURY => CandidatureStatut::TRANSMISE_AU_JURY,
            default => abort(403),
        };
        $nouveauxChemins = [];
        $anciensChemins = [];
        $codesDeposes = [];

        try {
            DB::transaction(function () use (
                $request,
                $documentsRequis,
                $candidature,
                $ancienStatut,
                $nouveauStatut,
                &$nouveauxChemins,
                &$anciensChemins,
                &$codesDeposes,
            ): void {
                foreach ($documentsRequis as $code => $typeDocument) {
                    $fichier = $request->file('documents.'.$code);

                    if (! $fichier) {
                        continue;
                    }

                    $chemin = $fichier->store('candidature_documents', 'local');
                    $nouveauxChemins[] = $chemin;
                    $documentExistant = CandidatureDocument::query()
                        ->where('candidature_id', $candidature->id)
                        ->where('type_document_id', $typeDocument->id)
                        ->first();

                    if ($documentExistant && $documentExistant->path !== $chemin) {
                        $anciensChemins[] = $documentExistant->path;
                    }

                    CandidatureDocument::query()->updateOrCreate(
                        [
                            'candidature_id' => $candidature->id,
                            'type_document_id' => $typeDocument->id,
                        ],
                        [
                            'original_name' => $fichier->getClientOriginalName(),
                            'stored_name' => basename($chemin),
                            'path' => $chemin,
                            'mime_type' => $fichier->getMimeType(),
                            'size' => $fichier->getSize(),
                            'statut_validation' => DocumentStatutValidation::EN_ATTENTE,
                            'commentaire_validation' => null,
                        ],
                    );
                    $codesDeposes[] = $code;
                }

                $candidature->update(['statut' => $nouveauStatut]);
                $candidature->historiques()->create([
                    'ancien_statut' => $ancienStatut->value,
                    'nouveau_statut' => $nouveauStatut->value,
                    'acteur_type' => 'candidat',
                    'commentaire' => 'Documents complémentaires transmis par le candidat.',
                    'metadata' => ['documents' => $codesDeposes],
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($nouveauxChemins);

            throw $exception;
        }

        Storage::disk('local')->delete($anciensChemins);

        if ($ancienStatut === CandidatureStatut::COMPLEMENT_ADMISSION
            && $candidature->agent_admission_id) {
            try {
                $candidature->load([
                    'candidat',
                    'programme',
                    'agentAdmission',
                ]);
                Mail::to($candidature->agentAdmission->email)
                    ->send(new ComplementCandidatureRecuMail($candidature));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('candidatures.complements.confirmation', [$candidature, $token])
            ->with('success', 'Vos documents complémentaires ont bien été transmis.');
    }

    public function confirmation(Candidature $candidature, string $token): View
    {
        abort_unless(hash_equals((string) $candidature->edit_token, $token), 404);

        return view('candidatures.complements-confirmation', compact('candidature'));
    }

    /**
     * @param  Collection<string, TypeDocument>  $documentsRequis
     */
    private function validator(Request $request, Collection $documentsRequis): ValidationValidator
    {
        $rules = [
            'documents' => ['required', 'array'],
        ];

        foreach ($documentsRequis as $code => $document) {
            $extensions = implode(',', $document->extensions_autorisees ?? []);
            $rules['documents.'.$code] = [
                'required',
                'file',
                'max:'.($document->taille_max_mb * 1024),
                'mimes:'.$extensions,
            ];
        }

        $validator = Validator::make($request->all(), $rules, [
            'documents.required' => 'Sélectionnez au moins un document.',
            'documents.*.required' => 'Chaque document demandé doit être transmis.',
            'file' => 'Le document transmis n’est pas un fichier valide.',
            'max' => 'Le document dépasse la taille maximale autorisée.',
            'mimes' => 'Le format du document n’est pas autorisé.',
        ]);

        $validator->after(function (ValidationValidator $validator) use ($request, $documentsRequis): void {
            $contientUnFichier = $documentsRequis
                ->keys()
                ->contains(fn (string $code): bool => $request->hasFile('documents.'.$code));

            if (! $contientUnFichier) {
                $validator->errors()->add('documents', 'Sélectionnez au moins un document.');
            }
        });

        return $validator;
    }

    /**
     * @return Collection<int, TypeDocument>
     */
    private function documentsDemandes(Candidature $candidature): Collection
    {
        $historique = $candidature->historiques()
            ->where('nouveau_statut', $candidature->statut->value)
            ->latest()
            ->first();
        $typeDocumentIds = collect($historique?->metadata['type_document_ids'] ?? []);
        $documents = $candidature->programmeNiveau->typesDocuments();

        if ($typeDocumentIds->isNotEmpty()) {
            $documents->whereIn('types_documents.id', $typeDocumentIds);
        } else {
            $documents->where('types_documents.actif', true);
        }

        return $documents->get();
    }

    private function autoriser(Candidature $candidature, string $token): void
    {
        abort_unless(hash_equals((string) $candidature->edit_token, $token), 404);
        abort_unless(in_array($candidature->statut, [
            CandidatureStatut::COMPLEMENT_ADMISSION,
            CandidatureStatut::COMPLEMENT_JURY,
        ], true), 403);
        abort_unless($candidature->programme_niveau_id, 422);
    }
}
