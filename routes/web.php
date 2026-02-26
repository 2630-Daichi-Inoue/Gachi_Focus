<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Controllers (public/user)
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CategoryController;
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
*/
// 2/26修正
// Route::get('/', [HomeController::class, 'index'])->name('index');
// Route::redirect('/', '/login')->name('index');
// Route::get('/', function () {
//     return view('auth.login'); // Laravel UIならだいたいこれ
// })->name('index');
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();

    if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
        return redirect()->route('admin.home');
    }

    return app(\App\Http\Controllers\HomeController::class)->index();
})->name('index');

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
    //  Reservation (current system)
    Route::get('/current-reservation', [ReservationController::class, 'currentShow'])->name('reservations.current');
    Route::get('/past-reservation', [ReservationController::class, 'pastShow'])->name('reservations.past');

    //  Invoice download
    Route::get('/reservations/{id}/invoice', [ReservationController::class, 'downloadInvoice'])->name('reservations.invoice');

    //  Reservation actions
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::get('/reservations/{id}/rebook', [ReservationController::class, 'rebook'])->name('reservations.rebook');

    //  Resource routes
    Route::resource('reservations', ReservationController::class)->only(['show', 'edit', 'update', 'destroy']);

    //  Reservation (new room flow)
    Route::get('/rooms/{space}/reserve', [ReservationController::class, 'create'])->name('rooms.reserve.form');
    Route::post('/rooms/{space}/reserve', [ReservationController::class, 'store'])->name('rooms.reserve.submit');
    Route::get('/rooms/{space}/show', [ReservationController::class, 'showRoom'])->name('rooms.show');
    Route::post('/rooms/reserve/preview', [ReservationController::class, 'preview'])->name('rooms.reserve.preview');

    //  Pricing quote API
    Route::post('/pricing/quote', [ReservationController::class, 'quote'])->name('pricing.quote');


    /*
    |--------------------------------------------------------------------------
    | Payments (Stripe)
    |--------------------------------------------------------------------------
    */
    Route::post('/reservations/{reservation}/pay', [PaymentController::class, 'checkout'])->name('reservations.pay');
    Route::get('/payments/success/{reservation}', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/cancel/{reservation}', [PaymentController::class, 'cancel'])->name('payments.cancel');
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook (no CSRF)
|--------------------------------------------------------------------------
*/
// Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
//     ->name('stripe.webhook')
//     ->withoutMiddleware([VerifyCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Reviews (public)
|--------------------------------------------------------------------------
*/
Route::get('/reviews/{space}', [ReviewController::class, 'index'])->name('reviews.index');
Route::post('/reviews/{space}', [ReviewController::class, 'store'])->name('reviews.store');
Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

/*
|--------------------------------------------------------------------------
| Profile / Notifications / Utilities
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

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Area
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
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
});
