<?php

namespace LaravelLens\LaravelLens;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaravelLens\LaravelLens\Console\Commands\LensAuditCommand;

class LaravelLensServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-lens.php', 'laravel-lens');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerPublishing();
        $this->registerCommands();
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('laravel-lens.route_prefix', 'laravel-lens'),
            'middleware' => config('laravel-lens.middleware', ['web']),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Register the package views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-lens');
    }

    /**
     * Register the package's Artisan commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LensAuditCommand::class,
            ]);
        }
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-lens.php' => config_path('laravel-lens.php'),
            ], 'laravel-lens-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-lens'),
            ], 'laravel-lens-views');
        }
    }
}
