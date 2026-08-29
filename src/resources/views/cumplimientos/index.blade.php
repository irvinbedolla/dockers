@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Cumplimientos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ url()->current() }}" method="GET" class="mb-4">
                                <div class="input-group" style="max-width: 450px;">
                                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por NUE" value="{{ request('buscar') }}">
                                    <button class="btn btn-primary" type="submit" style="background-color: #354647; border-color: #354647;">Buscar</button>
                                    @if(request('buscar'))
                                        <a href="{{ url()->current() }}" class="btn btn-secondary">Limpiar</a>
                                    @endif
                                </div>
                            </form>
                                <div class="table-responsive">
                                    <table id="example" class="table-striped" style="width:100%">
                                        <thead style="background-color: #354647;">
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Hora</th>
                                            <th style="color: #fff;">Número de Expediente</th>
                                            <th style="color: #fff;">Tipo</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Detalles</th>
                                        </thead>
                                        <tbody class="contenidobusqueda">
                                            @foreach($cumplimientos as $audiencia)
                                                <tr>
                                                    <td>{{ $audiencia->fecha_formateada }}</td>
                                                    <td>{{ $audiencia->hora_formateada }}</td>
                                                    <td>{{$audiencia->NUE_FINAL}}</td>
                                                    <td>{{$audiencia->tipo_pago}}</td>
                                                    <td>{{$audiencia->estatus}}</td>
                                                    <td><a class="btn btn-primary btn-sm" href="{{ route('pago_cumplimiento', $audiencia->id) }}"><i class="bi bi-receipt"></i> Cumplimiento</a></td>
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


@section('scripts')
    <script src="{{ asset('assets/js/usuarios/usuarios.js') }}"></script>
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
                    "info": "Mostrando del _START_ al _END_ de un bloque de _TOTAL_ solicitudes",
                    "infoEmpty": "Mostrando 0 a 0 de 0 filas",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "zeroRecords": "No se encontraron coincidencias en esta página."
                }
            });
        });
    </script>
@endsection