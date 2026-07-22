@extends('layouts.app')

@section('content')
<div class="container py-4 mesero-theme">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mesas-title mb-0">MESAS/ORDENES</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Volver al panel</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.exchange-rate.store') }}" class="row g-2 align-items-center">
                @csrf
                <div class="col-auto">
                    <label for="date" class="visually-hidden">Fecha</label>
                    <input type="date" id="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}">
                </div>
                <div class="col-auto">
                    <label for="rate" class="visually-hidden">Tasa (Bs por USD)</label>
                    <input type="number" step="0.01" min="0" id="rate" name="rate" class="form-control" placeholder="Ej. 30.50" value="{{ old('rate', optional($rate)->rate) }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Guardar tasa</button>
                </div>
                <div class="col-12">
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="exchange-history mt-4">
        <h2 class="h5">Historial de tasas (últimos 30 días)</h2>
        <div class="table-responsive mt-2">
            <table class="table table-sm">
                <thead>
                    <tr><th>Fecha</th><th>Tasa (Bs por USD)</th></tr>
                </thead>
                <tbody>
                    @foreach($rates as $r)
                        <tr>
                            <td>{{ $r->date }}</td>
                            <td>{{ number_format($r->rate ?? 0, 2, '.', ',') }} Bs</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
