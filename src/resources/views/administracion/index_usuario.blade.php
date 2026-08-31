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
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            @can('ver-usuario')
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped table-hover align-middle w-100">
                                        <thead style="background-color: #354647; color: #fff;">
                                            <tr>
                                                <th class="text-center text-white" style="width: 5%; color: #ffffff !important;">Folio</th>
                                                <th class="text-white" style="color: #ffffff !important;">Nombre</th>
                                                <th class="text-white" style="color: #ffffff !important;">E-mail</th>
                                                <th class="text-white" style="color: #ffffff !important;">Rol</th>
                                                <th class="text-white" style="color: #ffffff !important;">Delegación</th>
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
                                                    <td>
                                                        <div class="d-flex justify-content-center gap-2">
                                                            @can('editar-usuario')
                                                                <a class="btn btn-info btn-sm text-white" href="{{ route('administrador_usuarios_edit', $usuario->id) }}" onclick="editar_usuario();">
                                                                    <i class="bi bi-pencil-square me-1"></i> Editar
                                                                </a>
                                                            @endcan
                                                            @can('borrar-usuario')
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
                            @endcan
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

@section('scripts')
    <script src="{{ asset('assets/js/usuarios/usuarios.js') }}"></script>
    <script>
        $(document).ready(function() {
            if (!$.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable({
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
