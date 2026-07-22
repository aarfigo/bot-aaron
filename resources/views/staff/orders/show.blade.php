@extends('layouts.app')

@section('content')
<div class="container">
    <style>
        /* Small, local styles to make the order show view cleaner on mobile */
        .order-card { border-radius: .5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04); overflow: hidden; }
        .order-header { padding: .75rem 1rem; background: #fff; }
        .order-meta { font-size: .95rem; color: #222; }
        .order-meta .meta-row { display:flex; gap:.5rem; align-items:center; flex-wrap:wrap; }
        .order-meta .meta-row strong { min-width:72px; color:#333; }
        .status-badge { padding:.45rem .6rem; border-radius:.375rem; font-weight:600; }
        .items-section { padding: .75rem 0; }
        .items-section .table { margin-bottom:0; }
        @media (max-width:575.98px){
            .order-header h1 { font-size:1.05rem; margin-bottom:.4rem; }
            .order-meta { font-size:.9rem; }
            .status-badge { font-size:.85rem; }
        }
    </style>

    <div class="card order-card mb-3">
        <div class="order-header">
            <h1 class="h5 mb-2">Pedido #{{ $order->orderID }}</h1>
            @php
                $status = $order->status ?? '';
                $statusClass = match($status) {
                    'waiting' => 'bg-secondary text-white',
                    'ready' => 'bg-success text-white',
                    'cleaned' => 'bg-dark text-white',
                    default => 'bg-light text-dark'
                };
                // exchange rate for the order date (Bs per 1 USD)
                $rate = \App\Models\ExchangeRate::forDate($order->order_date ?? now()->toDateString());
            @endphp
            <div class="order-meta">
                <div class="meta-row mb-1"><strong>Mesa:</strong> @if(!empty($order->table_name)) {{ $order->table_name }} @elseif(!empty(optional($order)->table_number)) {{ 'Mesa ' . optional($order)->table_number }} @elseif(trim((string)(optional($order)->customer_table ?? '')) !== '') {{ 'Mesa ' . optional($order)->customer_table }} @else N/A @endif</div>
                <div class="meta-row mb-1"><strong>Fecha:</strong> {{ $order->order_date }} <span style="flex:1"></span><span class="status-badge {{ $statusClass }}">{{ ucfirst($status) }}</span></div>
                <div class="meta-row"><strong>Total:</strong> <span class="fw-bold">
                    @if($rate)
                        ${{ number_format($order->total,2) }} / {{ number_format($order->total * $rate,2) }} Bs
                        <span class="small text-muted">(Tasa: {{ number_format($rate,4) }} Bs/US$)</span>
                    @else
                        ${{ number_format($order->total,2) }}
                    @endif
                </span></div>
                @php $role = optional(auth()->user())->role; @endphp
                @if(($order->status ?? '') !== 'cleaned' && in_array($role, ['mesero','admin']))
                    <div class="mt-2"><a href="{{ route('staff.orders.edit', $order->orderID) }}" class="btn btn-sm btn-dark">Editar pedido</a></div>
                @endif
            </div>
        </div>
        <div class="card-body items-section">
            <h4 class="h6 mb-2">Items</h4>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Producto</th><th style="width:72px">Cantidad</th><th>Comentario</th><th style="width:90px;text-align:right">Precio</th></tr></thead>
                    <tbody>
                    @foreach($items as $it)
                        <tr>
                            <td>{{ $it->menuItemName }}</td>
                            <td>{{ $it->quantity }}</td>
                            <td>{{ $it->comment }}</td>
                            <td class="text-end">
                                @if($rate)
                                    ${{ number_format($it->price,2) }} / {{ number_format($it->price * $rate,2) }} Bs
                                @else
                                    ${{ number_format($it->price,2) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <h4 class="mt-3">Actualizar estado</h4>
    @php $role = optional(auth()->user())->role; @endphp
                @if(in_array($role, ['cocina_barra', 'admin']))
        <form method="POST" action="{{ route('staff.orders.status', $order->orderID) }}">
            @csrf
            <div class="mb-3">
                <select name="status" class="form-control" style="width:200px;">
                    @php
                        // Only two states for the mesero/cocina flow: waiting and ready.
                        $states = ['waiting' => 'En cola', 'ready' => 'Lista'];
                    @endphp
                    @foreach($states as $key => $label)
                        <option value="{{ $key }}" {{ $order->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">Actualizar estado</button>
        </form>
    @else
        <div class="alert alert-secondary">Solo el personal de cocina puede actualizar el estado de la orden.</div>
    @endif

</div>
@endsection
