<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing Page (Single Page Application)
Route::get('/', function () {
    return Inertia::render('welcome');
})->name('landing');

// Legacy routes for backward compatibility
Route::get('/home', function () {
    return Inertia::render('welcome');
})->name('home');

// Include all route files
require __DIR__.'/auth.php';
require __DIR__.'/voters.php';
require __DIR__.'/head.php';
require __DIR__.'/officers.php';
require __DIR__.'/settings.php';
