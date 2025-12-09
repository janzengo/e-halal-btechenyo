<?php

declare(strict_types=1);

namespace App\Providers;

use App\Extensions\MultiAuthDatabaseSessionHandler;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
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
        Session::extend('multi_auth_database', function ($app) {
            $table = $app['config']['session.table'];
            $lifetime = $app['config']['session.lifetime'];
            $connection = $app['db']->connection($app['config']['session.connection']);

            return new MultiAuthDatabaseSessionHandler(
                $connection,
                $table,
                $lifetime,
                $app
            );
        });
    }
}
