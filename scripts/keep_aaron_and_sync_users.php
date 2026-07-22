<?php
// Backup DB, delete all users except 'aaron', recreate users from tbl_staff using staff passwords (if present), then run test script.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "Starting: keep only 'aaron' in users and sync from tbl_staff\n";

// 1) Backup SQLite DB if using sqlite
$config = require __DIR__ . '/../config/database.php';
define('APP_BASE', __DIR__ . '/..');
$default = $config['default'] ?? 'sqlite';
$backupPath = null;
if ($default === 'sqlite') {
    $dbPath = env('DB_DATABASE', database_path('database.sqlite'));
    if (! file_exists($dbPath)) {
        echo "SQLite DB not found at {$dbPath}\n";
    } else {
        $ts = date('Ymd-His');
        $dir = APP_BASE . '/database/backups';
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        $backupPath = $dir . "/database.sqlite." . $ts . ".bak";
        copy($dbPath, $backupPath);
        echo "Backup created: {$backupPath}\n";
    }
} else {
    echo "Non-sqlite DB configured ({$default}), skipping file copy backup. Make sure you have a DB dump.\n";
}

// 2) Delete users except 'aaron'
$keep = 'aaron';
$usersBefore = DB::table('users')->count();
$deleted = DB::table('users')->where('username', '!=', $keep)->delete();
$usersAfter = DB::table('users')->count();

echo "Users before: {$usersBefore}, deleted: {$deleted}, users after: {$usersAfter}\n";

// 3) Recreate users from tbl_staff
$created = 0;
if (DB::getSchemaBuilder()->hasTable('tbl_staff')) {
    $staffRows = DB::table('tbl_staff')->get();
    foreach ($staffRows as $s) {
        $username = $s->username ?? null;
        if (! $username) continue;
        if ($username === $keep) continue; // skip aaron if somehow present in tbl_staff

        // determine email
        $email = (str_contains($username, '@')) ? $username : ($username . '@staff.local');

        // try to use staff password if exists; if empty, generate random
        $plaintextPassword = null;
        $pw = $s->password ?? null;
        // if pw looks like bcrypt ($2y$) assume it's hashed and we'll copy the hash directly
        if ($pw && Str::startsWith($pw, '$2y$')) {
            $passwordHash = $pw;
        } elseif ($pw) {
            // assume plain text, hash it
            $passwordHash = Hash::make($pw);
            $plaintextPassword = $pw;
        } else {
            // no password found, generate a random one
            $plaintextPassword = Str::random(12);
            $passwordHash = Hash::make($plaintextPassword);
        }

        // upsert into users
        $now = now();
        DB::table('users')->updateOrInsert(
            ['username' => $username],
            [
                'name' => $s->staffName ?? $username,
                'email' => $email,
                'username' => $username,
                'password' => $passwordHash,
                'role' => strtolower(trim($s->role ?? '')) ?: 'mesero',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        $created++;
        if ($plaintextPassword) {
            echo "Created user {$username} with TEMP password: {$plaintextPassword}\n";
        } else {
            echo "Created user {$username} with existing hashed password from tbl_staff\n";
        }
    }
    echo "Recreated {$created} users from tbl_staff\n";
} else {
    echo "tbl_staff table not present; skipped recreation.\n";
}

// 4) Ensure 'aaron' exists and report
$aaron = DB::table('users')->where('username', $keep)->first();
if ($aaron) {
    echo "Aaron present: id={$aaron->id}, username={$aaron->username}, email={$aaron->email}\n";
} else {
    echo "Aaron not found in users table. You may need to run scripts/create_aaron_admin.php\n";
}

// 5) Run test_login_attempts.php to verify logins
echo "Running login checks...\n";
passthru('php "' . __DIR__ . '/test_login_attempts.php"', $rt);
if ($rt === 0) echo "Login checks completed.\n"; else echo "Login checks returned code {$rt}\n";

echo "Done.\n";
