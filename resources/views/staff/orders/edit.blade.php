@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Pedido #{{ $order->orderID }}</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-7">
            <h4>Productos</h4>
            <div id="category-pills" class="mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary me-1 pill-filter active" data-menu="">Todas las categorías</button>
                @foreach($menus as $m)
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1 pill-filter" data-menu="{{ $m->menuID }}">{{ $m->menuName }}</button>
                @endforeach
            </div>

            <div id="products-list" class="row g-3" style="max-height:72vh; overflow:auto;">
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
                                        <button class="btn btn-sm btn-dark tile-decrement me-1" type="button">-</button>
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

        <div class="col-12 col-md-5">
            <h4>Pedido</h4>
            <form id="order-form" method="POST" action="{{ route('staff.orders.update', $order->orderID) }}" class="card shadow-sm">
                @csrf
                @method('PATCH')
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h6 mb-0">Pedido</div>
                        <div class="small text-muted">Lista de items seleccionados</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Total</div>
                        <div class="h5 fw-bold">$ <span id="basket-total">0.00</span></div>
                        @if(in_array(optional(Auth::user())->role, ['mesero','admin']))
                            <div class="mt-2">
                                <button type="submit" form="order-form" class="btn btn-sm btn-dark">Guardar edición</button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body">
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

                    <div class="row g-2 mt-3">
                        {{-- Table number removed per request --}}
                        <div class="col-12 col-md-6">
                            <label for="customer-name" class="form-label small required">Nombre del cliente <span class="text-danger">*</span></label>
                            <input type="text" id="customer-name" name="nombre" required class="form-control" value="{{ old('nombre', $order->customer_name) }}" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="customer-cedula" class="form-label small required">Cédula / ID <span class="text-danger">*</span></label>
                            <input type="text" id="customer-cedula" name="cedula" required class="form-control" value="{{ old('cedula', $order->customer_cedula) }}" />
                        </div>
                    </div>

                    <div class="row g-2 mt-3">
                        <div class="col-12 col-md-6">
                            <label for="table-number" class="form-label small">Mesa</label>
                            @if(isset($tables) && $tables->isNotEmpty())
                                <div class="d-flex">
                                    <select id="table-number" name="table_number" class="form-control">
                                        <option value="">-- Seleccionar mesa (opcional) --</option>
                                        @foreach($tables as $t)
                                            @php $currentTable = old('table_number', (optional($order)->customer_table ?? optional($order)->table_number)); @endphp
                                            <option value="{{ $t->number }}" {{ ($currentTable == $t->number) ? 'selected' : '' }}>{{ $t->number }} @if($t->name) - {{ $t->name }}@endif</option>
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
                                <input type="number" id="table-number" name="table_number" class="form-control" value="{{ old('table_number', (optional($order)->customer_table ?? optional($order)->table_number)) }}" placeholder="Número de mesa (opcional)" min="1" />
                            @endif
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit">Guardar cambios</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const basket = [];
    const basketTable = document.querySelector('#basket-table tbody');
    const totalEl = document.getElementById('basket-total');
    const orderForm = document.getElementById('order-form');
    // when a comment input is focused, we avoid replacing the basket DOM
    // to prevent mobile keyboards from closing. If a full render is
    // requested while focused, we set a pending flag and re-render after blur.
    let pendingFullBasketRender = false;

    function clearHiddenItemInputs(){ orderForm.querySelectorAll('input[name^="items"]').forEach(i=>i.remove()); }
    function findInBasket(id){ return basket.find(b=>b.id == id); }
    function escapeHtml(s){ return s ? s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }

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
        const tile = document.querySelector(`.product-tile[data-id='${id}']`);
        const ex = findInBasket(id);
        if(!tile) return;
        const badge = tile.querySelector('.qty-badge');
        if(ex){ badge.style.display = ''; badge.textContent = ex.quantity; tile.classList.add('selected'); }
        else { badge.style.display = 'none'; badge.textContent = '0'; tile.classList.remove('selected'); }
    }

    function renderBasket(force = false){
        // preserve focus: if a comment input is focused, update totals & hidden inputs only
        const activeEl = document.activeElement;
        const isCommentFocused = activeEl && activeEl.classList && activeEl.classList.contains('item-comment');
        if(isCommentFocused && !force){
            // pull visible comments into the model
            document.querySelectorAll('.item-comment').forEach(ic=>{
                const idx = parseInt(ic.dataset.idx);
                if(!Number.isNaN(idx) && basket[idx]) basket[idx].comment = ic.value || '';
            });
            // recalc totals
            let total = 0; basket.forEach(b=>{ total += b.price * b.quantity; });
            totalEl.textContent = total.toFixed(2);
            // update hidden inputs in-place if present
            basket.forEach((b,i)=>{
                const h = orderForm.querySelector(`input[name="items[${i}][comment]"]`);
                if(h) h.value = b.comment || '';
            });
            // mark that a full render should happen once the comment loses focus
            pendingFullBasketRender = true;
            return;
        }

        // clear pending flag because we're about to perform full render
        pendingFullBasketRender = false;

        // full render
        if(basketTable) basketTable.innerHTML = '';
        let total = 0;
        basket.forEach((row, idx)=>{
            const lineTotal = (row.price * row.quantity);
            total += lineTotal;
                if(basketTable){
                const tr = document.createElement('tr');
                // render comment as an editable button that opens a modal on mobile/desktop
                const commentLabel = escapeHtml(row.comment || '');
                tr.innerHTML = `
                    <td>${idx+1}</td>
                    <td class="text-center align-middle">
                        <div class="d-flex flex-column align-items-center">
                            <button type="button" class="btn btn-sm btn-danger remove-item mb-1" data-idx="${idx}">X</button>
                            <div class="small text-muted">#${row.id}</div>
                        </div>
                    </td>
                    <td class="fw-bold">${escapeHtml(row.name)}</td>
                    <td><button type="button" class="btn btn-sm btn-outline-secondary edit-comment-btn" data-idx="${idx}">${commentLabel || 'Agregar'}</button></td>
                    <td><input type="number" min="1" value="${row.quantity}" class="form-control form-control-sm qty-input qty-input-small" data-idx="${idx}" /></td>
                    <td class="text-end">${row.price.toFixed(2)}</td>
                    <td class="text-end">${lineTotal.toFixed(2)}</td>
                `;
                basketTable.appendChild(tr);
            }
        });
        totalEl.textContent = total.toFixed(2);

        // hidden inputs
        clearHiddenItemInputs();
        basket.forEach((b,i)=>{
            const idInput = document.createElement('input'); idInput.type='hidden'; idInput.name = `items[${i}][itemID]`; idInput.value = b.id; orderForm.appendChild(idInput);
            const qtyInput = document.createElement('input'); qtyInput.type='hidden'; qtyInput.name = `items[${i}][quantity]`; qtyInput.value = b.quantity; orderForm.appendChild(qtyInput);
            const cInput = document.createElement('input'); cInput.type='hidden'; cInput.name = `items[${i}][comment]`; cInput.value = b.comment || ''; orderForm.appendChild(cInput);
        });
    }

    // interactions
    document.querySelectorAll('.product-tile').forEach(tile=>{
        tile.addEventListener('click', function(e){ if(e.target.closest('.tile-decrement')) return; addProductById(this.dataset.id, this.dataset.name, parseFloat(this.dataset.price), 1); });
        tile.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); this.click(); } });
    });

    document.addEventListener('click', function(e){
        if(e.target.classList.contains('tile-increment')){ const tile = e.target.closest('.product-tile'); addProductById(tile.dataset.id, tile.dataset.name, parseFloat(tile.dataset.price), 1); }
        else if(e.target.classList.contains('tile-decrement')){ const tile = e.target.closest('.product-tile'); const ex = findInBasket(tile.dataset.id); if(ex) setProductQuantity(tile.dataset.id, ex.quantity - 1); }
        else if(e.target.classList.contains('remove-item')){ const idx = parseInt(e.target.dataset.idx); const id = basket[idx].id; basket.splice(idx,1); updateTileState(id); renderBasket(); }
        else if(e.target.classList.contains('edit-comment-btn')){ const idx = parseInt(e.target.dataset.idx); openCommentModal(idx); }
    });

    document.addEventListener('input', function(e){
        if(e.target.classList.contains('qty-input')){ const idx = parseInt(e.target.dataset.idx); const val = parseInt(e.target.value) || 1; basket[idx].quantity = val; updateTileState(basket[idx].id); renderBasket(); }
        if(e.target.classList.contains('item-comment')){ const idx = parseInt(e.target.dataset.idx); const val = e.target.value || ''; if(basket[idx]){ basket[idx].comment = val; const hidden = orderForm.querySelector(`input[name="items[${idx}][comment]"]`); if(hidden) hidden.value = val; } try{ window.__basketInputHasFocus = true; }catch(_){ } }
    });

    // If a comment had focus and we deferred a full render, re-render after the comment loses focus
    document.addEventListener('focusout', function(e){
        if(e.target && e.target.classList && e.target.classList.contains('item-comment')){
            if(pendingFullBasketRender){
                // slight delay to ensure focus state updated
                setTimeout(()=>{ renderBasket(true); }, 30);
            }
            try{ window.__basketInputHasFocus = false; }catch(_){ }
        }
    });

    // Robustness: track focused comment and caret so we can restore it if some other
    // script replaces the basket DOM unexpectedly (common cause of mobile keyboard closing).
    let focusedComment = null; // { idx, caretStart, caretEnd, value }

    function getCaretPos(el){ try{ return { start: el.selectionStart, end: el.selectionEnd }; }catch(e){ return { start: null, end: null }; } }
    function setCaretPos(el, pos){ try{ if(pos && pos.start!=null){ el.focus(); el.setSelectionRange(pos.start, pos.end); } else el.focus(); }catch(e){ try{ el.focus(); }catch(_){} }
    }

    document.addEventListener('focusin', function(e){
        if(e.target && e.target.classList && e.target.classList.contains('item-comment')){
            const idx = parseInt(e.target.dataset.idx);
            const caret = getCaretPos(e.target);
            focusedComment = { idx: idx, caretStart: caret.start, caretEnd: caret.end, value: e.target.value };
            try{ window.__basketInputHasFocus = true; }catch(_){ }
        }
    });

    document.addEventListener('input', function(e){
        if(e.target && e.target.classList && e.target.classList.contains('item-comment')){
            const idx = parseInt(e.target.dataset.idx);
            const caret = getCaretPos(e.target);
            if(focusedComment && focusedComment.idx === idx){ focusedComment.caretStart = caret.start; focusedComment.caretEnd = caret.end; focusedComment.value = e.target.value; }
            try{ window.__basketInputHasFocus = true; }catch(_){ }
        }
    }, true);

    // Observe unexpected DOM changes inside the basket container. If the focused input
    // disappears, recreate the basket and restore focus & caret to the corresponding input.
    try{
        const basketContainer = document.querySelector('#basket-table');
        if(basketContainer && window.MutationObserver){
            const mo = new MutationObserver(function(muts){
                if(!focusedComment) return;
                const selector = `.item-comment[data-idx="${focusedComment.idx}"]`;
                if(!basketContainer.querySelector(selector)){
                    // input was removed; force full render and restore focus
                    renderBasket(true);
                    // after render, try to find input and restore caret
                    setTimeout(()=>{
                        const el = basketContainer.querySelector(selector);
                        if(el){
                            el.value = focusedComment.value || '';
                            setCaretPos(el, { start: focusedComment.caretStart, end: focusedComment.caretEnd });
                        }
                    }, 25);
                }
            });
            mo.observe(basketContainer, { childList: true, subtree: true });
        }
    }catch(err){ /* if MutationObserver not available or errors, fail silently */ }

    const pillsContainer = document.getElementById('category-pills');
    if(pillsContainer){ pillsContainer.addEventListener('click', function(e){ const btn = e.target.closest('.pill-filter'); if(!btn) return; pillsContainer.querySelectorAll('.pill-filter').forEach(p=>p.classList.remove('active')); btn.classList.add('active'); const val = btn.dataset.menu; document.querySelectorAll('.product-row').forEach(r=>{ if(!val || r.dataset.menu==val) r.style.display=''; else r.style.display='none'; }); }); }

    document.getElementById('order-form').addEventListener('submit', function(e){
        if(basket.length === 0){ e.preventDefault(); alert('Añade al menos un producto al pedido'); return; }
        // copy visible comments into model then render hidden inputs; force full render so inputs exist before submit
        document.querySelectorAll('.item-comment').forEach(ic=>{ const idx = parseInt(ic.dataset.idx); if(!Number.isNaN(idx) && basket[idx]) basket[idx].comment = ic.value || ''; });
        // require customer name and cedula for the order
        const nameEl = document.getElementById('customer-name');
        const cedulaEl = document.getElementById('customer-cedula');
        const nameVal = nameEl ? String(nameEl.value || '').trim() : '';
        const cedVal = cedulaEl ? String(cedulaEl.value || '').trim() : '';
        if(!nameVal){ e.preventDefault(); alert('Introduce el nombre del cliente (obligatorio)'); if(nameEl) nameEl.focus(); return; }
        if(!cedVal){ e.preventDefault(); alert('Introduce la cédula / ID del cliente (obligatorio)'); if(cedulaEl) cedulaEl.focus(); return; }
        renderBasket(true);
    });

    // init tiles
    document.querySelectorAll('.product-tile').forEach(t=>{ updateTileState(t.dataset.id); });

    // Prefill basket from existing order items (preserve comment and quantity)
    (function prefillExisting(){
        const existing = @json($orderItems->map(function($i){ return ['itemID' => $i->itemID, 'quantity' => $i->quantity, 'comment' => $i->comment ?? '']; }));
        const tileData = {};
        document.querySelectorAll('.product-tile').forEach(t=>{ tileData[t.dataset.id] = { name: t.dataset.name, price: parseFloat(t.dataset.price) }; });
        existing.forEach(it=>{ const td = tileData[it.itemID]; if(!td) return; basket.push({ id: it.itemID, name: td.name, price: td.price, quantity: Math.max(1, parseInt(it.quantity)||1), comment: it.comment || '' }); updateTileState(it.itemID); });
        renderBasket();
    })();

    // ----- Comment modal implementation -----
    // create modal DOM appended to body so pollers won't replace it
    function createCommentModal(){
        if(document.getElementById('comment-modal-root')) return;
        const root = document.createElement('div'); root.id = 'comment-modal-root';
        root.style.display = 'none';
        root.innerHTML = `
            <div id="cm-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:1200;">
                <div style="background:#fff;border-radius:8px;max-width:520px;width:92%;padding:12px;box-shadow:0 6px 18px rgba(0,0,0,0.2);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Editar comentario</strong>
                        <button type="button" id="cm-close" class="btn-close" aria-label="Close"></button>
                    </div>
                    <textarea id="cm-text" class="form-control" rows="4" placeholder="Ej: sin cebolla"></textarea>
                    <div class="mt-2 d-flex justify-content-end">
                        <button id="cm-cancel" class="btn btn-sm btn-secondary me-2">Cancelar</button>
                        <button id="cm-save" class="btn btn-sm btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(root);

        const backdrop = document.getElementById('cm-backdrop');
        const txt = document.getElementById('cm-text');
        const btnClose = document.getElementById('cm-close');
        const btnCancel = document.getElementById('cm-cancel');
        const btnSave = document.getElementById('cm-save');

        function closeModal(){
            root.style.display = 'none';
            try{ window.__basketInputHasFocus = false; }catch(_){}
        }
        function openModal(){
            root.style.display = '';
            // small timeout to ensure visible then focus
            setTimeout(()=>{ txt.focus(); txt.setSelectionRange(txt.value.length, txt.value.length); }, 25);
        }

        btnClose.addEventListener('click', closeModal);
        btnCancel.addEventListener('click', closeModal);
        btnSave.addEventListener('click', function(){
            if(typeof root._editingIdx === 'number'){
                const v = txt.value || '';
                basket[root._editingIdx].comment = v;
                // update visible button text
                const btn = document.querySelector(`.edit-comment-btn[data-idx="${root._editingIdx}"]`);
                if(btn) btn.textContent = v || 'Agregar';
                // update hidden input if present
                const hidden = orderForm.querySelector(`input[name="items[${root._editingIdx}][comment]"]`);
                if(hidden) hidden.value = v;
            }
            closeModal();
        });

        // expose helpers
        window.__openCommentModal = function(idx){
            root._editingIdx = idx;
            const cur = (basket[idx] && basket[idx].comment) ? basket[idx].comment : '';
            txt.value = cur;
            try{ window.__basketInputHasFocus = true; }catch(_){}
            openModal();
        };
    }

    function openCommentModal(idx){ createCommentModal(); window.__openCommentModal(idx); }

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
                    const opt = document.createElement('option');
                    opt.value = json.table.number;
                    opt.textContent = json.table.number + (json.table.name ? ' - ' + json.table.name : '');
                    select.appendChild(opt);
                    select.value = json.table.number;
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

