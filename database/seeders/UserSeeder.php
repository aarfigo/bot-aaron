<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insertOrIgnore([
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mesero',
                'email' => 'mesero@example.com',
                'password' => Hash::make('password'),
                'role' => 'waiter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cocina',
                'email' => 'cocina@example.com',
                'password' => Hash::make('password'),
                'role' => 'chef',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
