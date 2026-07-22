<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = $argv[1] ?? null;
if(! $id){ echo "Usage: php scripts/inspect_order_table.php <orderID>\n"; exit(1); }
$order = \Illuminate\Support\Facades\DB::table('tbl_order')->where('orderID', $id)->first();
if(! $order){ echo "Order $id not found\n"; exit(2); }
print_r([ 'orderID' => $order->orderID, 'customer_table' => $order->customer_table, 'order_date' => $order->order_date, 'total' => $order->total, 'status' => $order->status ]);
