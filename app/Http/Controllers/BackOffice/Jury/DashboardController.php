<?php

namespace App\Http\Controllers\BackOffice\Jury;

use App\Enums\CandidatureStatut;
use App\Http\Controllers\Controller;
use App\Models\Candidature;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('viewAnyJury', Candidature::class);

        $dossiersJury = Candidature::query()
            ->whereIn('statut', CandidatureStatut::valeursVisiblesParJury());

        return view('back-office.jury.dashboard', [
            'totalDossiers' => (clone $dossiersJury)->count(),
            'aEtudier' => (clone $dossiersJury)
                ->where('statut', CandidatureStatut::TRANSMISE_AU_JURY)
                ->count(),
            'complementsEnAttente' => (clone $dossiersJury)
                ->where('statut', CandidatureStatut::COMPLEMENT_JURY)
                ->count(),
            'decisionsRendues' => (clone $dossiersJury)
                ->whereIn('statut', [
                    CandidatureStatut::ADMISE,
                    CandidatureStatut::REFUSEE,
                ])
                ->count(),
            'candidaturesRecentes' => (clone $dossiersJury)
                ->with(['candidat', 'programme', 'programmeNiveau.niveau'])
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
