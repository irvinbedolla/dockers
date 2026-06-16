@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Audiencias</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div align="center">
                                        <a href="{{ route('todas_audiencias') }}" class="btn btn-primary"  style="width: 100%">Audiencias</a>
                                    </div>
                                </div>
                                
                                <div class="col-xs-12 col-sm-4 col-md-4">
                                    <div align="center">
                                        <a href="{{ route('todas_solicitudes') }}" class="btn btn-primary"  style="width: 100%">Solicitudes</a>
                                    </div>
                                </div>
                                <!--
                                <div class="col-xs-12 col-sm-4 col-md-2">
                                    <div align="center">
                                        <a href="{{ route('audiencias.conciliador') }}" class="btn btn-primary" style="width: 100%">Audieniecias hoy</a>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-2">
                                    <div align="center">
                                        <a href="{{ route('todas_audiencias') }}" class="btn btn-primary"  style="width: 100%">Audiencias</a>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-2">
                                    <div align="center">
                                        <a href="{{ route('todas_solicitudes') }}" class="btn btn-primary"  style="width: 100%">Solicitudes</a>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-2">
                                    <div align="center">
                                        <a href="{{ route('todas_ratificaciones') }}" class="btn btn-primary"  style="width: 100%">Ratificaciones</a>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-2">
                                    <div align="center">
                                        <a href="{{ route('todos_complimientos') }}" class="btn btn-primary"  style="width: 100%">Cumplimientos</a>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4 col-md-2">
                                    <div align="center">
                                       <button type="button" class="btn btn-primary open-modal" data-bs-toggle="modal" data-bs-target="#ModalArchivar"  style="width: 100%">
                                            Buscar Solictudes
                                        </button>
                                    </div>
                                </div>
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


<div class="modal fade bd-example-modal-lg" id="ModalArchivar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('solicitudes_busqueda')}}">
        @csrf
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Filtros de busqueda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Fecha inicio</label>
                            <input type="date" name="inicio" class="form-control">
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Fecha final</label>
                            <input type="date" name="final" class="form-control">
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Número de expediente</label>
                            <input type="text" name="nue" class="form-control">
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>CURP</label>
                            <input type="text" name="curp" class="form-control">
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Nombre Solicitante</label>
                            <input type="text" name="solicitante" class="form-control">
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Nombre del citado</label>
                            <input type="text" name="citado" class="form-control">
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Folio</label>
                            <input type="text" name="folio" class="form-control">
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Estatus solicitud</label>
                            <select class="form-control" name="estatus">
                                <option value="">Seleccione</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Confirmado">Confirmado</option>
                                <option value="Incompetencia">Incompetencia</option>
                                <option value="Incomparecencia">Incomparecencia</option>
                                <option value="Archivada">Archivada</option>
                                <option value="Conciliacion">Convenio</option>
                                <option value="No conciliacion">No conciliación</option>
                            </select>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Tipo de solicitud</label>
                            <select class="form-control" name="tipo">
                                <option value="">Seleccione</option>
                                <option value="1">Trabajador</option>
                                <option value="2">Patron individual</option>
                                <option value="3">Patron colectiva</option>
                                <option value="4">Sindicato</option>
                            </select>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Auxiliar</label>
                            <select class="form-control" name="auxiliar">
                                <option value="">Seleccione</option>
                                @foreach($auxiliares as $aux)
                                    <option value="{{$aux['id']}}">{{$aux['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <label>Conciliador</label>
                            <select class="form-control" name="conciliador">
                                <option value="">Seleccione</option>
                                @foreach($conciliadores as $con)
                                    <option value="{{$con['id']}}">{{$con['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>

@section('scripts')
    <script src="../public/assets/js/poderes/general.js"></script>
@endsection
