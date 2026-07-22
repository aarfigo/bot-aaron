<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "Backup then delete users except 'aaron'\n";

// backup
$config = require __DIR__ . '/../config/database.php';
$default = $config['default'] ?? 'sqlite';
$backupPath = null;
if ($default === 'sqlite') {
    $dbPath = env('DB_DATABASE', database_path('database.sqlite'));
    $ts = date('Ymd-His');
    $dir = __DIR__ . '/../database/backups';
    if (! is_dir($dir)) mkdir($dir, 0755, true);
    $backupPath = $dir . "/database.sqlite." . $ts . ".predelete.bak";
    if (file_exists($dbPath)) copy($dbPath, $backupPath);
    echo "Backup created: {$backupPath}\n";
} else {
    echo "Non-sqlite DB configured ({$default}), skipping file copy backup.\n";
}

// delete where username is null OR username != 'aaron'
$keep = 'aaron';
$usersBefore = DB::table('users')->count();
$deleted = DB::table('users')->where(function($q) use ($keep) {
    $q->whereNull('username')->orWhere('username', '!=', $keep);
})->delete();
$usersAfter = DB::table('users')->count();

echo "Users before: {$usersBefore}, deleted: {$deleted}, users after: {$usersAfter}\n";

// list remaining users
$rows = DB::table('users')->select('id','username','email','role')->get();
foreach ($rows as $r) {
    echo "- id={$r->id} username={$r->username} email={$r->email} role={$r->role}\n";
}

// run login checks
echo "Running login checks...\n";
passthru('php "' . __DIR__ . '/test_login_attempts.php"', $rt);
if ($rt === 0) echo "Login checks completed.\n"; else echo "Login checks returned code {$rt}\n";

echo "Done.\n";
