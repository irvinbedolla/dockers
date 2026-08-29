@extends('layouts.app')


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
                                                            <li><a class="btn btn-info" style="width: 100%" href="{{route('VerPDFCasosPrevistos' , $recepcion->id) }}"  target="_blank">Atención para casos previstos</a></li>
                                                            <li><a class="btn btn-info" style="width: 100%" href="{{route('VerPDFCanalizacion' , $recepcion->id) }}"  target="_blank">Canalización</a></li>
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

@endsection