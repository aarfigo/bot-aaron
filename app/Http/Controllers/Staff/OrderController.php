<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Middleware\EnsureUserHasRole;

class OrderController extends Controller
{
    private function flushLiveMetricsCache(): void
    {
        try{
            Cache::forget('orders_counts_live');
            Cache::forget('orders_metrics_live');
        }catch(\Exception $e){ /* silent */ }
    }
    public function __construct()
    {
        // Allow mesero and admin to access index/create/store/edit/update/clean
        $this->middleware(['auth', EnsureUserHasRole::class . ':mesero,admin'])->only(['index','create','store','edit','update','clean']);
        // Both mesero and cocina_barra (and admin) can view order details
        $this->middleware(['auth', EnsureUserHasRole::class . ':mesero,cocina_barra,admin'])->only(['show']);
        // Only cocina_barra (and admin) can change status
        $this->middleware(['auth', EnsureUserHasRole::class . ':cocina_barra,admin'])->only(['changeStatus']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // allow quick filtering by table via ?table=NUMBER or a typed search ?table_search=NUMBER
        $tableFilter = $request->input('table');
        $tableSearch = $request->input('table_search');
        $q = trim((string) $request->input('q', ''));

        $ordersQuery = DB::table('tbl_order')
            ->leftJoin('tables', 'tables.number', '=', 'tbl_order.customer_table')
            ->where('status', '<>', 'cleaned')
            ->select('tbl_order.*', 'tables.name as table_name', 'tables.number as table_number');

        if ($q !== '') {
            $ordersQuery->where(function($qq) use ($q) {
                $qq->where('customer_name', 'like', '%'.$q.'%')
                   ->orWhere('customer_cedula', 'like', '%'.$q.'%');
                if (is_numeric($q)) {
                    $qq->orWhere('customer_table', intval($q));
                }
            });
        } else {
            if ($tableFilter !== null && $tableFilter !== '') {
                $ordersQuery->where('customer_table', $tableFilter);
            } elseif ($tableSearch !== null && $tableSearch !== '') {
                if (is_numeric($tableSearch)) {
                    $ordersQuery->where('customer_table', intval($tableSearch));
                }
            }
        }

        $orders = $ordersQuery
            ->orderBy('orderID','asc')
            ->limit(50)
            ->get();

        // Provide master tables and counts if the tables table exists
        $allTables = [];
        if (DB::getSchemaBuilder()->hasTable('tables')) {
            $allTables = DB::table('tables')
                ->leftJoin('tbl_order', function($join){
                    $join->on('tables.number', '=', 'tbl_order.customer_table')
                        ->where('tbl_order.status', '<>', 'cleaned');
                })
                ->select('tables.id','tables.number','tables.name', DB::raw('count(tbl_order.orderID) as cnt'))
                ->groupBy('tables.id','tables.number','tables.name')
                ->orderBy('tables.number')
                ->get();
        }

        return view('staff.orders.index', compact('orders','allTables','tableFilter','tableSearch','q'));
    }

    public function update(Request $request, $id)
    {
        $order = DB::table('tbl_order')
            ->leftJoin('tables', 'tables.number', '=', 'tbl_order.customer_table')
            ->select('tbl_order.*', 'tables.name as table_name', 'tables.number as table_number')
            ->where('orderID', $id)
            ->first();
        if (!$order) abort(404);

        $request->validate([ 'table_number' => 'nullable|integer|min:1' ]);
        $items = $request->input('items', []);

        // persist table number change (if provided)
        if ($request->has('table_number')){
            DB::table('tbl_order')->where('orderID', $id)->update(['customer_table' => $request->input('table_number')]);
        }

        // persist optional customer info if provided during edit
        if ($request->has('nombre') || $request->has('cedula')) {
            DB::table('tbl_order')->where('orderID', $id)->update([
                'customer_name' => $request->input('nombre', $order->customer_name ?? null),
                'customer_cedula' => $request->input('cedula', $order->customer_cedula ?? null),
            ]);
        }

        if (empty($items)) {
            return redirect()->back()->withErrors(['items' => 'No hay artículos para añadir'])->withInput();
        }

        $resetStatusToWaiting = ($order->status === 'ready');

        DB::transaction(function() use ($items, $id, $resetStatusToWaiting){
            $existingDetails = DB::table('tbl_orderdetail')->where('orderID', $id)->get()->map(function($d){ return (array) $d; })->toArray();

            Log::info('Updating order details', ['orderID' => $id, 'user' => optional(auth()->user())->id, 'role' => optional(auth()->user())->role, 'items_incoming' => $items, 'existing_count' => count($existingDetails), 'resetStatusToWaiting' => $resetStatusToWaiting]);

            DB::table('tbl_orderdetail')->where('orderID', $id)->delete();

            $total = 0;
            foreach($items as $item){
                if (empty($item['itemID'])) continue;
                $qty = intval($item['quantity'] ?? 1);
                $resolvedComment = isset($item['comment']) ? $item['comment'] : null;
                if (($resolvedComment === null || $resolvedComment === '') && !empty($existingDetails)){
                    foreach($existingDetails as $k => $ed){
                        if (isset($ed['itemID']) && $ed['itemID'] == $item['itemID'] && !empty($ed['comment'])){
                            $resolvedComment = $ed['comment'];
                            unset($existingDetails[$k]);
                            break;
                        }
                    }
                }

                $price = DB::table('tbl_menuitem')->where('itemID', $item['itemID'])->value('price') ?? 0;
                DB::table('tbl_orderdetail')->insert([
                    'orderID' => $id,
                    'orderDetailID' => null,
                    'itemID' => $item['itemID'],
                    'quantity' => $qty,
                    'comment' => $resolvedComment ?? '',
                ]);
                $total += $price * max(1, $qty);
            }

            $updateData = ['total' => $total, 'kitchen_cleared' => false];
            if ($resetStatusToWaiting) {
                $updateData['status'] = 'waiting';
            }

            DB::table('tbl_order')->where('orderID',$id)->update($updateData);
        });

        // invalidate cached live metrics/counts so other tabs get fresh numbers immediately
        $this->flushLiveMetricsCache();

        return redirect()->route('staff.orders.show',$id)->with('success','Pedido actualizado');
    }

    public function create()
    {
        $menus = \App\Models\Menu::all();
        $items = \App\Models\MenuItem::all();
        $tables = \App\Models\Table::orderBy('number')->get();
        return view('staff.orders.create', compact('menus','items','tables'));
    }

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        $items = $data['items'] ?? [];
        if (empty($items)) {
            return redirect()->back()->withErrors(['items' => 'No hay artículos en el pedido'])->withInput();
        }

        $total = 0;
        DB::transaction(function() use ($items, &$orderId, &$total, $data, $request) {
            $tableVal = $request->input('table_number') ?? $data['table_number'] ?? $request->input('customer_table') ?? null;

            $orderId = DB::table('tbl_order')->insertGetId([
                'status' => 'waiting',
                'kitchen_cleared' => false,
                'total' => 0,
                'order_date' => now()->toDateString(),
                'created_by' => auth()->id(),
                'customer_table' => $tableVal,
                'customer_name' => $request->input('nombre') ?? null,
                'customer_cedula' => $request->input('cedula') ?? null,
                'payment_method' => $request->input('payment_method') ?? null,
                'payment_reference' => $request->input('payment_reference') ?? null,
            ]);

            foreach ($items as $item) {
                if (empty($item['itemID'])) continue;
                $price = DB::table('tbl_menuitem')->where('itemID', $item['itemID'])->value('price') ?? 0;
                $qty = intval($item['quantity'] ?? 1);
                $lineTotal = $price * max(1, $qty);
                DB::table('tbl_orderdetail')->insert([
                    'orderID' => $orderId,
                    'orderDetailID' => null,
                    'itemID' => $item['itemID'],
                    'quantity' => $qty,
                    'comment' => $item['comment'] ?? '',
                ]);
                $total += $lineTotal;
            }

            DB::table('tbl_order')->where('orderID', $orderId)->update(['total' => $total]);
        });

        // Do not archive the sale in sales_history at creation time.
        // The order should remain visible in the active workflow until it is actually cleaned/finalized.
        Log::info('Order created', ['orderId' => $orderId]);

        $this->flushLiveMetricsCache();

        // Precompute fresh counts/metrics to flash so index view can broadcast immediately (no manual refresh needed)
        try {
            $today = now()->toDateString();
            $base = DB::table('tbl_order')
                ->whereDate('order_date', $today);
            $waitingCnt = (clone $base)->where('status','waiting')->count();
            $readyCnt = (clone $base)->where('status','ready')->count();
            $activeTables = (clone $base)
                ->whereNotNull('customer_table')
                ->whereIn('status',['waiting','ready'])
                ->distinct()->count('customer_table');
            $totalTables = DB::getSchemaBuilder()->hasTable('tables') ? DB::table('tables')->count() : 0;
            $freeTables = max(0, $totalTables - $activeTables);
            session(['order_created_meta' => [
                'waiting' => (int)$waitingCnt,
                'ready' => (int)$readyCnt,
                'to_charge' => (int)$readyCnt,
                'active_tables' => (int)$activeTables,
                'free_tables' => (int)$freeTables,
                'total_tables' => (int)$totalTables,
                'order_id' => $orderId,
            ]]);
        } catch(\Exception $e) { /* silent */ }

        return redirect()->route('staff.orders.index')->with('success','Pedido creado');
    }

    public function show($id)
    {
        $order = DB::table('tbl_order')
            ->leftJoin('tables', 'tables.number', '=', 'tbl_order.customer_table')
            ->select('tbl_order.*', 'tables.name as table_name', 'tables.number as table_number')
            ->where('orderID', $id)
            ->first();
        $user = auth()->user();

        if ($user && $user->role === 'mesero') {
            if (!$order || ($order->status ?? null) === 'cleaned') {
                abort(403, 'No autorizado');
            }
        }
        $items = DB::table('tbl_orderdetail')
            ->where('orderID', $id)
            ->join('tbl_menuitem', 'tbl_orderdetail.itemID', '=', 'tbl_menuitem.itemID')
            ->select('tbl_orderdetail.*', 'tbl_menuitem.menuItemName', 'tbl_menuitem.price')
            ->get();

        return view('staff.orders.show', compact('order', 'items'));
    }

    public function edit($id)
    {
        $order = DB::table('tbl_order')->where('orderID', $id)->first();
        if (! $order) abort(404);

        $user = auth()->user();
        if ($user && $user->role === 'mesero') {
            if (($order->status ?? null) === 'cleaned') {
                abort(403, 'No autorizado');
            }
        }

        $menus = \App\Models\Menu::all();
        $items = \App\Models\MenuItem::all();
        $tables = \App\Models\Table::orderBy('number')->get();
        $orderItems = DB::table('tbl_orderdetail')
            ->where('orderID', $id)
            ->get();

        return view('staff.orders.edit', compact('order', 'items', 'menus', 'orderItems', 'tables'));
    }

    public function changeStatus(Request $request, $orderId)
    {
        $status = $request->input('status');
        $origin = $request->input('origin');
        $role = optional(auth()->user())->role;

        \Log::info('changeStatus called', ['order' => $orderId, 'status' => $status, 'origin' => $origin, 'user' => optional(auth()->user())->id ?? null, 'role' => $role]);

        $affected = null;

        if ($status === 'cleaned' && $origin === 'kitchen') {
            $affected = DB::table('tbl_order')->where('orderID', $orderId)->update([
                'kitchen_cleared' => true,
                'attended_by' => auth()->id(),
            ]);
            \Log::info('changeStatus kitchen-clear update', ['order' => $orderId, 'affected' => $affected]);
        } elseif ($status === 'cleaned' && in_array($role, ['mesero','admin'])) {
            $order = DB::table('tbl_order')->where('orderID', $orderId)->first();
            if ($order) {
                $exists = DB::table('sales_history')->where('orderID', $order->orderID)->exists();
                if (! $exists) {
                    $orderItems = DB::table('tbl_orderdetail')
                        ->where('orderID', $orderId)
                        ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                        ->select('tbl_menuitem.itemID','tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_menuitem.price')
                        ->get();

                    DB::table('sales_history')->insert([
                        'orderID' => $order->orderID,
                        'total' => $order->total,
                        'order_date' => $order->order_date,
                        'nombre' => $order->customer_name ?? request()->input('nombre') ?? null,
                        'cedula' => $order->customer_cedula ?? request()->input('cedula') ?? null,
                        'cleaned_by' => auth()->id(),
                        'created_by' => $order->created_by,
                        'payment_method' => $order->payment_method ?? request()->input('payment_method') ?? null,
                        'reference' => $order->payment_reference ?? request()->input('payment_reference') ?? null,
                        'items' => json_encode($orderItems),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $affected = DB::table('tbl_order')->where('orderID', $orderId)->update([
                    'status' => 'cleaned',
                    'kitchen_cleared' => true,
                    'attended_by' => auth()->id(),
                ]);
                \Log::info('changeStatus cleaned by mesero/admin update', ['order' => $orderId, 'affected' => $affected]);

                if (!($request->expectsJson() || $request->ajax())) {
                    return redirect()->route('staff.orders.index')->with('success','Orden finalizada y archivada en historial de ventas');
                }
            }
        } else {
            $affected = DB::table('tbl_order')->where('orderID',$orderId)->update([
                'status' => $status,
                'attended_by' => auth()->id(),
            ]);
            \Log::info('changeStatus status update', ['order' => $orderId, 'status' => $status, 'affected' => $affected]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            // cache invalidation prior to returning updated counts
            $this->flushLiveMetricsCache();
            $order = DB::table('tbl_order')->where('orderID', $orderId)->first();
            $today = now()->toDateString();
            $waiting = DB::table('tbl_order')
                ->whereDate('order_date', $today)
                ->where('status','waiting')
                ->count();
            $ready = DB::table('tbl_order')
                ->whereDate('order_date', $today)
                ->where('status','ready')
                ->count();

            return response()->json([
                'orderID' => $orderId,
                'status' => $order->status ?? $status,
                'attended_by' => $order->attended_by ?? auth()->id(),
                'counts' => ['waiting' => $waiting, 'ready' => $ready],
                'updated' => isset($affected) ? $affected : null,
            ]);
        }

        $this->flushLiveMetricsCache();

        return redirect()->back()->with('success','Estado actualizado');
    }

    /**
     * Kitchen-only quick clear: mark order as kitchen_cleared without archiving.
     * Returns JSON when requested so the client can update UI immediately.
     */
    public function kitchenClear(Request $request, $orderId)
    {
        $order = DB::table('tbl_order')->where('orderID', $orderId)->first();
        if (! $order) return response()->json(['error' => 'not found'], 404);

        try{
            $affected = DB::table('tbl_order')->where('orderID', $orderId)->update([
                'kitchen_cleared' => true,
                'attended_by' => auth()->id(),
            ]);
            \Log::info('kitchenClear updated rows', ['order' => $orderId, 'affected' => $affected, 'user' => auth()->id()]);
        }catch(\Exception $e){
            \Log::warning('kitchenClear failed', ['order' => $orderId, 'error' => $e->getMessage()]);
            return response()->json(['ok' => false], 500);
        }

        // compute current counts for all active waiting/ready orders
        $today = now()->toDateString();
        $waiting = DB::table('tbl_order')
            ->whereDate('order_date', $today)
            ->where('status','waiting')
            ->count();
        $ready = DB::table('tbl_order')
            ->whereDate('order_date', $today)
            ->where('status','ready')
            ->count();

        if ($request->expectsJson() || $request->ajax()){
            $this->flushLiveMetricsCache();
            return response()->json(['ok' => true, 'orderID' => $orderId, 'updated' => isset($affected) ? $affected : 0, 'counts' => ['waiting' => $waiting, 'ready' => $ready]]);
        }

        $this->flushLiveMetricsCache();

        return redirect()->back()->with('success','Orden marcada como limpiada por cocina');
    }

    public function clean(Request $request, $orderId)
    {
        $order = DB::table('tbl_order')->where('orderID', $orderId)->first();
        if (!$order) abort(404);

        if ($order->status !== 'ready') {
            return redirect()->back()->withErrors(['order' => 'Solo se pueden limpiar órdenes en estado "Lista"']);
        }

        $orderItems = DB::table('tbl_orderdetail')
            ->where('orderID', $orderId)
            ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
            ->select('tbl_menuitem.itemID','tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_menuitem.price')
            ->get();

        $role = optional(auth()->user())->role;
        $origin = $request->input('origin');

        if ($origin === 'kitchen') {
            DB::table('tbl_order')->where('orderID', $orderId)->update([
                'kitchen_cleared' => true,
                'attended_by' => auth()->id(),
            ]);
            $this->flushLiveMetricsCache();
            return redirect()->back()->with('success','Orden marcada como finalizada (limpiada)');
        }

        if (in_array($role, ['mesero','admin'])) {
            $exists = DB::table('sales_history')->where('orderID', $order->orderID)->exists();
            if (! $exists) {
                DB::table('sales_history')->insert([
                    'orderID' => $order->orderID,
                    'total' => $order->total,
                    'order_date' => $order->order_date,
                    'nombre' => $order->customer_name ?? $request->input('nombre') ?? null,
                    'cedula' => $order->customer_cedula ?? $request->input('cedula') ?? null,
                    'cleaned_by' => auth()->id(),
                    'created_by' => $order->created_by,
                    'payment_method' => $order->payment_method ?? $request->input('payment_method') ?? null,
                    'reference' => $order->payment_reference ?? $request->input('payment_reference') ?? null,
                    'items' => json_encode($orderItems),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

              DB::table('tbl_order')->where('orderID', $orderId)->update([
                  'status' => 'cleaned',
                  'kitchen_cleared' => true,
                  'attended_by' => auth()->id(),
              ]);

              $this->flushLiveMetricsCache();
              return redirect()->route('staff.orders.index')->with('success','Orden marcada como finalizada y archivada en historial de ventas');
        }

        DB::table('tbl_order')->where('orderID', $orderId)->update([
            'kitchen_cleared' => true,
            'attended_by' => auth()->id(),
        ]);
        $this->flushLiveMetricsCache();
        return redirect()->back()->with('success','Orden marcada como finalizada (limpiada)');
    }

    public function finalize(Request $request, $orderId)
    {
        $order = DB::table('tbl_order')->where('orderID', $orderId)->first();
        if (!$order) abort(404);

        if ($order->status !== 'ready') {
            return redirect()->back()->withErrors(['order' => 'Solo se pueden finalizar órdenes en estado "Lista"']);
        }

        $orderItems = DB::table('tbl_orderdetail')
            ->where('orderID', $orderId)
            ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
            ->select('tbl_menuitem.itemID','tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_menuitem.price')
            ->get();

        DB::table('sales_history')->insert([
            'orderID' => $order->orderID,
            'total' => $order->total,
            'order_date' => $order->order_date,
            'nombre' => $order->customer_name ?? $request->input('nombre') ?? null,
            'cedula' => $order->customer_cedula ?? $request->input('cedula') ?? null,
            'cleaned_by' => auth()->id(),
            'created_by' => $order->created_by,
            'payment_method' => $order->payment_method ?? $request->input('payment_method') ?? null,
            'reference' => $order->payment_reference ?? $request->input('payment_reference') ?? null,
            'items' => json_encode($orderItems),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tbl_order')->where('orderID', $orderId)->update([
              'status' => 'cleaned',
              'kitchen_cleared' => true,
              'attended_by' => auth()->id(),
        ]);
        $this->flushLiveMetricsCache();
        return redirect()->route('staff.orders.index')->with('success','Orden finalizada y archivada en historial de ventas');
        }

    /**
     * Show a charge page for a single order (used when order has no table)
     */
    public function charge(Request $request, $orderId)
    {
        $order = DB::table('tbl_order')->where('orderID', $orderId)->first();
        if (! $order) abort(404);

        $items = DB::table('tbl_orderdetail')
            ->where('orderID', $orderId)
            ->join('tbl_menuitem', 'tbl_orderdetail.itemID', '=', 'tbl_menuitem.itemID')
            ->select('tbl_orderdetail.*', 'tbl_menuitem.menuItemName', 'tbl_menuitem.price')
            ->get();

        $rate = \App\Models\ExchangeRate::forDate($order->order_date ?? now()->toDateString());

        return view('staff.orders.charge', compact('order','items','rate'));
    }

    /**
     * Return only the tiles fragment HTML for lightweight polling.
     */
    public function kdsTiles(Request $request)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        // Fetch recent orders and limit to reduce payload
        $orders = DB::table('tbl_order')
            ->leftJoin('tables', 'tables.number', '=', 'tbl_order.customer_table')
            ->where('status', '<>', 'cleaned')
            ->where('kitchen_cleared', false)
            ->select('tbl_order.*', 'tables.name as table_name', 'tables.number as table_number')
            ->orderBy('orderID','asc')
            ->limit(60)
            ->get();

        $ordersCollection = collect($orders);


        // Preload order items in one query to avoid N+1 inside the Blade fragment
        $orderIds = $ordersCollection->pluck('orderID')->filter()->unique()->values();
        $itemsMap = collect();
        if($orderIds->isNotEmpty()){
            $details = DB::table('tbl_orderdetail as od')
                ->join('tbl_menuitem as mi','od.itemID','=','mi.itemID')
                ->select('od.orderID','mi.menuItemName','od.quantity','od.comment')
                ->whereIn('od.orderID', $orderIds)
                ->get()
                ->groupBy('orderID');
            $itemsMap = $details; // group: orderID => collection of items
        }

        $html = view('staff.orders._kds_tiles_fragment', [
            'ordersCollection' => $ordersCollection,
            'orderItemsMap' => $itemsMap,
        ])->render();
        return response($html);
    }

    /**
     * Reinicio día: archiva y limpia órdenes residuales de días anteriores
     * que aún no fueron limpiadas en cocina/barra.
     */
    public function reinicioDia(Request $request)
    {
        $role = optional(auth()->user())->role;
        if(!in_array($role, ['cocina_barra','admin'])){
            abort(403,'No autorizado');
        }
        try {
            $cutoff = now()->toDateString();
            $oldOrders = DB::table('tbl_order')
                ->where('status','<>','cleaned')
                ->whereDate('order_date','<',$cutoff)
                ->get();

            foreach($oldOrders as $order) {
                $exists = DB::table('sales_history')->where('orderID', $order->orderID)->exists();
                if (!$exists) {
                    $orderItems = DB::table('tbl_orderdetail')
                        ->where('orderID', $order->orderID)
                        ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                        ->select('tbl_menuitem.itemID','tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_menuitem.price')
                        ->get();

                    DB::table('sales_history')->insert([
                        'orderID' => $order->orderID,
                        'total' => $order->total,
                        'order_date' => $order->order_date,
                        'nombre' => $order->customer_name ?? null,
                        'cedula' => $order->customer_cedula ?? null,
                        'cleaned_by' => auth()->id(),
                        'created_by' => $order->created_by,
                        'payment_method' => $order->payment_method ?? null,
                        'reference' => $order->payment_reference ?? null,
                        'items' => json_encode($orderItems),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $affected = DB::table('tbl_order')
                ->where('status','<>','cleaned')
                ->whereDate('order_date','<',$cutoff)
                ->update([
                    'status' => 'cleaned',
                    'kitchen_cleared' => true,
                    'attended_by' => auth()->id(),
                ]);
        } catch(\Exception $e){
            \Log::warning('reinicioDia failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error','Error al reiniciar: '.$e->getMessage());
        }
        $this->flushLiveMetricsCache();
        // Fuerza actualización inmediata de contadores via sesión/localStorage para otras pestañas
        try {
            $waiting = DB::table('tbl_order')
                ->where('status','waiting')
                ->count();
            $ready = DB::table('tbl_order')
                ->where('status','ready')
                ->count();
            // Totales de mesas para que el status bar muestre 0 / TOTAL inmediatamente
            $totalTables = DB::getSchemaBuilder()->hasTable('tables') ? (int) DB::table('tables')->count() : 0;
            $freeTables = $totalTables; // tras reinicio no hay mesas activas
            session(['order_created_meta' => [
                'waiting' => (int)$waiting,
                'ready' => (int)$ready,
                'to_charge' => (int)$ready,
                'active_tables' => 0,
                'free_tables' => (int)$freeTables,
                'total_tables' => (int)$totalTables,
                'order_id' => null,
            ]]);
        } catch(\Exception $e) { /* silent */ }
        return redirect()->route('staff.orders.index')->with('success','Reinicio día completado. Órdenes limpiadas: '.$affected);
    }

}
