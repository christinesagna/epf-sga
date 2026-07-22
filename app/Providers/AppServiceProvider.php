<?php

namespace App\Providers;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define(
            'administrer-back-office',
            fn (User $user): bool => $user->role === RoleUtilisateur::SUPER_ADMIN,
        );
    }
}
