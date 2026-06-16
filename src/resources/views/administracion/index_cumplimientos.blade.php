@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Administración</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('borrar_cumplimeinto')}}">
                                @csrf
                                <div class="modal-body" id="modal-body-content">
                                    <div class="row">  
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <label>Tipo de cumplimiento</label>
                                            <select class="form-control" name="tipo">
                                                <option value="">Seleccione</option>
                                                <option value="Audiencia">Audiencia</option>
                                                <option value="Ratificación">Ratificación</option>
                                            </select>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <label>Folio</label>
                                            <input type="number" class="form-control" name="folio">
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <label>Año</label>
                                            <input type="number" class="form-control" name="año">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-primary">Consultar</button>
                                </div>
                            </form>


                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Cambio de estatus Correcto.</strong>
                                    {{ session()->get('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if (session('message'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Expediente Localizado</strong>
                                    {{ session()->get('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="row">
                                    <div class="table-responsive">
                                        <table id="example" class="table table-striped mt-1">
                                            @if (session('tipo') == "Audiencia")
                                                <thead style="background-color: #4A001F;">
                                                    <tr>
                                                        <th style="color: #fff;">NUE</th>
                                                        <th style="color: #fff; text-align: center;">Fecha</th>
                                                        <th style="color: #fff; text-align: center;">Descripción</th>
                                                        <th style="color: #fff; text-align: center;">Estatus</th>
                                                        <th style="color: #fff; text-align: center;">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (session('folios_generados'))
                                                        @foreach (session('folios_generados') as $folio)
                                                            <tr>
                                                                <td style="text-align: center;">{{ $folio['NUE'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['fecha'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['descripcion'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['estatus'] }}</td> 
                                                                <td style="text-align: center;">
                                                                    <form method="POST" action="{{ route('borrar_cumplimeintoA', $folio['id']) }} ">
                                                                        @csrf
                                                                        <input type="hidden" name="_method" value="DELETE">
                                                                        <button class="btn btn-danger" onclick=editar_usuario(); type="submit">Borrar cumplimeinto</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            @endif
                                            @if (session('tipo') == "Ratificación")
                                                <thead style="background-color: #4A001F;">
                                                    <tr>
                                                        <th style="color: #fff;">NUE</th>
                                                        <th style="color: #fff; text-align: center;">Fecha</th>
                                                        <th style="color: #fff; text-align: center;">Descripción</th>
                                                        <th style="color: #fff; text-align: center;">Estatus</th>
                                                        <th style="color: #fff; text-align: center;">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (session('folios_generados'))
                                                        @foreach (session('folios_generados') as $folio)
                                                            <tr>
                                                                <<td style="text-align: center;">{{ $folio['NUE'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['fecha'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['descripcion'] }}</td>
                                                                <td style="text-align: center;">{{ $folio['estatus'] }}</td> 
                                                                <td style="text-align: center;">
                                                                    <form method="POST" action="{{ route('borrar_cumplimeintoA', $folio['id']) }} ">
                                                                        @csrf
                                                                        <input type="hidden" name="_method" value="DELETE">
                                                                        <button class="btn btn-danger" onclick=editar_usuario(); type="submit">Borrar cumplimeinto</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            @endif
                            <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            <!--<span class="badge badge-danger">{{ $error }}</span>-->
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
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