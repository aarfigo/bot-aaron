@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="m-0">Mesas</h1>
        <a href="{{ route('admin.tables.create') }}" class="btn btn-dark">Crear mesa</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">Mesas creadas</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width:70px">ID</th>
                        <th>Nombre</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tables as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->name ?? 'Mesa '.$t->number }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.tables.edit', $t->id) }}" class="btn btn-sm btn-outline-secondary me-1">Editar</a>
                                <a href="{{ route('admin.tables.show', $t->id) }}" class="btn btn-sm btn-outline-primary me-1">Ver</a>
                                <form method="POST" action="{{ route('admin.tables.destroy', $t->id) }}" class="d-inline" onsubmit="return confirm('Eliminar mesa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">No hay mesas aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $tables->links() ?? '' }}
    </div>
</div>
@endsection
