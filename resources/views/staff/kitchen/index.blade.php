@extends('layouts.app')

@section('content')
<div id="kitchen-board" class="container-fluid kds-dense">
    <h1 class="mb-3">Panel Cocina</h1>

    <div class="row">
        <style>
            /* Board background: remove dark color so page uses default (light) background */
            #kitchen-board { background: transparent; padding: 22px 0; }
            .kds-column { background: transparent; }

            /* Center the inner board and keep consistent max width for symmetry */
            .container-fluid > .row { display: flex; flex-direction: column; gap: 1.6rem; align-items: stretch; padding: 0 18px; max-width: 1280px; margin: 0 auto; }
            .kds-column { flex: none; width: 100%; max-width: 100%; min-width: 0; border-left: none; }

            /* Column header styling for light backgrounds */
            .kds-column-header { font-size: 1rem; padding: .4rem .85rem; line-height:1; background: rgba(0,0,0,0.04); color:#0b2b40; text-align:center; border-radius:8px; border-bottom: 2px solid rgba(0,0,0,0.06); font-weight:900 }
            .kds-column-header strong { letter-spacing: 0.6px }

            /* Column body: horizontal scroller with inline cards (accumulate horizontally) */
            .kds-column { position:relative; padding-top: 56px; }
            .kds-column-body { display:flex; flex-direction:row; flex-wrap:nowrap; gap: 14px; padding: 12px 24px; overflow-x:auto; overflow-y:hidden; align-items:flex-start; scroll-snap-type: x mandatory; }
            .kds-column-body:before{ content:''; flex:0 0 8px }
            .kds-column-body::-webkit-scrollbar { height:10px }
            .kds-column-body::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.22); border-radius:6px }

            /* scroll controls */
            .kds-scroll-left, .kds-scroll-right { position:absolute; top:12px; width:40px; height:40px; border-radius:8px; border:0; background: rgba(0,0,0,0.06); color:#0b2b40; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:50; opacity:0.96; transition: transform .12s ease }
            .kds-scroll-left { left:10px }
            .kds-scroll-right { right:10px }
            .kds-scroll-left:hover, .kds-scroll-right:hover { background: rgba(0,0,0,0.12); transform: translateY(-2px) }

            /* cards snap and don't shrink - ensure they sit inline */
            .kds-column-body .kds-card { scroll-snap-align: start; flex: 0 0 auto; margin-bottom:0; transition: transform .18s ease, box-shadow .18s ease }
            /* keep header space reserved so arrows don't overlap cards */
            .kds-column-header { padding-top: 12px; padding-bottom: 8px }

            /* Cards: white tiles with radius and soft shadow */
            .kds-card {
                display:flex;
                flex-direction:column;
                background: #ffffff;
                border-radius: 10px;
                border: 1px solid rgba(0,0,0,0.08);
                padding: 12px 12px 46px 12px;
                box-shadow: 0 8px 18px rgba(0,0,0,0.18);
                font-size: 1rem;
                color: #0b0b0b;
                position: relative;
                width: 220px;
                height: auto; min-height: 108px;
                max-width: 100%;
                transform: translateY(0);
            }
            .kds-card:hover { transform: translateY(-8px); box-shadow: 0 18px 34px rgba(0,0,0,0.28) }

            /* Small meta row above the main header inside card */
            .kds-card .kds-meta { font-size: .86rem; color: rgba(0,0,0,0.6); margin-bottom: 6px; display:flex; justify-content:space-between }

            /* pin decoration top-left */
            .kds-card .kds-pin { position: absolute; left: 12px; top: -8px; transform: rotate(-10deg); font-size: 16px; }

            /* circular dispatch button bottom-right */
            .kds-dispatch { position: absolute; right: 10px; bottom: 10px; width: 38px; height:38px; border-radius:50%; border: none; background: #1464c7; color: #fff; display:flex; align-items:center; justify-content:center; box-shadow: 0 8px 18px rgba(0,0,0,0.2); cursor:pointer; font-weight:800; font-size:0.95rem }

            .kds-card-head { display:flex; justify-content:space-between; align-items:flex-start; padding-bottom:6px; }
            .kds-card-head .small.text-muted { font-size: .95rem; color: rgba(0,0,0,0.58); }
            .kds-card-head .fw-bold { font-size: 1.18rem; font-weight:900; margin-top:2px; color: #0b0b0b; }

            .kds-card-body { padding: 6px 0 0 0; flex:1; display:flex; flex-direction:column; gap:6px; overflow:visible; }
            .kds-item { display:flex; justify-content:space-between; align-items:flex-start; padding:4px 0; margin:0; }
            .kds-item-left .fw-bold { font-size: 1rem; font-weight:800; }
            .kds-item-comment { font-size:0.92rem; color: rgba(0,0,0,0.75); margin-top:3px; display:block }
            .kds-item-right { font-size:0.95rem; color: rgba(0,0,0,0.55); margin-left:.5rem; }

            /* Footer: larger status button styled like the mock */
            .kds-card-footer { padding-top:6px; display:flex; gap:.4rem; justify-content:flex-start; align-items:center; min-height:36px; position:relative; z-index:60 }
            .kds-card-footer .btn, .quick-status-btn { padding:.38rem .7rem; font-size:.92rem; border-radius:8px; font-weight:900 }
            /* Actions container groups View + quick-status buttons inline */
            .kds-actions { display:flex; align-items:center; gap:8px }
            .kds-actions .quick-status-form { margin:0 }
            .kds-actions .quick-status-btn { min-width:66px; white-space:nowrap }
            /* Slightly smaller buttons in dense mode */
            .kds-dense .kds-actions .quick-status-btn { min-width:56px; padding:.22rem .45rem; font-size:0.82rem }
            .kds-card-footer > * { display:inline-flex; align-items:center; margin:0; }
            .kds-card-footer form { display:inline-flex; margin:0 }
            .kds-card-footer .quick-status-btn { margin-left:6px }

            /* actions container next to 'Ver' */
            .kds-actions { display:inline-flex; gap:8px; align-items:center }
            .kds-actions .btn { display:inline-flex; align-items:center }
            /* Inline actions under preview to ensure status buttons are always visible */
            .kds-inline-actions { display:flex; gap:8px; align-items:center }
            .kds-inline-actions .quick-status-btn { min-width:66px }
            .kds-dense .kds-inline-actions .quick-status-btn { min-width:56px; padding:.2rem .45rem }
            /* ensure card footer is visible and not clipped when cards are compact */
            .kds-card { overflow: visible }
            /* in dense mode pin footer inside the card bottom so it's always reachable */
                /* Keep footer in the normal flow in dense mode so it's not clipped by the scroller.
                    This prevents the footer from being placed outside the visible card area when ancestors
                    use `overflow:hidden`. */
                .kds-dense .kds-card-footer { position: relative; left: auto; right: auto; bottom: auto; display:flex; justify-content:flex-start; gap:8px; background: transparent; z-index:70 }
                /* Reserve modest bottom padding so footer fits inside the card box */
                .kds-dense .kds-card { padding-bottom: 46px }
            /* Ensure quick-status buttons are visible and clickable in dense mode */
            .quick-status-btn { display:inline-block !important; opacity:1 !important; z-index:5 }
            .kds-card-footer .btn-primary { background: linear-gradient(180deg,#2e6bd6,#1b4fb0); border-color: rgba(0,0,0,0.08); color:#fff }
            .kds-card-footer .btn-success { background: linear-gradient(180deg,#2fb26f,#1a8a4f); border-color: rgba(0,0,0,0.06); color:#fff }
            .kds-card-footer .btn-outline-secondary { color:#333; border-color: rgba(0,0,0,0.06); background:transparent }
            .kds-card-footer .badge { font-size:0.95rem; padding:.38rem .62rem; font-weight:800 }

            .kds-card.collapsed .kds-item-comment { display:none; }

            @media (max-width: 992px) {
                .container-fluid > .row { flex-wrap: wrap; }
                .kds-column { max-width: 100%; min-width: 0; }
                .kds-card { min-height: 110px; width: 100%; }
                .kds-card-head .fw-bold { font-size: 1.05rem }
            }

            /* Mobile (phones) improvements: show horizontal accumulation and better single-column fit on narrow screens */
            @media (max-width: 600px) {
                /* show arrows on small screens so cooks can navigate with buttons */
                .kds-scroll-left, .kds-scroll-right { display: flex !important; width:44px; height:44px; border-radius:10px }

                /* allow the main row to be full-bleed on phones and avoid side clipping */
                .container-fluid > .row { padding: 0 6px; max-width: none; }

                /* make each column a horizontal carousel so orders accumulate side-by-side */
                .kds-column { padding-top: 8px; }
                .kds-column-body {
                    display:flex; flex-direction:row; flex-wrap: wrap; gap:12px; padding: 10px 12px; overflow-x:auto; overflow-y:hidden;
                    -webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory; scroll-padding-inline: 12px;
                    touch-action: auto; overscroll-behavior-x: contain; scroll-behavior: smooth; position: relative; z-index: 10; pointer-events: auto;
                }
                .kds-column-body::-webkit-scrollbar { height:8px }

                /* cards: full-width on phone for better reading and stable layout */
                .kds-card { flex: 0 0 100%; width: 100%; min-width: 100%; box-sizing: border-box; padding: 12px; border-radius: 10px; box-shadow: 0 8px 16px rgba(0,0,0,0.12); transform: none; scroll-snap-align: start }
                .kds-column-body .kds-card:first-child { margin-inline-start: 0 }
                .kds-column-body .kds-card:last-child { margin-inline-end: 0 }
                .kds-card:hover { transform: none }

                /* headings and text sizes tuned for the compact phone look */
                .kds-card-head .fw-bold { font-size: 1.06rem }
                .kds-item-left .fw-bold { font-size: 1rem }
                .kds-item-comment { font-size: 0.96rem }

                /* actions remain inline but wrap as needed; keep buttons tappable */
                .kds-inline-actions, .kds-actions { display:flex; gap:8px; flex-wrap:wrap }
                .kds-inline-actions .btn, .kds-actions .btn, .quick-status-btn { font-size: 0.95rem; padding: .42rem .7rem }
                .kds-inline-actions { margin-bottom: 6px }

                /* footer minimal */
                .kds-card-footer { padding-top:4px }

                /* make column header sit above scroller but not cover it visually */
                .kds-column-header { position: relative; z-index: 2 }

                /* ensure dense mode adapts to phone width too */
                .kds-dense .kds-card { flex: 0 0 100%; width: 100%; padding-bottom: 46px }
                .kds-dense .kds-column-body { overflow-x: auto; overflow-y: hidden }
            }

            /* visual separators between cards when stacked */
            .kds-column-body .kds-card + .kds-card { margin-top: 10px }

            /* Dense mode overrides: reduce sizes to maximize visible orders horizontally */
            .kds-dense .kds-column-body { gap:8px; padding:8px 16px; }
            .kds-dense .kds-card { flex: 0 0 170px; width: 170px; min-height: auto; padding:8px 8px 56px 8px; box-shadow:none; border-color: rgba(0,0,0,0.06); font-size:0.86rem }
            .kds-dense .kds-card .kds-meta, .kds-dense .kds-card-head .small.text-muted { font-size:0.72rem }
            .kds-dense .kds-card-head .fw-bold { font-size:1rem }
            .kds-dense .kds-item-left .fw-bold { font-size:0.95rem }
            /* keep comments visible in dense mode so cooks can read notes */
            .kds-dense .kds-item-comment { display:block }
            .kds-dense .kds-dispatch { width:30px; height:30px; right:8px; bottom:8px }
            .kds-dense .kds-card-footer .btn, .kds-dense .quick-status-btn { padding:.2rem .45rem; font-size:0.8rem }
            /* In dense mode allow cards to expand vertically so all items are visible */
            .kds-dense .kds-column-body { max-height: none; overflow-x:auto; overflow-y:visible }
            /* ensure multiple cards are visible by default on typical screens */
            .kds-dense .kds-column-body .kds-card { scroll-snap-align:start }
        </style>
        @php
            $cols = ['waiting' => 'En cola', 'ready' => 'Lista'];
            $colors = ['waiting' => 'bg-secondary', 'ready' => 'bg-success'];
        @endphp

                    @php
                        // Flatten ordersByStatus into a single collection and set status on each order
                        $allOrders = collect();
                        foreach(($ordersByStatus ?? []) as $st => $group){
                            foreach($group as $oo){ $oo->status = $st; $allOrders->push($oo); }
                        }
                    @endphp

                    {{-- Presentación: usar la vista en modo 'table' para que el panel de cocina se vea
                        como en la foto (orden agrupada por orden, filas por item). No tocar lógica, solo presentación. --}}
                    <style>
                        /* Table-like kitchen board styling (aim: match photo aesthetics) */
                        #kitchen-board .table-responsive { margin-top: 8px; }
                        #kitchen-board table.table { border-collapse: separate; border-spacing: 0; width: 100%; background: transparent; }
                        #kitchen-board table.table thead th { background: #fafafa; border-bottom: 2px solid rgba(0,0,0,0.06); color:#222; font-weight:700; text-align:left; padding:8px 12px; }
                        /* Compact rows: reduce padding so more rows fit on screen */
                        #kitchen-board table.table tbody td { vertical-align: middle; padding: 8px 10px; border-top: 1px solid rgba(0,0,0,0.06); }
                        /* Reduce extra spacing between rows */
                        #kitchen-board table.table tbody tr { line-height: 1.12; }
                        #kitchen-board table.table tbody tr td:first-child { font-weight:800; color:#333; width:100px; padding-right:6px; }
                        #kitchen-board table.table tbody td small.text-muted { display:block; margin-top:4px; color:rgba(0,0,0,0.45); font-weight:600 }
                        /* Strong division between order groups so kitchen staff can distinguish orders easily */
                        #kitchen-board table.table tbody tr.order-group-divider td { border-bottom: 3px solid rgba(0,0,0,0.18); padding-bottom: 12px; }
                        #kitchen-board table.table tbody tr.order-group-divider td:first-child { padding-bottom: 12px; }
                        #kitchen-board table.table tbody td, #kitchen-board table.table tbody td .text-muted { font-size: 1.02rem; }
                        #kitchen-board table.table thead th { font-size: 1.04rem }
                        /* Slightly smaller badge so it doesn't push layout */
                        #kitchen-board .badge { background: #ffc107; color: #222; font-weight:800; padding:.28rem .45rem; border-radius:6px; font-size:0.95rem }
                        #kitchen-board .btn.btn-outline-primary { border-color: rgba(0,0,0,0.06); color:#2b6fb3; background: #fff; }
                        #kitchen-board .btn.btn-dark { background:#f6f6f6; color:#b03232; border:1px solid rgba(0,0,0,0.06); }
                        #kitchen-board .btn.btn-dark.ms-1 { background: transparent; color:#d9534f; border:1px solid rgba(217,83,79,0.12); }
                        /* Make the table boxed like the reference image */
                        #kitchen-board .table-bordered { border: 2px solid rgba(0,0,0,0.12); border-radius:6px; overflow:hidden; }
                        @media (max-width: 768px){ #kitchen-board table.table tbody td { padding: 10px 8px } }
                        /* Button styling: keep existing classes & behavior, change only visuals */
                        #kitchen-board .kds-actions { display:flex; flex-direction:column; align-items:flex-end; gap:6px; }
                        #kitchen-board .kds-actions .btn { background: #0b0b0b; color:#fff; border: none; box-shadow: 0 8px 18px rgba(2,6,23,0.38); padding:6px 10px; border-radius:16px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; font-size:0.95rem }
                        #kitchen-board .kds-actions .btn.btn-outline-primary { background:#0b0b0b; color:#fff; border:none; }
                        #kitchen-board .kds-actions .btn.btn-dark { background:#0b0b0b; color:#fff; border:none; }
                        #kitchen-board .kds-actions .btn.btn-sm { padding:6px 10px; font-size:0.9rem; }
                        /* small separator bar for status controls above buttons (like photo 3) */
                        .kds-status-row { display:flex; flex-direction:column; align-items:flex-end; gap:6px }
                        .kds-status-group { display:flex; gap:8px; align-items:center }
                        .kds-status-btn { background: transparent; border: none; padding: 0; display:inline-flex; align-items:center; }
                        .kds-tile-dot { width:28px; height:8px; border-radius:10px; display:inline-block; border:0; box-shadow: 0 6px 10px rgba(0,0,0,0.14); opacity:0.95 }
                        .kds-tile-dot.red { background: #ff8a80 }
                        .kds-tile-dot.green { background: #a5d6a7 }
                        /* ensure Limpiar button is visually prominent but reuses JS handlers */
                        .kds-hide-local { background:#111 !important; color:#fff !important; box-shadow: 0 8px 16px rgba(0,0,0,0.34) !important; border-radius:16px !important; padding:6px 10px !important }
                        /* make stacked actions compact on smaller screens */
                        @media (max-width: 768px) {
                            #kitchen-board .kds-actions { align-items:flex-start }
                            #kitchen-board .kds-actions .btn { padding:6px 10px }
                        }
                    </style>
                    @include('staff.orders._kds_columns', ['orders' => $allOrders, 'useColors' => true, 'mode' => 'table'])
                </div>
            </div>
            @endsection

            @push('scripts')
            <script>
            // NOTE: automatic full-page polling removed to avoid interfering with interactive admin workflows.
            // intercept quick status forms to use AJAX and update the board without full reload
            document.addEventListener('click', function(e){
                const btn = e.target.closest('.quick-status-btn');
                if(!btn) return;
                e.preventDefault();
                const form = btn.closest('form');
                if(!form) return;

                // if this is a clean action, ask for confirmation
                const statusInput = form.querySelector('input[name="status"]');
                if(statusInput && statusInput.value === 'cleaned'){
                    if(!confirm('¿Confirmas limpiar esta orden? Esto la quitará del panel de cocina.')) return;

                    // Match waiter "Limpiar" behavior: hide immediately locally, persist hidden id,
                    // pause auto-refresh, then perform server updates in background (fire-and-forget).
                    const orderIdMatch = (form.action || '').match(/\/staff\/orders\/(\d+)\/status/);
                    const orderId = orderIdMatch ? orderIdMatch[1] : (form.querySelector('[name="orderID"]') ? form.querySelector('[name="orderID"]').value : null);

                    // Pause auto-replace longer to allow server-side hide to take effect
                    try{ if(window.__pauseKdsReplace) window.__pauseKdsReplace(5000); }catch(_){ }

                    // Persist locally so poll snapshots hide this order
                    try{
                        window.__kdsHiddenSet = window.__kdsHiddenSet || new Set();
                        if(orderId) window.__kdsHiddenSet.add(String(orderId));
                        try{ sessionStorage.setItem('__kdsHiddenIds', JSON.stringify(Array.from(window.__kdsHiddenSet))); }catch(_){ }
                    }catch(err){ console.debug('kds hide set error (local - kitchen)', err); }

                    // First, inform server/session via kds/hide and WAIT for it to complete so the next poll/refresh
                    // will see the order removed from server-rendered HTML. This avoids races where refresh re-inserts tile.
                    try{
                        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                        const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
                        if(orderId){
                            await fetch('/staff/kds/hide/' + encodeURIComponent(orderId), { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json' } });
                        }
                    }catch(e){ console.debug('kds hide immediate request failed', e); }

                    // Remove card from DOM for instant feedback
                    const card = form.closest('.kds-card');
                    if(card){ card.style.transition = 'opacity .28s ease, transform .28s ease'; card.style.opacity = '0'; card.style.transform = 'scale(.96)'; setTimeout(()=>{ try{ card.remove(); }catch(e){} }, 300); }

                    // Background server updates: try kitchen-clear/status to persist DB state
                    (async function(){
                        try{
                            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                            const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
                            if(orderId){
                                try{
                                    const resClear = await fetch('/staff/orders/' + encodeURIComponent(orderId) + '/kitchen-clear', {
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
                                        console.warn('kitchen-clear failed for', orderId, resClear && resClear.status);
                                        try{
                                            const resStatus = await fetch('/staff/orders/' + encodeURIComponent(orderId) + '/status', {
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
                                        }catch(fbErr){ console.debug('fallback status error', fbErr); }
                                    } else {
                                        try{ const j = await resClear.json(); console.debug('kitchen-clear OK', j); }catch(_){ console.debug('kitchen-clear OK (no json)'); }
                                    }
                                }catch(err){ console.debug('kitchen hide background error', err); }
                            }
                        }catch(err){ console.debug('kitchen hide background error', err); }
                    })();

                    return; // don't run the normal AJAX status update below
                }

                const url = form.action;
                const formData = new FormData(form);

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(r=>r.json()).then(data=>{
                    // find the card element and remove it from the current column (we'll let auto-refresh bring the updated card into place)
                    const card = form.closest('.kds-card');
                    if(card){ card.remove(); }
                    // optionally show a small toast
                    console.log('Order updated', data);
                }).catch(err=>{ console.error('Status update failed', err); alert('Error al actualizar estado'); });
            });

            // toggle expand/collapse on card header click, avoid toggling when clicking buttons/links
            document.addEventListener('click', function(e){
                const head = e.target.closest('.kds-card-head');
                if(!head) return;
                // ignore clicks on interactive elements inside header
                if(e.target.closest('button,a,form')) return;
                const card = head.closest('.kds-card');
                if(!card) return;
                card.classList.toggle('collapsed');
            });

            // keyboard accessibility: toggle on Enter/Space when header focused
            document.addEventListener('keydown', function(e){
                if(e.key !== 'Enter' && e.key !== ' ') return;
                const el = document.activeElement;
                if(!el || !el.classList.contains('kds-card-head')) return;
                const card = el.closest('.kds-card');
                if(card) { card.classList.toggle('collapsed'); e.preventDefault(); }
            });

            // polling: refresh the kitchen board every 10s unless the user is interacting inside it
            (function(){
                // The dedicated counts poller in the KDS partial handles full-board replacement
                // when counts change. Avoid running another full-page fetch loop in table mode
                // to prevent duplicate requests and UI thrashing.
                try{ window.__kds_mode = 'table'; }catch(e){}
                const selector = '#kitchen-board';
                // Only enable a lightweight refresh loop when not in table mode
                const intervalMs = 10000; // Increased to 10 seconds to reduce refreshes
                let lastBoardHash = null; // To avoid unnecessary replacements
                let refreshInFlight = false;

                function fetchWithTimeout(url, options, timeoutMs){
                    const controller = new AbortController();
                    const timeoutId = setTimeout(function(){ controller.abort(); }, timeoutMs || 10000);
                    const mergedOptions = Object.assign({}, options || {}, { signal: controller.signal });
                    return fetch(url, mergedOptions).finally(function(){ clearTimeout(timeoutId); });
                }

                async function refreshKitchen(){
                    if(refreshInFlight) return;
                    refreshInFlight = true;
                    try{
                        // if we're in table mode, skip this refresher (pollCounts will handle updates)
                        if(window.__kds_mode === 'table') return;
                        const res = await fetchWithTimeout(window.location.href, { credentials: 'same-origin' }, 10000);
                        const text = await res.text();
                        const doc = new DOMParser().parseFromString(text, 'text/html');
                        const newBoard = doc.querySelector(selector);
                        const oldBoard = document.querySelector(selector);
                        if(!newBoard || !oldBoard) return;
                        const active = document.activeElement;
                        if(oldBoard.contains(active)) return;
                        try{ if(window.__basketInputHasFocus) return; }catch(e){ }
                        try{ if(window.__kdsUserInteracting) return; }catch(e){ }

                        // Calculate hash of new board content to avoid unnecessary replacements
                        const newBoardHTML = newBoard.innerHTML;
                        const newHash = btoa(newBoardHTML).slice(0, 32); // Simple hash
                        if(lastBoardHash === newHash) return; // No changes, skip replacement

                        const oldCols = Array.from(oldBoard.querySelectorAll('.kds-column-body'));
                        const scrollPositions = oldCols.map(c=> c ? c.scrollLeft : 0);

                        oldBoard.replaceWith(newBoard);
                        lastBoardHash = newHash; // Update hash

                        const newCols = Array.from(document.querySelectorAll('.kds-column-body'));
                        newCols.forEach((c, idx)=>{ try{ if(c && typeof scrollPositions[idx] !== 'undefined') c.scrollLeft = scrollPositions[idx]; }catch(e){} });
                    }catch(e){ console.debug('Kitchen refresh error', e); }
                    finally{
                        refreshInFlight = false;
                        setTimeout(refreshKitchen, intervalMs);
                    }
                }
                setTimeout(refreshKitchen, 500);
            })();

            // horizontal scroll controls: arrow buttons + wheel-to-scroll
            // helper to pause auto-replace after user interaction
            (function(){
                window.__kdsUserInteracting = false;
                let __kdsUserInteractingTimeout = null;
                window.__pauseKdsReplace = function(ms){
                    window.__kdsUserInteracting = true;
                    if(__kdsUserInteractingTimeout) clearTimeout(__kdsUserInteractingTimeout);
                    __kdsUserInteractingTimeout = setTimeout(function(){ window.__kdsUserInteracting = false; }, ms||2000);
                };
            })();

            document.addEventListener('click', function(e){
                const left = e.target.closest('.kds-scroll-left');
                const right = e.target.closest('.kds-scroll-right');
                if(!left && !right) return;
                const col = (left||right).closest('.kds-column');
                if(!col) return;
                const body = col.querySelector('.kds-column-body');
                if(!body) return;
                // pause auto-replace briefly while user navigates
                try{ window.__pauseKdsReplace(2500); }catch(e){}
                // snap to next card using card width
                const card = body.querySelector('.kds-card');
                const style = card ? getComputedStyle(card) : null;
                const marginRight = style ? parseInt(style.marginRight||0) : 0;
                const cardW = card ? (card.offsetWidth + marginRight) : Math.max(220, Math.floor(body.clientWidth*0.6));
                const amount = cardW;
                if(left) body.scrollBy({ left: -amount, behavior: 'smooth' });
                if(right) body.scrollBy({ left: amount, behavior: 'smooth' });
            });

            // wheel-to-scroll horizontally inside column bodies (only when pointer over body)
            document.querySelectorAll('.kds-column-body').forEach(function(body){
                body.addEventListener('wheel', function(e){
                    // horizontal scroll when using vertical wheel
                    e.preventDefault();
                    body.scrollLeft += e.deltaY;
                }, { passive: false });
            });
            </script>
            @endpush
