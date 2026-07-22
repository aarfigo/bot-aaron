@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card mx-auto" style="max-width:900px;">
        <div class="card-body">
            <h3 class="card-title mb-3">Crear producto</h3>
            <p class="text-muted mb-3">Añade un nuevo producto al menú. Completa nombre, categoría y precio.</p>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.menuitem.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Nombre</label>
                    <input name="menuItemName" class="form-control form-control-lg" placeholder="Ej: Empanada de carne" value="{{ old('menuItemName') }}" />
                    <div class="form-text">Nombre visible en la pantalla de pedidos.</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label">Categoría</label>
                        <select name="menuID" class="form-select form-select-lg">
                            @php $selectedMenu = old('menuID', request()->query('menu')) ?? null; @endphp
                            @foreach($menus as $m)
                                <option value="{{ $m->menuID }}" {{ $selectedMenu == $m->menuID ? 'selected' : '' }}>{{ $m->menuName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Precio</label>
                        <input name="price" type="number" step="0.01" min="0" class="form-control form-control-lg" placeholder="0.00" value="{{ old('price') }}" />
                        <div class="form-text">Introduce el precio en números (ej. 12.50).</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-2">
                    <a href="{{ route('admin.menuitem.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                    <button class="btn btn-success btn-lg">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

