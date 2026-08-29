@extends('layouts.app')

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
                        
                        @can('ver-seer')
                            <!-- Formulario de búsqueda nativo (Opcional si usas DataTables) -->
                            <form method="GET" action="{{ route('notificaciones') }}" class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="busqueda" class="form-control" placeholder="Buscar por expediente, citado, dirección o tipo..." value="{{ request('busqueda') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                        @if(request('busqueda'))
                                            <a href="{{ route('notificaciones') }}" class="btn btn-secondary">
                                                <i class="fas fa-times"></i> Limpiar
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table id="example" class="table table-striped mt-1 w-100" style="text-align:center;">
                                    <thead style="background-color: #354647;">
                                        <tr>
                                            <th style="color: #fff;">Expediente</th>
                                            <th style="color: #fff;">Citado</th>
                                            <th style="color: #fff;">Dirección</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Tipo</th>
                                            <th class="text-center" style="color: #fff;">Asignar</th>
                                            <th style="color: #fff;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mis_notificaciones as $notificacion)
                                            <!-- Si necesitas el ID oculto para algún script, es mejor ponerlo aquí en el tr -->
                                            <tr data-id="{{ $notificacion->id_solicitud }}">
                                                <td>{{$notificacion->NUE}}</td>
                                                <td>{{$notificacion->nombre}} {{$notificacion->primer_apellido}} {{$notificacion->segundo_apellido}}</td>
                                                <td>
                                                    COLONIA {{$notificacion->colonia}}, {{$notificacion->tipo_vialidad}} {{$notificacion->calle}} #{{$notificacion->n_ext}} 
                                                    @if(!empty($notificacion->n_int))
                                                        INT. {{ $notificacion->n_int }}
                                                    @endif
                                                    {{mb_strtoupper($notificacion->municipio_nombre, 'UTF-8')}}, {{mb_strtoupper($notificacion->estado_nombre, 'UTF-8')}}
                                                </td>
                                                <td>{{$notificacion->estatus}}</td>
                                                <td>{{$notificacion->tipo_notificacion}}</td>
                                                
                                                <!-- Columna Asignar unificada -->
                                                <td>
                                                    @if($notificacion->estatus == "Pendiente" || $notificacion->estatus == "Sin asignar")
                                                        <div class="d-flex align-items-center justify-content-center" style="gap: 10px;">
                                                            <form id="form-asignar-{{$notificacion->id_citado}}" method="POST" action="{{ route('seer.store_enlace', $notificacion->id_citado) }}" class="needs-validation m-0 w-100 novalidate">
                                                                @csrf
                                                                <input type="hidden" name="id" value="{{$notificacion->id_solicitud}}">
                                                                <select class="form-control" name="notificador" required>
                                                                    <option value="">Seleccione</option>
                                                                    @foreach($personas as $persona)
                                                                        <option value="{{$persona->id}}">{{$persona->name}}</option>
                                                                    @endforeach
                                                                </select> 
                                                            </form>
                                                            <button type="submit" form="form-asignar-{{$notificacion->id_citado}}" class="btn btn-primary btn-sm text-nowrap">
                                                                <i class="bi bi-arrow-left-square"></i> Asignar
                                                            </button>  
                                                        </div>
                                                    @else
                                                        {{$notificacion->notificador_nombre}}
                                                    @endif
                                                </td>
                                                
                                                <!-- Columna Acciones -->
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-center flex-wrap" style="gap: 5px;"> 
                                                        @if($notificacion->estatus == "Pendiente" || $notificacion->estatus == "Sin asignar")
                                                            <a class="btn btn-info text-white btn-sm" href="{{ route('editar_citado', $notificacion->id_citado) }}" onclick="consultar_estadistica();">
                                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                                            </a>
                                                        @endif
                                                        
                                                        @if($notificacion->estatus === "Finalizado exitosamente")
                                                            <div class="dropdown">
                                                                <button class="btn btn-primary dropdown-toggle btn-sm" type="button" id="dropdownMenuButton{{$notificacion->id_citado}}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{$notificacion->id_citado}}">
                                                                    @if($notificacion->tipo_notificacion === "Citatorio")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFRazonNoticacion', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                    @endif
                                                                    @if($notificacion->tipo_notificacion === "Multa")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFmulta', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif     

                                                        @if($notificacion->estatus === "No notificada")
                                                            <div class="dropdown">
                                                                <button class="btn btn-primary dropdown-toggle btn-sm" type="button" id="dropdownMenuButton{{$notificacion->id_citado}}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{$notificacion->id_citado}}">
                                                                    @if($notificacion->tipo_notificacion === "Citatorio")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFInstructivo', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                    @endif
                                                                    @if($notificacion->tipo_notificacion === "Multa")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFmulta', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif      

                                                        @if($notificacion->estatus === "No exitosa se constituye")
                                                            <div class="dropdown">
                                                                <button class="btn btn-primary dropdown-toggle btn-sm" type="button" id="dropdownMenuButton{{$notificacion->id_citado}}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{$notificacion->id_citado}}">
                                                                    @if($notificacion->tipo_notificacion === "Citatorio")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFNoExitosa', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                    @endif
                                                                    @if($notificacion->tipo_notificacion === "Multa")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFmulta', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif                                      

                                                        @if($notificacion->estatus === "No exitosa no se constituye")
                                                            <a class="btn btn-success btn-sm" href="{{ route('PDFNoExitosaInt', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a>
                                                        @endif
                                                    </div> 
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endcan
                        
                        <!-- Paginación nativa comentada. DataTables manejará esto ahora -->
                        <!-- 
                        <div class="d-flex justify-content-end mt-2">
                            {{ $mis_notificaciones->links('pagination::bootstrap-4') }}
                        </div> 
                        -->                     
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('body_end')
    <div id="menu_carga" style="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>
    @endpush
</section>
@endsection

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
    </script>
@endsection