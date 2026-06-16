@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Busqueda de audiencias</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Consulta tus audiencias</h3>
                            <form method="POST" action="{{ route('historial_conciliador') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                        <label>Fecha inicio</label>
                                        <input type="date" name="fecha_inicio" class="form-control">
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
                                <div class="modal-footer text-center">
                                    <button type="submit" class="btn btn-primary" onclick=consultar_estadistica(); >Consultar</button>
                                </div>
                            </form>
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
    <script src="../../public/js/estadistica/estadistica.js"></script>
@endsection



