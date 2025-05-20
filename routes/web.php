<?php

use Amprest\DtTables\Http\Controllers\AssetController;
use Amprest\DtTables\Http\Controllers\DataTableColumnController;
use Amprest\DtTables\Http\Controllers\DataTableController;
use Amprest\DtTables\Http\Middleware\PreventIfEnvironmentIsNotLocal;
use Illuminate\Support\Facades\Route;

//  Define the route for the datatable assets
Route::get('assets/{name}', AssetController::class)->name('asset.show');

//  Define the middleware for the routes
Route::middleware(PreventIfEnvironmentIsNotLocal::class)->group(function () {
    //  Define the route for the data table model
    Route::name('data-tables.')->prefix('data-tables')->group(function () {
        Route::get('/', [DataTableController::class, 'index'])->name('index');
        Route::post('/', [DataTableController::class, 'store'])->name('store');
        Route::get('{data_table}/edit', [DataTableController::class, 'edit'])->name('edit');
        Route::put('{data_table}/update', [DataTableController::class, 'update'])->name('update');
        Route::delete('{data_table}/destroy', [DataTableController::class, 'destroy'])->name('destroy');
    });

    // Route::resource('data-tables', DataTableController::class)->except(['show']);

    //  Define the route for the data table columns
    Route::resource('data-tables.data-table-columns', DataTableColumnController::class)
        ->only(['store', 'update', 'destroy'])
        ->shallow();
});
