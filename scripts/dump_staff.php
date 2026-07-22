<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$staff = App\Models\Staff::all()->toArray();
echo json_encode($staff, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
