<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\SpacesController;
use App\Http\Controllers\Admin\ReservationsController;
use App\Http\Controllers\Admin\AmenitiesController;
use App\Http\Controllers\Admin\ReviewsController;
use \App\Http\Controllers\Admin\ContactsController;
use \App\Http\Controllers\Admin\AnnouncementsController;

// User Controllers
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('index');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Admin Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Admin Spaces Management
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

        // Admin Amenities Management
        Route::get('/amenities', [AmenitiesController::class, 'index'])
            ->name('amenities.index');

        Route::post('/amenities', [AmenitiesController::class, 'store'])
            ->name('amenities.store');

        Route::patch('/amenities/{amenity}', [AmenitiesController::class, 'update'])
            ->name('amenities.update');

        Route::delete('/amenities/{amenity}', [AmenitiesController::class, 'destroy'])
            ->name('amenities.destroy');

        // Admin Reservations Management
        Route::get('/reservations', [ReservationsController::class, 'index'])
            ->name('reservations.index');

        Route::patch('/reservations/{reservation}/cancel', [ReservationsController::class, 'cancel'])
            ->name('reservations.cancel');

        // Admin Users Management
        Route::get('/users', [UsersController::class, 'index'])
            ->name('users.index');

        Route::patch('/users/{user}/restrict', [UsersController::class, 'restrict'])
            ->name('users.restrict');

        Route::patch('/users/{user}/activate', [UsersController::class, 'activate'])
            ->name('users.activate');

        Route::patch('/users/{user}/ban', [UsersController::class, 'ban'])
            ->name('users.ban');

        // Admin Reviews Management
        Route::get('/reviews', [ReviewsController::class, 'index'])
            ->name('reviews.index');

        Route::patch('/reviews/{review}/hide', [ReviewsController::class, 'hide'])
            ->withTrashed()
            ->name('reviews.hide');

        Route::patch('/reviews/{review}/show', [ReviewsController::class, 'show'])
            ->withTrashed()
            ->name('reviews.show');

        // Admin Contacts Management
        Route::get('/contacts', [ContactsController::class, 'index'])
            ->name('contacts.index');

        Route::patch('/contacts/{contact}/read', [ContactsController::class, 'read'])
            ->name('contacts.read');

        Route::patch('/contacts/{contact}/close', [ContactsController::class, 'close'])
            ->name('contacts.close');

        // Admin Announcements Management
        Route::get('/announcements', [AnnouncementsController::class, 'index'])
            ->name('announcements.index');

        Route::get('/announcements/create', [AnnouncementsController::class, 'create'])
            ->name('announcements.create');

        Route::post('/announcements', [AnnouncementsController::class, 'store'])
            ->name('announcements.store');

        Route::patch('/announcements/{announcement}/hide', [AnnouncementsController::class, 'hide'])
            ->name('announcements.hide');

    }
);

Route::middleware('auth')->group(function () {

    // User Spaces Routes
    Route::get('/spaces', [SpaceController::class, 'index'])
            ->name('spaces.index');
    Route::get('/spaces/{space}', [SpaceController::class, 'show'])
            ->name('spaces.show');
    Route::get('/spaces/{space}/reviews', [SpaceController::class, 'reviewIndex'])
            ->name('spaces.reviewIndex');

    // User Reservation-making Routes
    Route::get('/spaces/{space}/reservations/create', [ReservationController::class, 'create'])
            ->name('reservations.create');
    Route::get('/spaces/{space}/reservations/payment', [ReservationController::class, 'payment'])
            ->name('reservations.payment');
    Route::post('/spaces/{space}/reservations', [ReservationController::class, 'store'])
            ->name('reservations.store');

    // User Reservation Management Routes
    Route::get('/reservations', [ReservationController::class, 'index'])
            ->name('reservations.index');
    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])
            ->name('reservations.cancel');

    // User Review Routes
    Route::get('/reservations/{reservation}/reviews', [ReviewController::class, 'index'])
            ->name('reviews.index');
    Route::get('/reservations/{reservation}/reviews/create', [ReviewController::class, 'createOrEdit'])
            ->name('reviews.create');
    Route::post('/reservations/{reservation}/reviews', [ReviewController::class, 'store'])
            ->name('reviews.store');
    Route::get('/reservations/{reservation}/reviews/edit', [ReviewController::class, 'createOrEdit'])
            ->name('reviews.edit');
    Route::patch('/reservations/{reservation}/reviews', [ReviewController::class, 'update'])
            ->name('reviews.update');
    Route::delete('/reservations/{reservation}/reviews', [ReviewController::class, 'destroy'])
            ->name('reviews.destroy');

    // User Contact Routes
    Route::get('/contacts', [\App\Http\Controllers\ContactController::class, 'index'])
            ->name('contacts.index');
    Route::get('/contacts/create', [\App\Http\Controllers\ContactController::class, 'create'])
            ->name('contacts.create');
    Route::post('/contacts', [\App\Http\Controllers\ContactController::class, 'store'])
            ->name('contacts.store');
    Route::patch('/contacts/{contact}/cancel', [\App\Http\Controllers\ContactController::class, 'cancel'])
            ->name('contacts.cancel');

    // User Announcement Routes
    Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index'])
            ->name('announcements.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
