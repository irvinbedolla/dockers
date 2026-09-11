@extends('layouts.app')
@section('title', 'Caso de Excepción')


@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Caso de Excepción</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            
                            {{-- Sin .menu-visible: esa clase pone overflow:visible y anula
                                 el scroll, asi que la tabla se desbordaba. Se puede
                                 quitar porque el desplegable de la columna de acciones
                                 se posiciona con Popper en estrategia 'fixed'. --}}
                            <div class="table-responsive">
                                <table id="example" class="table table-striped mt-2">
                                    <thead style="background-color: #354647;">
                                        <th style="color: #fff;">ID</th>
                                        <th style="color: #fff;">Nombre</th>
                                        <th style="color: #fff;">Tipo de Caso</th>
                                        <th style="color: #fff;">Grupos Vulnerable</th>
                                        <th style="color: #fff;">Delegación</th>
                                        <th style="color: #fff;"></th>
                                    </thead>
                                    <tbody>
                                        @foreach($recepciones as $recepcion)
                                            <tr>
                                                <td>{{$recepcion->id}}</td>
                                                <td>{{$recepcion->solicitante}}</td>
                                                <td>{{$recepcion->tipo_caso}}</td>
                                                <td>{{$recepcion->vulnerables}}</td>
                                                <td>{{$recepcion->delegacion}}</td>
                                                <td>
                                                @if($recepcion->estatus === 'atendido')
                                                
                                                    <div class="dropdown">
                                                        <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-file-earmark-text-fill"></i> Documentos
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                            @can('casos_excepcion_atencion')<li><a class="btn btn-info" style="width: 100%" href="{{route('VerPDFCasosPrevistos' , $recepcion->id) }}"  target="_blank">Atención para casos previstos</a></li>@endcan
                                                            @can('casos_excepcion_canalizacion')<li><a class="btn btn-info" style="width: 100%" href="{{route('VerPDFCanalizacion' , $recepcion->id) }}"  target="_blank">Canalización</a></li>@endcan
                                                        </ul>
                                                    </div>
                                                        
                                                @else
                                                    <a class="btn btn-warning btn-sm" href="{{ route('atender_excepcion' , $recepcion->id)}}"  onclick=crear_turnos();><i class="bi bi-play-fill"></i> Atender</a>
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

    <script src="{{ asset('assets/js/poderes/general.js') }}"></script>

    <script>
        // El desplegable de la tabla queda dentro del contenedor que ahora hace
        // scroll y este lo recortaria. Con la estrategia 'fixed' de Popper el menu
        // se posiciona contra el viewport y se escapa del recorte.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('#example [data-bs-toggle="dropdown"]').forEach(function (boton) {
                bootstrap.Dropdown.getOrCreateInstance(boton, {
                    popperConfig: function (config) {
                        return Object.assign({}, config, { strategy: 'fixed' });
                    }
                });
            });
        });
    </script>

@endsection