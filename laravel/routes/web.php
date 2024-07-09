<?php

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

Route::get('vehicle_images/{vehicle}/{file_name}', function (string $vehicle, string $file_name) {
    $vhc_images_disk = Storage::disk('vehicle_images');

    return response($vhc_images_disk->get($vehicle.'/'.$file_name), 200)
        ->header('Content-Type', $vhc_images_disk->mimeType($vehicle.'/'.$file_name));
})->middleware(['auth', 'verified']);

Route::middleware(['auth', 'verified'])
    ->controller(VehicleController::class)
    ->prefix('vehicles')
    ->name('vehicles.')
    ->group(function () {
        Route::get('', 'index')->name('index');
    });

// Route::fallback(function (){
//     return redirect('');
// });
