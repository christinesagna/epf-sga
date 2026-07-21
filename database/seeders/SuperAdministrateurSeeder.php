<?php

namespace Database\Seeders;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

class SuperAdministrateurSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('backoffice.super_admin.name');
        $email = config('backoffice.super_admin.email');
        $password = config('backoffice.super_admin.password');

        if (! is_string($name) || $name === ''
            || ! is_string($email) || $email === ''
            || ! is_string($password) || $password === '') {
            throw new RuntimeException(
                'Définissez SUPER_ADMIN_NAME, SUPER_ADMIN_EMAIL et SUPER_ADMIN_PASSWORD avant de lancer ce seeder.',
            );
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 12) {
            throw new RuntimeException(
                'SUPER_ADMIN_EMAIL doit être valide et SUPER_ADMIN_PASSWORD doit contenir au moins 12 caractères.',
            );
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser) {
            if ($existingUser->role !== RoleUtilisateur::SUPER_ADMIN) {
                throw new RuntimeException(
                    "L'adresse {$email} appartient déjà à un utilisateur qui n'est pas super-administrateur.",
                );
            }

            return;
        }

        $user = new User([
            'name' => $name,
            'email' => $email,
            'role' => RoleUtilisateur::SUPER_ADMIN,
            'actif' => true,
            'password' => $password,
        ]);

        $user->email_verified_at = Carbon::now();
        $user->save();
    }
}
