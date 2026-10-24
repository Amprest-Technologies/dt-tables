<?php

namespace Nyumbani\DtTables;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nyumbani\Docs\DocsRegistry;
use Nyumbani\DtTables\Console\Commands\MakeDataTableCommand;
use Nyumbani\DtTables\Docs\DtTablesDocs;
use Nyumbani\DtTables\Http\Middleware\AutoInjectDtTableAssets;
use Nyumbani\DtTables\Providers\RouteServiceProvider;
use Nyumbani\DtTables\Services\HelpersService;
use Nyumbani\DtTables\Support\Filesystem;
use Nyumbani\DtTables\Support\LegacyDataMigrator;
use Nyumbani\DtTables\Views\Components\DataTable;
use Nyumbani\DtTables\Views\Components\DataTableAssets;

class DtTablesServiceProvider extends ServiceProvider
{
    /**
     * Get a path inside the DtTables package, wherever Composer installed it.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function path(string $path = ''): string
    {
        return dirname(__DIR__).($path === '' ? '' : '/'.ltrim($path, '/'));
    }

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
        $this->mergeConfigFrom(self::path('config/dt-tables.php'), 'dt-tables');

        //  Load the views file
        $this->loadViewsFrom(self::path('resources/views'), 'dt-tables');

        //  Publish the configuration file
        $this->loadTranslationsFrom(self::path('lang'), 'dt-tables');

        //  Load the routes files
        $this->loadRoutes();

        //  Register global middleware
        $this->app[Kernel::class]->pushMiddleware(AutoInjectDtTableAssets::class);

        //  Create the package assets
        $this->createAssets();

        //  Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([MakeDataTableCommand::class]);
        }

        //  Load the main blade component
        Blade::component('data-table', DataTable::class);

        //  Load the assets blade component
        Blade::component('data-table-assets', DataTableAssets::class);

        //  Add the docs, which Nyumbani Docs serves, when it is installed
        if (class_exists(DocsRegistry::class)) {
            $this->registerDocs();
        }
    }

    /**
     * Add the DtTables docs to the Nyumbani docs, which Nyumbani Docs serves.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    protected function registerDocs(): void
    {
        //  Describe the pages
        $this->app->make(DocsRegistry::class)->register(DtTablesDocs::describe());
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
        //  Get the directory
        $directory = dt_tables_storage_path();

        //  Migrate any legacy single-file dt-tables.json into the new folder structure
        if (file_exists($legacyPath = base_path('dt-tables.json'))) {
            (new LegacyDataMigrator($legacyPath, $directory))->handle();
        }

        //  Create the directory
        Filesystem::ensureDirectoryExists($directory);
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
            ->group(fn () => $this->loadRoutesFrom(self::path('routes/web.php')));

        //  Load the API file
        $this->app[Router::class]
            ->name('dt-tables.api.')
            ->prefix('api/dt-tables')
            ->middleware('api')
            ->group(fn () => $this->loadRoutesFrom(self::path('routes/api.php')));
    }
}
