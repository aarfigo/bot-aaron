<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('username','aaron')->first();
if(! $user){ echo "NO USER 'aaron' found\n"; exit(1); }
Auth::login($user);

$id = $argv[1] ?? 6;
$order = \Illuminate\Support\Facades\DB::table('tbl_order')->where('orderID',$id)->first();
if(! $order){ echo "Order $id not found\n"; exit(1); }
 $items = \Illuminate\Support\Facades\DB::table('tbl_orderdetail')
    ->where('orderID',$id)
    ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
    ->select('tbl_orderdetail.*','tbl_menuitem.menuItemName','tbl_menuitem.price')
    ->get();

$html = view('staff.orders.show', compact('order','items'));
// render and print small snippet
try{ echo $html->render(); }catch(\Throwable $e){ echo "ERR: ".$e->getMessage()."\n"; }
