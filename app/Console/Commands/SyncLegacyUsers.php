<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SyncLegacyUsers extends Command
{
    protected $signature = 'sync:legacy-users';
    protected $description = 'Sincroniza usuarios legacy de tbl_admin y tbl_staff hacia la tabla users';

    public function handle()
    {
        $this->info('Iniciando sincronización de tbl_admin...');

        $admins = DB::table('tbl_admin')->get();
        foreach ($admins as $a) {
            $email = property_exists($a, 'email') && $a->email ? $a->email : ($a->username . '@example.com');
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $a->username ?? 'admin',
                    'email' => $email,
                    'password' => $this->normalizePassword($a->password),
                    'role' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->info('Sincronizando tbl_staff...');
        $staff = DB::table('tbl_staff')->get();
        foreach ($staff as $s) {
            $email = ($s->username ?? 'user') . '@example.com';
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name' => $s->username,
                    'email' => $email,
                    'password' => $this->normalizePassword($s->password),
                    // Map legacy role to current canonical role names. Default to 'mesero'.
                    // Legacy 'staff' values may exist and will be interpreted as 'mesero' elsewhere.
                    'role' => $s->role ?? 'mesero',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->info('Sincronización completada.');
        return 0;
    }

    private function normalizePassword($pwd)
    {
        if (! $pwd) {
            return Hash::make('password');
        }

        // If the password looks like a bcrypt hash, return as-is; otherwise hash it
        if (is_string($pwd) && preg_match('/^\$2[ayb]\$/', $pwd)) {
            return $pwd;
        }

        return Hash::make($pwd);
    }
}
