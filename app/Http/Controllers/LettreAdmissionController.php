<?php

namespace App\Http\Controllers;

use App\Enums\CandidatureStatut;
use App\Models\Candidature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class LettreAdmissionController extends Controller
{
    public function __invoke(
        Candidature $candidature,
        string $token,
    ): Response {
        abort_unless(
            hash_equals((string) $candidature->edit_token, $token),
            404,
        );
        abort_unless(
            $candidature->statut === CandidatureStatut::ADMISE,
            403,
        );

        $candidature->load([
            'candidat',
            'programme',
            'programmeNiveau.niveau',
        ]);
        $decision = $candidature->historiques()
            ->where('nouveau_statut', CandidatureStatut::ADMISE->value)
            ->latest()
            ->first();

        $pdf = Pdf::loadView('pdf.lettre-admission', [
            'candidature' => $candidature,
            'dateDecision' => $decision?->created_at ?? $candidature->updated_at,
            'reference' => sprintf(
                'EPF-%s-%06d',
                ($decision?->created_at ?? $candidature->updated_at)->format('Y'),
                $candidature->id,
            ),
            'logoDataUri' => $this->logoDataUri(),
        ])->setPaper('a4');

        return $pdf->download(
            'lettre-admission-'.$candidature->id.'.pdf',
        );
    }

    private function logoDataUri(): string
    {
        $logo = file_get_contents(public_path('images/logo-epf-africa.jpg'));

        return 'data:image/jpeg;base64,'.base64_encode($logo);
    }
}
