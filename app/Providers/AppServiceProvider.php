<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('rsvp-lookup', fn (Request $r) => Limit::perMinute(8)->by($r->ip()));
        RateLimiter::for('rsvp-submit', fn (Request $r) => Limit::perMinute(6)->by($r->ip()));
    }
}
