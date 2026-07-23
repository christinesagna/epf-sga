<?php

namespace Tests\Feature\Administration;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use App\Notifications\InvitationUtilisateur;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class GestionUtilisateursTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_gestion_des_utilisateurs_est_reservee_aux_super_administrateurs(): void
    {
        $jury = User::factory()->jury()->create();
        $administrateur = User::factory()->superAdmin()->create();

        $this->get('/back-office/administration/utilisateurs')
            ->assertRedirect(route('login'));

        $this->actingAs($jury)
            ->get('/back-office/administration/utilisateurs')
            ->assertForbidden();

        $this->actingAs($administrateur)
            ->get('/back-office/administration/utilisateurs')
            ->assertOk()
            ->assertSee('Gestion des utilisateurs internes')
            ->assertSee('Inviter un utilisateur');
    }

    public function test_un_super_administrateur_invite_un_utilisateur_sans_definir_son_mot_de_passe(): void
    {
        Notification::fake();

        $administrateur = User::factory()->superAdmin()->create();

        $this->actingAs($administrateur)
            ->post('/back-office/administration/utilisateurs', [
                'name' => 'Membre du jury',
                'email' => 'jury.invite@epf.example',
                'role' => RoleUtilisateur::JURY->value,
            ])
            ->assertRedirect(route('administration.utilisateurs.index'));

        $utilisateur = User::query()->where('email', 'jury.invite@epf.example')->firstOrFail();

        $this->assertFalse($utilisateur->actif);
        $this->assertNull($utilisateur->email_verified_at);
        $this->assertNotNull($utilisateur->invitation_sent_at);
        $this->assertSame(RoleUtilisateur::JURY, $utilisateur->role);
        $this->assertFalse(Hash::check('password', $utilisateur->password));

        Notification::assertSentTo(
            $utilisateur,
            InvitationUtilisateur::class,
            fn (InvitationUtilisateur $notification): bool => Password::broker()
                ->tokenExists($utilisateur, $notification->token),
        );

        $action = DB::table('actions_administratives')
            ->where('action', 'utilisateur_invite')
            ->where('auteur_id', $administrateur->id)
            ->where('utilisateur_cible_id', $utilisateur->id)
            ->first();

        $this->assertNotNull($action);
        $this->assertSame(
            RoleUtilisateur::JURY->value,
            json_decode($action->nouvelles_valeurs, true, flags: JSON_THROW_ON_ERROR)['role'],
        );
    }

    public function test_la_liste_peut_etre_recherchee_et_filtree(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        User::factory()->jury()->create([
            'name' => 'Alice Jury',
            'email' => 'alice.jury@epf.example',
        ]);
        User::factory()->create([
            'name' => 'Benoît Admission',
            'email' => 'benoit.admission@epf.example',
        ]);

        $this->actingAs($administrateur)
            ->get('/back-office/administration/utilisateurs?recherche=Alice&role=jury&etat=actif')
            ->assertOk()
            ->assertSee('Alice Jury')
            ->assertDontSee('Benoît Admission');
    }

    public function test_un_echec_d_envoi_ne_conserve_pas_un_compte_incomplet(): void
    {
        $administrateur = User::factory()->superAdmin()->create();

        $this->mock(Dispatcher::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')
                ->once()
                ->andThrow(new RuntimeException('Serveur SMTP indisponible.'));
        });

        $this->actingAs($administrateur)
            ->from(route('administration.utilisateurs.index'))
            ->post(route('administration.utilisateurs.store'), [
                'name' => 'Invitation impossible',
                'email' => 'smtp.indisponible@epf.example',
                'role' => RoleUtilisateur::JURY->value,
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', [
            'email' => 'smtp.indisponible@epf.example',
        ]);
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'smtp.indisponible@epf.example',
        ]);
        $this->assertDatabaseMissing('actions_administratives', [
            'action' => 'utilisateur_invite',
        ]);
    }

    public function test_le_role_est_modifie_et_la_desactivation_revoque_les_sessions(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $utilisateur = User::factory()->jury()->create();

        DB::table('sessions')->insert([
            'id' => 'session-utilisateur-test',
            'user_id' => $utilisateur->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload-test',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($administrateur)
            ->patch(route('administration.utilisateurs.role', $utilisateur), [
                'role' => RoleUtilisateur::ADMISSION->value,
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($administrateur)
            ->patch(route('administration.utilisateurs.etat', $utilisateur), [
                'actif' => false,
            ])
            ->assertSessionHasNoErrors();

        $utilisateur->refresh();

        $this->assertSame(RoleUtilisateur::ADMISSION, $utilisateur->role);
        $this->assertFalse($utilisateur->actif);
        $this->assertDatabaseMissing('sessions', ['id' => 'session-utilisateur-test']);
        $this->assertDatabaseHas('actions_administratives', [
            'action' => 'role_modifie',
            'utilisateur_cible_id' => $utilisateur->id,
        ]);
        $this->assertDatabaseHas('actions_administratives', [
            'action' => 'utilisateur_desactive',
            'utilisateur_cible_id' => $utilisateur->id,
        ]);
    }

    public function test_un_administrateur_ne_peut_pas_se_desactiver_ou_retrograder_le_dernier_administrateur_actif(): void
    {
        $administrateur = User::factory()->superAdmin()->create();

        $this->actingAs($administrateur)
            ->from(route('administration.utilisateurs.index'))
            ->patch(route('administration.utilisateurs.etat', $administrateur), [
                'actif' => false,
            ])
            ->assertSessionHasErrors('actif');

        $this->actingAs($administrateur)
            ->from(route('administration.utilisateurs.index'))
            ->patch(route('administration.utilisateurs.role', $administrateur), [
                'role' => RoleUtilisateur::JURY->value,
            ])
            ->assertSessionHasErrors('role');

        $administrateur->refresh();

        $this->assertTrue($administrateur->actif);
        $this->assertSame(RoleUtilisateur::SUPER_ADMIN, $administrateur->role);
    }

    public function test_un_compte_non_verifie_ne_peut_pas_etre_active_manuellement(): void
    {
        $administrateur = User::factory()->superAdmin()->create();
        $utilisateur = User::factory()->inactive()->unverified()->create([
            'invitation_sent_at' => now(),
        ]);

        $this->actingAs($administrateur)
            ->from(route('administration.utilisateurs.index'))
            ->patch(route('administration.utilisateurs.etat', $utilisateur), [
                'actif' => true,
            ])
            ->assertSessionHasErrors('actif');

        $this->assertFalse($utilisateur->fresh()->actif);
    }

    public function test_la_date_de_derniere_connexion_est_enregistree(): void
    {
        $utilisateur = User::factory()->create(['last_login_at' => null]);

        $this->post('/back-office/connexion', [
            'email' => $utilisateur->email,
            'password' => 'password',
        ])->assertRedirect(route('admission.dashboard', absolute: false));

        $this->assertNotNull($utilisateur->fresh()->last_login_at);
    }
}
