@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pedidos</h1>

    @if(in_array(optional(Auth::user())->role, ['mesero','admin']))
        <div class="d-flex align-items-center mb-3 staff-header">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('staff.orders.create') }}" class="btn btn-dark">Crear pedido</a>
                <form method="GET" action="{{ route('staff.orders.index') }}" class="search-inline d-flex align-items-center gap-2">
                    <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar por nombre, cédula o mesa" value="{{ old('q', $q ?? '') }}" />
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Buscar</button>
                </form>
            </div>
            <div class="ms-auto">
                <a href="{{ route('staff.tables.index') }}" class="btn btn-dark">Mesas</a>
                @if(in_array(optional(Auth::user())->role, ['cocina_barra','admin']))
                    <form method="POST" action="{{ route('staff.orders.reinicio-dia') }}" class="d-inline" onsubmit="return confirm('¿Reiniciar día? Esto limpiará órdenes residuales de días anteriores en cocina/barra.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Reinicio día</button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        // show only the active order workflow columns for staff (do not include 'cleaned')
        $cols = ['waiting' => 'En cola', 'ready' => 'Lista'];
        $colors = ['waiting' => 'bg-secondary', 'ready' => 'bg-success'];
        $ordersCollection = collect($orders);
    @endphp

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @include('staff.orders._kds_columns', ['useColors' => true])
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    @if(session('order_created_meta'))
        try {
            const meta = @json(session('order_created_meta'));
            // Broadcast immediate update so other tabs/pages pick it up.
            localStorage.setItem('order_created_update', JSON.stringify({t:Date.now(), meta: meta}));
            // If updateOSBCounts is already defined (status bar loaded) apply immediately
            if(window.updateOSBCounts){ window.updateOSBCounts(meta); }
            // Inline counters (if present) update
            const w = document.getElementById('cnt-waiting'); if(w && typeof meta.waiting !== 'undefined') w.textContent = meta.waiting;
            const r = document.getElementById('cnt-ready'); if(r && typeof meta.ready !== 'undefined') r.textContent = meta.ready;
        } catch(e) { console.debug('order_created_meta broadcast error', e); }
    @endif

    // Listen for creation events from other tabs
    window.addEventListener('storage', function(e){
        if(e.key === 'order_created_update'){
            try{
                const payload = JSON.parse(e.newValue);
                if(payload && payload.meta){
                    if(window.updateOSBCounts){ window.updateOSBCounts(payload.meta); }
                    const w = document.getElementById('cnt-waiting'); if(w && typeof payload.meta.waiting !== 'undefined') w.textContent = payload.meta.waiting;
                    const r = document.getElementById('cnt-ready'); if(r && typeof payload.meta.ready !== 'undefined') r.textContent = payload.meta.ready;
                    // If KDS poll function exposed, trigger an early poll to show the new tile sooner
                    if(window.pollKds){ try{ window.pollKds(); }catch(_){ } }
                }
            }catch(err){ console.debug('order_created_update parse error', err); }
        }
    });
});
</script>
@endpush
