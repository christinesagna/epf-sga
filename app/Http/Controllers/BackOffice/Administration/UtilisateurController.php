<?php

namespace App\Http\Controllers\BackOffice\Administration;

use App\Enums\RoleUtilisateur;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\InvitationUtilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'recherche' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::enum(RoleUtilisateur::class)],
            'etat' => ['nullable', Rule::in(['actif', 'inactif', 'invitation'])],
        ]);

        $utilisateurs = User::query()
            ->whereNotNull('role')
            ->when($filtres['recherche'] ?? null, function ($query, string $recherche): void {
                $query->where(function ($query) use ($recherche): void {
                    $query
                        ->where('name', 'like', "%{$recherche}%")
                        ->orWhere('email', 'like', "%{$recherche}%");
                });
            })
            ->when($filtres['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filtres['etat'] ?? null, function ($query, string $etat): void {
                match ($etat) {
                    'actif' => $query->where('actif', true),
                    'inactif' => $query->where('actif', false)->whereNotNull('email_verified_at'),
                    'invitation' => $query
                        ->where('actif', false)
                        ->whereNotNull('invitation_sent_at')
                        ->whereNull('email_verified_at'),
                };
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('back-office.administration.utilisateurs.index', [
            'utilisateurs' => $utilisateurs,
            'roles' => RoleUtilisateur::cases(),
            'filtres' => $filtres,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::enum(RoleUtilisateur::class)],
        ]);

        $utilisateur = User::query()->create([
            'name' => $donnees['name'],
            'email' => Str::lower($donnees['email']),
            'role' => $donnees['role'],
            'actif' => false,
            'password' => Str::random(64),
        ]);

        $token = Password::broker()->createToken($utilisateur);
        $utilisateur->notify(new InvitationUtilisateur($token));

        $utilisateur->forceFill([
            'invitation_sent_at' => now(),
        ])->save();

        $this->historiser(
            $request->user(),
            'utilisateur_invite',
            $utilisateur,
            null,
            [
                'role' => $utilisateur->role->value,
                'actif' => false,
                'invitation_sent_at' => $utilisateur->invitation_sent_at->toIso8601String(),
            ],
        );

        return redirect()
            ->route('administration.utilisateurs.index')
            ->with('status', "L’invitation de {$utilisateur->name} a été envoyée.");
    }

    public function renvoyerInvitation(Request $request, User $utilisateur): RedirectResponse
    {
        if ($utilisateur->actif || $utilisateur->email_verified_at !== null) {
            throw ValidationException::withMessages([
                'invitation' => 'Seul un compte en attente peut recevoir une nouvelle invitation.',
            ]);
        }

        $ancienneDate = $utilisateur->invitation_sent_at?->toIso8601String();
        $token = Password::broker()->createToken($utilisateur);
        $utilisateur->notify(new InvitationUtilisateur($token));

        $utilisateur->forceFill([
            'invitation_sent_at' => now(),
        ])->save();

        $this->historiser(
            $request->user(),
            'invitation_renvoyee',
            $utilisateur,
            ['invitation_sent_at' => $ancienneDate],
            ['invitation_sent_at' => $utilisateur->invitation_sent_at->toIso8601String()],
        );

        return back()->with('status', "Une nouvelle invitation a été envoyée à {$utilisateur->name}.");
    }

    public function modifierRole(Request $request, User $utilisateur): RedirectResponse
    {
        $donnees = $request->validate([
            'role' => ['required', Rule::enum(RoleUtilisateur::class)],
        ]);

        $nouveauRole = RoleUtilisateur::from($donnees['role']);
        $ancienRole = $utilisateur->role;

        if ($ancienRole === $nouveauRole) {
            return back()->with('status', 'Le rôle est déjà à jour.');
        }

        if ($utilisateur->actif
            && $ancienRole === RoleUtilisateur::SUPER_ADMIN
            && $nouveauRole !== RoleUtilisateur::SUPER_ADMIN
            && $this->nombreSuperAdministrateursActifs() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'Le dernier super-administrateur actif ne peut pas changer de rôle.',
            ]);
        }

        DB::transaction(function () use ($request, $utilisateur, $ancienRole, $nouveauRole): void {
            $utilisateur->role = $nouveauRole;
            $utilisateur->save();

            $this->historiser(
                $request->user(),
                'role_modifie',
                $utilisateur,
                ['role' => $ancienRole->value],
                ['role' => $nouveauRole->value],
            );
        });

        return back()->with('status', "Le rôle de {$utilisateur->name} a été modifié.");
    }

    public function modifierEtat(Request $request, User $utilisateur): RedirectResponse
    {
        $donnees = $request->validate([
            'actif' => ['required', 'boolean'],
        ]);

        $nouvelEtat = (bool) $donnees['actif'];
        $ancienEtat = $utilisateur->actif;

        if ($ancienEtat === $nouvelEtat) {
            return back()->with('status', 'L’état du compte est déjà à jour.');
        }

        if (! $nouvelEtat && $request->user()->is($utilisateur)) {
            throw ValidationException::withMessages([
                'actif' => 'Vous ne pouvez pas désactiver votre propre compte.',
            ]);
        }

        if ($nouvelEtat && $utilisateur->email_verified_at === null) {
            throw ValidationException::withMessages([
                'actif' => 'Le compte sera activé lorsque l’utilisateur aura accepté son invitation.',
            ]);
        }

        if (! $nouvelEtat
            && $utilisateur->role === RoleUtilisateur::SUPER_ADMIN
            && $this->nombreSuperAdministrateursActifs() <= 1) {
            throw ValidationException::withMessages([
                'actif' => 'Le dernier super-administrateur actif ne peut pas être désactivé.',
            ]);
        }

        DB::transaction(function () use ($request, $utilisateur, $ancienEtat, $nouvelEtat): void {
            $utilisateur->actif = $nouvelEtat;
            $utilisateur->save();

            if (! $nouvelEtat) {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $utilisateur->id)
                    ->delete();
            }

            $this->historiser(
                $request->user(),
                $nouvelEtat ? 'utilisateur_active' : 'utilisateur_desactive',
                $utilisateur,
                ['actif' => $ancienEtat],
                ['actif' => $nouvelEtat],
            );
        });

        return back()->with('status', "L’état du compte de {$utilisateur->name} a été modifié.");
    }

    private function nombreSuperAdministrateursActifs(): int
    {
        return User::query()
            ->where('role', RoleUtilisateur::SUPER_ADMIN->value)
            ->where('actif', true)
            ->count();
    }

    /**
     * @param  array<string, mixed>|null  $anciennesValeurs
     * @param  array<string, mixed>|null  $nouvellesValeurs
     */
    private function historiser(
        User $auteur,
        string $action,
        User $utilisateurCible,
        ?array $anciennesValeurs,
        ?array $nouvellesValeurs,
    ): void {
        DB::table('actions_administratives')->insert([
            'auteur_id' => $auteur->id,
            'action' => $action,
            'utilisateur_cible_id' => $utilisateurCible->id,
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
