<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Create or update an admin entry using the username as the unique key.
        // Password is hashed and can be controlled via the ADMIN_SEED_PASSWORD env var for safety.
        // default password for seeded admin accounts (change in .env if needed)
        $plain = env('ADMIN_SEED_PASSWORD', 'clave123');

        // Ensure the legacy admin table has both 'admin' and 'aaron' entries
        DB::table('tbl_admin')->updateOrInsert(
            ['username' => 'admin'],
            ['password' => Hash::make($plain)]
        );

        DB::table('tbl_admin')->updateOrInsert(
            ['username' => 'aaron'],
            ['password' => Hash::make($plain)]
        );

        // Also ensure a default user record exists for 'aaron' in the standard users table
        // Uses a predictable email so the seeder can be idempotent.
        DB::table('users')->updateOrInsert(
            ['email' => 'aaron@example.local'],
            [
                'name' => 'Aaron',
                'password' => Hash::make($plain),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Ensure role 'admin' exists in roles table
        if (DB::getSchemaBuilder()->hasTable('tbl_role')) {
            DB::table('tbl_role')->insertOrIgnore([
                ['role' => 'admin']
            ]);
        }

        // Ensure 'aaron' exists in tbl_staff with role 'admin' and active status
        if (DB::getSchemaBuilder()->hasTable('tbl_staff')) {
            DB::table('tbl_staff')->updateOrInsert(
                ['username' => 'aaron'],
                [
                    'password' => Hash::make($plain),
                    'status' => 'active',
                    'role' => 'admin'
                ]
            );
        }
    }
}
