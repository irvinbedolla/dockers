@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Tarjeta Informativa</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table-striped" style="width:100%">
                                    <thead style="background-color: #4A001F;">
                                        <th style="display: none;">ID</th>
                                        <th style="color: #fff;">Nombre</th>
                                        <th style="color: #fff;">Grupo Vulnerable</th>
                                        <th style="color: #fff;">Tipo</th>
                                        <th style="color: #fff;">Resolición</th>
                                        <th style="color: #fff;">Motivo</th>
                                        <th style="color: #fff;">Acciones</th>
                                    </thead>
                                    <tbody class="contenidobusqueda">
                                        @foreach($misturnos as $turno)
                                            <tr>
                                                <td style="display: none;">{{$turno->id}}</td>
                                                <td>{{$turno->solicitante}}</td>
                                                <td>{{$turno->vulnerables}}</td>
                                                <td>{{$turno->tipo_caso}}</td>
                                                <td>{{$turno->resultado}}</td>
                                                <td>{{$turno->motivo}}</td>
                                                <td><a class="btn btn-info"   href="{{ route('llenar_tarjeta', $turno->id) }}" onclick=disponibles();>Agregar</a></td>
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