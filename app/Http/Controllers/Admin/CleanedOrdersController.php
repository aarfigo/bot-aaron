<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CleanedOrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\EnsureUserHasRole::class . ':admin']);
    }

    public function index()
    {
        // read from sales_history table (archived sales)
        $orders = DB::table('sales_history')->orderBy('created_at','desc')->get()->map(function($o){
            $o = (array) $o;
            // determine exchange rate for the sale date (BS per USD)
            $rate = \App\Models\ExchangeRate::forDate($o['order_date'] ?? now()->toDateString());
            $o['exchange_rate'] = $rate;
            $o['total_usd'] = (float) ($o['total'] ?? 0);
            $o['total_bs'] = $rate ? round($o['total_usd'] * (float)$rate, 2) : null;
            return (object) $o;
        });

        $activePaidOrders = DB::table('tbl_order')
            ->where('status','<>','cleaned')
            ->whereNotNull('payment_method')
            ->orderBy('order_date','desc')
            ->get()
            ->map(function($o){
                $o = (array) $o;
                $o['id'] = $o['orderID'];
                $orderItems = DB::table('tbl_orderdetail')->where('orderID', $o['orderID'])->get()->map(function($detail){
                    return [
                        'itemID' => $detail->itemID,
                        'quantity' => $detail->quantity,
                        'comment' => $detail->comment,
                    ];
                })->toArray();
                $o['items'] = json_encode($orderItems);
                $rate = \App\Models\ExchangeRate::forDate($o['order_date'] ?? now()->toDateString());
                $o['exchange_rate'] = $rate;
                $o['total_usd'] = (float) ($o['total'] ?? 0);
                $o['total_bs'] = $rate ? round($o['total_usd'] * (float)$rate, 2) : null;
                $o['cleaned_by'] = null;
                return (object) $o;
            });

        return view('admin.cleaned_orders.index', compact('orders','activePaidOrders'));
    }

    public function show($id)
    {
        $sale = DB::table('sales_history')->where('id', $id)->first();
        if (!$sale) abort(404);
        $items = json_decode($sale->items, true) ?? [];
        $rate = \App\Models\ExchangeRate::forDate($sale->order_date ?? now()->toDateString());
        $sale = (array) $sale;
        $sale['exchange_rate'] = $rate;
        $sale['total_usd'] = (float) ($sale['total'] ?? 0);
        $sale['total_bs'] = $rate ? round($sale['total_usd'] * (float)$rate, 2) : null;
        return view('admin.cleaned_orders.show', ['sale' => (object)$sale, 'items' => $items]);
    }

    public function destroy($id)
    {
        // delete sales_history record only (keep original data already archived)
        DB::table('sales_history')->where('id', $id)->delete();
        return redirect()->route('admin.cleaned-orders.index')->with('success','Registro de venta eliminado');
    }

    /**
     * Render a printable invoice for a given archived sale.
     */
    public function print($id)
    {
        $sale = DB::table('sales_history')->where('id', $id)->first();
        if (!$sale) abort(404);
        $items = json_decode($sale->items, true) ?? [];
        // Build company/fiscal info from environment or config if available so the print view
        // can render the exact header (name, address, rfc, phone). These env vars can be
        // set in .env: COMPANY_NAME, COMPANY_ADDRESS (use \n for line breaks), COMPANY_RFC, COMPANY_PHONE
        $company = [
            'name' => env('COMPANY_NAME', config('app.name')),
            'address' => env('COMPANY_ADDRESS', "Direccion de la empresa\nCP: ----- COL. -----"),
            'rfc' => env('COMPANY_RFC', null),
            'phone' => env('COMPANY_PHONE', null),
        ];

        $rate = \App\Models\ExchangeRate::forDate($sale->order_date ?? now()->toDateString());
        $saleArr = (array) $sale;
        $saleArr['exchange_rate'] = $rate;
        $saleArr['total_usd'] = (float) ($saleArr['total'] ?? 0);
        $saleArr['total_bs'] = $rate ? round($saleArr['total_usd'] * (float)$rate, 2) : null;

        return view('admin.cleaned_orders.print', ['sale' => (object)$saleArr, 'items' => $items, 'company' => $company]);
    }

    /**
     * Update the payment method for a given archived sale (admin only).
     */
    public function updatePayment(Request $request, $id)
    {
        $sale = DB::table('sales_history')->where('id', $id)->first();
        if (!$sale) abort(404);

        $validated = $request->validate([
            'payment_method' => 'nullable|string|max:50',
            'nombre' => 'nullable|string|max:191',
            'cedula' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:255',
        ]);

        DB::table('sales_history')->where('id', $id)->update([
            'payment_method' => $validated['payment_method'] ?? null,
            'nombre' => $validated['nombre'] ?? null,
            'cedula' => $validated['cedula'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.cleaned-orders.show', $id)->with('success','Información de la venta actualizada');
    }
}
