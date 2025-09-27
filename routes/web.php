<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SpaceController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

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

// Route::get('/reserve', [ReservationController::class, 'show'])   ->name('reserve.coworkingspace');
// Route::get('/rooms/{slug}', [ReservationController::class, 'show'])   ->name('rooms.show');
// Route::post('/rooms/{slug}/reserve', [ReservationController::class, 'store'])->name('rooms.reserve');

