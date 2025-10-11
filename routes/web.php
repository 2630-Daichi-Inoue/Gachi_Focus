<?php

use App\Http\Controllers\Admin\CategoriesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

# ADMIN
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\SpacesController;
use App\Http\Controllers\Admin\ReservationsController;
use App\Models\User;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserSpaceController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;

use App\Http\Controllers\Admin\SpaceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\NotificationController;

Auth::routes();
# check if the user logged in
Route::group(['middleware' => 'auth'], function(){
    # if logged in, the user will be redirected to log in page
    Route::get('/', [HomeController::class,'index'])->name('index');
    Route::get('/search', [HomeController::class, 'search'])->name('search');
});

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
    Route::patch('profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    // Account delete in profile page
    Route::delete('/profile/{id}/destroy', [ProfileController::class, 'destroy'])->name('profile.destroy');




// edit coworkingspace category
Route::get('/utilities',        [UtilityController::class, 'index'])->name('utilities.index');
Route::post('/utilities',       [UtilityController::class, 'store'])->name('utilities.store');
Route::put('/utilities/{utility}',  [UtilityController::class, 'update'])->name('utilities.update');
Route::delete('/utilities/{utility}', [UtilityController::class, 'destroy'])->name('utilities.destroy');

    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function(){
        # USER
        Route::get('/users', [UsersController::class,'index'])->name('users');
        Route::delete('/users/{id}/deactivate', [UsersController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('/users/{id}/activate', [UsersController::class, 'activate'])->name('users.activate');

        # RESERVATION
        Route::get('/reservations', [ReservationsController::class, 'index'])->name('reservations');
        Route::patch('/reservations/{id}/action', [ReservationsController::class, 'action'])->name('reservations.action');

        # SPACE
        Route::get('/space/register', [SpacesController::class, 'register'])->name('space.register');
        Route::post('/space/store', [SpacesController::class, 'store'])->name('space.store');
        Route::get('/space/{id}/edit', [SpacesController::class, 'edit'])->name('space.edit');
        Route::patch('/space/{id}/update', [SpacesController::class,'update'])->name('space.update');
        Route::delete('/space/{id}/destroy', [SpacesController::class,'destroy'])->name('space.destroy');

        # CATEGORY
        Route::get('/categories', [CategoriesController::class, 'index'])->name('categories');
        Route::post('/categories/store', [CategoriesController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{id}/update', [CategoriesController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}/destroy', [CategoriesController::class, 'destroy'])->name('categories.destroy');
    });
});

// Notification Page
Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');
