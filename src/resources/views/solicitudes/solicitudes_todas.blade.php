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
                            <div class="row mb-3">
                                <div class="col-md-5">
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group">
                                            <input type="text" name="buscar" class="form-control" placeholder="Buscar solicitud por NUE o Solicitante..." value="{{ request('buscar') }}">
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
                                        <th style="color: #fff;">Folio</th>
                                        <th style="color: #fff;">Fecha Captura</th>
                                        <th style="color: #fff;">Expediente</th>
                                        <th style="color: #fff;">Solicitante</th>
                                        <th style="color: #fff;">Citados</th>
                                        <th style="color: #fff;">Actividad Económica</th>
                                        <th style="color: #fff;">Tipo Solicitante</th>
                                        <th style="color: #fff;">Estatus</th>
                                        <th style="color: #fff;">Revisar</th>
                                        <th style="color: #fff;">Documentos</th>
                                        {{--@if($userRole == "Enlace" || $userRole == "Super Usuario" || $userRole == "Conciliador")
                                            <th style="color: #fff;">Editar</th>
                                        @endif--}}
                                    </thead>
                                    <tbody class="contenidobusqueda">
                                        @foreach($solicitudes as $solicitud)
                                            <tr>
                                                <td>{{$solicitud->consecutivo}}</td>
                                                <td>{{$solicitud->fecha}}</td>
                                                <td>{{$solicitud->NUE}}</td>
                                                <td>{{$solicitud->nombre}}</td>
                                                <td>{{$solicitud->lista_citados}}</td>
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
                                                    <button type="button"
                                                        class="btn btn-info open-audiencias-modal"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalAudiencias"
                                                        data-id="{{ $solicitud->id }}">
                                                        Revisar
                                                    </button>
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
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
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
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
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
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
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
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
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
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
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
                                                                    <!--li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFincumplimientoAudiencia', $solicitud->id) }}"      target="_blank">Constancia de Incumplimiento</a></li-->                                                                    
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($solicitud->estatus == "Conciliacion" || $solicitud->estatus == "Concluida" || $solicitud->estatus == "Reinstalacion")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <!--li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFAudiencia', $solicitud->id) }}"  target="_blank">Acta de Audiencia</a></li-->
                                                                     @if(isset($solicitud->mostrar_ptu) && $solicitud->mostrar_ptu)
                                                                        <li><a class="dropdown-item" href="{{ route('PDFconvenioPTU_NO_S', $solicitud->id) }}" target="_blank" style="background-color: #d4edda; font-weight: bold;">Convenio PTU (No Labora)</a></li>
                                                                    @else
                                                                    <li>
                                                                        <a class="btn btn-info btn-convenio-audiencia" style="width: 100%"
                                                                           data-id="{{ $solicitud->id }}"
                                                                           data-base="{{ $solicitud->estatus == 'Reinstalacion' ? route('PDFconvenioreinstalacion', $solicitud->id) : route('PDFconveniosolicitud', $solicitud->id) }}"
                                                                           href="{{ $solicitud->estatus == 'Reinstalacion' ? route('PDFconvenioreinstalacion', $solicitud->id) : route('PDFconveniosolicitud', $solicitud->id) }}"
                                                                           target="_blank">Convenio</a>
                                                                    </li>
                                                                    @endif
                                                                    {{--<li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFconveniosolicitud', $solicitud->id) }}" target="_blank">Convenio</a></li>--}}
                                                                    <li>
                                                                        <a class="btn btn-info btn-constancia-audiencia" style="width: 100%"
                                                                           data-id="{{ $solicitud->id }}"
                                                                           data-base="{{ route('PDFcumplimientoTotal', $solicitud->id) }}"
                                                                           href="{{ route('PDFcumplimientoTotal', $solicitud->id) }}"
                                                                           target="_blank">Constancia de cumplimiento</a>
                                                                    </li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($solicitud->estatus == "Confirmado")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%"  href="{{ route('PDFnotificacion_solicitante', $solicitud->id) }}" target="_blank">Notificación al solicitante</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%"  href="{{ route('PDFacuseConfirmada', $solicitud->id) }}"  target="_blank">Acuse de solicitud confirmada</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFCaratulaInfo', ['tipo' => 'solicitud', 'id' => $solicitud->id]) }}"  target="_blank">Imprimir solicitud</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                        @elseif($solicitud->estatus == "Desistimiento")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%"  href="{{ route('PDFnotificacion_solicitante', $solicitud->id) }}" target="_blank">Notificación al solicitante</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%"  href="{{ route('PDFacuseConfirmada', $solicitud->id) }}"  target="_blank">Acuse de solicitud confirmada</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFCaratulaInfo', ['tipo' => 'solicitud', 'id' => $solicitud->id]) }}"  target="_blank">Imprimir solicitud</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($solicitud->estatus == "Prevencion")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"  target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFacuse_solicitud', $solicitud->id) }}"  target="_blank">Acuse de solicitud</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFCaratulaInfo', ['tipo' => 'solicitud', 'id' => $solicitud->id]) }}"  target="_blank">Imprimir solicitud</a></li>
                                                                    <li><button type="button" class="btn btn-info btn-mostrar-registros" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @endif

                                                </td>
                                                {{--@if($userRole == "Enlace" || $userRole == "Super Usuario" || $userRole == "Conciliador")
                                                    <td>
                                                        <a class="btn btn-success" href="{{ route('solicitud_audiencia', $solicitud->id) }}">Editar</a>
                                                    </td>
                                                @endif--}}
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

<div class="modal fade" id="modalAudiencias" tabindex="-1" aria-labelledby="modalAudienciasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAudienciasLabel">Audiencias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped" style="width: 100%; text-align: center;">
                        <thead style="background-color: #D2D3D5;">
                            <tr>
                                <th>ID</th>
                                <th>Estatus</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="listaAudienciasSolicitud"></tbody>
                    </table>
                </div>
                <div id="audienciasSolicitudEmpty" class="text-center text-muted" style="display:none;">
                    No se encontraron audiencias para esta solicitud.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

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



@section('scripts')
    <script src="../public/assets/js/poderes/general.js"></script>
    <script>
        $(document).ready(function () {
            // 1. Evitar el error "Cannot reinitialise DataTable" destruyendo instancias previas automáticas
            if ($.fn.DataTable.isDataTable('#example')) {
                $('#example').DataTable().destroy();
            }

            // 2. Inicialización Unificada Inteligente
            $('#example').DataTable({
                "destroy": true,
                "paging": true,        // Segmenta de 10 en 10 localmente las filas cargadas en pantalla
                "pageLength": 10,
                "searching": true,     // Activa el input rápido de DataTables (esquina superior derecha)
                "ordering": true,      // Permite ordenar columnas al hacer clic
                "info": true,          // Muestra el texto informativo de filas
                "language": {
                    "search": "Filtrar en esta pantalla:",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ solicitudes",
                    "infoEmpty": "Mostrando 0 a 0 de 0 filas",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "zeroRecords": "No se encontraron coincidencias en esta página. Use el buscador de arriba."
                }
            });
            $(document).on('click', '.btn-mostrar-registros', function() {
            //$('#btnMostrarRegistros').on('click', function() {
                const listaRegistros = $('#listaRegistros');
                const pdfsUrlBase = "{{ url('ObtenerCitatorios') }}";
                const id = $(this).data('id');
                console.log(id);
                const pdfRouteBase = '{{ route("PDFSolicitud", ["id" => "xxx"]) }}';

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
                                       <a href="${pdfUrl}" target="_blank" class="btn btn-primary"> Ver PDF </a>
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
        });

        $(document).on('click', '.open-expediente-modal', function() {
            var idRegistro = $(this).data('id');            
            document.getElementById('expediente_audiencia_id').value = idRegistro;
        });

        //Modal de audiencias para Revisar
        $(document).on('click', '.open-audiencias-modal', function() {
            const solicitudId = $(this).data('id');
            const lista = $('#listaAudienciasSolicitud');
            const empty = $('#audienciasSolicitudEmpty');
            lista.empty();
            empty.hide();

            const endpoint = `{{ url('/api/audiencias-por-solicitud') }}/${solicitudId}`;
            const revisarBase = `{{ route('solicitud_audiencia', 0) }}?isAudiencia=No&audiencia_id=AUDIENCIA_ID`;

            $.ajax({
                url: endpoint,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function(a) {
                            const revisarUrl = revisarBase
                                .replace('/0?', `/${solicitudId}?`)
                                .replace('AUDIENCIA_ID', a.id);

                            const fecha = a.fecha || '';
                            const hora = a.hora ? (a.hora.substring(0,5) + ' HRS') : '';
                            let estatus = a.estatus || '';
                            if (estatus === 'No conciliacion reagendada') {
                                estatus = 'Reagendada (Solicitud de nueva fecha)';
                            } else if (estatus === 'Reagendada') {
                                estatus = 'Reagendada (A notificar por el CCL)';
                            }

                            lista.append(`
                                <tr>
                                    <td>${a.id}</td>
                                    <td>${estatus}</td>
                                    <td>${fecha}</td>
                                    <td>${hora}</td>
                                    <td>
                                        <a href="${revisarUrl}" class="btn btn-primary">Revisar</a>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        empty.text('No se encontraron audiencias para esta solicitud.').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al obtener audiencias:', error);
                    empty.text('Error de conexión con el servidor.').show();
                }
            });
        });

        async function getUltimaAudienciaIdPorSolicitud(solicitudId) {
            try {
                const endpoint = `{{ url('/api/audiencias-por-solicitud') }}/${solicitudId}`;
                const resp = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
                if (!resp.ok) return null;
                const data = await resp.json();
                if (!Array.isArray(data) || data.length === 0) return null;
                const last = data[data.length - 1];
                return last?.id ?? null;
            } catch (e) {
                console.error('Error al consultar última audiencia:', e);
                return null;
            }
        }

        function buildUrlWithAudienciaId(baseUrl, audienciaId) {
            if (!audienciaId) return baseUrl;
            const sep = baseUrl.includes('?') ? '&' : '?';
            return `${baseUrl}${sep}audiencia_id=${audienciaId}`;
        }

        $(document).on('click', '.btn-convenio-audiencia, .btn-constancia-audiencia', async function(e) {
            e.preventDefault();
            const $a = $(this);
            const solicitudId = $a.data('id');
            const base = $a.data('base') || $a.attr('href');

            const audienciaId = await getUltimaAudienciaIdPorSolicitud(solicitudId);
            const finalUrl = buildUrlWithAudienciaId(base, audienciaId);
            const target = $a.attr('target') || '_self';

            window.open(finalUrl, target);
        });
    </script>
@endsection