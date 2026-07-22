<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientBoardController extends Controller
{
    public function index(Request $request)
    {
        return view('client.terminal', $this->buildPayload($request));
    }

    public function data(Request $request)
    {
        return response()->json($this->buildPayload($request));
    }

    private function buildPayload(Request $request): array
    {
        $search = trim((string) $request->input('q', ''));
        $activeStatuses = ['waiting', 'ready'];
        $today = now()->toDateString();

        $query = DB::table('tbl_order')
            ->leftJoin('tables', 'tables.number', '=', 'tbl_order.customer_table')
            ->whereIn('tbl_order.status', $activeStatuses)
            ->whereDate('tbl_order.order_date', $today)
            ->select(
                'tbl_order.orderID',
                'tbl_order.status',
                'tbl_order.order_date',
                'tbl_order.customer_name',
                'tbl_order.customer_cedula',
                'tbl_order.customer_table',
                'tables.name as table_name',
                'tables.number as table_number'
            )
            ->orderBy('tbl_order.orderID', 'asc');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('tbl_order.customer_name', 'like', '%' . $search . '%')
                    ->orWhere('tbl_order.customer_cedula', 'like', '%' . $search . '%');

                if (is_numeric($search)) {
                    $builder->orWhere('tbl_order.orderID', (int) $search)
                        ->orWhere('tbl_order.customer_table', (int) $search);
                }
            });
        }

        $orders = $query->limit(60)->get();

        $groups = [
            'waiting' => [],
            'ready' => [],
        ];

        foreach ($orders as $order) {
            $entry = [
                'orderID' => (int) $order->orderID,
                'status' => (string) ($order->status ?? 'waiting'),
                'customer_name' => (string) ($order->customer_name ?? ''),
                'customer_table' => $order->customer_table !== null ? (int) $order->customer_table : null,
                'table_name' => $order->table_name ?? null,
                'table_number' => $order->table_number !== null ? (int) $order->table_number : null,
                'order_date' => $order->order_date ?? null,
            ];

            $groups[$entry['status']][] = $entry;
        }

        $waitingCount = count($groups['waiting']);
        $readyCount = count($groups['ready']);

        return [
            'search' => $search,
            'counts' => [
                'waiting' => $waitingCount,
                'ready' => $readyCount,
                'active' => $waitingCount + $readyCount,
            ],
            'orders' => $groups,
            'lastUpdated' => now()->format('H:i:s'),
        ];
    }
}
