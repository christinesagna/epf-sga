<?php

namespace App\Http\Controllers\BackOffice\Admission;

use App\Enums\CandidatureStatut;
use App\Enums\DocumentStatutValidation;
use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                fn (Builder $query, int|string $programmeId) => $query->where('programme_id', $programmeId),
            )
            ->when(
                $filtres['programme_niveau_id'] ?? null,
                fn (Builder $query, int|string $programmeNiveauId) => $query
                    ->where('programme_niveau_id', $programmeNiveauId),
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
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('back-office.admission.candidatures.index', [
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
            'statuts' => collect(CandidatureStatut::cases())
                ->reject(fn (CandidatureStatut $statut) => $statut === CandidatureStatut::BROUILLON),
        ]);
    }

    public function show(Candidature $candidature): View
    {
        Gate::authorize('view', $candidature);

        $candidature->load([
            'candidat',
            'programme',
            'programmeNiveau.niveau',
            'agentAdmission',
            'documents.typeDocument',
            'historiques' => fn ($query) => $query->latest(),
            'programmeNiveau.typesDocuments',
        ]);

        $documentsObligatoires = $candidature->programmeNiveau?->typesDocuments
            ->filter(fn ($typeDocument): bool => $typeDocument->actif
                && (bool) $typeDocument->pivot->obligatoire)
            ?? collect();
        $documentsParType = $candidature->documents->keyBy('type_document_id');
        $documentsObligatoiresValides = $documentsObligatoires->every(
            fn ($typeDocument): bool => $documentsParType
                ->get($typeDocument->id)
                ?->statut_validation === DocumentStatutValidation::VALIDE,
        );

        return view('back-office.admission.candidatures.show', [
            'candidature' => $candidature,
            'documentsObligatoires' => $documentsObligatoires,
            'documentsObligatoiresValides' => $documentsObligatoiresValides,
        ]);
    }

    public function prendreEnCharge(Request $request, Candidature $candidature): RedirectResponse
    {
        DB::transaction(function () use ($request, $candidature): void {
            $candidature = Candidature::query()
                ->lockForUpdate()
                ->findOrFail($candidature->id);

            Gate::forUser($request->user())->authorize('prendreEnCharge', $candidature);

            $ancienStatut = $candidature->statut;
            $candidature->update([
                'agent_admission_id' => $request->user()->id,
                'pris_en_charge_at' => now(),
                'statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            ]);
            $candidature->historiques()->create([
                'ancien_statut' => $ancienStatut->value,
                'nouveau_statut' => CandidatureStatut::EN_TRAITEMENT_ADMISSION->value,
                'acteur_type' => 'admission',
                'acteur_id' => $request->user()->id,
                'commentaire' => 'Dossier pris en charge par le service d’admission.',
            ]);
        });

        return back()->with('status', 'La candidature vous a été attribuée.');
    }

    public function transmettreAuJury(Request $request, Candidature $candidature): RedirectResponse
    {
        DB::transaction(function () use ($request, $candidature): void {
            $candidature = Candidature::query()
                ->with('programmeNiveau')
                ->lockForUpdate()
                ->findOrFail($candidature->id);

            Gate::forUser($request->user())->authorize('transmettreAuJury', $candidature);

            $typesObligatoires = $candidature->programmeNiveau?->typesDocuments()
                ->where('types_documents.actif', true)
                ->wherePivot('obligatoire', true)
                ->pluck('types_documents.id')
                ?? collect();
            $typesValides = $candidature->documents()
                ->whereIn('type_document_id', $typesObligatoires)
                ->where('statut_validation', DocumentStatutValidation::VALIDE)
                ->pluck('type_document_id');

            if ($typesObligatoires->diff($typesValides)->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'transmission' => 'Tous les documents obligatoires doivent être présents et validés avant la transmission au jury.',
                ]);
            }

            $ancienStatut = $candidature->statut;
            $candidature->update([
                'statut' => CandidatureStatut::TRANSMISE_AU_JURY,
            ]);
            $candidature->historiques()->create([
                'ancien_statut' => $ancienStatut->value,
                'nouveau_statut' => CandidatureStatut::TRANSMISE_AU_JURY->value,
                'acteur_type' => 'admission',
                'acteur_id' => $request->user()->id,
                'commentaire' => 'Dossier complet transmis au jury.',
            ]);
        });

        return back()->with('status', 'La candidature a été transmise au jury.');
    }
}
