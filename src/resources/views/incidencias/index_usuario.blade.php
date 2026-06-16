@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Incidencias</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <a class="btn btn-warning" href="{{ route('incidencias_crear') }}" onclick=crear_usuario();>Nueva</a>
                            <div class="table-responsive">
                                <table id="example" class="table table-striped mt-1" style="text-align:center">
                                    <thead style="background-color: #4A001F;">
                                        <th style="display: none;">ID</th>
                                        <th style="color: #fff;">Fecha</th>
                                        <th style="color: #fff;">Hora</th>
                                        <th style="color: #fff;">Motivo</th>
                                        <th style="color: #fff;">Estatus</th>
                                        <th style="color: #fff;">Tiempo atención</th>
                                    </thead>
                                    <tbody>
                                        @foreach($incidencias as $incidencia)
                                            <tr>
                                                <td style="display: none;">{{$incidencia->id}}</td>
                                                <td>{{$incidencia->created_at->format('d-m-Y')}}</td>
                                                <td>{{$incidencia->created_at->format('H:i:s')}}</td>
                                                <td>{{$incidencia->motivo}}</td>
                                                <td>{{$incidencia->estatus}}</td>
                                                <td>{{ $incidencia->created_at->diffForHumans($incidencia->updated_at) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- Centramos la paginación a la derecha-->
                            <div class="pagination justify-content-end">
                            </div>                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="menu_carga" style ="display: none;">
            <div>.</div>
            <div class="loader"></div>
        </div>
        
@section('scripts')
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
@endsection
        
    </section>
    
    
@endsection




