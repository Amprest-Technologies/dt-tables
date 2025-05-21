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

    //  Define the route for the data table column model
    Route::name('data-tables.columns.')->prefix('data-tables/columns')->group(function () {
        Route::post('{data_table}', [DataTableColumnController::class, 'store'])->name('store');
        Route::put('{data_table}/{data_table_column}', [DataTableColumnController::class, 'update'])->name('update');
        Route::delete('{data_table}/{data_table_column}', [DataTableColumnController::class, 'destroy'])->name('destroy');
    });
});
