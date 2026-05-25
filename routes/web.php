<?php

use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::prefix('admin')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    });



require __DIR__.'/settings.php';
