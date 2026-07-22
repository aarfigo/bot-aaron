@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card mx-auto" style="max-width:820px;">
        <div class="card-body">
            <h3 class="card-title mb-3">Crear Staff</h3>
            <p class="text-muted mb-3">Crea un nuevo usuario del personal. Solo completa los campos necesarios.</p>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.staff.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input name="staffName" class="form-control form-control-lg" placeholder="Nombre completo" value="{{ old('staffName') }}" />
                </div>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input name="username" class="form-control form-control-lg" placeholder="usuario para login" value="{{ old('username') }}" />
                    <div class="form-text">El username se usa para iniciar sesión.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input name="password" type="password" class="form-control form-control-lg" placeholder="Dejar en blanco para generar/usar uno temporal" />
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <select name="role" class="form-select form-select-lg">
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="mesero" {{ old('role', 'mesero') == 'mesero' ? 'selected' : '' }}>Mesero</option>
                            <option value="cocina_barra" {{ old('role') == 'cocina_barra' ? 'selected' : '' }}>Cocina/Barra</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select form-select-lg">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                    <button class="btn btn-primary btn-lg">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
