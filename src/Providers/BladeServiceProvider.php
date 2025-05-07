<?php

namespace Amprest\LaravelDatatables\Providers;

use Amprest\LaravelDatatables\View\Components\Datatable;
use Amprest\LaravelDatatables\View\Components\DatatablesScripts;
use Amprest\LaravelDatatables\View\Components\DatatablesStyles;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //  Include a customized alias for the datatables component
        Blade::component('datatable', Datatable::class);
        Blade::component('datatables-styles', DatatablesStyles::class);
        Blade::component('datatables-scripts', DatatablesScripts::class);
    }
}
