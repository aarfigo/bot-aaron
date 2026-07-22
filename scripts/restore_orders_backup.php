<?php
// Usage: php scripts/restore_orders_backup.php <backup-file>
// Restores customer_table values from the backup JSON created by set_order_table.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$args = $argv;
array_shift($args);
if (count($args) < 1) {
    echo "Usage: php scripts/restore_orders_backup.php <backup-file>\n";
    exit(1);
}

$file = $args[0];
if (!file_exists($file)) {
    echo "Backup file not found: {$file}\n";
    exit(2);
}

$rows = json_decode(file_get_contents($file));
if (!is_array($rows) && !is_object($rows)) {
    echo "Invalid backup format\n"; exit(3);
}

$rows = (array)$rows;
if (empty($rows)) { echo "Backup contains no rows\n"; exit(0); }

// create a timestamped backup of current rows before restoring
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
$now = date('Ymd_His');
$curBackup = $backupDir . "/pre_restore_{$now}.json";
$orderIds = array_map(function($r){ return $r->orderID ?? ($r['orderID'] ?? null); }, $rows);
$current = DB::table('tbl_order')->whereIn('orderID', $orderIds)->get();
file_put_contents($curBackup, json_encode($current, JSON_PRETTY_PRINT));
echo "Created pre-restore backup: {$curBackup}\n";

$count = 0;
foreach ($rows as $r) {
    $orderID = $r->orderID ?? ($r['orderID'] ?? null);
    if (!$orderID) continue;
    $val = property_exists($r, 'customer_table') ? $r->customer_table : (is_array($r) && array_key_exists('customer_table', $r) ? $r['customer_table'] : null);
    // use update to set value (null preserved)
    DB::table('tbl_order')->where('orderID', $orderID)->update(['customer_table' => $val]);
    $count++;
}

echo "Restored {$count} orders from {$file}\n";
exit(0);
