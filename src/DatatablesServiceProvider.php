<?php

namespace Amprest\LaravelDatatables;

use Amprest\LaravelDatatables\Console\Commands\DatatablesInstall;
use Amprest\LaravelDatatables\Providers\BladeServiceProvider;
use Amprest\LaravelDatatables\Providers\FacadeServiceProvider;
use Illuminate\Support\ServiceProvider;

class DatatablesServiceProvider extends ServiceProvider
{
    /**
     * Variable to store the root path
     *
     * @var string
     */
    private string $root;

    /**
     * Variable to store the package's name
     *
     * @var string
     */
    private string $packageName = '';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //  Determine the root path
        $this->root = __DIR__.DIRECTORY_SEPARATOR.'..';

        //  Load helper functions
        $this->registerHelpers();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //  Register service providers
        $this->registerServiceProviders();

        //  Load route files
        $this->registerRouteFiles();

        //  Load config files
        $this->registerConfigFiles();

        //  Register commands
        $this->registerCommands();

        //  Register the views folder
        $this->registerViewsFolder();

        //  Allow publishing of config files
        $this->allowPublishingOfConfigFiles();
    }

    /**
     * Register service providers
     *
     * @return void
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    public function registerServiceProviders(): void
    {
        $this->app->register(BladeServiceProvider::class);
        $this->app->register(FacadeServiceProvider::class);
    }

    /**
     * Register helper files
     *
     * @return void
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    protected function registerHelpers(): void
    {
        foreach (glob(__DIR__.DIRECTORY_SEPARATOR.'Utils'.DIRECTORY_SEPARATOR.'*.php') as $helper) {
            require_once $helper;
        }
    }

    /**
     * Register route files
     *
     * @return void
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    public function registerRouteFiles(): void
    {
        //  Create a route group
        $this->app['router']->name('datatables.')->prefix('datatables')
            ->middleware(['web'])
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'routes/web.php');
            });
    }

    /**
     * Register the config files
     *
     * @return void
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    public function registerConfigFiles()
    {
        $this->mergeConfigFrom($this->root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'package.php', 'package');
        $this->mergeConfigFrom($this->root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'datatables.php', 'datatables');
    }

    /**
     * Register the package commands
     *
     * @return void
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    public function registerCommands()
    {
        //  Register custom package commands
        if ($this->app->runningInConsole()) {
            $this->commands([DatatablesInstall::class]);
        }
    }

    /**
     * Register the views folder
     *
     * @return void
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    public function registerViewsFolder()
    {
        //  Load other package file dependancies
        $this->loadViewsFrom(
            $this->root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views',
            config('package.name')
        );
    }

    /**
     * Allow config files to be published
     *
     * @return void
     *
     * @author Alvin Gichira Kaburu <geekaburu@amprest.co.ke>
     */
    public function allowPublishingOfConfigFiles()
    {
        //  Allow the config files to be published.
        $this->publishes([
            $this->root.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'datatables.php' => config_path('datatables.php'),
        ], 'datatables-config');

        //  Allow public assets to be published
        $this->publishes([
            $this->root.DIRECTORY_SEPARATOR.'public' => public_path('vendor'.DIRECTORY_SEPARATOR.''.$this->packageName),
        ], 'datatables-assets');
    }
}
