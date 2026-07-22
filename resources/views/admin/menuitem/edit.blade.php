@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title mb-3">Editar producto</h3>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.menuitem.update', $item->itemID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Categoría</label>
                        <select name="menuID" class="form-select">
                            @foreach($menus as $m)
                                <option value="{{ $m->menuID }}" {{ $m->menuID == $item->menuID ? 'selected' : '' }}>{{ $m->menuName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nombre</label>
                        <input name="menuItemName" class="form-control" value="{{ old('menuItemName', $item->menuItemName) }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Precio</label>
                        <input name="price" class="form-control" value="{{ old('price', $item->price) }}" />
                    </div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-primary">Actualizar</button>
                    <a href="{{ route('admin.menuitem.index') }}" class="btn btn-outline-secondary ms-2">Volver</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
