<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TableController extends Controller
{
    // list master tables with active order counts
    public function index()
    {
        $tables = DB::table('tables')
            ->leftJoin('tbl_order', function($join){
                $join->on('tables.number', '=', 'tbl_order.customer_table')
                    ->where('tbl_order.status', '<>', 'cleaned');
            })
            ->select(
                'tables.id','tables.number','tables.name',
                DB::raw('COUNT(tbl_order.orderID) as cnt'),
                DB::raw("SUM(CASE WHEN tbl_order.status = 'waiting' THEN 1 ELSE 0 END) as waiting_cnt"),
                DB::raw("SUM(CASE WHEN tbl_order.status = 'ready' THEN 1 ELSE 0 END) as ready_cnt"),
                DB::raw('COALESCE(SUM(tbl_order.total),0) as total_sum')
            )
            ->groupBy('tables.id','tables.number','tables.name')
            ->orderBy('tables.number')
            ->get();

        return view('staff.tables.index', compact('tables'));
    }

    // show details for a specific table number (orders and items)
    public function show($number)
    {
        $number = (int) $number;
        $table = DB::table('tables')->where('number', $number)->first();
        $orders = DB::table('tbl_order')
            ->where('customer_table', $number)
            ->where('status', '<>', 'cleaned')
            ->orderBy('order_date','desc')
            ->get();

        // fetch order items for each order
        $orderDetails = [];
        foreach($orders as $o){
            $items = DB::table('tbl_orderdetail')
                ->where('orderID', $o->orderID)
                ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                ->select('tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_menuitem.price','tbl_orderdetail.comment')
                ->get();
            $orderDetails[$o->orderID] = $items;
        }

        return view('staff.tables.show', compact('number','table','orders','orderDetails'));
    }

    // Show a charge page for all active orders on a table (similar to staff.orders.charge view)
    public function chargeView($number)
    {
        $number = (int) $number;

        $orders = DB::table('tbl_order')
            ->where('customer_table', $number)
            ->where('status', '<>', 'cleaned')
            ->orderBy('order_date','desc')
            ->get();

        if ($orders->isEmpty()){
            return redirect()->route('staff.tables.show', $number)->withErrors(['table' => 'No hay órdenes activas para esta mesa.']);
        }

        // collect items grouped by order for display
        $orderDetails = [];
        foreach($orders as $o){
            $items = DB::table('tbl_orderdetail')
                ->where('orderID', $o->orderID)
                ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                ->select('tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_menuitem.price','tbl_orderdetail.comment')
                ->get();
            $orderDetails[$o->orderID] = $items;
        }

        $total = $orders->sum('total');
        $rate = \App\Models\ExchangeRate::forDate($orders->first()->order_date ?? now()->toDateString());

        // use a compact summary for the view
        return view('staff.tables.charge', compact('number','orders','orderDetails','total','rate'));
    }

    private function flushLiveMetricsCache(): void
    {
        try{
            Cache::forget('orders_counts_live');
            Cache::forget('orders_metrics_live');
        }catch(\Exception $e){ /* silent */ }
    }

    // charge all active orders for a table: archive each order into sales_history and mark cleaned
    public function charge(Request $request, $number)
    {
        $number = (int) $number;
        $userId = auth()->id();

        $orders = DB::table('tbl_order')
            ->where('customer_table', $number)
            ->where('status', '<>', 'cleaned')
            ->get();

        if ($orders->isEmpty()){
            return redirect()->route('staff.tables.show', $number)->withErrors(['table' => 'No hay órdenes activas para esta mesa.']);
        }

        $paymentMethod = $request->input('payment_method');
        $reference = $request->input('reference');
        $nombre = $request->input('nombre');
        $cedula = $request->input('cedula');

        DB::transaction(function() use ($orders, $userId, $paymentMethod, $reference, $nombre, $cedula){
            foreach($orders as $order){
                $exists = DB::table('sales_history')->where('orderID', $order->orderID)->exists();
                $orderItems = DB::table('tbl_orderdetail')
                    ->where('orderID', $order->orderID)
                    ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                    ->select('tbl_menuitem.itemID','tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_menuitem.price')
                    ->get();

                if (! $exists){
                    DB::table('sales_history')->insert([
                        'orderID' => $order->orderID,
                        'total' => $order->total,
                        'order_date' => $order->order_date,
                        'nombre' => $nombre ?? $order->customer_name ?? null,
                        'cedula' => $cedula ?? $order->customer_cedula ?? null,
                        'cleaned_by' => $userId,
                        'created_by' => $order->created_by,
                        'payment_method' => $order->payment_method ?? $paymentMethod ?? null,
                        'reference' => $order->payment_reference ?? $reference ?? null,
                        'items' => json_encode($orderItems),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Update order record: set cleaned and attach optional customer info if provided
                $orderUpdate = [
                    'status' => 'cleaned',
                    'kitchen_cleared' => true,
                    'attended_by' => $userId,
                ];
                if (!empty($nombre)) $orderUpdate['customer_name'] = $nombre;
                if (!empty($cedula)) $orderUpdate['customer_cedula'] = $cedula;
                if (!empty($paymentMethod)) $orderUpdate['payment_method'] = $paymentMethod;
                if (!empty($reference)) $orderUpdate['payment_reference'] = $reference;

                DB::table('tbl_order')->where('orderID', $order->orderID)->update($orderUpdate);
            }
        });

        $this->flushLiveMetricsCache();

        return redirect()->route('staff.orders.index')->with('success','Mesa cobrada y órdenes archivadas');
    }

    // Quick-create a table (used by staff UI via AJAX)
    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|integer|min:1|unique:tables,number',
            'name' => 'nullable|string|max:255',
        ]);

        $id = DB::table('tables')->insertGetId([
            'number' => $data['number'],
            'name' => $data['name'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $table = DB::table('tables')->where('id', $id)->first();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'table' => $table], 201);
        }

        return redirect()->route('staff.tables.index')->with('success', 'Mesa creada');
    }
    public function update(Request $request, $number)
    {
        $currentNumber = (int) $number;
        $table = DB::table('tables')->where('number', $currentNumber)->first();
        if (! $table) {
            abort(404);
        }

        $request->validate([
            'number' => ['required','integer','min:1', \Illuminate\Validation\Rule::unique('tables','number')->ignore($currentNumber,'number')],
            'name' => ['nullable','string','max:255'],
        ]);

        $newNumber = (int) $request->input('number');
        $name = $request->input('name');

        DB::transaction(function() use ($currentNumber, $newNumber, $name) {
            DB::table('tables')->where('number', $currentNumber)->update([
                'number' => $newNumber,
                'name' => $name !== null ? trim($name) : null,
                'updated_at' => now(),
            ]);

            if ($newNumber !== $currentNumber) {
                DB::table('tbl_order')
                    ->where('customer_table', $currentNumber)
                    ->update(['customer_table' => $newNumber]);
            }
        });

        return redirect()->route('staff.tables.show', $newNumber)->with('success', 'Mesa actualizada');
    }

    public function destroy($number)
    {
        $number = (int) $number;
        $table = DB::table('tables')->where('number', $number)->first();
        if (! $table) {
            abort(404);
        }

        DB::table('tables')->where('number', $number)->delete();

        return redirect()->route('staff.tables.index')->with('success', 'Mesa borrada');
    }
}
