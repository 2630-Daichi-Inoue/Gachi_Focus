<?php

use App\Http\Controllers\Admin\CategoriesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FollowController;
# ADMIN
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\PostsController;
use App\Http\Controllers\Admin\SpacesController;


Auth::routes();
# check if the user logged in
Route::group(['middleware' => 'auth'], function(){
    # if logged in, the user will be redirected to log in page
    Route::get('/', [HomeController::class,'index'])->name('index');
    Route::get('/search', [HomeController::class, 'search'])->name('search');

    # SPACE
    Route::get('/post/create', [PostController::class, 'create'])->name('post.create');
    Route::post('/post/store', [PostController::class, 'store'])->name('post.store');
    Route::get('/post/{id}/show', [PostController::class, 'show'])->name('post.show');
    Route::get('/post/{id}/edit', [PostController::class, 'edit'])->name('post.edit');
    Route::patch('/post/{id}/update', [PostController::class,'update'])->name('post.update');
    Route::delete('/post/{id}/destroy', [PostController::class, 'destroy'])->name('post.destroy');

    # COMMENT
    Route::post('/comment/{post_id}/store', [CommentController::class, 'store'])->name('comment.store');
    Route::delete('/comment/{id}/destroy', [CommentController::class, 'destroy'])->name('comment.destroy');

    # PROFILE
    Route::get('/profile/{id}/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function(){
        # USER
        Route::get('/users', [UsersController::class,'index'])->name('users');
        Route::delete('/users/{id}/deactivate', [UsersController::class,'deactivate'])->name('users.deactivate');
        Route::patch('/users/{id}/activate', [UsersController::class,'activate'])->name('users.activate');

        # POST
        Route::get('/posts/create', [PostsController::class, 'register'])->name('posts');
        Route::delete('/posts/{id}/hide', [PostsController::class,'hide'])->name('posts.hide');
        Route::patch('/posts/{id}/unhide', [PostsController::class,'unhide'])->name('posts.unhide');

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
