<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\SpacesController;
use App\Http\Controllers\Admin\ReservationsController;
use App\Http\Controllers\Admin\AmenitiesController;
use App\Http\Controllers\Admin\ReviewsController;
use \App\Http\Controllers\Admin\ContactsController;
use \App\Http\Controllers\Admin\AnnouncementsController;
use \App\Http\Controllers\Admin\NotificationsController;

// User Controllers
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('index');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Admin Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Admin Spaces Management
        Route::prefix('spaces')->name('spaces.')->controller(SpacesController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{space}/edit', 'edit')->withTrashed()->name('edit');
            Route::patch('/{space}', 'update')->withTrashed()->name('update');
            Route::patch('/{space}/hide', 'hide')->withTrashed()->name('hide');
            Route::patch('/{space}/show', 'show')->withTrashed()->name('show');
            Route::delete('/{space}', 'destroy')->name('destroy');
        });

        // Admin Amenities Management
        Route::prefix('amenities')->name('amenities.')->controller(AmenitiesController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::patch('/{amenity}', 'update')->name('update');
            Route::delete('/{amenity}', 'destroy')->name('destroy');
        });

        // Admin Reservations Management
        Route::prefix('reservations')->name('reservations.')->controller(ReservationsController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/{reservation}/cancel', 'cancel')->name('cancel');
        });

        // Admin Users Management
        Route::prefix('users')->name('users.')->controller(UsersController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/{user}/restrict', 'restrict')->name('restrict');
            Route::patch('/{user}/activate', 'activate')->name('activate');
            Route::patch('/{user}/ban', 'ban')->name('ban');
        });

        // Admin Reviews Management
        Route::prefix('reviews')->name('reviews.')->controller(ReviewsController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/{review}/hide', 'hide')->withTrashed()->name('hide');
            Route::patch('/{review}/show', 'show')->withTrashed()->name('show');
        });

        // Admin Contacts Management
        Route::prefix('contacts')->name('contacts.')->controller(ContactsController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/{contact}/read', 'read')->name('read');
            Route::patch('/{contact}/close', 'close')->name('close');
        });

        // Admin Announcements Management
        Route::prefix('announcements')->name('announcements.')->controller(AnnouncementsController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::patch('/{announcement}/hide', 'hide')->name('hide');
        });

        // Admin Notifications Management
        Route::get('/notifications', [NotificationsController::class, 'index'])
            ->name('notifications.index');

        Route::get('/space-notifications/{space}/create', [NotificationsController::class, 'create'])
            ->name('space-notifications.create');

        Route::post('/space-notifications/{space}', [NotificationsController::class, 'store'])
            ->name('space-notifications.store');

        Route::get('/user-notifications/{user}/create', [NotificationsController::class, 'create'])
            ->name('user-notifications.create');

        Route::post('/user-notifications/{user}', [NotificationsController::class, 'store'])
            ->name('user-notifications.store');

        Route::get('/contact-notifications/{contact}/create', [NotificationsController::class, 'create'])
            ->name('contact-notifications.create');

        Route::post('/contact-notifications/{contact}', [NotificationsController::class, 'store'])
            ->name('contact-notifications.store');

    }
);

Route::middleware('auth')->group(function () {

    // Profile
    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // Spaces
    Route::prefix('spaces')->group(function () {
        Route::controller(SpaceController::class)->group(function () {
            Route::get('/', 'index')->name('spaces.index');
            Route::get('/{space}', 'show')->name('spaces.show');
            Route::get('/{space}/reviews', 'reviewIndex')->name('spaces.reviewIndex');
        });

        Route::prefix('{space}')->group(function () {
            Route::prefix('reservations')->name('reservations.')->controller(ReservationController::class)->group(function () {
                Route::get('/create', 'create')->name('create');
                Route::get('/payment', 'payment')->name('payment');
                Route::post('/', 'store')->name('store');
            });

            Route::prefix('favorite')->name('favorites.')->controller(FavoriteController::class)->group(function () {
                Route::post('/', 'store')->name('store');
                Route::delete('/', 'destroy')->name('destroy');
            });
        });
    });

    // Reservations
    Route::prefix('reservations')->group(function () {
        Route::controller(ReservationController::class)->group(function () {
            Route::get('/', 'index')->name('reservations.index');
            Route::patch('/{reservation}/cancel', 'cancel')->name('reservations.cancel');
        });

        Route::prefix('{reservation}/reviews')->name('reviews.')->controller(ReviewController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'createOrEdit')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/edit', 'createOrEdit')->name('edit');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
        });
    });

    // Contacts
    Route::prefix('contacts')->name('contacts.')->controller(ContactController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::patch('/{contact}/cancel', 'cancel')->name('cancel');
    });

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/{notification}/read', 'read')->name('read');
    });

    // Payments
    Route::prefix('payments')->name('payments.')->controller(PaymentController::class)->group(function () {
        Route::get('/{reservation}/checkout', 'checkout')->name('checkout');
        Route::get('/{reservation}/success',  'success')->name('success');
        Route::get('/{reservation}/cancel',   'cancel')->name('cancel');
    });
});

// Stripe webhook — outside auth middleware, CSRF excluded via VerifyCsrfToken
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

require __DIR__.'/auth.php';
