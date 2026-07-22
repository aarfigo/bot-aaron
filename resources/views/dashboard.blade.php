<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de control') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @php
                $waiting = DB::table('tbl_order')->where('status','waiting')->count();
                $ready = DB::table('tbl_order')->where('status','ready')->count();
            @endphp

            <div class="row g-3 mt-3">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="small text-muted">En cola</div>
                            <div class="h3">{{ $waiting }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="small text-muted">Lista</div>
                            <div class="h3">{{ $ready }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="small text-muted">Finalizadas (ventas)</div>
                            <div class="h3">{{ DB::table('sales_history')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@php
    $today = \Carbon\Carbon::now()->toDateString();
    $exchangeRate = \App\Models\ExchangeRate::where('date', '<=', $today)->orderBy('date','desc')->value('rate');
@endphp

<!-- Static exchange button under counters (non-floating) -->
<div class="mt-3 mb-4 admin-exchange-wrapper">
    <div class="exchange-toggle-fixed">
        <a href="{{ route('admin.exchange-rate.index') }}" class="btn btn-primary exchange-btn" title="Tasa del día (Bs por USD)">Bs</a>
        @if($exchangeRate)
            <div class="small text-muted mt-1">Tasa: {{ number_format($exchangeRate,2) }} Bs</div>
        @endif
    </div>
</div>
