<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Fix for JWT Auth with PHP 7.4 - register User model binding
        $this->app->bind('App\Models\User', function ($app) {
            return new \App\Models\User([]);
        });
    }
}
