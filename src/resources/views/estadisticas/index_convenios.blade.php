@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">SEER - Pagos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @can('crear-seer')
                                <a class="btn btn-warning" href="{{ route('convenios_agregar') }}"    onclick=nuevo_estadistica();>Agregar</a>
                                <a class="btn btn-info" href="{{ route('seer') }}"                    onclick=nuevo_estadistica();>Regresar</a>
                                @if($userRole[0] == "Conciliador" || $userRole[0] == "Auxiliar")
                                        <table id="tabla_seer_auxiliar" class="table table-striped mt-1">
                                            <div class="table-responsive">
                                                <thead style="background-color: #4A001F;">
                                                    <th style="display: none;">ID</th>
                                                    <th style="color: #fff;">Fecha</th>
                                                    <th style="color: #fff;">Número unico de identificación</th>
                                                    <th style="color: #fff;">Monto</th>
                                                    <th style="color: #fff;">Tipo de pago</th>
                                                </thead>
                                                <tbody>
                                                    @foreach($convenios as $convenio)
                                                        <tr>
                                                            <td style="display: none;">{{$convenio->id}}</td>
                                                            <td>{{$convenio->fecha}}</td>
                                                            <td>{{$convenio->NUE}}</td>
                                                            <td>${{number_format($convenio->monto,2)}}</td>
                                                            <td>{{$convenio->tipo_pago}}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </div>
                                        </table>
                                    @endif
                                <!-- Centramos la paginación a la derecha-->
                                <div class="pagination justify-content-end">
                                    </div>                        
                                </div>
                            @endcan
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
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
@endsection