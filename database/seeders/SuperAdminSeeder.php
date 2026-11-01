<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'], // Checks if this email already exists
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'), // Change this to your secure password
                'department' => 'management',
                'store_id' => 1,
                'is_super_admin' => true,
            ]
        );
    }
}
