<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal Cliente</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=vt323:400&display=swap" rel="stylesheet">
    <style>
        :root {
            /* NUEVA PALETA DE COLORES (FONDO CLARO RETRO) */
            --bg: #f3f4f6;                  /* Gris claro de fondo */
            --frame: #e5e7eb;               /* Gris medio para el marco */
            --frame-light: #ffffff;
            --panel: #ffffff;               /* Fondo de la pantalla en blanco puro/crema */
            --panel-deep: #f9fafb;          
            --amber: #14005c;               /* Ámbar oscuro para que contraste excelente sobre fondo claro */
            --amber-soft: #14005c;          
            --text: #1f2937;                /* Texto principal gris casi negro */
            --muted: #4b5563;               /* Texto secundario gris */
            --green: #166534;               /* Verde oscuro legible */
            --red: #991b1b;                 /* Rojo oscuro legible */
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            font-family: "VT323", "Courier New", Courier, monospace;
            /* Degradados sutiles en tonos claros */
            background:
                radial-gradient(circle at top, rgba(240,177,60,0.08), transparent 35%),
                radial-gradient(circle at bottom right, rgba(59,130,246,0.05), transparent 30%),
                linear-gradient(180deg, #f9fafb 0%, #f3f4f6 100%);
            color: var(--text);
            overflow-x: hidden;
            text-transform: uppercase;
            text-rendering: geometricPrecision;
        }

        /* Líneas de escaneo sutiles (estilo monitor antiguo pero claro) */
        body:before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,0,0,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.015) 1px, transparent 1px);
            background-size: 46px 46px;
            opacity: 0.8;
            pointer-events: none;
            mask-image: linear-gradient(180deg, rgba(0,0,0,0.9), transparent 95%);
        }

        .terminal-shell {
            width: min(1500px, calc(100vw - 24px));
            margin: 12px auto;
            padding: 18px;
        }

        .terminal-frame {
            position: relative;
            border-radius: 26px;
            padding: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.8), rgba(255,255,255,0.4));
            box-shadow:
                0 20px 50px rgba(0,0,0,0.08),
                inset 0 1px 0 rgba(255,255,255,0.8);
            border: 1px solid rgba(0,0,0,0.08);
        }

        .terminal-screen {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            border: 2px solid rgba(0,0,0,0.08);
            background: linear-gradient(180deg, #fafafa, #f4f4f5);
            box-shadow:
                inset 0 0 0 1px rgba(255,255,255,0.5),
                inset 0 -40px 80px rgba(0,0,0,0.02);
        }

        /* Sombra de escaneo animada */
        .terminal-screen:after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent, rgba(0,0,0,0.015), transparent);
            transform: translateY(-100%);
            animation: scan 7.2s linear infinite;
            pointer-events: none;
        }

        @keyframes scan {
            from { transform: translateY(-100%); }
            to { transform: translateY(100%); }
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 18px;
            padding: 20px 22px 14px;
            flex-wrap: wrap;
        }

        .brand {
            min-width: 0;
        }

        .brand-title {
            margin: 0;
            font-size: clamp(1.5rem, 2.4vw, 2.7rem);
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--amber);
            line-height: 1;
            text-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .brand-subtitle {
            margin: 8px 0 0;
            font-size: 1.05rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .terminal-tag {
            border-radius: 12px;
            border: 1px solid rgba(178,94,0,0.25);
            background: rgba(178,94,0,0.06);
            color: var(--amber);
            padding: 8px 14px 6px;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            white-space: nowrap;
            font-size: 1.08rem;
        }

        .terminal-meta {
            padding: 0 22px 16px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .meta-pill {
            border-radius: 999px;
            border: 1px solid rgba(0,0,0,0.08);
            background: rgba(0,0,0,0.03);
            padding: 8px 14px 6px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--text);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }

        .meta-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 8px rgba(22,101,52,0.4);
        }

        .table-wrap {
            padding: 0 18px 18px;
        }

        .board-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .board-table thead th {
            font-size: 1rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--amber);
            text-align: left;
            padding: 6px 14px;
            white-space: nowrap;
        }

        /* Filas de la tabla */
        .board-table tbody tr {
            background: linear-gradient(180deg, rgba(0,0,0,0.02), rgba(0,0,0,0.04));
        }

        .board-table tbody td {
            padding: 10px 14px 9px;
            border-top: 1px solid rgba(0,0,0,0.03);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-size: clamp(1.55rem, 2.05vw, 2.2rem);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text);
        }

        .board-table tbody tr td:first-child {
            border-left: 1px solid rgba(0,0,0,0.04);
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
            color: var(--amber-soft);
            font-size: clamp(1.85rem, 2.6vw, 2.8rem);
            width: 150px;
            white-space: nowrap;
        }

        .board-table tbody tr td:last-child {
            border-right: 1px solid rgba(0,0,0,0.04);
            border-top-right-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        .flight-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 96px;
            padding: 4px 10px 2px;
            border-radius: 999px;
            border: 1px solid rgba(178,94,0,0.2);
            background: rgba(178,94,0,0.06);
            color: var(--amber);
            letter-spacing: 0.2em;
            font-weight: 700;
            font-size: 0.92em;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 128px;
            padding: 4px 10px 2px;
            border-radius: 999px;
            letter-spacing: 0.18em;
            text-align: center;
            font-weight: 700;
            border: 1px solid transparent;
            font-size: 0.92em;
        }

        .status-waiting {
            color: #78350f;
            background: rgba(217,119,6,0.12);
            border-color: rgba(217,119,6,0.2);
        }

        .status-ready {
            color: #14532d;
            background: rgba(22,101,52,0.12);
            border-color: rgba(22,101,52,0.2);
        }

        .status-blank {
            color: var(--muted);
            background: rgba(0,0,0,0.05);
            border-color: rgba(0,0,0,0.08);
        }

        .empty-row td {
            text-align: center;
            padding: 28px 18px;
            color: var(--muted);
            letter-spacing: 0.14em;
        }

        .footer-strip {
            padding: 0 22px 20px;
            color: var(--muted);
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-size: 0.98rem;
        }

        .blink {
            animation: blink 1.8s steps(1) infinite;
        }

        @keyframes blink {
            50% { opacity: 0.6; }
        }

        @media (max-width: 900px) {
            .terminal-shell { width: min(100vw, 100%); padding: 8px; }
            .terminal-frame { border-radius: 18px; padding: 12px; }
            .topbar, .terminal-meta, .table-wrap, .footer-strip { padding-left: 12px; padding-right: 12px; }
            .board-table thead { display: none; }
            .board-table, .board-table tbody, .board-table tr, .board-table td { display: block; width: 100%; }
            .board-table { border-spacing: 0; }
            .board-table tbody tr { margin-bottom: 10px; border-radius: 16px; overflow: hidden; }
            .board-table tbody td { display: flex; justify-content: space-between; gap: 12px; align-items: center; }
            .board-table tbody td::before {
                content: attr(data-label);
                color: var(--amber);
                letter-spacing: 0.14em;
                font-size: 0.8rem;
                text-transform: uppercase;
            }
            .board-table tbody tr td:first-child { width: auto; }
        }
    </style>
</head>
<body>
    @php
        $waitingOrders = collect($orders['waiting'] ?? [])->sortBy('orderID')->values();
        $readyOrders = collect($orders['ready'] ?? [])->sortBy('orderID')->values();
        $allOrders = $waitingOrders->merge($readyOrders)->values();
        $counts = $counts ?? ['waiting' => 0, 'ready' => 0, 'active' => 0];
    @endphp

    <main class="terminal-shell">
        <section class="terminal-frame">
            <div class="terminal-screen">
                <div class="topbar">
                    <div class="brand">
                        <h1 class="brand-title">Cliente</h1>
                        <p class="brand-subtitle">Seguimiento de ordenes en vivo</p>
                    </div>
                    <div class="terminal-tag">Terminal A</div>
                </div>

                <div class="terminal-meta">
                    <div class="meta-pill"><span class="meta-dot" style="background: var(--amber); box-shadow: 0 0 8px rgba(178,94,0,0.4);"></span> En cola: <strong id="waiting-count">{{ $counts['waiting'] }}</strong></div>
                    <div class="meta-pill"><span class="meta-dot" style="background: var(--green);"></span> Lista: <strong id="ready-count">{{ $counts['ready'] }}</strong></div>
                    <div class="meta-pill"><span class="meta-dot" style="background: var(--amber);"></span> Activas: <strong id="active-count">{{ $counts['active'] }}</strong></div>
                    <div class="meta-pill">Actualizado: <strong id="last-updated">{{ $lastUpdated }}</strong></div>
                </div>

                <div class="table-wrap">
                    <table class="board-table" aria-label="Tablero de ordenes del cliente">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Cliente</th>
                                <th>Mesa</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="board-body">
                            @forelse($allOrders as $order)
                                @php
                                    $tableLabel = $order['table_name'] ?? ($order['table_number'] ? 'Mesa ' . $order['table_number'] : ($order['customer_table'] ? 'Mesa ' . $order['customer_table'] : 'Sin mesa'));
                                    $clientLabel = trim((string) ($order['customer_name'] ?? '')) !== '' ? $order['customer_name'] : 'Orden #' . $order['orderID'];
                                    $statusClass = $order['status'] === 'ready' ? 'status-ready' : 'status-waiting';
                                    $statusText = $order['status'] === 'ready' ? 'LISTA' : 'EN COLA';
                                @endphp
                                <tr data-order-id="{{ $order['orderID'] }}">
                                    <td data-label="Orden"><span class="flight-code">#{{ str_pad((string) $order['orderID'], 3, '0', STR_PAD_LEFT) }}</span></td>
                                    <td data-label="Cliente">{{ $clientLabel }}</td>
                                    <td data-label="Mesa">{{ $tableLabel }}</td>
                                    <td data-label="Estado"><span class="status-chip {{ $statusClass }}">{{ $statusText }}</span></td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="4">Sin ordenes activas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="footer-strip blink">
                    Cuando el estado cambie a lista, la orden se movera a la parte de lista automaticamente.
                </div>
            </div>
        </section>
    </main>

    <script>
        const refreshUrl = @json(route('client.board.data'));
        const searchValue = @json($search);
        const waitingCount = document.getElementById('waiting-count');
        const readyCount = document.getElementById('ready-count');
        const activeCount = document.getElementById('active-count');
        const lastUpdated = document.getElementById('last-updated');
        const boardBody = document.getElementById('board-body');

        function escapeHtml(text) {
            return String(text ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function renderRows(payload) {
            const waiting = payload.orders?.waiting || [];
            const ready = payload.orders?.ready || [];
            const rows = [...waiting, ...ready].sort((a, b) => Number(a.orderID) - Number(b.orderID));

            if (!rows.length) {
                boardBody.innerHTML = '<tr class="empty-row"><td colspan="4">Sin ordenes activas</td></tr>';
            } else {
                boardBody.innerHTML = rows.map((order) => {
                    const tableLabel = order.table_name || (order.table_number ? `Mesa ${order.table_number}` : (order.customer_table ? `Mesa ${order.customer_table}` : 'Sin mesa'));
                    const clientLabel = (order.customer_name && String(order.customer_name).trim() !== '') ? escapeHtml(order.customer_name) : `Orden #${escapeHtml(order.orderID)}`;
                    const statusClass = order.status === 'ready' ? 'status-ready' : 'status-waiting';
                    const statusText = order.status === 'ready' ? 'LISTA' : 'EN COLA';

                    return `
                        <tr data-order-id="${escapeHtml(order.orderID)}">
                            <td data-label="Orden"><span class="flight-code">#${String(order.orderID).padStart(3, '0')}</span></td>
                            <td data-label="Cliente">${clientLabel}</td>
                            <td data-label="Mesa">${escapeHtml(tableLabel)}</td>
                            <td data-label="Estado"><span class="status-chip ${statusClass}">${statusText}</span></td>
                        </tr>
                    `;
                }).join('');
            }

            waitingCount.textContent = payload.counts?.waiting ?? 0;
            readyCount.textContent = payload.counts?.ready ?? 0;
            activeCount.textContent = payload.counts?.active ?? 0;
            lastUpdated.textContent = payload.lastUpdated ?? lastUpdated.textContent;
        }

        async function refreshBoard() {
            try {
                const url = new URL(refreshUrl, window.location.origin);
                if (searchValue !== '') {
                    url.searchParams.set('q', searchValue);
                }

                const response = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return;
                const payload = await response.json();
                renderRows(payload);
            } catch (error) {
                console.debug('client board refresh failed', error);
            }
        }

        setInterval(refreshBoard, 4000);
    </script>
</body>
</html>