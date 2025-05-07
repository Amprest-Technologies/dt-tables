<?php

namespace Amprest\LaravelDT;

use Amprest\LaravelDT\View\Components\DatatablesAssets;
use Amprest\LaravelDT\View\Components\Datatable;
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
        $this->publishes([
            __DIR__.'/../config/laravel-dt.php' => config_path('laravel-dt.php'),
        ]);

        //  Load the routes file
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        //  Load the views file
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-dt');

        //  Load the blade components
        Blade::component('datatable-source', Datatable::class);
        Blade::component('datatables-assets', DatatablesAssets::class);
    }
}