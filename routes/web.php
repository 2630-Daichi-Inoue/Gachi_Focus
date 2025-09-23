<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SpaceController;

Route::get('/', function () {
    return view('welcome');
});

// Admin
Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('/home', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('home');
    
    Route::resource('reservations', App\Http\Controllers\Admin\ReservationController::class);
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::resource('spaces', App\Http\Controllers\Admin\SpaceController::class);
});