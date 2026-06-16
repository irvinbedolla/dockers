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

                            @if(session()->has('success'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <strong>¡Contraseña Actualizada!</strong>
                                            {{ session()->get('success') }}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
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

                            <div class="row">
                                <div class="col-xs-4 col-sm-4 col-md-3">
                                    <div align="center" class="mb-2">
                                        <a href="{{ route('configuracion_usuarios') }}" class="btn btn-primary" style="width: 100%">Usuarios</a>
                                    </div>
                                </div>
                                @if($userRole[0] == "Super Usuario")
                                    <div class="col-xs-4 col-sm-4 col-md-3">
                                        <div align="center" class="mb-2">
                                            <a href="{{ route('configuracion_sedes') }}" class="btn btn-primary" style="width: 100%">Dias inhábiles</a>
                                        </div>
                                    </div>
                                    <div class="col-xs-4 col-sm-4 col-md-3">
                                        <div align="center" class="mb-2">
                                            <a href="{{ route('genera_retroceso') }}" class="btn btn-primary" style="width: 100%">Retrocesos</a>
                                        </div>
                                    </div>
                                    <div class="col-xs-4 col-sm-4 col-md-3">
                                        <div align="center" class="mb-2">
                                            <a href="{{ route('configuracion_borrar_cumpli') }}" class="btn btn-primary" style="width: 100%">Borrar Cumplimientos</a>
                                        </div>
                                    </div>
                                @endif
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

