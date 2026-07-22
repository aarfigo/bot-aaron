@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Editar Staff</h3>

    @if($errors->any())
        <div class="alert alert-danger"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form action="{{ route('admin.staff.update', $staff->staffID) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3"><label class="form-label">Nombre</label><input name="staffName" class="form-control" value="{{ old('staffName', $staff->staffName) }}" /></div>
        <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" value="{{ old('username', $staff->username) }}" /></div>
        <div class="mb-3"><label class="form-label">Password (dejar vacío para mantener)</label><input name="password" type="password" class="form-control" /></div>
        <button class="btn btn-primary">Actualizar</button>
    </form>
</div>
@endsection
