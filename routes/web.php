<?php

use App\Http\Controllers\BackOffice\Administration\DashboardController as AdministrationDashboardController;
use App\Http\Controllers\BackOffice\Administration\UtilisateurController;
use App\Http\Controllers\CandidatureComplementController;
use App\Http\Controllers\ProgrammeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/programmes', [ProgrammeController::class, 'index'])->name('programmes.index');
Route::get('/programmes/{niveau}', [ProgrammeController::class, 'show'])->name('programmes.show');
Route::view('/candidatures/create', 'candidatures.create')->name('candidatures.create');
Route::get('/candidatures/{candidature}/complements/{token}', [CandidatureComplementController::class, 'edit'])
    ->middleware('throttle:10,1')
    ->name('candidatures.complements.edit');
Route::post('/candidatures/{candidature}/complements/{token}', [CandidatureComplementController::class, 'update'])
    ->middleware('throttle:10,1')
    ->name('candidatures.complements.update');
Route::get('/candidatures/{candidature}/complements/{token}/confirmation', [CandidatureComplementController::class, 'confirmation'])
    ->middleware('throttle:10,1')
    ->name('candidatures.complements.confirmation');

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
