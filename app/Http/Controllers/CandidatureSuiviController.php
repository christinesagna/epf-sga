<?php

namespace App\Http\Controllers;

use App\Enums\CandidatureStatut;
use App\Models\Candidature;
use Illuminate\View\View;

class CandidatureSuiviController extends Controller
{
    public function __invoke(Candidature $candidature, string $token): View
    {
        abort_unless(
            hash_equals((string) $candidature->edit_token, $token),
            404,
        );

        $candidature->load([
            'candidat',
            'programme',
            'programmeNiveau.niveau',
            'historiques' => fn ($query) => $query->latest(),
        ]);

        return view('candidatures.suivi', [
            'candidature' => $candidature,
            'token' => $token,
            'complementAttendu' => in_array($candidature->statut, [
                CandidatureStatut::COMPLEMENT_ADMISSION,
                CandidatureStatut::COMPLEMENT_JURY,
            ], true),
        ]);
    }
}
