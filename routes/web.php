<?php

use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Controllers\Client\ClientBoardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Staff\OrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
// Ensure route parameter {order} only matches numbers so literal paths like 'orders-metrics' never collide
Route::pattern('order', '[0-9]+');

// Redirect any /public/* request to the clean app path and preserve the current parent folder.
Route::get('/public', function () {
    $target = preg_replace('#/public#', '', request()->getRequestUri(), 1);
    return redirect()->to($target ?: '/');
});
Route::get('/public/{any}', function ($any) {
    $target = preg_replace('#/public#', '', request()->getRequestUri(), 1);
    return redirect()->to($target ?: '/');
})->where('any', '.*');

Route::get('/cliente', [ClientBoardController::class, 'index'])->name('client.board');
Route::get('/cliente/data', [ClientBoardController::class, 'data'])->name('client.board.data');

// Make the application start at the appropriate page.
// If the user is authenticated, send them to their panel directly.
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role ?? null;
        if (in_array($role, ['admin'])) {
            return redirect()->route('admin.dashboard');
        }
        if (in_array($role, ['mesero','cocina_barra'])) {
            return redirect()->route('staff.dashboard');
        }
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if($user){
        $role = $user->role ?? null;
        // If admin, send to admin panel
        if(in_array($role, ['admin'])){
            return redirect()->route('admin.dashboard');
        }
        // If staff roles, send to staff dashboard
        if(in_array($role, ['mesero','cocina_barra'])){
            return redirect()->route('staff.dashboard');
        }
    }
    // Fallback: show the standard dashboard view
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin area (simple role middleware)
Route::middleware(['auth', EnsureUserHasRole::class . ':admin'])->prefix('admin')->name('admin.')->group(function(){
    Route::get('/', function(){ return view('admin.index'); })->name('dashboard');
    Route::resource('menu', MenuController::class);
    Route::resource('menuitem', \App\Http\Controllers\Admin\MenuItemController::class);
    // Inline price update for menu items (AJAX)
    Route::patch('menuitem/{id}/price', [\App\Http\Controllers\Admin\MenuItemController::class, 'updatePrice'])->name('menuitem.updatePrice');
    Route::resource('staff', \App\Http\Controllers\Admin\StaffController::class);
    // Admin panel to manage cleaned/archived orders (so admin can remove them from the panel)
    Route::get('cleaned-orders', [\App\Http\Controllers\Admin\CleanedOrdersController::class, 'index'])->name('cleaned-orders.index');
    Route::get('cleaned-orders/{id}', [\App\Http\Controllers\Admin\CleanedOrdersController::class, 'show'])->name('cleaned-orders.show');
    // Printable invoice for an archived sale
    Route::get('cleaned-orders/{id}/print', [\App\Http\Controllers\Admin\CleanedOrdersController::class, 'print'])->name('cleaned-orders.print');
    Route::delete('cleaned-orders/{order}', [\App\Http\Controllers\Admin\CleanedOrdersController::class, 'destroy'])->name('cleaned-orders.destroy');
    // Admin may set or update payment method on archived sales
    Route::post('cleaned-orders/{id}/payment', [\App\Http\Controllers\Admin\CleanedOrdersController::class, 'updatePayment'])->name('cleaned-orders.payment');
    // Exchange rate (daily) management
    Route::get('exchange-rate', [\App\Http\Controllers\Admin\ExchangeRateController::class, 'index'])->name('exchange-rate.index');
    Route::post('exchange-rate', [\App\Http\Controllers\Admin\ExchangeRateController::class, 'store'])->name('exchange-rate.store');
    // Provide simple named routes for show/edit used by the view to avoid RouteNotFound
    // They currently redirect back to index; implement proper show/edit in controller if needed.
    Route::get('exchange-rate/{id}', function($id){ return redirect()->route('admin.exchange-rate.index'); })->name('exchange-rate.show');
    Route::get('exchange-rate/{id}/edit', function($id){ return redirect()->route('admin.exchange-rate.index'); })->name('exchange-rate.edit');
    
});

// Staff area
// Allow 'mesero' and 'cocina_barra' roles (and admin) to access staff routes.
Route::middleware(['auth', EnsureUserHasRole::class . ':mesero,cocina_barra,admin'])->prefix('staff')->name('staff.')->group(function(){
    Route::get('/', function(){ return view('staff.index'); })->name('dashboard');
    // Orders resource includes edit/update so waiters can add items to existing orders
    Route::resource('orders', OrderController::class)->only(['index','create','store','show','edit','update']);
    Route::get('kitchen', [\App\Http\Controllers\Staff\KitchenController::class, 'index'])->name('kitchen.index');
    // Lightweight counts endpoint for live dashboard updates (waiting / ready)
    // Use hyphen paths to avoid collision with resource route orders/{order}
    Route::get('orders-counts', function(){
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        // Cache very briefly (1s) so invalidation feels instant after writes
        $data = \Illuminate\Support\Facades\Cache::remember('orders_counts_live', 1, function(){
            $today = now()->toDateString();
            $waiting = \Illuminate\Support\Facades\DB::table('tbl_order')
                ->whereDate('order_date', $today)
                ->where('status','waiting')
                ->count();
            $ready = \Illuminate\Support\Facades\DB::table('tbl_order')
                ->whereDate('order_date', $today)
                ->where('status','ready')
                ->count();
            return ['waiting' => (int)$waiting, 'ready' => (int)$ready];
        });
        return response()->json($data);
    })->name('orders.counts');
    // Extended metrics (live status bar): waiting, ready, to_charge, active_tables, free_tables
    Route::get('orders-metrics', function(){
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        // Same brief 1s cache for metrics
        $data = \Illuminate\Support\Facades\Cache::remember('orders_metrics_live', 1, function(){
            $today = now()->toDateString();
            $base = \Illuminate\Support\Facades\DB::table('tbl_order')
                ->whereDate('order_date', $today);
            $waiting = (clone $base)->where('status','waiting')->count();
            $ready = (clone $base)->where('status','ready')->count();
            $toCharge = $ready;
            $activeTables = (clone $base)
                ->whereNotNull('customer_table')
                ->whereIn('status',['waiting','ready'])
                ->distinct()
                ->count('customer_table');
            $totalTables = 0; $freeTables = 0;
            if(\Illuminate\Support\Facades\Schema::hasTable('tables')){
                $totalTables = \Illuminate\Support\Facades\DB::table('tables')->count();
                $freeTables = max(0, $totalTables - $activeTables);
            }
            return [
                'waiting' => (int)$waiting,
                'ready' => (int)$ready,
                'to_charge' => (int)$toCharge,
                'active_tables' => (int)$activeTables,
                'free_tables' => (int)$freeTables,
                'total_tables' => (int)$totalTables,
            ];
        });
        return response()->json($data);
    })->name('orders.metrics');
    // Lightweight KDS tiles fragment (returns only the tiles HTML) to reduce polling payload
    Route::get('kds/tiles', [OrderController::class, 'kdsTiles'])->name('kds.tiles');
    // Tables management (staff quick actions)
    Route::get('tables', [\App\Http\Controllers\Staff\TableController::class, 'index'])->name('tables.index');
    Route::get('tables/{number}', [\App\Http\Controllers\Staff\TableController::class, 'show'])->name('tables.show');
    Route::post('tables', [\App\Http\Controllers\Staff\TableController::class, 'store'])->name('tables.store');
    Route::patch('tables/{number}', [\App\Http\Controllers\Staff\TableController::class, 'update'])->name('tables.update');
    Route::delete('tables/{number}', [\App\Http\Controllers\Staff\TableController::class, 'destroy'])->name('tables.destroy');
    // Show charge page for a table (GET) so staff can select payment method before finalizing
    Route::get('tables/{number}/charge', [\App\Http\Controllers\Staff\TableController::class, 'chargeView'])->name('tables.charge.view');
    Route::post('tables/{number}/charge', [\App\Http\Controllers\Staff\TableController::class, 'charge'])->name('tables.charge');
    
    // Kitchen-only: change status, finalize and clean should be performed by cocina_barra or admin
    // Cleaning (finalizing) an order should be done by the waiter or admin only
    Route::post('orders/{order}/clean', [OrderController::class, 'clean'])
        ->middleware(EnsureUserHasRole::class . ':mesero,admin')
        ->name('orders.clean');
    // Finalize (archive sale) - kitchen or admin only
    // Finalizing (saving payment / archiving) should be performed by waiter or admin
    Route::post('orders/{order}/finalize', [OrderController::class, 'finalize'])
        ->middleware(EnsureUserHasRole::class . ':mesero,admin')
        ->name('orders.finalize');
    // Page to charge a specific order (for orders without a table or quick-charge view)
    Route::get('orders/{order}/charge', [OrderController::class, 'charge'])->name('orders.charge');
    // Status change - kitchen or admin only
    Route::post('orders/{order}/status', [OrderController::class,'changeStatus'])
        ->middleware(EnsureUserHasRole::class . ':cocina_barra,admin')
        ->name('orders.status');
    // Endpoint to allow kitchen to mark an order cleared (kitchen action)
    Route::post('orders/{order}/kitchen-clear', [OrderController::class, 'kitchenClear'])
        ->middleware(EnsureUserHasRole::class . ':cocina_barra,admin')
        ->name('orders.kitchen-clear');
    // Reinicio día (limpia órdenes residuales de cocina/barra de días anteriores)
    Route::post('orders/reinicio-dia', [OrderController::class, 'reinicioDia'])
        ->middleware(EnsureUserHasRole::class . ':cocina_barra,admin')
        ->name('orders.reinicio-dia');
    // Mark an order as hidden for the current kitchen session (does NOT archive or finalize)
    Route::post('kds/hide/{order}', function(Request $request, $order){
        $hidden = session('kds_hidden_orders', []);
        $orderId = (int) $order;
        if(!in_array($orderId, $hidden)){
            $hidden[] = $orderId;
            session(['kds_hidden_orders' => $hidden]);
        }

        // Persist the hide only in the current kitchen session.
        // This avoids hiding the order permanently before it is delivered and finalized.
        // Do not update tbl_order.kitchen_cleared here so the order remains active until cleaning.
        try{
            $user = auth()->user();
            $role = $user->role ?? null;
            if(in_array($role, ['cocina_barra','admin'])){
                \Illuminate\Support\Facades\Log::info('kds.hide called; session-only hide applied', ['order' => $orderId, 'user' => $user->id ?? null, 'role' => $role]);
            }
        }catch(\Exception $e){
            \Illuminate\Support\Facades\Log::warning('kds.hide logging failed', ['order' => $orderId, 'error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    })->name('kds.hide');
});

require __DIR__.'/auth.php';
