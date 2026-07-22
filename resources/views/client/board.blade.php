<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal Cliente</title>
    <style>
        :root {
            --bg: #0b1020;
            --panel: rgba(12, 18, 36, 0.88);
            --panel-strong: rgba(17, 24, 39, 0.96);
            --border: rgba(255, 195, 92, 0.18);
            --amber: #ffc85c;
            --amber-soft: #f5d97f;
            --green: #8ef0b0;
            --text: #f7eed2;
            --muted: rgba(247, 238, 210, 0.72);
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            color: var(--text);
            font-family: "Courier New", Courier, monospace;
            background:
                radial-gradient(circle at top, rgba(255,200,92,0.14), transparent 34%),
                radial-gradient(circle at bottom right, rgba(76,145,255,0.11), transparent 28%),
                linear-gradient(180deg, #070b14 0%, #101829 48%, #070b14 100%);
            overflow-x: hidden;
        }

        body:before {
            content: "";
            position: fixed;
            inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,0.8), transparent 82%);
            pointer-events: none;
            opacity: 0.45;
        }

        .wrap {
            position: relative;
            max-width: 1280px;
            margin: 0 auto;
            padding: 22px 16px 40px;
        }

        .hero {
            border: 1px solid rgba(255,255,255,0.12);
            background: linear-gradient(180deg, rgba(12,18,36,0.92), rgba(7,10,18,0.98));
            border-radius: 26px;
            padding: 24px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5);
            overflow: hidden;
            position: relative;
        }

        .hero:after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent, rgba(255,255,255,0.03), transparent);
            transform: translateY(-100%);
            animation: scan 6.8s linear infinite;
            pointer-events: none;
        }

        @keyframes scan {
            from { transform: translateY(-100%); }
            to { transform: translateY(100%); }
        }

        .topline {
            display: flex;
            gap: 14px;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .plane {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255,200,92,0.2);
            background: linear-gradient(180deg, rgba(255,200,92,0.18), rgba(255,200,92,0.04));
            color: var(--amber);
            font-size: 1.35rem;
            font-weight: 900;
            text-shadow: 0 0 14px rgba(255,200,92,0.3);
        }

        .title {
            margin: 0;
            font-size: clamp(1.6rem, 2.4vw, 2.6rem);
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--amber);
        }

        .subtitle {
            margin: 6px 0 0;
            color: var(--muted);
            letter-spacing: 0.08em;
            font-size: 0.95rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.04);
            color: var(--text);
            white-space: nowrap;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--green);
            box-shadow: 0 0 18px rgba(142, 240, 176, 0.95);
        }

        .search-bar {
            margin: 18px 0 24px;
            display: grid;
            grid-template-columns: 1.3fr auto auto;
            gap: 10px;
        }

        .search-bar input,
        .search-bar button,
        .search-bar a {
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.12);
            padding: 14px 16px;
            font: inherit;
            outline: none;
        }

        .search-bar input {
            background: rgba(255,255,255,0.04);
            color: var(--text);
        }

        .search-bar input::placeholder { color: rgba(247,238,210,0.45); }
        .search-bar button {
            cursor: pointer;
            color: #1a1201;
            background: linear-gradient(180deg, #ffd980, #f1b33c);
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .search-bar a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: var(--amber-soft);
            text-decoration: none;
        }

        .board-meta {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 22px;
        }

        .meta-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 18px;
            padding: 16px;
        }

        .meta-label {
            display: block;
            color: var(--muted);
            font-size: 0.82rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .meta-value {
            font-size: 2rem;
            color: var(--amber);
            letter-spacing: 0.1em;
            font-weight: 700;
        }

        .board {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .panel {
            min-height: 360px;
            border-radius: 22px;
            border: 1px solid rgba(255,255,255,0.12);
            background: linear-gradient(180deg, rgba(13,18,35,0.96), rgba(8,11,22,0.98));
            overflow: hidden;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.09);
            background: rgba(255,255,255,0.03);
        }

        .panel-head h2 {
            margin: 0;
            font-size: 1.05rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--amber);
        }

        .panel-head span {
            color: var(--muted);
            letter-spacing: 0.1em;
            font-size: 0.82rem;
        }

        .panel-body {
            padding: 12px 14px 16px;
            display: grid;
            gap: 10px;
        }

        .flight-card {
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.08);
            background: linear-gradient(180deg, rgba(255,255,255,0.05), rgba(255,255,255,0.02));
            padding: 14px 16px;
            display: grid;
            grid-template-columns: 84px 1fr auto;
            gap: 12px;
            align-items: center;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        }

        .flight-code {
            font-size: 1.65rem;
            color: var(--amber);
            letter-spacing: 0.18em;
            font-weight: 700;
            text-shadow: 0 0 16px rgba(255,200,92,0.2);
        }

        .flight-main {
            min-width: 0;
        }

        .flight-main .line-1 {
            font-size: 1.05rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #fff6d7;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .flight-main .line-2 {
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.82rem;
            letter-spacing: 0.08em;
        }

        .status-badge {
            min-width: 112px;
            text-align: center;
            border-radius: 999px;
            padding: 10px 12px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .status-waiting {
            color: #ffda88;
            background: rgba(255, 200, 92, 0.1);
            border-color: rgba(255, 200, 92, 0.18);
        }

        .status-ready {
            color: #a7f7c0;
            background: rgba(142, 240, 176, 0.1);
            border-color: rgba(142, 240, 176, 0.18);
        }

        .empty {
            padding: 30px 16px;
            color: rgba(247,238,210,0.65);
            text-align: center;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .notice {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.04);
            color: var(--muted);
            letter-spacing: 0.04em;
        }

        .callout {
            margin-top: 16px;
            border-radius: 18px;
            border: 1px solid rgba(255,200,92,0.2);
            background: linear-gradient(180deg, rgba(255,200,92,0.16), rgba(255,200,92,0.05));
            padding: 16px 18px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--amber-soft);
        }

        .pulse { animation: pulse 1.6s ease-in-out infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @media (max-width: 920px) {
            .board, .board-meta { grid-template-columns: 1fr; }
            .search-bar { grid-template-columns: 1fr; }
            .flight-card { grid-template-columns: 68px 1fr; }
            .status-badge { grid-column: 1 / -1; justify-self: start; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="hero">
            <div class="topline">
                <div class="brand">
                    <div class="plane">✈</div>
                    <div>
                        <h1 class="title">Terminal Cliente</h1>
                        <p class="subtitle">Estado de la orden en tiempo real, estilo tablero de aeropuerto</p>
                    </div>
                </div>
                <div class="status-pill">
                    <span class="status-dot"></span>
                    <span id="last-updated">Actualizado {{ $lastUpdated }}</span>
                </div>
            </div>

            <form class="search-bar" method="GET" action="{{ route('client.board') }}">
                <input type="search" name="q" value="{{ $search }}" placeholder="Buscar por orden, mesa o nombre" aria-label="Buscar orden">
                <button type="submit">Consultar</button>
                <a href="{{ route('client.board') }}">Ver todo</a>
            </form>

            <div class="board-meta">
                <div class="meta-card">
                    <span class="meta-label">En cola</span>
                    <div class="meta-value" id="waiting-count">{{ $counts['waiting'] }}</div>
                </div>
                <div class="meta-card">
                    <span class="meta-label">Lista</span>
                    <div class="meta-value" id="ready-count">{{ $counts['ready'] }}</div>
                </div>
                <div class="meta-card">
                    <span class="meta-label">Activas</span>
                    <div class="meta-value" id="active-count">{{ $counts['active'] }}</div>
                </div>
                <div class="meta-card">
                    <span class="meta-label">Filtro</span>
                    <div class="meta-value" id="filter-label">{{ $search !== '' ? 'ON' : 'ALL' }}</div>
                </div>
            </div>

            <div class="board">
                <section class="panel">
                    <div class="panel-head">
                        <h2>En Cola</h2>
                        <span>Board / Waiting</span>
                    </div>
                    <div class="panel-body" id="waiting-board"></div>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <h2>Lista</h2>
                        <span>Board / Ready</span>
                    </div>
                    <div class="panel-body" id="ready-board"></div>
                </section>
            </div>

            <div class="notice">
                Este tablero se actualiza solo. Cuando tu orden pase a <strong>lista</strong>, aparecerá en el lado derecho.
            </div>

            <div class="callout pulse" id="ready-callout" style="display: none;">Tu orden ya está lista</div>
        </section>
    </div>

    <script>
        const refreshUrl = @json(route('client.board.data'));
        const initialData = @json($orders);
        const searchValue = @json($search);
        const waitingBoard = document.getElementById('waiting-board');
        const readyBoard = document.getElementById('ready-board');
        const waitingCount = document.getElementById('waiting-count');
        const readyCount = document.getElementById('ready-count');
        const activeCount = document.getElementById('active-count');
        const lastUpdated = document.getElementById('last-updated');
        const filterLabel = document.getElementById('filter-label');
        const readyCallout = document.getElementById('ready-callout');

        let previousReadyIds = new Set((initialData.ready || []).map(item => String(item.orderID)));

        function escapeHtml(text) {
            return String(text ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function renderCard(order) {
            const tableLabel = order.table_name || (order.table_number ? `Mesa ${order.table_number}` : (order.customer_table ? `Mesa ${order.customer_table}` : 'Sin mesa'));
            const customerLabel = order.customer_name ? escapeHtml(order.customer_name) : 'Orden sin nombre';
            const statusClass = order.status === 'ready' ? 'status-ready' : 'status-waiting';
            const statusText = order.status === 'ready' ? 'LISTA' : 'EN COLA';
            return `
                <article class="flight-card">
                    <div class="flight-code">${String(order.orderID).padStart(3, '0')}</div>
                    <div class="flight-main">
                        <div class="line-1">${customerLabel}</div>
                        <div class="line-2">${escapeHtml(tableLabel)} · Orden #${escapeHtml(order.orderID)}</div>
                    </div>
                    <div class="status-badge ${statusClass}">${statusText}</div>
                </article>
            `;
        }

        function renderBoard(payload) {
            const waiting = payload.orders?.waiting || [];
            const ready = payload.orders?.ready || [];

            waitingBoard.innerHTML = waiting.length ? waiting.map(renderCard).join('') : '<div class="empty">Sin órdenes en cola</div>';
            readyBoard.innerHTML = ready.length ? ready.map(renderCard).join('') : '<div class="empty">Aún no hay órdenes listas</div>';

            waitingCount.textContent = payload.counts?.waiting ?? 0;
            readyCount.textContent = payload.counts?.ready ?? 0;
            activeCount.textContent = payload.counts?.active ?? 0;
            lastUpdated.textContent = `Actualizado ${payload.lastUpdated ?? ''}`;
            filterLabel.textContent = searchValue !== '' ? 'ON' : 'ALL';

            const currentReadyIds = new Set(ready.map(item => String(item.orderID)));
            const becameReady = [...currentReadyIds].some(id => !previousReadyIds.has(id));
            if (becameReady) {
                readyCallout.style.display = 'block';
                clearTimeout(window.__clientBoardTimer);
                window.__clientBoardTimer = setTimeout(() => {
                    readyCallout.style.display = 'none';
                }, 5000);
            }
            previousReadyIds = currentReadyIds;
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
                renderBoard(payload);
            } catch (error) {
                console.debug('client board refresh failed', error);
            }
        }

        renderBoard(initialData);
        setInterval(refreshBoard, 4000);
    </script>
</body>
</html>
