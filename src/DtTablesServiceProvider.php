<?php

namespace Amprest\DtTables;

use Amprest\DtTables\Console\Commands\SchemaSeeder;
use Amprest\DtTables\Providers\DatabaseServiceProvider;
use Amprest\DtTables\Views\Components\DataTable;
use Amprest\DtTables\Views\Components\DataTableAssets;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class DtTablesServiceProvider extends ServiceProvider
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
        $this->mergeConfigFrom(__DIR__."/../config/dt-tables.php", $this->packageName);

        //  Load the views file
        $this->loadViewsFrom(__DIR__.'/../resources/views', $this->packageName);

        //  Publish the configuration file
        $this->loadTranslationsFrom(__DIR__.'/../lang', $this->packageName);

        //  Load the routes file
        $this->app['router']
            ->name('dt-tables.')
            ->prefix($this->packageName)
            ->middleware('web')
            ->group(fn () => $this->loadRoutesFrom(__DIR__.'/../routes/web.php'));

        //  Load custom commands
        if ($this->app->runningInConsole()) {
            $this->commands([SchemaSeeder::class]);
        }

        //  Load the blade components
        Blade::component('data-table', DataTable::class);
        Blade::component('data-table-assets', DataTableAssets::class);
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