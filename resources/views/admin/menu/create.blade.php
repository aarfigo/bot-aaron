@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Categoría</h1>
    <form method="post" action="{{ route('admin.menu.store') }}">
        @csrf
        <div class="mb-3">
            <label for="menuName">Nombre de la categoría</label>
            <input id="menuName" name="menuName" class="form-control" required>
        </div>
        <button class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
