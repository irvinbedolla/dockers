@extends('layouts.app')
@section('title', 'Asesoría')

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading">Asesoría</h3>
            @can('asesoria_crear')
                <a class="btn btn-primary open-modal btn-sm" style="background-color: #CEA845; border-color: #CEA845; color: #fff;" data-bs-toggle="modal" data-bs-target="#modalNuevaAsesoria">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Asesoria
                </a>
            @endcan
        </div>
        <div class="section-body">
            @php $fecha_actual = date('d-m-Y'); @endphp
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            
                            
                            <!-- Alerta de Éxito al guardar -->
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong><i class="fas fa-check-circle me-1"></i> ¡Excelente!</strong> {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                                </div>
                            @endif

                            <!-- Validación de errores -->
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul class="mb-0 mt-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                                </div>
                            @endif

                            
                            <div class="table-responsive">
                                @can('asesoria_consultar')
                                    <table id="example" class="table-striped" style="width:100%">
                                        <thead style="background-color: #354647;">
                                            <th class="text-center text-white" style="color: #ffffff !important;">Fecha</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Delegación</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Nombre</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Acciones</th>
                                        </thead>
                                        <tbody class="contenidobusqueda">
                                            @foreach($asesorias as $asesoria)
                                                <tr>
                                                    <td class="text-center">{{$asesoria->fecha}}</td>
                                                    <td class="text-center">{{$asesoria->delegacion}}</td>
                                                    <td >{{$asesoria->nombre}}</td>
                                                    
                                                    <td class="text-center text-white">
                                                       
                                                        
                                                        @can('asesoria_editar')<button type="button" class="btn btn-info open-modal btn-sm" data-id="{{ $asesoria->id }}" data-asesoria="{{ json_encode($asesoria)}}" data-bs-toggle="modal" data-bs-target="#modalNuevaAsesoria"><i class="bi bi-pencil"></i> Editar Asesoria</button>@endcan                                                                                                
                                                        @can('asesoria_borrar')<button type="button" class="btn btn-danger open-modal btn-sm" data-id="{{ $asesoria->id }}" data-bs-toggle="modal" data-bs-target="#modalBorrarAsesoria"><i class="bi bi-x-circle"></i> Borrar Asesoria</button>@endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endcan
                            </div>
                            

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<div class="modal fade" id="modalNuevaAsesoria" tabindex="-1" aria-labelledby="revisarModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate' method='POST' action="{{route('seer.store_asesoria')}}">
        @csrf
        <input type="hidden" name="asesoria_id" id="asesoria_id" value="">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header" style="background-color: #354647; color: white;">
                    <h5 class="modal-title" id="revisarModalLabel">Nueva Asesoria</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" required value="{{ old('nombre') }}" oninput="this.value = this.value.toUpperCase()" >
                                <div class="invalid-feedback">
                                    El nombre es obligatorio.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xs-12 col-sm-6 col-md-6">
                            <div class="form-group">
                                <label for="sexo">Sexo</label>
                                <select class="form-control" name="sexo" id="sexo" required>
                                    <option value="">Seleccione</option>
                                    <option value="Hombre" {{ old('sexo') == 'Hombre' ? 'selected' : '' }}>Hombre</option>
                                    <option value="Mujer" {{ old('sexo') == 'Mujer' ? 'selected' : '' }}>Mujer</option>
                                    <option value="Otro" {{ old('sexo') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo sexo es obligatorio.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    @can('asesoria_crear')
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar
                            </button>
                    @endcan
                </div>

            </div>
        </div>
    </form>
</div>

<!-- Modal Confirmar Borrado -->
<div class="modal fade" id="modalBorrarAsesoria" tabindex="-1" aria-labelledby="modalBorrarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <form method="POST" action="{{ route('seer.destroy_asesoria') }}">
                @csrf
                
                <input type="hidden" name="asesoria_id" id="delete_asesoria_id" value="">

                <div class="modal-header" style="background-color: #dc3545; color: white;">
                    <h5 class="modal-title" id="modalBorrarLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body text-center mt-3">
                    <h4 >¿Estás seguro?</h4>
                    <h5>Esta acción no se puede deshacer</h5>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash-fill me-1"></i> Sí, eliminar
                    </button>
                </div>
            </form>
            
        </div>
    </div>
</div>


@section('scripts')
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }
            $('#example').DataTable({
                "destroy": true,
                "paging": true,
                "pageLength": 10,
                "searching": true,
                "ordering": true,
                "order": [],
                "info": true,
                "language": {
                    "search": "Filtrar en esta pantalla:",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ solicitudes",
                    "infoEmpty": "Mostrando 0 a 0 de 0 filas",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "zeroRecords": "No se encontraron coincidencias en esta página."
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
    
            const modalEditar = document.getElementById('modalNuevaAsesoria');
            if (modalEditar) {
                modalEditar.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget; 
                    if (!button) return; 

                    const id = button.getAttribute('data-id') || '';
                    const asesoriaData = button.getAttribute('data-asesoria'); 

                    const form = modalEditar.querySelector('form');
                    if(form) form.reset(); 

                    const inputId = form.querySelector('input[name="asesoria_id"]');
                    if (inputId) inputId.value = id;
                    
                    if (asesoriaData && asesoriaData !== '{}') {
                        const asesoria = JSON.parse(asesoriaData);

                        const inputNombre = form.querySelector('input[name="nombre"]');
                        const selectSexo = form.querySelector('select[name="sexo"]');
                        
                        if (inputNombre) inputNombre.value = asesoria.nombre || '';
                        if (selectSexo) selectSexo.value = asesoria.sexo || '';
                        
                    }
                });
            }
        });

        const modalBorrar = document.getElementById('modalBorrarAsesoria');
        if (modalBorrar) {
            modalBorrar.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; 
                if (!button) return;

                const id = button.getAttribute('data-id');

                const inputDeleteId = modalBorrar.querySelector('#delete_asesoria_id');
                if (inputDeleteId) {
                    inputDeleteId.value = id;
                }
            });
        }
    
    </script>
    <script src="{{ asset('assets/js/estadistica/estadistica.js') }}"></script>
@endsection