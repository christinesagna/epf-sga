<?php

namespace Tests\Feature\Administration;

use App\Enums\CandidatureStatut;
use App\Enums\RoleUtilisateur;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function test_un_super_administrateur_accede_aux_indicateurs_groupes_du_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create();
        User::factory()->jury()->create();
        User::factory()->unverified()->create();
        User::factory()->jury()->inactive()->unverified()->create([
            'invitation_sent_at' => now(),
        ]);

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

        $statuts = [
            CandidatureStatut::BROUILLON,
            CandidatureStatut::SOUMISE,
            CandidatureStatut::SOUMISE,
            CandidatureStatut::EN_TRAITEMENT_ADMISSION,
            CandidatureStatut::COMPLEMENT_ADMISSION,
            CandidatureStatut::TRANSMISE_AU_JURY,
            CandidatureStatut::TRANSMISE_AU_JURY,
            CandidatureStatut::COMPLEMENT_JURY,
            CandidatureStatut::ADMISE,
            CandidatureStatut::ADMISE,
            CandidatureStatut::REFUSEE,
        ];

        foreach ($statuts as $index => $statut) {
            $this->creerCandidature($programme, $statut, $index);
        }

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = mb_strtolower($query->sql);
        });

        $this->actingAs($admin)
            ->get('/back-office/administration')
            ->assertOk()
            ->assertViewHas('niveauxConfigures', 1)
            ->assertViewHas('invitationsEnAttente', 1)
            ->assertViewHas('indicateursCandidatures', [
                'total' => 10,
                'nouvelles' => 2,
                'admission' => 1,
                'complements' => 2,
                'complements_admission' => 1,
                'complements_jury' => 1,
                'jury' => 2,
                'decisions' => 3,
                'admises' => 2,
                'refusees' => 1,
            ])
            ->assertSee('Suivi des candidatures')
            ->assertSee('Configuration du back-office')
            ->assertSee('Comptes internes')
            ->assertSee('Invitations en attente')
            ->assertSee('Programmes actifs')
            ->assertSee('Niveaux configurés')
            ->assertSee('Admission : 1 · Jury : 1')
            ->assertSee('Admises : 2 · Refusées : 1')
            ->assertSee('Gérer les utilisateurs')
            ->assertSee('Gérer les programmes')
            ->assertDontSee('Module en préparation')
            ->assertDontSee('La gestion des comptes internes permettra');

        $this->assertCount(
            1,
            collect($queries)->filter(
                fn (string $query): bool => str_contains($query, 'from `candidatures`'),
            ),
        );
    }

    public function test_le_dashboard_affiche_des_indicateurs_a_zero_sans_candidature(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('administration.dashboard'))
            ->assertOk()
            ->assertViewHas('indicateursCandidatures', [
                'total' => 0,
                'nouvelles' => 0,
                'admission' => 0,
                'complements' => 0,
                'complements_admission' => 0,
                'complements_jury' => 0,
                'jury' => 0,
                'decisions' => 0,
                'admises' => 0,
                'refusees' => 0,
            ])
            ->assertSee('Dossiers soumis')
            ->assertSee('Les brouillons ne sont pas comptabilisés');
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
        ])->assertRedirect(route('admission.dashboard', absolute: false));
    }

    public function test_un_super_administrateur_est_toujours_dirige_vers_le_dashboard_administratif(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->get('/back-office')
            ->assertRedirect(route('login'));

        $this->post('/back-office/connexion', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('administration.dashboard', absolute: false));

        $this->get('/back-office')
            ->assertRedirect(route('administration.dashboard'));

        $this->get('/back-office/administration')
            ->assertOk()
            ->assertSee('Suivi des candidatures');
    }

    private function creerCandidature(
        Programme $programme,
        CandidatureStatut $statut,
        int $index,
    ): Candidature {
        $candidat = Candidat::query()->create([
            'nom' => "Candidat {$index}",
            'prenom' => 'Test',
            'email' => "candidat-{$index}@example.com",
        ]);

        return Candidature::query()->create([
            'candidat_id' => $candidat->id,
            'programme_id' => $programme->id,
            'edit_token' => (string) Str::uuid(),
            'statut' => $statut,
            'submitted_at' => $statut === CandidatureStatut::BROUILLON ? null : now(),
        ]);
    }
}
