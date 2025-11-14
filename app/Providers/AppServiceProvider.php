<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

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
        // Rate limiter for login attempts (5 attempts per minute per user/IP)
        RateLimiter::for('login.limit', function () {
            return Limit::perMinute(5)->by(
                auth()->id() ?: request()->ip()
            );
        });
        
        // Rate limiter for registration attempts (3 attempts per minute per user/IP)
        RateLimiter::for('register.limit', function () {
            return Limit::perMinute(3)->by(
                auth()->id() ?: request()->ip()
            );
        });
    }
}
