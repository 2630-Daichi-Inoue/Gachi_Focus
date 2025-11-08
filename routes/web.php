<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Controllers (public/user)
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\UserSpaceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReservationController;

// Admin Controllers
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\ReservationsController;
use App\Http\Controllers\Admin\SpaceController;
use App\Http\Controllers\Admin\SpacesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;

// Payments
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;

// Middleware
use App\Http\Middleware\VerifyCsrfToken;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
| NOTE: Move into auth group if you want to require login for home/search.
*/
Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/search', [HomeController::class, 'search'])->name('search');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/spaces/{id}', [UserSpaceController::class, 'show'])->name('space.detail');

/*
|--------------------------------------------------------------------------
| Reservation (User) - auth required
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Primary endpoints (rooms.* as primary)
    |--------------------------------------------------------------------------
    */
    // Reserve form (rooms.*)
    Route::get('/rooms/{space}/reserve', [ReservationController::class, 'create'])
        ->name('rooms.reserve.form');

    // Submit reservation (rooms.*)
    Route::post('/rooms/{space}/reserve', [ReservationController::class, 'store'])
        ->name('rooms.reserve.submit');

    // Show after reserve (rooms.*)
    Route::get('/rooms/{space}/show', [ReservationController::class, 'showRoom'])
        ->name('rooms.show');

    // Preview before checkout (rooms.*)
    Route::post('/rooms/reserve/preview', [ReservationController::class, 'preview'])
        ->name('rooms.reserve.preview');

    // Pricing quote API (AJAX)
    Route::post('/pricing/quote', [ReservationController::class, 'quote'])
        ->name('pricing.quote');

    /*
    |--------------------------------------------------------------------------
    | Back-compat aliases (spaces.*) - keep teammates' links working
    |--------------------------------------------------------------------------
    */
    Route::get('/spaces/{space}/reserve', [ReservationController::class, 'create'])
        ->name('spaces.reserve.form'); // alias
    Route::post('/spaces/{space}/reserve', [ReservationController::class, 'store'])
        ->name('spaces.reserve.submit'); // alias
    Route::get('/spaces/{space}/show', [ReservationController::class, 'showRoom'])
        ->name('spaces.show'); // alias
    Route::post('/spaces/reserve/preview', [ReservationController::class, 'preview'])
        ->name('spaces.reserve.preview'); // alias

    /*
    |--------------------------------------------------------------------------
    | Reservation custom actions
    |--------------------------------------------------------------------------
    */
    Route::get('/current-reservation', [ReservationController::class, 'currentShow'])
        ->name('reservations.current');
    Route::get('/past-reservation', [ReservationController::class, 'pastShow'])
        ->name('reservations.past');

    Route::get('/reservations/{id}/invoice', [ReservationController::class, 'downloadInvoice'])
        ->name('reservations.invoice');

    // Keep existing signature (id) to avoid breaking teammates
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])
        ->name('reservations.cancel');

    Route::get('/reservations/{id}/rebook', [ReservationController::class, 'rebook'])
        ->name('reservations.rebook');

    // Resource routes (place after specific routes to prevent collisions)
    Route::resource('reservations', ReservationController::class)
        ->only(['show','edit','update','destroy']);

    /*
    |--------------------------------------------------------------------------
    | Payments (Checkout)
    |--------------------------------------------------------------------------
    */

    // create checkout session (used by Stripe.js)
    // Start checkout
    Route::post('/reservations/{reservation}/pay', [PaymentController::class, 'checkout'])
        ->name('reservations.pay');

    // Return URLs
    Route::get('/payments/success/{reservation}', [PaymentController::class, 'success'])
        ->name('payments.success');
    Route::get('/payments/cancel/{reservation}', [PaymentController::class, 'cancel'])
        ->name('payments.cancel');
});

/*
|--------------------------------------------------------------------------
| Backward compatibility for old /room-b?space_id=1
|--------------------------------------------------------------------------
| Redirect /room-b -> new /rooms/{space}/reserve while preserving space_id.
*/
Route::get('/room-b', function (\Illuminate\Http\Request $request) {
    $spaceId = $request->query('space_id');
    if ($spaceId) {
        // Prefer rooms.* primary (spaces.* still available as alias)
        return redirect()->route('rooms.reserve.form', ['space' => $spaceId], 301);
    }
    return redirect()->route('index', [], 302);
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook (no CSRF)
|--------------------------------------------------------------------------
*/
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Reviews (public or switch to auth if desired)
|--------------------------------------------------------------------------
*/
Route::get('/reviews/{space}', [ReviewController::class, 'index'])->name('reviews.index');
Route::post('/reviews/{space}', [ReviewController::class, 'store'])->name('reviews.store');
Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

/*
|--------------------------------------------------------------------------
| Profile / Utilities / Notifications (auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile/{id}/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile/{id}/destroy', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Utilities (tags/categories for facilities)
    Route::get('/utilities', [UtilityController::class, 'index'])->name('utilities.index');
    Route::post('/utilities', [UtilityController::class, 'store'])->name('utilities.store');
    Route::put('/utilities/{utility}', [UtilityController::class, 'update'])->name('utilities.update');
    Route::delete('/utilities/{utility}', [UtilityController::class, 'destroy'])->name('utilities.destroy');

    // Reservations (user side)
    Route::get('/room-b', [ReservationController::class, 'create'])->name('rooms.reserve.form');
    Route::post('/room-b', [ReservationController::class, 'store'])->name('rooms.reserve.submit');
    Route::resource('reservations', ReservationController::class)->only(['show', 'edit', 'update']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
    // current
    Route::get('/current-reservation', [ReservationController::class, 'currentShow'])->name('reservations.current');
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::get('/reservations/{id}/rebook', [ReservationController::class, 'rebook'])->name('reservations.rebook');
    // past
    Route::get('/past-reservation', [ReservationController::class, 'pastShow'])->name('reservations.past');
    Route::get('/reservations/{id}/invoice', [ReservationController::class, 'downloadInvoice'])->name('reservations.invoice');
});

// ================================================
// Admin Area
// ================================================
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    // Dashboard
    Route::get('/home', [AdminHomeController::class, 'index'])->name('home');

    // Users
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::delete('/users/{id}/deactivate', [UsersController::class, 'deactivate'])->name('users.deactivate');
    Route::patch('/users/{id}/activate', [UsersController::class, 'activate'])->name('users.activate');

    // Reservations
    // Route::resource('reservations', AdminReservationController::class);
    Route::get('/reservations', [ReservationsController::class, 'index'])->name('reservations.index');
    // Custom admin action (keep path/name if teammates use it)

    Route::patch('/reservations/{id}/action', [ReservationsController::class, 'action'])->name('reservations.action');

    // Spaces
    Route::get('/spaces', [SpacesController::class, 'index'])->name('spaces.index');
    Route::get('/spaces/register', [SpacesController::class, 'register'])->name('spaces.register');
    Route::post('/spaces/store', [SpacesController::class, 'store'])->name('spaces.store');
    Route::get('/spaces/{id}/edit', [SpacesController::class, 'edit'])->name('spaces.edit');
    Route::patch('/spaces/{id}/update', [SpacesController::class, 'update'])->name('spaces.update');
    Route::delete('/spaces/{id}/destroy', [SpacesController::class, 'destroy'])->name('spaces.destroy');

    // Notifications
    Route::resource('notifications', AdminNotificationController::class);
    Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');

    // Categories (optional)
    // Route::get('/categories', [CategoriesController::class, 'index'])->name('categories');
    // Route::post('/categories/store', [CategoriesController::class, 'store'])->name('categories.store');
    // Route::patch('/categories/{id}/update', [CategoriesController::class, 'update'])->name('categories.update');
    // Route::delete('/categories/{id}/destroy', [CategoriesController::class, 'destroy'])->name('categories.destroy');
});
