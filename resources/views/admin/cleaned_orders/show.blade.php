@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Detalle de venta <small class="text-muted">#{{ $sale->id }}</small></h1>
        <div class="text-end">
            <div class="fw-bold">#{{ $sale->orderID }}</div>
        </div>
    </div>

    <div class="mb-2 text-end">
        <a href="{{ route('admin.cleaned-orders.print', $sale->id) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Ver/Imprimir factura</a>
        <a href="{{ route('admin.cleaned-orders.print', $sale->id) }}?autoprint=1" target="_blank" rel="noopener" class="btn btn-primary btn-sm ms-2">Imprimir ahora</a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-muted small">Fecha</div>
                            <div>{{ $sale->order_date }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Finalizado por</div>
                            <div>{{ optional(DB::table('users')->where('id',$sale->cleaned_by)->first())->name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <hr />

                    @php
                        $rate = \App\Models\ExchangeRate::forDate($sale->order_date ?? now()->toDateString());
                        // fallback to latest known rate if no rate exists for the order date
                        if(!$rate){
                            $rate = \App\Models\ExchangeRate::orderBy('date','desc')->value('rate');
                        }
                        // ensure numeric or null
                        $rate = $rate ? (float)$rate : null;
                    @endphp
                    @php
                        $methodLabels = [
                            'cash' => 'Efectivo',
                            'pago_movil' => 'Pago Móvil',
                            'pos' => 'Débito (Punto de venta)',
                            'card' => 'Tarjeta',
                            'transfer' => 'Transferencia',
                            'other' => 'Otro',
                        ];
                        $paymentLabel = $methodLabels[$sale->payment_method] ?? ($sale->payment_method ? ucfirst($sale->payment_method) : 'N/D');
                    @endphp
                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="text-muted small">Cliente</div>
                            <div>{{ $sale->nombre ?? 'N/D' }} <span class="text-muted">({{ $sale->cedula ?? 'N/D' }})</span></div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="text-muted small">Total</div>
                                @php
                                    $usdVal = $sale->total_usd ?? $sale->total ?? 0;
                                    if(isset($sale->total_bs) && $sale->total_bs !== null){
                                        $bsVal = $sale->total_bs;
                                    } else {
                                        $bsVal = $rate ? ($usdVal * $rate) : null;
                                    }
                                @endphp
                                <div class="h5 fw-bold">
                                    @if($bsVal)
                                        {{ number_format($bsVal,2) }} Bs <small class="text-muted">— ${{ number_format($usdVal,2) }}</small>
                                    @else
                                        ${{ number_format($usdVal,2) }}
                                    @endif
                                </div>
                        </div>
                    </div>
                    <div class="row align-items-center mt-3">
                        <div class="col-6">
                            <div class="text-muted small">Método de pago</div>
                            <div>{{ $paymentLabel }}</div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="text-muted small">Referencia</div>
                            <div>{{ $sale->reference ?? 'N/D' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            {{-- Only admin routes reach this view; provide a small form to set/change payment method and edit cliente info --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.cleaned-orders.payment', $sale->id) }}" class="row g-2 align-items-center">
                        @csrf
                        <div class="col-12">
                            <label for="payment_method" class="form-label small">Método de pago</label>
                            <select name="payment_method" id="payment_method" class="form-select">
                                <option value="" {{ $sale->payment_method ? '' : 'selected' }}>-- Sin especificar --</option>
                                <option value="cash" {{ $sale->payment_method=='cash' ? 'selected' : '' }}>Efectivo</option>
                                <option value="pago_movil" {{ $sale->payment_method=='pago_movil' ? 'selected' : '' }}>Pago Móvil</option>
                                <option value="pos" {{ in_array($sale->payment_method, ['pos','debito','débito']) ? 'selected' : '' }}>Débito (Punto de venta)</option>
                                <option value="card" {{ $sale->payment_method=='card' ? 'selected' : '' }}>Tarjeta</option>
                                <option value="transfer" {{ $sale->payment_method=='transfer' ? 'selected' : '' }}>Transferencia</option>
                                <option value="other" {{ $sale->payment_method=='other' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-12">
                            <label class="form-label small">Nombre cliente</label>
                            <input type="text" name="nombre" class="form-control form-control-sm" value="{{ old('nombre', $sale->nombre ?? '') }}" />
                        </div>

                        <div class="col-12 col-md-12">
                            <label class="form-label small">Cédula / ID</label>
                            <input type="text" name="cedula" class="form-control form-control-sm" value="{{ old('cedula', $sale->cedula ?? '') }}" />
                        </div>

                        <div class="col-12 col-md-12">
                            <label class="form-label small">Referencia de pago</label>
                            <input type="text" name="reference" class="form-control form-control-sm" value="{{ old('reference', $sale->reference ?? '') }}" />
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary w-100">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif



    <h4>Productos vendidos</h4>
    <table class="table table-sm">
        <thead>
            <tr><th>Código</th><th>Producto</th><th>Cantidad</th><th>Precio</th></tr>
        </thead>
        <tbody>
        @foreach($items as $it)
            @php
                $price = (float)($it['price'] ?? 0);
                $quantity = intval($it['quantity'] ?? 0);
                $subtotal = $price * $quantity;
                $priceBs = $rate ? $price * $rate : null;
                $subtotalBs = $rate ? $subtotal * $rate : null;
            @endphp
            <tr>
                <td>{{ $it['itemID'] ?? ($it['itemID'] ?? '') }}</td>
                <td>{{ $it['menuItemName'] ?? '—' }}</td>
                <td>{{ $quantity }}</td>
                <td class="text-end">
                    <div>{{ number_format($price,2) }} USD</div>
                    <div class="small text-muted">@if($priceBs) {{ number_format($priceBs,2) }} Bs @else - Bs @endif</div>
                </td>
                
            </tr>
        @endforeach
        </tbody>
    </table>

    <a href="{{ route('admin.cleaned-orders.index') }}" class="btn btn-secondary">Volver al historial</a>
</div>
@endsection
