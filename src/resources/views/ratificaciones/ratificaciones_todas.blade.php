@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Ratificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group">
                                            <input type="text" name="buscar" class="form-control" placeholder="Buscar ratificación por NUE o Empresa..." value="{{ request('buscar') }}">
                                            <button class="btn btn-primary" type="submit" style="background-color: #4A001F; border-color: #4A001F;">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                            @if(request('buscar'))
                                                <a href="{{ url()->current() }}" class="btn btn-secondary">Limpiar</a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-2"> 
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">N° Interno</th> 
                                            <th style="color: #fff;">NUE</th> 
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Empresa</th>
                                            <th style="color: #fff;">Teléfono</th>
                                            <th style="color: #fff;">Correo</th>
                                            <th style="color: #fff;">Trabajador</th>
                                            <th style="color: #fff;">Delegación</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Detalles</th>
                                            <th style="color: #fff;">Concluir</th>
                                            <th style="color: #fff;">Cumplimientos</th>
                                            <th style="color: #fff;">Documentos</th>
                                            @if($userRole == "Enlace" || $userRole == "Super Usuario" || $userRole == "Auxiliar")
                                                <th style="color: #fff;">Editar</th>
                                            @endif
                                        </thead>
                                        <tbody>
                                            @foreach($solicitudes as $solicitud)
                                                <tr>
                                                    <td>{{$solicitud->consecutivo}}</td>
                                                    <td>{{$solicitud->NUE}}</td>
                                                    <td>{{$solicitud->fecha}}</td> 
                                                    <td>@if(is_null($solicitud->nombre_empresa) && is_null($solicitud->primero_empresa) && is_null($solicitud->segundo_empresa))
                                                        {{$solicitud->empresa}}
                                                       @else {{$solicitud->nombre_empresa}} {{$solicitud->primero_empresa}} {{$solicitud->segundo_empresa}}@endif</td>
                                                    <td>{{$solicitud->telefono}}</td>
                                                    <td>{{$solicitud->email}}</td>
                                                    <td>{{$solicitud->trabajador}} {{$solicitud->primero_trabajador}}  {{$solicitud->segundo_trabajador}}</td>
                                                    <td>{{$solicitud->delegacion}}</td>
                                                    <td>{{$solicitud->estatus}}</td>
                                                    <td><a class="btn btn-primary" href="{{ route('consultar_ratificacion', $solicitud->id) }}">Consultar</a></td>
                                                    <td>
                                                        @if($solicitud->estatus == "Confirmado")
                                                            <a class="btn btn-info" href="{{ route('ratificacion_concluir', $solicitud->id) }}">Concluir</a>
                                                        @endif
                                                        @if($solicitud->estatus == "Concluida Pagos")
                                                            <a class="btn btn-info" href="{{ route('ratificacion_pagar', $solicitud->id) }}">Pagar</a> 
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($solicitud->estatus == "Concluida" || $solicitud->estatus == "Concluida Pagos")
                                                            <a class="btn btn-primary" href="{{ route('ratificacion_cumplimientos', $solicitud->id) }}">Generar cumplimiento</a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-warning open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $solicitud->id }}">Subir Documento</button>
                                                        @if($solicitud->estatus == "Concluida")
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    @if(($solicitud->estatus == "Concluida" && $solicitud->motivo == "Pago de prestaciones" && $solicitud->PagoPTU == "1") || ($solicitud->estatus == "Concluida" && $solicitud->motivo == "PTU"))
                                                                        <li><a class="dropdown-item" href="{{ route('PDFconvenioPTU_NO_R', $solicitud->id) }}"  target="_blank">Convenio PTU</a></li>
                                                                    @else
                                                                        <li><a class="dropdown-item" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}"  target="_blank">Convenio</a></li>
                                                                    @endif
                                                                    <li><a class="dropdown-item" href="{{ route('PDFaudiencia', $solicitud->id) }}"  target="_blank">Acta de audiencia</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfo', ['tipo' => 'ratificacion', 'id' => $solicitud->id]) }}"  target="_blank">Imprimir solicitud</a></li>
                                                                    @if($solicitud->constancia == 0)
                                                                        <li><a class="dropdown-item" href="{{ route('PDFcumplimientoR', $solicitud->id) }}"  target="_blank">Constancia de cumplimiento</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @elseif($solicitud->estatus == "Concluida Pagos")
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    @if(($solicitud->estatus == "Concluida" && $solicitud->motivo == "Pago de prestaciones" && $solicitud->PagoPTU == "1") || ($solicitud->estatus == "Concluida" && $solicitud->motivo == "PTU"))
                                                                        <li><a class="dropdown-item" href="{{ route('PDFconvenioPTU_NO_R', $solicitud->id) }}"  target="_blank">Convenio PTU</a></li>
                                                                    @else
                                                                        <li><a class="dropdown-item" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}"  target="_blank">Convenio</a></li>
                                                                    @endif
                                                                    <li><a class="dropdown-item" href="{{ route('PDFaudiencia', $solicitud->id) }}"  target="_blank">Acta de audiencia</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfo', ['tipo' => 'ratificacion', 'id' => $solicitud->id]) }}"  target="_blank">Imprimir solicitud</a></li>
                                                                </ul>
                                                            </div>
                                                        @elseif($solicitud->estatus == "Confirmado")
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    <a class="btn btn-success" href="{{ route('PDFratifi', $solicitud->id) }}"  target="_blank">Acuse</a>
                                                                </ul>
                                                            </div>
                                                        @elseif($solicitud->estatus == "Incumplimiento")
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    @if(($solicitud->estatus == "Concluida" && $solicitud->motivo == "Pago de prestaciones" && $solicitud->PagoPTU == "1") || ($solicitud->estatus == "Concluida" && $solicitud->motivo == "PTU"))
                                                                        <li><a class="dropdown-item" href="{{ route('PDFconvenioPTU_NO_R', $solicitud->id) }}"  target="_blank">Convenio PTU</a></li>
                                                                    @else
                                                                        <li><a class="dropdown-item" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}"  target="_blank">Convenio</a></li>
                                                                    @endif
                                                                    <a class="btn btn-success" href="{{ route('PDFincumplimiento', $solicitud->id) }}"  target="_blank">Incumplimiento</a>
                                                                </ul>
                                                            </div>
                                                        @elseif($solicitud->estatus == "Archivada")
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    <a class="btn btn-success" href="{{ route('PDFinteres', $solicitud->id) }}"  target="_blank">Acta de Archivo</a><br><br>
                                                                    <a class="btn btn-info" href="{{ route('PDFincomparecenciaT', $solicitud->id) }}" target="_blank">Certificado de incomparecencia del trabajador</a>
                                                                </ul>
                                                            </div>
                                                        @endif                                                        
                                                    </td>
                                                    @if($userRole == "Enlace" || $userRole == "Super Usuario" || $userRole == "Auxiliar")
                                                        <td>
                                                            <a class="btn btn-success" href="{{ route('vista_previa_citas', $solicitud->id) }}">Editar</a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            <!-- Centramos la paginación a la derecha-->
                            <div class="pagination justify-content-end"></div>

                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class='needs-validation novalidate'  method='POST' action="{{route('rechazar_turnos')}}">
            @csrf
            <input type="hidden" id="modal-id" name="id" value="">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Motivo de rechazo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea name="observaciones" style="width:100%"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- Modal Expediente -->
    <div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <form  class='needs-validation novalidate' method='POST' action="{{ route('subir_expediente_ratificacion') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="audiencia_id" id="expediente_audiencia_id" value="">
            <div class="modal-dialog modal-l">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Subir expediente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label>Documento en PDF</label>
                                <input type="file" name="documentoExpediente" class="form-control" accept=".pdf" required>
                                <div class="invalid-feedback">
                                    El documento es obligatorio.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div  class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group"><br>
                            <label for="name">Nombre de expediente<span style="color:red;">(*)</span></label>
                            <input type="text" name="nombreExpediente" class="form-control" required> 
                            <div class="invalid-feedback">
                                El nombre para el expediente es obligatorio.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Agregar</button> 
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="nuevo_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

@section('scripts')
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#tu_id_de_tabla_ratificaciones')) {
                $('#tu_id_de_tabla_ratificaciones').DataTable().destroy();
            }

            $('#tu_id_de_tabla_ratificaciones').DataTable({
                "destroy": true,
                "paging": true,        // Segmenta de 10 en 10 localmente las 500 filas
                "pageLength": 10,
                "searching": true,     // Activa el filtro rápido de la esquina de DataTables
                "ordering": true,
                "info": true,
                "language": {
                    "search": "Filtrar en esta pantalla:",
                    "lengthMenu": "Mostrar _MENU_ filas",
                    "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ registros",
                    "zeroRecords": "No se encontraron coincidencias en esta página. Use el buscador superior."
                }
            });
        });
        
        $(document).on('click', '.open-expediente-modal', function() {
            // 2. Capturar el 'data-id'
            var idRegistro = $(this).data('id');            
            document.getElementById('expediente_audiencia_id').value = idRegistro;
        });
    </script>
    <script src="../public/assets/js/poderes/general.js"></script>
@endsection
