@extends('layouts.app')

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
                            <h3 class="text-center">Historial</h3>
                            @can('ver-seer')
                                <div class="table-responsive">
                                    <table id="HistorialSolicitudes" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Fecha de notificación</th>
                                            <th style="color: #fff;">Número único de identificación</th>
                                            <th style="color: #fff;">Solicitante</th>
                                            <th style="color: #fff;">Empresa</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Citatorio</th>
                                        </thead>
                                        <tbody>
                                            @foreach($notificaciones as $notificacion)
                                                <tr>
                                                    <td style="display: none;">{{$notificacion->id_solicitud}}</td>
                                                    <td>{{$notificacion->fecha}}</td> 
                                                    <td>{{$notificacion->NUE}}</td>
                                                    <td>{{$notificacion->nombre_solicitante}}</td>
                                                    <td>{{$notificacion->nombre}} {{$notificacion->primer_apellido}} {{$notificacion->segundo_apellido}}</td>
                                                    <td>{{$notificacion->estatus}}</td>
                                                    <td>
                                                        <a class="btn btn-success" href="{{ route('PDFconvenio', $notificacion->id_citado) }}" onclick=consultar_estadistica(); tarjet="_black">Citatorio</a>
                                                    </td>
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