<?php
// Render the staff orders create page as user 'aaron' and check for table_number input
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('username','aaron')->first();
if(! $user){ echo "NO USER 'aaron' found\n"; exit(1); }
Auth::login($user);

try{
    $errors = new Illuminate\Support\ViewErrorBag();
    $html = view('staff.orders.create', ['menus' => \App\Models\Menu::all(), 'items' => \App\Models\MenuItem::all(), 'errors' => $errors])->render();
}catch(\Throwable $e){
    echo "ERROR rendering create view: " . $e->getMessage() . "\n";
    exit(2);
}

if(strpos($html,'name="table_number"') !== false || strpos($html,'id="table-number"') !== false){
    echo "OK: create page contains table_number input\n";
    echo "---SNIPPET---\n" . substr($html, 0, 1200) . "\n---END---\n";
}else{
    echo "MISSING: create page lacks table_number input\n";
    echo substr($html,0,1200);
}
