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
                                            @if ($documento_general->tipo_solicitud == 1)
                                                <td>Identificación de Solicitante</td>
                                                <td>{{$documento_solicitante->documentoIdentificacion}}</td>
                                                <td><a target='_blank' href="../storage/app/documentosSolicitud/{{$documento_solicitante->documentoIdentificacion}}">PDF</a></td>
                                            @elseif ($documento_general->tipo_solicitud == 2)
                                                <td>Identificación del representante del Solicitante</td>
                                                <td>{{$documento_solicitante->documentoIdentificacion}}</td>
                                                <td><a target='_blank' href="../storage/app/documentos_abogados/{{$documento_solicitante->poder->ineDocumento}}">PDF</a></td>
                                            @endif
                                        </tr>
                                        @if(isset($documentos_comparecencia) && count($documentos_comparecencia) > 0)
                                            @foreach($documentos_comparecencia as $doc_citado)
                                            <tr>
                                                <td>Identificación de Citado: {{$doc_citado->nombre}} {{$doc_citado->primer_apellido}} {{$doc_citado->segundo_apellido}}</td>
                                                <td>{{ basename($doc_citado->identificacion_comparecencia) }}</td>
                                                <td><a target='_blank' href="../storage/app/documentosSolicitud/{{$doc_citado->identificacion_comparecencia}}">PDF</a></td>
                                            </tr>
                                            @endforeach
                                        @endif
                                        @if(isset($documento_abogado))
                                            @if(count($documento_abogado) != 0)
                                                @foreach($documento_abogado as $documento)
                                                    <tr>
                                                        <td colspan="3" style="text-align: center; background-color:#7c7c7b">REPRESENTANTE LEGAL</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Identificación de Citado:{{$documento->nombres_patronal}} {{$documento->primer_apellido_patronal}} {{$documento->segundo_apellido_patronal}}</td>
                                                        <td>{{$documento->ineDocumento}}</td>
                                                        <td><a target='_blank' href="../storage/app/documentosSolicitud/{{$documento->ineDocumento}}">PDF</a></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Poder de Citado:{{$documento->nombres_patronal}} {{$documento->primer_apellido_patronal}} {{$documento->segundo_apellido_patronal}}</td>
                                                        <td>{{$documento->representacionDocumento}}</td>
                                                        <td><a target='_blank' href="../storage/app/documentosSolicitud/{{$documento->representacionDocumento}}">PDF</a></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Cedula:{{$documento->nombres_patronal}} {{$documento->primer_apellido_patronal}} {{$documento->segundo_apellido_patronal}}</td>
                                                        <td>{{$documento->cedulaDocumento}}</td>
                                                        <td><a target='_blank' href="../storage/app/documentosSolicitud/{{$documento->cedulaDocumento}}">PDF</a></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Anexo:{{$documento->nombres_patronal}} {{$documento->primer_apellido_patronal}} {{$documento->segundo_apellido_patronal}}</td>
                                                        <td>{{$documento->anexo_documeto}}</td>
                                                        <td><a target='_blank' href="../storage/app/documentosSolicitud/{{$documento->anexo_documeto}}">PDF</a></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endif
                                        @if(isset($documento_fisica))
                                            @if(count($documento_fisica) != 0)
                                                @foreach($documento_fisica as $documento)
                                                    <tr>
                                                        <td colspan="3" style="text-align: center; background-color:#7c7c7b">PERSONA FISICA</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Identificación de Citado(Persona Fisica)</td>
                                                        <td>{{$documento->documentoIdentificacion}}</td>
                                                        <td><a target='_blank' href="../storage/app/documentosSolicitud/{{$documento->documentoIdentificacion}}">PDF</a></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endif
                                        @if(isset($documento_subidos))
                                            @if(count($documento_subidos) != 0)
                                                <tr>
                                                    <td colspan="5" style="text-align: center; background-color:#7c7c7b">DOCUMENTOS CARGADOS</td>
                                                </tr>
                                                @foreach($documento_subidos as $solicitud)
                                                    
                                                    <tr>
                                                        <td colspan="4">{{$solicitud->nombre_documento}}</td> 
                                                        <td><a target='_blank' href="{{ route('documento_solicitud_ver', $solicitud->id) }}">PDF</a></td>
                                                    </tr>
                                                @endforeach
                                            @endif
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