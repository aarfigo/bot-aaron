@extends('layouts.app')

@section('content')
<h1>Panel de control</h1>
<p>Bienvenido empleado.</p>

@php
	$waiting = \Illuminate\Support\Facades\DB::table('tbl_order')->where('status','waiting')->count();
	$ready = \Illuminate\Support\Facades\DB::table('tbl_order')->where('status','ready')->count();
@endphp

<div class="row g-3 mt-3">
	<div class="col-md-6">
		<div class="card text-center"><div class="card-body"><div class="small text-muted">En cola</div><div class="h3"><span id="cnt-waiting">{{ $waiting }}</span></div></div></div>
	</div>
	<div class="col-md-6">
		<div class="card text-center"><div class="card-body"><div class="small text-muted">Lista</div><div class="h3"><span id="cnt-ready">{{ $ready }}</span></div></div></div>
	</div>
</div>

@push('scripts')
<script>
// Poll the lightweight counts endpoint every 1s and update the dashboard counters
(function(){
	const url = '{{ route('staff.orders.counts') }}';
	async function refreshCounts(){
		try{
			const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
			if(!res.ok) return;
			const j = await res.json();
			if(j && typeof j.waiting !== 'undefined'){
				const w = document.getElementById('cnt-waiting'); if(w) w.textContent = j.waiting;
			}
			if(j && typeof j.ready !== 'undefined'){
				const r = document.getElementById('cnt-ready'); if(r) r.textContent = j.ready;
			}
		}catch(e){ /* ignore transient errors */ }
	}
	setInterval(refreshCounts, 1000);
})();
</script>
@endpush

 

@endsection
