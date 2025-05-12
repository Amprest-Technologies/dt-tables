<?php

namespace Amprest\LaravelDT;

use Amprest\LaravelDT\Views\Components\Datatable;
use Amprest\LaravelDT\Views\Components\DatatableAssets;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class DTServiceProvider extends ServiceProvider
{
    /**
     * Define the package name.
     *
     */
    protected string $packageName = 'laravel-dt';

    /**
     * Register the application services.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function register(): void
    {
        $this->createDatabaseConnection();
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

        //  Load helpers
        $this->loadHelpersFrom(__DIR__.'/../src/Utils');

        //  Load the routes file
        $this->app['router']
            ->name('laravel-dt.')
            ->prefix($this->packageName)
            ->middleware('web')
            ->group(fn () => $this->loadRoutesFrom(__DIR__.'/../routes/web.php'));

        //  Load the views file
        $this->loadViewsFrom(__DIR__.'/../resources/views', $this->packageName);

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

    /**
     * Create the database connection for the package.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    protected function createDatabaseConnection(): void
    {
        //  Check if the database exists and create it if it doesn't
        if (! file_exists($path = base_path('laravel-dt.sqlite'))) {
            touch($path);
        }

        //  Set the connection config early
        config()->set("database.connections.{$this->packageName}", [
            'driver' => 'sqlite',
            'database' => $path,
            'foreign_key_constraints' => true,
        ]);

        //  Check if the table exists,
        if(!Schema::connection($this->packageName)->hasTable('data_tables')) {
            //  If it doesnt exist, create it
            Schema::connection($this->packageName)->create('data_tables', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
    }
}