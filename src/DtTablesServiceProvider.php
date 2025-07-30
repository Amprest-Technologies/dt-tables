<?php

namespace Amprest\DtTables;

use Amprest\DtTables\Http\Middleware\AutoInjectDtTableAssets;
use Amprest\DtTables\Providers\RouteServiceProvider;
use Amprest\DtTables\Services\HelpersService;
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
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function register(): void
    {
        //  Load helpers
        $this->loadHelpersFrom(__DIR__.'/../src/Utils');

        //  Register other service providers
        $this->app->register(RouteServiceProvider::class);

        //  Register the helpers service
        $this->app->singleton('dtTableHelper', function ($app) {
            return new HelpersService;
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function boot(): void
    {
        //  Load the configuration file
        $this->mergeConfigFrom(package_path('config/dt-tables.php'), 'dt-tables');

        //  Load the views file
        $this->loadViewsFrom(package_path('resources/views'), 'dt-tables');

        //  Publish the configuration file
        $this->loadTranslationsFrom(package_path('lang'), 'dt-tables');

        //  Load the routes files
        $this->loadRoutes();

        //  Register global middleware
        $this->app[Kernel::class]->pushMiddleware(AutoInjectDtTableAssets::class);

        //  Create the package assets
        $this->createAssets();

        //  Load the blade components
        Blade::component('data-table', DataTable::class);
        Blade::component('data-table-assets', DataTableAssets::class);
    }

    /**
     * Add any package helpers to the application.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function loadHelpersFrom(string $path): void
    {
        foreach (glob("$path/*.php") as $helper) {
            require_once $helper;
        }
    }

    /**
     * Create the package folder if it doesn't exist.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function createAssets(): void
    {
        if (! file_exists($path = base_path('dt-tables.json'))) {
            touch($path);
        }
    }

    /**
     * Load the package routes.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function loadRoutes(): void
    {
        //  Load the web file
        $this->app[Router::class]
            ->name('dt-tables.')
            ->prefix('dt-tables')
            ->middleware('web')
            ->group(fn () => $this->loadRoutesFrom(package_path('routes/web.php')));

        //  Load the API file
        $this->app[Router::class]
            ->name('dt-tables.api.')
            ->prefix('api/dt-tables')
            ->middleware('api')
            ->group(fn () => $this->loadRoutesFrom(package_path('routes/api.php')));
    }
}
