<?php

use App\Enums\RoleUtilisateur;
use App\Http\Controllers\BackOffice\Administration\DashboardController as AdministrationDashboardController;
use App\Http\Controllers\BackOffice\Administration\ProgrammeController as AdministrationProgrammeController;
use App\Http\Controllers\BackOffice\Administration\ProgrammeNiveauController;
use App\Http\Controllers\BackOffice\Administration\UtilisateurController;
use App\Http\Controllers\BackOffice\Admission\CandidatureController as AdmissionCandidatureController;
use App\Http\Controllers\BackOffice\Admission\CandidatureDocumentController as AdmissionCandidatureDocumentController;
use App\Http\Controllers\BackOffice\Admission\DashboardController as AdmissionDashboardController;
use App\Http\Controllers\BackOffice\Jury\CandidatureController as JuryCandidatureController;
use App\Http\Controllers\BackOffice\Jury\CandidatureDocumentController as JuryCandidatureDocumentController;
use App\Http\Controllers\BackOffice\Jury\DashboardController as JuryDashboardController;
use App\Http\Controllers\CandidatureComplementController;
use App\Http\Controllers\CandidatureSuiviController;
use App\Http\Controllers\LettreAdmissionController;
use App\Http\Controllers\ProgrammeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/programmes', [ProgrammeController::class, 'index'])->name('programmes.index');
Route::get('/programmes/{niveau}', [ProgrammeController::class, 'show'])->name('programmes.show');
Route::view('/candidatures/create', 'candidatures.create')->name('candidatures.create');
Route::get('/candidatures/{candidature}/suivi/{token}', CandidatureSuiviController::class)
    ->middleware('throttle:10,1')
    ->name('candidatures.suivi');
Route::get(
    '/candidatures/{candidature}/lettre-admission/{token}',
    LettreAdmissionController::class,
)
    ->middleware('throttle:10,1')
    ->name('candidatures.lettre-admission');
Route::get('/candidatures/{candidature}/complements/{token}', [CandidatureComplementController::class, 'edit'])
    ->middleware('throttle:10,1')
    ->name('candidatures.complements.edit');
Route::post('/candidatures/{candidature}/complements/{token}', [CandidatureComplementController::class, 'update'])
    ->middleware('throttle:10,1')
    ->name('candidatures.complements.update');
Route::get('/candidatures/{candidature}/complements/{token}/confirmation', [CandidatureComplementController::class, 'confirmation'])
    ->middleware('throttle:10,1')
    ->name('candidatures.complements.confirmation');

Route::get('/back-office', function (Request $request) {
    if ($request->user()->role === RoleUtilisateur::SUPER_ADMIN) {
        return redirect()->route('administration.dashboard');
    }

    if ($request->user()->role === RoleUtilisateur::ADMISSION) {
        return redirect()->route('admission.dashboard');
    }

    if ($request->user()->role === RoleUtilisateur::JURY) {
        return redirect()->route('jury.dashboard');
    }

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
        Route::get('/programmes', [AdministrationProgrammeController::class, 'index'])
            ->name('programmes.index');
        Route::get('/programmes/creer', [AdministrationProgrammeController::class, 'create'])
            ->name('programmes.create');
        Route::post('/programmes', [AdministrationProgrammeController::class, 'store'])
            ->name('programmes.store');
        Route::get('/programmes/{programme}/modifier', [AdministrationProgrammeController::class, 'edit'])
            ->name('programmes.edit');
        Route::put('/programmes/{programme}', [AdministrationProgrammeController::class, 'update'])
            ->name('programmes.update');
        Route::patch('/programmes/{programme}/etat', [AdministrationProgrammeController::class, 'modifierEtat'])
            ->name('programmes.etat');
        Route::post('/programmes/{programme}/niveaux', [ProgrammeNiveauController::class, 'store'])
            ->name('programmes.niveaux.store');
        Route::post('/programmes/{programme}/niveaux/nouveau', [ProgrammeNiveauController::class, 'storeNouveau'])
            ->name('programmes.niveaux.nouveau');
        Route::patch('/programme-niveaux/{programmeNiveau}', [ProgrammeNiveauController::class, 'update'])
            ->name('programme-niveaux.update');
    });

Route::prefix('back-office/admission')
    ->name('admission.')
    ->middleware(['auth', 'verified', 'actif'])
    ->group(function (): void {
        Route::get('/', AdmissionDashboardController::class)->name('dashboard');
        Route::get('/candidatures', [AdmissionCandidatureController::class, 'index'])
            ->name('candidatures.index');
        Route::get('/candidatures/{candidature}', [AdmissionCandidatureController::class, 'show'])
            ->name('candidatures.show');
        Route::post(
            '/candidatures/{candidature}/prise-en-charge',
            [AdmissionCandidatureController::class, 'prendreEnCharge'],
        )->name('candidatures.prise-en-charge');
        Route::post(
            '/candidatures/{candidature}/transmission-jury',
            [AdmissionCandidatureController::class, 'transmettreAuJury'],
        )->name('candidatures.transmission-jury');
        Route::post(
            '/candidatures/{candidature}/demande-complement',
            [AdmissionCandidatureController::class, 'demanderComplement'],
        )->name('candidatures.demande-complement');
        Route::get(
            '/documents/{document}/ouvrir',
            [AdmissionCandidatureDocumentController::class, 'show'],
        )->name('documents.show');
        Route::patch(
            '/documents/{document}',
            [AdmissionCandidatureDocumentController::class, 'update'],
        )->name('documents.update');
    });

Route::prefix('back-office/jury')
    ->name('jury.')
    ->middleware(['auth', 'verified', 'actif'])
    ->group(function (): void {
        Route::get('/', JuryDashboardController::class)->name('dashboard');
        Route::get('/candidatures', [JuryCandidatureController::class, 'index'])
            ->name('candidatures.index');
        Route::get('/candidatures/{candidature}', [JuryCandidatureController::class, 'show'])
            ->name('candidatures.show');
        Route::post(
            '/candidatures/{candidature}/demande-complement',
            [JuryCandidatureController::class, 'demanderComplement'],
        )->name('candidatures.demande-complement');
        Route::post(
            '/candidatures/{candidature}/decision',
            [JuryCandidatureController::class, 'decider'],
        )->name('candidatures.decision');
        Route::post(
            '/candidatures/{candidature}/reorientation',
            [JuryCandidatureController::class, 'reorienter'],
        )->name('candidatures.reorientation');
        Route::get(
            '/documents/{document}/ouvrir',
            [JuryCandidatureDocumentController::class, 'show'],
        )->name('documents.show');
    });

require __DIR__.'/auth.php';
