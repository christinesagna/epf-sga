<?php

namespace App\Http\Controllers\BackOffice\Administration;

use App\Enums\CandidatureStatut;
use App\Enums\RoleUtilisateur;
use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidatureController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Candidature::class);

        $filtres = $request->validate([
            'recherche' => ['nullable', 'string', 'max:255'],
            'programme_id' => ['nullable', 'integer', Rule::exists('programmes', 'id')],
            'programme_niveau_id' => ['nullable', 'integer', Rule::exists('programme_niveaux', 'id')],
            'agent_admission_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'statut' => ['nullable', Rule::enum(CandidatureStatut::class)],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $candidatures = Candidature::query()
            ->where('statut', '!=', CandidatureStatut::BROUILLON)
            ->with([
                'candidat',
                'programme',
                'programmeNiveau.niveau',
                'agentAdmission',
            ])
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
                fn (Builder $query, int|string $programmeId) => $query
                    ->where('programme_id', $programmeId),
            )
            ->when(
                $filtres['programme_niveau_id'] ?? null,
                fn (Builder $query, int|string $programmeNiveauId) => $query
                    ->where('programme_niveau_id', $programmeNiveauId),
            )
            ->when(
                $filtres['agent_admission_id'] ?? null,
                fn (Builder $query, int|string $agentId) => $query
                    ->where('agent_admission_id', $agentId),
            )
            ->when(
                $filtres['statut'] ?? null,
                fn (Builder $query, string $statut) => $query->where('statut', $statut),
            )
            ->when(
                $filtres['date_debut'] ?? null,
                fn (Builder $query, string $date) => $query
                    ->whereDate('submitted_at', '>=', $date),
            )
            ->when(
                $filtres['date_fin'] ?? null,
                fn (Builder $query, string $date) => $query
                    ->whereDate('submitted_at', '<=', $date),
            )
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('back-office.administration.candidatures.index', [
            'candidatures' => $candidatures,
            'filtres' => $filtres,
            'programmes' => Programme::query()
                ->whereHas('candidatures', fn (Builder $query) => $query
                    ->where('statut', '!=', CandidatureStatut::BROUILLON))
                ->orderBy('nom')
                ->get(),
            'niveauxProgrammes' => ProgrammeNiveau::query()
                ->whereHas('candidatures', fn (Builder $query) => $query
                    ->where('statut', '!=', CandidatureStatut::BROUILLON))
                ->with(['programme', 'niveau'])
                ->when(
                    $filtres['programme_id'] ?? null,
                    fn (Builder $query, int|string $programmeId) => $query
                        ->where('programme_id', $programmeId),
                )
                ->orderBy('programme_id')
                ->orderBy('ordre')
                ->get(),
            'agentsAdmission' => User::query()
                ->where('role', RoleUtilisateur::ADMISSION)
                ->orderBy('name')
                ->get(),
            'statuts' => collect(CandidatureStatut::cases())
                ->reject(fn (CandidatureStatut $statut) => $statut === CandidatureStatut::BROUILLON),
        ]);
    }

    public function show(Candidature $candidature): View
    {
        Gate::authorize('view', $candidature);

        abort_if($candidature->statut === CandidatureStatut::BROUILLON, 404);

        $candidature->load([
            'candidat',
            'programme',
            'programmeNiveau.niveau',
            'agentAdmission',
            'documents.typeDocument',
            'historiques' => fn ($query) => $query->latest(),
        ]);
        $programmeIdsHistorique = $candidature->historiques
            ->filter(fn ($historique): bool => ($historique->metadata['action'] ?? null) === 'reorientation')
            ->flatMap(fn ($historique): array => [
                $historique->metadata['ancien_programme_id'] ?? null,
                $historique->metadata['nouveau_programme_id'] ?? null,
            ])
            ->filter()
            ->unique();
        $acteurIds = $candidature->historiques
            ->pluck('acteur_id')
            ->filter()
            ->unique();

        return view('back-office.administration.candidatures.show', [
            'candidature' => $candidature,
            'programmesHistorique' => Programme::query()
                ->whereKey($programmeIdsHistorique)
                ->pluck('nom', 'id'),
            'acteursHistorique' => User::query()
                ->whereKey($acteurIds)
                ->pluck('name', 'id'),
        ]);
    }
}
