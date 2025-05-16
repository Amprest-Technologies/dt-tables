<?php

use Amprest\DtTables\Http\Controllers\AssetController;
use Amprest\DtTables\Http\Controllers\DataTableColumnController;
use Amprest\DtTables\Http\Controllers\DataTableController;
use Amprest\DtTables\Http\Middleware\PreventIfEnvironmentIsNotLocal;
use Illuminate\Support\Facades\Route;

//  Define the route for the datatable assets
Route::get('assets/{name}', AssetController::class)->name('asset.show');

//  Define the middleware for the routes
Route::middleware(PreventIfEnvironmentIsNotLocal::class)->group(function(){
    //  Define the route for the data table model
    Route::resource('data-tables', DataTableController::class)->except(['show']);
    
    //  Define the route for the data table columns
    Route::resource('data-tables.data-table-columns', DataTableColumnController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();
});