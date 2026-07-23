<?php

namespace App\Http\Controllers\BackOffice\Jury;

use App\Enums\CandidatureStatut;
use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidatureController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAnyJury', Candidature::class);

        $filtres = $request->validate([
            'recherche' => ['nullable', 'string', 'max:255'],
            'programme_id' => ['nullable', 'integer', Rule::exists('programmes', 'id')],
            'programme_niveau_id' => ['nullable', 'integer', Rule::exists('programme_niveaux', 'id')],
            'statut' => ['nullable', Rule::in(CandidatureStatut::valeursVisiblesParJury())],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $statutsJury = CandidatureStatut::valeursVisiblesParJury();

        $candidatures = Candidature::query()
            ->whereIn('statut', $statutsJury)
            ->with(['candidat', 'programme', 'programmeNiveau.niveau'])
            ->when($filtres['recherche'] ?? null, function (Builder $query, string $recherche): void {
                $query->where(function (Builder $query) use ($recherche): void {
                    $query
                        ->where('code_suivi', 'like', "%{$recherche}%")
                        ->orWhereHas('candidat', function (Builder $query) use ($recherche): void {
                            $query
                                ->where('nom', 'like', "%{$recherche}%")
                                ->orWhere('prenom', 'like', "%{$recherche}%")
                                ->orWhere('email', 'like', "%{$recherche}%");
                        });
                });
            })
            ->when(
                $filtres['programme_id'] ?? null,
                fn (Builder $query, int|string $programmeId) => $query->where('programme_id', $programmeId),
            )
            ->when(
                $filtres['programme_niveau_id'] ?? null,
                fn (Builder $query, int|string $niveauId) => $query->where('programme_niveau_id', $niveauId),
            )
            ->when(
                $filtres['statut'] ?? null,
                fn (Builder $query, string $statut) => $query->where('statut', $statut),
            )
            ->when(
                $filtres['date_debut'] ?? null,
                fn (Builder $query, string $date) => $query->whereDate('submitted_at', '>=', $date),
            )
            ->when(
                $filtres['date_fin'] ?? null,
                fn (Builder $query, string $date) => $query->whereDate('submitted_at', '<=', $date),
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('back-office.jury.candidatures.index', [
            'candidatures' => $candidatures,
            'filtres' => $filtres,
            'programmes' => Programme::query()
                ->whereHas(
                    'candidatures',
                    fn (Builder $query) => $query->whereIn('statut', $statutsJury),
                )
                ->orderBy('nom')
                ->get(),
            'niveauxProgrammes' => ProgrammeNiveau::query()
                ->whereHas(
                    'candidatures',
                    fn (Builder $query) => $query->whereIn('statut', $statutsJury),
                )
                ->with(['programme', 'niveau'])
                ->when(
                    $filtres['programme_id'] ?? null,
                    fn (Builder $query, int|string $programmeId) => $query->where('programme_id', $programmeId),
                )
                ->orderBy('programme_id')
                ->orderBy('ordre')
                ->get(),
            'statuts' => CandidatureStatut::visiblesParJury(),
        ]);
    }

    public function show(Candidature $candidature): View
    {
        Gate::authorize('viewJury', $candidature);

        $candidature->load([
            'candidat',
            'programme',
            'programmeNiveau.niveau',
            'agentAdmission',
            'documents.typeDocument',
            'historiques' => fn ($query) => $query->latest(),
        ]);

        return view('back-office.jury.candidatures.show', [
            'candidature' => $candidature,
        ]);
    }
}
