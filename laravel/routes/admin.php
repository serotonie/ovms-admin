<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminVehicleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

/*
*   Admin Users Route
*/

Route::middleware(['auth', 'verified', 'role:Super Admin|Admin|Fleet Manager'])
    ->controller(AdminUserController::class)
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::get('invite', function () {
            return Inertia::render('Users/Invite', [
                'roles' => Role::all('name', 'id')->sortBy('id')->pluck('name'),
            ]);
        })->middleware('permission:users all create')->name('invite.create');
        Route::post('', 'invite')->middleware('permission:users all create')->name('invite.store');

        Route::get('', 'index')->middleware('permission:users all read')->name('index');
        Route::get('{user}', 'show')->middleware('permission:users all read')->name('show');

        Route::get('{user}/edit', 'edit')->middleware('permission:users all update')->name('edit');
        Route::match(['put', 'patch'], '/{user}', 'update')->middleware('permission:users all update')->name('update');

        Route::delete('{user}', 'destroy')->middleware('permission:users all delete')->name('delete');
    });

/*
*   Admin Vehicles Route
*/

Route::middleware(['auth', 'verified', 'role:Super Admin|Admin|Fleet Manager'])
    ->controller(AdminVehicleController::class)
    ->prefix('admin/vehicles')
    ->name('admin.vehicles.')
    ->group(function () {
        Route::get('create', 'create')->middleware('permission:vehicles all create')->name('create');
        Route::post('store', 'store')->middleware('permission:vehicles all create')->name('store');

        Route::get('', 'index')->middleware('permission:vehicles all read')->name('index');

        Route::get('{vehicle}/edit', 'edit')->middleware('permission:vehicles all update')->name('edit');
        Route::match(['put', 'patch'], '/{vehicle}', 'update')->middleware('permission:vehicles all update')->name('update');

        Route::delete('{vehicle}', 'destroy')->middleware('permission:vehicles all delete')->name('delete');
    });
