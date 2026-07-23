<?php

namespace App\Providers;

use App\Enums\RoleUtilisateur;
use App\Models\Candidature;
use App\Models\User;
use App\Policies\CandidaturePolicy;
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
        Gate::policy(Candidature::class, CandidaturePolicy::class);

        Gate::define(
            'administrer-back-office',
            fn (User $user): bool => $user->role === RoleUtilisateur::SUPER_ADMIN,
        );
    }
}
