@php
    // This fragment expects $ordersCollection to be provided (collection of orders)
    $ordersCollection = $ordersCollection ?? collect($orders);
    $allOrders = $ordersCollection->sortBy('orderID');
@endphp
<div class="kds-grid-container">
    <div class="kds-tiles-grid">
        @if($allOrders->isEmpty())
            <div class="text-muted">No hay órdenes</div>
        @else
            @foreach($allOrders as $o)
                @php
                    // Use preloaded items map (avoids N+1 queries). Falls back to empty collection if not present.
                    $orderItems = isset($orderItemsMap) ? ($orderItemsMap[$o->orderID] ?? collect()) : collect();
                    $statusKey = $o->status ?? $o->state ?? $o->order_status ?? '';
                    $statusClass = preg_replace('/[^a-z0-9_-]/i','', (string) $statusKey);
                @endphp
                <div class="kds-tile state-{{ $statusClass }}" data-order-id="{{ $o->orderID }}">
                    <div>
                        @php
                            $kdsTitle = trim(optional($o)->customer_name ?? '');
                            $tableLabel = null;
                            if(!empty($o->table_name)){
                                $tableLabel = $o->table_name;
                            } elseif(isset($o->table_number) && !empty($o->table_number)){
                                $tableLabel = 'Mesa ' . $o->table_number;
                            } elseif(!empty($o->customer_table)){
                                $tableLabel = 'Mesa ' . $o->customer_table;
                            }
                        @endphp
                        <div class="kds-tile-head">
                            @if($kdsTitle !== '')
                                {{ $kdsTitle }}@if($tableLabel) · <small class="text-muted">{{ $tableLabel }}</small>@endif
                            @else
                                {{ 'Orden #' . $o->orderID }}@if($tableLabel) · <small class="text-muted">{{ $tableLabel }}</small>@endif
                            @endif
                        </div>
                        @php $commentedItems = $orderItems->filter(fn($it) => !empty($it->comment))->count(); @endphp
                        <div class="kds-tile-items mt-2">
                            @foreach($orderItems->take(6) as $it)
                                <div class="line">
                                    <div class="item-main">{{ $it->menuItemName }} <span class="text-muted">x{{ $it->quantity }}</span></div>
                                    @if(!empty($it->comment))
                                        <div class="item-comment">{{ $it->comment }}</div>
                                    @endif
                                </div>
                            @endforeach
                            @if($orderItems->count() > 6)
                                <div class="text-muted">+{{ $orderItems->count()-6 }} más</div>
                            @endif
                            @if($commentedItems > 0)
                                <div class="text-muted small mt-2">Comentarios en {{ $commentedItems }} ítem{{ $commentedItems !== 1 ? 's' : '' }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="kds-tile-footer">
                        @php $role = optional(Auth::user())->role; @endphp
                        <div class="kds-status-row">
                            @if(in_array($role, ['cocina_barra','admin']))
                                <div class="kds-status-group" role="group" aria-label="Estado">
                                    <button type="button" class="kds-status-btn" data-status="waiting" data-url="{{ route('staff.orders.status', $o->orderID) }}" title="Marcar en cola">
                                        <span class="kds-tile-dot red"></span>
                                    </button>
                                    <button type="button" class="kds-status-btn" data-status="ready" data-url="{{ route('staff.orders.status', $o->orderID) }}" title="Marcar lista">
                                        <span class="kds-tile-dot green"></span>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="kds-actions">
                            <a href="{{ route('staff.orders.show', $o->orderID) }}" class="btn btn-sm btn-outline-secondary" style="color:#ffffff !important;">Ver</a>
                            @if(in_array($role, ['cocina_barra','admin']))
                                <button type="button" class="btn btn-sm btn-dark kds-hide-local ms-1" style="color:#ffffff !important;" data-order-id="{{ $o->orderID }}">Limpiar</button>
                            @endif
                            @if(($statusKey ?? '') !== 'cleaned' && in_array($role, ['mesero','admin']))
                                <a href="{{ route('staff.orders.edit', $o->orderID) }}" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Editar pedido</a>
                            @endif
                            @if($statusKey === 'ready' && (in_array($role, ['mesero','admin']) ))
                                @php $table_num = isset($o->table_number) ? $o->table_number : ($o->customer_table ?? null); @endphp
                                @if(!empty($table_num))
                                    <a href="{{ route('staff.tables.charge.view', $table_num) }}" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Cobrar</a>
                                @else
                                    <a href="{{ route('staff.orders.charge', $o->orderID) }}" class="btn btn-sm btn-dark ms-1" style="color:#ffffff !important;">Cobrar</a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
