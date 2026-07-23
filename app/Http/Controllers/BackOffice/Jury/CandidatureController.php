<?php

namespace App\Http\Controllers\BackOffice\Jury;

use App\Enums\CandidatureStatut;
use App\Http\Controllers\Controller;
use App\Mail\DecisionCandidatureMail;
use App\Mail\DemandeComplementCandidatureMail;
use App\Mail\ReorientationCandidatureMail;
use App\Models\Candidature;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

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
            'typesDocuments' => $candidature->programmeNiveau?->typesDocuments()
                ->where('types_documents.actif', true)
                ->get()
                ?? collect(),
            'niveauxReorientation' => ProgrammeNiveau::query()
                ->where('actif', true)
                ->where('programme_id', '!=', $candidature->programme_id)
                ->whereHas('programme', fn (Builder $query) => $query->where('actif', true))
                ->with(['programme', 'niveau'])
                ->orderBy('programme_id')
                ->orderBy('ordre')
                ->get(),
        ]);
    }

    public function demanderComplement(Request $request, Candidature $candidature): RedirectResponse
    {
        Gate::authorize('demanderComplementJury', $candidature);

        $donnees = $request->validate([
            'type_document_ids' => ['required', 'array', 'min:1'],
            'type_document_ids.*' => ['integer', 'distinct', Rule::exists('types_documents', 'id')],
            'motif_complement' => ['required', 'string', 'max:2000'],
        ], [
            'type_document_ids.required' => 'Sélectionnez au moins un document à demander.',
            'motif_complement.required' => 'Précisez les éléments attendus par le jury.',
        ]);

        $candidature = DB::transaction(function () use ($request, $candidature, $donnees): Candidature {
            $candidature = Candidature::query()
                ->with('programmeNiveau')
                ->lockForUpdate()
                ->findOrFail($candidature->id);

            Gate::forUser($request->user())
                ->authorize('demanderComplementJury', $candidature);

            $typesAutorises = $candidature->programmeNiveau->typesDocuments()
                ->where('types_documents.actif', true)
                ->whereIn('types_documents.id', $donnees['type_document_ids'])
                ->pluck('types_documents.id');

            if ($typesAutorises->count() !== count($donnees['type_document_ids'])) {
                throw ValidationException::withMessages([
                    'type_document_ids' => 'Un document sélectionné ne correspond pas au programme actuel.',
                ]);
            }

            $ancienStatut = $candidature->statut;
            $candidature->update([
                'statut' => CandidatureStatut::COMPLEMENT_JURY,
            ]);
            $candidature->historiques()->create([
                'ancien_statut' => $ancienStatut->value,
                'nouveau_statut' => CandidatureStatut::COMPLEMENT_JURY->value,
                'acteur_type' => 'jury',
                'acteur_id' => $request->user()->id,
                'commentaire' => $donnees['motif_complement'],
                'metadata' => [
                    'type_document_ids' => $typesAutorises->values()->all(),
                ],
            ]);

            return $candidature->load(['candidat', 'programme']);
        });

        try {
            Mail::to($candidature->candidat->email)
                ->send(new DemandeComplementCandidatureMail(
                    $candidature,
                    $donnees['motif_complement'],
                    'jury',
                ));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'warning',
                'La demande est enregistrée, mais l’e-mail n’a pas pu être envoyé au candidat.',
            );
        }

        return back()->with('status', 'La demande de complément du jury a été envoyée.');
    }

    public function decider(Request $request, Candidature $candidature): RedirectResponse
    {
        Gate::authorize('deciderJury', $candidature);

        $donnees = $request->validate([
            'decision' => [
                'required',
                Rule::in([
                    CandidatureStatut::ADMISE->value,
                    CandidatureStatut::REFUSEE->value,
                ]),
            ],
            'motif_decision' => [
                Rule::requiredIf($request->input('decision') === CandidatureStatut::REFUSEE->value),
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'motif_decision.required' => 'Un motif est obligatoire pour refuser une candidature.',
        ]);
        $decision = CandidatureStatut::from($donnees['decision']);

        $candidature = DB::transaction(function () use (
            $request,
            $candidature,
            $donnees,
            $decision,
        ): Candidature {
            $candidature = Candidature::query()
                ->lockForUpdate()
                ->findOrFail($candidature->id);

            Gate::forUser($request->user())->authorize('deciderJury', $candidature);

            $ancienStatut = $candidature->statut;
            $commentaire = $decision === CandidatureStatut::ADMISE
                ? ($donnees['motif_decision'] ?? 'Candidature admise par le jury.')
                : $donnees['motif_decision'];

            $candidature->update(['statut' => $decision]);
            $candidature->historiques()->create([
                'ancien_statut' => $ancienStatut->value,
                'nouveau_statut' => $decision->value,
                'acteur_type' => 'jury',
                'acteur_id' => $request->user()->id,
                'commentaire' => $commentaire,
            ]);

            return $candidature->load(['candidat', 'programme']);
        });

        try {
            Mail::to($candidature->candidat->email)
                ->send(new DecisionCandidatureMail(
                    $candidature,
                    $donnees['motif_decision'] ?? null,
                ));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'warning',
                'La décision est enregistrée, mais l’e-mail n’a pas pu être envoyé au candidat.',
            );
        }

        return back()->with('status', 'La décision du jury a été enregistrée et envoyée.');
    }

    public function reorienter(Request $request, Candidature $candidature): RedirectResponse
    {
        Gate::authorize('reorienterJury', $candidature);

        $donnees = $request->validate([
            'programme_niveau_id' => [
                'required',
                'integer',
                Rule::exists('programme_niveaux', 'id'),
            ],
            'motif_reorientation' => ['required', 'string', 'max:2000'],
        ], [
            'motif_reorientation.required' => 'Expliquez la réorientation proposée par le jury.',
        ]);

        [$candidature, $ancienProgramme] = DB::transaction(function () use (
            $request,
            $candidature,
            $donnees,
        ): array {
            $candidature = Candidature::query()
                ->with('programme')
                ->lockForUpdate()
                ->findOrFail($candidature->id);

            Gate::forUser($request->user())->authorize('reorienterJury', $candidature);

            $nouveauNiveau = ProgrammeNiveau::query()
                ->whereKey($donnees['programme_niveau_id'])
                ->where('actif', true)
                ->whereHas('programme', fn (Builder $query) => $query->where('actif', true))
                ->with(['programme', 'niveau'])
                ->first();

            if (! $nouveauNiveau || $nouveauNiveau->programme_id === $candidature->programme_id) {
                throw ValidationException::withMessages([
                    'programme_niveau_id' => 'Choisissez un niveau actif appartenant à un autre programme.',
                ]);
            }

            $candidatureExistante = Candidature::query()
                ->where('candidat_id', $candidature->candidat_id)
                ->where('programme_id', $nouveauNiveau->programme_id)
                ->where('id', '!=', $candidature->id)
                ->exists();

            if ($candidatureExistante) {
                throw ValidationException::withMessages([
                    'programme_niveau_id' => 'Ce candidat possède déjà une candidature pour le programme choisi.',
                ]);
            }

            $ancienProgramme = $candidature->programme;
            $ancienProgrammeNiveauId = $candidature->programme_niveau_id;
            $ancienStatut = $candidature->statut;

            $candidature->update([
                'programme_origine_id' => $candidature->programme_origine_id
                    ?? $candidature->programme_id,
                'programme_id' => $nouveauNiveau->programme_id,
                'programme_niveau_id' => $nouveauNiveau->id,
            ]);
            $candidature->historiques()->create([
                'ancien_statut' => $ancienStatut->value,
                'nouveau_statut' => $ancienStatut->value,
                'acteur_type' => 'jury',
                'acteur_id' => $request->user()->id,
                'commentaire' => $donnees['motif_reorientation'],
                'metadata' => [
                    'action' => 'reorientation',
                    'ancien_programme_id' => $ancienProgramme->id,
                    'ancien_programme_niveau_id' => $ancienProgrammeNiveauId,
                    'nouveau_programme_id' => $nouveauNiveau->programme_id,
                    'nouveau_programme_niveau_id' => $nouveauNiveau->id,
                ],
            ]);

            return [
                $candidature->load(['candidat', 'programme', 'programmeNiveau.niveau']),
                $ancienProgramme,
            ];
        });

        try {
            Mail::to($candidature->candidat->email)
                ->send(new ReorientationCandidatureMail(
                    $candidature,
                    $ancienProgramme,
                    $donnees['motif_reorientation'],
                ));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'warning',
                'La réorientation est enregistrée, mais l’e-mail n’a pas pu être envoyé au candidat.',
            );
        }

        return back()->with('status', 'La candidature a été réorientée et le candidat a été informé.');
    }
}
