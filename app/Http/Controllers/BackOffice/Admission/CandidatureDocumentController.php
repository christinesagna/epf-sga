<?php

namespace App\Http\Controllers\BackOffice\Admission;

use App\Enums\DocumentStatutValidation;
use App\Http\Controllers\Controller;
use App\Models\CandidatureDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidatureDocumentController extends Controller
{
    public function show(CandidatureDocument $document): StreamedResponse
    {
        $document->loadMissing('candidature');

        Gate::authorize('view', $document->candidature);

        $stockage = Storage::disk('local');

        abort_unless($stockage->exists($document->path), 404);

        $mimeType = $stockage->mimeType($document->path)
            ?: $document->mime_type
            ?: 'application/octet-stream';

        return $stockage->response(
            $document->path,
            $document->original_name,
            ['Content-Type' => $mimeType],
            'inline',
        );
    }

    public function update(Request $request, CandidatureDocument $document): RedirectResponse
    {
        $document->loadMissing('candidature');
        Gate::authorize('controlerDocuments', $document->candidature);

        $donnees = $request->validate([
            'statut_validation' => [
                'required',
                Rule::in([
                    DocumentStatutValidation::VALIDE->value,
                    DocumentStatutValidation::REJETE->value,
                ]),
            ],
            'commentaire_validation' => [
                Rule::requiredIf(
                    $request->input('statut_validation') === DocumentStatutValidation::REJETE->value,
                ),
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'commentaire_validation.required' => 'Un motif est obligatoire pour rejeter un document.',
        ]);

        DB::transaction(function () use ($request, $document, $donnees): void {
            $document = CandidatureDocument::query()
                ->with('candidature')
                ->lockForUpdate()
                ->findOrFail($document->id);

            Gate::forUser($request->user())
                ->authorize('controlerDocuments', $document->candidature);

            $ancienStatutDocument = $document->statut_validation;
            $document->update([
                'statut_validation' => $donnees['statut_validation'],
                'commentaire_validation' => $donnees['commentaire_validation'] ?? null,
            ]);
            $document->candidature->historiques()->create([
                'ancien_statut' => $document->candidature->statut->value,
                'nouveau_statut' => $document->candidature->statut->value,
                'acteur_type' => 'admission',
                'acteur_id' => $request->user()->id,
                'commentaire' => $donnees['statut_validation'] === DocumentStatutValidation::VALIDE->value
                    ? 'Document validé par le service d’admission.'
                    : 'Document rejeté par le service d’admission.',
                'metadata' => [
                    'document_id' => $document->id,
                    'type_document_id' => $document->type_document_id,
                    'ancien_statut_document' => $ancienStatutDocument->value,
                    'nouveau_statut_document' => $donnees['statut_validation'],
                ],
            ]);
        });

        return back()->with('status', 'Le contrôle du document a été enregistré.');
    }
}
