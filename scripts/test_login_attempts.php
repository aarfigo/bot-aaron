<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$tests = [
    ['username' => 'aaron', 'password' => 'clave123'],
    ['username' => 'maria', 'password' => 'maria'], // example staff we saw
    ['username' => 'Marin', 'password' => '1234abcd..'],
];

foreach ($tests as $t) {
    $username = $t['username'];
    $password = $t['password'];

    $ok = Auth::attempt(['username' => $username, 'password' => $password]);
    echo "Attempt users table for {$username}: " . ($ok ? 'OK' : 'FAIL') . PHP_EOL;

    $staff = App\Models\Staff::where('username', $username)->first();
    if ($staff) {
        $matches = false;
        if (!empty($staff->password)) {
            try {
                if (Hash::check($password, $staff->password)) {
                    $matches = true;
                }
            } catch (\RuntimeException $e) {
                // Hash::check may throw if stored value is not bcrypt. Fall through to other checks.
            }
            if (! $matches) {
                if (function_exists('password_verify') && @password_verify($password, $staff->password)) {
                    $matches = true;
                } elseif ($password === $staff->password) {
                    $matches = true;
                }
            }
        }
        echo "Staff row found for {$username}, password matches: " . ($matches ? 'YES' : 'NO') . PHP_EOL;
    } else {
        echo "No staff row for {$username}\n";
    }

    echo str_repeat('-', 40) . PHP_EOL;
}
