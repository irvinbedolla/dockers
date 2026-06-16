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
                                            <th style="color: #fff;">Fecha</th>
                                            <th style="color: #fff;">Empresa/Patrón(a)</th>
                                            <th style="color: #fff;">Trabajador(a)</th>
                                            <th style="color: #fff;">Teléfono</th>
                                            <th style="color: #fff;">Correo</th>
                                            <th style="color: #fff;">Estatus</th>
                                            <th style="color: #fff;">Informe general</th>
                                            <th style="color: #fff;">Documentos</th>
                                        </thead>
                                        <tbody>
                                            @foreach($solicitudes as $solicitud)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d/m/y') }}</td> 
                                                    <td>{{$solicitud->empresa}}</td>
                                                    <td>{{$solicitud->trabajador}}</td>
                                                    <td>{{$solicitud->telefono}}</td>
                                                    <td>{{$solicitud->email}}</td>
                                                    <td>{{$solicitud->estatus}}</td>
                                                    <td><a class="btn btn-primary" href="{{ route('consultar_ratificacion', $solicitud->id) }}" onclick=consultar_estadistica();>Consultar</a></td>
                                                    <td>
                                                        @if($solicitud->estatus === "Confirmado")
                                                            <div class="dropdown  mt-2">
                                                                <button class="btn btn-warning dropdown-toggle load-pdfs" type="button" id="dropdownCitatoriosBtn-{{ $solicitud->id }}" data-id="{{ $solicitud->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownCitatoriosBtn-{{ $solicitud->id }}">
                                                                    <li><a class="btn btn-info" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}"       target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="dropdown-item" href="{{ route('PDFratifi', $solicitud->id) }}" target="_blank">Acuse</a></li>
                                                                    <li class="dropdown-divider"></li>
                                                                </ul>
                                                            </div>
                                                        @elseif($solicitud->estatus === "Concluida")
                                                            <div class="dropdown  mt-2">
                                                                <button class="btn btn-warning dropdown-toggle load-pdfs" type="button" id="dropdownCitatoriosBtn-{{ $solicitud->id }}" data-id="{{ $solicitud->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownCitatoriosBtn-{{ $solicitud->id }}">
                                                                    <li><a class="btn btn-info" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}" target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-success" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}" target="_black">Convenio</a></li>
                                                                    <li><a class="btn btn-success" href="{{ route('PDFcumplimiento', $solicitud->id) }}"  target="_blank">Constancia de cumplimiento</a></li>
                                                                </ul>
                                                            </div>
                                                        @elseif($solicitud->estatus == "Concluida Pagos")
                                                            <div class="dropdown  mt-2">
                                                                <button class="btn btn-warning dropdown-toggle load-pdfs" type="button" id="dropdownCitatoriosBtn-{{ $solicitud->id }}" data-id="{{ $solicitud->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownCitatoriosBtn-{{ $solicitud->id }}">
                                                                    <li><a class="btn btn-info" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}" target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-success" href="{{ route('PDFconvenioratificacion', $solicitud->id) }}" target="_black">Convenio</a></li>
                                                                    <li><a class="btn btn-success" href="{{ route('PDFaudiencia', $solicitud->id) }}"  target="_blank">Acta de audiencia</a></li>
                                                                </ul>
                                                            </div>
                                                        @elseif($solicitud->estatus == "Incumplimiento")
                                                            <div class="dropdown  mt-2">
                                                                <button class="btn btn-warning dropdown-toggle load-pdfs" type="button" id="dropdownCitatoriosBtn-{{ $solicitud->id }}" data-id="{{ $solicitud->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownCitatoriosBtn-{{ $solicitud->id }}">
                                                                    <li><a class="btn btn-info" href="{{ route('VerDocumentosRatificacion', $solicitud->id) }}" target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-success" href="{{ route('PDFincumplimiento', $solicitud->id) }}" target="_black">Incumplimiento</a></li>
                                                                </ul>
                                                            </div>
                                                        @elseif($solicitud->estatus == "Archivada")
                                                            <div class="dropdown  mt-2">
                                                                <button class="btn btn-warning dropdown-toggle load-pdfs" type="button" id="dropdownCitatoriosBtn-{{ $solicitud->id }}" data-id="{{ $solicitud->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    Documentos
                                                                </button>
                                                                <ul class="dropdown-menu" aria-labelledby="dropdownCitatoriosBtn-{{ $solicitud->id }}">
                                                                    <li><a class="btn btn-info" href="{{ route('VerDocumentosAudiencia', $solicitud->id) }}" target="_blank">Documentos Digitales</a></li>
                                                                    <li><a class="btn btn-success" href="{{ route('PDFinteres', $solicitud->id) }}" target="_black">Acta de Archivo</a></li>
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

<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <script src="../public/assets/js/poderes/general.js"></script>
@endsection
