@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Productos</h3>
        <a href="{{ route('admin.menuitem.create') }}" class="btn btn-primary">Crear producto</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th style="width:70px;">ID</th>
                <th>Nombre</th>
                <th style="width:150px;">Categoría</th>
                <th style="width:160px;">Precio</th>
                <th style="width:180px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td class="fw-bold">{{ $it->itemID }}</td>
                <td>{{ $it->menuItemName }}</td>
                <td><span class="badge bg-secondary">{{ optional($it->menu)->menuName }}</span></td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm price-input" data-id="{{ $it->itemID }}" value="{{ number_format($it->price,2,'.','') }}" />
                        <span class="text-success ms-2 save-ok" data-id="{{ $it->itemID }}" style="display:none;">Guardado</span>
                        <small class="text-muted ms-3 price-bs" data-id="{{ $it->itemID }}">@if($exchangeRate) {{ number_format($it->price * $exchangeRate,2) }} Bs @else N/D @endif</small>
                    </div>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.menuitem.edit', $it->itemID) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <form action="{{ route('admin.menuitem.destroy', $it->itemID) }}" method="POST" onsubmit="return confirm('Eliminar item?');">
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    document.querySelectorAll('.price-input').forEach(function(input){
        let timeout;
        input.addEventListener('input', function(){
            clearTimeout(timeout);
            const id = this.dataset.id;
            const value = this.value;
            // debounce to avoid too many requests
            timeout = setTimeout(()=>{
                fetch(`/admin/menuitem/${id}/price`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ price: value })
                }).then(r=>r.json()).then(data=>{
                    if(data.status === 'ok'){
                        const ok = document.querySelector('.save-ok[data-id="'+id+'"]');
                        if(ok){ ok.style.display = 'inline'; setTimeout(()=> ok.style.display='none',1200); }
                    } else {
                        alert('Error al guardar precio');
                    }
                }).catch(e=>{ console.error(e); alert('Error al guardar precio'); });
            }, 500);
        });
    });
});
</script>
@endpush

