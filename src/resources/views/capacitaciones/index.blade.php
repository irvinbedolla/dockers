@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Capacitaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('crear-curso')
                                <a class="btn btn-warning" href="{{ route('capacitaciones.create') }}" onclick=nuevo_estadistica();> Nuevo</a>
                            @endcan
                            @can('aceptar-persona')
                                <a class="btn btn-warning" href="{{ route('capacitaciones.personas') }}" onclick=nuevo_estadistica();> Personas</a>
                            @endcan

                            @can('ver-curso')
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Capacitacíon</th>
                                            <th style="color: #fff;">Modulos</th>
                                            <th style="color: #fff;">Participantes</th>
                                            <th style="color: #fff;">Calificaciones</th>
                                            <th style="color: #fff;">Periodo</th>
                                            <th style="color: #fff;">Acciones</th>
                                        </thead>
                                        <tbody>
                                            @foreach($capacitaciones as $capacitacion)
                                                <tr>
                                                    <td>{{$capacitacion->nombre}}</td>
                                                    <td><a class="btn btn-success" href="{{ route('capacitaciones.modulos', $capacitacion->id)}}" onclick=nuevo_estadistica();>Consultar</a></td>
                                                    <td>
                                                    @if($capacitacion->estatus == "Terminado")
                                                        <a class="btn btn-success" href="{{ route('capacitaciones.addpersonas', $capacitacion->id)}}" onclick=nuevo_estadistica();>Agregar</a>
                                                    @endif
                                                    </td>
                                                    <td><a class="btn btn-success" href="{{ route('capacitaciones.calificaciones', $capacitacion->id)}}" onclick=nuevo_estadistica();>Consultar</a></td>
                                                    <td>{{$capacitacion->inicio}} : {{$capacitacion->fin}}</td>
                                                    <td>
                                                        <form method="POST" action="{{ route('capacitaciones.destroy', $capacitacion->id) }} ">
                                                            @csrf
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endcan
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

<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../public/js/estadistica/estadistica.js"></script>
@endsection