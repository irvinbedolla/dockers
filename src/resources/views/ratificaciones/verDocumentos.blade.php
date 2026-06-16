@extends('layouts.app')


@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Documentos</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                 <table class="table table-striped mt-2">
                                    <thead style="background-color: #4A001F;">
                                        <th style="color: #fff;">Nombre del Documento</th>
                                        <th style="color: #fff;">Documento</th>
                                        <th style="color: #fff;">Acciones</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>CURP del Trabajador</td>
                                            <td>{{$documento_general->trabajador_curp}}</td>
                                            <td><a target='_blank' href="../storage/app/documentos_ratificacion/{{$documento_general->documentoCurp}}">PDF</a><br></td>
                                        </tr>
                                        <tr>
                                            <td>Identificación del Trabajador</td>
                                            <td>{{$documento_general->tipo_identificacion}}</td>
                                            <td><a target='_blank' href="../storage/app/documentos_ratificacion/{{$documento_general->documentoidentificacion}}">PDF</a></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align: center; background-color:#7c7c7b">REPRESENTANTE LEGAL</td>
                                        </tr>
                                        <tr>
                                            <td>Identificación de Citado:{{$documento_abogado->nombres_patronal}}</td>
                                            <td>{{$documento_abogado->ineDocumento}}</td>
                                            <td><a target='_blank' href="../storage/app/documentos_abogados/{{$documento_abogado->ineDocumento}}">PDF</a></td>
                                        </tr>
                                        <tr>
                                            <td>Poder de Citado:{{$documento_abogado->nombres_patronal}}</td>
                                            <td>{{$documento_abogado->representacionDocumento}}</td>
                                            <td><a target='_blank' href="../storage/app/documentos_abogados/{{$documento_abogado->representacionDocumento}}">PDF</a></td>
                                        </tr>
                                            <tr>
                                            <td>Anexo de Citado:{{$documento_abogado->nombres_patronal}}</td>
                                            <td>{{$documento_abogado->cedulaDocumento}}</td>
                                            <td><a target='_blank' href="../storage/app/documentos_abogados/{{$documento_abogado->cedulaDocumento}}">PDF</a></td>
                                        </tr>
                                        <tr>
                                            <td>Anexo de Citado:{{$documento_abogado->nombres_patronal}}</td>
                                            <td>{{$documento_abogado->anexo_documeto}}</td>
                                            <td><a target='_blank' href="../storage/app/documentos_abogados/{{$documento_abogado->anexo_documeto}}">PDF</a></td>
                                        </tr>
                                        
                                        @if(count($documento_subidos) != 0)
                                            <tr>
                                                <td colspan="5" style="text-align: center; background-color:#7c7c7b">DOCUMENTOS CARGADOS</td>
                                            </tr>
                                            @foreach($documento_subidos as $solicitud)
                                            <tr>
                                                <td colspan="4">{{$solicitud->nombre_documento}}</td> 
                                                <td><a target='_blank' href="{{ route('documento_ratificacion_ver', $solicitud->id) }}">PDF</a></td>
                                            </tr>
                                            @endforeach
                                        @endif
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

<div id="nuevo_turno" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>


@section('scripts')
    <script src="../public/assets/js/turnos/turnos.js"></script>
@endsection