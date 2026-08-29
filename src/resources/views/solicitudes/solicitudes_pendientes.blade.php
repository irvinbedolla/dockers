@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Solicitudes Pendientes</h3>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped table-hover align-middle w-100">
                                    <thead style="background-color: #354647;">
                                        <tr>
                                            <th class="text-white" style="color: #ffffff !important;">Fecha Captura</th>
                                            <th class="text-white" style="color: #ffffff !important;">Solicitante</th>
                                            <th class="text-white" style="color: #ffffff !important;">Rama Industrial</th>
                                            <th class="text-white" style="color: #ffffff !important;">Actividad Económica</th>
                                            <th class="text-white" style="color: #ffffff !important;">Patronal / Trabajador</th>
                                            <th class="text-center text-white" style="color: #ffffff !important;">Estatus</th>
                                            <th class="text-white" style="color: #ffffff !important;">Tipo Solicitud</th>
                                            <th class="text-white" style="color: #ffffff !important;">Delegación</th>
                                            <th class="text-center text-white" style="width: 10%; color: #ffffff !important;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="contenidobusqueda">
                                        @foreach($solicitudes as $solicitud)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($solicitud->fecha)->format('d-m-Y') }}</td>
                                                <td class="fw-bold">{{ $solicitud->nombre }}</td>
                                                <td>{{ $solicitud->rama_industrial }}</td>
                                                <td>{{ $solicitud->actividad }}</td>
                                                
                                                <td>
                                                    @if($solicitud->tipo_solicitud == 1)
                                                        Trabajador
                                                    @elseif($solicitud->tipo_solicitud == 2)
                                                        Patronal
                                                    @elseif($solicitud->tipo_solicitud == 3)
                                                        Patronal Colectiva
                                                    @elseif($solicitud->tipo_solicitud == 4)
                                                        Sindical
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                        {{ $solicitud->estatus }}
                                                    </span>
                                                </td>

                                                <td>
                                                    @if($solicitud->tipo_generacion == 0)
                                                        <span class="badge bg-info text-white">Solicitud en línea</span>
                                                    @elseif($solicitud->tipo_generacion == 1000)
                                                        <span class="badge bg-secondary">Solicitud en tablet</span>
                                                    @else
                                                        <span class="badge bg-primary">Personal Centro</span>
                                                    @endif
                                                </td>

                                                <td>{{ $solicitud->delegacion }}</td>

                                                <td class="text-center">
                                                    <a class="btn btn-info btn-sm text-white" href="{{ route('solicitud_editar', $solicitud->id) }}" onclick="editar_usuario();">
                                                        <i class="bi bi-eye me-1"></i> Revisar
                                                    </a>
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
@endsection

<div id="nuevo_usuario" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="{{ asset('assets/js/usuarios/usuarios.js') }}"></script>
    <script>
        $(document).ready(function() {
            if (!$.fn.DataTable.isDataTable('#example')) {
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
                        "infoEmpty": "Mostrando 0 a 0 de 0 solicitudes",
                        "infoFiltered": "(filtrado de un total de _MAX_ solicitudes)",
                        "zeroRecords": "No se encontraron coincidencias."
                    }
                });
            }
        });
    </script>
@endsection