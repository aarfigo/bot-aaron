<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderApiController extends Controller
{
    // POST /api/orders/{order}/items
    public function addItem(Request $request, $orderId)
    {
        $request->validate([
            'itemID' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
            'comment' => 'nullable|string|max:255',
        ]);

        $itemID = $request->input('itemID');
        $quantity = $request->input('quantity', 1);
        $comment = $request->input('comment', '');

        $price = DB::table('tbl_menuitem')->where('itemID',$itemID)->value('price');
        if ($price === null) {
            return response()->json(['error' => 'Item no encontrado'], 404);
        }

        DB::table('tbl_orderdetail')->insert([
            'orderID' => $orderId,
            'orderDetailID' => null,
            'itemID' => $itemID,
            'quantity' => $quantity,
            'comment' => $comment,
        ]);

        // update total (simple recalculation)
        $total = DB::table('tbl_orderdetail')
            ->join('tbl_menuitem','tbl_orderdetail.itemID','tbl_menuitem.itemID')
            ->where('tbl_orderdetail.orderID',$orderId)
            ->sum(DB::raw('tbl_menuitem.price * tbl_orderdetail.quantity'));

        DB::table('tbl_order')->where('orderID',$orderId)->update(['total' => $total]);

        return response()->json(['success' => true, 'total' => $total]);
    }
}
