<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvitationUtilisateurController extends Controller
{
    public function create(Request $request, string $token): View|Response
    {
        $email = $request->string('email')->toString();
        $utilisateur = User::query()->where('email', $email)->first();

        $lienValide = $utilisateur !== null
            && ! $utilisateur->actif
            && $utilisateur->email_verified_at === null
            && $utilisateur->invitation_sent_at !== null
            && Password::broker()->tokenExists($utilisateur, $token);

        $donnees = [
            'email' => $email,
            'token' => $token,
            'lienValide' => $lienValide,
        ];

        return $lienValide
            ? view('back-office.auth.accept-invitation', $donnees)
            : response()->view('back-office.auth.accept-invitation', $donnees, 410);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $invitationEnAttente = User::query()
            ->where('email', $donnees['email'])
            ->where('actif', false)
            ->whereNull('email_verified_at')
            ->whereNotNull('invitation_sent_at')
            ->exists();

        if (! $invitationEnAttente) {
            throw ValidationException::withMessages([
                'email' => 'Cette invitation est invalide ou a déjà été utilisée.',
            ]);
        }

        $statut = Password::broker()->reset(
            $donnees,
            function (User $utilisateur, string $password): void {
                $utilisateur->forceFill([
                    'password' => $password,
                    'email_verified_at' => now(),
                    'actif' => true,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($utilisateur));
            },
        );

        if ($statut !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'Cette invitation est invalide ou expirée.',
            ]);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Votre mot de passe est défini. Vous pouvez maintenant vous connecter.');
    }
}
