<?php

namespace App\Http\Controllers\BackOffice\Admission;

use App\Enums\CandidatureStatut;
use App\Http\Controllers\Controller;
use App\Models\Candidature;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('viewAny', Candidature::class);

        $candidaturesRecues = Candidature::query()
            ->where('statut', '!=', CandidatureStatut::BROUILLON)
            ->count();

        return view('back-office.admission.dashboard', [
            'candidaturesRecues' => $candidaturesRecues,
            'nouvellesCandidatures' => Candidature::query()
                ->where('statut', CandidatureStatut::SOUMISE)
                ->count(),
            'dossiersEnTraitement' => Candidature::query()
                ->where('statut', CandidatureStatut::EN_TRAITEMENT_ADMISSION)
                ->count(),
            'complementsEnAttente' => Candidature::query()
                ->where('statut', CandidatureStatut::COMPLEMENT_ADMISSION)
                ->count(),
            'mesDossiers' => Candidature::query()
                ->where('agent_admission_id', auth()->id())
                ->count(),
            'candidaturesRecentes' => Candidature::query()
                ->where('statut', '!=', CandidatureStatut::BROUILLON)
                ->with(['candidat', 'programme', 'programmeNiveau.niveau'])
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
