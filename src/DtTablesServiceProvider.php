<?php

namespace Amprest\DtTables;

use Amprest\DtTables\Console\Commands\SchemaSeeder;
use Amprest\DtTables\Http\Middleware\AutoInjectDtTableAssets;
use Amprest\DtTables\Providers\DatabaseServiceProvider;
use Amprest\DtTables\Views\Components\DataTable;
use Amprest\DtTables\Views\Components\DataTableAssets;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class DtTablesServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function register(): void
    {
        //  Load helpers
        $this->loadHelpersFrom(__DIR__.'/../src/Utils');

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
        $this->mergeConfigFrom(package_path('config/dt-tables.php'), 'dt-tables');

        //  Load the views file
        $this->loadViewsFrom(package_path('resources/views'), 'dt-tables');

        //  Publish the configuration file
        $this->loadTranslationsFrom(package_path('lang'), 'dt-tables');

        //  Load the routes file
        $this->app[Router::class]
            ->name('dt-tables.')
            ->prefix('dt-tables')
            ->middleware('web')
            ->group(fn () => $this->loadRoutesFrom(package_path('routes/web.php')));

        //  Register global middleware
        $this->app[Kernel::class]->pushMiddleware(AutoInjectDtTableAssets::class);

        //  Load custom commands
        if ($this->app->runningInConsole()) {
            //  Add the package commands
            $this->commands([SchemaSeeder::class]);

            //  Prohibit the schema seeder in production
            SchemaSeeder::prohibit(! $this->app->environment('local'));
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