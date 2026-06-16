@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
    $id = auth()->user()->id;
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Expediente</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                        @if($rol == "Super Usuario" || $rol == 'Capacitacion Admin')
                            <a class="btn btn-warning" href="{{ route('expedientes.edit', $id)}}">Registrar Expediente</a>
                            <div class="table-responsive">
                                <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="display: none;">ID</th>
                                        <th style="color: #fff;">Nombres</th>
                                        <th style="color: #fff;">Correo</th>
                                        <th style="color: #fff;">Teléfono</th>
                                        <th style="color: #fff;">Acciones</th>
                                    </thead>
                                    <tbody>
                                        @foreach($personas as $persona)
                                            <tr>
                                                <td style="display: none;">{{$persona->id_usuario}}</td>
                                                <td>{{$persona->nombre}}</td>
                                                <td>{{$persona->email}}</td>
                                                <td>{{$persona->telefono}}</td>
                                                <td>
                                                    <a class="btn btn-info" href="{{ route('expedientes.edit', $persona->id_usuario)}}">Editar</a>
                                                    <a class="btn btn-info"    href="{{ route('expedientes.documentos', $persona->id_usuario)}}">Documentos</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            @if($persona == "no existe")
                                <a class="btn btn-warning" href="{{ route('expedientes.edit', $id)}}">Registrar Expediente</a>                               
                            @endif
                            @if($persona != "no existe")
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-2">
                                        <thead style="background-color: #4A001F;">
                                            <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Nombres</th>
                                            <th style="color: #fff;">Correo</th>
                                            <th style="color: #fff;">Telefono</th>
                                            <th style="color: #fff;">Acciones</th>
                                        </thead>
                                        <tbody>
                                            @foreach($personas as $persona)
                                                <tr>
                                                    <td style="display: none;">{{$persona->id_usuario}}</td>
                                                    <td>{{$persona->nombre}}</td>
                                                    <td>{{$persona->email}}</td>
                                                    <td>{{$persona->telefono}}</td>
                                                    <td>
                                                        <a class="btn btn-info" href="{{ route('expedientes.edit', $persona->id_usuario)}}">Editar</a>
                                                        <a class="btn btn-info"    href="{{ route('expedientes.documentos', $persona->id_usuario)}}">Documentos</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Centramos la paginación a la derecha-->
                                <div class="pagination justify-content-end">
                                </div>
                            @endif    
                        @endif                        
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