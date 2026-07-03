@extends('layouts.app_editar')

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
                            <a class="btn btn-info" href="{{ route('capacitaciones') }}"> Regresar</a><br>
                            @can('ver-curso')
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-1">
                                        <thead style="background-color: #4A001F;">
                                            <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Nombre</th>
                                            <th style="color: #fff;">Modulo</th>
                                            <th style="color: #fff;">Persona</th>
                                            <th style="color: #fff;">Calificacion</th>
                                        </thead>
                                        <tbody>
                                            @foreach($capacitaciones as $capacitacion)
                                                <tr>
                                                    <td>{{$capacitacion->capacitacion}}</td>
                                                    <td>{{$capacitacion->modulo}}</td>
                                                    <td>{{$capacitacion->persona}}</td>
                                                    <td>{{$capacitacion->calificacion}}</td>
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

