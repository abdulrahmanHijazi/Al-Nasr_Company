<?php

use Illuminate\Support\Facades\Route;

// Dashboard / Filament handles its own routes.
// Inertia dashboard (optional) lives under /dashboard prefix.
Route::prefix('dashboard')->name('dashboard.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', fn () => \Inertia\Inertia::render('Dashboard/Index'))->name('index');
});
