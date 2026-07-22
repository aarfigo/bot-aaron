<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\Validator;

$user = User::where('username','aaron')->first();
if(!$user){ echo "aaron user not found\n"; exit(1); }

// login user
Illuminate\Support\Facades\Auth::login($user);

// pick a menu item id
$itemId = Illuminate\Support\Facades\DB::table('tbl_menuitem')->value('itemID');
if(!$itemId){ echo "no menu items found\n"; exit(2); }

$post = [
    'items' => [ [ 'itemID' => $itemId, 'quantity' => 1, 'comment' => '' ] ],
    'table_number' => 77,
];

// create base request
$base = HttpRequest::create('/staff/orders', 'POST', $post);
$storeReq = StoreOrderRequest::createFromBase($base);
$storeReq->setContainer($app);
$storeReq->setRedirector($app->make('redirect'));

// validate data using the rules from StoreOrderRequest
$rules = (new StoreOrderRequest())->rules();
$validator = Validator::make($post, $rules);
if($validator->fails()){
    echo "Validation failed:\n";
    print_r($validator->errors()->all());
    exit(3);
}
$data = $validator->validated();

// replicate the controller store DB transaction to ensure customer_table is persisted
DB::transaction(function() use ($data, &$orderId, $itemId){
    $items = $data['items'] ?? [];
    $orderId = DB::table('tbl_order')->insertGetId([
        'status' => 'waiting',
        'kitchen_cleared' => false,
        'total' => 0,
        'order_date' => now()->toDateString(),
        'created_by' => auth()->id(),
        'customer_table' => $data['table_number'] ?? null,
    ]);

    $total = 0;
    foreach ($items as $it) {
        if (empty($it['itemID'])) continue;
        $price = DB::table('tbl_menuitem')->where('itemID', $it['itemID'])->value('price') ?? 0;
        $qty = intval($it['quantity'] ?? 1);
        DB::table('tbl_orderdetail')->insert([
            'orderID' => $orderId,
            'orderDetailID' => null,
            'itemID' => $it['itemID'],
            'quantity' => $qty,
            'comment' => $it['comment'] ?? '',
        ]);
        $total += $price * max(1, $qty);
    }

    DB::table('tbl_order')->where('orderID', $orderId)->update(['total' => $total]);
});

// find last inserted order by this user and show its customer_table
$order = Illuminate\Support\Facades\DB::table('tbl_order')->where('created_by', $user->id)->orderBy('orderID','desc')->first();
if($order) print_r(['orderID'=>$order->orderID,'customer_table'=>$order->customer_table,'order_date'=>$order->order_date]);
else echo "no order created\n";
