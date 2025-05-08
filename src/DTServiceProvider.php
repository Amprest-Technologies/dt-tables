<?php

namespace Amprest\LaravelDT;

use Amprest\LaravelDT\Views\Components\Datatable;
use Amprest\LaravelDT\Views\Components\DatatableAssets;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class DTServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function boot(): void
    {
        //  Load helpers
        $this->loadHelpersFrom(__DIR__.'/../src/Utils');

        //  Load the configuration file
        $this->mergeConfigFrom(__DIR__."/../config/laravel-dt.php", 'laravel-dt');

        //  Load the routes file
        $this->app['router']
            ->name('laravel-dt.')
            ->prefix('laravel-dt')
            ->middleware('web')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });

        //  Load the views file
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-dt');

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