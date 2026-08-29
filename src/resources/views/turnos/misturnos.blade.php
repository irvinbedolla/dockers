@extends('layouts.app')


@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Mis turnos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <a class="btn btn-warning" href="{{ route('misturnos') }}"  onclick=crear_turnos();>Cargar turnos</a>
                            <div class="table-responsive">
                                <table id="example" class="table table-striped mt-2">
                                    <thead style="background-color: #354647;">
                                        <th style="color: #fff;">ID</th>
                                        <th style="color: #fff;">Nombre</th>
                                        <th style="color: #fff;">Estatus</th>
                                        <th style="color: #fff;">Acciones</th>
                                    </thead>
                                    <tbody>
                                        @foreach($misturnos as $turnos)
                                            <tr>
                                                <td>{{$turnos->id}}</td>
                                                <td>{{$turnos->solicitante}}</td>
                                                <td>{{$turnos->estatus}}</td>
                                                <td>
                                                    @if($turnos->estatus == "no atendido")
                                                        @if($turnos->exepcion == "Si")
                                                            <a class="btn btn-info"     href="{{ route('turnos.terminado_revisar', $turnos->id) }}" onclick=no_disponible();>Terminado</a>
                                                        @else
                                                            <a class="btn btn-warning"  href="{{ route('turnos.cambioexcepcion', $turnos->id) }}" onclick=no_disponible();>Caso Excepción</a>
                                                            <a class="btn btn-info"     href="{{ route('turnos.terminado', $turnos->id) }}" onclick=no_disponible();>Terminado</a>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('body_end')
<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush


@section('scripts')
    <script src="../public/js/turnos/turnos.js"></script>
@endsection