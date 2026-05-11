<?php

namespace App\Providers;

use App\Models\Contact;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Vite::prefetch(concurrency: 3);
        Paginator::useBootstrapFive();

        View::composer('layouts.admin', function ($view) {
            if (Auth::check() && Auth::user()->isAdmin()) {
                $unreadCount = Contact::whereNull('read_at')->whereNull('canceled_at')->count();
                $unreadContacts = Contact::with('user')
                    ->whereNull('read_at')
                    ->whereNull('canceled_at')
                    ->latest()
                    ->limit(5)
                    ->get();
                $view->with(compact('unreadCount', 'unreadContacts'));
            }
        });
    }
}
