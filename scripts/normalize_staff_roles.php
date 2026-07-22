<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Str;

$map = [
    'waiter' => 'mesero',
    'chef' => 'cocina_barra',
    'staff' => 'mesero',
    'Mesero' => 'mesero',
    'Chef' => 'cocina_barra',
    'Cocina' => 'cocina_barra',
    'cocina' => 'cocina_barra',
];

$staff = App\Models\Staff::all();
foreach ($staff as $s) {
    $original = $s->role;
    $lower = Str::lower($original);
    if (isset($map[$original])) {
        $new = $map[$original];
    } elseif (isset($map[ucfirst($lower)])) {
        $new = $map[ucfirst($lower)];
    } else {
        // Fallback: normalize known English -> Spanish
        if ($lower === 'waiter') $new = 'mesero';
        elseif ($lower === 'chef') $new = 'cocina_barra';
        else $new = $lower;
    }
    if ($new !== $original) {
        $s->role = $new;
        $s->save();
        echo "Updated staffID {$s->staffID}: '{$original}' -> '{$new}'\n";
    }
}

echo "Done.\n";
