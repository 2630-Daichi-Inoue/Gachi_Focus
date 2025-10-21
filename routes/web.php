<?php

use App\Models\User;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserSpaceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UtilityController;

// Admin Controllers
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\SpaceController;
use App\Http\Controllers\Admin\SpacesController;
use App\Http\Controllers\Admin\ReservationsController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\CategoriesController;

// ================================================
// Authentication
// ================================================
Auth::routes();

// ================================================
// User Area (Authenticated Users Only)
// ================================================
Route::middleware('auth')->group(function () {

    // Home
    Route::get('/', [HomeController::class, 'index'])->name('index');
    Route::get('/search', [HomeController::class, 'search'])->name('search');

    // Profile
// Admin
Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('/home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');
    
    Route::resource('reservations', AdminReservationController::class);
    Route::resource('users', UserController::class);
    Route::resource('spaces', SpaceController::class);
});

// reservation /change/cancle
Route::get('/room-b',  [ReservationController::class, 'create'])->name('rooms.reserve.form');  
Route::post('/room-b', [ReservationController::class, 'store'])->name('rooms.reserve.submit'); 

Route::resource('reservations', ReservationController::class)
    ->only(['show','edit','update'])
    ->middleware('auth');

Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])
    ->name('reservations.destroy');

// Route::get('/reserve', [ReservationController::class, 'show'])   ->name('reserve.coworkingspace');
// Route::get('/rooms/{slug}', [ReservationController::class, 'show'])   ->name('rooms.show');
// Route::post('/rooms/{slug}/reserve', [ReservationController::class, 'store'])->name('rooms.reserve');

// payment
Route::post('/rooms/reserve/preview', [ReservationController::class, 'preview'])
    ->name('rooms.reserve.preview');

Route::post('/pricing/quote', [ReservationController::class, 'quote'])
    ->name('pricing.quote');

Route::post('/checkout', [PaymentController::class, 'checkout'])
    ->name('checkout.start');

    // added to payment 251018seira
Route::middleware(['auth'])->group(function () {
    Route::post('/payments/checkout', [PaymentController::class, 'createCheckoutSession'])->name('payments.checkout');
    Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');
});

    // added to payment 251018seira　webhook (no CSRF because Stripe calls it directly)
    Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])
        ->name('stripe.webhook')
        ->withoutMiddleware([VerifyCsrfToken::class]);



// contact page
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
// Spaces
Route::get('spaces/{id}', [UserSpaceController::class, 'show'])->name('space.detail');

// Profile Page
Route::middleware('auth')->group(function (){
    // PROFILE
    Route::get('/profile/{id}/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile/{id}/destroy', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Utilities (co-working space categories etc.)
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
});

// ================================================
// Contact & Space Detail (Public)
// ================================================
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/spaces/{id}', [UserSpaceController::class, 'show'])->name('space.detail');

// ================================================
// Admin Area
// ================================================
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {

    // Dashboard
    Route::get('/home', [AdminHomeController::class, 'index'])->name('home');

    // Users
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::delete('/users/{id}/deactivate', [UsersController::class, 'deactivate'])->name('users.deactivate');
    Route::patch('/users/{id}/activate', [UsersController::class, 'activate'])->name('users.activate');

    // Reservations
    Route::resource('reservations', AdminReservationController::class);
    Route::get('/reservations', [ReservationsController::class, 'index'])->name('reservations');
    Route::patch('/reservations/{id}/action', [ReservationsController::class, 'action'])->name('reservations.action');

    // Spaces
    Route::resource('spaces', SpaceController::class);
    Route::get('/space/register', [SpacesController::class, 'register'])->name('space.register');
    Route::post('/space/store', [SpacesController::class, 'store'])->name('space.store');
    Route::get('/space/{id}/edit', [SpacesController::class, 'edit'])->name('space.edit');
    Route::patch('/space/{id}/update', [SpacesController::class, 'update'])->name('space.update');
    Route::delete('/space/{id}/destroy', [SpacesController::class, 'destroy'])->name('space.destroy');

});
// Notification Page
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');
    
