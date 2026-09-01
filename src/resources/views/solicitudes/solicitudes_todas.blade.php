@extends('layouts.app')
@section('title', 'Resultado de la Búsqueda')

<style>
    /* Evita que los dropdowns se corten dentro de la tabla en Bootstrap 5 */
    .table-responsive {
        overflow: visible !important;
    }
</style>

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Resultado de la Búsqueda</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <!-- Formulario de Búsqueda -->
                            <div class="row mb-4">
                                <div class="col-md-5">
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group">
                                            <input type="text" name="buscar" class="form-control" placeholder="Buscar solicitud por NUE o Solicitante..." value="{{ request('buscar') }}">
                                            <button class="btn btn-primary" type="submit" style="background-color: #354647; border-color: #354647;">
                                                <i class="fas fa-search me-1"></i> Buscar
                                            </button>
                                            @if(request('buscar'))
                                                <a href="{{ url()->current() }}" class="btn btn-secondary">Limpiar Filtro</a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Tabla Principal de Resultados -->
                            <div class="table-responsive menu-visible">
                                <table id="example" class="table table-striped table-hover align-middle w-100">
                                    <thead style="background-color: #354647;">
                                        <tr>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Folio</th>
                                            <th class="text-white" style="color: #ffffff !important;">Fecha Captura</th>
                                            <th class="text-white" style="color: #ffffff !important;">Expediente</th>
                                            <th class="text-white" style="color: #ffffff !important;">Solicitante</th>
                                            <th class="text-white" style="color: #ffffff !important;">Teléfono</th>
                                            <th class="text-white" style="color: #ffffff !important;">Citados</th>
                                            <th class="text-white" style="color: #ffffff !important;">Actividad Económica</th>
                                            <th class="text-white" style="color: #ffffff !important;">Tipo Solicitante</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Estatus</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Revisar</th>
                                            <th class="text-center text-white" style="width: 14%; color: #ffffff !important;">Documentos</th>
                                        </tr>
                                    </thead>
                                    <tbody class="contenidobusqueda">
                                        @foreach($solicitudes as $solicitud)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $solicitud->consecutivo }}</td>
                                                <td>{{ \Carbon\Carbon::parse($solicitud->fecha)->format('d-m-Y') }}</td>
                                                <td class="fw-bold text-primary">{{ $solicitud->NUE }}</td>
                                                <td>{{ $solicitud->nombre }}</td>
                                                <td>{{ $solicitud->telefono }}</td>
                                                <td>{{ $solicitud->lista_citados }}</td>
                                                <td>{{ $solicitud->actividad }}</td>
                                                <td>
                                                    @if($solicitud->tipo_solicitud == 1) Trabajador
                                                    @elseif($solicitud->tipo_solicitud == 2) Patronal
                                                    @elseif($solicitud->tipo_solicitud == 3) Patronal Colectiva
                                                    @elseif($solicitud->tipo_solicitud == 4) Sindical
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">{{ $solicitud->estatus }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="btn btn-info btn-sm text-white open-audiencias-modal"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalAudiencias"
                                                        data-id="{{ $solicitud->id }}">
                                                        <i class="bi bi-eye me-1"></i> Revisar
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column gap-1 align-items-center">
                                                        <button type="button" class="btn btn-warning btn-sm text-dark fw-semibold open-expediente-modal w-100" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $solicitud->id }}" style="background-color: #CEA845; border-color: #CEA845; color: #000000 !important;">
                                                            <i class="bi bi-upload me-1"></i> Subir Documento
                                                        </button>

                                                        @if(in_array($solicitud->estatus, ['Archivada', 'Incompetencia', 'Comparecencia', 'Reagendada', 'No conciliacion', 'Incumplimiento', 'Conciliacion', 'Concluida', 'Reinstalacion', 'Confirmado', 'Desistimiento', 'Prevencion']))
                                                            <div class="dropdown w-100">
                                                                <button class="btn btn-secondary btn-sm text-dark fw-semibold dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: #e2e6ea; border-color: #d3d9df; color: #000000 !important;">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                                    <li>
                                                                        <button type="button" class="dropdown-item btn-cargar-lista-docs" data-id="{{ $solicitud->id }}" data-doc-url="{{ signedDocRoute('VerDocumentosAudiencia', ['id' => $solicitud->id]) }}">
                                                                            Documentos Digitales
                                                                        </button>
                                                                    </li>

                                                                    @if($solicitud->estatus == "Archivada")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFfalltaInteres', $solicitud->id) }}" target="_blank">Acta de Archivo</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'seguimiento', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Seguimiento</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'caratula', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Solicitud</a></li>
                                                                    @elseif($solicitud->estatus == "Incompetencia")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFincompetencia', $solicitud->id) }}" target="_blank">Incompetencia</a></li>
                                                                    @elseif($solicitud->estatus == "Comparecencia")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFinteres', $solicitud->id) }}" target="_blank">Acta de incomparecencia</a></li>
                                                                    @elseif($solicitud->estatus == "Reagendada" || $solicitud->estatus == "Confirmado" || $solicitud->estatus == "Desistimiento")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFnotificacion_solicitante', $solicitud->id) }}" target="_blank">Notificación al solicitante</a></li>
                                                                        @if(in_array($solicitud->estatus, ['Confirmado', 'Desistimiento']))
                                                                            <li><a class="dropdown-item" href="{{ route('PDFacuseConfirmada', $solicitud->id) }}" target="_blank">Acuse de solicitud confirmada</a></li>
                                                                            <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfo', ['tipo' => 'solicitud', 'id' => $solicitud->id]) }}" target="_blank">Formato de Solicitud</a></li>
                                                                            <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'seguimiento', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Seguimiento</a></li>
                                                                            <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'caratula', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Solicitud</a></li>
                                                                        @endif
                                                                    @elseif($solicitud->estatus == "No conciliacion")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFno_conciliacion', $solicitud->id) }}" target="_blank">Constancias de no conciliación</a></li>
                                                                        <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#noConciliacion" data-id="{{ $solicitud->id }}">Constancia de no conciliación</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'seguimiento', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Seguimiento</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'caratula', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Solicitud</a></li>
                                                                    @elseif(in_array($solicitud->estatus, ['Conciliacion', 'Concluida', 'Reinstalacion']))
                                                                        @if(isset($solicitud->mostrar_ptu) && $solicitud->mostrar_ptu)
                                                                            <li><a class="dropdown-item bg-success text-white fw-bold" href="{{ route('PDFconvenioPTU_NO_S', $solicitud->id) }}" target="_blank">Convenio PTU (No Labora)</a></li>
                                                                        @else
                                                                            <li>
                                                                                <a class="dropdown-item btn-convenio-audiencia" 
                                                                                   data-id="{{ $solicitud->id }}"
                                                                                   data-base="{{ $solicitud->estatus == 'Reinstalacion' ? route('PDFconvenioreinstalacion', $solicitud->id) : route('PDFconveniosolicitud', $solicitud->id) }}"
                                                                                   href="{{ $solicitud->estatus == 'Reinstalacion' ? route('PDFconvenioreinstalacion', $solicitud->id) : route('PDFconveniosolicitud', $solicitud->id) }}"
                                                                                   target="_blank">Convenio</a>
                                                                            </li>
                                                                            <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'seguimiento', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Seguimiento</a></li>
                                                                            <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilio', ['tipo' => 'caratula', 'id' => $solicitud->id]) }}" target="_blank">Carátula de Solicitud</a></li>
                                                                        @endif
                                                                        <li>
                                                                            <a class="dropdown-item btn-constancia-audiencia" 
                                                                               data-id="{{ $solicitud->id }}"
                                                                               data-base="{{ route('PDFcumplimientoTotal', $solicitud->id) }}"
                                                                               href="{{ route('PDFcumplimientoTotal', $solicitud->id) }}"
                                                                               target="_blank">Constancia de cumplimiento</a>
                                                                        </li>
                                                                    @elseif($solicitud->estatus == "Prevencion")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFacuse_solicitud', $solicitud->id) }}" target="_blank">Acuse de solicitud</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfo', ['tipo' => 'solicitud', 'id' => $solicitud->id]) }}" target="_blank">Formato de Solicitud</a></li>
                                                                    @endif

                                                                    <li><button type="button" class="dropdown-item btn-mostrar-registros" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        @endif
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

    <!-- Modales -->
    <div id="nuevo_usuario" style="display: none;">
        <div class="loader"></div>
    </div>

    <!-- Modal Audiencias -->
    <div class="modal fade" id="modalAudiencias" tabindex="-1" aria-labelledby="modalAudienciasLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAudienciasLabel">Audiencias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle w-100 text-center">
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

    <!-- Modal Documentos / Citatorios -->
    <div class="modal fade" id="documentos" tabindex="-1" aria-labelledby="modalLabelCitatorios" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelCitatorios">Citatorios</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle w-100 text-center">
                            <thead style="background-color: #D2D3D5;">
                                <tr>
                                    <th>Citatorios</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="listaRegistros"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Expediente -->
    <div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="modalLabelExpediente" aria-hidden="true">
        <form class="needs-validation" novalidate method="POST" action="{{ route('subir_expediente') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="audiencia_id" id="expediente_audiencia_id">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabelExpediente">Subir expediente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label class="form-label">Documento en PDF</label>
                                <input type="file" name="documentoExpediente" class="form-control" accept=".pdf" required>
                                <div class="invalid-feedback">El documento es obligatorio.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Nombre de expediente <span class="text-danger">(*)</span></label>
                                <input type="text" name="nombreExpediente" class="form-control" required> 
                                <div class="invalid-feedback">El nombre para el expediente es obligatorio.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845; color:#ffffff !important;">Agregar</button> 
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Listado de Documentos Digitales -->
    <div class="modal fade" id="modalListaDocs" tabindex="-1" aria-labelledby="modalListaDocsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background-color: #354647;">
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalListaDocsLabel">
                        <i class="bi bi-folder2-open me-2"></i>Documentos Digitales
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive rounded border">
                        <table class="table table-striped table-hover align-middle mb-0 w-100">
                            <thead style="background-color: #354647;">
                                <tr>
                                    <th class="text-white py-3 ps-3" style="width: 45%; color: #ffffff !important;">Nombre del Documento</th>
                                    <th class="text-white py-3" style="width: 35%; color: #ffffff !important;">Archivo</th>
                                    <th class="text-center text-white py-3 pe-3" style="width: 20%; color: #ffffff !important;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyListaDocs">
                                <!-- Inyección vía JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<!-- Modal Consatncias de no conciliacion separadas -->
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
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
    <script>
        $(document).ready(function () {
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

            // Modal Citatorios vía AJAX (Bootstrap 5.3 API)
            $(document).on('click', '.btn-mostrar-registros', function() {
                const listaRegistros = $('#listaRegistros');
                const pdfsUrlBase = "{{ url('ObtenerCitatorios') }}";
                const id = $(this).data('id');
                const pdfRouteBase = '{{ route("PDFSolicitud", ["id" => "xxx"]) }}';

                listaRegistros.empty();

                $.ajax({
                    url: `${pdfsUrlBase}/${id}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            $.each(data, function(index, registro) {
                                const pdfUrl = pdfRouteBase.replace('xxx', registro.id);
                                const listItem = `
                                <tr>
                                    <td class="text-start"><strong>${registro.nombre} ${registro.primer_apellido} ${registro.segundo_apellido}</strong></td>
                                    <td>
                                        <a href="${pdfUrl}" target="_blank" class="btn btn-primary btn-sm">Ver PDF</a>
                                    </td>
                                </tr>`;
                                listaRegistros.append(listItem);
                            });
                        } else {
                            listaRegistros.append('<tr><td colspan="2" class="text-muted">No se encontraron registros.</td></tr>');
                        }

                        const modalElement = document.getElementById('documentos');
                        const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modalInstance.show();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al obtener los datos:", error);
                        listaRegistros.append('<tr><td colspan="2" class="text-danger">Error de conexión con el servidor.</td></tr>');

                        const modalElement = document.getElementById('documentos');
                        const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modalInstance.show();
                    }
                });
            });

            $(document).on('click', '.open-expediente-modal', function() {
                var idRegistro = $(this).data('id');            
                document.getElementById('expediente_audiencia_id').value = idRegistro;
            });

            // Modal Audiencias para Revisar
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
                                            <a href="${revisarUrl}" class="btn btn-primary btn-sm">Revisar</a>
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

            $(document).on('click', '.btn-cargar-lista-docs', function() {
                const solicitudId = $(this).data('id');
                const tbody = $('#tbodyListaDocs');
                const modalElement = document.getElementById('modalListaDocs');
                const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

                const finalUrl = $(this).data('docUrl');

                tbody.html(`
                    <tr>
                        <td colspan="3" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2 text-muted mb-0">Obteniendo listado de documentos...</p>
                        </td>
                    </tr>
                `);

                modalInstance.show();

                $.ajax({
                    url: finalUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        tbody.empty();
                        if (Array.isArray(data) && data.length > 0) {
                            $.each(data, function(index, item) {
                                const botonAccion = item.url
                                    ? `<a href="${item.url}" target="_blank" class="btn btn-primary btn-sm px-3 shadow-sm"><i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF</a>`
                                    : `<span class="badge bg-secondary rounded-pill px-3 py-2 fw-normal">No disponible</span>`;

                                const row = `
                                    <tr>
                                        <td class="fw-semibold ps-3 py-3">${item.nombre}</td>
                                        <td class="text-muted small py-3" style="max-width: 200px;">
                                            <div class="text-truncate" title="${item.archivo || ''}">
                                                ${item.archivo || 'N/A'}
                                            </div>
                                        </td>
                                        <td class="text-center pe-3 py-3">${botonAccion}</td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                        } else {
                            tbody.append(`
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No hay documentos registrados para esta solicitud.</td>
                                </tr>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al obtener documentos:', error);
                        tbody.html(`
                            <tr>
                                <td colspan="3" class="text-center text-danger py-4">Ocurrió un error al intentar consultar los documentos (${xhr.status}).</td>
                            </tr>
                        `);
                    }
                });
            });

            // Limpiar el iframe al cerrar el modal para no consumir memoria
            $('#modalDocumentosIframe').on('hidden.bs.modal', function () {
                $('#iframeDocumentos').attr('src', '');
            });
        });
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