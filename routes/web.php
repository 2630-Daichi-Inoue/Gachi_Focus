<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SpacesController;
use App\Http\Controllers\Admin\AmenitiesController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('index');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/spaces', [SpacesController::class, 'index'])
            ->name('spaces.index');

        Route::get('/spaces/register', [SpacesController::class, 'register'])
            ->name('spaces.register');

        Route::post('/spaces', [SpacesController::class, 'store'])
            ->name('spaces.store');

        Route::get('/spaces/{space}/edit', [SpacesController::class, 'edit'])
            ->withTrashed()
            ->name('spaces.edit');

        Route::patch('/spaces/{space}', [SpacesController::class, 'update'])
            ->withTrashed()
            ->name('spaces.update');

        Route::patch('/spaces/{space}/hide', [SpacesController::class, 'hide'])
            ->withTrashed()
            ->name('spaces.hide');

        Route::patch('/spaces/{space}/show', [SpacesController::class, 'show'])
            ->withTrashed()
            ->name('spaces.show');

        Route::delete('/spaces/{space}', [SpacesController::class, 'destroy'])
            ->name('spaces.destroy');

        Route::get('/amenities', [AmenitiesController::class, 'index'])
            ->name('amenities.index');

        Route::post('/amenities', [AmenitiesController::class, 'store'])
            ->name('amenities.store');

        Route::patch('/amenities/{amenity}', [AmenitiesController::class, 'update'])
            ->name('amenities.update');

        Route::delete('/amenities/{amenity}', [AmenitiesController::class, 'destroy'])
            ->name('amenities.destroy');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
