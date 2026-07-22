@extends('layouts.app')

@section('content')
<div class="container">
    <h1> Mesa {{ $number }} @if($table && !empty($table->name)) <small class="text-muted">{{ $table->name }}</small>@endif </h1>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
        <a href="{{ route('staff.tables.index') }}" class="btn btn-outline-secondary">Volver</a>
        @if($table)
            <button id="toggle-edit-table" type="button" class="btn btn-sm btn-outline-secondary">Editar mesa</button>
            <form method="POST" action="{{ route('staff.tables.destroy', $table->number) }}" class="m-0" onsubmit="return confirm('¿Eliminar esta mesa?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Borrar mesa</button>
            </form>
        @endif
    </div>

    @if($table)
        <div id="edit-table-panel" class="card mb-3" style="display:none;">
            <div class="card-body">
                <form method="POST" action="{{ route('staff.tables.update', $number) }}" class="row g-2 align-items-end" onsubmit="return confirm('¿Confirmar actualización de la mesa?');">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-3">
                        <label for="table-number-input" class="form-label small">Número de mesa</label>
                        <input id="table-number-input" type="number" name="number" class="form-control form-control-sm" value="{{ $table->number }}" min="1" required />
                    </div>
                    <div class="col-md-4">
                        <label for="table-name-input" class="form-label small">Nombre de mesa</label>
                        <input id="table-name-input" type="text" name="name" class="form-control form-control-sm" value="{{ $table->name }}" placeholder="Nombre (opcional)" />
                    </div>
                    <div class="col-md-5 text-end">
                        <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                        <button id="cancel-edit-table" type="button" class="btn btn-sm btn-secondary">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @php $grandTotal = 0; $rate = \App\Models\ExchangeRate::forDate(now()->toDateString()); @endphp
    @if(count($orders))
        <div id="table-panel">
        <div class="card mb-3">
            <div class="card-body">
                <h5>Órdenes activas ({{ count($orders) }})</h5>
                <div class="list-group">
                    @foreach($orders as $o)
                        @php $grandTotal += $o->total; @endphp
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>#{{ $o->orderID }}</strong> — <small class="text-muted">{{ $o->order_date }}</small>
                                    <div>Estado: {{ $o->status }}</div>
                                </div>
                                <div class="text-end">
                                    <div>Total: <strong>
                                        @if($rate)
                                            ${{ number_format($o->total,2) }} / {{ number_format($o->total * $rate,2) }} Bs
                                        @else
                                            ${{ number_format($o->total,2) }}
                                        @endif
                                    </strong></div>
                                    <div class="small text-muted">Creado por: {{ $o->created_by }}</div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <strong>Items</strong>
                                <ul>
                                    @foreach($orderDetails[$o->orderID] ?? [] as $it)
                                        <li>{{ $it->menuItemName }} x{{ $it->quantity }} — {{ number_format($it->price,2) }} {{ $it->comment ? ' — '.$it->comment : '' }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5>Total mesa: <strong>
                        @if($rate)
                            ${{ number_format($grandTotal,2) }} / {{ number_format($grandTotal * $rate,2) }} Bs
                            <div class="small text-muted">Tasa: {{ number_format($rate,4) }} Bs/US$</div>
                        @else
                            ${{ number_format($grandTotal,2) }}
                        @endif
                    </strong></h5>
                </div>
                <div>
                    <form method="post" action="{{ route('staff.tables.charge', $number) }}" class="d-flex flex-wrap gap-2 align-items-center">
                        @csrf

                        {{-- Optional customer info --}}
                        <input name="nombre" type="text" class="form-control form-control-sm me-2" placeholder="Nombre del cliente (opcional)" value="{{ old('nombre') }}" />
                        <input name="cedula" type="text" class="form-control form-control-sm me-2" placeholder="Cédula / ID (opcional)" value="{{ old('cedula') }}" />

                        <select name="payment_method" class="form-control form-control-sm me-2">
                            <option value="">Método de pago (opcional)</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="pago_movil">Pago móvil</option>
                        </select>
                        <input name="reference" type="text" class="form-control form-control-sm me-2" placeholder="Ref. / Descripción (opcional)" />
                        <button class="btn btn-dark" type="submit" onclick="return confirm('Confirmar cobro de la mesa?')">Cobrar mesa</button>
                    </form>
                </div>
            </div>
        </div>
        </div>
    @else
        <div id="table-panel"><div class="alert alert-info">No hay órdenes activas para esta mesa.</div></div>
    @endif

    @push('scripts')
    <script>
    (function(){
        const selector = '#table-panel';
        const intervalMs = 1000; // 1s live updates
        async function refreshPanel(){
            try{
                const active = document.activeElement;
                if(active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable)) return;
                const res = await fetch(window.location.href, { credentials: 'same-origin' });
                const text = await res.text();
                const doc = new DOMParser().parseFromString(text, 'text/html');
                const newPanel = doc.querySelector(selector);
                const oldPanel = document.querySelector(selector);
                if(!newPanel || !oldPanel) return;
                // replace only inner content to avoid re-running scripts globally
                oldPanel.replaceWith(newPanel);
            }catch(e){ console.debug('Table panel refresh error', e); }
        }
        setInterval(refreshPanel, intervalMs);
    })();

    document.addEventListener('DOMContentLoaded', function(){
        const toggle = document.getElementById('toggle-edit-table');
        const panel = document.getElementById('edit-table-panel');
        const cancel = document.getElementById('cancel-edit-table');
        if(toggle && panel){
            toggle.addEventListener('click', function(){
                panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
            });
        }
        if(cancel && panel){
            cancel.addEventListener('click', function(){
                panel.style.display = 'none';
            });
        }
    });
    </script>
    @endpush
</div>
@endsection
