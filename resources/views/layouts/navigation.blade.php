<nav x-data="{ open: false, openQuick: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-2">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Botón de acceso rápido (móvil) junto al logo: abre categorías/links principales -->
                <div class="flex items-center sm:hidden ms-2">
                    <button @click="openQuick = ! openQuick" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" aria-label="Acceso rápido">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h10M4 12h8M4 18h6" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex nav-links">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Panel de control') }}
                    </x-nav-link>
                    @php $role = optional(Auth::user())->role; @endphp
                    @if($role === 'admin')
                        <x-nav-link :href="route('admin.menu.index')" :active="request()->routeIs('admin.menu.*')">
                            {{ __('Categorías') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.menuitem.index')" :active="request()->routeIs('admin.menuitem.*')">
                            {{ __('Productos') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.staff.index')" :active="request()->routeIs('admin.staff.*')">
                            {{ __('Staff') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.cleaned-orders.index')" :active="request()->routeIs('admin.cleaned-orders.*')">
                            {{ __('Historial de venta') }}
                        </x-nav-link>
                            <x-nav-link :href="route('staff.orders.index')" :active="request()->routeIs('staff.orders.*')">
                                {{ __('Ordenes') }}
                            </x-nav-link>
                            <x-nav-link :href="route('staff.orders.create')" :active="request()->routeIs('staff.orders.create')">
                                {{ __('Crear pedido') }}
                            </x-nav-link>
                            <x-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                                {{ __('Cliente') }}
                            </x-nav-link>
                            <x-nav-link :href="route('staff.kitchen.index')" :active="request()->routeIs('staff.kitchen.*')">
                                {{ __('Cocina/Barra') }}
                            </x-nav-link>
                    @elseif($role === 'mesero')
                        <x-nav-link :href="route('staff.orders.index')" :active="request()->routeIs('staff.orders.*')">
                            {{ __('Ordenes') }}
                        </x-nav-link>
                            <x-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                                {{ __('Cliente') }}
                            </x-nav-link>
                    @elseif($role === 'cocina_barra')
                        <x-nav-link :href="route('staff.kitchen.index')" :active="request()->routeIs('staff.kitchen')">
                            {{ __('Cocina/Barra') }}
                        </x-nav-link>
                            <x-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                                {{ __('Cliente') }}
                            </x-nav-link>
                    @elseif(in_array($role, ['staff', 'mesero']))
                        {{-- backward-compatible: legacy 'staff' users or 'mesero' see orders --}}
                        <x-nav-link :href="route('staff.orders.index')" :active="request()->routeIs('staff.orders.*')">
                            {{ __('Ordenes') }}
                        </x-nav-link>
                        @if(in_array($role, ['staff', 'mesero', 'cocina_barra']))
                            {{-- legacy staff, mesero or cocina_barra users see the kitchen link (backward-compat) --}}
                            <x-nav-link :href="route('staff.kitchen.index')" :active="request()->routeIs('staff.kitchen')">
                                {{ __('Cocina') }}
                            </x-nav-link>
                        @endif
                        <x-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                            {{ __('Cliente') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 nav-settings">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if(optional(Auth::user())->role === 'admin')
                                {{-- Keep dropdown minimal for admins since primary admin links are shown in the main nav --- avoid duplication ---}}
                                <div class="border-t border-gray-100"></div>
                                <x-dropdown-link :href="route('admin.dashboard') ?? route('admin.menu.index')">
                                    {{ __('Admin panel') }}
                                </x-dropdown-link>
                            @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (móvil) a la derecha: restaurado -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" aria-label="Abrir menú">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú de acceso rápido (móvil, junto al logo) -->
    <div :class="{'block': openQuick, 'hidden': ! openQuick}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @php $role = optional(Auth::user())->role; @endphp
            <!-- Agrupa los enlaces principales que se ven en la foto 2 -->
            @if($role === 'admin')
                <x-responsive-nav-link :href="route('admin.menu.index')" :active="request()->routeIs('admin.menu.*')">
                    {{ __('Categorías') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.menuitem.index')" :active="request()->routeIs('admin.menuitem.*')">
                    {{ __('Productos') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.staff.index')" :active="request()->routeIs('admin.staff.*')">
                    {{ __('Staff') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.cleaned-orders.index')" :active="request()->routeIs('admin.cleaned-orders.*')">
                    {{ __('Historial de venta') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.orders.index')" :active="request()->routeIs('staff.orders.*')">
                    {{ __('Ordenes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.orders.create')" :active="request()->routeIs('staff.orders.create')">
                    {{ __('Crear pedido') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                    {{ __('Cliente') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.kitchen.index')" :active="request()->routeIs('staff.kitchen.*')">
                    {{ __('Cocina/Barra') }}
                </x-responsive-nav-link>
            @elseif($role === 'mesero')
                <x-responsive-nav-link :href="route('staff.orders.index')" :active="request()->routeIs('staff.orders.*')">
                    {{ __('Ordenes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.orders.create')" :active="request()->routeIs('staff.orders.create')">
                    {{ __('Crear pedido') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                    {{ __('Cliente') }}
                </x-responsive-nav-link>
            @elseif($role === 'cocina_barra')
                <x-responsive-nav-link :href="route('staff.kitchen.index')" :active="request()->routeIs('staff.kitchen.*')">
                    {{ __('Cocina/Barra') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                    {{ __('Cliente') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Panel de control') }}
                </x-responsive-nav-link>
            @endif
        </div>
    </div>

    <!-- Responsive Navigation Menu (hamburguesa derecha) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Panel de control') }}
            </x-responsive-nav-link>

            @php $role = optional(Auth::user())->role; @endphp
            {{-- Show links according to role: admin sees all, mesero sees order flows, cocina_barra sees kitchen, legacy 'staff' treated as mesero --}}
            @if($role === 'admin')
                <x-responsive-nav-link :href="route('staff.orders.index')" :active="request()->routeIs('staff.orders.*')">
                    {{ __('Ordenes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.orders.create')" :active="request()->routeIs('staff.orders.create')">
                    {{ __('Crear pedido') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                    {{ __('Cliente') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.kitchen.index')" :active="request()->routeIs('staff.kitchen.*')">
                    {{ __('Cocina/Barra') }}
                </x-responsive-nav-link>
            @elseif(in_array($role, ['mesero','staff']))
                <x-responsive-nav-link :href="route('staff.orders.index')" :active="request()->routeIs('staff.orders.*')">
                    {{ __('Ordenes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.orders.create')" :active="request()->routeIs('staff.orders.create')">
                    {{ __('Crear pedido') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                    {{ __('Cliente') }}
                </x-responsive-nav-link>
            @elseif($role === 'cocina_barra')
                <x-responsive-nav-link :href="route('staff.kitchen.index')" :active="request()->routeIs('staff.kitchen.*')">
                    {{ __('Cocina/Barra') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('client.board')" :active="request()->routeIs('client.board')">
                    {{ __('Cliente') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
