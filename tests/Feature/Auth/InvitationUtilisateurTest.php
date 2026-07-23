<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use App\Notifications\InvitationUtilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class InvitationUtilisateurTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_accepte_son_invitation_et_active_son_compte(): void
    {
        $utilisateur = User::factory()->jury()->inactive()->unverified()->create([
            'invitation_sent_at' => now(),
        ]);
        $token = Password::broker()->createToken($utilisateur);

        $this->get(route('invitation.accept', [
            'token' => $token,
            'email' => $utilisateur->email,
        ]))
            ->assertOk()
            ->assertSee('Définissez votre mot de passe');

        $this->post(route('invitation.store'), [
            'token' => $token,
            'email' => $utilisateur->email,
            'password' => 'MotDePasse!2026',
            'password_confirmation' => 'MotDePasse!2026',
        ])->assertRedirect(route('login'));

        $utilisateur->refresh();

        $this->assertTrue($utilisateur->actif);
        $this->assertNotNull($utilisateur->email_verified_at);
        $this->assertTrue(Hash::check('MotDePasse!2026', $utilisateur->password));
        $this->assertFalse(Password::broker()->tokenExists($utilisateur, $token));
    }

    public function test_une_invitation_expire_apres_soixante_minutes(): void
    {
        Carbon::setTestNow('2026-07-22 10:00:00');

        $utilisateur = User::factory()->jury()->inactive()->unverified()->create([
            'invitation_sent_at' => now(),
        ]);
        $token = Password::broker()->createToken($utilisateur);

        Carbon::setTestNow('2026-07-22 11:01:00');

        $this->get(route('invitation.accept', [
            'token' => $token,
            'email' => $utilisateur->email,
        ]))
            ->assertStatus(410)
            ->assertSee('Cette invitation n’est plus valide');

        $this->from(route('invitation.accept', [
            'token' => $token,
            'email' => $utilisateur->email,
        ]))->post(route('invitation.store'), [
            'token' => $token,
            'email' => $utilisateur->email,
            'password' => 'MotDePasse!2026',
            'password_confirmation' => 'MotDePasse!2026',
        ])->assertSessionHasErrors('email');

        $this->assertFalse($utilisateur->fresh()->actif);

        Carbon::setTestNow();
    }

    public function test_le_renvoi_remplace_l_ancien_token_et_est_historise(): void
    {
        Notification::fake();

        $administrateur = User::factory()->superAdmin()->create();
        $utilisateur = User::factory()->jury()->inactive()->unverified()->create([
            'invitation_sent_at' => now()->subMinutes(10),
        ]);
        $ancienToken = Password::broker()->createToken($utilisateur);

        $this->actingAs($administrateur)
            ->post(route('administration.utilisateurs.invitation.renvoyer', $utilisateur))
            ->assertSessionHasNoErrors();

        $utilisateur->refresh();

        $this->assertFalse(Password::broker()->tokenExists($utilisateur, $ancienToken));
        Notification::assertSentTo(
            $utilisateur,
            InvitationUtilisateur::class,
            fn (InvitationUtilisateur $notification): bool => Password::broker()
                ->tokenExists($utilisateur, $notification->token),
        );
        $this->assertDatabaseHas('actions_administratives', [
            'auteur_id' => $administrateur->id,
            'action' => 'invitation_renvoyee',
            'utilisateur_cible_id' => $utilisateur->id,
        ]);
    }

    public function test_un_compte_actif_ne_peut_pas_recevoir_une_invitation(): void
    {
        Notification::fake();

        $administrateur = User::factory()->superAdmin()->create();
        $utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::JURY,
        ]);

        $this->actingAs($administrateur)
            ->from(route('administration.utilisateurs.index'))
            ->post(route('administration.utilisateurs.invitation.renvoyer', $utilisateur))
            ->assertSessionHasErrors('invitation');

        Notification::assertNothingSent();
        $this->assertSame(0, DB::table('actions_administratives')->count());
    }
}
