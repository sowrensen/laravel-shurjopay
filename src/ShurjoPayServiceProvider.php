<?php

namespace Sowren\ShurjoPay;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ShurjoPayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->registerResources();
    }

    /**
     * Register all package resources.
     *
     * @return void
     */
    private function registerResources()
    {
        $this->registerRoutes();
    }

    /**
     * Register package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/shurjopay.php' => config_path('shurjopay.php'),
        ], 'ls-config');
    }

    /**
     * Register package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfigurations(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Get route group configurations.
     *
     * @return array
     */
    private function routeConfigurations()
    {
        return [
            'prefix' => 'shurjopay',
            'namespace' => 'Sowren\ShurjoPay\Http\Controllers',
        ];
    }
}
