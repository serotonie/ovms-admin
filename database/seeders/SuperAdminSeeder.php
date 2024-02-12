<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = User::create([
            'name'      => env('SUPER_ADMIN_NAME', 'Super'),
            'email'     => env('SUPER_ADMIN_MAIL', 'admin@example.com'),
            'password'  => Hash::make(env('SUPER_ADMIN_PASSWORD', 'SuperPassword1234')),
        ]);
    }
}
