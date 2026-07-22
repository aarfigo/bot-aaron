<?php /* KDS tiles partial - restored from checkpoint and merged with stable polling/handlers */ ?>

@php
    // mode: 'full' or 'mini'
    $mode = $mode ?? 'full';
    if(!isset($orders)){
        // Show all active waiting/ready orders, including orders that carried over from previous days.
        $orders = DB::table('tbl_order')
            ->whereIn('status', ['waiting','ready'])
            ->where('kitchen_cleared', false)
            ->orderByRaw("CASE WHEN status = 'ready' THEN 0 ELSE 1 END")
            ->orderBy('orderID','asc')
            ->get();
    }
    // Always work with a collection and preserve any locally hidden orders via session storage
    $ordersCollection = collect($orders);

    $hiddenOrders = session('kds_hidden_orders', []);
@endphp

<style>
    /* Make KDS columns scrollable on small screens so each column's orders are reachable */
    @media (max-width: 767.98px) {
        #kds-board .card { margin-bottom: .75rem; }
        #kds-board .card, #kds-board .card .card-body, #kds-board .card .card { max-height: none !important; overflow: visible !important; min-height: auto !important; }
        #kds-board .kds-column-header { position: static !important; top: auto !important; z-index: 1; }
        html, body, #kds-board, .container, .container-fluid { height: auto !important; overflow: visible !important; }
    }
</style>

<div id="kds-board">
@if($mode === 'mini')
    <div class="kds-mini">
        @php $miniOrders = $ordersCollection->sortBy('orderID'); @endphp
        @forelse($miniOrders->take(8) as $o)
            @if(in_array($o->orderID, $hiddenOrders))
                @continue
            @endif
            @php
                $orderItems = DB::table('tbl_orderdetail')->where('orderID', $o->orderID)
                    ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                    ->select('tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_orderdetail.comment as comment')
                    ->get();
            @endphp
            <div class="card mb-2">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                    </div>
            </div>
        @empty
            <div class="text-muted">No hay órdenes</div>
        @endforelse
    </div>
@elseif($mode === 'full')
    <style>
    /* KDS tile grid styles to match screenshot */
    .kds-grid-container { max-width: 1160px; margin: 0 auto; padding: 6px 12px 30px 12px; }
    .kds-tiles-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:14px; align-items:start; justify-items:start; }
    .kds-tile { border-radius:12px; border:3px solid #111; padding:12px; box-shadow:none; color:#111; display:flex; flex-direction:column; justify-content:space-between; width:220px; max-width:100%; }
    .kds-tile { box-sizing: border-box; min-height:150px; }
    .kds-tile-head { font-weight:900; font-size:1.04rem; margin-bottom:8px; }
    .kds-tile-items { flex:1; font-size:0.95rem; line-height:1.25; }
    .kds-tile-items .line { margin-bottom:6px; }
    .kds-tile-footer { display:flex; flex-direction:column; gap:6px; align-items:flex-start; justify-content:flex-start; margin-top:10px; padding-top:6px; }
    .kds-tile-footer .kds-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
    .kds-tile-footer .kds-actions form { display:inline-flex; margin:0; }
    .kds-tile-footer .kds-actions .btn { margin:0; }
    /* total removed from tiles per UX request */
    .kds-status-row { display:flex; gap:8px; align-items:center; }
    .kds-actions { display:flex; gap:8px; align-items:center; justify-content:flex-start; }
    .kds-tile .btn-sm { padding:3px 8px; font-size:0.78rem; }
    .kds-tile-dot { width:18px; height:18px; border-radius:6px; display:inline-block; border:2px solid rgba(0,0,0,0.08); }
    .kds-status-group button { border: none; background: transparent; padding:4px 6px; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; }
    .kds-status-group button:disabled { opacity:0.5; cursor:not-allowed; }
    .kds-status-btn .kds-tile-dot { transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease; }
    .kds-status-btn:hover .kds-tile-dot, .kds-status-btn:focus .kds-tile-dot { transform: scale(1.18); box-shadow: 0 6px 14px rgba(17,17,17,0.10); }
    .kds-status-btn:focus { outline: 2px solid rgba(0,0,0,0.06); outline-offset: 3px; border-radius:8px; }
    .kds-status-group { gap:8px; display:inline-flex; }
    .kds-tile-dot.red { background:#ff8a80; }
    /* removed yellow status - keep only red and green for waiting/ready */
    .kds-tile-dot.green { background:#a5d6a7; }
    .kds-tile.state-waiting { background: #ffebee; }
    /* preparing state removed */
    .kds-tile.state-ready { background: #e8f5e9; }
    .item-comment { margin-top:3px; color: #5f6368; font-size:0.82rem; }
    @media (max-width:420px){ .kds-tiles-grid{ grid-template-columns: repeat(auto-fill, minmax(140px,1fr)); gap:10px; } .kds-tile{ width:100%; } }
    /* KDS button styling — improved contrast and accessibility */
    .kds-actions .btn{
        border-radius:10px;
        padding:6px 10px;
        box-shadow: none; /* remove heavy shadows for clarity */
        transition: transform .09s ease, box-shadow .12s ease, background-color .12s ease;
        border: 1px solid rgba(0,0,0,0.06);
        background: #fff;
        color: #222;
        font-weight:600;
        text-shadow: none;
        -webkit-font-smoothing: antialiased;
    }
    .kds-actions .btn:hover{
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }
    .kds-actions .btn:focus{ outline: 2px solid rgba(0,123,255,0.18); outline-offset: 3px; }
    .kds-actions .btn-sm{ padding:6px 10px; font-size:0.92rem; }
    .kds-actions .btn-outline-secondary{ background:#f7fbf7; border-color: rgba(0,0,0,0.04); color: #222; }
    .kds-actions .btn-warning{ background: #ffd966; color:#111; border-color: transparent; }
    .kds-actions .btn-success{ background: #69f0ae; color:#023; border-color: transparent; }
    .kds-actions .btn-outline-dark{ background:transparent; border:1px solid rgba(0,0,0,0.06); color: #111; }
    /* ensure btn-dark shows as a true dark button with high-contrast white text */
    .kds-actions .btn.btn-dark{
        background: linear-gradient(180deg,#0b0b0b,#1a1a1a) !important;
        color: #ffffff !important;
        border-color: rgba(255,255,255,0.06) !important;
        box-shadow: none !important;
    }
    /* Make sure links inside buttons inherit color and have no confusing shadows */
    .kds-actions .btn a, .kds-actions .btn i { color: inherit !important; text-decoration: none !important; }
    /* Force white text inside dark-styled KDS buttons to remove stray blue link color */
    .kds-actions .btn.btn-dark,
    .kds-actions .btn.btn-dark *,
    .kds-actions a.btn.btn-dark,
    .kds-actions a.btn.btn-dark * {
        color: #ffffff !important;
    }
    /* Ensure outline-dark stays readable (dark text on light background) */
    .kds-actions .btn.btn-outline-dark,
    .kds-actions .btn.btn-outline-dark * {
        color: #111 !important;
    }
    /* Additional overrides: convert blue link/text (text-primary) inside KDS actions to white */
    .kds-actions .text-primary, .kds-actions .btn .text-primary, .kds-actions a .text-primary {
        color: #ffffff !important;
    }
    .kds-actions a.btn, .kds-actions button.btn, .kds-actions .kds-status-btn {
        color: inherit !important;
    }
    /* Ensure SVG icons inside buttons also render white */
    .kds-actions svg, .kds-actions .icon, .kds-actions i { fill: #ffffff !important; color: #ffffff !important; }
    /* Strong override: force all text inside KDS action buttons to white to eliminate residual blue */
    .kds-actions .btn, .kds-actions .btn * {
        color: #ffffff !important;
        fill: #ffffff !important;
        stroke: #ffffff !important;
    }
    </style>

    <div class="kds-grid-container">
        <div class="kds-tiles-grid">
            @php $allOrders = $ordersCollection->sortBy('orderID'); @endphp
            @if($allOrders->isEmpty())
                <div class="text-muted">No hay órdenes</div>
            @else
                @foreach($allOrders as $o)
                    @if(in_array($o->orderID, $hiddenOrders))
                        @continue
                    @endif
                    @php
                        $orderItems = DB::table('tbl_orderdetail')->where('orderID', $o->orderID)
                            ->join('tbl_menuitem','tbl_orderdetail.itemID','=','tbl_menuitem.itemID')
                            ->select('tbl_menuitem.menuItemName','tbl_orderdetail.quantity','tbl_orderdetail.comment as comment')
                            ->get();
                        $statusKey = $o->status ?? $o->state ?? $o->order_status ?? '';
                        $statusClass = preg_replace('/[^a-z0-9_-]/i','', (string) $statusKey);
                    @endphp
                    <div class="kds-tile state-{{ $statusClass }}" data-order-id="{{ $o->orderID }}">
                        <div>
                            @php
                                $kdsTitle = trim(optional($o)->customer_name ?? '');
                                $tableLabel = null;
                                if(!empty($o->table_name)){
                                    $tableLabel = $o->table_name;
                                } elseif(isset($o->table_number) && !empty($o->table_number)){
                                    $tableLabel = 'Mesa ' . $o->table_number;
                                } elseif(!empty($o->customer_table)){
                                    $tableLabel = 'Mesa ' . $o->customer_table;
                                }
                            @endphp
                            <div class="kds-tile-head">
                                @if($kdsTitle !== '')
                                    {{ $kdsTitle }}@if($tableLabel) · <small class="text-muted">{{ $tableLabel }}</small>@endif
                                @else
                                    {{ 'Orden #' . $o->orderID }}@if($tableLabel) · <small class="text-muted">{{ $tableLabel }}</small>@endif
                                @endif
                            </div>
                            
                            @php $commentedItems = $orderItems->filter(fn($it) => !empty($it->comment))->count(); @endphp
                            <div class="kds-tile-items mt-2">
                                @foreach($orderItems->take(6) as $it)
                                    <div class="line">
                                        <div class="item-main">{{ $it->menuItemName }} <span class="text-muted">x{{ $it->quantity }}</span></div>
                                        @if(!empty($it->comment))
                                            <div class="item-comment">{{ $it->comment }}</div>
                                        @endif
                                    </div>
                                @endforeach
                                @if($orderItems->count() > 6)
                                    <div class="text-muted">+{{ $orderItems->count()-6 }} más</div>
                                @endif
                                @if($commentedItems > 0)
                                    <div class="text-muted small mt-2">Comentarios en {{ $commentedItems }} ítem{{ $commentedItems !== 1 ? 's' : '' }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="kds-tile-footer">
                            @php $role = optional(Auth::user())->role; @endphp
                            <div class="kds-status-row">
                                @if(in_array($role, ['cocina_barra','admin']))
                                    <div class="kds-status-group" role="group" aria-label="Estado">
                                        <button type="button" class="kds-status-btn" data-status="waiting" data-url="{{ route('staff.orders.status', $o->orderID) }}" title="Marcar en cola">
                                            <span class="kds-tile-dot red"></span>
                                        </button>
                                        <button type="button" class="kds-status-btn" data-status="ready" data-url="{{ route('staff.orders.status', $o->orderID) }}" title="Marcar lista">
                                            <span class="kds-tile-dot green"></span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            @php
                                $currentRoute = optional(request()->route())->getName();
                                $isOrdersView = $currentRoute === 'staff.orders.index';
                                $isKitchenView = $currentRoute === 'staff.kitchen.index' || request()->query('simple');
                                $table_num = isset($o->table_number) ? $o->table_number : ($o->customer_table ?? null);
                                $rateForOrder = \App\Models\ExchangeRate::forDate($o->order_date ?? now()->toDateString());
                                $orderTotalUsd = $o->total ?? 0;
                                $orderTotalBs = $rateForOrder ? ($orderTotalUsd * $rateForOrder) : null;
                            @endphp
                            <div class="kds-actions">
                                <a href="{{ route('staff.orders.show', $o->orderID) }}" class="btn btn-sm btn-outline-secondary" style="color:#ffffff !important;">Ver</a>
                                @if(in_array($role, ['cocina_barra','admin']))
                                    <form method="POST" action="{{ route('staff.orders.kitchen-clear', $o->orderID) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Limpiar</button>
                                    </form>
                                @endif
                                @if(($statusKey ?? '') !== 'cleaned' && in_array($role, ['mesero','admin']))
                                    <a href="{{ route('staff.orders.edit', $o->orderID) }}" class="btn btn-sm btn-dark ms-1">Editar pedido</a>
                                @endif
                                @if($statusKey === 'ready' && (in_array($role, ['mesero','admin']) ))
                                    {{-- total removed from KDS tile (do not show amount here) --}}
                                    @if(!empty($table_num))
                                        <a href="{{ route('staff.tables.charge.view', $table_num) }}" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Cobrar</a>
                                    @else
                                        <a href="{{ route('staff.orders.charge', $o->orderID) }}" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Cobrar</a>
                                    @endif
                                @endif
                                {{-- 'Limpiar' buttons removed per request: Cobrar covers same action --}}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endif

@push('scripts')
    <script>
    // Ensure clicking 'Limpiar' immediately hides the tile locally, pauses polling longer,
    // then performs server updates in background. This prevents quick re-insertions.
    document.addEventListener('click', function(e){
        const btn = e.target.closest && e.target.closest('.kds-hide-local');
        if(!btn) return;
        e.preventDefault();
        const id = btn.getAttribute('data-order-id') || btn.dataset.orderId;
        if(!id) return;
        const tile = btn.closest('.kds-tile');

        // Pause polling longer to avoid race where a concurrent poll re-inserts the tile
        if(typeof pauseKdsReplace === 'function'){
            try{ pauseKdsReplace(3000); }catch(_){}
        }

        // Persist locally immediately so poll sees the hide snapshot
        try{
            window.__kdsHiddenSet = window.__kdsHiddenSet || new Set();
            window.__kdsHiddenSet.add(String(id));
            try{ sessionStorage.setItem('__kdsHiddenIds', JSON.stringify(Array.from(window.__kdsHiddenSet))); }catch(_){ }
        }catch(err){ console.debug('kds hide set error (local)', err); }

        // Remove tile from DOM immediately with animation for instant feedback
        if(tile){ tile.style.transition = 'opacity .28s ease, transform .28s ease'; tile.style.opacity = '0'; tile.style.transform = 'scale(.96)'; setTimeout(()=>{ try{ tile.remove(); }catch(e){} }, 300); }

        // Fire-and-forget: inform server to keep session/server state in sync
        (async function(){
            try{
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
                // Primary attempt: call dedicated kitchen-clear endpoint
                try{
                    const resClear = await fetch('/staff/orders/' + encodeURIComponent(id) + '/kitchen-clear', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ origin: 'kitchen' })
                    });
                    if(!resClear || !resClear.ok){
                        console.warn('kitchen-clear request failed', resClear && resClear.status);
                        // fallback to status endpoint if kitchen-clear not available
                        try{
                            const resStatus = await fetch('/staff/orders/' + encodeURIComponent(id) + '/status', {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token || '',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ status: 'cleaned', origin: 'kitchen' })
                            });
                            if(!resStatus || !resStatus.ok) console.warn('fallback status update failed', resStatus && resStatus.status);
                            else {
                                try{ const j2 = await resStatus.json(); if(j2 && j2.counts) updateLocalCounts(j2.counts); }catch(_){}
                            }
                        }catch(fbErr){ console.debug('fallback status error', fbErr); }
                    } else {
                        try{ const j = await resClear.json(); console.debug('kitchen-clear OK', j); if(j && j.counts) updateLocalCounts(j.counts); }catch(_){ console.debug('kitchen-clear OK (no json)'); }
                    }
                }catch(err){
                    console.debug('kitchen-clear background error', err);
                }

                // Also inform session-based hide endpoint as a best-effort fallback
                try{
                    const t = token || '';
                    const resHide = await fetch('/staff/kds/hide/' + encodeURIComponent(id), { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': t, 'Accept': 'application/json' } }).catch(()=>null);
                    if(resHide && !resHide.ok) console.debug('kds/hide responded with', resHide.status);
                }catch(hErr){ console.debug('kds hide background error', hErr); }
            }catch(err){ console.debug('kds hide background error', err); }
        })();
    });
    </script>
    <script>
    // helper: update dashboard counters locally and broadcast via localStorage so other tabs update immediately
    function updateLocalCounts(counts){
        try{
            if(counts && typeof counts.waiting !== 'undefined'){
                const w = document.getElementById('cnt-waiting'); if(w) w.textContent = counts.waiting;
            }
            if(counts && typeof counts.ready !== 'undefined'){
                const r = document.getElementById('cnt-ready'); if(r) r.textContent = counts.ready;
            }
            // Also update the Order Status Bar if present
            try{ if(window.updateOSBCounts){ window.updateOSBCounts({ waiting: counts.waiting, ready: counts.ready, to_charge: counts.ready }); } }catch(_){ }
            // notify other tabs to refresh counts (storage event)
            try{ localStorage.setItem('kds_counts_update', JSON.stringify({t: Date.now(), counts: counts})); }catch(_){ }
        }catch(e){ console.debug('updateLocalCounts error', e); }
    }
    </script>
<script>
    (function(){
    const selector = '#kds-board';
    const gridSelector = '.kds-tiles-grid';
    // increase tile polling interval to reduce load; poll tiles only in 'full' mode
    const intervalMs = 2500;
    const KDS_MODE = '{{ $mode }}';
    window.__kdsUserInteracting = window.__kdsUserInteracting || false;
    let __kdsUserInteractingTimeout = null;
    function pauseKdsReplace(ms){ window.__kdsUserInteracting = true; if(__kdsUserInteractingTimeout) clearTimeout(__kdsUserInteractingTimeout); __kdsUserInteractingTimeout = setTimeout(()=>{ window.__kdsUserInteracting = false; }, ms || 2500); }
    function attachInteractionListeners(root){ try{ const nodes = root.querySelectorAll(gridSelector); nodes.forEach(n => { n.addEventListener('scroll', ()=> pauseKdsReplace(2500), { passive: true }); n.addEventListener('pointerdown', ()=> pauseKdsReplace(2500), { passive: true }); n.addEventListener('touchstart', ()=> pauseKdsReplace(2500), { passive: true }); }); }catch(e){} }

    function fetchWithTimeout(url, options, timeoutMs){
        const controller = new AbortController();
        const timeoutId = setTimeout(function(){ controller.abort(); }, timeoutMs || 8000);
        const mergedOptions = Object.assign({}, options || {}, { signal: controller.signal });
        return fetch(url, mergedOptions).finally(function(){ clearTimeout(timeoutId); });
    }

    // initialize hidden-set from sessionStorage so it survives poll fetches
    try{
        const saved = sessionStorage.getItem('__kdsHiddenIds');
        const arr = saved ? JSON.parse(saved) : [];
        window.__kdsHiddenSet = new Set(Array.isArray(arr) ? arr.map(String) : []);
    }catch(_){ window.__kdsHiddenSet = window.__kdsHiddenSet || new Set(); }

    async function pollKds(){
        try{
            if(window.__kdsUserInteracting) return;
            if(document.activeElement && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA' || document.activeElement.isContentEditable)) return;

            // Snapshot hidden IDs at start of poll to avoid races while processing new HTML
            let localHidden = new Set();
            try{
                const saved = sessionStorage.getItem('__kdsHiddenIds');
                const arr = saved ? JSON.parse(saved) : [];
                localHidden = new Set(Array.isArray(arr) ? arr.map(String) : []);
                // keep window.__kdsHiddenSet in sync
                window.__kdsHiddenSet = window.__kdsHiddenSet || new Set();
                Array.from(localHidden).forEach(id => window.__kdsHiddenSet.add(id));
            }catch(_){ localHidden = window.__kdsHiddenSet || new Set(); }

            // Fetch only the lightweight tiles fragment to reduce bandwidth and parsing cost
            const tilesUrl = '{{ route('staff.kds.tiles') }}';
            const res = await fetchWithTimeout(tilesUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } }, 8000);
            const text = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newTiles = doc.querySelectorAll('.kds-tiles-grid .kds-tile');
            if(!newTiles || newTiles.length === 0) return;
            const root = document.querySelector(selector);
            if(!root) return;
            attachInteractionListeners(root);
            const grid = root.querySelector(gridSelector);
            if(!grid) return;

            // Replace grid content with returned fragment while preserving hidden IDs
            try{
                const frag = doc.querySelector('.kds-grid-container');
                if(frag){
                    // temporarily pause transition updates while replacing
                    grid.innerHTML = frag.querySelector('.kds-tiles-grid').innerHTML;
                }
            }catch(e){ /* ignore */ }

            // Remove any tiles that were hidden by the cook during this session so they cannot briefly reappear
            try{
                grid.querySelectorAll('.kds-tile').forEach(function(et){
                    const eid = et.getAttribute('data-order-id');
                    if(eid && (localHidden.has(String(eid)) || (window.__kdsHiddenSet && window.__kdsHiddenSet.has(String(eid))))){
                        try{ et.remove(); }catch(e){}
                    }
                });
            }catch(e){ /* ignore */ }
            // Re-attach interaction listeners for newly added elements
            try{ attachInteractionListeners(grid); }catch(e){}
        }catch(e){ console.debug('kds poll error', e); }
        finally{
            setTimeout(pollKds, intervalMs);
        }
    }
    // Expose pollKds globally so other pages (e.g., index after creation) can request a fresh tiles fetch immediately
    try{ window.pollKds = pollKds; }catch(_){ }
    document.addEventListener('DOMContentLoaded', function(){ const root = document.querySelector(selector) || document; attachInteractionListeners(root); });
    // Delegated handler for status buttons (waiting / ready)
    document.addEventListener('click', async function(e){
        const btn = e.target.closest && e.target.closest('.kds-status-btn');
        if(!btn) return;
        e.preventDefault();
        const status = btn.getAttribute('data-status') || btn.dataset.status;
        const url = btn.getAttribute('data-url') || btn.dataset.url;
        if(!url || !status) return;
        const tile = btn.closest('.kds-tile');
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
        try{
            btn.disabled = true;
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            });

            if(res && (res.status === 200 || res.ok)){
                // update UI immediately
                let __kds_status_json = null;
                try{
                    __kds_status_json = await res.json().catch(()=>null);
                    if(__kds_status_json && __kds_status_json.counts) updateLocalCounts(__kds_status_json.counts);
                }catch(_){ }
                // Pause polling briefly so the poller doesn't immediately overwrite the successful change
                try{ if(typeof pauseKdsReplace === 'function') pauseKdsReplace(2500); }catch(_){ }
                try{ console.debug('kds status OK', {status: status, url: url, resp: __kds_status_json}); }catch(_){ }

                if(tile){
                    Array.from(tile.classList).forEach(c => { if(String(c).indexOf('state-') === 0) tile.classList.remove(c); });
                    tile.classList.add('state-' + String(status).replace(/[^a-z0-9_-]/gi,''));
                    const redDot = tile.querySelector('.kds-tile-dot.red');
                    const greenDot = tile.querySelector('.kds-tile-dot.green');
                    if(status === 'waiting'){
                        if(redDot) redDot.style.opacity = '1';
                        if(greenDot) greenDot.style.opacity = '0.35';
                    } else if(status === 'ready'){
                        if(redDot) redDot.style.opacity = '0.35';
                        if(greenDot) greenDot.style.opacity = '1';
                    }
                } else {
                    const row = btn.closest('tr');
                    if(row){
                        const badge = row.querySelector('.badge');
                        if(badge){
                            badge.textContent = String(status);
                            badge.classList.remove('bg-warning','bg-success');
                            if(status === 'waiting') badge.classList.add('bg-warning');
                            if(status === 'ready') badge.classList.add('bg-success');
                        }
                        try{ btn.style.transform = 'translateY(-2px)'; setTimeout(()=>{ btn.style.transform = ''; }, 220); }catch(_){ }
                    }
                }
            } else {
                // Fetch failed / returned non-OK (403, 419, 
                // etc). Log response body to help debugging, then try form fallback.
                try{
                    const bodyText = await res.clone().text().catch(()=>null);
                    console.debug('kds status update failed, response status', res && res.status, 'body:', bodyText);
                }catch(_){ }
                console.debug('kds status update failed, falling back to form POST', res && res.status);
                try{
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.style.display = 'none';
                    const inpStatus = document.createElement('input'); inpStatus.name = 'status'; inpStatus.value = status; form.appendChild(inpStatus);
                    const inpOrigin = document.createElement('input'); inpOrigin.name = 'origin'; inpOrigin.value = 'kitchen'; form.appendChild(inpOrigin);
                    const inpToken = document.createElement('input'); inpToken.type = 'hidden'; inpToken.name = '_token'; inpToken.value = token || ''; form.appendChild(inpToken);
                    document.body.appendChild(form);
                    form.submit();
                }catch(ferr){ console.debug('form fallback failed', ferr); }
            }
        }catch(err){
            console.debug('kds status error', err);
            // as a last resort, try the form fallback
            try{
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.style.display = 'none';
                const inpStatus = document.createElement('input'); inpStatus.name = 'status'; inpStatus.value = status; form.appendChild(inpStatus);
                const inpOrigin = document.createElement('input'); inpOrigin.name = 'origin'; inpOrigin.value = 'kitchen'; form.appendChild(inpOrigin);
                const inpToken = document.createElement('input'); inpToken.type = 'hidden'; inpToken.name = '_token'; inpToken.value = token || ''; form.appendChild(inpToken);
                document.body.appendChild(form);
                form.submit();
            }catch(_){ }
        }
        finally{ try{ btn.disabled = false; }catch(_){ } }
    });

    // Only poll tile diffs when in full (tile) mode. Table mode relies on counts-based refresh.
    if(KDS_MODE !== 'table'){
        setTimeout(pollKds, 0);
    }
})();
</script>
@push('scripts')
<script>
// Lightweight counts poller to trigger full board refresh across all clients when counts change
(function(){
    const countsUrl = '{{ route('staff.orders.counts') }}';
    window.__kds_last_counts = window.__kds_last_counts || null;
    let countsPolling = false;

    function fetchWithTimeout(url, options, timeoutMs){
        const controller = new AbortController();
        const timeoutId = setTimeout(function(){ controller.abort(); }, timeoutMs || 8000);
        const mergedOptions = Object.assign({}, options || {}, { signal: controller.signal });
        return fetch(url, mergedOptions).finally(function(){ clearTimeout(timeoutId); });
    }

    async function pollCounts(){
        if(countsPolling) return;
        countsPolling = true;
        try{
            // don't trigger while user interacting
            if(window.__kdsUserInteracting) return;
            const res = await fetchWithTimeout(countsUrl, { credentials: 'same-origin' }, 8000);
            if(!res || !res.ok) return;
            const json = await res.json();
            const last = window.__kds_last_counts || {};
            const changed = (String(last.waiting||'') !== String(json.waiting||'')) || (String(last.ready||'') !== String(json.ready||''));
            if(window.__kds_last_counts === null) {
                window.__kds_last_counts = json; return; // initial snapshot
            }
            if(changed){
                window.__kds_last_counts = json;
                // avoid replacing while user is typing/has focus inside board
                try{ if(document.activeElement && document.querySelector('#kitchen-board') && document.querySelector('#kitchen-board').contains(document.activeElement)) return; }catch(e){}
                // fetch current page and replace board region
                try{
                    const page = await fetch(window.location.href, { credentials: 'same-origin' });
                    const text = await page.text();
                    const doc = new DOMParser().parseFromString(text, 'text/html');
                    const newBoard = doc.querySelector('#kitchen-board');
                    const oldBoard = document.querySelector('#kitchen-board');
                    if(newBoard && oldBoard){
                        // preserve some scroll positions if present
                        const oldCols = Array.from(oldBoard.querySelectorAll('.kds-column-body'));
                        const scrollPositions = oldCols.map(c=> c ? c.scrollLeft : 0);
                        oldBoard.replaceWith(newBoard);
                        const newCols = Array.from(document.querySelectorAll('.kds-column-body'));
                        newCols.forEach((c, idx)=>{ try{ if(c && typeof scrollPositions[idx] !== 'undefined') c.scrollLeft = scrollPositions[idx]; }catch(e){} });
                    }
                }catch(e){ console.debug('kds counts refresh failed', e); }
            }
        }catch(e){ /* ignore transient errors */ }
        finally{
            countsPolling = false;
            setTimeout(pollCounts, 2500);
        }
    }
    // poll every 2.5s to reduce server load; counts will trigger full-board refresh when changed
    setTimeout(pollCounts, 0);
})();
</script>
@endpush
@endpush

<script>
// Table mode: dedicated handler for 'Limpiar' buttons.
(function(){
    document.addEventListener('click', async function(e){
        const btn = e.target.closest && e.target.closest('.kds-hide-local');
        if(!btn) return;
        // only act if we're in a table (avoid duplicating tile behavior)
        if(!document.querySelector('table')) return;
        e.preventDefault();
        const id = btn.getAttribute('data-order-id') || btn.dataset.orderId;
        if(!id) return;

        try{
            try{ if(window.__pauseKdsReplace) window.__pauseKdsReplace(3500); }catch(_){ }
            // Pause counts polling to prevent refresh re-inserting the order before server update completes
            window.__kdsUserInteracting = true;
            setTimeout(() => { window.__kdsUserInteracting = false; }, 5000);
            window.__kdsHiddenSet = window.__kdsHiddenSet || new Set();
            window.__kdsHiddenSet.add(String(id));
            try{ sessionStorage.setItem('__kdsHiddenIds', JSON.stringify(Array.from(window.__kdsHiddenSet))); }catch(_){ }

            // remove rows locally
            document.querySelectorAll('tr[data-order-id="' + id + '"]').forEach(function(r){ try{ r.remove(); }catch(e){} });

            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
            const res = await fetch('/staff/orders/' + encodeURIComponent(id) + '/kitchen-clear', {
                method: 'POST', credentials: 'same-origin', headers: {
                    'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json'
                }, body: JSON.stringify({ origin: 'kitchen' })
            });
            if(res && res.ok){ try{ const j = await res.json(); console.debug('kitchen-clear', j); }catch(_){ console.debug('kitchen-clear ok'); } }
            else { console.warn('kitchen-clear failed', res && res.status); }
        }catch(err){ console.debug('kds table clear error', err); }
    });
})();
</script>

{{-- Eliminado el bloque duplicado que registraba otro 'click' para .kds-hide-local (causaba ejecución doble y parpadeos) --}}


        @if($mode === 'table' || request()->query('layout') === 'table')
            <style>
                /* Full-width divider row between grouped orders in kitchen table mode */
                .order-divider-row td {
                    padding: 0 !important;
                    border: none !important;
                    background: #0e1114 !important;
                    height: 5px;
                    line-height: 0;
                }
                .order-divider-row td span {
                    display: block;
                    height: 5px;
                }
            </style>
            <div class="card mt-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:80px"># de Orden</th>
                                    <th>Nombre del Menú</th>
                                    <th style="width:90px">Cantidad</th>
                                    <th>Notas</th>
                                    <th style="width:120px">Estado</th>
                                    <th style="width:160px">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rows = DB::table('tbl_orderdetail as od')
                                        ->join('tbl_menuitem as mi','od.itemID','=','mi.itemID')
                                        ->join('tbl_order as o','od.orderID','=','o.orderID')
                                        ->select('o.orderID','o.status','o.customer_table','o.order_date','mi.menuItemName','mi.menuID','od.quantity', 'od.comment as note')
                                        // Restrict to today's active waiting/ready orders in table view as well
                                        ->whereDate('o.order_date', now()->toDateString())
                                        ->whereIn('o.status', ['waiting','ready'])
                                        ->where('o.kitchen_cleared', false)
                                        ->orderByRaw("CASE WHEN o.status = 'ready' THEN 0 ELSE 1 END")
                                        ->orderBy('o.orderID','asc')
                                        ->get();
                                    $grouped = $rows->groupBy('orderID');
                                @endphp
                                @foreach($grouped as $orderId => $items)
                                    @foreach($items as $idx => $it)
                                        <tr data-order-id="{{ $orderId }}">
                                            @if($idx === 0)
                                                <td rowspan="{{ $items->count() }}"># {{ $orderId }}<div class="small text-muted">{{ optional($items->first())->order_date }}</div></td>
                                            @endif
                                            <td>{{ $it->menuItemName }}</td>
                                            <td>{{ $it->quantity }}</td>
                                            <td>{{ $it->note }}</td>
                                            @if($idx === 0)
                                                <td rowspan="{{ $items->count() }}">
                                                    <span class="badge @php echo ($it->status === 'waiting') ? 'bg-warning' : 'bg-success'; @endphp">{{ $it->status }}</span>
                                                    
                                                </td>
                                                @php
                                                    $statusKey = $items->first()->status ?? '';
                                                    $role = optional(Auth::user())->role;
                                                    $table_num = $items->first()->customer_table ?? ($items->first()->table_number ?? null);
                                                @endphp
                                                <td rowspan="{{ $items->count() }}">
                                                    <a href="{{ route('staff.orders.show', $orderId) }}" class="btn btn-sm btn-outline-primary mb-1" style="color:#ffffff !important;">Ver</a>

                                                    @if(in_array($role, ['cocina_barra','admin']))
                                                        <div class="mb-2">
                                                            <button type="button" class="kds-status-btn btn btn-sm btn-outline-secondary" data-status="waiting" data-url="{{ route('staff.orders.status', $orderId) }}" title="Marcar en cola">
                                                                <span class="kds-tile-dot red"></span>
                                                            </button>
                                                            <button type="button" class="kds-status-btn btn btn-sm btn-outline-secondary" data-status="ready" data-url="{{ route('staff.orders.status', $orderId) }}" title="Marcar lista">
                                                                <span class="kds-tile-dot green"></span>
                                                            </button>
                                                            <form method="POST" action="{{ route('staff.orders.kitchen-clear', $orderId) }}" style="display:inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Limpiar</button>
                                                            </form>
                                                        </div>
                                                    @endif

                                                    @if(in_array(optional(Auth::user())->role,['mesero','admin']))
                                                        @if(($items->first()->status ?? '') !== 'cleaned' && in_array(optional(auth()->user())->role, ['mesero','admin']))
                                                            <a href="{{ route('staff.orders.edit', $orderId) }}" class="btn btn-sm btn-dark mb-1" style="color:#ffffff !important;">Editar pedido</a>
                                                        @endif
                                                    @endif

                                                    @if($statusKey === 'ready' && (in_array(optional(Auth::user())->role, ['mesero','admin'])))
                                                        @if(!empty($table_num))
                                                            <a href="{{ route('staff.tables.charge.view', $table_num) }}" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Cobrar</a>
                                                        @else
                                                            <a href="{{ route('staff.orders.charge', $orderId) }}" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Cobrar</a>
                                                        @endif
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    <tr class="order-divider-row"><td colspan="6"><span></span></td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>

    

    {{-- Manejador duplicado eliminado: el handler principal arriba ya gestiona '.kds-hide-local' y pausa el polling.
         Mantener solo el script superior evita ejecución doble y parpadeos cuando se ocultan órdenes. --}}
