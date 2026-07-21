<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/back-office', function () {
    return view('back-office.dashboard');
})->middleware(['auth', 'verified', 'actif'])->name('back-office.dashboard');

require __DIR__.'/auth.php';
