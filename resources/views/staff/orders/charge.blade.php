@extends('layouts.app')

@section('content')
<div class="container">
    <h1> Cobrar orden #{{ $order->orderID }} </h1>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="mb-3">
        <a href="{{ route('staff.orders.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <strong>#{{ $order->orderID }}</strong> — <small class="text-muted">{{ $order->order_date }}</small>
                    <div>Estado: {{ $order->status }}</div>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Total:</div>
                    <div class="h4">
                        @if($rate)
                            ${{ number_format($order->total,2) }} / {{ number_format($order->total * $rate,2) }} Bs
                        @else
                            ${{ number_format($order->total,2) }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <strong>Items</strong>
                <ul>
                    @foreach($items as $it)
                        <li>{{ $it->menuItemName }} x{{ $it->quantity }} — {{ number_format($it->price,2) }} @if($it->comment) — {{ $it->comment }} @endif</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5>Total a cobrar: <strong>
                    @if($rate)
                        ${{ number_format($order->total,2) }} / {{ number_format($order->total * $rate,2) }} Bs
                    @else
                        ${{ number_format($order->total,2) }}
                    @endif
                </strong></h5>
            </div>
            <div>
                <form method="post" action="{{ route('staff.orders.finalize', $order->orderID) }}" class="d-flex flex-wrap gap-2 align-items-center">
                    @csrf
                    <input type="hidden" name="origin" value="mesero" />
                    <input name="nombre" type="text" class="form-control form-control-sm me-2" placeholder="Nombre del cliente (opcional)" value="{{ old('nombre', $order->customer_name ?? '') }}" />
                    <input name="cedula" type="text" class="form-control form-control-sm me-2" placeholder="Cédula / ID (opcional)" value="{{ old('cedula', $order->customer_cedula ?? '') }}" />
                    <select name="payment_method" class="form-control form-control-sm me-2">
                        <option value="" {{ old('payment_method', $order->payment_method ?? '') == '' ? 'selected' : '' }}>Método de pago (opcional)</option>
                        <option value="cash" {{ old('payment_method', $order->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Efectivo</option>
                        <option value="pago_movil" {{ old('payment_method', $order->payment_method ?? '') == 'pago_movil' ? 'selected' : '' }}>Pago móvil</option>
                        <option value="pos" {{ old('payment_method', $order->payment_method ?? '') == 'pos' ? 'selected' : '' }}>Débito (Punto de venta)</option>
                        <option value="card" {{ old('payment_method', $order->payment_method ?? '') == 'card' ? 'selected' : '' }}>Tarjeta</option>
                        <option value="transfer" {{ old('payment_method', $order->payment_method ?? '') == 'transfer' ? 'selected' : '' }}>Transferencia</option>
                        <option value="other" {{ old('payment_method', $order->payment_method ?? '') == 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                    <input name="reference" type="text" class="form-control form-control-sm me-2" placeholder="Ref. / Descripción (opcional)" value="{{ old('reference', $order->payment_reference ?? '') }}" />
                    <button class="btn btn-dark" type="submit" onclick="return confirm('Confirmar cobro de la orden?')">Cobrar orden</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
