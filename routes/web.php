<?php

use Nyumbani\DtTables\Http\Controllers\AssetController;
use Nyumbani\DtTables\Http\Controllers\DataTableColumnController;
use Nyumbani\DtTables\Http\Controllers\DataTableController;
use Nyumbani\DtTables\Http\Controllers\HomeController;
use Nyumbani\DtTables\Http\Middleware\PreventIfEnvironmentIsNotLocal;
use Illuminate\Support\Facades\Route;

//  Define the route for the datatable assets, which are static so they need no session or cookies
Route::get('assets/{name}', AssetController::class)->name('asset.show')->withoutMiddleware('web');

//  Define the middleware for the routes
Route::middleware(PreventIfEnvironmentIsNotLocal::class)->group(function () {
    //  Define the landing page of the package
    Route::get('/', HomeController::class)->name('home');

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
