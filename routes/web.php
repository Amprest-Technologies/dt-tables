<?php

use Amprest\LaravelDT\Http\Controllers\AssetController;
use Amprest\LaravelDT\Http\Controllers\DataTableColumnController;
use Amprest\LaravelDT\Http\Controllers\DataTableController;
use Illuminate\Support\Facades\Route;

//  Define the route for the datatable assets
Route::get('assets/{name}', AssetController::class)->name('asset.show');

//  Define the route for the data table model
Route::resource('data-tables', DataTableController::class)->except(['show']);

//  Define the route for the data table columns
Route::resource('data-tables.data-table-columns', DataTableColumnController::class)
    ->only(['store', 'update', 'destroy'])
    ->shallow();