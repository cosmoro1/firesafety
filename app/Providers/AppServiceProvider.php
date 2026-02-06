<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; 
use Illuminate\Support\Facades\URL; 

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Keep your existing Tailwind Pagination setting
        Paginator::useTailwind(); 

        // 2. Force HTTPS (Crucial for Railway/Heroku production environment)
        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // 3. FORCE IPv4 (The Fix for Gmail Timeouts)
        // This tells PHP to ignore IPv6 and bind strictly to IPv4 (0.0.0.0).
        // This bypasses the network block causing your "Connection timed out" error.
        stream_context_set_default([
            'socket' => [
                'bindto' => '0.0.0.0:0'
            ]
        ]);
    }
}