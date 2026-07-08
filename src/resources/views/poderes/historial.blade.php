@extends('layouts.app')
@section('content')
<section class="section">
        <div class="section-header">
            <h3 class="page__heading">Historial</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('ver-abogado')
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-2">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff; text-align: center;">Folio</th>
                                            <th style="color: #fff; text-align: center;">Editor</th>
                                            <th style="color: #fff; text-align: center;">Fecha y hora de la edición</th>
                                            <th style="color: #fff; text-align: center;">Tipo</th>
                                            <th style="color: #fff; text-align: center;">Registro</th>
                                        </thead>
                                        <tbody>
                                            @foreach ($historiales as $historial)
                                                <tr>
                                                    <td style="text-align: center;">{{ $historial->id_abogado }}</td>
                                                    <td style="text-align: center;">{{ $historial->usuario->name }}</td>
                                                    <td style="text-align: center;">{{ $historial->updated_at}}</td>
                                                    <td style="text-align: center;">{{ $historial->tipo_cambio }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-warning btn-ver-historial" data-bs-toggle="modal" data-bs-target="#expediente" data-historial-id="{{ $historial->id }}">Visualizar</button>
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
    </section>
@endsection

@section('page_css')
    <style>
        /* Fuerza el ancho del modal aunque exista CSS de Bootstrap 4/tema que lo sobrescriba */
        #expediente .modal-dialog {
            width: 900px !important;
            max-width: 900px !important;
        }
    </style>
@endsection

<div class="modal fade" id="expediente" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <form class='needs-validation' novalidate method='POST' action="#" enctype="multipart/form-data">
        @csrf
        <div class="modal-dialog" style="--bs-modal-width: 900px; max-width: 900px; width: 900px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Expediente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <h5 class="modal-title" id="modalLabel">Empleador</h5>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Tipo de Persona</label>
                            <input type="text" id="modal_tipo_representante" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-9">
                            <label class="form-label">Nombre de la empresa/patrón</label>
                            <input type="text" id="modal_nombre_patron" name="nombre_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Telefono Patronal</label>
                            <input type="text" id="modal_telefono_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Email Patronal</label>
                            <input type="text" id="modal_email_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">CURP Patronal</label>
                            <input type="text" id="modal_curp_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">RFC Patronal</label>
                            <input type="text" id="modal_rfc_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Género</label>
                            <input type="text" id="modal_sexo_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Giro Comercial</label>
                            <input type="text" id="modal_giro_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Estado</label>
                            <input type="text" id="modal_estado_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Municipio</label>
                            <input type="text" id="modal_municipio_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Tipo de vialidad</label>
                            <input type="text" id="modal_tipo_vialidad_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nombre de la vialidad</label>
                            <input type="text" id="modal_vialidad_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Núm Ext.</label>
                            <input type="text" id="modal_num_ext_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Núm Int.</label>
                            <input type="text" id="modal_num_int_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Colonia</label>
                            <input type="text" id="modal_colonia_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">C.P.</label>
                            <input type="text" id="modal_cp_patron" class="form-control" disabled>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Representante</label>
                            <input type="text" id="modal_representante" class="form-control" disabled>
                        </div>

                        <div id="bloque_representante" class="col-12">
                            <div class="row g-3">
                                <h5 class="modal-title" id="modalLabel">Representante</h5>

                                <div class="col-12 col-md-9">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" id="modal_nombre_representante" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" id="modal_numero_representante" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Correo Electrónico</label>
                                    <input type="text" id="modal_correo_representante" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">CURP</label>
                                    <input type="text" id="modal_curp_representante" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Género</label>
                                    <input type="text" id="modal_sexo_representante" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Tipo de Documento</label>
                                    <input type="text" id="modal_tipo_documento_representante" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-9">
                                    <label class="form-label">Descripción del Poder</label>
                                    <input type="text" id="modal_descripcion_poder" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Fecha de expedición</label>
                                    <input type="date" id="modal_fecha_registro" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Fecha de vigencia</label>
                                    <input type="date" id="modal_fecha_vigencia" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Estatus</label>
                                    <input type="text" id="modal_estatus" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Tipo de Identificación</label>
                                    <input type="text" id="tipo_identificacion" class="form-control" disabled>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Número de Identificación</label>
                                    <input type="text" id="numero_identificacion" class="form-control" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label class="form-label">Acta Constitutiva / Identificación del Empleador</label>
                                <br>
                                <a target="_blank" id="link_acta_constitutiva" class="d-none">PDF</a>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Identificación del Representante</label>
                                <br>
                                <a target="_blank" id="link_identificacion_representante" class="d-none">PDF</a>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Documento que acredita la personería</label>
                                <br>
                                <a target="_blank" id="link_poder_representante" class="d-none">PDF</a>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Anexo</label>
                                <br><br>
                                <a target="_blank" id="link_anexo_representante" class="d-none">PDF</a>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
</form>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {
            $(document).on('click', '.btn-ver-historial', function () {
                var historialId = $(this).data('historial-id');

                // limpiar mientras carga
                $('#modal_nombre_patron').val('Cargando...');
                $('#modal_telefono_patron').val('Cargando...');

                $.ajax({
                    url: '{{ url('/poderes/history/detail') }}/' + historialId,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        var nombreCompleto = [data.nombres_patronal, data.primer_apellido_patronal, data.segundo_apellido_patronal]
                            .filter(Boolean)
                            .join(' ');

                        $('#modal_nombre_patron').val(nombreCompleto);
                        $('#modal_telefono_patron').val(data.telefono_patronal || '');
                        $('#modal_email_patron').val(data.email_patronal || '');
                        $('#modal_curp_patron').val(data.curp_patronal || '');
                        $('#modal_rfc_patron').val(data.rfc_patronal || '');
                        $('#modal_sexo_patron').val(data.sexo_patronal || '');
                        $('#modal_giro_patron').val(data.giroComercial || '');
                        // Relaciones (por si llega snake_case o camelCase según JSON)
                        var estadoNombre = (data.estadoPatronal && data.estadoPatronal.nombre)
                            ? data.estadoPatronal.nombre
                            : ((data.estado_patronal && data.estado_patronal.nombre) ? data.estado_patronal.nombre : '');
                        var municipioNombre = (data.municipioPatronal && data.municipioPatronal.nombre)
                            ? data.municipioPatronal.nombre
                            : ((data.municipio_patronal && data.municipio_patronal.nombre) ? data.municipio_patronal.nombre : '');

                        $('#modal_estado_patron').val(estadoNombre);
                        $('#modal_municipio_patron').val(municipioNombre);
                        $('#modal_tipo_vialidad_patron').val(data.tipo_vialidad_patronal || '');
                        $('#modal_vialidad_patron').val(data.vialidad_patronal || '');
                        $('#modal_num_ext_patron').val(data.num_ext_patronal || '');
                        $('#modal_num_int_patron').val(data.num_int_patronal || '');
                        $('#modal_colonia_patron').val(data.colonia_patronal || '');
                        $('#modal_cp_patron').val(data.cp_patronal || '');

                        var representante = (data.reprecentante || '').toString().trim();
                        $('#modal_representante').val(representante);

                        // Mostrar/ocultar la sección de representante según el valor ('No' => ocultar)
                        if (representante.toLowerCase() === 'no') {
                            $('#bloque_representante').addClass('d-none');
                        } else {
                            $('#bloque_representante').removeClass('d-none');
                        }

                        var nombreCompletoRepresentante = [data.nombre_representante, data.primer_apellido_representante, data.segundo_apellido_representante]
                            .filter(Boolean)
                            .join(' ');

                        $('#modal_tipo_representante').val(data.tipo || '');
                        $('#modal_nombre_representante').val(nombreCompletoRepresentante);
                        $('#modal_numero_representante').val(data.numero_representante || '');
                        $('#modal_correo_representante').val(data.correo_representante || '');
                        $('#modal_curp_representante').val(data.curp_representante || '');
                        $('#modal_sexo_representante').val(data.sexo_representante || '');
                        $('#modal_sexo_representante').val(data.sexo_representante || '');
                        $('#modal_tipo_documento_representante').val(data.tipo_documento_representante || '');
                        $('#modal_descripcion_poder').val(data.descipcion_poder || '');
                        $('#modal_fecha_registro').val(data.fechaRegistro || '');
                        $('#modal_fecha_vigencia').val(data.fechaVigencia || '');
                        $('#modal_estatus').val(data.estatus || '');
                        $('#tipo_identificacion').val(data.tipo_identificacion || '');
                        $('#numero_identificacion').val(data.num_identificacion || '');

                        var url = '../../storage/app/documentos_abogados/' + data.ineDocumento;
                        $('#link_acta_constitutiva').removeClass('d-none').attr('href', url);

                        var url1 = '../../storage/app/documentos_abogados/' + data.representacionDocumento;
                        $('#link_identificacion_representante').removeClass('d-none').attr('href', url1);

                        var url1 = '../../storage/app/documentos_abogados/' + data.cedulaDocumento;
                        $('#link_poder_representante').removeClass('d-none').attr('href', url1);

                        var url1 = '../../storage/app/documentos_abogados/' + data.anexo_documeto;
                        $('#link_anexo_representante').removeClass('d-none').attr('href', url1);

                    },
                    error: function (xhr) {
                        console.error('Error al obtener el historial:', xhr);
                        $('#modal_nombre_patron').val('No se pudo cargar');
                        $('#modal_telefono_patron').val('');
                    }
                });
            });
        });
    </script>
@endsection
