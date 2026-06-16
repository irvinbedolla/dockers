@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Notificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-striped mt-1" style="text-align:center">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;">Expediente</th>
                                        <th style="color: #fff;">Citado</th>
                                        <th style="color: #fff;">Dirección</th>
                                        <th style="color: #fff;">Estatus</th>
                                        <th style="color: #fff;">Medio</th>
                                        <th style="color: #fff;">Tipo de notificación</th>"
                                        <th style="color: #fff;">Editar</th>
                                        <th style="color: #fff;">Documento</th>
                                    </thead>
                                    <tbody>
                                        @foreach($notificaciones as $notificacion)
                                            <tr>
                                                <td>{{$notificacion->NUE}}</td>
                                                <td>{{$notificacion->nombre}} {{$notificacion->primer_apellido}} {{$notificacion->segundo_apellido}}</td>
                                                <td>COLONIA {{$notificacion->colonia}}, {{$notificacion->tipo_vialidad}} {{$notificacion->calle}} #{{$notificacion->n_ext}} 
                                                    @if(!empty($notificacion->n_int))
                                                        INT. {{ $notificacion->n_int }}
                                                    @endif{{mb_strtoupper($notificacion->municipio_citado, 'UTF-8')}}, {{mb_strtoupper($notificacion->estado_citado, 'UTF-8')}}</td>
                                                <td>{{$notificacion->estatus}}</td>
                                                <td>{{$notificacion->notificacion}}</td>
                                                <td>{{$notificacion->tipo_notificacion}}</td>
                                                <td>
                                                    <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('editar_citado_historial')}}">
                                                        @csrf
                                                        <input type="hidden" name="id_solicitud" value="{{ $notificacion->id_solicitud}}">
                                                        <input type="hidden" name="id" value="{{ $notificacion->id}}">
                                                        <button type="submit" class="btn btn-primary">Editar</button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <div class="col-xs-12 col-sm-12 col-md-12"> 
                                                        @if($notificacion->estatus === "Finalizado exitosamente")
                                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">Documentos</button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFRazonNoticacion', [$notificacion->id, $notificacion->id_solicitud]) }}"  target="_blank">Notificación</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFmultaNotificacion', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                            </ul>
                                                        @endif     
                                                        @if($notificacion->estatus === "No notificada" || $notificacion->estatus === "Notificada en Audiencia")
                                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                        </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFInstructivo', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFMultaInstructivo', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                            </ul>
                                                        @endif      
                                                        @if($notificacion->estatus === "No exitosa se constituye")
                                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button> 
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li class="mb-2"><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFNoExitConstituye', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFMultaNoExitConstituye', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                            </ul>
                                                        @endif                                      
                                                        @if($notificacion->estatus === "No exitosa no se constituye")
                                                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button> 
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li class="mb-2"><a class="btn btn-info" style="width: 100%" href="{{ route('PDFnotificadoNoexitosaNS', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Notificación</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFMultaNoExitConstituye', [$notificacion->id, $notificacion->id_solicitud]) }}" target="_blank">Multa</a></li>
                                                            </ul>
                                                        @endif 
                                                    </div> 
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
        
        <div id="menu_carga" style ="display: none;">
            <div>.</div>
            <div class="loader"></div>
        </div>
        
@section('scripts')
    <script src="../public/assets/js/estadistica/estadistica.js"></script>
@endsection
        
    </section>
    
    
@endsection




