@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Solicitudes</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Folio</th>
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Solicitante</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Resumen</th>
                                            <th style="color: #fff;">Documentos</th>
                                        </thead>
                                        <tbody>
                                            @foreach($solicitudes as $solicitud)
                                                <tr>
                                                    <td>{{$solicitud->id}}</td>
                                                    <td>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d/m/y') }}</td> 
                                                    <td>{{$solicitud->nombre}}</td>
                                                    <td>
                                                        @if($solicitud->estatus == "Prevencion")
                                                            <p style="color: red;">Prevención</p>
                                                        @else
                                                            {{$solicitud->estatus}}
                                                        @endif
                                                    </td>
                                                    <td><a class="btn btn-primary" href="{{ route('consulta_solicitante', $solicitud->id) }}" onclick=consultar_estadistica();>Consultar</a></td>
                                                    <td>
                                                        @if(($solicitud->estatus !== "Pendiente") && ($solicitud->estatus !== "Prevencion"))
                                                            <div class="dropdown  mt-2">
                                                                <button class="btn btn-warning dropdown-toggle load-pdfs" type="button" id="dropdownCitatoriosBtn-{{ $solicitud->id }}" data-id="{{ $solicitud->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownCitatoriosBtn-{{ $solicitud->id }}">
                                                                    <li><button type="button" id="btnMostrarRegistros" class="btn btn-info" style="width: 100%" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                    @if(($solicitud->estatus !== "Pendiente") && ($solicitud->estatus !== "Prevencion"))
                                                                        <li><a class="btn btn-info" style="width: 100%"  href="{{ route('PDFnotificacion_solicitante', $solicitud->id) }}" target="_blank">Notificación al solicitante</a></li>
                                                                        <li><a class="btn btn-info" style="width: 100%"  href="{{ route('PDFacuseConfirmada', $solicitud->id) }}"  target="_blank">Acuse de solicitud confirmada</a></li>
                                                                        <li><a class="btn btn-info" style="width: 100%"  href="{{ route('PDFacuse_solicitud', $solicitud->id) }}"  target="_blank">Acuse de solicitud</a></li>
                                                                    @endif
                                                                </ul>
                                                            </div>                                           
                                                        @endif
                                                        @if(($solicitud->estatus === "Pendiente"))
                                                            <a class="btn btn-info" href="{{ route('PDFacuse_solicitud', $solicitud->id) }}"  target="_blank">Acuse de solicitud</a>
                                                        @endif
                                                        @if($solicitud->estatus === "Concluida")
                                                            <a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}"    target="_blank">Documentos Digitales</a>
                                                            <a class="btn btn-success" style="width: 100%"  href="{{ route('PDFconveniosolicitud', $solicitud->id) }}"  target="_blank">Convenio</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            <div class="pagination justify-content-end">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
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

@section('scripts')

<script src="../public/assets/js/poderes/general.js"></script>
<script>
        const pdfsUrlBase = "{{ url('solicitud/pdfs') }}";

        $(document).ready(function() {
            $('#btnMostrarRegistros').on('click', function() {
                const listaRegistros = $('#listaRegistros');
                const pdfsUrlBase = "{{ url('ObtenerCitatorios') }}";
                const id = $(this).data('id');
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
                                        <a href="${pdfUrl}">PDF</a>
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
</script>
@endsection


