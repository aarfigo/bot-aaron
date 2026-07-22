<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('tbl_order')->whereNull('customer_table')->orWhere('customer_table','')->orderBy('orderID','desc')->get();
$count = $rows->count();
echo "Orders with empty customer_table: {$count}\n";
if ($count > 0) {
    $ids = $rows->pluck('orderID')->slice(0,20)->all();
    echo "Sample orderIDs: " . implode(',', $ids) . "\n";
}
exit(0);
