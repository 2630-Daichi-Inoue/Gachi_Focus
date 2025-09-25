<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SpaceController;
use App\Http\Controllers\Admin\ReservationController;

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


// Admin
Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('/home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');
    
    Route::resource('reservations', App\Http\Controllers\Admin\ReservationController::class);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::resource('spaces', App\Http\Controllers\Admin\SpaceController::class);
});

