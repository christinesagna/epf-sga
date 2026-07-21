<?php

use App\Http\Controllers\ProgrammeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/programmes', [ProgrammeController::class, 'index'])->name('programmes.index');
Route::get('/programmes/{niveau}', [ProgrammeController::class, 'show'])->name('programmes.show');
Route::view('/candidatures/create', 'candidatures.create')->name('candidatures.create');


Route::get('/back-office', function () {
    return view('back-office.dashboard');
})->middleware(['auth', 'verified', 'actif'])->name('back-office.dashboard');

require __DIR__.'/auth.php';

