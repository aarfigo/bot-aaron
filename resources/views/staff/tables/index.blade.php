@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mesas</h1>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="mb-3 d-flex align-items-center">
        <button id="show-add-table" class="btn btn-sm btn-danger me-2">Agregar mesa</button>
        <div id="add-table-inline" style="display:none; min-width:360px;">
            <div class="input-group">
                <input type="number" id="new-table-number" class="form-control form-control-sm" placeholder="Número de mesa" min="1" />
                <input type="text" id="new-table-name" class="form-control form-control-sm" placeholder="Nombre (opcional)" />
                <button id="add-table-btn" class="btn btn-sm btn-primary">Agregar</button>
            </div>
            <div id="add-table-feedback" class="small text-danger mt-1" style="display:none"></div>
        </div>
    </div>

    @if($tables->isEmpty())
        <div class="alert alert-info">No hay mesas registradas.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:90px">Mesa</th>
                        <th>Nombre</th>
                        <th style="width:120px">Órdenes activas</th>
                        <th style="width:110px">En cola</th>
                        <th style="width:110px">Listas</th>
                        <th style="width:160px">Total acumulado</th>
                        <th style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tables as $t)
                        <tr>
                            <td>{{ $t->number }}</td>
                            <td>{{ $t->name }}</td>
                            <td>{{ intval($t->cnt) }}</td>
                            <td><span class="badge bg-secondary">{{ intval($t->waiting_cnt ?? 0) }}</span></td>
                            <td><span class="badge bg-success">{{ intval($t->ready_cnt ?? 0) }}</span></td>
                            <td><strong>{{ number_format($t->total_sum,2) }}</strong></td>
                            <td>
                                <a href="{{ route('staff.tables.show', $t->number) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-table-btn" data-number="{{ $t->number }}">Editar</button>
                                <form method="POST" action="{{ route('staff.tables.destroy', $t->number) }}" class="d-inline-block" onsubmit="return confirm('¿Eliminar esta mesa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Borrar</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="table-edit-row" id="edit-row-{{ $t->number }}" style="display:none;">
                            <td colspan="7">
                                <form method="POST" action="{{ route('staff.tables.update', $t->number) }}" class="row g-2 align-items-center" onsubmit="return confirm('¿Confirmar actualización de esta mesa?');">
                                    @csrf
                                    @method('PATCH')
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-2">
                                            <input type="number" name="number" class="form-control form-control-sm" value="{{ $t->number }}" min="1" required />
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $t->name }}" placeholder="Nombre de mesa (opcional)" />
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                            <button type="button" class="btn btn-sm btn-secondary cancel-edit">Cancelar</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const showBtn = document.getElementById('show-add-table');
        const inline = document.getElementById('add-table-inline');
        const addBtn = document.getElementById('add-table-btn');
        const feedback = document.getElementById('add-table-feedback');
        const numberIn = document.getElementById('new-table-number');
        const nameIn = document.getElementById('new-table-name');
        let tbody = document.querySelector('.table tbody');
        const container = document.querySelector('.container');
        const emptyAlert = document.querySelector('.alert.alert-info');

        function createTableList(){
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            wrapper.innerHTML = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width:90px">Mesa</th>
                            <th>Nombre</th>
                            <th style="width:120px">Órdenes activas</th>
                            <th style="width:110px">En cola</th>
                            <th style="width:110px">Listas</th>
                            <th style="width:160px">Total acumulado</th>
                            <th style="width:120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `;
            if(emptyAlert) emptyAlert.remove();
            if(container) container.appendChild(wrapper);
            tbody = wrapper.querySelector('tbody');
        }

        function appendTableRow(t){
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${t.number}</td>
                <td>${t.name || ''}</td>
                <td>0</td>
                <td><span class="badge bg-secondary">0</span></td>
                <td><span class="badge bg-success">0</span></td>
                <td><strong>0.00</strong></td>
                <td>
                    <a href="/staff/tables/${t.number}" class="btn btn-sm btn-outline-primary">Ver</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary edit-table-btn" data-number="${t.number}">Editar</button>
                    <form method="POST" action="/staff/tables/${t.number}" class="d-inline-block" onsubmit="return confirm('¿Eliminar esta mesa?');">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="_method" value="DELETE" />
                        <button type="submit" class="btn btn-sm btn-outline-danger">Borrar</button>
                    </form>
                </td>
            `;
            const editRow = document.createElement('tr');
            editRow.className = 'table-edit-row';
            editRow.id = 'edit-row-' + t.number;
            editRow.style.display = 'none';
            editRow.innerHTML = `
                <td colspan="7">
                    <form method="POST" action="/staff/tables/${t.number}" class="row g-2 align-items-center" onsubmit="return confirm('¿Confirmar actualización de esta mesa?');">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="_method" value="PATCH" />
                        <div class="row g-2 align-items-center">
                            <div class="col-md-2">
                                <input type="number" name="number" class="form-control form-control-sm" value="${t.number}" min="1" required />
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="name" class="form-control form-control-sm" value="${t.name || ''}" placeholder="Nombre de mesa (opcional)" />
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-sm btn-success">Guardar</button>
                                <button type="button" class="btn btn-sm btn-secondary cancel-edit">Cancelar</button>
                            </div>
                        </div>
                    </form>
                </td>
            `;
            if(!tbody){
                createTableList();
            }
            tbody.appendChild(tr);
            tbody.appendChild(editRow);

            const appendEditButton = tr.querySelector('.edit-table-btn');
            if(appendEditButton){
                appendEditButton.addEventListener('click', function(){
                    document.querySelectorAll('.table-edit-row').forEach(function(r){ r.style.display = 'none'; });
                    const target = document.getElementById('edit-row-' + t.number);
                    if(target) target.style.display = 'table-row';
                });
            }
            const cancelButton = editRow.querySelector('.cancel-edit');
            if(cancelButton){
                cancelButton.addEventListener('click', function(){
                    editRow.style.display = 'none';
                });
            }
        }

        if(showBtn && inline){
            showBtn.addEventListener('click', function(){ inline.style.display = inline.style.display === 'none' ? '' : 'none'; });
        }

        if(addBtn){
            addBtn.addEventListener('click', function(){
                feedback.style.display = 'none';
                const num = parseInt(numberIn.value || 0);
                const name = (nameIn.value || '').trim();
                if(!num || num < 1){ feedback.textContent = 'Introduce un número de mesa válido'; feedback.style.display = ''; return; }
                addBtn.disabled = true;
                fetch('{{ route('staff.tables.store') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ number: num, name: name })
                }).then(r => r.json()).then(json => {
                    if(json && json.ok && json.table){
                        appendTableRow(json.table);
                        numberIn.value = '';
                        nameIn.value = '';
                        inline.style.display = 'none';
                    } else {
                        feedback.textContent = (json && json.message) ? json.message : 'No se pudo crear la mesa'; feedback.style.display = '';
                    }
                }).catch(err => { feedback.textContent = 'Error al crear la mesa'; feedback.style.display = ''; console.error(err); }).finally(()=>{ addBtn.disabled = false; });
            });
        }

        document.querySelectorAll('.edit-table-btn').forEach(function(btn){
            btn.addEventListener('click', function(){
                const target = document.getElementById('edit-row-' + btn.dataset.number);
                if(!target) return;
                document.querySelectorAll('.table-edit-row').forEach(function(r){ r.style.display = 'none'; });
                target.style.display = 'table-row';
            });
        });
        document.querySelectorAll('.cancel-edit').forEach(function(btn){
            btn.addEventListener('click', function(){
                const row = btn.closest('.table-edit-row');
                if(row) row.style.display = 'none';
            });
        });
    });
    </script>
    @endpush
</div>
@endsection
