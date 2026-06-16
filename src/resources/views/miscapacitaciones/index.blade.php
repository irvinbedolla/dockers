@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Mis capacitaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if (\Session::has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <ul>
                                        <li>{!! \Session::get('success') !!}</li>
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @can('ver-miscapacitaciones')
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Capacitación</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Calificación</th>
                                            <th style="color: #fff;">Acciones</th>
                                        </thead>
                                        <tbody>
                                            @foreach($capacitaciones as $capacitacion)
                                                <tr>
                                                    <td style="display: none;">{{$capacitacion->id}}</td>
                                                    <td>{{$capacitacion->nombre}}</td>
                                                    <td>{{$capacitacion->estatus}}</td>
                                                    <td>
                                                        {{$capacitacion->calificacion}}
                                                    </td>
                                                    <td> 
                                                        <a class="btn btn-info" href="{{ route('miscapacitaciones.edit', $capacitacion->id)}}" onclick=nuevo_estadistica();>Iniciar</a>
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