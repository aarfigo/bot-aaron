<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$staff = App\Models\Staff::whereNull('staffName')->orWhere('staffName', '')->get();
foreach ($staff as $s) {
    $s->staffName = $s->username;
    $s->save();
    echo "Set staffName for staffID {$s->staffID} => {$s->staffName}\n";
}

echo "Done.\n"; 
