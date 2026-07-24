<?php

namespace App\Http\Controllers\BackOffice\Administration;

use App\Enums\CandidatureStatut;
use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $candidaturesParStatut = Candidature::query()
            ->selectRaw('statut, COUNT(*) as total')
            ->where('statut', '!=', CandidatureStatut::BROUILLON)
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $nombre = fn (CandidatureStatut $statut): int => (int) $candidaturesParStatut
            ->get($statut->value, 0);
        $complementsAdmission = $nombre(CandidatureStatut::COMPLEMENT_ADMISSION);
        $complementsJury = $nombre(CandidatureStatut::COMPLEMENT_JURY);
        $admises = $nombre(CandidatureStatut::ADMISE);
        $refusees = $nombre(CandidatureStatut::REFUSEE);

        return view('back-office.administration.dashboard', [
            'indicateursCandidatures' => [
                'total' => $candidaturesParStatut->sum(),
                'nouvelles' => $nombre(CandidatureStatut::SOUMISE),
                'admission' => $nombre(CandidatureStatut::EN_TRAITEMENT_ADMISSION),
                'complements' => $complementsAdmission + $complementsJury,
                'complements_admission' => $complementsAdmission,
                'complements_jury' => $complementsJury,
                'jury' => $nombre(CandidatureStatut::TRANSMISE_AU_JURY),
                'decisions' => $admises + $refusees,
                'admises' => $admises,
                'refusees' => $refusees,
            ],
            'utilisateursInternes' => User::query()->whereNotNull('role')->count(),
            'utilisateursActifs' => User::query()->whereNotNull('role')->where('actif', true)->count(),
            'invitationsEnAttente' => User::query()
                ->whereNotNull('role')
                ->whereNotNull('invitation_sent_at')
                ->whereNull('email_verified_at')
                ->count(),
            'programmesActifs' => Programme::query()->where('actif', true)->count(),
            'niveauxConfigures' => Niveau::query()->count(),
            'typesDocumentsActifs' => TypeDocument::query()->where('actif', true)->count(),
        ]);
    }
}
