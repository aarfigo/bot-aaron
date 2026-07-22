@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card mx-auto" style="max-width:1100px;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Staff</h3>
                <a href="{{ route('admin.staff.create') }}" role="button" class="btn btn-primary btn-sm" style="background-color:#0d6efd!important;border-color:#0d6efd!important;border-radius:8px;padding:6px 12px;display:inline-block;">
                    <span style="color:#ffffff!important;font-weight:700!important;display:inline-block;line-height:1;">Crear Staff</span>
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:70px">ID</th>
                            <th>Nombre</th>
                            <th>Username</th>
                            <th style="width:180px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($staff as $s)
                        <tr>
                            <td class="text-muted">{{ $s->staffID }}</td>
                            <td>{{ $s->staffName }}</td>
                            <td>{{ $s->username }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.staff.edit', $s->staffID) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <form action="{{ route('admin.staff.destroy', $s->staffID) }}" method="POST" onsubmit="return confirm('¿Eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

