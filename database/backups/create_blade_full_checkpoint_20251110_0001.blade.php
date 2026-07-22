@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Pedido (CHECKPOINT 2025-11-10 00:01)</h1>

    <style>
        /* Small screens: stack products first, then pedido; allow full vertical scroll */
        @media (max-width:575.98px){
            .sticky-top { position: static !important; top: auto !important; }
            .product-row { padding: .25rem !important; }
            /* products list: do not lock internal scrolling on small screens — let the page scroll so Pedido remains reachable */
            #products-list { max-height: none !important; overflow-y: visible !important; }
            /* ensure products zone and container do not constrain page scroll on very small screens */
            #products-zone, .products-zone, .container, .row { height: auto !important; min-height: auto !important; overflow: visible !important; }
            /* ensure products zone has bottom padding so last items aren't hidden behind footer */
            .products-zone { padding-bottom: 100px; }
            /* ensure pedido/form/table do not create internal scroll boxes on mobile; allow page to scroll vertically */
            #pedido-zone, #order-form, #order-form .card { max-height: none !important; height: auto !important; overflow: visible !important; }
            /* Allow horizontal scrolling for wide tables but keep vertical overflow visible so rows are reachable */
            .table-responsive { overflow-x: auto !important; overflow-y: visible !important; -webkit-overflow-scrolling: touch; }
            /* Prevent inputs inside table from forcing the table to be clipped */
            .pos-table td input.form-control { min-width: 0; }
            /* keep the table visible on mobile (we render table-based basket) */
            .table-responsive { display: block !important; }
            /* hide stacked mobile basket (we're using table on all sizes) */
            #mobile-basket { display: none !important; }
            /* ensure submit button is visible on mobile */
            .mobile-submit-hidden { display: block !important; }
            /* show mobile action bar with total/submit */
            #mobile-action-bar { display:flex !important; z-index: 9999; height:68px; align-items:center; }
            /* avoid mobile-action-bar covering content by reserving space */
            body { padding-bottom: 92px; }
            /* ensure the mobile action bar is fully on top and clickable */
            #mobile-action-bar { z-index: 99999 !important; pointer-events: auto !important; }
            #mobile-submit-btn { z-index: 100000 !important; position: relative; }
            /* Ensure the pedido area keeps enough bottom space so rows and the submit button are reachable on small phones */
            #pedido-zone { padding-bottom: 180px !important; }
            .mobile-submit-hidden { margin-bottom: 16px !important; }
            /* reduce some spacing to fit more content on screen */
            .card.product-tile .card-body { padding: .75rem; }
            .card.product-tile h6 { font-size: .95rem; }
            /* ensure page/container can scroll on mobile */
            html, body { height: auto !important; overflow: visible !important; }
            /* avoid forcing full-viewport min-height which can hide content below */
            .container { min-height: auto !important; }
        }

        /* Desktop/table behaviour (>=576px) */
        @media (min-width:576px){
            .pos-table { width: 100%; table-layout: auto; }
            .pos-table th, .pos-table td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .pos-table th { font-size: .9rem; }
            .pos-table input.form-control { width: 100%; }
            #products-list { max-height: 72vh; overflow: auto; }
        }
        /* Zones visual separation */
        .products-zone { padding-right: 0.5rem; }
        .pedido-zone { padding-left: 0.5rem; }
        @media (max-width:575.98px){
            /* add spacing between the product list and pedido on mobile */
            .products-zone { margin-bottom: 1rem; }
            .pedido-zone { background: #fff; border-radius: .5rem; padding-top: .5rem; }
        }
    </style>

    <div class="row">
        @if($errors->any())
            <div class="col-12">
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
                <div class="col-12 col-md-7 products-zone" id="products-zone">
            <h4>Productos</h4>
            <div class="mb-3">
                <div id="category-pills" class="mb-2" style="max-width:100%;">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1 pill-filter active" data-menu="">Todas las categorías</button>
                    @foreach($menus as $m)
                        <button type="button" class="btn btn-sm btn-outline-secondary me-1 pill-filter" data-menu="{{ $m->menuID }}">{{ $m->menuName }}</button>
                    @endforeach
                </div>
                <div id="products-list" class="row g-3">
                    @foreach($items as $it)
                        <div class="col-6 col-md-3 product-row p-1" data-menu="{{ $it->menuID }}">
                            <div class="card product-tile h-100" tabindex="0" data-id="{{ $it->itemID }}" data-name="{{ $it->menuItemName }}" data-price="{{ $it->price }}">
                                <div class="card-body d-flex flex-column justify-content-between position-relative">
                                    <div>
                                        <h6 class="card-title mb-1">{{ $it->menuItemName }}</h6>
                                        <small class="text-muted d-block">{{ optional($it->menu)->menuName }}</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="fw-bold fs-6">{{ number_format($it->price,2) }}</div>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-sm btn-outline-secondary tile-decrement me-1" type="button">-</button>
                                            <button class="btn btn-success btn-sm tile-increment" type="button">+</button>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary qty-badge position-absolute" style="display:none; top:8px; right:8px;">0</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
                <div class="col-12 col-md-5 pedido-zone" id="pedido-zone">
                    <h4>Pedido</h4>
            <form id="order-form" method="POST" action="{{ route('staff.orders.store') }}" class="card p-3">
                @csrf

                            <div class="mb-2">
                                {{-- Desktop/table view (sm and up) --}}
                                <div class="table-responsive">
                                    <table class="table pos-table" id="basket-table">
                                        <thead>
                                                    <tr>
                                                        <th style="width:30px">LN</th>
                                                        {{-- delete button + code will share this column --}}
                                                        <th style="width:70px"></th>
                                                        <th>Producto</th>
                                                        <th style="width:220px">Comentario</th>
                                                        <th style="width:90px">Cant</th>
                                                        <th style="width:100px">Precio</th>
                                                        <th style="width:100px">Subtotal</th>
                                                    </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                {{-- Mobile stacked view (xs) - hidden to keep table layout on small screens for better consistency --}}
                                <div id="mobile-basket" class="d-none"></div>
                            </div>

                            <div class="mb-3">
                                <label for="table-number" class="form-label">Número de mesa <span class="text-danger">*</span></label>
                                <input type="number" id="table-number" name="table_number" min="1" class="form-control" required placeholder="Ej: 12" />
                            </div>

                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="totals-box">
                                        <div class="d-flex justify-content-between align-items-baseline"><div class="pos-subtitle">Total:</div><div class="totals-big"><span id="basket-total">0.00</span></div></div>
                                    </div>
                                </div>
                                <div class="col-12 mt-2 mobile-submit-hidden">
                                    <div class="d-grid">
                                        <button class="btn btn-primary btn-lg" type="submit">Enviar pedido</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- Mobile fixed action bar: shows total and submit button --}}
                        <div id="mobile-action-bar" class="d-none" style="position:fixed;left:0;right:0;bottom:0;height:68px;padding:.6rem 1rem;background:#fff;border-top:1px solid rgba(0,0,0,.06);box-shadow:0 -2px 6px rgba(0,0,0,0.05);z-index:1050;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Total</div>
                                    <div class="fw-bold" id="mobile-basket-total">0.00</div>
                                </div>
                                <div>
                                    <button id="mobile-submit-btn" class="btn btn-primary">Enviar pedido</button>
                                </div>
                            </div>
                        </div>
                        {{-- Sidebar KDS removed from create page per UX request --}}
                </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
    // Ensure mobile action bar doesn't block access: set padding and reservation on show/resize
    function ensureMobileActionBarSpacing(){
        const mobileActionBar = document.getElementById('mobile-action-bar');
        if(!mobileActionBar) return;
        const h = mobileActionBar.offsetHeight || 68;
        // set body bottom padding so page content can scroll above the fixed bar
        document.body.style.paddingBottom = h + 24 + 'px'; // extra breathing room
        // add bottom margin to the order form so the inline submit isn't hidden
        const orderForm = document.getElementById('order-form');
        if(orderForm) orderForm.style.marginBottom = (h + 12) + 'px';
    }
    // run early and on resize/orientation change
    ensureMobileActionBarSpacing();
    window.addEventListener('resize', ensureMobileActionBarSpacing);
    window.addEventListener('orientationchange', function(){ setTimeout(ensureMobileActionBarSpacing, 250); });

    const basket = [];
    const basketTable = document.querySelector('#basket-table tbody');
    const mobileBasketEl = document.getElementById('mobile-basket');
    const totalEl = document.getElementById('basket-total');
    const orderForm = document.getElementById('order-form');

    function clearHiddenItemInputs(){
        orderForm.querySelectorAll('input[name^="items"]').forEach(i=>i.remove());
    }

    function findInBasket(id){ return basket.find(b=>b.id == id); }

    function addProductById(id, name, price, qty = 1){
        const ex = findInBasket(id);
        if(ex){ ex.quantity = Math.max(1, ex.quantity + qty); }
        else { basket.push({ id: id, name: name, price: price, quantity: Math.max(1, qty), comment: '' }); }
        updateTileState(id);
        renderBasket();
    }

    function setProductQuantity(id, qty){
        const ex = findInBasket(id);
        if(!ex) return;
        ex.quantity = Math.max(0, qty);
        if(ex.quantity === 0){ const i = basket.indexOf(ex); if(i>=0) basket.splice(i,1); }
        updateTileState(id);
        renderBasket();
    }

    function updateTileState(id){
        // update badge and selected state for a tile
        const tile = document.querySelector(`.product-tile[data-id='${id}']`);
        const ex = findInBasket(id);
        if(!tile) return;
        const badge = tile.querySelector('.qty-badge');
        if(ex){ badge.style.display = ''; badge.textContent = ex.quantity; tile.classList.add('selected'); }
        else { badge.style.display = 'none'; badge.textContent = '0'; tile.classList.remove('selected'); }
    }

    function escapeHtml(s){ return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }


    function renderBasket(){
        // Always render a table-based basket to keep layout consistent on all screen sizes
        if(basketTable) basketTable.innerHTML = '';
        let total = 0;

        basket.forEach((row, idx)=>{
            const lineTotal = (row.price * row.quantity);
            total += lineTotal;

            if (basketTable){
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${idx+1}</td>
                    <td class="text-center align-middle">
                        <div class="d-flex flex-column align-items-center">
                            <button type="button" class="btn btn-sm btn-danger remove-item mb-1" data-idx="${idx}">X</button>
                            <div class="small text-muted">#${row.id}</div>
                        </div>
                    </td>
                    <td class="fw-bold">${escapeHtml(row.name)}</td>
                    <td><input type="text" class="form-control form-control-sm item-comment" data-idx="${idx}" value="${escapeHtml(row.comment || '')}" placeholder="Ej: sin cebolla" /></td>
                    <td><input type="number" min="1" value="${row.quantity}" class="form-control form-control-sm qty-input qty-input-small" data-idx="${idx}" /></td>
                    <td class="text-end">${row.price.toFixed(2)}</td>
                    <td class="text-end">${lineTotal.toFixed(2)}</td>
                `;
                basketTable.appendChild(tr);
            }
        });

        // only show total (no subtotal/tax)
        totalEl.textContent = total.toFixed(2);
    // also update mobile total element if present
    const mobileTotalEl = document.getElementById('mobile-basket-total');
    if (mobileTotalEl) mobileTotalEl.textContent = total.toFixed(2);
    // refresh mobile action bar visibility/total
    if(typeof refreshMobileBar === 'function') refreshMobileBar();

        // write hidden inputs for submission
        clearHiddenItemInputs();
        basket.forEach((b, i)=>{
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = `items[${i}][itemID]`;
            idInput.value = b.id;
            orderForm.appendChild(idInput);

            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = `items[${i}][quantity]`;
            qtyInput.value = b.quantity;
            orderForm.appendChild(qtyInput);

            const cInput = document.createElement('input');
            cInput.type = 'hidden';
            cInput.name = `items[${i}][comment]`;
            cInput.value = b.comment || '';
            orderForm.appendChild(cInput);
        });
    }

    // tile interactions
    document.querySelectorAll('.product-tile').forEach(tile=>{
        // increment on click of tile body
        tile.addEventListener('click', function(e){
            // ignore clicks on the decrement button
            if(e.target.closest('.tile-decrement')) return;
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            addProductById(id, name, price, 1);
        });

        // keyboard add with Enter
        tile.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); this.click(); } });
    });

    // delegated +/- handlers
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('tile-increment')){
            const tile = e.target.closest('.product-tile');
            const id = tile.dataset.id; addProductById(id, tile.dataset.name, parseFloat(tile.dataset.price), 1);
        } else if(e.target.classList.contains('tile-decrement')){
            const tile = e.target.closest('.product-tile');
            const id = tile.dataset.id; const ex = findInBasket(id);
            if(ex){ setProductQuantity(id, ex.quantity - 1); }
        }
    });

    // keep tiles in sync when basket row qty changes or removed
    basketTable.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-item')){
            const idx = parseInt(e.target.dataset.idx);
            const id = basket[idx].id;
            basket.splice(idx,1);
            updateTileState(id);
            renderBasket();
        }
    });

    basketTable.addEventListener('input', function(e){
        if(e.target.classList.contains('qty-input')){
            const idx = parseInt(e.target.dataset.idx);
            const val = parseInt(e.target.value) || 1;
            basket[idx].quantity = val;
            updateTileState(basket[idx].id);
            renderBasket();
        }
        if(e.target.classList.contains('item-comment')){
            const idx = parseInt(e.target.dataset.idx);
            const val = e.target.value || '';
            if(basket[idx]){
                basket[idx].comment = val;
                // update hidden input if present
                const hidden = orderForm.querySelector(`input[name="items[${idx}][comment]"]`);
                if(hidden) hidden.value = val;
            }
        }
    });

    // fall back: old add-product button compatibility
    document.querySelectorAll('.add-product').forEach(btn=>{
        btn.addEventListener('click', function(){
            const id = this.dataset.id; const name = this.dataset.name; const price = parseFloat(this.dataset.price);
            addProductById(id, name, price, 1);
        });
    });
    
        // respond to window resize so we re-render mobile/desktop layout when orientation changes
        window.addEventListener('resize', function(){
            renderBasket();
        });

    // category pills filter
    const pillsContainer = document.getElementById('category-pills');
    if(pillsContainer){
        pillsContainer.addEventListener('click', function(e){
            const btn = e.target.closest('.pill-filter');
            if(!btn) return;
            // set active class
            pillsContainer.querySelectorAll('.pill-filter').forEach(p=>p.classList.remove('active'));
            btn.classList.add('active');
            const val = btn.dataset.menu;
            document.querySelectorAll('.product-row').forEach(r=>{
                if(!val || r.dataset.menu == val) r.style.display = '';
                else r.style.display = 'none';
            });
        });
    }

    // submit
    document.getElementById('order-form').addEventListener('submit', function(e){
        if(basket.length === 0){ e.preventDefault(); alert('Añade al menos un producto al pedido'); return; }
        // validate table number is present and >= 1
        const tableEl = document.getElementById('table-number');
        const tableVal = tableEl ? parseInt(tableEl.value, 10) : 0;
        if(!tableVal || tableVal < 1){
            e.preventDefault();
            alert('Introduce el número de la mesa (obligatorio)');
            if(tableEl) tableEl.focus();
            return;
        }
        renderBasket();
    });

    // wire mobile submit button to trigger the form submit
    const mobileSubmit = document.getElementById('mobile-submit-btn');
    if(mobileSubmit){
        mobileSubmit.addEventListener('click', function(){
            // scroll to top of form to show validation messages if any
            document.getElementById('order-form').scrollIntoView({behavior:'smooth'});
            document.getElementById('order-form').requestSubmit ? document.getElementById('order-form').requestSubmit() : document.getElementById('order-form').submit();
        });
    }

    // mobile action bar: update total and provide a quick scroll to pedido
    const mobileActionBar = document.getElementById('mobile-action-bar');
    function refreshMobileBar(){
        const mTotalEl = document.getElementById('mobile-basket-total');
        if(mTotalEl) mTotalEl.textContent = (parseFloat(totalEl.textContent) || 0).toFixed(2);
        if(window.innerWidth < 576 && mobileActionBar){
            mobileActionBar.style.display = 'flex';
            // ensure spacing is updated when we show the bar
            ensureMobileActionBarSpacing();
        } else if(mobileActionBar) {
            mobileActionBar.style.display = 'none';
            // reset spacing when hidden
            document.body.style.paddingBottom = null;
            const orderFormEl = document.getElementById('order-form'); if(orderFormEl) orderFormEl.style.marginBottom = null;
        }
    }

    // when mobile action bar is clicked on its total area, scroll to pedido zone
    if(mobileActionBar){
        mobileActionBar.addEventListener('click', function(e){
            // if click is on button, let submit handler run; otherwise scroll
            if(e.target && e.target.id === 'mobile-submit-btn') return;
            const pedido = document.getElementById('pedido-zone');
            if(pedido) pedido.scrollIntoView({behavior:'smooth'});
        });
    }

    // initialize tile badge states in case server-side preselected
    document.querySelectorAll('.product-tile').forEach(t=>{ updateTileState(t.dataset.id); });
    // initial refresh of mobile action bar
    if(typeof refreshMobileBar === 'function') refreshMobileBar();
})();
</script>
@endpush
