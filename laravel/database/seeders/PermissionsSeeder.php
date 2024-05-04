<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Wijzijnweb\LaravelInertiaPermissions\App\Models\PermissionGroup;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // CRUD permission on users all
        $ressource_group = 'users';
        $sub_group = 'all';

        $this->CreateCRUDPermission($ressource_group, $sub_group);

        // Permissions on users own
        $ressource_group = 'users';
        $sub_group = 'own';
        $actions = [
            'c' => false,
            'r' => true,
            'u' => true,
            'd' => false,
            'rest' => false,
            'fD' => false,
        ];

        $this->CreateCRUDPermission($ressource_group, $sub_group, $actions);

        // CRUD permission on vehicles all
        $ressource_group = 'vehicles';
        $sub_group = 'all';

        $this->CreateCRUDPermission($ressource_group, $sub_group);

        // Permissions on vehicles owned
        $ressource_group = 'vehicles';
        $sub_group = 'own';
        $actions = [
            'c' => false,
            'r' => true,
            'u' => true,
            'd' => false,
            'rest' => false,
            'fD' => false,
        ];

        $this->CreateCRUDPermission($ressource_group, $sub_group, $actions);

        // Permissions on vehicles used
        $ressource_group = 'vehicles';
        $sub_group = 'use';
        $actions = [
            'c' => false,
            'r' => true,
            'u' => false,
            'd' => false,
            'rest' => false,
            'fD' => false,
        ];

        $this->CreateCRUDPermission($ressource_group, $sub_group, $actions);

        // CRUD permissions on ressources
        $ressource_groups = [
            //
        ];

        foreach ($ressource_groups as $ressource_group) {
            foreach (['all', 'own'] as $sub_group) {
                $this->CreateCRUDPermission($ressource_group, $sub_group);
            }

        }
    }

    private function CreateCRUDPermission(
        $ressource_group,
        $sub_group,
        $actions = [
            'c' => true,
            'r' => true,
            'u' => true,
            'd' => true,
            'rest' => true,
            'fD' => true,
        ])
    {
        $current_group = PermissionGroup::create(['name' => $ressource_group.'_'.$sub_group]);
        if ($actions['c']) {
            Permission::create([
                'name' => $ressource_group.' '.$sub_group.' create',
                'label' => $ressource_group.'_'.$sub_group.'.create',
                'permission_group_id' => $current_group->id,
            ]);
        }

        if ($actions['r']) {
            Permission::create([
                'name' => $ressource_group.' '.$sub_group.' read',
                'label' => $ressource_group.'_'.$sub_group.'.read',
                'permission_group_id' => $current_group->id,
            ]);
        }

        if ($actions['u']) {
            Permission::create([
                'name' => $ressource_group.' '.$sub_group.' update',
                'label' => $ressource_group.'_'.$sub_group.'.update',
                'permission_group_id' => $current_group->id,
            ]);
        }

        if ($actions['d']) {
            Permission::create([
                'name' => $ressource_group.' '.$sub_group.' delete',
                'label' => $ressource_group.'_'.$sub_group.'.delete',
                'permission_group_id' => $current_group->id,
            ]);
        }

        if ($actions['rest']) {
            Permission::create([
                'name' => $ressource_group.' '.$sub_group.' restore',
                'label' => $ressource_group.'_'.$sub_group.'.restore',
                'permission_group_id' => $current_group->id,
            ]);
        }

        if ($actions['fD']) {
            Permission::create([
                'name' => $ressource_group.' '.$sub_group.' forceDelete',
                'label' => $ressource_group.'_'.$sub_group.'.forceDelete',
                'permission_group_id' => $current_group->id,
            ]);
        }
    }
}
