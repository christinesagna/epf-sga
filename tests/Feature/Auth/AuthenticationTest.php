<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/back-office/connexion');

        $response->assertOk()
            ->assertSee('EPF Africa')
            ->assertSee('Email professionnel');
    }

    public function test_candidate_home_remains_public_and_registration_is_disabled(): void
    {
        $this->get('/')->assertOk();
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_active_verified_users_can_authenticate(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/back-office/connexion', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admission.dashboard', absolute: false));

        $this->get('/back-office')
            ->assertRedirect(route('admission.dashboard'));

        $this->get(route('admission.dashboard'))
            ->assertOk()
            ->assertSee(RoleUtilisateur::ADMISSION->libelle());
    }

    public function test_un_membre_du_jury_est_redirige_vers_son_espace(): void
    {
        $jury = User::factory()->jury()->create();

        $this->post('/back-office/connexion', [
            'email' => $jury->email,
            'password' => 'password',
        ])->assertRedirect(route('jury.dashboard', absolute: false));

        $this->get('/back-office')
            ->assertRedirect(route('jury.dashboard'));
    }

    public function test_invalid_credentials_and_inactive_accounts_use_the_same_error(): void
    {
        $user = User::factory()->create();
        $inactiveUser = User::factory()->inactive()->create();

        $this->post('/back-office/connexion', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email' => 'Identifiants invalides.']);

        $this->post('/back-office/connexion', [
            'email' => $inactiveUser->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['email' => 'Identifiants invalides.']);

        $this->assertGuest();
    }

    public function test_unverified_users_are_sent_to_email_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->post('/back-office/connexion', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admission.dashboard', absolute: false));

        $this->get('/back-office')->assertRedirect(route('verification.notice'));
    }

    public function test_an_existing_session_is_closed_when_the_account_is_deactivated(): void
    {
        $user = User::factory()->create();

        $user->update(['actif' => false]);

        $this->actingAs($user)
            ->get('/back-office')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post('/back-office/connexion', [
                'email' => $user->email,
                'password' => 'incorrect',
            ]);
        }

        $this->post('/back-office/connexion', [
            'email' => $user->email,
            'password' => 'incorrect',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/back-office/deconnexion');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
