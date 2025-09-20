<?php

use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/room-b', [ReservationController::class, 'roomB']);
Route::post('/room-b', [ReservationController::class, 'reserve']);

Route::resource('reservations', ReservationController::class)
     ->only(['show','edit','update'])
     ->middleware('auth');



     
Route::get('/reserve', [ReservationController::class, 'show'])   ->name('reserve.coworkingspace');
Route::get('/rooms/{slug}', [ReservationController::class, 'show'])   ->name('rooms.show');
Route::post('/rooms/{slug}/reserve', [ReservationController::class, 'store'])->name('rooms.reserve');


