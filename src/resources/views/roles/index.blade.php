@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Gestión de Roles</h3>
            @can('crear-rol')
                <a class="btn btn-warning shadow-sm" href="{{ route('roles.create') }}" onclick="crear_rol();" style="background-color: #CEA845; border-color: #CEA845; color: #fff;">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Rol
                </a>
            @endcan
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-hover align-middle w-100">
                                    <thead style="background-color: #4A001F;">
                                        <tr>
                                            <th class="text-white" style="color: #ffffff !important;">Rol</th>
                                            <th class="text-center text-white" style="width: 15%; color: #ffffff !important;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="contenidobusqueda">
                                        @foreach ($roles as $role)
                                            <tr>
                                                <td class="fw-bold">{{ $role->name }}</td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        @can('editar-rol')
                                                            <a class="btn btn-info btn-sm text-white" href="{{ route('roles.edit', $role->id) }}" onclick="editar_rol();">
                                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                                            </a>
                                                        @endcan
                                                        @can('borrar-rol')
                                                            <form method="POST" action="{{ route('roles.destroy', $role->id) }}" class="d-inline mb-0">
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

<div id="menu_carga" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="{{ asset('assets/js/general/menu.js') }}"></script>
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
                        "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ roles",
                        "infoEmpty": "Mostrando 0 a 0 de 0 roles",
                        "infoFiltered": "(filtrado de un total de _MAX_ roles)",
                        "zeroRecords": "No se encontraron coincidencias."
                    }
                });
            }
        });
    </script>
@endsection