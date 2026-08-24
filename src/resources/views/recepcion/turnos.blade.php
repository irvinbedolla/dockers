@extends('layouts.app')


@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Turnos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('crear-turnos')
                                <a class="btn btn-info" href="{{ route('nueva_cita') }}" onclick=crear_turnos();>Nuevo Turno</a>
                            @endcan

                            @can('ver-turno')
                                <div class="table-responsive">
                                    <table id="example" class="table-striped" style="width:100%">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;"></th>
                                            <th style="color: #fff;">Folio</th>
                                            <th style="color: #fff;">Solicitante</th>
                                            <th style="color: #fff;">Tipo</th>
                                            <th style="color: #fff;">Hora</th>
                                            <th style="color: #fff;">Módulo</th>
                                            <th style="color: #fff;">Auxiliar</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Acciones</th>
                                        </thead>
                                        <tbody>
                                            @foreach($turnos as $turno)
                                                <tr>
                                                    <td></td>
                                                    <td>{{$turno->id}}</td>
                                                    <td>{{$turno->solicitante}}</td>
                                                    <td>{{$turno->tipo}}</td>
                                                    <td>{{$turno->hora}}</td>
                                                    <td>{{$turno->lugar_auxiliar}}</td>
                                                    @if($turno->name != '')
                                                        <td>{{$turno->name}}</td>
                                                    @else
                                                        <td>Pendiente</td>
                                                    @endif
                                                    
                                                    
                                                    <td>{{$turno->estatus}}</td>
                                                    
                                                    <td>  
                                                        @if($turno->estatus === "no atendido" && $turno->exepcion === "No")
                                                        <a class="btn btn-info" href="{{ route('cambiar',$turno->id) }}" onclick=disponibles();>Asignar</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endcan
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