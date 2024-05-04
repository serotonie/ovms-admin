<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => 'PermissionsSeeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'RolesSeeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'SuperadminSeeder',
            '--force' => true,
        ]);
    }
};
