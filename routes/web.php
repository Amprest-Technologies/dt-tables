<?php

use Amprest\LaravelDT\Http\Controllers\AssetController;
use Illuminate\Support\Facades\Route;

//  Define the route for the datatable assets
Route::get('assets/{name}', AssetController::class)->name('asset.show');