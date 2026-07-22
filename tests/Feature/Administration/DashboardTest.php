<?php

namespace Tests\Feature\Administration;

use App\Enums\RoleUtilisateur;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->get('/back-office/administration')
            ->assertRedirect(route('login'));
    }

    public function test_un_utilisateur_interne_non_administrateur_ne_peut_pas_acceder_a_administration(): void
    {
        foreach ([RoleUtilisateur::ADMISSION, RoleUtilisateur::JURY] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get('/back-office/administration')
                ->assertForbidden();
        }
    }

    public function test_un_super_administrateur_non_verifie_est_redirige_vers_la_verification(): void
    {
        $user = User::factory()->superAdmin()->unverified()->create();

        $this->actingAs($user)
            ->get('/back-office/administration')
            ->assertRedirect(route('verification.notice'));
    }

    public function test_un_super_administrateur_accede_aux_indicateurs_sans_consulter_les_candidatures(): void
    {
        $admin = User::factory()->superAdmin()->create();
        User::factory()->jury()->create();
        User::factory()->unverified()->create();

        $programme = Programme::query()->create([
            'nom' => 'Programme de test',
            'slug' => 'programme-de-test',
            'niveau' => 'master',
            'actif' => true,
        ]);

        $autreProgramme = Programme::query()->create([
            'nom' => 'Autre programme de test',
            'slug' => 'autre-programme-de-test',
            'niveau' => 'master',
            'actif' => true,
        ]);

        $niveau = Niveau::query()->create([
            'code' => 'master_1',
            'libelle' => 'Master 1',
        ]);

        ProgrammeNiveau::query()->create([
            'programme_id' => $programme->id,
            'niveau_id' => $niveau->id,
            'actif' => true,
        ]);

        ProgrammeNiveau::query()->create([
            'programme_id' => $autreProgramme->id,
            'niveau_id' => $niveau->id,
            'actif' => true,
        ]);

        TypeDocument::query()->create([
            'code' => 'document_test',
            'libelle' => 'Document de test',
            'actif' => true,
        ]);

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = mb_strtolower($query->sql);
        });

        $this->actingAs($admin)
            ->get('/back-office/administration')
            ->assertOk()
            ->assertViewHas('niveauxConfigures', 1)
            ->assertSee('Indicateurs du back-office')
            ->assertSee('Comptes internes')
            ->assertSee('Programmes actifs')
            ->assertSee('Niveaux configurés')
            ->assertSee('Module en préparation')
            ->assertSee('Aucun dossier n’est consulté');

        $this->assertFalse(
            collect($queries)->contains(fn (string $query): bool => str_contains($query, 'candidatures')),
        );
    }

    public function test_la_connexion_redirige_chaque_role_vers_son_accueil(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $admission = User::factory()->create();

        $this->post('/back-office/connexion', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('administration.dashboard', absolute: false));

        $this->post('/back-office/deconnexion');

        $this->post('/back-office/connexion', [
            'email' => $admission->email,
            'password' => 'password',
        ])->assertRedirect(route('back-office.dashboard', absolute: false));
    }
}
