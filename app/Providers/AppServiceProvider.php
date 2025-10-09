<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\CustomNotification;

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
        View::composer('layouts.app', function($view){
            if(auth()->check()){
                $notifications = auth()->user()
                        ->customNotifications()
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                        
                $view->with('notifications', $notifications);
            }
        });
    }
}
