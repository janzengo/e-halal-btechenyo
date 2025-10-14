<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\Guards\VoterGuard;
use App\Auth\Providers\AdminProvider;
use App\Auth\Providers\VoterProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom guard
        Auth::extend('voter', function ($app, $name, array $config) {
            $provider = new VoterProvider;

            return new VoterGuard($provider, $app['request']);
        });

        // Register custom user provider
        Auth::provider('voter', function ($app, array $config) {
            return new VoterProvider;
        });

        // Register admin user provider
        Auth::provider('admin', function ($app, array $config) {
            return new AdminProvider;
        });
    }
}
