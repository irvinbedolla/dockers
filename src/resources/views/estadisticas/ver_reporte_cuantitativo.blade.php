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
                            <h3 class="text-center">Reporte Cuantitativo</h3>
                            <a class="btn btn-primary" href="{{ route('seer.estadistica') }}">Regresar</a>
                            <div id="solicitud" class="tabcontent">
                                <div class="table-responsive">
                                    <table id="tabla_seer_auxiliares1" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Solicitudes</th>
                                            <th style="color: #fff;">Ratificaciones</th>
                                            <th style="color: #fff;">Convenio en ratificaciones</th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{$solicitudes->solicitudes}}</td>
                                                <td>{{$ratificaciones->ratificaciones}}</td>
                                                <td>${{ number_format($montoratificaciones->ratificaciones,2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table id="tabla_seer_auxiliares1" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Audiencias</th>
                                            <th style="color: #fff;">Convenio de audiencias</th>
                                            <th style="color: #fff;">N° Pagos</th>
                                            <th style="color: #fff;">Total de Pagos</th>
                                            <th style="color: #fff;">Asesorias</th>
                                            <th style="color: #fff;">Colectivas</th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{$audiencia->audiencia}}</td>
                                                <td>${{ number_format($montoaudiencia->audiencia,2) }}</td>
                                                <td>{{$convenios->convenios}}</td>
                                                <td>${{ number_format($total_pagos->monto_pagos, 2)}}</td>
                                                <td>{{$asesorias->asesorias}}</td>
                                                <td>{{$colectivas->colectivas}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table id="tabla_seer_auxiliares1" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Porcentaje de Efectividad</th>
                                            <th style="color: #fff;">Total Convenido</th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{($porcenaje)*100}}%</td>
                                                <td>${{ number_format(($montoratificaciones->ratificaciones+$montoaudiencia->audiencia),2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>      
                            </div>                      
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../public/js/estadistica/estadistica.js"></script>
@endsection

