@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Ratificaciones</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped mt-2">
                                        <thead style="background-color: #4A001F;">
                                            <th style="color: #fff;">Folio</th>
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Empresa</th>
                                            <th style="color: #fff;">Teléfono</th>
                                            <th style="color: #fff;">Correo</th>
                                            <th style="color: #fff;">Trabajador</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Concluir</th>
                                            <th style="color: #fff;">Documentos</th>
                                        </thead>
                                        <tbody>
                                            @foreach($solicitudes as $solicitud)
                                                <tr>
                                                    <td>{{$solicitud->id}}</td>
                                                    <td>{{$solicitud->fecha}}</td> 
                                                    <td>@if(is_null($solicitud->nombre_empresa) && is_null($solicitud->primero_empresa) && is_null($solicitud->segundo_empresa))
                                                        {{$solicitud->empresa}}
                                                       @else {{$solicitud->nombre_empresa}} {{$solicitud->primero_empresa}} {{$solicitud->segundo_empresa}}@endif</td>
                                                    <td>{{$solicitud->telefono}}</td>
                                                    <td>{{$solicitud->email}}</td>
                                                    <td>{{$solicitud->trabajador}} {{$solicitud->primero_trabajador}}  {{$solicitud->segundo_trabajador}}</td>
                                                    <td>{{$solicitud->estatus}}</td>
                                                    <td>
                                                        @if($solicitud->estatus == "Confirmado")
                                                            <a class="btn btn-info" href="{{ route('ratificacion_concluir', $solicitud->id) }}">Concluir</a>
                                                        @endif
                                                        @if($solicitud->estatus == "Concluida Pagos")
                                                            <a class="btn btn-info" href="{{ route('ratificacion_pagar', $solicitud->id) }}">Pagar</a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                    @if($solicitud->estatus == "Archivada")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFinteres', $solicitud->id) }}"  target="_blank">Acta de Archivo</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($solicitud->estatus == "Incompetencia")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFincompetencia', $solicitud->id) }}" target="_blank">Incompetencia</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    @elseif($solicitud->estatus == "Comparecencia")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFinteres', $solicitud->id) }}"              target="_blank">Acta de incomparecencia</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>                                                     
                                                    @elseif($solicitud->estatus == "No conciliacion")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFno_conciliacion', $solicitud->id) }}"      target="_blank">Constancia de no conciliación</a></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($solicitud->estatus == "Conciliacion")
                                                        <div class="dropdown">
                                                            <div class="dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerPDFAudiencia', $solicitud->id) }}"  target="_blank">Acta de Audiencia</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFconveniosolicitud', $solicitud->id) }}" target="_blank">Convenio</a></li>
                                                                    <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFcumplimiento', $solicitud->id) }}"  target="_blank">Constancia de cumplimiento</a></li>
                                                                    <li><button type="button" class="btn open-modal" data-bs-toggle="modal" data-bs-target="#documentos" data-id="{{ $solicitud->id }}">Citatorios</button></li>
                                                                </ul>
                                                            </div>
                                                        </div> 
                                                    @elseif($solicitud->estatus == "Concluida")
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button> 
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFaudiencia', $solicitud->id) }}"  target="_blank">Acta de Audiencia</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}" target="_blank">Convenio</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFcumplimientoR', $solicitud->id) }}" target="_blank">Constancia de cumplimiento</a></li>
                                                            </ul>
                                                        </div>
                                                    @elseif($solicitud->estatus == "Concluida Pagos")
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFaudiencia', $solicitud->id) }}"  target="_blank">Acta de Audiencia</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}" target="_blank">Convenio</a></li>
                                                            </ul>
                                                        </div>
                                                    @elseif($solicitud->estatus == "Confirmado")
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFratifi', $solicitud->id) }}"  target="_blank">Acuse</a></li>
                                                            </ul>
                                                        </div>
                                                    @elseif($solicitud->estatus == "Incumplimiento")
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="btn btn-info" style="width: 100%" style="width: 100%" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                <li><a class="btn btn-info" style="width: 100%" href="{{ route('PDFincumplimiento', $solicitud->id) }}"  target="_blank">Incumplimiento</a></li>
                                                            </ul>
                                                        </div>
                                                     @elseif($solicitud->estatus == "Archivada")
                                                        <div class="dropdown">
                                                            <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Documentos
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                                <li><a class="dropdown-item" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"  target="_blank">Identificaciones</a></li>
                                                                <li><a class="btn btn-success" href="{{ route('PDFinteres', $solicitud->id) }}"  target="_blank">Acta de Archivo</a></li>
                                                            </ul>
                                                        </div>
                                                            
                                                    @endif
                                                        
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

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('rechazar_turnos')}}">
        @csrf
        <input type="hidden" id="modal-id" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo de rechazo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea name="observaciones" style="width:100%"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- Modal Documentos -->
{{--<div class="modal fade" id="documentos" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabel">DOCUMENTOS</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <table class="table table-striped" style="width: 100%; text-align: center;">
                <thead style="background-color: #D2D3D5;">
                  <tr>
                    <th>Convenio</th>
                    <th><a class="btn btn-success" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}"  target="_blank">Ver PDF</a></th>
                  </tr>
                  <tr>
                    <th>Acta de audiencia</th>
                    <th><a class="btn btn-success" href="{{ route('PDFaudiencia', $solicitud->id) }}"  target="_blank">Ver PDF</a></th>
                  </tr>
                  <tr>
                    <th>Constancia de cumplimiento</th>
                    <th><a class="btn btn-success" href="{{ route('PDFcumplimiento', $solicitud->id) }}"  target="_blank">Ver PDF</a></th>
                  </tr>
                </thead>
            </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
</div>--}}
<!-- Modal Documentos 2-->
{{--<div class="modal fade" id="documentos2" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabel">DOCUMENTOS</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <table class="table table-striped" style="width: 100%; text-align: center;">
                <thead style="background-color: #D2D3D5;">
                  <tr>
                    <th>Convenio</th>
                    <th><a class="btn btn-success" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}"  target="_blank">Ver PDF</a></th>
                  </tr>
                  <tr>
                    <th>Acta de audiencia</th>
                    <th><a class="btn btn-success" href="{{ route('PDFaudiencia', $solicitud->id) }}"  target="_blank">Ver PDF</a></th>
                  </tr>
                </thead>
            </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
</div>--}}
<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script>
        $('.open-modal').click(function() {
            const id = $(this).data('id'); // Obtiene el valor de data-id
            document.getElementById('modal-id').value = id;
        });
    </script>
    <script src="../public/assets/js/poderes/general.js"></script>
@endsection
