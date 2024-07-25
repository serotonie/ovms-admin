<?php

use App\Http\Controllers\TripController;
use App\Http\Controllers\VehicleController;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('', function () {
            return redirect('dashboard');
        })->name('home');

        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard', [
                'vehicles' => Vehicle::whereRelation('users', 'user_id', '=', auth()->user()->id)
                    ->orWhere('owner_id', auth()->user()->id)
                    ->orWhere('main_user_id', auth()->user()->id)
                    ->count(),
            ]);
        })->name('dashboard');
    });

Route::get('uploads/{file_name}', function (string $file_name) {
    return response(Storage::get($file_name), 200)
        ->header('Content-Type', Storage::mimeType($file_name));
})->middleware(['auth', 'verified']);

Route::middleware(['auth', 'verified'])
    ->controller(VehicleController::class)
    ->prefix('vehicles')
    ->name('vehicles.')
    ->group(function () {
        Route::get('', 'index')->name('index');
    });

Route::middleware(['auth', 'verified'])
    ->controller(TripController::class)
    ->prefix('trips')
    ->name('trips.')
    ->group(function () {
        Route::get('', 'index')->name('index');
        Route::match(['put', 'patch'], '/{trip}', 'update')->middleware('permission:users all update')->name('update');
    });

// Route::fallback(function (){
//     return redirect('');
// });
