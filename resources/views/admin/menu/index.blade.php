@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Categorías</h1>
        <a href="{{ route('admin.menu.create') }}" class="btn btn-primary">Crear categoría</a>
    </div>

    <div class="row g-3">
        @foreach($menus as $m)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card admin-category-card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $m->menuName }}</h5>
                        <div class="mt-auto">
                            <a href="{{ route('admin.menu.edit', $m->menuID) }}" class="btn btn-sm btn-outline-secondary me-2">Editar</a>
                            <a href="{{ route('admin.menuitem.index', ['menu' => $m->menuID]) }}" class="btn btn-sm btn-outline-primary me-2">Ver productos</a>
                            <a href="{{ route('admin.menuitem.create', ['menu' => $m->menuID]) }}" class="btn btn-sm btn-success">Crear producto</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
