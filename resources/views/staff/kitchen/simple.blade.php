<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Panel Cocina - Simple</title>
    <style>
        body{font-family: Arial, Helvetica, sans-serif; background:#f5f5f5; margin:0; padding:20px}
        .panel{background:#fff; border:2px solid #ccc; padding:0; max-width:1100px; margin:0 auto}
        table{width:100%; border-collapse:collapse}
        thead th{background:#f0f0f0; padding:12px 10px; border-bottom:2px solid #ccc; text-align:left}
        tbody td{Padding:14px 10px; border-bottom:1px solid #e6e6e6; vertical-align:top}
        .order-header{background:#fafafa; font-weight:700; padding:10px; border-top:1px solid #ccc}
        .badge{display:inline-block;padding:4px 8px;border-radius:6px;font-size:0.85rem}
        .badge.waiting{background:#f7c948;color:#6b3b00}
        .btn{display:inline-block;padding:6px 10px;border-radius:6px;border:1px solid #bbb;background:#fff;font-size:0.9rem;margin-left:6px}
        .btn.primary{border-color:#2b7be4;color:#2b7be4}
        .btn.success{border-color:#3aa85a;color:#2f7b3a}
        .btn.danger{border-color:#d9534f;color:#d9534f}
        .small-muted{color:#666;font-size:0.95rem}
        .nowrap{white-space:nowrap}
        @media(max-width:800px){ .panel{padding:0 8px} thead th{font-size:0.9rem} tbody td{font-size:0.95rem} }
    </style>
</head>
<body>
    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th style="width:9%"># de Orden</th>
                    <th style="width:12%">Categoría</th>
                    <th>Nombre del Menú</th>
                    <th style="width:8%">Cantidad</th>
                    <th style="width:24%">Notas</th>
                    <th style="width:8%">Estado</th>
                    <th style="width:16%">Opciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusLabels = ['waiting' => 'waiting', 'ready' => 'ready'];
                @endphp

                @foreach($ordersByStatus as $status => $orders)
                    @foreach($orders as $o)
                        @foreach($o->items as $it)
                            <tr>
                                <td class="nowrap"># {{ $o->orderID }}</td>
                                <td class="small-muted">{{ $it->category ?? '' }}</td>
                                <td>{{ $it->menuItemName }}</td>
                                <td class="nowrap">{{ $it->quantity }}</td>
                                <td class="small-muted">{{ $it->comment ?? ($o->customer_table ?? '') }}</td>
                                <td>
                                                    <span class="badge {{ $status == 'waiting' ? 'waiting' : '' }}">{{ $statusLabels[$status] ?? $status }}</span>
                                                </td>
                                                <td>
                                                    <form method="POST" action="{{ route('staff.orders.status', $o->orderID) }}" style="display:inline">
                                                        @csrf
                                                        <input type="hidden" name="origin" value="kitchen">
                                                        @if($status == 'waiting')
                                                            <input type="hidden" name="status" value="ready">
                                                            <button class="btn primary" type="submit">Listo</button>
                                                        @else
                                                            <input type="hidden" name="status" value="cleaned">
                                                            <button class="btn danger" type="submit">Limpiar</button>
                                                        @endif
                                                    </form>
                                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
