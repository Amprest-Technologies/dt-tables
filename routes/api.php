<?php

use Nyumbani\DtTables\Http\Controllers\API\ButtonTriggeredController;
use Nyumbani\DtTables\Http\Controllers\API\ServerSideController;
use Illuminate\Support\Facades\Route;

//  Define the route for the button triggered
Route::post('button-triggered', ButtonTriggeredController::class)->name('button-triggered');

//  Define the routes for the server-side tables
Route::middleware(config('dt-tables.server_side.middleware', ['web']))
    ->prefix('server-side')
    ->name('server-side.')
    ->group(function () {
        Route::post('/', [ServerSideController::class, 'rows'])->name('rows');
        Route::post('options', [ServerSideController::class, 'options'])->name('options');
    });
