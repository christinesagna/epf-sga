<?php

use App\Http\Controllers\BackOffice\Administration\DashboardController as AdministrationDashboardController;
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
    });

require __DIR__.'/auth.php';
