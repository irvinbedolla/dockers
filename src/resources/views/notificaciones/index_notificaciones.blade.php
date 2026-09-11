@extends('layouts.app')
@section('title', 'Solicitudes')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Notificaciones</h3>
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
                                
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <div align="center">
                                        <a href="{{ route('notificaciones_consultar') }}" class="btn btn-primary" style="width: 100%">Notificaciones</a>
                                    </div>
                                </div>
                            
                            
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <div align="center">
                                        @if($userRole === "Notificador")
                                            <a href="{{ route('seer') }}" class="btn btn-primary" style="width: 100%">Por Notificar</a>
                                        @else
                                            <a href="{{ route('notificaciones') }}" class="btn btn-primary" style="width: 100%">Por Notificar</a>
                                        @endif
                                    </div>
                                </div>
                                @if($userRole === "Notificador")
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <div align="center">
                                        <a href="{{ route('Historial_Notificacador') }}" class="btn btn-primary" style="width: 100%">Mis Notificaciones</a>
                                        
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
