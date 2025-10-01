<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\SpaceController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;

Auth::routes();
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Admin
Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('/home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');
    
    Route::resource('reservations', AdminReservationController::class);
    Route::resource('users', UserController::class);
    Route::resource('spaces', SpaceController::class);
});


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

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');


// Profile Page
Route::middleware('auth')->group(function (){
    // PROFILE
    Route::get('/profile/{id}/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    // Account delete in profile page
    Route::delete('/profile/{id}/destroy', [ProfileController::class, 'destroy'])->name('profile.destroy');

});