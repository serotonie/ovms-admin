<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Role::create([
            'name' => 'User',
            'code' => 'user',
            'color' => 'green',
        ])->givePermissionTo(Permission::where('label', 'LIKE', '%_own%')->get())
            ->givePermissionTo(Permission::where('label', 'LIKE', '%_use%')->get());

        Role::create([
            'name' => 'Fleet Manager',
            'code' => 'fleetmanager',
            'color' => 'lime',
        ])->givePermissionTo(Role::findByName('User')->permissions)
            ->givePermissionTo(Permission::findByName('users all read'))
            ->givePermissionTo(Permission::where('label', 'LIKE', '%vehicles_all%')->get());

        Role::create([
            'name' => 'Admin',
            'code' => 'admin',
            'color' => 'orange',
        ])->givePermissionTo(Role::findByName('User')->permissions)
            ->givePermissionTo(Permission::where('label', 'LIKE', '%_all%')->get());

        Role::create([
            'name' => 'Super Admin',
            'code' => 'superadmin',
            'color' => 'red',
        ])->givePermissionTo(Permission::all());

    }
}
