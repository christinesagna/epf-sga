<?php

namespace App\Http\Controllers\BackOffice\Administration;

use App\Http\Controllers\Controller;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProgrammeNiveauController extends Controller
{
    public function store(Request $request, Programme $programme): RedirectResponse
    {
        $donnees = $request->validate([
            'niveau_id' => [
                'required',
                'integer',
                Rule::exists('niveaux', 'id'),
                Rule::unique('programme_niveaux', 'niveau_id')
                    ->where('programme_id', $programme->id),
            ],
            'ordre' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        DB::transaction(function () use ($request, $programme, $donnees): void {
            $programmeNiveau = $programme->niveaux()->create([
                'niveau_id' => $donnees['niveau_id'],
                'ordre' => $donnees['ordre'],
                'actif' => true,
            ]);

            $this->historiser($request->user(), 'niveau_programme_ajoute', $programmeNiveau, null, [
                'niveau_id' => $programmeNiveau->niveau_id,
                'ordre' => $programmeNiveau->ordre,
                'actif' => true,
            ]);
        });

        return back()->with('status', 'Le niveau a été associé au programme.');
    }

    public function storeNouveau(Request $request, Programme $programme): RedirectResponse
    {
        $donnees = $request->validate([
            'libelle' => ['required', 'string', 'max:255', Rule::unique('niveaux', 'libelle')],
            'ordre' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        DB::transaction(function () use ($request, $programme, $donnees): void {
            $niveau = Niveau::query()->create([
                'code' => $this->genererCodeUnique($donnees['libelle']),
                'libelle' => $donnees['libelle'],
            ]);

            $programmeNiveau = $programme->niveaux()->create([
                'niveau_id' => $niveau->id,
                'ordre' => $donnees['ordre'],
                'actif' => true,
            ]);

            $this->historiser($request->user(), 'niveau_programme_cree', $programmeNiveau, null, [
                'niveau_id' => $niveau->id,
                'code' => $niveau->code,
                'libelle' => $niveau->libelle,
                'ordre' => $programmeNiveau->ordre,
                'actif' => true,
            ]);
        });

        return back()->with('status', 'Le niveau a été créé dans le catalogue et associé au programme.');
    }

    public function update(Request $request, ProgrammeNiveau $programmeNiveau): RedirectResponse
    {
        $donnees = $request->validate([
            'ordre' => ['required', 'integer', 'min:1', 'max:999'],
            'actif' => ['required', 'boolean'],
        ]);

        $nouvelEtat = (bool) $donnees['actif'];

        if ($programmeNiveau->actif
            && ! $nouvelEtat
            && $programmeNiveau->programme->actif
            && ! $programmeNiveau->programme->niveaux()
                ->where('id', '!=', $programmeNiveau->id)
                ->where('actif', true)
                ->exists()) {
            throw ValidationException::withMessages([
                'actif' => 'Désactivez d’abord le programme ou conservez au moins un niveau actif.',
            ]);
        }

        $anciennesValeurs = [
            'ordre' => $programmeNiveau->ordre,
            'actif' => $programmeNiveau->actif,
        ];

        DB::transaction(function () use ($request, $programmeNiveau, $donnees, $nouvelEtat, $anciennesValeurs): void {
            $programmeNiveau->update([
                'ordre' => $donnees['ordre'],
                'actif' => $nouvelEtat,
            ]);

            $this->historiser(
                $request->user(),
                'niveau_programme_modifie',
                $programmeNiveau,
                $anciennesValeurs,
                [
                    'ordre' => $programmeNiveau->ordre,
                    'actif' => $programmeNiveau->actif,
                ],
            );
        });

        return back()->with('status', 'Le niveau du programme a été modifié.');
    }

    private function genererCodeUnique(string $libelle): string
    {
        $base = Str::slug($libelle, '_') ?: 'niveau';
        $code = $base;
        $suffixe = 2;

        while (Niveau::query()->where('code', $code)->exists()) {
            $code = "{$base}_{$suffixe}";
            $suffixe++;
        }

        return $code;
    }

    /**
     * @param  array<string, mixed>|null  $anciennesValeurs
     * @param  array<string, mixed>|null  $nouvellesValeurs
     */
    private function historiser(
        User $auteur,
        string $action,
        ProgrammeNiveau $programmeNiveau,
        ?array $anciennesValeurs,
        ?array $nouvellesValeurs,
    ): void {
        DB::table('actions_administratives')->insert([
            'auteur_id' => $auteur->id,
            'utilisateur_cible_id' => null,
            'cible_type' => 'programme_niveau',
            'cible_id' => $programmeNiveau->id,
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
