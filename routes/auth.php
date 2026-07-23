<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\InvitationUtilisateurController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('back-office')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('connexion', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('connexion', [AuthenticatedSessionController::class, 'store']);

        Route::get('mot-de-passe-oublie', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('mot-de-passe-oublie', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::get('reinitialisation/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('reinitialisation', [NewPasswordController::class, 'store'])
            ->name('password.store');

        Route::get('invitation/{token}', [InvitationUtilisateurController::class, 'create'])
            ->name('invitation.accept');

        Route::post('invitation', [InvitationUtilisateurController::class, 'store'])
            ->name('invitation.store');
    });

    Route::middleware(['auth', 'actif'])->group(function () {
        Route::get('verification-email', EmailVerificationPromptController::class)
            ->name('verification.notice');

        Route::get('verification-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('verification-email/notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

        Route::post('deconnexion', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');
    });
});
