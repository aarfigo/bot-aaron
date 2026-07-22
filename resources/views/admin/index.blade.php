@extends('layouts.app')

@section('content')
<h1>Panel de control</h1>
<p>Bienvenido administrador.</p>

<div class="mb-3">
	<a href="{{ route('staff.orders.index') }}" class="btn btn-outline-primary me-2">Ver órdenes</a>
	<a href="{{ route('staff.kitchen.index') }}" class="btn btn-primary">Ir a panel de cocina</a>
</div>

@php
	// Scope to today's work to keep admin dashboard consistent with staff views
	$today = \Carbon\Carbon::now()->toDateString();
	$waiting = \Illuminate\Support\Facades\DB::table('tbl_order')->where('status','waiting')
		->whereDate('order_date', $today)
		->count();
	$ready = \Illuminate\Support\Facades\DB::table('tbl_order')->where('status','ready')
		->whereDate('order_date', $today)
		->count();
	$salesCount = \Illuminate\Support\Facades\DB::table('sales_history')->count();
	// current exchange rate (BS per 1 USD) for today
	$exchangeRate = \App\Models\ExchangeRate::where('date', '<=', $today)->orderBy('date','desc')->value('rate');
@endphp

<div class="row g-3 mt-3">
	<div class="col-md-3">
		<div class="card text-center"><div class="card-body"><div class="small text-muted">En cola</div><div id="admin-waiting-count" class="h3">{{ $waiting }}</div></div></div>
	</div>
	<div class="col-md-3">
		<div class="card text-center"><div class="card-body"><div class="small text-muted">Lista</div><div id="admin-ready-count" class="h3">{{ $ready }}</div></div></div>
	</div>
	<div class="col-md-3">
		<div class="card text-center"><div class="card-body"><div class="small text-muted">Historial ventas</div><div class="h3">{{ $salesCount }}</div></div></div>
	</div>
	<div class="col-md-3">
		<div class="card text-center">
			<div class="card-body">
				<div class="small text-muted">Tipo de cambio (Bs / USD)</div>
				<div class="h5">{{ $exchangeRate ? number_format($exchangeRate,2) : 'N/D' }}</div>
				<div class="mt-2">
					<form method="POST" action="{{ route('admin.exchange-rate.store') }}" class="d-flex gap-2 justify-content-center">
						@csrf
						<input type="hidden" name="date" value="{{ $today }}" />
						<input name="rate" type="text" class="form-control form-control-sm" placeholder="Bs por USD" style="width:120px" />
						<button class="btn btn-sm btn-primary">Guardar</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Static exchange button under counters for admin dashboard (in-flow) -->
<div class="mt-3 mb-4 admin-exchange-wrapper">
	<div class="exchange-toggle-fixed">
		<a href="{{ route('admin.exchange-rate.index') }}" class="btn btn-primary exchange-btn" title="Tasa del día (Bs por USD)">Bs</a>
		@if($exchangeRate)
			<div class="small text-muted mt-1">Tasa: {{ number_format($exchangeRate,2) }} Bs</div>
		@endif
	</div>
</div>

@endsection

@push('scripts')
<script>
(function(){
	const metricsUrl = '{{ route('staff.orders.metrics') }}';
	function render(m){
		try{
			if(typeof m.waiting !== 'undefined'){ const el = document.getElementById('admin-waiting-count'); if(el) el.textContent = m.waiting; }
			if(typeof m.ready !== 'undefined'){ const el = document.getElementById('admin-ready-count'); if(el) el.textContent = m.ready; }
		}catch(_){ }
	}
	async function poll(){
		try{ const r = await fetch(metricsUrl, { credentials:'same-origin' }); if(!r.ok) return; const j = await r.json(); render(j); }
		catch(_){ }
	}
	setInterval(poll, 2000);
	window.addEventListener('storage', function(e){
		try{
			if(e.key === 'osb_metrics_update'){ const p = JSON.parse(e.newValue); if(p && p.metrics) render(p.metrics); }
			else if(e.key === 'kds_counts_update'){ const p = JSON.parse(e.newValue); if(p && p.counts){ render({ waiting:p.counts.waiting, ready:p.counts.ready }); } }
		}catch(_){ }
	});
})();
</script>
@endpush
