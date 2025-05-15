<?php

namespace Amprest\LaravelDT;

use Amprest\LaravelDT\Console\Commands\SchemaSeeder;
use Amprest\LaravelDT\Providers\DatabaseServiceProvider;
use Amprest\LaravelDT\Views\Components\Datatable;
use Amprest\LaravelDT\Views\Components\DatatableAssets;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class DTServiceProvider extends ServiceProvider
{
    /**
     * Define the package name.
     *
     */
    protected string $packageName = '';

    /**
     * Register the application services.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function register(): void
    {
        //  Load helpers
        $this->loadHelpersFrom(__DIR__.'/../src/Utils');

        //  Load the package config
        $this->packageName = package_name();

        //  Register other service providers
        $this->app->register(DatabaseServiceProvider::class);
    }

    /**
     * Bootstrap the application services.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function boot(): void
    {
        //  Load the configuration file
        $this->mergeConfigFrom(__DIR__."/../config/laravel-dt.php", $this->packageName);

        //  Load the routes file
        $this->app['router']
            ->name('laravel-dt.')
            ->prefix($this->packageName)
            ->middleware('web')
            ->group(fn () => $this->loadRoutesFrom(__DIR__.'/../routes/web.php'));

        //  Load the views file
        $this->loadViewsFrom(__DIR__.'/../resources/views', $this->packageName);

        //  Load custom commands
        if ($this->app->runningInConsole()) {
            $this->commands([SchemaSeeder::class]);
        }

        //  Load the blade components
        Blade::component('datatable', Datatable::class);
        Blade::component('datatable-assets', DatatableAssets::class);
    }

    /**
     * Add any package helpers to the application.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function loadHelpersFrom(string $path): void
    {
        foreach (glob("$path/*.php") as $helper) {
            require_once $helper;
        }
    }
}