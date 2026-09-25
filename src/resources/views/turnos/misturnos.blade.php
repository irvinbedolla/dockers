@extends('layouts.app')
@section('title', 'Mis turnos')


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
                            <a class="btn btn-primary btn-sm" href="{{ route('misturnos') }}"  onclick=crear_turnos();>Cargar turnos</a>
                            <div class="table-responsive">
                                <table id="example" class="table table-striped mt-2">
                                    <thead style="background-color: #354647;">
                                        <th class="text-center" style="color: #fff;">Folio</th>
                                        <th class="text-center" style="color: #fff;">Hora</th>
                                        <th class="text-center" style="color: #fff;">Tramite</th>
                                        <th class="text-center" style="color: #fff;">Nombre</th>
                                        <th class="text-center" style="color: #fff;">Estatus</th>
                                        <th class="text-center" style="color: #fff; width: 15%;">Acciones</th>
                                    </thead>
                                    <tbody>
                                        @foreach($misturnos as $turnos)
                                            <tr>
                                                <td class="text-center">{{str_pad($turnos->consecutivo, 5, '0', STR_PAD_LEFT)}}</td>
                                                <td class="text-center">{{$turnos->hora->format('H:i')}}</td>
                                                <td class="text-center">@if($turnos->exepcion === 'Si')Caso de excepcion/@endif{{$turnos->tipo}}</td>
                                                <td class="text-center">{{$turnos->solicitante}}</td>
                                                <td class="text-center">
                                                    @if($turnos->estatus === 'atendido')
                                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                                    @elseif($turnos->estatus === 'confirmada')
                                                        <span class="badge bg-info rounded-pill px-3 py-2">
                                                    @elseif($turnos->estatus === 'expirada')
                                                        <span class="badge bg-danger rounded-pill px-3 py-2">
                                                    @else
                                                        <span class="badge bg-warning rounded-pill px-3 py-2">
                                                        
                                                    @endif
                                                    {{$turnos->estatus}}</span>
                                                    
                                                </td>
                                                <td class="text-center">
                                                    @if($turnos->estatus == "confirmada")
                                                        @if($turnos->exepcion == "No")
                                                            <a class="btn btn-info btn-sm" href="{{ route('turnos.terminado', $turnos->id) }}" onclick=no_disponible();><i class="bi bi-check2-square"></i> Atendido</a>
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