<?php

namespace Tests\Feature\Administration;

use App\Models\Niveau;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use App\Models\User;
use Database\Seeders\NiveauxSeeder;
use Database\Seeders\ProgrammesSeeder;
use Database\Seeders\TypesDocumentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestionProgrammesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_gestion_des_programmes_est_reservee_aux_super_administrateurs(): void
    {
        $jury = User::factory()->jury()->create();
        $administrateur = User::factory()->superAdmin()->create();

        $this->get('/back-office/administration/programmes')
            ->assertRedirect(route('login'));

        $this->actingAs($jury)
            ->get('/back-office/administration/programmes')
            ->assertForbidden();

        $this->actingAs($administrateur)
            ->get('/back-office/administration/programmes')
            ->assertOk()
            ->assertSee('Gestion des programmes')
            ->assertSee('Créer un programme');
    }

    public function test_un_programme_est_cree_inactif_avec_un_slug_unique_et_une_trace(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        Programme::query()->create([
            ...$this->donneesProgramme(),
            'nom' => 'Data & IA',
            'slug' => 'data-ia',
            'actif' => true,
        ]);

        $this->actingAs($administrateur)
            ->post(route('administration.programmes.store'), [
                ...$this->donneesProgramme(),
                'nom' => 'Data IA',
            ])
            ->assertRedirect();

        $programme = Programme::query()->where('nom', 'Data IA')->firstOrFail();

        $this->assertFalse($programme->actif);
        $this->assertSame('data-ia-2', $programme->slug);
        $this->assertDatabaseHas('actions_administratives', [
            'auteur_id' => $administrateur->id,
            'action' => 'programme_cree',
            'cible_type' => 'programme',
            'cible_id' => $programme->id,
            'utilisateur_cible_id' => null,
        ]);
    }

    public function test_les_donnees_du_programme_sont_validees_et_le_slug_reste_stable(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $programme = $this->creerProgramme();
        $slugInitial = $programme->slug;

        $this->actingAs($administrateur)
            ->from(route('administration.programmes.edit', $programme))
            ->put(route('administration.programmes.update', $programme), [
                ...$this->donneesProgramme(),
                'nom' => 'Programme renommé',
                'date_ouverture' => '2026-10-01',
                'date_fermeture' => '2026-09-01',
            ])
            ->assertSessionHasErrors('date_fermeture');

        $this->actingAs($administrateur)
            ->put(route('administration.programmes.update', $programme), [
                ...$this->donneesProgramme(),
                'nom' => 'Programme renommé',
            ])
            ->assertSessionHasNoErrors();

        $programme->refresh();

        $this->assertSame('Programme renommé', $programme->nom);
        $this->assertSame($slugInitial, $programme->slug);
        $this->assertDatabaseHas('actions_administratives', [
            'action' => 'programme_modifie',
            'cible_type' => 'programme',
            'cible_id' => $programme->id,
        ]);
    }

    public function test_un_programme_ne_peut_etre_active_que_s_il_possede_un_niveau_actif(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $programme = $this->creerProgramme();

        $this->actingAs($administrateur)
            ->from(route('administration.programmes.index'))
            ->patch(route('administration.programmes.etat', $programme), ['actif' => true])
            ->assertSessionHasErrors('actif');

        $niveau = Niveau::query()->create([
            'code' => 'licence_1',
            'libelle' => 'Licence 1',
        ]);
        $programme->niveaux()->create([
            'niveau_id' => $niveau->id,
            'ordre' => 1,
            'actif' => true,
        ]);

        $this->actingAs($administrateur)
            ->patch(route('administration.programmes.etat', $programme), ['actif' => true])
            ->assertSessionHasNoErrors();

        $this->assertTrue($programme->fresh()->actif);
        $this->assertDatabaseHas('actions_administratives', [
            'action' => 'programme_active',
            'cible_id' => $programme->id,
        ]);
    }

    public function test_un_niveau_existant_ou_nouveau_peut_etre_associe_et_ordonne(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $programme = $this->creerProgramme();
        $niveau = Niveau::query()->create([
            'code' => 'master_1',
            'libelle' => 'Master 1',
        ]);

        $this->actingAs($administrateur)
            ->post(route('administration.programmes.niveaux.store', $programme), [
                'niveau_id' => $niveau->id,
                'ordre' => 2,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($administrateur)
            ->post(route('administration.programmes.niveaux.nouveau', $programme), [
                'libelle' => 'Année préparatoire spéciale',
                'ordre' => 1,
            ])
            ->assertSessionHasNoErrors();

        $niveauCree = Niveau::query()->where('libelle', 'Année préparatoire spéciale')->firstOrFail();
        $association = ProgrammeNiveau::query()
            ->where('programme_id', $programme->id)
            ->where('niveau_id', $niveau->id)
            ->firstOrFail();

        $this->assertSame('annee_preparatoire_speciale', $niveauCree->code);
        $this->assertDatabaseHas('programme_niveaux', [
            'programme_id' => $programme->id,
            'niveau_id' => $niveauCree->id,
            'ordre' => 1,
            'actif' => true,
        ]);

        $this->actingAs($administrateur)
            ->patch(route('administration.programme-niveaux.update', $association), [
                'ordre' => 3,
                'actif' => false,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('programme_niveaux', [
            'id' => $association->id,
            'ordre' => 3,
            'actif' => false,
        ]);
        $this->assertDatabaseHas('actions_administratives', [
            'action' => 'niveau_programme_modifie',
            'cible_type' => 'programme_niveau',
            'cible_id' => $association->id,
        ]);
    }

    public function test_le_dernier_niveau_actif_d_un_programme_actif_ne_peut_pas_etre_desactive(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $programme = $this->creerProgramme(['actif' => true]);
        $niveau = Niveau::query()->create([
            'code' => 'licence_1',
            'libelle' => 'Licence 1',
        ]);
        $association = $programme->niveaux()->create([
            'niveau_id' => $niveau->id,
            'ordre' => 1,
            'actif' => true,
        ]);

        $this->actingAs($administrateur)
            ->from(route('administration.programmes.edit', $programme))
            ->patch(route('administration.programme-niveaux.update', $association), [
                'ordre' => 1,
                'actif' => false,
            ])
            ->assertSessionHasErrors('actif');

        $this->assertTrue($association->fresh()->actif);
    }

    public function test_les_seeders_n_ecrasent_pas_les_modifications_administratives(): void
    {
        $this->seed(TypesDocumentsSeeder::class);
        $this->seed(NiveauxSeeder::class);
        $this->seed(ProgrammesSeeder::class);

        $programme = Programme::query()
            ->where('nom', 'Master Informatique')
            ->firstOrFail();
        $programme->update([
            'capacite_accueil' => 12,
            'description' => 'Description modifiée dans le back-office.',
            'actif' => false,
        ]);

        $association = $programme->niveaux()->firstOrFail();
        $association->update([
            'ordre' => 9,
            'actif' => false,
        ]);

        $niveau = $association->niveau;
        $niveau->update(['libelle' => 'Libellé administré']);

        $this->seed(NiveauxSeeder::class);
        $this->seed(ProgrammesSeeder::class);

        $programme->refresh();
        $association->refresh();

        $this->assertSame(12, $programme->capacite_accueil);
        $this->assertSame('Description modifiée dans le back-office.', $programme->description);
        $this->assertFalse($programme->actif);
        $this->assertSame(9, $association->ordre);
        $this->assertFalse($association->actif);
        $this->assertSame('Libellé administré', $niveau->fresh()->libelle);
        $this->assertSame(7, Programme::query()->count());
    }

    /**
     * @param  array<string, mixed>  $attributs
     */
    private function creerProgramme(array $attributs = []): Programme
    {
        return Programme::query()->create([
            ...$this->donneesProgramme(),
            'slug' => 'programme-test',
            'actif' => false,
            ...$attributs,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function donneesProgramme(): array
    {
        return [
            'nom' => 'Programme test',
            'niveau' => 'master',
            'capacite_accueil' => 50,
            'date_ouverture' => '2026-01-01',
            'date_fermeture' => '2026-10-31',
            'frais_scolarite' => 2500000,
            'echeancier_paiement' => 'Deux versements.',
            'description' => 'Description du programme.',
        ];
    }
}
