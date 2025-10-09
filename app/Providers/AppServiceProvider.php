<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
<<<<<<< HEAD
use Illuminate\Support\Facades\View;
use App\Models\CustomNotification;
=======
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
>>>>>>> a34d197bc6b4d2e2a4b441002a19d4db1ee2192b

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
<<<<<<< HEAD
        View::composer('layouts.app', function($view){
            if(auth()->check()){
                $notifications = auth()->user()
                        ->customNotifications()
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                        
                $view->with('notifications', $notifications);
            }
=======
        Paginator::useBootstrap();

        Gate::define('admin', function($user){
            return $user->role_id === User::ADMIN_ROLE_ID;
>>>>>>> a34d197bc6b4d2e2a4b441002a19d4db1ee2192b
        });
    }
}
