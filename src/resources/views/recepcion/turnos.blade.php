@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Gestión de Turnos</h3>
            <a class="btn btn-warning shadow-sm" href="{{ route('nueva_cita') }}" onclick="crear_turnos();" style="background-color: #CEA845; border-color: #CEA845; color: #fff;">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Turno
            </a>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            @can('ver-turno')
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped table-hover align-middle w-100">
                                        <thead style="background-color: #354647;">
                                            <tr>
                                                <th class="text-center text-white" style="width: 5%; color: #ffffff !important;">Folio</th>
                                                <th class="text-white" style="color: #ffffff !important;">Solicitante</th>
                                                <th class="text-white" style="color: #ffffff !important;">Tipo</th>
                                                <th class="text-white" style="color: #ffffff !important;">Hora</th>
                                                <th class="text-white" style="color: #ffffff !important;">Módulo</th>
                                                <th class="text-white" style="color: #ffffff !important;">Auxiliar</th>
                                                <th class="text-center text-white" style="color: #ffffff !important;">Estatus</th>
                                                <th class="text-center text-white" style="width: 12%; color: #ffffff !important;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="contenidobusqueda">
                                            @foreach($turnos as $turno)
                                                <tr>
                                                    <td class="text-center fw-bold">{{ $turno->id }}</td>
                                                    <td>{{ $turno->solicitante }}</td>
                                                    <td>{{ $turno->tipo }}</td>
                                                    <td>{{ $turno->hora }}</td>
                                                    <td>{{ $turno->lugar_auxiliar }}</td>
                                                    <td>
                                                        @if(!empty($turno->name))
                                                            {{ $turno->name }}
                                                        @else
                                                            <span class="text-muted italic">Pendiente</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if(strtolower($turno->estatus) === 'atendido')
                                                            <span class="badge bg-success rounded-pill px-3 py-2">Atendido</span>
                                                        @elseif(strtolower($turno->estatus) === 'no atendido')
                                                            <span class="badge bg-danger rounded-pill px-3 py-2">No atendido</span>
                                                        @else
                                                            <span class="badge bg-secondary rounded-pill px-3 py-2">{{ $turno->estatus }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($turno->estatus === "no atendido" && $turno->exepcion === "No")
                                                            <a class="btn btn-info btn-sm text-white" href="{{ route('cambiar', $turno->id) }}" onclick="disponibles();">
                                                                <i class="bi bi-person-check me-1"></i> Asignar
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('body_end')
<div id="nuevo_turno" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/turnos/turnos.js') }}"></script>
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
                        "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ turnos",
                        "infoEmpty": "Mostrando 0 a 0 de 0 turnos",
                        "infoFiltered": "(filtrado de un total de _MAX_ turnos)",
                        "zeroRecords": "No se encontraron coincidencias."
                    }
                });
            }
        });
    </script>
@endsection