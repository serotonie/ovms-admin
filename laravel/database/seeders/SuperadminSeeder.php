<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::factory()->create([
            'name' => env('SUPERADMIN_NAME', 'super'),
            'password' => env('SUPERADMIN_PASSWORD', 'password'),
            'email' => env('SUPERADMIN_MAIL', 'super@example.com'),
            'email_verified_at' => now(),
        ]);

        $superadmin->assignRole('Super Admin');
    }
}
