@php
    // Prefill totals from DB so initial render reflects real availability before polling.
    $totalTables = \Illuminate\Support\Facades\Schema::hasTable('tables')
        ? \Illuminate\Support\Facades\DB::table('tables')->count()
        : 0;
    // Active tables = mesas con órdenes waiting/ready, incluso si cocina ya las marcó localmente.
    $today = now()->toDateString();
    $activeTablesPrefill = 0; $waitingPrefill = 0; $readyPrefill = 0;
    if($totalTables > 0 && \Illuminate\Support\Facades\Schema::hasTable('tbl_order')){
        $baseQ = \Illuminate\Support\Facades\DB::table('tbl_order')
            ->whereDate('order_date', $today)
            ->whereNotNull('customer_table');
        $activeTablesPrefill = (clone $baseQ)->whereIn('status',[ 'waiting','ready' ])->distinct()->count('customer_table');
        $waitingPrefill = (clone $baseQ)->where('status','waiting')->count();
        $readyPrefill = (clone $baseQ)->where('status','ready')->count();
    }
    $freeTablesPrefill = max(0, $totalTables - $activeTablesPrefill);
    $metrics = [
        'waiting' => $waitingPrefill,
        'ready' => $readyPrefill,
        'to_charge' => $readyPrefill,
        'active_tables' => $activeTablesPrefill,
        'free_tables' => $freeTablesPrefill,
        'total_tables' => $totalTables,
    ];
@endphp
<div id="order-status-bar" class="order-status-bar mt-3 mb-3">
    <div class="osb-wrapper">
        <div class="osb-item"><span class="osb-label">En cola</span> <span id="osb-waiting" class="osb-value">{{ $metrics['waiting'] }}</span></div>
        <div class="osb-item"><span class="osb-label">Listas</span> <span id="osb-ready" class="osb-value">{{ $metrics['ready'] }}</span></div>
        <div class="osb-item"><span class="osb-label">Por cobrar</span> <span id="osb-charge" class="osb-value">{{ $metrics['to_charge'] }}</span></div>
        <div class="osb-item"><span class="osb-label">Mesas activas (consumiendo)</span> <span id="osb-active-tables" class="osb-value">{{ $metrics['active_tables'] }}{{ $metrics['total_tables'] ? ' / '.$metrics['total_tables'] : '' }}</span></div>
        <div class="osb-item"><span class="osb-label">Mesas libres (disponibles)</span> <span id="osb-free-tables" class="osb-value">{{ $metrics['free_tables'] }} / {{ $metrics['total_tables'] }}</span></div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const url = '{{ route('staff.orders.metrics') }}';
    const baseInterval = 1500; // intervalo base
    function nextInterval(){ return baseInterval + Math.floor(Math.random()*350); } // pequeño jitter evita sincronización
    // Definir updateOSBCounts ANTES de la primera petición para permitir actualizaciones inmediatas
    window.updateOSBCounts = function(partial){
        try{
            if(partial){
                if(typeof partial.waiting !== 'undefined'){ const el = document.getElementById('osb-waiting'); if(el) el.textContent = partial.waiting; }
                if(typeof partial.ready !== 'undefined'){ const el = document.getElementById('osb-ready'); if(el) el.textContent = partial.ready; }
                if(typeof partial.to_charge !== 'undefined'){ const el = document.getElementById('osb-charge'); if(el) el.textContent = partial.to_charge; }
                if(typeof partial.active_tables !== 'undefined' && typeof partial.total_tables !== 'undefined'){ const el = document.getElementById('osb-active-tables'); if(el) el.textContent = `${partial.active_tables} / ${partial.total_tables}`; }
                if(typeof partial.free_tables !== 'undefined' && typeof partial.total_tables !== 'undefined'){ const el = document.getElementById('osb-free-tables'); if(el) el.textContent = `${partial.free_tables} / ${partial.total_tables}`; }
            }
        }catch(_){ }
    };
    async function pollMetrics(){
        try{
            const res = await fetch(url, { credentials:'same-origin' });
            if(!res.ok) return;
            const json = await res.json();
            // Update simple counters
            const basicMap = {
                'osb-waiting': json.waiting,
                'osb-ready': json.ready,
                'osb-charge': json.to_charge,
            };
            Object.entries(basicMap).forEach(([id,val])=>{ const el = document.getElementById(id); if(el) el.textContent = val; });
            // Active tables as active / total
            const activeEl = document.getElementById('osb-active-tables');
            if(activeEl){ activeEl.textContent = `${json.active_tables} / ${json.total_tables ?? '?'}`; }
            // Free tables displayed as free / total
            const freeEl = document.getElementById('osb-free-tables');
            if(freeEl){ freeEl.textContent = `${json.free_tables} / ${json.total_tables ?? '?'}`; }
            // broadcast to other tabs (optional)
            try{ localStorage.setItem('osb_metrics_update', JSON.stringify({t:Date.now(), metrics:json})); }catch(_){ }
        }catch(e){ /* silent */ }
    }
    // Poll con jitter: cada ciclo programa el siguiente
    function schedulePoll(){ setTimeout(()=>{ pollMetrics().finally(schedulePoll); }, nextInterval()); }
    pollMetrics().finally(schedulePoll);
    window.addEventListener('storage', function(e){
        try{
            if(e.key === 'osb_metrics_update'){
                const payload = JSON.parse(e.newValue); if(payload && payload.metrics){ updateOSBCounts(payload.metrics); }
            } else if(e.key === 'kds_counts_update'){
                const payload = JSON.parse(e.newValue); if(payload && payload.counts){ const c = payload.counts; updateOSBCounts({ waiting:c.waiting, ready:c.ready, to_charge:c.ready }); }
            }
        }catch(_){ }
    });
})();
</script>
<style>
    .order-status-bar { background:#121212; border-radius:14px; padding:10px 14px; box-shadow:0 8px 24px rgba(0,0,0,0.38); }
    .order-status-bar .osb-wrapper { display:flex; flex-wrap:wrap; gap:14px; }
    .order-status-bar .osb-item { display:flex; flex-direction:column; min-width:110px; }
    .order-status-bar .osb-label { font-size:.75rem; text-transform:uppercase; letter-spacing:.05em; opacity:.75; }
    .order-status-bar .osb-value { font-size:1.2rem; font-weight:700; }
    @media(max-width:720px){ .order-status-bar .osb-wrapper { gap:10px; } .order-status-bar .osb-item{ min-width:92px; } }
</style>
@endpush