@extends('layouts.app')

<?php 
use Carbon\Carbon;
?>
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Estadistica de turno</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                                <a class="btn btn-info"    href="{{ route('turno_estadistica') }}" onclick=crear_turnos();>Regresar</a>
                                Total atendidos: {{$suma_turnos->total}}
                                <div class="table-responsive">
                                    <table id="tabla_usuarios" class="table table-striped">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff">Folio</th>
                                            <th style="color: #fff;">Auxiliar</th>
                                            <th style="color: #fff;">Solicitante</th>
                                            <th style="color: #fff;">Tipo</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Hora inicio</th>
                                            <th style="color: #fff;">Duración</th>
                                        </thead>
                                        <tbody>
                                            @foreach($turnos as $turno)
                                                <tr>
                                                    <td>{{$turno->id}}</td>
                                                    <td>{{$turno->name}}</td>
                                                    <td>{{$turno->solicitante}}</td>
                                                    <td>{{$turno->tipo}}</td>
                                                    <td>{{$turno->estatus}}</td>
                                                    <td>{{$turno->fecha}}</td>
                                                    <td>{{$turno->hora}}</td>
                                                        @php
                                                        $inicio = new DateTime("$turno->fecha $turno->hora");
                                                        $duracion = $inicio->diff($turno->updated_at);
                                                        @endphp

                                                    <td>{{$duracion->format('%H hora(s) %i minutos');}}</td>

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

<div id="nuevo_turno" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../public/assets/js/turnos/turnos.js"></script>
@endsection