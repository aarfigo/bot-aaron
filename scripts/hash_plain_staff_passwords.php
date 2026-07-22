<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$staff = App\Models\Staff::all();
foreach ($staff as $s) {
    $pw = $s->password;
    if (strpos($pw, '$2y$') === 0) continue; // already bcrypt
    // Hash the current password if it's not empty
    if (!empty($pw)) {
        $s->password = password_hash($pw, PASSWORD_BCRYPT);
        $s->save();
        echo "Hashed password for staffID {$s->staffID}\n";
    }
}

echo "Done.\n";