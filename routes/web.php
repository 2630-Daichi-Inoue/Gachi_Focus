<?php

use App\Http\Controllers\Admin\CategoriesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

# ADMIN
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\PostsController;
use App\Http\Controllers\Admin\SpacesController;
use App\Http\Controllers\Admin\ReservationsController;

Auth::routes();
# check if the user logged in
Route::group(['middleware' => 'auth'], function(){
    # if logged in, the user will be redirected to log in page
    Route::get('/', [HomeController::class,'index'])->name('index');
    Route::get('/search', [HomeController::class, 'search'])->name('search');

    # PROFILE
    Route::get('/profile/{id}/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function(){
        # USER
        Route::get('/users', [UsersController::class,'index'])->name('users');
        Route::delete('/users/{id}/deactivate', [UsersController::class,'deactivate'])->name('users.deactivate');
        Route::patch('/users/{id}/activate', [UsersController::class,'activate'])->name('users.activate');

        # RESERVATION
        Route::get('/reservations', [ReservationsController::class,'index'])->name('reservations');
        Route::patch('/reservations/{id}/action', [ReservationsController::class,'action'])->name('reservations.action');

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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
