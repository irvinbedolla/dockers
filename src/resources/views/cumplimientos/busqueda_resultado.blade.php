@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp

<style>
    /* Style the tab */
    .tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }
    
    /* Style the buttons that are used to open the tab content */
    .tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
    }
    
    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }
    
    /* Create an active/current tablink class */
    .tab button.active {
        background-color: #ccc;
    }
    
    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }

    <style>
    body {font-family: Arial;}

    /* Style the tab */
    .tab {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
    }

    /* Style the buttons inside the tab */
    .tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
    font-size: 17px;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
    background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .tab button.active {
    background-color: #ccc;
    }

    /* Style the tab content */
    .tabcontent {
    display: none;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
    }
    .span {
        width: 100%;
        height: 50px;
    }
</style>

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
                            <div class="tab">
                                <a class="btn btn-info" onclick="openCity(event, 'detalles')">Ratificaciones</a>
                                <a class="btn btn-info" onclick="openCity(event, 'solicitante')">Audiencias</a>
                            </div>


                            <div id="detalles" class="tabcontent">
                                <div id="tabla_detalles" class="row">
                                    <div class="table-responsive">
                                        <table id="example" class="table table-striped mt-2">
                                            <thead style="background-color: #4A001F;">
                                                <th style="color: #fff;">Fecha</th>
                                                <th style="color: #fff;">Hora</th>
                                                <th style="color: #fff;">Número de Expediente</th>
                                                <th style="color: #fff;">Empresa</th>
                                                <th style="color: #fff;">Trabajador</th>
                                                <th style="color: #fff;">Descripción</th>
                                                <th style="color: #fff;">Observaciones</th>
                                                <th style="color: #fff;">Monto</th>
                                                <th style="color: #fff;">Estatus</th>
                                                <th style="color: #fff;">Concluir</th>
                                                <th style="color: #fff;">Documentos</th>
                                            </thead>
                                            <tbody>
                                                @foreach($solicitudes as $ratificacion)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($ratificacion->fecha)->format('d/m/Y') }}</td>
                                                        <td>{{\Carbon\Carbon::parse($ratificacion->hora)->translatedFormat('h:i')}} Hrs.</td>
                                                        <td>{{$ratificacion->NUE}}</td>
                                                        <td>{{$ratificacion->empresa}}</td>
                                                        <td>{{$ratificacion->trabajador}}</td>
                                                        <td>{{$ratificacion->descripcion}}</td>
                                                        <td>{{$ratificacion->observaciones}}</td>
                                                        <td>${{number_format($ratificacion->monto, 2)}}</td>
                                                        <td>{{$ratificacion->estatus}}</td>
                                                        <td><a class="btn btn-primary" href="{{ route('consulta_cumplimiento', ['id' => $ratificacion->id, 'tipo' => 3] ) }}" target="_blank">Consultar</a></td>
                                                        <td>
                                                           @if($ratificacion->estatus == "Pagado")
                                                                <a class="btn btn-success" href="{{ route('PDFpagos', $ratificacion->id) }}" target="_blank">PDF</a>
                                                            @elseif($ratificacion->estatus == "No pagado")
                                                                <a class="btn btn-info" href="{{ route('PDFincumplimiento', $ratificacion->id_solicitud) }}" target="_blank">PDF</a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="solicitante" class="tabcontent">
                                <div id="tabla_solicitante" class="row">   
                                    <div class="table-responsive">
                                        <table id="example" class="table table-striped mt-2">
                                            <thead style="background-color: #4A001F;">
                                                <th style="color: #fff;">Fecha</th>
                                                <th style="color: #fff;">Hora</th>
                                                <th style="color: #fff;">Número de Expediente</th>
                                                <th style="color: #fff;">Trabajador</th>
                                                <th style="color: #fff;">Descripción</th>
                                                <th style="color: #fff;">Observaciones</th>
                                                <th style="color: #fff;">Monto</th>
                                                <th style="color: #fff;">Estatus</th>
                                                <th style="color: #fff;">Concluir</th>
                                                <th style="color: #fff;">Documentos</th>
                                            </thead>
                                            <tbody>
                                                @foreach($solicitudes as $audiencia)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($audiencia->fecha)->format('d/m/Y') }}</td>
                                                        <td>{{\Carbon\Carbon::parse($audiencia->hora)->translatedFormat('h:i')}} Hrs.</td>
                                                        <td>{{$audiencia->NUE}}</td>
                                                        <td>{{$audiencia->trabajador}}</td>
                                                        <td>{{$audiencia->descripcion}}</td>
                                                        <td>{{$audiencia->observaciones}}</td>
                                                        <td>${{number_format($audiencia->monto, 2)}}</td>
                                                        <td>{{$audiencia->estatus}}</td>
                                                        <td><a class="btn btn-primary" href="{{ route('consulta_cumplimiento', ['id' => $audiencia->id, 'tipo' => 4] ) }}">Consultar</a></td>
                                                        <td>
                                                            @if($audiencia->estatus == "Pagado")
                                                                <a class="btn btn-success" href="{{ route('VerPDFAudiencia', $audiencia->id) }}" target="_blank">PDF</a>
                                                            @elseif($audiencia->estatus == "No pagado")
                                                                <a class="btn btn-info" href="{{ route('PDFincumplimientoAudiencia', $audiencia->id_solicitud) }}" target="_blank">PDF</a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>                     
                                </div>
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


<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
        <script>
            $('#tabla_detalles').show();
            $('#tabla_solicitante').show();
    </script>
@endsection