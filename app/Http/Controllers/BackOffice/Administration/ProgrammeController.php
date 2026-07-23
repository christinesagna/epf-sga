<?php

namespace App\Http\Controllers\BackOffice\Administration;

use App\Http\Controllers\Controller;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const CYCLES = [
        'classe_preparatoire' => 'Classe préparatoire',
        'licence' => 'Licence',
        'cycle_ingenieur' => 'Cycle ingénieur',
        'master' => 'Master',
    ];

    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'recherche' => ['nullable', 'string', 'max:255'],
            'cycle' => ['nullable', Rule::in(array_keys(self::CYCLES))],
            'etat' => ['nullable', Rule::in(['actif', 'inactif'])],
        ]);

        $programmes = Programme::query()
            ->withCount([
                'niveaux',
                'niveaux as niveaux_actifs_count' => fn (Builder $query) => $query->where('actif', true),
            ])
            ->when($filtres['recherche'] ?? null, function (Builder $query, string $recherche): void {
                $query->where('nom', 'like', "%{$recherche}%");
            })
            ->when($filtres['cycle'] ?? null, fn (Builder $query, string $cycle) => $query->where('niveau', $cycle))
            ->when($filtres['etat'] ?? null, fn (Builder $query, string $etat) => $query->where('actif', $etat === 'actif'))
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('back-office.administration.programmes.index', [
            'programmes' => $programmes,
            'cycles' => self::CYCLES,
            'filtres' => $filtres,
        ]);
    }

    public function create(): View
    {
        return view('back-office.administration.programmes.create', [
            'programme' => new Programme,
            'cycles' => self::CYCLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $this->validerProgramme($request);
        $donnees['slug'] = $this->genererSlugUnique($donnees['nom']);
        $donnees['actif'] = false;

        $programme = DB::transaction(function () use ($request, $donnees): Programme {
            $programme = Programme::query()->create($donnees);

            $this->historiser(
                $request->user(),
                'programme_cree',
                $programme,
                null,
                $this->valeursHistorisees($programme),
            );

            return $programme;
        });

        return redirect()
            ->route('administration.programmes.edit', $programme)
            ->with('status', 'Le programme a été créé inactif. Ajoutez au moins un niveau actif avant de l’activer.');
    }

    public function edit(Programme $programme): View
    {
        $programme->load('niveaux.niveau');

        $niveauIdsAssocies = $programme->niveaux->pluck('niveau_id');

        return view('back-office.administration.programmes.edit', [
            'programme' => $programme,
            'cycles' => self::CYCLES,
            'niveauxDisponibles' => Niveau::query()
                ->whereNotIn('id', $niveauIdsAssocies)
                ->orderBy('libelle')
                ->get(),
        ]);
    }

    public function update(Request $request, Programme $programme): RedirectResponse
    {
        $donnees = $this->validerProgramme($request, $programme);
        $anciennesValeurs = $this->valeursHistorisees($programme);

        DB::transaction(function () use ($request, $programme, $donnees, $anciennesValeurs): void {
            $programme->update($donnees);

            $this->historiser(
                $request->user(),
                'programme_modifie',
                $programme,
                $anciennesValeurs,
                $this->valeursHistorisees($programme),
            );
        });

        return back()->with('status', 'Le programme a été modifié. Son adresse publique reste inchangée.');
    }

    public function modifierEtat(Request $request, Programme $programme): RedirectResponse
    {
        $donnees = $request->validate([
            'actif' => ['required', 'boolean'],
        ]);

        $nouvelEtat = (bool) $donnees['actif'];

        if ($programme->actif === $nouvelEtat) {
            return back()->with('status', 'L’état du programme est déjà à jour.');
        }

        if ($nouvelEtat && ! $programme->niveaux()->where('actif', true)->exists()) {
            throw ValidationException::withMessages([
                'actif' => 'Un programme doit posséder au moins un niveau actif avant son activation.',
            ]);
        }

        DB::transaction(function () use ($request, $programme, $nouvelEtat): void {
            $ancienEtat = $programme->actif;
            $programme->actif = $nouvelEtat;
            $programme->save();

            $this->historiser(
                $request->user(),
                $nouvelEtat ? 'programme_active' : 'programme_desactive',
                $programme,
                ['actif' => $ancienEtat],
                ['actif' => $nouvelEtat],
            );
        });

        return back()->with('status', $nouvelEtat
            ? 'Le programme est maintenant visible dans le parcours candidat.'
            : 'Le programme a été désactivé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validerProgramme(Request $request, ?Programme $programme = null): array
    {
        return $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('programmes', 'nom')->ignore($programme),
            ],
            'niveau' => ['required', Rule::in(array_keys(self::CYCLES))],
            'capacite_accueil' => ['required', 'integer', 'min:0'],
            'date_ouverture' => ['nullable', 'date'],
            'date_fermeture' => ['nullable', 'date', 'after_or_equal:date_ouverture'],
            'frais_scolarite' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'echeancier_paiement' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function genererSlugUnique(string $nom): string
    {
        $base = Str::slug($nom) ?: 'programme';
        $slug = $base;
        $suffixe = 2;

        while (Programme::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffixe}";
            $suffixe++;
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function valeursHistorisees(Programme $programme): array
    {
        return [
            'nom' => $programme->nom,
            'slug' => $programme->slug,
            'niveau' => $programme->niveau,
            'capacite_accueil' => $programme->capacite_accueil,
            'date_ouverture' => $programme->date_ouverture?->toDateString(),
            'date_fermeture' => $programme->date_fermeture?->toDateString(),
            'frais_scolarite' => $programme->frais_scolarite,
            'echeancier_paiement' => $programme->echeancier_paiement,
            'description' => $programme->description,
            'actif' => $programme->actif,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $anciennesValeurs
     * @param  array<string, mixed>|null  $nouvellesValeurs
     */
    private function historiser(
        User $auteur,
        string $action,
        Programme $programme,
        ?array $anciennesValeurs,
        ?array $nouvellesValeurs,
    ): void {
        DB::table('actions_administratives')->insert([
            'auteur_id' => $auteur->id,
            'utilisateur_cible_id' => null,
            'cible_type' => 'programme',
            'cible_id' => $programme->id,
            'action' => $action,
            'anciennes_valeurs' => $anciennesValeurs === null
                ? null
                : json_encode($anciennesValeurs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'nouvelles_valeurs' => $nouvellesValeurs === null
                ? null
                : json_encode($nouvellesValeurs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }
}
