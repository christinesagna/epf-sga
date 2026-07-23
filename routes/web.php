<?php

use App\Http\Controllers\BackOffice\Administration\DashboardController as AdministrationDashboardController;
use App\Http\Controllers\BackOffice\Administration\UtilisateurController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/back-office', function () {
    return view('back-office.dashboard');
})->middleware(['auth', 'verified', 'actif'])->name('back-office.dashboard');

Route::prefix('back-office/administration')
    ->name('administration.')
    ->middleware(['auth', 'verified', 'actif', 'can:administrer-back-office'])
    ->group(function (): void {
        Route::get('/', AdministrationDashboardController::class)->name('dashboard');
        Route::get('/utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs.index');
        Route::post('/utilisateurs', [UtilisateurController::class, 'store'])->name('utilisateurs.store');
        Route::post('/utilisateurs/{utilisateur}/invitation', [UtilisateurController::class, 'renvoyerInvitation'])
            ->name('utilisateurs.invitation.renvoyer');
        Route::patch('/utilisateurs/{utilisateur}/role', [UtilisateurController::class, 'modifierRole'])
            ->name('utilisateurs.role');
        Route::patch('/utilisateurs/{utilisateur}/etat', [UtilisateurController::class, 'modifierEtat'])
            ->name('utilisateurs.etat');
    });

require __DIR__.'/auth.php';
