@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Citas de Dirección General</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <a class="btn btn-warning" href="{{ route('cita_direccion_crear') }}" onclick=crear_usuario();> Nuevo</a>
                                <div class="table-responsive">
                                    <table id="example" class="table-striped" style="width:100%">
                                        <thead style="background-color: #4A001F;">
                                            <th style="display: none;">ID</th>
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Hora Inicio</th>
                                            <th style="color: #fff;">Hora Final</th>
                                            <th style="color: #fff;">Nombre</th>
                                            <th style="color: #fff;">Descripción</th>
                                            <th style="color: #fff;">Unidad</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">QR</th>
                                        </thead>
                                        <tbody class="contenidobusqueda">
                                            @foreach($citas as $cita)
                                                <tr>
                                                    <td>{{$cita->fecha}}</td>
                                                    <td>{{$cita->hora}}</td>
                                                    <td>{{$cita->fin}}</td>
                                                    <td style="display: none;">{{$cita->id}}</td>
                                                    <td>{{$cita->nombre}}</td>
                                                    <td>{{$cita->descripcion}}</td>
                                                    <td>{{$cita->unidad}}</td>
                                                    <td>{{$cita->estatus}}</td>
                                                    <td>
                                                        <a class="btn btn-info" href="{{ route('generarQR_cita', $cita->id)}}" target="_blank">Generar QR</a>
                                                    </td>
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
    </section>
@endsection

<div id="nuevo_usuario" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../public/assets/js/usuarios/usuarios.js"></script>
@endsection