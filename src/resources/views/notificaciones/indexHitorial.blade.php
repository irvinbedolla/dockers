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
                                    <div class="table-responsive">
                                        <table id="example" class="table table-striped mt-1" style="text-align:center">
                                            <thead style="background-color: #4A001F;">
                                                <th style="color: #fff;">Expediente</th>
                                                <th style="color: #fff;">Solicitante</th>
                                                <th style="color: #fff;">Citado</th>
                                                <th style="color: #fff;">Dirección</th>
                                                <th style="color: #fff;">Estatus</th>
                                                <th style="color: #fff;">Tipo</th>
                                                <th style="color: #fff;">Notificador</th>
                                                <th style="color: #fff;">Acciones</th>
                                                <th style="color: #fff;">Documentos</th>
                                            </thead>
                                            <tbody>
                                                @foreach($mis_notificaciones as $notificacion)
                                                    <tr>
                                                        <td>{{$notificacion->NUE}}</td>
                                                        <td>{{$notificacion->nombre_solicitado}}</td>
                                                        <td>{{$notificacion->nombre}} {{$notificacion->primer_apellido}} {{$notificacion->segundo_apellido}}</td>
                                                        <td>COLONIA {{$notificacion->colonia}}, {{$notificacion->tipo_vialidad}} {{$notificacion->calle}} #{{$notificacion->n_ext}} 
                                                            @if(!empty($notificacion->n_int))
                                                                INT. {{ $notificacion->n_int }}
                                                            @endif{{mb_strtoupper($notificacion->municipio_citado, 'UTF-8')}}, {{mb_strtoupper($notificacion->estado_citado, 'UTF-8')}}</td>
                                                        <td>{{$notificacion->estatus}}</td>
                                                        <td>{{$notificacion->tipo_notificacion}}</td>
                                                        <td>{{$notificacion->notificador_nombre}}</td>
                                                        <td>
                                                            @if($notificacion->estatus == "Pendiente" || $notificacion->estatus == "Sin asignar")
                                                                <a class="btn btn-primary" href="{{ route('editar_citado', $notificacion->id_citado) }}" onclick="consultar_estadistica();">Editar</a>
                                                            @else
                                                                Concluida
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($notificacion->estatus === "Finalizado exitosamente")
                                                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        Documentos
                                                                    </button>
                                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                        <li><a class="dropdown-item" href="{{ route('VerDocumentosAudiencia', $notificacion->id_solicitud) }}"  target="_blank">Identificaciones</a></li>
                                                                        @if($notificacion->tipo_notificacion === "Citatorio")
                                                                            <li><a class="dropdown-item" style="width: 100%" href="{{ route('PDFRazonNoticacion', [$notificacion->id_citado, $notificacion->id_solicitud]) }}"  target="_blank">Notificación</a></li>
                                                                        @endif
                                                                        @if($notificacion->tipo_notificacion === "Multa")
                                                                            <li><a class="dropdown-item" style="width: 100%" href="{{ route('PDFmultaNotificacion', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                                        @endif
                                                                        <button type="button" class="btn btn-warning open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $notificacion->id_solicitud }}">Subir Documento</button><br>
                                                                    </ul>
                                                            @endif    
                                                            @if($notificacion->estatus === "No notificada" || $notificacion->estatus === "Exitosa por Instructivo")
                                                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        Documentos
                                                                    </button>
                                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                        <li><a class="dropdown-item" href="{{ route('VerDocumentosAudiencia', $notificacion->id_solicitud) }}"  target="_blank">Identificaciones</a></li>
                                                                        @if($notificacion->tipo_notificacion === "Citatorio")
                                                                            <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFInstructivo', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                        @endif
                                                                        @if($notificacion->tipo_notificacion === "Multa")
                                                                            <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFMultaInstructivo', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                                        @endif
                                                                        <button type="button" class="btn btn-warning open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $notificacion->id_solicitud }}">Subir Documento</button><br>
                                                                    </ul>
                                                            @endif      
                                                            @if($notificacion->estatus === "No exitosa se constituye")
                                                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        Documentos
                                                                    </button>
                                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                        <li><a class="dropdown-item" href="{{ route('VerDocumentosAudiencia', $notificacion->id_solicitud) }}"  target="_blank">Identificaciones</a></li>
                                                                        @if($notificacion->tipo_notificacion === "Citatorio")
                                                                            {{--@if($notificacion->problema_diligencia === "CERRADO")
                                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFNoExitosa', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                            @else--}}
                                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFNoExitConstituye', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                            {{--@endif--}}
                                                                        @endif
                                                                        @if($notificacion->tipo_notificacion === "Multa")
                                                                            <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFMultaNoExitConstituye', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                                        @endif
                                                                        <button type="button" class="btn btn-warning open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $notificacion->id_solicitud }}">Subir Documento</button><br>
                                                                    </ul>
                                                            @endif                                      
                                                            @if($notificacion->estatus === "No exitosa no se constituye")
                                                                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        Documentos
                                                                    </button>
                                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                        <li><a class="dropdown-item" href="{{ route('VerDocumentosAudiencia', $notificacion->id_solicitud) }}"  target="_blank">Identificaciones</a></li>
                                                                        @if($notificacion->tipo_notificacion === "Citatorio")
                                                                            <li><a class="dropdown-item" style="width: 100%" href="{{ route('PDFnotificadoNoexitosaNS', [$notificacion->id_citado, $notificacion->id_solicitud]) }}"  target="_blank">Notificación</a></li>
                                                                        @endif
                                                                        {{--@if($notificacion->tipo_notificacion === "Multa")
                                                                            <li><a class="dropdown-item" style="width: 100%" href="{{ route('PDFmultaNotificacion', [$notificacion->id_citado, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                                        @endif--}}
                                                                        <button type="button" class="btn btn-warning open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $notificacion->id_solicitud }}">Subir Documento</button><br>
                                                                    </ul>
                                                            @endif
                                                            @if($notificacion->estatus === "Sin asignar")
                                                                Pendiente
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                            @endcan
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

        @push('modals')
            <div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content needs-validation" novalidate method="POST" action="{{ route('subir_expediente') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="audiencia_id" id="expediente_audiencia_id">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">Subir expediente</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Documento en PDF</label>
                                    <input type="file" name="documentoExpediente" class="form-control" accept=".pdf" required>
                                    <div class="invalid-feedback">
                                        El documento es obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group"><br>
                                    <label for="name">Nombre de expediente<span style="color:red;">(*)</span></label>
                                    <input type="text" name="nombreExpediente" class="form-control" required>
                                    <div class="invalid-feedback">
                                        El nombre para el expediente es obligatorio.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Agregar</button>
                        </div>
                    </form>
                </div>
            </div>
        @endpush
    
    </section>

    
    
@section('scripts')
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
    <style>
        .modal {
            z-index: 20000;
        }
        .modal-backdrop {
            z-index: 19990;
        }
    </style>
    <script>
        $(document).on('click', '.open-expediente-modal', function() {
            // 2. Capturar el 'data-id'
            var idRegistro = $(this).data('id');            
            document.getElementById('expediente_audiencia_id').value = idRegistro;
        });
    </script>
@endsection
        
    
    
    
@endsection




