@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Resultado de la busqueda</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabla1" class="table-striped" style="width:100%">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;">Folio</th>
                                        <th style="color: #fff;">Fecha Captura</th>
                                        <th style="color: #fff;">Solicitante</th>
                                        <th style="color: #fff;">Actividad Economica</th>
                                        <th style="color: #fff;">Tipo Solicitud</th>
                                        <th style="color: #fff;">Estatus</th>
                                        <th style="color: #fff;">Revisar</th>
                                        <th style="color: #fff;">Acciones</th>
                                        <th style="color: #fff;">Documentos</th>
                                    </thead>
                                    <tbody class="contenidobusqueda">
                                        @foreach($solicitudes as $solicitud)
                                            <tr>
                                                <td>{{$solicitud->id}}</td>
                                                <td>{{$solicitud->fecha}}</td>
                                                <td>{{$solicitud->nombre}}</td>
                                                <td>{{$solicitud->actividad}}</td>
                                                @if($solicitud->tipo_solicitud == 1)
                                                    <td>Trabajador</td>
                                                @elseif($solicitud->tipo_solicitud == 2)
                                                    <td>Patronal</td>
                                                @elseif($solicitud->tipo_solicitud == 3)
                                                    <td>Patronal Colectiva</td>
                                                @elseif($solicitud->tipo_solicitud == 4)
                                                    <td>Sindical</td>
                                                @endif
                                                <td>{{$solicitud->estatus}}</td>
                                                <td>
                                                    <a class="btn btn-info" href="{{ route('solicitud_revisar', $solicitud->id)}}" target="_blank">Revisar</a>
                                                </td>
                                                <td>
                                                    @if($solicitud->estatus == "Confirmado")
                                                        <a class="btn btn-success" href="{{ route('inicioAudiencia', $solicitud->id, 'Confirmado') }}">Iniciar</a><br>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $solicitud->id }}">Subir Documento</button><br>
                                                    @if($solicitud->estatus == "Archivada")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFfalltaInteres', $solicitud->id) }}"        target="_blank">Acta de Archivo</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($solicitud->estatus == "Incompetencia")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFincompetencia', $solicitud->id) }}"        target="_blank">Incompetencia</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($solicitud->estatus == "Comparecencia")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFinteres', $solicitud->id) }}"              target="_blank">Acta de incomparecencia</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($solicitud->estatus == "Reagendada")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"      target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFnotificacion_solicitante', $solicitud->id) }}" target="_blank">Notificación al solicitante</a></li>
                                                                    <li><button type="button" class="btn btn-info open-modal" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div>                                                        
                                                    @elseif($solicitud->estatus == "No conciliacion")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFno_conciliacion', $solicitud->id) }}"      target="_blank">Constancia de no conciliación</a></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($solicitud->estatus == "Incumplimiento")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFincumplimientoAudiencia', $solicitud->id) }}"      target="_blank">Constancia de Incumplimiento</a></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($solicitud->estatus == "Conciliacion")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('VerPDFAudiencia', $solicitud->id) }}"  target="_blank">Acta de Audiencia</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFconveniosolicitud', $solicitud->id) }}" target="_blank">Convenio</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFcumplimiento', $solicitud->id) }}"  target="_blank">Constancia de cumplimiento</a></li>
                                                                    <li><button type="button" class="btn open-modal" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @endif 
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
    </section>
@endsection

<div id="nuevo_usuario" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
<div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <form  class='needs-validation novalidate' method='POST' action="{{ route('subir_expediente') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="audiencia_id" id="expediente_audiencia_id">
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
                                El doceumento es obligatorio.
                            </div>
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


@section('scripts')
    <script src="../../public/assets/js/usuarios/usuarios.js"></script>
    <script>
        $(document).ready(function() {
            $('.open-expediente-modal').click(function () {
                const id = $(this).data('id');
                
                $('#expediente_audiencia_id').val(id);
            });
        });
    </script>
@endsection