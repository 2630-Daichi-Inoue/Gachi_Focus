<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


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