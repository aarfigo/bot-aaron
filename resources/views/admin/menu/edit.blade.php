@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Categoría</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('admin.menu.update', $menu->menuID) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="menuName">Nombre de la categoría</label>
            <input id="menuName" name="menuName" class="form-control" required value="{{ old('menuName', $menu->menuName) }}">
        </div>
        <button class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
