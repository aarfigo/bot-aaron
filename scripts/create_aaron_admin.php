<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$username = 'aaron';
$password = 'clave123';
$name = 'Aaron Admin';
$email = 'aaron.admin@example.com';

// Create or update in users table (idempotent)
DB::table('users')->updateOrInsert(
    ['username' => $username],
    [
        'name' => $name,
        'email' => $email,
        'username' => $username,
        'password' => Hash::make($password),
        'role' => 'admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

echo "User 'aaron' created or updated in users table.\n";

// Also ensure tbl_admin entry exists (legacy admin table)
if (DB::getSchemaBuilder()->hasTable('tbl_admin')) {
    DB::table('tbl_admin')->updateOrInsert(
        ['username' => $username],
        ['password' => Hash::make($password)]
    );
    echo "Admin record ensured in tbl_admin.\n";
} else {
    echo "tbl_admin table not present; skipped legacy admin insertion.\n";
}

// Verify
$user = DB::table('users')->where('username', $username)->first();
if ($user) {
    echo json_encode([
        'id' => $user->id,
        'username' => $user->username,
        'email' => $user->email,
        'role' => $user->role,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
