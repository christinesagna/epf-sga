<?php

use App\Http\Controllers\ProgrammeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/programmes', [ProgrammeController::class, 'index'])->name('programmes.index');
Route::get('/programmes/{niveau}', [ProgrammeController::class, 'show'])->name('programmes.show');
Route::view('/candidatures/create', 'candidatures.create')->name('candidatures.create');


