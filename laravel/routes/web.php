<?php

use App\Http\Controllers\VehicleController;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Route;
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
                    ->get(),
            ]);
        })->name('dashboard');
    })
    ->controller(VehicleController::class)
    ->prefix('vehicles')
    ->name('vehicles.')
    ->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('{vehicle}', 'show')->name('show');
    });

// Route::fallback(function (){
//     return redirect('');
// });
