<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Fonts (system / Figtree) -->

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Bootstrap (legacy views use Bootstrap classes) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ENjdO4Dr2bkBIFxQpeoYz1FQ784/1p2U3Ckz9ZgqQGA5w5YkN0R8ABTQXK0yK7NQ" crossorigin="anonymous">
    <!-- Project KDS / visual fixes (restored neutral CSS) -->
    <link href="{{ asset('css/kds-fixes.css') }}" rel="stylesheet" />
    <style>
        /* Center the whole app content with a comfortable max width for KDS and staff panels */
        .app-centered-container {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 12px;
            padding-right: 12px;
        }
        @media (max-width: 768px) {
            .app-centered-container { max-width: 100%; padding-left: 8px; padding-right: 8px; }
        }
        /* widen the centered container for kitchen pages only when the helper class is present */
        .app-centered-container.kitchen-wide {
            max-width: 1600px;
        }
    </style>
        <style>
            /* Global button styling for staff UI: rounded, subtle shadows and hover
               Enhanced to make outline variants look like tappable buttons */
            .btn {
                border-radius: 10px !important;
                padding: 8px 12px !important;
                box-shadow: 0 4px 12px rgba(0,0,0,0.04);
                transition: transform .12s ease, box-shadow .14s ease, opacity .12s ease;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,0.10); }
            .btn:active { transform: translateY(0); box-shadow: 0 6px 12px rgba(0,0,0,0.06); }
            .btn-sm { padding: 6px 10px !important; font-size: .92rem; }

            /* Primary / success / warning: keep distinct filled styles */
            .btn-primary { background: linear-gradient(180deg,#4f9df5,#2f7be6); border-color: transparent; color: #fff; }
            .btn-success { background: linear-gradient(180deg,#7ee6b8,#2ed07d); border-color: transparent; color:#063; }
            .btn-warning { background: linear-gradient(180deg,#ffd98a,#ffc145); border-color: transparent; color:#221; }

            /* Dark action buttons used across staff UI */
            .btn-dark {
                background: #111 !important;
                color: #fff !important;
                border-color: transparent !important;
                box-shadow: 0 8px 18px rgba(0,0,0,0.12) !important;
                padding: 8px 14px !important;
            }
            .btn-dark:hover{ transform: translateY(-2px); box-shadow: 0 12px 28px rgba(0,0,0,0.18) !important; }

            /* Make outline variants visually match the dark action style where appropriate
               This lets small action buttons like 'Ver', 'Mesas', 'Agregar mesa' appear
               as dark pill buttons similar to primary actions. Use !important to ensure
               these rules take precedence over component-local styles. */
            .btn-outline-secondary, .btn-outline-primary, .btn-outline-dark {
                background: #111 !important;
                color: #fff !important;
                border-color: transparent !important;
                box-shadow: 0 6px 18px rgba(0,0,0,0.12) !important;
            }
            .btn-outline-secondary:hover, .btn-outline-primary:hover, .btn-outline-dark:hover{
                transform: translateY(-2px);
                box-shadow: 0 12px 28px rgba(0,0,0,0.18) !important;
                opacity: 0.98;
            }

            /* Make 'outline' variants feel like real buttons (soft filled backgrounds)
               so small actions like 'Ver' / 'Mesas' look tappable on touch screens */
            .btn-outline-secondary {
                background: #f4f8f6;
                border-color: rgba(0,0,0,0.06);
                color: #1f2937;
            }
            .btn-outline-primary {
                background: #e9f2ff;
                border-color: rgba(47,123,230,0.16);
                color: #1f5fc4;
            }

            /* Pill-style filters */
            .pill-filter { border-radius: 999px; padding:6px 12px; }
            /* Small layout helpers for staff header spacing */
            .staff-header .gap-2 { gap: .5rem; }
            .staff-header .gap-3 { gap: .75rem; }
            /* Make staff header a single-line flex container so controls stay on one row
               and the 'Mesas' button (ms-auto) remains right-aligned. Prevent wrapping. */
            .staff-header { display:flex; justify-content:space-between; align-items: center; flex-wrap: nowrap; gap: .75rem; }

            /* Search-inline helper: limit width so it doesn't force wrapping on desktop
               but allow full width on small screens. */
            .search-inline input[type="search"], .search-inline input[type="text"] {
                min-width: 180px;
                max-width: 520px;
                width: 38ch;
            }
            @media (max-width: 768px) {
                .search-inline { width: 100%; }
                .search-inline input[type="search"], .search-inline input[type="text"] { width: 100%; max-width: 100%; }
                .staff-header { flex-wrap: wrap; gap: .5rem; }
            }

            /* Active pill should be dark with white text (not blue) */
            .pill-filter.active, .pill-filter:active, .pill-filter:focus {
                background: #111 !important;
                color: #fff !important;
                border-color: transparent !important;
                box-shadow: 0 8px 18px rgba(0,0,0,0.12) !important;
            }
            /* Ensure anchors inside pill-filters also display white text when active/focused
               This overrides default link colors (browser/Bootstrap) that sometimes show
               blue text on active/visited states. */
            .pill-filter.active a, .pill-filter a.active, .pill-filter a:active, .pill-filter a:focus, .pill-filter a:visited {
                color: #fff !important;
                background: transparent !important;
                text-decoration: none !important;
            }
            /* Force .btn-dark and related outline variants to keep white text even if
               inner content is an anchor or has visited/active styles. This fixes
               cases where the button shows blue link text on top of a dark pill. */
            .btn-dark, .btn-dark * , .btn-outline-dark, .btn-outline-dark *,
            .btn-outline-secondary, .btn-outline-secondary *,
            .category-actions .btn, .category-actions .btn * {
                color: #fff !important;
            }
            /* make outline-secondary in category lists appear slightly translucent black */
            .category-actions .btn, .category-actions .btn.btn-outline-secondary {
                background: rgba(17,17,17,0.92) !important;
                border-color: transparent !important;
                box-shadow: 0 6px 14px rgba(0,0,0,0.10) !important;
            }
            /* Remove default Bootstrap/UA blue focus halo and mobile tap highlight so
               active/selected state shows as designed (black/white) rather than browser blue. */
            *, *::before, *::after { -webkit-tap-highlight-color: transparent; }
            /* Ensure pills keep black/white appearance when focused/active */
            .pill-filter:focus, .pill-filter:focus-visible, .pill-filter:active, .pill-filter.active,
            .pill-filter a:focus, .pill-filter a:active {
                outline: none !important;
                box-shadow: none !important;
                background: #111 !important;
                color: #fff !important;
            }
            /* Make text selection transparent so selected letters don't turn browser-blue */
            ::selection { background: transparent; color: inherit; }
            ::-moz-selection { background: transparent; color: inherit; }
            /* Prevent blue focus halo on buttons inside cards/KDS and general buttons */
            .kds-tile .btn:focus, .kds-tile .btn:focus-visible, .btn:focus, .btn:focus-visible,
            a.btn:focus, a.btn:focus-visible {
                outline: none !important;
                box-shadow: none !important;
            }
            /* Keep subtle neutral shadow for inputs/selects but avoid strong blue outlines */
            input:focus, select:focus, textarea:focus { box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important; outline: none !important; }

            /* Search button and small links consistency */
            .search-inline .btn { height: calc(2.15rem + 4px); }
            a.btn.btn-sm { text-decoration: none; }

            /* Buttons inside KDS tiles: slightly smaller and flatter */
            .kds-tile .btn { box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding:6px 10px; }
            .kds-tile .btn:hover { box-shadow: 0 8px 16px rgba(0,0,0,0.08); }
        </style>
        <style>
            /* Global dark background for entire app (applies to all pages) */
            /* Use system font stack (Figtree fallback) to keep load fast */
            html, body { background: #2f2f2f !important; color: #ffffff; font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
            /* Tailwind's utility classes like .font-sans should follow the system font */
            .font-sans, body.font-sans { font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important; }
            /* Ensure main wrapper is transparent so body background shows through */
            .min-h-screen { background: transparent !important; }

            /* Layout spacing */
            .app-centered-container { padding-top: 18px; padding-bottom: 32px; }

            /* Cards / panels: white, rounded and elevated for contrast */
            .card, .bg-white {
                background-color: #ffffff !important;
                color: #111 !important;
                border-radius: 12px !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.22) !important;
                -webkit-font-smoothing:antialiased;
            }

            /* Ensure any elements placed on dark surface use white text for readability */
            header, nav, .app-centered-container, main, .staff-header, .page-title, .page-subtitle, .page-section, .panel, .panel * {
                color: #ffffff !important;
            }

            /* Exceptions: keep text inside white cards readable (dark) */
            .card, .bg-white, .card * { color: #111 !important; }
            /* Tables and list groups should sit on white panels */
            .table, .list-group { background: transparent; }

            /* Header and navigation: keep white and crisp */
            header.bg-white { background: #ffffff !important; border-bottom: 1px solid rgba(0,0,0,0.06); padding: 6px 0; }
            nav a, header a { color: #111 !important; }
                /* Make header/navigation links use the system font and have a pill/button aesthetic
                   while keeping the overall white/nav look. On hover/active they adopt
                   the dark button colors so everything combines visually. */
                header nav a, header a, nav .nav-link, .navbar-nav .nav-link, header .navbar .nav-link, .navbar-nav > li > a {
                    font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif !important;
                    font-weight: 700 !important;
                    padding: 6px 10px !important;
                    border-radius: 6px !important;
                    box-shadow: none !important;
                    display: inline-flex !important;
                    align-items: center;
                    gap: .5rem;
                    transition: all .14s ease !important;
                    background: transparent !important;
                    color: #111 !important;
                    margin-right: 6px;
                    text-decoration: none !important;
                }
            header nav a:hover, header nav a.active, nav .nav-link:hover, nav .nav-link.active, .navbar-nav .nav-link.active {
                background: #111 !important;
                color: #fff !important;
                box-shadow: 0 12px 28px rgba(0,0,0,0.18) !important;
                transform: translateY(-2px);
            }
            /* Allow the header navigation to wrap when the viewport is narrow so items don't get cut off */
            header .flex, header .flex > div { flex-wrap: wrap; gap: 6px; align-items: center; }
            /* Slightly reduce nav item spacing on small screens to fit more items */
            @media (max-width: 1100px) {
                header nav a, header a, nav .nav-link { padding: 6px 8px !important; margin-right: 4px !important; }
            }

            /* Ensure the logo stays left and nav flows naturally; reduce large left margin used for spacing */
            .hidden.space-x-8.sm:-my-px.sm:ms-10.sm:flex { margin-left: 0 !important; }

            /* New helpers for nav layout to reserve space for the settings dropdown (username)
               and prevent the username button from being cut off. */
            .nav-links { display:flex; flex-wrap:wrap; gap:6px; align-items:center; max-width: calc(100% - 200px); overflow-x:auto; }
            .nav-links::-webkit-scrollbar{ height:6px }
            .nav-settings { margin-left: auto; display:flex; align-items:center; }
            /* On small screens allow nav to shrink and make settings always visible on the right */
            @media (max-width: 1100px){
                .nav-links{ max-width: calc(100% - 120px); }
            }
            /* Hide top navbar links on small screens; use quick-access and hamburger panels only */
            @media (max-width: 768px){
                .nav-links{ display: none !important; }
            }
            /* Page header titles: use system font to avoid loading extra fonts */
            header .leading-tight, header h1, header h2, .page-title, .page-title *,
            .app-centered-container h1, .app-centered-container h2 {
                font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif !important;
                font-weight: 600 !important;
                color: #111 !important;
                -webkit-font-smoothing:antialiased !important;
            }
            /* Make top-level container headings readable on dark background (white text)
               but avoid changing headings inside white cards/panels which should remain dark. */
            .app-centered-container > h1, .app-centered-container > h2,
            .container > h1, .container > h2, .container > h3,
            .container-fluid > h1, .container-fluid > h2, #kitchen-board h1, #kitchen-board h2 {
                font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif !important;
                color: #ffffff !important;
                font-weight: 600 !important;
            }
            /* Title should not have a pill background; keep simple text */
            .app-centered-container h1, .app-centered-container h2, .page-title {
                display: block; padding: 0; border-radius: 0; background: transparent !important; box-shadow: none !important; margin-bottom: 12px;
            }

            /* Product / menu tiles: white cards but slightly lifted and spaced */
            .menu-item-card, .product-card, .card.menu-item, .card.product {
                border-radius: 10px; padding: 12px; margin-bottom: 18px;
                box-shadow: 0 6px 18px rgba(0,0,0,0.18);
                background: #fff !important; color: #111 !important;
            }

            /* KDS / order tiles: soft pink background to stand out from dark surface */
            .kds-tile {
                background: #fde8e9; /* soft pink */
                border-radius: 10px;
                padding: 12px;
                box-shadow: 0 8px 20px rgba(0,0,0,0.18);
                color: #111;
            }

            /* Buttons: consistent dark, rounded, tactile */
            .btn, .btn-dark, .btn-outline-dark {
                border-radius: 12px !important;
                background: #0f0f0f !important;
                color: #fff !important;
                padding: 8px 12px !important;
                box-shadow: 0 8px 22px rgba(0,0,0,0.28) !important;
                border: none !important;
            }
            .btn:active, .btn:focus { transform: translateY(0); box-shadow: 0 6px 12px rgba(0,0,0,0.18) !important; }

            /* Pill filters */
            .pill-filter { background: #0f0f0f !important; color: #fff !important; padding:6px 14px; border-radius:999px; box-shadow: 0 8px 18px rgba(0,0,0,0.22); }

            /* Links inside white panels should be dark and clearly visible */
            .card a, .bg-white a { color: #0b3b66 !important; }

            /* Improve scrollbar visibility on dark background (for desktop) */
            ::-webkit-scrollbar { width: 10px; height:10px }
            ::-webkit-scrollbar-track { background: transparent }
            ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius:8px }

            /* Small adjustments for KDS tile buttons inside darker surface */
            .kds-tile .btn { background:#111 !important; color:#fff !important; box-shadow:0 6px 14px rgba(0,0,0,0.2); }

            /* Keep selection and focus subtle */
            ::selection { background: rgba(255,255,255,0.04); color: inherit; }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen">
            <div class="{{ request()->is('staff/kitchen*') ? 'app-centered-container kitchen-wide' : 'app-centered-container' }}">
                @include('layouts.navigation')

                @php $role = optional(Auth::user())->role; @endphp
                @if(in_array($role, ['admin','mesero','cocina_barra']))
                    @include('components.order-status-bar')
                @endif

                {{-- Flash messages: success / warning / error --}}
                <div class="mt-3">
                    @php $msgSuccess = session('success'); $msgError = session('error'); $msgWarning = session('warning'); @endphp
                    @if($msgSuccess || $msgError || $msgWarning)
                        <style>
                            .app-toast { position: fixed; right: 20px; top: 20px; z-index: 1055; min-width: 260px; max-width: 80vw; border-radius: 12px; box-shadow: 0 12px 28px rgba(0,0,0,.22); color:#fff; padding: 12px 16px; display:flex; align-items:center; gap:10px; }
                            .app-toast.success { background: #16a34a; }
                            .app-toast.error { background: #dc2626; }
                            .app-toast.warning { background: #f59e0b; color:#111; }
                            .app-toast .msg { font-weight: 800; }
                            .app-toast .sub { font-size:.85rem; opacity:.95; }
                            .app-toast .icon { width:10px; height:10px; border-radius:50%; background:rgba(255,255,255,.9); }
                            @media(max-width:640px){ .app-toast { left: 10px; right: 10px; top: 10px; } }
                        </style>
                        <div id="app-toast" class="app-toast {{ $msgError ? 'error' : ($msgWarning ? 'warning' : 'success') }}">
                            <span class="icon"></span>
                            <div>
                                <div class="msg">{{ $msgError ? 'Error' : ($msgWarning ? 'Atención' : 'Operación exitosa') }}</div>
                                <div class="sub">{{ $msgError ?? $msgWarning ?? $msgSuccess }}</div>
                            </div>
                        </div>
                        <script>
                        (function(){
                            const el = document.getElementById('app-toast');
                            if(!el) return;
                            setTimeout(()=>{ try{ el.style.transition='opacity .6s ease, transform .6s ease'; el.style.opacity='0'; el.style.transform='translateY(-8px)'; setTimeout(()=>{ el.remove(); }, 700); }catch(_){} }, 3200);
                        })();
                        </script>
                    @endif
                </div>

                <!-- Page Heading -->
                @if(isset($header) || View::hasSection('header'))
                    <header class="bg-white shadow">
                        <div class="py-6 px-4 sm:px-6 lg:px-8">
                            @isset($header)
                                {{ $header }}
                            @else
                                @yield('header')
                            @endisset
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>
        </div>
        {{-- global helper: track when a mesero comment input has focus so pollers can pause replacements --}}
        <script>
            // expose today's exchange rate (Bs per 1 USD) to client JS as window.__exchangeRate
            try{
                @php
                    try{
                        $__exRate = \App\Models\ExchangeRate::forDate(now()->toDateString());
                    }catch(\Throwable $__e){ $__exRate = null; }
                @endphp
                window.__exchangeRate = {{ json_encode($__exRate) }};
            }catch(e){ window.__exchangeRate = null; }

            // initialize global flag early so other scripts can check it
            try{
                window.__basketInputHasFocus = false;
                document.addEventListener('focusin', function(e){
                    try{ if(e.target && e.target.classList && e.target.classList.contains('item-comment')) window.__basketInputHasFocus = true; }catch(_){}
                });
                document.addEventListener('focusout', function(e){
                    try{ if(e.target && e.target.classList && e.target.classList.contains('item-comment')) window.__basketInputHasFocus = false; }catch(_){}
                });
            }catch(err){ /* silent fallback */ }
            // Monkey-patch replaceWith globally to avoid any poller accidentally
            // replacing DOM nodes while a basket/comment input has focus on mobile
            try{
                (function(){
                    const orig = Node.prototype.replaceWith;
                    if(!orig) return;
                    Node.prototype.replaceWith = function(...nodes){
                        try{
                            if(window && window.__basketInputHasFocus){
                                // skip replacement while typing to avoid losing focus/closing keyboard
                                return this;
                            }
                        }catch(e){ /* ignore and proceed to original */ }
                        return orig.apply(this, nodes);
                    };
                })();
            }catch(err){ /* ignore if unable to patch */ }
        </script>
        {{-- place for pushed scripts from views --}}
        @stack('scripts')
        <script>
            // Removed blocking font-loader script to speed up rendering.
            // The app now relies on the system font stack (Figtree) for fast display.
        </script>
        <script>
            // Listen for cross-tab updates to kitchen counts so dashboards refresh immediately
            window.addEventListener('storage', function(e){
                try{
                    if(!e.key) return;
                    if(e.key === 'kds_counts_update'){
                        const payload = e.newValue ? JSON.parse(e.newValue) : null;
                        if(payload && payload.counts){
                            const counts = payload.counts;
                            const w = document.getElementById('cnt-waiting'); if(w && typeof counts.waiting !== 'undefined') w.textContent = counts.waiting;
                            const r = document.getElementById('cnt-ready'); if(r && typeof counts.ready !== 'undefined') r.textContent = counts.ready;
                        }
                        // If we are on the kitchen page, fetch and replace the board immediately
                        try{
                            const board = document.querySelector('#kitchen-board');
                            if(board){
                                fetch(window.location.href, { credentials: 'same-origin' }).then(r=>r.text()).then(t=>{
                                    const doc = new DOMParser().parseFromString(t, 'text/html');
                                    const newBoard = doc.querySelector('#kitchen-board');
                                    if(newBoard && board){ board.replaceWith(newBoard); }
                                }).catch(()=>{});
                            }
                        }catch(_){ }
                    }
                }catch(err){ console.debug('storage listener error', err); }
            });
        </script>
        <!-- debug overlay removed -->
    </body>
</html>
