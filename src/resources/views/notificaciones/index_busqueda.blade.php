@extends('layouts.app')
@section('title', 'Notificaciones')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Notificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            
                            <div class="table-responsive menu-visible">
                                <table id="example" class="table table-striped mt-1" style="text-align:center">
                                    <thead style="background-color: #354647;">
                                        <th style="color: #fff;">Expediente</th>
                                        <th style="color: #fff;">Citado</th>
                                        <th style="color: #fff;">Dirección</th>
                                        <th style="color: #fff;">Estatus</th>
                                        <th style="color: #fff;">Medio</th>
                                        <th style="color: #fff;">Tipo de notificación</th>
                                        @hasanyrole('Enlace|Super Usuario')
                                            <th style="color: #fff;">Notificador asignado</th>
                                        @endhasanyrole
                                        <th style="color: #fff;">Editar</th>
                                        <th style="color: #fff;">Documento</th>
                                    </thead>
                                    <tbody>
                                        @foreach($notificaciones as $notificacion)
                                            <tr>
                                                <td>{{$notificacion->NUE}}</td>
                                                <td>{{$notificacion->nombre}} {{$notificacion->primer_apellido}} {{$notificacion->segundo_apellido}}</td>
                                                <td>COLONIA {{$notificacion->colonia}}, {{$notificacion->tipo_vialidad}} {{$notificacion->calle}} #{{$notificacion->n_ext}} 
                                                    @if(!empty($notificacion->n_int))
                                                        INT. {{ $notificacion->n_int }}
                                                    @endif{{mb_strtoupper($notificacion->municipio_citado, 'UTF-8')}}, {{mb_strtoupper($notificacion->estado_citado, 'UTF-8')}}</td>
                                                <td>{{$notificacion->estatus}}</td>
                                                <td>{{$notificacion->notificacion}}</td>
                                                <td>{{$notificacion->tipo_notificacion}}</td>
                                                @hasanyrole('Enlace|Super Usuario')
                                                    <td>
                                                        @if($notificacion->estatus === "Notificada en Audiencia")
                                                            -
                                                        @else
                                                            <div>{{ $notificacion->notificador_nombre ?? 'Sin asignar' }}</div>
                                                            @php
                                                                $opcionesNotificadores = ($notificadoresPorSede ?? collect())->get($notificacion->delegacion, collect());
                                                            @endphp
                                                            @if(!empty($notificacion->id_notificador) && $opcionesNotificadores->isNotEmpty())
                                                                <button type="button"
                                                                        class="btn btn-warning btn-sm mt-1 open-notificador-modal"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#asignarNotificadorModal"
                                                                        data-id="{{ $notificacion->id }}"
                                                                        data-notificador="{{ $notificacion->id_notificador }}"
                                                                        data-opciones="{{ $opcionesNotificadores->map(fn($o) => ['id' => $o->id, 'name' => $o->name])->values() }}">
                                                                    Reasignar
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                @endhasanyrole
                                                <td>
                                                    <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('editar_citado_historial')}}">
                                                        @csrf
                                                        <input type="hidden" name="id_solicitud" value="{{ $notificacion->id_solicitud}}">
                                                        <input type="hidden" name="id" value="{{ $notificacion->id}}">
                                                        <button type="submit" class="btn btn-primary">Editar</button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <div class="col-xs-12 col-sm-12 col-md-12"> 
                                                        @if($notificacion->estatus === "Finalizado exitosamente")
                                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">Documentos</button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFRazonNoticacion', [$notificacion->id, $notificacion->id_solicitud]) }}"  target="_blank">Notificación</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFmultaNotificacion', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                            </ul>
                                                        @endif     
                                                        @if($notificacion->estatus === "No notificada" || $notificacion->estatus === "Notificada en Audiencia")
                                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                        </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFInstructivo', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFMultaInstructivo', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                            </ul>
                                                        @endif      
                                                        @if($notificacion->estatus === "No exitosa se constituye")
                                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button> 
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li class="mb-2"><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFNoExitConstituye', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFMultaNoExitConstituye', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                            </ul>
                                                        @endif                                      
                                                        @if($notificacion->estatus === "No exitosa no se constituye")
                                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button> 
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li class="mb-2"><a class="btn btn-info" style="width: 100%" href="{{ route('PDFnotificadoNoexitosaNS', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFMultaNoExitConstituye', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                            </ul>
                                                        @endif 
                                                    </div> 
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                                
                            <!-- Centramos la paginación a la derecha-->
                            <div class="pagination justify-content-end">
                            </div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="menu_carga" style ="display: none;">
            <div>.</div>
            <div class="loader"></div>
        </div>

        @hasanyrole('Enlace|Super Usuario')
        @push('modals')
            <div class="modal fade" id="asignarNotificadorModal" tabindex="-1" aria-labelledby="asignarNotificadorModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content needs-validation" novalidate method="POST" action="{{ route('asignar_notificador_busqueda') }}">
                        @csrf
                        <input type="hidden" name="id" id="asignarNotificador_id">
                        <div class="modal-header">
                            <h5 class="modal-title" id="asignarNotificadorModalLabel">Reasignar notificador</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="asignarNotificador_select">Notificador</label>
                                <select class="form-control" name="id_notificador" id="asignarNotificador_select" required>
                                    <option value="">Seleccione</option>
                                </select>
                                <div class="invalid-feedback">
                                    Debe seleccionar un notificador.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-warning">Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
        @endpush
        @endhasanyrole

@section('scripts')
    <script src="{{ asset('assets/js/estadistica/estadistica.js') }}"></script>
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
                "info": true,
                "language": {
                    "search": "Filtrar en esta pantalla:",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ solicitudes",
                    "infoEmpty": "Mostrando 0 a 0 de 0 filas",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "zeroRecords": "No se encontraron coincidencias en esta página.",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });
        });
        $(document).on('click', '.open-notificador-modal', function() {
            var idCitado = $(this).data('id');
            var idNotificadorActual = $(this).data('notificador');
            var opciones = $(this).data('opciones') || [];

            document.getElementById('asignarNotificador_id').value = idCitado;

            var $select = $('#asignarNotificador_select');
            $select.find('option:not(:first)').remove();
            opciones.forEach(function(opcion) {
                var $option = $('<option></option>').val(opcion.id).text(opcion.name);
                if (String(opcion.id) === String(idNotificadorActual)) {
                    $option.prop('selected', true);
                }
                $select.append($option);
            });
        });
        
    </script>
@endsection
        
    </section>
    
    
@endsection




