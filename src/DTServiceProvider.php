<?php

namespace Amprest\LaravelDT;

use Amprest\LaravelDT\Views\Components\DatatablesAssets;
use Amprest\LaravelDT\Views\Components\Datatable;
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
        //  Load the configuration file
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-dt.php', 'laravel-dt');

        //  Load the routes file
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        //  Load the views file
        $this->loadViewsFrom(__DIR__.'/../resources/views', config('laravel-dt.name'));

        //  Load helpers
        $this->loadHelpersFrom(__DIR__.'/../src/Utils');

        //  Load the blade components
        Blade::component('datatable', Datatable::class);
        Blade::component('datatables-assets', DatatablesAssets::class);
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