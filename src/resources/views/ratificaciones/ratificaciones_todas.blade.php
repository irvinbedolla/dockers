@extends('layouts.app')

@php
    $fechaActual = date('Y-m-d');
@endphp

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Ratificaciones</h3>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <!-- Formulario de Búsqueda Superior -->
                            <div class="row mb-4">
                                <div class="col-md-5">
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group">
                                            <input type="text" name="buscar" class="form-control" placeholder="Buscar ratificación por NUE o Empresa..." value="{{ request('buscar') }}">
                                            <button class="btn btn-primary" type="submit" style="background-color: #4A001F; border-color: #4A001F;">
                                                <i class="fas fa-search me-1"></i> Buscar
                                            </button>
                                            @if(request('buscar'))
                                                <a href="{{ url()->current() }}" class="btn btn-secondary">Limpiar</a>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Tabla de Ratificaciones -->
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-hover align-middle w-100"> 
                                    <thead style="background-color: #4A001F;">
                                        <tr>
                                            <th class="text-center text-white" style="color: #ffffff !important;">N° Interno</th> 
                                            <th class="text-white" style="color: #ffffff !important;">NUE</th> 
                                            <th class="text-white" style="color: #ffffff !important;">Fecha</th>
                                            <th class="text-white" style="color: #ffffff !important;">Empresa</th>
                                            <th class="text-white" style="color: #ffffff !important;">Teléfono</th>
                                            <th class="text-white" style="color: #ffffff !important;">Correo</th>
                                            <th class="text-white" style="color: #ffffff !important;">Trabajador</th>
                                            <th class="text-white" style="color: #ffffff !important;">Delegación</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Estatus</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Detalles</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Concluir</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Cumplimientos</th>
                                            <th class="text-center text-white" style="width: 14%; color: #ffffff !important;">Documentos</th>
                                            @if($userRole == "Enlace" || $userRole == "Super Usuario" || $userRole == "Auxiliar")
                                                <th class="text-center text-white" style="color: #ffffff !important;">Editar</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="contenidobusqueda">
                                        @foreach($solicitudes as $solicitud)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $solicitud->consecutivo }}</td>
                                                <td class="fw-bold text-primary">{{ $solicitud->NUE }}</td>
                                                <td>{{ \Carbon\Carbon::parse($solicitud->fecha)->format('d-m-Y') }}</td> 
                                                <td>
                                                    @if(is_null($solicitud->nombre_empresa) && is_null($solicitud->primero_empresa) && is_null($solicitud->segundo_empresa))
                                                        {{ $solicitud->empresa }}
                                                    @else 
                                                        {{ $solicitud->nombre_empresa }} {{ $solicitud->primero_empresa }} {{ $solicitud->segundo_empresa }}
                                                    @endif
                                                </td>
                                                <td>{{ $solicitud->telefono }}</td>
                                                <td>{{ $solicitud->email }}</td>
                                                <td>{{ $solicitud->trabajador }} {{ $solicitud->primero_trabajador }} {{ $solicitud->segundo_trabajador }}</td>
                                                <td>{{ $solicitud->delegacion }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">{{ $solicitud->estatus }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <a class="btn btn-primary btn-sm" href="{{ route('consultar_ratificacion', $solicitud->id) }}">
                                                        <i class="bi bi-search me-1"></i> Consultar
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    @if($solicitud->estatus == "Confirmado")
                                                        <a class="btn btn-info btn-sm text-white" href="{{ route('ratificacion_concluir', $solicitud->id) }}">Concluir</a>
                                                    @elseif($solicitud->estatus == "Concluida Pagos")
                                                        <a class="btn btn-info btn-sm text-white" href="{{ route('ratificacion_pagar', $solicitud->id) }}">Pagar</a> 
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($solicitud->estatus == "Concluida" || $solicitud->estatus == "Concluida Pagos")
                                                        <a class="btn btn-primary btn-sm" href="{{ route('ratificacion_cumplimientos', $solicitud->id) }}">Generar cumplimiento</a>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column gap-1 align-items-center">
                                                        <button type="button" class="btn btn-warning btn-sm text-dark fw-semibold open-expediente-modal w-100" data-bs-toggle="modal" data-bs-target="#expediente" data-id="{{ $solicitud->id }}" style="background-color: #CEA845; border-color: #CEA845; color: #000000 !important;">
                                                            <i class="bi bi-upload me-1"></i> Subir Documento
                                                        </button>

                                                        @if(in_array($solicitud->estatus, ['Concluida', 'Concluida Pagos', 'Confirmado', 'Incumplimiento', 'Archivada']))
                                                            <div class="dropdown w-100">
                                                                <button class="btn btn-secondary btn-sm text-dark fw-semibold dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: #e2e6ea; border-color: #d3d9df; color: #000000 !important;">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                                    <li>
                                                                        <button type="button" class="dropdown-item btn-cargar-lista-docs" data-id="{{ $solicitud->id }}">
                                                                            Documentos Digitales
                                                                        </button>
                                                                    </li>
                                                                    
                                                                    @if(in_array($solicitud->estatus, ['Concluida', 'Concluida Pagos', 'Incumplimiento']))
                                                                        @if(($solicitud->estatus == "Concluida" && $solicitud->motivo == "Pago de prestaciones" && $solicitud->PagoPTU == "1") || ($solicitud->estatus == "Concluida" && $solicitud->motivo == "PTU"))
                                                                            <li><a class="dropdown-item" href="{{ route('PDFconvenioPTU_NO_R', $solicitud->id) }}" target="_blank">Convenio PTU</a></li>
                                                                        @else
                                                                            <li><a class="dropdown-item" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}" target="_blank">Convenio</a></li>
                                                                        @endif
                                                                    @endif

                                                                    @if(in_array($solicitud->estatus, ['Concluida', 'Concluida Pagos']))
                                                                        <li><a class="dropdown-item" href="{{ route('PDFaudiencia', $solicitud->id) }}" target="_blank">Acta de audiencia</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfo', ['tipo' => 'ratificacion', 'id' => $solicitud->id]) }}" target="_blank">Formato de Solicitud</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFCaratulaInfoConcilioR', ['id' => $solicitud->id]) }}" target="_blank">Carátula de Ratificaciones</a></li>
                                                                        @if($solicitud->constancia == 0 && $solicitud->estatus == "Concluida")
                                                                            <li><a class="dropdown-item" href="{{ route('PDFcumplimientoR', $solicitud->id) }}" target="_blank">Constancia de cumplimiento</a></li>
                                                                        @endif
                                                                    @elseif($solicitud->estatus == "Confirmado")
                                                                        <li><a class="dropdown-item text-success fw-bold" href="{{ route('PDFratifi', $solicitud->id) }}" target="_blank">Acuse</a></li>
                                                                    @elseif($solicitud->estatus == "Incumplimiento")
                                                                        <li><a class="dropdown-item text-danger fw-bold" href="{{ route('PDFincumplimiento', $solicitud->id) }}" target="_blank">Incumplimiento</a></li>
                                                                    @elseif($solicitud->estatus == "Archivada")
                                                                        <li><a class="dropdown-item" href="{{ route('PDFinteres', $solicitud->id) }}" target="_blank">Acta de Archivo</a></li>
                                                                        <li><a class="dropdown-item" href="{{ route('PDFincomparecenciaT', $solicitud->id) }}" target="_blank">Certificado de incomparecencia</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                @if($userRole == "Enlace" || $userRole == "Super Usuario" || $userRole == "Auxiliar")
                                                    <td class="text-center">
                                                        <a class="btn btn-success btn-sm" href="{{ route('vista_previa_citas', $solicitud->id) }}">
                                                            <i class="bi bi-pencil-square me-1"></i> Editar
                                                        </a>
                                                    </td>
                                                @endif
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

    <!-- Modal Rechazar Turnos -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form class="needs-validation" novalidate method="POST" action="{{ route('rechazar_turnos') }}">
            @csrf
            <input type="hidden" id="modal-id" name="id" value="">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Motivo de rechazo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <textarea name="observaciones" class="form-control" rows="4" required placeholder="Escriba el motivo de rechazo..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Subir Expediente -->
    <div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <form class="needs-validation" novalidate method="POST" action="{{ route('subir_expediente_ratificacion') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="audiencia_id" id="expediente_audiencia_id" value="">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Subir expediente</h5>
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
                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845; color: #ffffff !important;">Agregar</button> 
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal Listado de Documentos Digitales -->
    <div class="modal fade" id="modalListaDocs" tabindex="-1" aria-labelledby="modalListaDocsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background-color: #4A001F;">
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalListaDocsLabel">
                        <i class="bi bi-folder2-open me-2"></i>Documentos Digitales
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive rounded border">
                        <table class="table table-striped table-hover align-middle mb-0 w-100">
                            <thead style="background-color: #4A001F;">
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
@endsection

<div id="nuevo_poder" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>
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
                    "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "zeroRecords": "No se encontraron coincidencias."
                }
            });

            // Asignación de ID para subida de expediente
            $(document).on('click', '.open-expediente-modal', function() {
                var idRegistro = $(this).data('id');            
                document.getElementById('expediente_audiencia_id').value = idRegistro;
            });

            // Lógica del Modal de Documentos Digitales vía AJAX
            $(document).on('click', '.btn-cargar-lista-docs', function() {
                const solicitudId = $(this).data('id');
                const tbody = $('#tbodyListaDocs');
                const modalElement = document.getElementById('modalListaDocs');
                const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

                // Utiliza la ruta nombrada para consultar documentos de la ratificación (versión JSON del modal)
                const routeTemplate = '{{ route("VerDocumentosRatificacionModal", ["id" => "xxx"]) }}';
                const finalUrl = routeTemplate.replace('xxx', solicitudId);

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
                                    <td colspan="3" class="text-center text-muted py-4">No hay documentos registrados para esta ratificación.</td>
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
        });
    </script>
@endsection