@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Estadisticas</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Historial de solicitudes</h3>
                            @can('ver-seer')
                                <div class="table-responsive">
                                    <table id="HistorialSolicitudes" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Número unico de identificación</th>
                                            <th style="color: #fff;">Solicitante</th>
                                            <th style="color: #fff;">Motivo</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Actividad económica</th>
                                            <th style="color: #fff;">Detalles</th>

                                        </thead>
                                        <tbody>
                                            @foreach($personas as $persona)
                                                <tr>
                                                    <td style="display: none;">{{$persona->id_solicitud}}</td>
                                                    <td>{{$persona->fecha}}</td> 
                                                    <td>{{$persona->NUE}}</td>
                                                    <td>{{$persona->solicitante}}</td>
                                                    <td>{{$persona->motivo}}</td>
                                                    <td>{{$persona->estatus}}</td>
                                                    <td>{{$persona->actividad_economica}}</td>
                                                    <td><a class="btn btn-primary" href="{{ route('seer.estadistica_consultar', $persona->id_solicitud) }}" onclick=consultar_estadistica();>Consultar</a></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>      
                                </div>

                            @endcan
                            <!-- Centramos la paginación a la derecha-->
                            <div class="pagination justify-content-end"></div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="menu_carga" style ="display: none;">
            <div>.</div>
            <div class="loader"></div>
        </div>
        
        @section('scripts')
            <script src="../../public/js/estadistica/estadistica.js"></script>
        @endsection
        
    </section>
@endsection    