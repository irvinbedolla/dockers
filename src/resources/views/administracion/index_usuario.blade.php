@extends('layouts.app')
@section('title', 'Usuarios')


@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Usuarios</h3>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            {{-- La tabla estaba envuelta en una comprobacion del
                                 permiso 'ver-usuario', que no existe en la tabla
                                 permissions: los que hay son usuarios_crear,
                                 usuarios_editar y usuarios_eliminar. La condicion era
                                 falsa siempre y la pantalla salia en blanco para todos
                                 los roles. Tampoco hace falta: la ruta ya esta detras
                                 de role:Super Usuario|Administrador|Delegado. --}}
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-hover align-middle w-100">
                                    <thead style="background-color: #354647; color: #fff;">
                                        <tr>
                                            <th class="text-center text-white" style="width: 5%; color: #ffffff !important;">Folio</th>
                                            <th class="text-white" style="color: #ffffff !important;">Nombre</th>
                                            <th class="text-white" style="color: #ffffff !important;">E-mail</th>
                                            <th class="text-white" style="color: #ffffff !important;">Rol</th>
                                            <th class="text-white" style="color: #ffffff !important;">Delegación</th>
                                            <th class="text-center text-white" style="width: 12%; color: #ffffff !important;">Estatus</th>
                                            <th class="text-center text-white" style="width: 15%; color: #ffffff !important;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="contenidobusqueda">
                                        @foreach($usuarios as $usuario)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $usuario->id }}</td>
                                                <td>{{ $usuario->name }}</td>
                                                <td>{{ $usuario->email }}</td>
                                                <td>
                                                    @if(!empty($usuario->getRoleNames()))
                                                        @foreach($usuario->getRoleNames() as $rolName)
                                                            <span class="badge bg-dark rounded-pill px-3 py-2 fs-6 fw-normal">{{ $rolName }}</span>
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td>{{ $usuario->delegacion }}</td>
                                                {{-- data-order y data-search le dicen a DataTables que ordene
                                                     y busque por el valor y no por el texto de la celda: dentro
                                                     del <select> viven las dos opciones, así que sin esto todas
                                                     las filas contienen "Activo" y "Inactivo" a la vez. --}}
                                                <td class="text-center"
                                                    data-order="{{ $usuario->estatus }}"
                                                    data-search="{{ $usuario->estatus }}">
                                                    @can('usuarios_editar')
                                                        @if ((int) $usuario->id === (int) auth()->id())
                                                            {{-- La cuenta propia no lleva select: desactivarse a uno
                                                                 mismo deja fuera en el siguiente request. --}}
                                                            <span class="badge bg-success rounded-pill px-3 py-2 fw-normal">Activo</span>
                                                            <div class="form-text">Tu cuenta</div>
                                                        @else
                                                            <form method="POST" action="{{ route('usuarios_estatus', $usuario->id) }}" class="mb-0">
                                                                @csrf
                                                                <input type="hidden" name="_method" value="PATCH">
                                                                <select name="estatus"
                                                                        class="form-select form-select-sm estatus-select @if($usuario->estatus === 'Activo') es-activo @else es-inactivo @endif"
                                                                        aria-label="Estatus de {{ $usuario->name }}"
                                                                        onchange="this.form.submit()">
                                                                    <option value="Activo" @selected($usuario->estatus === 'Activo')>Activo</option>
                                                                    <option value="Inactivo" @selected($usuario->estatus === 'Inactivo')>Inactivo</option>
                                                                </select>
                                                            </form>
                                                        @endif
                                                    @else
                                                        <span class="badge rounded-pill px-3 py-2 fw-normal @if($usuario->estatus === 'Activo') bg-success @else bg-secondary @endif">
                                                            {{ $usuario->estatus }}
                                                        </span>
                                                    @endcan
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        @can('usuarios_editar')
                                                            <a class="btn btn-info btn-sm text-white" href="{{ route('administrador_usuarios_edit', $usuario->id) }}" onclick="editar_usuario();">
                                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                                            </a>
                                                        @endcan
                                                        @can('usuarios_eliminar')
                                                            <form method="POST" action="{{ route('usuarios_destroy', $usuario->id) }}" class="d-inline mb-0">
                                                                @csrf
                                                                <input type="hidden" name="_method" value="DELETE">
                                                                <button class="btn btn-danger btn-sm" onclick="editar_rol();" type="submit">
                                                                    <i class="bi bi-trash me-1"></i> Eliminar
                                                                </button>
                                                            </form>
                                                        @endcan
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
            </div>
        </div>
    </section>
@endsection

@push('body_end')
    <div id="nuevo_usuario" style="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>
@endpush

@section('page_css')
    <style>
        /* El color no es el único indicio: el propio texto del select dice
           Activo o Inactivo. El fondo sólo ayuda a barrer la columna de un
           vistazo. */
        .estatus-select { min-width: 108px; font-weight: 600; }
        .estatus-select.es-activo   { color: #1B5E3F; border-color: #A8CFBC; background-color: #F1F8F4; }
        .estatus-select.es-inactivo { color: #7A4A42; border-color: #E0C3BD; background-color: #FBF4F2; }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/usuarios/usuarios.js') }}"></script>
    <script>
        $(document).ready(function() {
            if (!$.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable({
                    // Sin colapso de columnas: son 7 y se consultan de un vistazo.
                    // El desplazamiento lo da el .table-responsive de Bootstrap que
                    // ya envuelve la tabla; no se usa scrollX porque clona el
                    // <thead> y necesita la hoja de estilos de DataTables, que
                    // este proyecto no carga.
                    "responsive": false,
                    "destroy": true,
                    "paging": true,
                    "pageLength": 10,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "language": {
                        "search": "Filtrar en esta pantalla:",
                        "lengthMenu": "Mostrar _MENU_ registros",
                        "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ usuarios",
                        "infoEmpty": "Mostrando 0 a 0 de 0 usuarios",
                        "infoFiltered": "(filtrado de un total de _MAX_ usuarios)",
                        "zeroRecords": "No se encontraron coincidencias."
                    }
                });
            }
        });
    </script>
@endsection
