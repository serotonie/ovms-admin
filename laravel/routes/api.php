<?php

use App\Http\Controllers\Api\TripIndexController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.basic')
    ->controller(TripIndexController::class)
    ->prefix('trips')
    ->name('api.trips.')
    ->group(function () {
        Route::get('', '__invoke')->name('index');
    });
