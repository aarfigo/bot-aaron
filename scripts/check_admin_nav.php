<?php
// boots the app and renders the admin navigation to confirm presence of staff order links
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('username', 'aaron')->first();
if (! $user) {
    echo "NO USER 'aaron' found\n";
    exit(1);
}

Auth::login($user);

try {
    $html = view('layouts.navigation')->render();
} catch (\Throwable $e) {
    echo "ERROR rendering view: " . $e->getMessage() . "\n";
    exit(2);
}

$found = (strpos($html, 'staff/orders/create') !== false) || (strpos($html, 'Crear pedido') !== false) || (strpos($html, 'Ordenes') !== false);

echo $found ? "OK: nav contains staff order links for admin\n" : "MISSING: nav lacks staff order links for admin\n";

// print a short snippet for inspection
$snippet = substr($html, 0, 1600);
echo "---SNIPPET START---\n" . $snippet . "\n---SNIPPET END---\n";
