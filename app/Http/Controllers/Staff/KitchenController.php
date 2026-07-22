<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitchenController extends Controller
{
    public function index(Request $request)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    // show two columns for the kitchen: waiting (en cola) and ready (lista)
    // Simplify workflow to only these two states to reduce UI complexity.
    $statuses = ['waiting','ready'];
        $orders = DB::table('tbl_order')
            ->whereIn('status', $statuses)
            ->where('kitchen_cleared', false)
            ->orderBy('orderID','asc')
            ->get()
            ->groupBy('status')
            ->map(function($group){
                return $group->map(function($o){
                    $items = DB::table('tbl_orderdetail')
                        ->where('orderID', $o->orderID)
                        ->join('tbl_menuitem', 'tbl_orderdetail.itemID', '=', 'tbl_menuitem.itemID')
                        ->select('tbl_orderdetail.*', 'tbl_menuitem.menuItemName')
                        ->get();
                    $o->items = $items;
                    return $o;
                });
            });

        // If the kitchen panel is requested in "simple" mode, render a minimal standalone view
        if ($request->query('simple')) {
            return view('staff.kitchen.simple', ['ordersByStatus' => $orders]);
        }

        return view('staff.kitchen.index', ['ordersByStatus' => $orders]);
    }
}
