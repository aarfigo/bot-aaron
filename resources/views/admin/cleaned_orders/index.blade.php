@extends('layouts.app')

@section('content')
<div id="cleaned-board" class="container">
    <h1 class="mb-4">Órdenes finalizadas</h1>

    <style>
        /* Aesthetic improvements for readability only */
        #cleaned-board .card { border-radius: 12px; box-shadow: 0 8px 22px rgba(0,0,0,0.06); border: 0; }
        #cleaned-board .card-body { padding: 18px; }
        #cleaned-board .meta { color: #6b7280; font-size: .9rem; }
        #cleaned-board .title { font-size: 1.05rem; font-weight: 700; margin-bottom: 6px; }
        #cleaned-board .amounts strong { color: #111; }
        #cleaned-board .actions { text-align: right; }
        @media (max-width: 575px){ #cleaned-board .card-body { flex-direction: column; gap:12px; } #cleaned-board .actions { text-align:left; margin-top:8px; } }
        /* summary styles */
        /* Summary contrast improvements for dark theme */
        #cleaned-board .summary { border-radius: 14px; background:#0f172a; color:#ffffff; padding:16px 20px; box-shadow: 0 12px 28px rgba(0,0,0,0.28); }
        #cleaned-board .summary .row { align-items:center; }
        #cleaned-board .summary .big { font-size:1.6rem; font-weight:800; color:#fff; }
        #cleaned-board .summary .label { font-size:.82rem; text-transform:uppercase; letter-spacing:.06em; opacity:.95; color:#dbeafe; }
        #cleaned-board .summary .pill { display:inline-block; padding:6px 10px; border-radius:100px; background:#0b1220; color:#e5e7eb; font-size:.85rem; border:1px solid rgba(255,255,255,0.12); }
        #cleaned-board .summary .bar { height:10px; background:#101b33; border-radius:999px; overflow:hidden; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06); }
        #cleaned-board .summary .bar > span { display:block; height:100%; }
        #cleaned-board .summary .bar .cash { background:#16a34a; }
        #cleaned-board .summary .bar .pm { background:#2563eb; }
        #cleaned-board .summary .bar .pos { background:#f97316; }
        #cleaned-board .summary .bar .transfer { background:#10b981; }
        #cleaned-board .summary .bar .card { background:#f59e0b; }
        #cleaned-board .summary .small { font-size:.9rem; opacity:.95; color:#e5e7eb; }
        #cleaned-board .summary .small .pill { background:#0b1220; color:#e5e7eb; }
        @media (max-width: 575px){
            #cleaned-board .summary { padding:14px; }
            #cleaned-board .summary .big { font-size:1.4rem; }
        }
        /* Light variant for summaries inside white cards (older date/range panels) */
        #cleaned-board .summary.summary-light { background:#ffffff; color:#111; box-shadow: 0 10px 24px rgba(0,0,0,0.14); }
        #cleaned-board .summary.summary-light .label { color:#374151; opacity:1; }
        #cleaned-board .summary.summary-light .big { color:#111; }
        #cleaned-board .summary.summary-light .small { color:#374151; }
        #cleaned-board .summary.summary-light .pill { background:#f3f4f6; color:#111; border:1px solid rgba(0,0,0,0.08); }
        #cleaned-board .summary.summary-light .bar { background:#e5e7eb; box-shadow:none; }
    </style>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        // Principal: resumen SOLO del día actual
        $today = now()->toDateString();
        $rateVal = (float) (\App\Models\ExchangeRate::forDate($today) ?? (\App\Models\ExchangeRate::orderBy('date','desc')->value('rate') ?? 0));
        $salesToday = \Illuminate\Support\Facades\DB::table('sales_history')->whereDate('order_date',$today)->get();
        $activePaidToday = \Illuminate\Support\Facades\DB::table('tbl_order')
            ->whereDate('order_date',$today)
            ->where('status','<>','cleaned')
            ->whereNotNull('payment_method')
            ->get();
        $combinedToday = collect($salesToday);
        foreach($activePaidToday as $activeOrder){
            if (! $combinedToday->contains('orderID', $activeOrder->orderID)) {
                $combinedToday->push($activeOrder);
            }
        }
        $totalUsd = 0.0; $totalBs = 0.0;
        $pmTotals = [ 'cash' => 0.0, 'pago_movil' => 0.0, 'pos' => 0.0, 'transfer' => 0.0, 'card' => 0.0, 'other' => 0.0 ];
        foreach($combinedToday as $s){
            $usd = (float) ($s->total ?? 0);
            $bs = isset($s->total_bs) && $s->total_bs !== null ? (float) $s->total_bs : ($rateVal ? $usd * $rateVal : 0.0);
            $totalUsd += $usd; $totalBs += $bs;
            $pm = strtolower(trim((string) ($s->payment_method ?? '')));
            if($pm === 'efectivo' || $pm === 'cash'){ $pmTotals['cash'] += $usd; }
            elseif($pm === 'pago_movil' || $pm === 'pagomovil' || $pm === 'pago movil' || $pm === 'pm'){ $pmTotals['pago_movil'] += $usd; }
            elseif($pm === 'punto de venta' || $pm === 'punto_de_venta' || $pm === 'pos' || $pm === 'debito' || $pm === 'débito'){ $pmTotals['pos'] += $usd; }
            elseif($pm === 'transferencia' || $pm === 'transfer'){ $pmTotals['transfer'] += $usd; }
            elseif($pm === 'tarjeta' || $pm === 'card' || $pm === 'credito' || $pm === 'crédito'){ $pmTotals['card'] += $usd; }
            else { $pmTotals['other'] += $usd; }
        }
        $sumForPct = max(0.0001, array_sum($pmTotals));
        $pctCash = round(($pmTotals['cash'] / $sumForPct) * 100);
        $pctPm = round(($pmTotals['pago_movil'] / $sumForPct) * 100);
        $pctPos = round(($pmTotals['pos'] / $sumForPct) * 100);
        $pctTransfer = round(($pmTotals['transfer'] / $sumForPct) * 100);
        $pctCard = round(($pmTotals['card'] / $sumForPct) * 100);
        $pctOther = max(0, 100 - $pctCash - $pctPm - $pctPos - $pctTransfer - $pctCard);
        $fmtUsd = number_format($totalUsd,2);
        $fmtBs = number_format($totalBs,2);
    @endphp

    <div class="summary mb-4">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="label">Venta total del día</div>
                <div class="big mt-1">${{ $fmtUsd }}</div>
                <div class="small">{{ $fmtBs }} Bs @if($rateVal) <span class="pill">Tasa: {{ number_format($rateVal,2) }}</span> @endif</div>
            </div>
            <div class="col-12 col-md-8">
                <div class="label">Métodos de pago</div>
                <div class="bar mt-2 d-flex">
                    <span class="cash" style="width:{{ $pctCash }}%"></span>
                    <span class="pm" style="width:{{ $pctPm }}%"></span>
                    <span class="pos" style="width:{{ $pctPos }}%"></span>
                    <span class="transfer" style="width:{{ $pctTransfer }}%"></span>
                    <span class="card" style="width:{{ $pctCard }}%"></span>
                    <span style="width:{{ $pctOther }}%"></span>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-2 small">
                    <span class="pill">Efectivo: {{ $pctCash }}%</span>
                    <span class="pill">Pago Móvil: {{ $pctPm }}%</span>
                    <span class="pill">Débito (Punto de venta): {{ $pctPos }}%</span>
                    <span class="pill">Transferencia: {{ $pctTransfer }}%</span>
                    <span class="pill">Tarjeta: {{ $pctCard }}%</span>
                    @if($pctOther > 0)<span class="pill">Otros: {{ $pctOther }}%</span>@endif
                </div>
            </div>
        </div>
    </div>

    {{-- Panel secundario: resumen por fecha seleccionada --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.cleaned-orders.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small">Resumen por fecha</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}" />
                </div>
                <div class="col-12 col-md-3">
                    <button class="btn btn-dark">Ver resumen</button>
                </div>
            </form>
            @php
                $chosen = request('date');
                $hasDate = $chosen && preg_match('/^\d{4}-\d{2}-\d{2}$/',$chosen);
                $sumUsd2=0.0; $sumBs2=0.0; $pct2=['cash'=>0,'pago_movil'=>0,'pos'=>0,'transfer'=>0,'card'=>0,'other'=>0]; $rate2=0.0;
                if($hasDate){
                    $rate2 = (float) (\App\Models\ExchangeRate::forDate($chosen) ?? (\App\Models\ExchangeRate::orderBy('date','desc')->value('rate') ?? 0));
                    $rows = \Illuminate\Support\Facades\DB::table('sales_history')->whereDate('order_date',$chosen)->get();
                    $activePaid = \Illuminate\Support\Facades\DB::table('tbl_order')
                        ->whereDate('order_date',$chosen)
                        ->where('status','<>','cleaned')
                        ->whereNotNull('payment_method')
                        ->get();
                    $combined = collect($rows);
                    foreach($activePaid as $activeOrder){
                        if (! $combined->contains('orderID', $activeOrder->orderID)) {
                            $combined->push($activeOrder);
                        }
                    }
                    $pm2 = ['cash'=>0.0,'pago_movil'=>0.0,'pos'=>0.0,'transfer'=>0.0,'card'=>0.0,'other'=>0.0];
                    foreach($combined as $s){
                        $usd=(float)($s->total ?? 0); $bs = isset($s->total_bs)&&$s->total_bs!==null ? (float)$s->total_bs : ($rate2 ? $usd*$rate2 : 0.0);
                        $sumUsd2 += $usd; $sumBs2 += $bs; $pm=strtolower(trim((string)($s->payment_method ?? '')));
                        if($pm==='efectivo'||$pm==='cash'){ $pm2['cash'] += $usd; }
                        elseif($pm==='pago_movil'||$pm==='pagomovil'||$pm==='pago movil'||$pm==='pm'){ $pm2['pago_movil'] += $usd; }
                        elseif($pm==='punto de venta'||$pm==='punto_de_venta'||$pm==='pos'||$pm==='debito'||$pm==='débito'){ $pm2['pos'] += $usd; }
                        elseif($pm==='transferencia'||$pm==='transfer'){ $pm2['transfer'] += $usd; }
                        elseif($pm==='tarjeta'||$pm==='card'||$pm==='credito'||$pm==='crédito'){ $pm2['card'] += $usd; }
                        else { $pm2['other'] += $usd; }
                    }
                    $tot = max(0.0001, array_sum($pm2));
                    $pct2 = [
                        'cash' => round(($pm2['cash']/$tot)*100),
                        'pago_movil' => round(($pm2['pago_movil']/$tot)*100),
                        'pos' => round(($pm2['pos']/$tot)*100),
                        'transfer' => round(($pm2['transfer']/$tot)*100),
                        'card' => round(($pm2['card']/$tot)*100),
                        'other' => max(0, 100 - round(($pm2['cash']/$tot)*100) - round(($pm2['pago_movil']/$tot)*100) - round(($pm2['pos']/$tot)*100) - round(($pm2['transfer']/$tot)*100) - round(($pm2['card']/$tot)*100)),
                    ];
                }
            @endphp
            @if($hasDate)
            <div class="summary summary-light mt-3">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="label">Venta total ({{ $chosen }})</div>
                        <div class="big mt-1">${{ number_format($sumUsd2,2) }}</div>
                        <div class="small">{{ number_format($sumBs2,2) }} Bs @if($rate2) <span class="pill">Tasa: {{ number_format($rate2,2) }}</span> @endif</div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="label">Métodos de pago</div>
                        <div class="bar mt-2 d-flex">
                            <span class="cash" style="width:{{ $pct2['cash'] }}%"></span>
                            <span class="pm" style="width:{{ $pct2['pago_movil'] }}%"></span>
                            <span class="pos" style="width:{{ $pct2['pos'] }}%"></span>
                            <span class="transfer" style="width:{{ $pct2['transfer'] }}%"></span>
                            <span class="card" style="width:{{ $pct2['card'] }}%"></span>
                            <span style="width:{{ $pct2['other'] }}%"></span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2 small">
                            <span class="pill">Efectivo: {{ $pct2['cash'] }}%</span>
                            <span class="pill">Pago Móvil: {{ $pct2['pago_movil'] }}%</span>
                            <span class="pill">Débito (Punto de venta): {{ $pct2['pos'] }}%</span>
                            <span class="pill">Transferencia: {{ $pct2['transfer'] }}%</span>
                            <span class="pill">Tarjeta: {{ $pct2['card'] }}%</span>
                            @if($pct2['other']>0)<span class="pill">Otros: {{ $pct2['other'] }}%</span>@endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Panel terciario: resumen por rango de fechas (acumulado) --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.cleaned-orders.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small">Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}" />
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small">Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}" />
                </div>
                <div class="col-12 col-md-3">
                    <button class="btn btn-dark">Ver acumulado</button>
                </div>
            </form>
            @php
                $from = request('from'); $to = request('to');
                $hasRange = $from && $to && preg_match('/^\d{4}-\d{2}-\d{2}$/',$from) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$to);
                $sumUsdR=0.0; $sumBsR=0.0; $pctR=['cash'=>0,'pago_movil'=>0,'pos'=>0,'transfer'=>0,'card'=>0,'other'=>0]; $rateR=0.0;
                if($hasRange){
                    // Use latest rate as approximation for entries without total_bs
                    $rateR = (float) (\App\Models\ExchangeRate::orderBy('date','desc')->value('rate') ?? 0);
                    $rowsR = \Illuminate\Support\Facades\DB::table('sales_history')
                        ->whereDate('order_date','>=',$from)
                        ->whereDate('order_date','<=',$to)
                        ->get();
                    $activePaidR = \Illuminate\Support\Facades\DB::table('tbl_order')
                        ->whereDate('order_date','>=',$from)
                        ->whereDate('order_date','<=',$to)
                        ->where('status','<>','cleaned')
                        ->whereNotNull('payment_method')
                        ->get();
                    $combinedR = collect($rowsR);
                    foreach($activePaidR as $activeOrder){
                        if (! $combinedR->contains('orderID', $activeOrder->orderID)) {
                            $combinedR->push($activeOrder);
                        }
                    }
                    $pmR = ['cash'=>0.0,'pago_movil'=>0.0,'pos'=>0.0,'transfer'=>0.0,'card'=>0.0,'other'=>0.0];
                    foreach($combinedR as $s){
                        $usd=(float)($s->total ?? 0); $bs = isset($s->total_bs)&&$s->total_bs!==null ? (float)$s->total_bs : ($rateR ? $usd*$rateR : 0.0);
                        $sumUsdR += $usd; $sumBsR += $bs; $pm=strtolower(trim((string)($s->payment_method ?? '')));
                        if($pm==='efectivo'||$pm==='cash'){ $pmR['cash'] += $usd; }
                        elseif($pm==='pago_movil'||$pm==='pagomovil'||$pm==='pago movil'||$pm==='pm'){ $pmR['pago_movil'] += $usd; }
                        elseif($pm==='punto de venta'||$pm==='punto_de_venta'||$pm==='pos'||$pm==='debito'||$pm==='débito'){ $pmR['pos'] += $usd; }
                        elseif($pm==='transferencia'||$pm==='transfer'){ $pmR['transfer'] += $usd; }
                        elseif($pm==='tarjeta'||$pm==='card'||$pm==='credito'||$pm==='crédito'){ $pmR['card'] += $usd; }
                        else { $pmR['other'] += $usd; }
                    }
                    $totR = max(0.0001, array_sum($pmR));
                    $pctR = [
                        'cash' => round(($pmR['cash']/$totR)*100),
                        'pago_movil' => round(($pmR['pago_movil']/$totR)*100),
                        'pos' => round(($pmR['pos']/$totR)*100),
                        'transfer' => round(($pmR['transfer']/$totR)*100),
                        'card' => round(($pmR['card']/$totR)*100),
                        'other' => max(0, 100 - round(($pmR['cash']/$totR)*100) - round(($pmR['pago_movil']/$totR)*100) - round(($pmR['pos']/$totR)*100) - round(($pmR['transfer']/$totR)*100) - round(($pmR['card']/$totR)*100)),
                    ];
                }
            @endphp
            @if($hasRange)
            <div class="summary summary-light mt-3">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="label">Venta total ({{ $from }} → {{ $to }})</div>
                        <div class="big mt-1">${{ number_format($sumUsdR,2) }}</div>
                        <div class="small">{{ number_format($sumBsR,2) }} Bs @if($rateR) <span class="pill">Tasa aprox: {{ number_format($rateR,2) }}</span> @endif</div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="label">Métodos de pago</div>
                        <div class="bar mt-2 d-flex">
                            <span class="cash" style="width:{{ $pctR['cash'] }}%"></span>
                            <span class="pm" style="width:{{ $pctR['pago_movil'] }}%"></span>
                            <span class="pos" style="width:{{ $pctR['pos'] }}%"></span>
                            <span class="transfer" style="width:{{ $pctR['transfer'] }}%"></span>
                            <span class="card" style="width:{{ $pctR['card'] }}%"></span>
                            <span style="width:{{ $pctR['other'] }}%"></span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2 small">
                            <span class="pill">Efectivo: {{ $pctR['cash'] }}%</span>
                            <span class="pill">Pago Móvil: {{ $pctR['pago_movil'] }}%</span>
                            <span class="pill">Débito (Punto de venta): {{ $pctR['pos'] }}%</span>
                            <span class="pill">Transferencia: {{ $pctR['transfer'] }}%</span>
                            <span class="pill">Tarjeta: {{ $pctR['card'] }}%</span>
                            @if($pctR['other']>0)<span class="pill">Otros: {{ $pctR['other'] }}%</span>@endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if(!empty($activePaidOrders) && $activePaidOrders->count())
        <div class="card mb-3 border-warning">
            <div class="card-body">
                <div class="title mb-3">Órdenes pagadas al momento (no finalizadas)</div>
                @foreach($activePaidOrders as $o)
                    @php
                        $usdVal = $o->total ?? 0;
                        $rateLoop = $o->exchange_rate;
                        if(isset($o->total_bs) && $o->total_bs !== null){
                            $bsVal = $o->total_bs;
                        } else {
                            $bsVal = $rateLoop ? ($usdVal * $rateLoop) : null;
                        }
                        $usd = number_format($usdVal,2);
                        $bs = $bsVal ? number_format($bsVal,2) : null;
                        $it = json_decode($o->items,true) ?? [];
                    @endphp
                    <div class="card mb-3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div style="flex:1; min-width:0;">
                                <div class="title">Orden activa #{{ $o->orderID }} — {{ $o->order_date }}</div>
                                <div class="amounts meta">Total: <strong>${{ $usd }}</strong>@if($bs) — <strong>{{ $bs }} Bs</strong>@endif<br>
                                    Estado: {{ $o->status ?? 'N/A' }} — Método: {{ $o->payment_method ?? 'N/D' }}
                                </div>
                                <div class="meta mt-1">Items: {{ count($it) }} artículos</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @forelse($orders as $o)
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div style="flex:1; min-width:0;">
                    <div class="title">Venta #{{ $o->id }} — #{{ $o->orderID }} — {{ $o->order_date }}</div>
                    @php
                        $usdVal = $o->total ?? 0;
                        $rateLoop = \App\Models\ExchangeRate::forDate($o->order_date ?? now()->toDateString());
                        if(isset($o->total_bs) && $o->total_bs !== null){
                            $bsVal = $o->total_bs;
                        } else {
                            $bsVal = $rateLoop ? ($usdVal * $rateLoop) : null;
                        }
                        $usd = number_format($usdVal,2);
                        $bs = $bsVal ? number_format($bsVal,2) : null;
                    @endphp
                    <div class="amounts meta">Total: <strong>${{ $usd }}</strong>@if($bs) — <strong>{{ $bs }} Bs</strong>@endif<br>
                        Finalizado por: {{ optional(DB::table('users')->where('id',$o->cleaned_by)->first())->name ?? 'N/A' }} — <span>Método: {{ $o->payment_method ?? 'N/D' }}</span>
                    </div>
                    <div class="meta mt-1">Items: @php $it = json_decode($o->items,true) ?? []; @endphp {{ count($it) }} artículos</div>
                </div>
                <div class="actions">
                    <a href="{{ route('admin.cleaned-orders.show', $o->id) }}" class="btn btn-sm btn-outline-primary me-2">Ver</a>
                    <form method="POST" action="{{ route('admin.cleaned-orders.destroy', $o->id) }}" style="display:inline" onsubmit="return confirm('Eliminar este registro de venta?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="text-muted">No hay ventas en el historial</div>
    @endforelse
</div>

{{-- Auto-refresh removed to restore previous admin behavior and avoid interfering with actions. --}}
@endsection
