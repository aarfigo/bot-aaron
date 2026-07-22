<?php
// Usage:
// php scripts/set_order_table.php <orderID> <table_number>
// php scripts/set_order_table.php --all-empty <table_number>

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$args = $argv;
array_shift($args); // drop script name

if (count($args) === 0) {
    echo "Usage:\n  php scripts/set_order_table.php <orderID> <table_number>\n  php scripts/set_order_table.php --all-empty <table_number>\n";
    exit(1);
}

if ($args[0] === '--all-empty') {
    if (!isset($args[1])) { echo "Missing table_number for --all-empty\n"; exit(2); }
    $table = trim($args[1]);
    $rows = DB::table('tbl_order')->whereNull('customer_table')->orWhere('customer_table','')->get();
    if ($rows->isEmpty()) { echo "No orders with empty customer_table found.\n"; exit(0); }
    $ids = $rows->pluck('orderID')->all();
    $backupDir = __DIR__ . '/backups';
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
    $timestamp = date('Ymd_His');
    $backupFile = $backupDir . "/orders_backup_{$timestamp}.json";
    file_put_contents($backupFile, json_encode($rows, JSON_PRETTY_PRINT));
    $updated = DB::table('tbl_order')->whereIn('orderID', $ids)->update(['customer_table' => $table]);
    echo "Backed up " . count($ids) . " rows to {$backupFile}\n";
    echo "Updated {$updated} rows to customer_table = {$table}\n";
    exit(0);
}

// single update
if (count($args) < 2) { echo "Require <orderID> <table_number>\n"; exit(3); }
$orderId = intval($args[0]);
$table = trim($args[1]);

$row = DB::table('tbl_order')->where('orderID', $orderId)->first();
if (!$row) { echo "Order {$orderId} not found\n"; exit(4); }

$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
$timestamp = date('Ymd_His');
$backupFile = $backupDir . "/order_{$orderId}_backup_{$timestamp}.json";
file_put_contents($backupFile, json_encode($row, JSON_PRETTY_PRINT));

echo "Order {$orderId} current customer_table: '" . ($row->customer_table ?? '') . "'\n";
$updated = DB::table('tbl_order')->where('orderID', $orderId)->update(['customer_table' => $table]);
if ($updated) echo "Updated order {$orderId} -> customer_table = {$table}\n";
else echo "No update performed (maybe same value).\n";
echo "Backup saved to {$backupFile}\n";

exit(0);
