@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Pedido</h1>

    <style>
        /* ====== Mejora visual del panel de cobro / pedido (solo CSS, sin cambiar lógica) ====== */
        .pedido-zone .card { border: none; background: #ffffff; border-radius:18px; box-shadow: 0 8px 28px rgba(0,0,0,0.18); }
        .pedido-zone .card-header { background: linear-gradient(180deg,#0f0f0f,#1c1c1c); color:#fff; border-top-left-radius:18px; border-top-right-radius:18px; padding:16px 20px; }
        .pedido-zone .card-header .h6 { font-weight:700; letter-spacing:.5px; }
        .pedido-zone .card-header .small { opacity:.78; }
        .pedido-zone .card-body { padding:18px 20px 28px; }
        #basket-table thead { background:#0f1620; color:#fff; }
        #basket-table thead th { font-weight:600; border-bottom: none; font-size: .72rem; letter-spacing:.05em; text-transform:uppercase; }
        #basket-table tbody td { vertical-align: middle; }
        #basket-table tbody tr { transition: background-color .15s ease; }
        #basket-table tbody tr:hover { background:#f5f8fb; }
        .remove-item.btn-danger { background:#222 !important; border:none !important; box-shadow:0 4px 12px rgba(0,0,0,.25) !important; }
        .remove-item.btn-danger:hover { background:#d32f2f !important; }
        .qty-input-small { text-align:center; }
        .item-comment { font-size:.8rem; }
        /* Bloque total arriba: alineación clara */
        .pedido-total-box { text-align:right; }
        .pedido-total-box .small { display:block; margin-bottom:4px; font-weight:600; color:#ccc; }
        .pedido-total-box .h5 { margin:0; font-size:1.4rem; font-weight:800; }
        .pedido-total-box button { margin-top:12px; }
        /* Controles del formulario: panel suave */
        .pedido-form-fields { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:18px 16px; margin-top:10px; }
        .pedido-form-fields label { font-size:.68rem; text-transform:uppercase; letter-spacing:.08em; font-weight:700; color:#334155; }
        .pedido-form-fields .form-control-lg { font-size:.9rem; padding:.65rem .75rem; border-radius:10px; }
        .pedido-form-fields .form-control { border:1px solid #cbd5e1; }
        .pedido-form-fields .form-control:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
        .pedido-form-fields .input-group .form-control { border-radius:8px; }
        /* Botón enviar amplio y destacado */
        .pedido-zone .btn.btn-primary.btn-lg { background:linear-gradient(180deg,#111,#242424) !important; border:none; font-weight:700; letter-spacing:.5px; box-shadow:0 10px 30px rgba(0,0,0,.35); }
        .pedido-zone .btn.btn-primary.btn-lg:hover { background:#111 !important; transform:translateY(-2px); }
        /* Pastillas categorías: mantener estilo pero suavizar cuando no activas */
        .pill-filter { background:#111 !important; color:#fff !important; font-size:.68rem; padding:6px 12px !important; border-radius:999px !important; box-shadow:0 6px 16px rgba(0,0,0,.28) !important; }
        .pill-filter:not(.active){ opacity:.75; }
        .pill-filter.active { background:#0a84ff !important; box-shadow:0 8px 20px rgba(10,132,255,.42) !important; }
        /* Tarjetas producto: simplificar sombras y resaltar seleccionadas */
        .card.product-tile { border:none; border-radius:14px; box-shadow:0 6px 16px rgba(0,0,0,.22); cursor:pointer; transition:transform .12s ease, box-shadow .18s ease; }
        .card.product-tile:hover { transform:translateY(-3px); box-shadow:0 12px 28px rgba(0,0,0,.30); }
        .card.product-tile.selected { outline:3px solid #0a84ff; box-shadow:0 0 0 4px rgba(10,132,255,.18); }
        .qty-badge { background:#0a84ff !important; font-weight:700; }
        /* Layout ajustes */
        .pedido-zone h4 { font-weight:800; letter-spacing:.5px; margin-bottom:12px; }
        .products-zone h4 { font-weight:800; letter-spacing:.5px; }
        /* Responsivo: tabla scroll suave */
        .table-responsive { scrollbar-width:thin; }
        .table-responsive::-webkit-scrollbar { height:8px; }
        .table-responsive::-webkit-scrollbar-thumb { background:rgba(0,0,0,.25); border-radius:8px; }
        /* Columna LN y acciones más compactas */
        #basket-table td:first-child { font-size:.75rem; font-weight:600; color:#64748b; }
        /* Suavizar fondo hover de botones +/- */
        .tile-increment, .tile-decrement { background:#111 !important; border:none !important; }
        .tile-increment:hover, .tile-decrement:hover { background:#0a84ff !important; }
        .product-tile .tile-qty { display:inline-block; min-width:28px; text-align:center; font-weight:700; font-size:.85rem; color:#111; background:#f1f5f9; border-radius:8px; padding:2px 6px; }
        .card.product-tile.selected .tile-qty { background:#0a84ff; color:#fff; }
        /* Reducir contraste extremo de sombras globales en esta vista para velocidad percibida */
        .pedido-zone .card, .card.product-tile { will-change:transform; }
        /* En móviles simplificar padding para mejor cabida */
        @media (max-width:575.98px){
            .pedido-zone .card-body { padding:16px 14px 24px; }
            .pedido-form-fields { padding:14px 12px; }
            #basket-table thead th { font-size:.62rem; }
            .item-comment { font-size:.72rem; }
        }
        /* ====== Fin mejoras visuales ====== */

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
            /* hide the inline submit/total on mobile because we use the fixed action bar */
            .mobile-submit-hidden { display: none !important; }
            /* ensure submit button is visible on mobile (inline) */
            .mobile-submit-hidden { display: block !important; margin-bottom: 0 !important; }
            /* remove any reserved bottom spacing for a removed mobile action bar */
            body { padding-bottom: 0 !important; }
            #pedido-zone { padding-bottom: 0 !important; }
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

    @php $exchangeRate = \App\Models\ExchangeRate::forDate(now()->toDateString()); @endphp
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
                                                <div class="fw-bold fs-6">
                                                    @if($exchangeRate)
                                                        {{ number_format($it->price * $exchangeRate,2) }} Bs
                                                    @else
                                                        ${{ number_format($it->price,2) }}
                                                    @endif
                                                </div>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-sm btn-dark tile-decrement me-1" type="button">-</button>
                                            <span class="tile-qty me-1">0</span>
                                            <button class="btn btn-sm btn-dark tile-increment" type="button">+</button>
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
            <form id="order-form" method="POST" action="{{ route('staff.orders.store') }}" class="card shadow-sm">
                @csrf

                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h6 mb-0">Pedido</div>
                        <div class="small text-muted">Lista de items seleccionados</div>
                    </div>
                    <div class="pedido-total-box">
                        <div class="small text-muted">Total</div>
                        <div class="h5 fw-bold"><span id="basket-total">0.00 Bs</span></div>
                        @if(in_array(optional(Auth::user())->role, ['mesero','admin']))
                            @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        {{-- Desktop/table view (sm and up) --}}
                        <div class="table-responsive">
                            <table class="table table-sm table-striped pos-table" id="basket-table">
                                <thead>
                                    <tr>
                                        <th style="width:30px">LN</th>
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

                    <div class="pedido-form-fields row g-2 mb-3">
                        {{-- Table number removed per request --}}
                        <div class="col-12 col-md-8">
                            <label for="customer-name" class="form-label small required">Nombre del cliente <span class="text-danger">*</span></label>
                            <input type="text" id="customer-name" name="nombre" required class="form-control form-control-lg" value="{{ old('nombre') }}" placeholder="Ej: Juan Pérez" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="customer-cedula" class="form-label small required">Cédula / ID <span class="text-danger">*</span></label>
                            <input type="text" id="customer-cedula" name="cedula" required class="form-control form-control-lg" value="{{ old('cedula') }}" placeholder="Ej: 0102345678" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="table-number" class="form-label small">Mesa</label>
                            @if(isset($tables) && $tables->isNotEmpty())
                                <div class="d-flex">
                                    <select id="table-number" name="table_number" class="form-control form-control-lg">
                                        <option value="">-- Seleccionar mesa (opcional) --</option>
                                        @foreach($tables as $t)
                                            <option value="{{ $t->number }}" {{ old('table_number') == $t->number ? 'selected' : '' }}>{{ $t->number }} @if($t->name) - {{ $t->name }}@endif</option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="show-add-table" class="btn btn-outline-secondary ms-2">Agregar mesa</button>
                                </div>
                                <div id="add-table-form" class="mt-2" style="display:none;">
                                    <div class="input-group">
                                        <input type="number" id="new-table-number" class="form-control" placeholder="Núm. mesa" min="1" />
                                        <input type="text" id="new-table-name" class="form-control" placeholder="Nombre (opcional)" />
                                        <button type="button" id="add-table-btn" class="btn btn-primary">Agregar</button>
                                    </div>
                                    <div id="add-table-feedback" class="small text-danger mt-1" style="display:none;"></div>
                                </div>
                            @else
                                <input type="number" id="table-number" name="table_number" class="form-control form-control-lg" value="{{ old('table_number') }}" placeholder="Número de mesa (opcional)" min="1" />
                            @endif
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="payment-method" class="form-label small">Método de pago (opcional)</label>
                            <select id="payment-method" name="payment_method" class="form-control form-control-lg">
                                <option value="">-- Seleccionar si paga ahora --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Efectivo</option>
                                <option value="pago_movil" {{ old('payment_method') == 'pago_movil' ? 'selected' : '' }}>Pago Móvil</option>
                                <option value="pos" {{ old('payment_method') == 'pos' ? 'selected' : '' }}>Débito (Punto de venta)</option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Tarjeta</option>
                                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transferencia</option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6" id="payment-reference-container" style="display: none;">
                            <label for="payment-reference" class="form-label small">Referencia de pago</label>
                            <input type="text" id="payment-reference" name="payment_reference" class="form-control form-control-lg" value="{{ old('payment_reference') }}" placeholder="Ej: ID de transacción, número de referencia" />
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="d-grid">
                                <button class="btn btn-primary btn-lg" type="submit">Enviar pedido</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
    // when a comment input is focused, avoid full DOM churn so mobile keyboards don't close
    let pendingFullBasketRender = false;

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
        const inlineQty = tile.querySelector('.tile-qty');
        if(ex){ badge.style.display = ''; badge.textContent = ex.quantity; tile.classList.add('selected'); }
        else { badge.style.display = 'none'; badge.textContent = '0'; tile.classList.remove('selected'); }
        if(inlineQty){ inlineQty.textContent = ex ? ex.quantity : '0'; }
    }

    function escapeHtml(s){ return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }


    function renderBasket(force = false){
        // If a comment input is focused, avoid replacing the basket DOM to keep the mobile keyboard open.
        const activeEl = document.activeElement;
        const isCommentFocused = activeEl && activeEl.classList && activeEl.classList.contains('item-comment');
        if(isCommentFocused && !force){
            // Pull visible comments into the model and update totals/hidden inputs only
            document.querySelectorAll('.item-comment').forEach(ic=>{
                const idx = parseInt(ic.dataset.idx);
                if(!Number.isNaN(idx) && basket[idx]) basket[idx].comment = ic.value || '';
            });
            // recalc totals
            let total = 0; basket.forEach(b=>{ total += b.price * b.quantity; });
            // format total: show Bs primary when rate exists
            const rateInlineEarly = (window.__exchangeRate && window.__exchangeRate > 0) ? parseFloat(window.__exchangeRate) : null;
            if(rateInlineEarly){ totalEl.textContent = (total * rateInlineEarly).toFixed(2) + ' Bs'; }
            else { totalEl.textContent = '$' + total.toFixed(2); }
            // update hidden comment inputs if present
            basket.forEach((b,i)=>{
                const h = orderForm.querySelector(`input[name="items[${i}][comment]"]`);
                if(h) h.value = b.comment || '';
            });
            pendingFullBasketRender = true;
            return;
        }
        // Always render a table-based basket to keep layout consistent on all screen sizes
        if(basketTable) basketTable.innerHTML = '';
        let totalUsd = 0;
        let totalBs = 0;

        basket.forEach((row, idx)=>{
            const lineTotalUsd = (row.price * row.quantity);
            totalUsd += lineTotalUsd;
            const rate = (window.__exchangeRate && window.__exchangeRate > 0) ? parseFloat(window.__exchangeRate) : null;
            const lineTotalBs = rate ? (lineTotalUsd * rate) : null;
            if (lineTotalBs !== null) totalBs += lineTotalBs;

            if (basketTable){
                const tr = document.createElement('tr');
                const priceCell = rate ? ( (row.price * rate).toFixed(2) + ' Bs') : ('$' + row.price.toFixed(2));
                const subtotalCell = rate ? (lineTotalBs.toFixed(2) + ' Bs') : ('$' + lineTotalUsd.toFixed(2));
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
                    <td class="text-end">${priceCell}</td>
                    <td class="text-end">${subtotalCell}</td>
                `;
                basketTable.appendChild(tr);
            }
        });

        // show total in Bs if rate exists, otherwise in USD
        const rate = (window.__exchangeRate && window.__exchangeRate > 0) ? parseFloat(window.__exchangeRate) : null;
        if(rate){ totalEl.textContent = totalBs.toFixed(2) + ' Bs'; }
        else { totalEl.textContent = '$' + totalUsd.toFixed(2); }
    // also update mobile total element to mirror desktop total
    const mobileTotalEl = document.getElementById('mobile-basket-total');
    if (mobileTotalEl) mobileTotalEl.textContent = totalEl.textContent;
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
        // require customer name and cedula
        const nameEl = document.getElementById('customer-name');
        const cedulaEl = document.getElementById('customer-cedula');
        const nameVal = nameEl ? String(nameEl.value || '').trim() : '';
        const cedVal = cedulaEl ? String(cedulaEl.value || '').trim() : '';
        if(!nameVal){ e.preventDefault(); alert('Introduce el nombre del cliente (obligatorio)'); if(nameEl) nameEl.focus(); return; }
        if(!cedVal){ e.preventDefault(); alert('Introduce la cédula / ID del cliente (obligatorio)'); if(cedulaEl) cedulaEl.focus(); return; }
        // force full render so hidden inputs exist before submit
        renderBasket(true);
    });

    // If a comment had focus and we deferred a full render, re-render after the comment loses focus
    document.addEventListener('focusout', function(e){
        if(e.target && e.target.classList && e.target.classList.contains('item-comment')){
            if(pendingFullBasketRender){
                setTimeout(()=>{ renderBasket(true); }, 30);
            }
            try{ window.__basketInputHasFocus = false; }catch(_){ }
        }
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
        if(mTotalEl) mTotalEl.textContent = totalEl.textContent;
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

    // Show/hide payment reference field based on payment method
    document.getElementById('payment-method').addEventListener('change', function() {
        const container = document.getElementById('payment-reference-container');
        const referenceInput = document.getElementById('payment-reference');
        if (this.value === 'pago_movil' || this.value === 'transfer') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
            referenceInput.value = '';
        }
    });

    // Trigger on page load if old value is set
    const paymentMethodSelect = document.getElementById('payment-method');
    if (paymentMethodSelect.value === 'pago_movil' || paymentMethodSelect.value === 'transfer') {
        document.getElementById('payment-reference-container').style.display = 'block';
    }
    // initial refresh of mobile action bar
    if(typeof refreshMobileBar === 'function') refreshMobileBar();
})();
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const showBtn = document.getElementById('show-add-table');
    const formWrap = document.getElementById('add-table-form');
    const addBtn = document.getElementById('add-table-btn');
    const feedback = document.getElementById('add-table-feedback');
    const select = document.getElementById('table-number');
    if(showBtn && formWrap){
        showBtn.addEventListener('click', function(){
            formWrap.style.display = formWrap.style.display === 'none' ? '' : 'none';
        });
    }
    if(addBtn){
        addBtn.addEventListener('click', function(){
            feedback.style.display = 'none';
            const number = parseInt(document.getElementById('new-table-number').value || 0);
            const name = document.getElementById('new-table-name').value || '';
            if(!number || number < 1){ feedback.textContent = 'Introduce un número de mesa válido'; feedback.style.display = ''; return; }

            addBtn.disabled = true;
            const token = '{{ csrf_token() }}';
            fetch('{{ route('staff.tables.store') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ number: number, name: name })
            }).then(r => r.json()).then(json => {
                if(json && json.ok && json.table){
                    // add option to select and choose it
                    const opt = document.createElement('option');
                    opt.value = json.table.number;
                    opt.textContent = json.table.number + (json.table.name ? ' - ' + json.table.name : '');
                    select.appendChild(opt);
                    select.value = json.table.number;
                    // reset form
                    document.getElementById('new-table-number').value = '';
                    document.getElementById('new-table-name').value = '';
                    formWrap.style.display = 'none';
                } else {
                    feedback.textContent = (json && json.message) ? json.message : 'No se pudo crear la mesa';
                    feedback.style.display = '';
                }
            }).catch(err => { feedback.textContent = 'Error al crear la mesa'; feedback.style.display = ''; console.error(err); }).finally(()=>{ addBtn.disabled = false; });
        });
    }
});
</script>
@endpush
