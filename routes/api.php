<?php

use Amprest\DtTables\Http\Controllers\API\ButtonTriggeredController;
use Illuminate\Support\Facades\Route;

Route::post('button-triggered', ButtonTriggeredController::class)->name('button-triggered');