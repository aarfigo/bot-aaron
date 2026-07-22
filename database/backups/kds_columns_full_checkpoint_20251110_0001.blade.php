<?php /* Backup of resources/views/staff/orders/_kds_columns.blade.php - CHECKPOINT 2025-11-10 00:01 */ ?>

@php
    // columns used by KDS. By default show active workflow states. If the
    // current user is an admin or waiter, also show the 'cleaned' column so
    // those roles can see orders that were marked cleaned (archived/processed)
    // from other panels (e.g. kitchen). Chefs should not see the cleaned
    // column as their panel is only for kitchen workflow.
    // Always render only the active workflow columns for staff (no 'cleaned' column here)
    $cols = ['waiting' => 'En cola', 'preparing' => 'En proceso', 'ready' => 'Lista'];
    // default colors for the three active states
    if (! isset($colors) || ! is_array($colors)) {
        $colors = ['waiting' => 'bg-secondary', 'preparing' => 'bg-warning', 'ready' => 'bg-success'];
    } else {
        // ensure required keys exist
        foreach(['waiting','preparing','ready'] as $k) {
            if (! array_key_exists($k, $colors)) $colors[$k] = ['waiting' => 'bg-secondary','preparing' => 'bg-warning','ready' => 'bg-success'][$k];
        }
    }
    // allow caller to decide if colored headers are used (default: true)
    $useColors = $useColors ?? true;
    // mode: 'full' (three columns) or 'mini' (compact single-column list for sidebars)
    $mode = $mode ?? 'full';
    // use provided $orders if present, otherwise load recent orders
    if(!isset($orders)){
        $orders = DB::table('tbl_order')->orderBy('orderID','desc')->limit(50)->get();
    }
    $ordersCollection = collect($orders);
@endphp

<style>
    /* Make KDS columns scrollable on small screens so each column's orders are reachable */
    @media (max-width: 767.98px) {
        /* On small screens stack columns and allow them to expand vertically so all orders are visible
           Avoid internal scrolling per-column which can be confusing; let the page scroll instead. */
        #kds-board .card { margin-bottom: .75rem; }
        /* make sure both the column card and any inner card bodies are free to expand */
        #kds-board .card, #kds-board .card .card-body, #kds-board .card .card { max-height: none !important; overflow: visible !important; min-height: auto !important; }
        /* Make headers normal flow so they don't overlap content */
        #kds-board .kds-column-header { position: static !important; top: auto !important; z-index: 1; }
        /* Ensure parent containers allow page scrolling on small devices */
        html, body, #kds-board, .container, .container-fluid { height: auto !important; overflow: visible !important; }
    }
</style>

<div id="kds-board">
@if($mode === 'mini')
    <div class="kds-mini">
        @forelse($ordersCollection->sortByDesc('orderID')->take(8) as $o)
            <div class="card mb-2">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-muted">#{{ $o->orderID }} — {{ $o->order_date }}</div>
                            <div class="small text-muted">Mesa: {{ trim((string)($o->customer_table ?? '')) !== '' ? $o->customer_table : 'N/A' }}</div>
                            <div class="fw-bold">{{ number_format($o->total,2) }}</div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('staff.orders.show', $o->orderID) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                        </div>
                    </div>
                    <div class="small text-muted mt-2">
                        @php
                            $orderItems = DB::table('tbl_orderdetail')->where('orderID', $o->orderID)
                                ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                                ->select('tbl_menuitem.menuItemName','tbl_orderdetail.quantity')
                                ->get();
                        @endphp
                        @foreach($orderItems->take(2) as $it)
                            <span>{{ $it->menuItemName }} x{{ $it->quantity }}</span>@if(!$loop->last) · @endif
                        @endforeach
                        @if($orderItems->count() > 2)
                            <div class="text-muted">+{{ $orderItems->count()-2 }} more</div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-muted">No hay órdenes</div>
        @endforelse
    </div>
@else
    <div class="row">
        @foreach($cols as $statusKey => $label)
            @php
                // header class: colored (from $colors) or light (bg-light)
                $headerBgClass = $useColors ? $colors[$statusKey] : 'bg-light';
                // determine text class: for success (dark green) use white; for warning use dark
                $headerTextClass = 'text-dark';
                if($useColors){
                    if($headerBgClass === 'bg-success' || $headerBgClass === 'bg-secondary') $headerTextClass = 'text-white';
                    else $headerTextClass = 'text-dark';
                }
            @endphp
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header {{ $headerBgClass }} {{ $headerTextClass }} kds-column-header">
                        <strong>{{ $label }}</strong>
                        <span class="float-end small">{{ $ordersCollection->where('status',$statusKey)->count() }}</span>
                    </div>
                    <div class="card-body" style="min-height:28vh;">
                        @forelse($ordersCollection->where('status',$statusKey) as $o)
                            <div class="card mb-2 p-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="small text-muted">#{{ $o->orderID }} — {{ $o->order_date }}</div>
                                        <div class="small text-muted">Mesa: {{ trim((string)($o->customer_table ?? '')) !== '' ? $o->customer_table : 'N/A' }}</div>
                                        <div class="fw-bold">{{ number_format($o->total,2) }}</div>
                                    </div>
                                    <div class="text-end">
                                        <a href="{{ route('staff.orders.show', $o->orderID) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                        @php $role = optional(Auth::user())->role; @endphp
                                        @if(in_array($o->status, ['waiting','preparing']) && in_array($role, ['mesero','admin']))
                                            <a href="{{ route('staff.orders.edit', $o->orderID) }}" class="btn btn-sm btn-outline-secondary ms-1">Editar</a>
                                        @endif
                                        @if($statusKey === 'ready' && in_array($role, ['mesero','admin']))
                                            <form method="POST" action="{{ route('staff.orders.clean', $o->orderID) }}" class="d-inline ms-1" onsubmit="return confirm('Marcar esta orden como finalizada?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">Limpiar</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="small text-muted mt-2">
                                    @php
                                        $orderItems = DB::table('tbl_orderdetail')->where('orderID', $o->orderID)
                                            ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                                            ->select('tbl_menuitem.menuItemName','tbl_orderdetail.quantity')
                                            ->get();
                                    @endphp
                                    @foreach($orderItems->take(2) as $it)
                                        <span>{{ $it->menuItemName }} x{{ $it->quantity }}</span>@if(!$loop->last) · @endif
                                    @endforeach
                                    @if($orderItems->count() > 2)
                                        <div class="text-muted">+{{ $orderItems->count()-2 }} more</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">No hay órdenes</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
(function(){
    const selector = '#kds-board';
    const intervalMs = 3000;
    async function refreshKds(){
        try{
            const res = await fetch(window.location.href, { credentials: 'same-origin' });
            const text = await res.text();
            const doc = new DOMParser().parseFromString(text, 'text/html');
            const newBoard = doc.querySelector(selector);
            const oldBoard = document.querySelector(selector);
            if(!newBoard || !oldBoard) return;
            // don't replace while user is interacting inside the board
            const active = document.activeElement;
            if(oldBoard.contains(active)) return;
            oldBoard.replaceWith(newBoard);
        }catch(e){
            // swallow errors; debugging left as console message
            console.debug('KDS refresh error', e);
        }
    }
    setInterval(refreshKds, intervalMs);
})();
</script>
@endpush
