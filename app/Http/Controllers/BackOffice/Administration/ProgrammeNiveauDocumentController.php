<?php

namespace App\Http\Controllers\BackOffice\Administration;

use App\Http\Controllers\Controller;
use App\Models\ProgrammeNiveau;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProgrammeNiveauDocumentController extends Controller
{
    public function edit(ProgrammeNiveau $programmeNiveau): View
    {
        $programmeNiveau->load(['programme', 'niveau', 'typesDocuments']);
        $associations = $programmeNiveau->typesDocuments->keyBy('id');

        return view('back-office.administration.programme-niveaux.documents.edit', [
            'programmeNiveau' => $programmeNiveau,
            'documentsActifs' => TypeDocument::query()
                ->where('actif', true)
                ->orderBy('libelle')
                ->get(),
            'documentsInactifsAssocies' => $programmeNiveau->typesDocuments
                ->where('actif', false)
                ->values(),
            'associations' => $associations,
        ]);
    }

    public function update(Request $request, ProgrammeNiveau $programmeNiveau): RedirectResponse
    {
        $donnees = $request->validate([
            'documents' => ['nullable', 'array'],
            'documents.*.selectionne' => ['required', 'boolean'],
            'documents.*.obligatoire' => ['required', 'boolean'],
            'documents.*.ordre' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $documents = collect($donnees['documents'] ?? []);
        $idsActifs = TypeDocument::query()
            ->where('actif', true)
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id);
        $idsInconnus = $documents->keys()
            ->map(fn (int|string $id): string => (string) $id)
            ->diff($idsActifs);

        if ($idsInconnus->isNotEmpty()) {
            throw ValidationException::withMessages([
                'documents' => 'La sélection contient un type de document inactif ou introuvable.',
            ]);
        }

        $anciennesValeurs = [
            'documents' => $this->associationsHistorisees($programmeNiveau),
        ];

        DB::transaction(function () use (
            $request,
            $programmeNiveau,
            $documents,
            $idsActifs,
            $anciennesValeurs,
        ): void {
            $documentsSelectionnes = $documents
                ->filter(fn (array $document): bool => (bool) $document['selectionne']);
            $idsSelectionnes = $documentsSelectionnes->keys()
                ->map(fn (int|string $id): int => (int) $id)
                ->values();
            $idsActifsEntiers = $idsActifs
                ->map(fn (string $id): int => (int) $id)
                ->values();

            $associationsActives = DB::table('programme_niveau_type_document')
                ->where('programme_niveau_id', $programmeNiveau->id)
                ->whereIn('type_document_id', $idsActifsEntiers)
                ->pluck('type_document_id')
                ->map(fn (int $id): int => $id);

            $suppression = DB::table('programme_niveau_type_document')
                ->where('programme_niveau_id', $programmeNiveau->id)
                ->whereIn('type_document_id', $idsActifsEntiers);

            if ($idsSelectionnes->isEmpty()) {
                $suppression->delete();
            } else {
                $suppression
                    ->whereNotIn('type_document_id', $idsSelectionnes)
                    ->delete();
            }

            foreach ($documentsSelectionnes as $typeDocumentId => $document) {
                $valeursPivot = [
                    'obligatoire' => (bool) $document['obligatoire'],
                    'ordre' => (int) $document['ordre'],
                    'updated_at' => now(),
                ];

                if ($associationsActives->contains((int) $typeDocumentId)) {
                    DB::table('programme_niveau_type_document')
                        ->where('programme_niveau_id', $programmeNiveau->id)
                        ->where('type_document_id', (int) $typeDocumentId)
                        ->update($valeursPivot);
                } else {
                    DB::table('programme_niveau_type_document')->insert([
                        'programme_niveau_id' => $programmeNiveau->id,
                        'type_document_id' => (int) $typeDocumentId,
                        ...$valeursPivot,
                        'created_at' => now(),
                    ]);
                }
            }

            $programmeNiveau->unsetRelation('typesDocuments');

            $this->historiser(
                $request->user(),
                $programmeNiveau,
                $anciennesValeurs,
                ['documents' => $this->associationsHistorisees($programmeNiveau)],
            );
        });

        return back()->with(
            'status',
            'Les documents demandés pour ce niveau ont été enregistrés. Les associations inactives ont été conservées.',
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function associationsHistorisees(ProgrammeNiveau $programmeNiveau): array
    {
        return DB::table('programme_niveau_type_document as association')
            ->join('types_documents as document', 'document.id', '=', 'association.type_document_id')
            ->where('association.programme_niveau_id', $programmeNiveau->id)
            ->orderBy('association.ordre')
            ->orderBy('document.libelle')
            ->get([
                'document.id',
                'document.code',
                'document.actif',
                'association.obligatoire',
                'association.ordre',
            ])
            ->map(fn (object $document): array => [
                'type_document_id' => $document->id,
                'code' => $document->code,
                'actif' => (bool) $document->actif,
                'obligatoire' => (bool) $document->obligatoire,
                'ordre' => $document->ordre,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $anciennesValeurs
     * @param  array<string, mixed>  $nouvellesValeurs
     */
    private function historiser(
        User $auteur,
        ProgrammeNiveau $programmeNiveau,
        array $anciennesValeurs,
        array $nouvellesValeurs,
    ): void {
        DB::table('actions_administratives')->insert([
            'auteur_id' => $auteur->id,
            'utilisateur_cible_id' => null,
            'cible_type' => 'programme_niveau',
            'cible_id' => $programmeNiveau->id,
            'action' => 'documents_niveau_modifies',
            'anciennes_valeurs' => json_encode(
                $anciennesValeurs,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
            ),
            'nouvelles_valeurs' => json_encode(
                $nouvellesValeurs,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
            ),
            'created_at' => now(),
        ]);
    }
}
