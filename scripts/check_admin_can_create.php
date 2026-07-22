<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('username','aaron')->first();
if (! $user) {
    echo "User 'aaron' not found in users table.\n";
    exit(1);
}

// Log in as aaron for the current request
Auth::loginUsingId($user->id);

$orders = DB::table('tbl_order')->orderBy('order_date','desc')->limit(50)->get();
$html = view('staff.orders.index', compact('orders'))->render();
$has = strpos($html, 'Crear pedido') !== false;
if ($has) echo "OK: admin 'aaron' can see 'Crear pedido' button.\n";
else echo "FAIL: admin 'aaron' cannot see the create button.\n";

// Optionally show a small snippet
$pos = strpos($html, 'Crear pedido');
if ($pos !== false) {
    echo "Snippet around match:\n" . substr($html, max(0,$pos-80), 160) . "\n";
}
