<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\CustomNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Support\Facades\URL;

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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $notifications = CustomNotification::where('receiver_id', auth()->id())
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                $view->with('notifications', $notifications);
            }
        });

        Paginator::useBootstrap();

        Gate::define('admin', function ($user) {
            return $user->role_id === User::ADMIN_ROLE_ID;
        });
    }
}
