<?php

use Amprest\DtTables\Http\Controllers\AssetController;
use Amprest\DtTables\Http\Controllers\DataTableColumnController;
use Amprest\DtTables\Http\Controllers\DataTableController;
use Amprest\DtTables\Http\Controllers\DocsController;
use Amprest\DtTables\Http\Controllers\HomeController;
use Amprest\DtTables\Http\Middleware\PreventIfEnvironmentIsNotLocal;
use Illuminate\Support\Facades\Route;

//  Define the route for the datatable assets, which are static so they need no session or cookies
Route::get('assets/{name}', AssetController::class)->name('asset.show')->withoutMiddleware('web');

//  Define the middleware for the routes
Route::middleware(PreventIfEnvironmentIsNotLocal::class)->group(function () {
    //  Define the landing page of the package
    Route::get('/', HomeController::class)->name('home');

    //  Define the routes for the documentation
    Route::name('docs.')->prefix('docs')->group(function () {
        Route::get('/', [DocsController::class, 'index'])->name('index');
        Route::get('{page}', [DocsController::class, 'show'])->where('page', '[a-z0-9-]+')->name('show');
    });

    //  Define the route for the data table model
    Route::name('data-tables.')->prefix('data-tables')->group(function () {
        Route::get('/', [DataTableController::class, 'index'])->name('index');
        Route::post('/', [DataTableController::class, 'store'])->name('store');
        Route::get('{data_table}/edit', [DataTableController::class, 'edit'])->name('edit');
        Route::put('{data_table}/update', [DataTableController::class, 'update'])->name('update');
        Route::delete('{data_table}/destroy', [DataTableController::class, 'destroy'])->name('destroy');
    });

    //  Define the route for the data table column model
    Route::name('data-tables.columns.')->prefix('data-tables/columns/{data_table}')->group(function () {
        Route::post('/', [DataTableColumnController::class, 'store'])->name('store');
        Route::put('{data_table_column}', [DataTableColumnController::class, 'update'])->name('update');
        Route::delete('{data_table_column}', [DataTableColumnController::class, 'destroy'])->name('destroy');
    });
});
