<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminVehicleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
*   Admin Users Route
*/

Route::middleware(['auth', 'verified'])
    ->controller(AdminUserController::class)
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::get('invite', function () {
            return Inertia::render('Admin/Users/Invite');
        })->name('invite.create');
        Route::post('', 'invite')->name('invite.store');

        Route::get('', 'index')->name('index');
        Route::get('{user}', 'show')->name('show');

        Route::get('{user}/edit', 'edit')->name('edit');
        Route::match(['put', 'patch'], '/{user}', 'update')->name('update');

        Route::delete('{user}', 'destroy')->name('delete');
    });

/*
*   Admin Vehicles Route
*/

Route::middleware(['auth', 'verified'])
    ->controller(AdminVehicleController::class)
    ->prefix('admin/vehicles')
    ->name('admin.vehicles.')
    ->group(function () {
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');

        Route::get('', 'index')->name('index');

        Route::get('{vehicle}/edit', 'edit')->name('edit');
        Route::match(['put', 'patch'], '/{vehicle}', 'update')->name('update');

        Route::delete('{vehicle}', 'destroy')->name('delete');
    });
