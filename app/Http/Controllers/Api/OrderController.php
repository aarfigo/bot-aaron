<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function current()
    {
        $orders = Order::with('table')->whereIn('status',['waiting','ready'])->orderBy('orderID','asc')->limit(50)->get();
        return response()->json($orders);
    }

    public function addItem(Request $request, $orderId)
    {
        $request->validate([
            'itemID' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'comment' => 'nullable|string|max:100',
        ]);

        $detail = OrderDetail::create([
            'orderID' => $orderId,
            'itemID' => $request->itemID,
            'quantity' => $request->quantity,
            'comment' => $request->comment ?? '',
        ]);

        return response()->json(['success' => true, 'detail' => $detail], 201);
    }
}
