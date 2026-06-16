@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Audiencias</h3>
        </div>
        <div class="section-body">
            <!-- Muestra los mensajes de éxito y/o error según sea el caso, al subir el expediente -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            <!-- Fin de alertas -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group">
                                            <input type="text" name="buscar" class="form-control" placeholder="Escribe el NUE o nombre del solicitante..." value="{{ request('buscar') }}">
                                            <button class="btn btn-primary" type="submit" style="background-color: #4A001F; border-color: #4A001F;">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                            @if(request('buscar'))
                                                <a href="{{ url()->current() }}" class="btn btn-secondary">Limpiar Filtro</a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Núm. expediente</th>
                                            <th style="color: #fff;">Fecha y Hora</th>
                                            <th style="color: #fff;">Solicitante</th>
                                            <th style="color: #fff;">Conciliador</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Detalles</th>
                                            <th style="color: #fff;">Acciones</th>
                                            <th style="color: #fff;">Cumplimientos</th>
                                            <th style="color: #fff;">Documentos</th>
                                        </thead>
                                        <tbody>
                                            @foreach($audiencias as $audiencia)
                                            <tr>
                                                <td>{{$audiencia->NUE}}</td>
                                                <td>{{ \Carbon\Carbon::parse($audiencia->fecha)->format('d-m-y') }} {{ \Carbon\Carbon::parse($audiencia->hora)->format('H:i') }} hrs.</td>
                                                <td>{{$audiencia->nombre}}</td>
                                                <td>{{$audiencia->conciliador_nombre}}</td>
                                                <td>{{$audiencia->estatus_modelo}}</td>
                                                <td><a class="btn btn-info" href="{{ route('solicitud_audiencia', $audiencia->id_solicitud) }}?isAudiencia=Si&audiencia_id={{ $audiencia->id }}" onclick=editar_usuario();>Revisar</a></td>
                                                <td>
                                                    @if($audiencia->estatus_modelo == "Pendiente")
                                                        <a class="btn btn-success" href="{{ route('inicioAudiencia', $audiencia->id_solicitud) }}?audiencia_id={{ $audiencia->id }}">Iniciar</a><br>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($audiencia->estatus_modelo == "Conciliacion" || $audiencia->estatus_modelo == "Concluida Pagos")
                                                        <a class="btn btn-primary" href="{{ route('audiencia_cumplimientos', $audiencia->id_solicitud) }}">Cumplimiento</a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning open-expediente-modal" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $audiencia->id_solicitud }}">Subir Documento</button>
                                                    
                                                    @if($audiencia->estatus_modelo == "Archivada en Audiencia")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFfalltaInteres', $audiencia->id_solicitud) }}"        target="_blank">Acta de Archivo</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFAudiencia', $audiencia->id_solicitud) . '?audiencia_id=' . $audiencia->id }}"  target="_blank">Acta de Audiencia</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($audiencia->estatus_modelo == "Archivada")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFfalltaInteres', $audiencia->id_solicitud) }}"        target="_blank">Acta de Archivo</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($audiencia->estatus_modelo == "Incompetencia")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFincompetencia', $audiencia->id_solicitud) }}"        target="_blank">Incompetencia</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($audiencia->estatus_modelo == "Comparecencia")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFinteres', $audiencia->id_solicitud) }}"              target="_blank">Acta de incomparecencia</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($audiencia->estatus_modelo == "Reagendada" || $audiencia->estatus_modelo == "No conciliacion reagendada")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"      target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFnotificacion_solicitante', $audiencia->id_solicitud) }}" target="_blank">Notificación al solicitante</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div>                                                        
                                                    @elseif($audiencia->estatus_modelo == "No conciliacion")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFno_conciliacion', $audiencia->id_solicitud) }}" target="_blank">Constancias de no conciliación (En un solo archivo)</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#noConciliacion" data-id="{{ $audiencia->id_solicitud }}">Constancias de no conciliación</button></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($audiencia->estatus_modelo == "Incumplimiento")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFincumplimientoAudiencia', $audiencia->id_solicitud) }}"      target="_blank">Constancia de Incumplimiento</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($audiencia->estatus_modelo == "Conciliacion" || $audiencia->estatus_modelo == "Concluida" || $audiencia->estatus_modelo == "Reinstalacion")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('VerPDFAudiencia', $audiencia->id_solicitud) . '?audiencia_id=' . $audiencia->id }}"  target="_blank">Acta de Audiencia</a></li>
                                                                    <li>
                                                                        <a class="dropdown-item" 
                                                                           href="{{ ($audiencia->estatus_modelo == 'Reinstalacion' ? route('PDFconvenioreinstalacion', $audiencia->id_solicitud) : route('PDFconveniosolicitud', $audiencia->id_solicitud)) . '?audiencia_id=' . $audiencia->id }}" 
                                                                           target="_blank">
                                                                            Convenio
                                                                        </a>
                                                                    </li>
                                                                    @if($audiencia->constancia == 0)
                                                                        <li><a class="dropdown-item" href="{{ route('PDFcumplimientoTotal', $audiencia->id_solicitud) }}"  target="_blank">Constancia de cumplimiento</a></li>
                                                                    @endif
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($audiencia->estatus_modelo == "Confirmado")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($audiencia->estatus_modelo == "Desistimiento") 
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="dropdown-item" href="{{ route('VerDocumentosAudiencia', $audiencia->id_solicitud) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFfalltaInteres', $audiencia->id_solicitud) }}"        target="_blank">Acta de Desistimiento</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $audiencia->id_solicitud }}">Citatorios</button></li>
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
                            <div class="pagination justify-content-end"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

    <!-- Modal Documentos -->
    <div class="modal fade" id="documentos" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="modalLabel">Citatorios</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped" style="width: 100%; text-align: center;">
                    <thead style="background-color: #D2D3D5;">
                    <tr>
                        <th>Citatorios</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody id="listaRegistros"></tbody>
                </table>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
        </div>
    </div>

    <!-- Modal Expediente -->
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
                        <div  class="col-xs-12 col-sm-12 col-md-12">
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
                </div>
            </div>
        </form>
    </div>
    <!-- Modal Constancias de no conciliación -->
    <div class="modal fade" id="noConciliacion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title">Constancias de No Conciliación</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped" style="width: 100%; text-align: center;">
                    <thead style="background-color: #D2D3D5;">
                    <tr>
                        <th>Citado</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody id="listaNoConciliacion"></tbody> 
                </table>
            </div>
        </div>
        </div>
    </div>
    <div id="nuevo_poder" style ="display: none;">
        <div>.</div>
        <div class="loader"></div>
    </div>

@section('scripts')
    <script src="../public/assets/js/poderes/general.js"></script>
    <script>
        const pdfsUrlBase  = "{{ url('solicitud/pdfs') }}";
    </script>
    <script>
        $(document).ready(function() {
            //$('#btnMostrarRegistros').on('click', function() {
            $(document).on('click', '.btn-mostrar-registros', function() {
                const listaRegistros = $('#listaRegistros');
                const pdfsUrlBase = "{{ url('ObtenerCitatorios') }}";
                const id = $(this).data('id');
                const pdfRouteBase = '{{ route("pdfCitatorioAudiencia", ["id" => "xxx"]) }}';

                listaRegistros.empty(); // Limpiar lista
                $.ajax({
                    url: `${pdfsUrlBase}/${id}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            // Iteramos sobre los datos recibidos (ya parseados por jQuery)
                            $.each(data, function(index, registro) {
                            const pdfUrl = pdfRouteBase.replace('xxx', registro.id);
                                const listItem = `
                                <tr>
                                    <td style="text-align: left;"> <strong>${registro.nombre} ${registro.primer_apellido} ${registro.segundo_apellido}</strong> </td>
                                    <td>
                                        <a href="${pdfUrl}" target="_blank" rel="noopener noreferrer">PDF</a>
                                    </td>
                                </tr>`;
                                listaRegistros.append(listItem);
                            });
                        } else {
                            listaRegistros.append('<li class="list-group-item">No se encontraron registros.</li>');
                        }
                        
                        // Mostrar el modal
                        var myModal = new bootstrap.Modal(document.getElementById('modalListado'));
                        myModal.show();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al obtener los datos:", error);
                        listaRegistros.append('<li class="list-group-item text-danger">Error de conexión con el servidor.</li>');
                        
                        var myModal = new bootstrap.Modal(document.getElementById('modalListado'));
                        myModal.show();
                    }
                });
             });

            $(document).on('click', '.open-expediente-modal', function() {
                // 2. Capturar el 'data-id'
                var idRegistro = $(this).data('id');            
                document.getElementById('expediente_audiencia_id').value = idRegistro;
            });
            // Limpiar backdrop y modal-open cuando modal se oculta
            $('#documentos').on('hidden.bs.modal', function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            });
        });
    </script>
    <script>//Constancias de no conciliación individuales en modal
        $(document).on('click', '[data-bs-target="#noConciliacion"]', function() {
            const idSolicitud = $(this).data('id');
            const tabla = $('#listaNoConciliacion');
            const urlData = "{{ url('ObtenerConstancias') }}/" + idSolicitud;
            
            // Ruta base para el PDF individual
            const routePdfBase = "{{ route('PDFnoConciliacionIndividual', ['id' => 'XXX']) }}";

            tabla.empty().append('<tr><td colspan="2">Cargando...</td></tr>');

            $.ajax({
                url: urlData,
                type: 'GET',
                success: function(data) {
                    tabla.empty();
                    if (data.length > 0) {
                        $.each(data, function(index, registro) {
                            // Reemplazamos el placeholder XXX por el ID del citado
                            const finalPdfUrl = routePdfBase.replace('XXX', registro.id);
                            
                            const row = `
                                <tr>
                                    <td style="text-align: left;">
                                        <strong>${registro.nombre} ${registro.primer_apellido} ${registro.segundo_apellido || ''}</strong>
                                    </td>
                                    <td>
                                        <a href="${finalPdfUrl}" class="btn btn-danger btn-sm" target="_blank">
                                           Ver PDF
                                        </a>
                                    </td>
                                </tr>`;
                            tabla.append(row);
                        });
                    } else {
                        tabla.append('<tr><td colspan="2">No hay registros disponibles.</td></tr>');
                    }
                },
                error: function() {
                    tabla.empty().append('<tr><td colspan="2" class="text-danger">Error al cargar datos.</td></tr>');
                }
            });
        });
    </script>
@endsection


