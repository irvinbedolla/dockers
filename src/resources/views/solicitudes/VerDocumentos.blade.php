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
                                        @php
                                            $solicitudId = $documento_general->id;

                                            // Resuelve URL para documentosSolicitud con fallback a ruta plana
                                            $urlSolicitud = function(?string $filename) use ($solicitudId): ?string {
                                                if (!$filename || $filename === 'Sin documento') return null;
                                                if (\Storage::exists("documentosSolicitud/{$solicitudId}/{$filename}"))
                                                    return asset("../storage/app/documentosSolicitud/{$solicitudId}/{$filename}");
                                                if (\Storage::exists("documentosSolicitud/{$filename}"))
                                                    return asset("../storage/app/documentosSolicitud/{$filename}");
                                                return null;
                                            };

                                            // Resuelve URL para documentos_abogados con fallback a ruta plana
                                            $urlAbogado = function(?string $filename, $idAbogado): ?string {
                                                if (!$filename || $filename === 'Sin documento') return null;
                                                if (\Storage::exists("documentos_abogados/{$idAbogado}/{$filename}"))
                                                    return asset("../storage/app/documentos_abogados/{$idAbogado}/{$filename}");
                                                if (\Storage::exists("documentos_abogados/{$filename}"))
                                                    return asset("../storage/app/documentos_abogados/{$filename}");
                                                return null;
                                            };
                                        @endphp

                                        {{-- Identificación del solicitante --}}
                                        <tr>
                                            @if ($documento_general->tipo_solicitud == 1)
                                                @php $urlId = $urlSolicitud($documento_solicitante->documentoIdentificacion); @endphp
                                                <td>Identificación de Solicitante</td>
                                                <td>{{$documento_solicitante->documentoIdentificacion}}</td>
                                                <td>
                                                    @if($urlId)
                                                        <a target='_blank' href="{{ $urlId }}">PDF</a>
                                                    @else
                                                        <span class="text-muted">No disponible</span>
                                                    @endif
                                                </td>
                                            @elseif ($documento_general->tipo_solicitud == 2)
                                                @php
                                                    $idAbogadoSol = $documento_solicitante->poder->idAbogado ?? null;
                                                    $urlIdRep = $idAbogadoSol
                                                        ? $urlAbogado($documento_solicitante->poder->ineDocumento, $idAbogadoSol)
                                                        : null;
                                                @endphp
                                                <td>Identificación del representante del Solicitante</td>
                                                <td>{{$documento_solicitante->poder->ineDocumento ?? ''}}</td>
                                                <td>
                                                    @if($urlIdRep)
                                                        <a target='_blank' href="{{ $urlIdRep }}">PDF</a>
                                                    @else
                                                        <span class="text-muted">No disponible</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>

                                        {{-- Identificación de comparecencia (citados patronales) --}}
                                        @if(isset($documentos_comparecencia) && count($documentos_comparecencia) > 0)
                                            @foreach($documentos_comparecencia as $doc_citado)
                                                @php $urlComp = $urlSolicitud($doc_citado->identificacion_comparecencia); @endphp
                                                <tr>
                                                    <td>Identificación de Citado: {{$doc_citado->nombre}} {{$doc_citado->primer_apellido}} {{$doc_citado->segundo_apellido}}</td>
                                                    <td>{{ basename($doc_citado->identificacion_comparecencia) }}</td>
                                                    <td>
                                                        @if($urlComp)
                                                            <a target='_blank' href="{{ $urlComp }}">PDF</a>
                                                        @else
                                                            <span class="text-muted">No disponible</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        {{-- Documentos del representante legal (Poder) --}}
                                        @if(isset($documento_abogado))
                                            @if(count($documento_abogado) != 0)
                                                @foreach($documento_abogado as $documento)
                                                    @php $idAbogadoDoc = $documento->idAbogado ?? null; @endphp
                                                    <tr>
                                                        <td colspan="3" style="text-align: center; background-color:#7c7c7b">REPRESENTANTE LEGAL</td>
                                                    </tr>
                                                    <tr>
                                                        @php $urlIne = $idAbogadoDoc ? $urlAbogado($documento->ineDocumento, $idAbogadoDoc) : null; @endphp
                                                        <td>Identificación de Citado:{{$documento->nombres_patronal}} {{$documento->primer_apellido_patronal}} {{$documento->segundo_apellido_patronal}}</td>
                                                        <td>{{$documento->ineDocumento}}</td>
                                                        <td>
                                                            @if($urlIne) <a target='_blank' href="{{ $urlIne }}">PDF</a>
                                                            @else <span class="text-muted">No disponible</span> @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        @php $urlRep = $idAbogadoDoc ? $urlAbogado($documento->representacionDocumento, $idAbogadoDoc) : null; @endphp
                                                        <td>Poder de Citado:{{$documento->nombres_patronal}} {{$documento->primer_apellido_patronal}} {{$documento->segundo_apellido_patronal}}</td>
                                                        <td>{{$documento->representacionDocumento}}</td>
                                                        <td>
                                                            @if($urlRep) <a target='_blank' href="{{ $urlRep }}">PDF</a>
                                                            @else <span class="text-muted">No disponible</span> @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        @php $urlCed = $idAbogadoDoc ? $urlAbogado($documento->cedulaDocumento, $idAbogadoDoc) : null; @endphp
                                                        <td>Cedula:{{$documento->nombres_patronal}} {{$documento->primer_apellido_patronal}} {{$documento->segundo_apellido_patronal}}</td>
                                                        <td>{{$documento->cedulaDocumento}}</td>
                                                        <td>
                                                            @if($urlCed) <a target='_blank' href="{{ $urlCed }}">PDF</a>
                                                            @else <span class="text-muted">No disponible</span> @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        @php $urlAnexo = $idAbogadoDoc ? $urlAbogado($documento->anexo_documeto, $idAbogadoDoc) : null; @endphp
                                                        <td>Anexo:{{$documento->nombres_patronal}} {{$documento->primer_apellido_patronal}} {{$documento->segundo_apellido_patronal}}</td>
                                                        <td>{{$documento->anexo_documeto}}</td>
                                                        <td>
                                                            @if($urlAnexo) <a target='_blank' href="{{ $urlAnexo }}">PDF</a>
                                                            @else <span class="text-muted">No disponible</span> @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endif

                                        {{-- Identificación de persona física --}}
                                        @if(isset($documento_fisica))
                                            @if(count($documento_fisica) != 0)
                                                <tr>
                                                    <td colspan="3" style="text-align: center; background-color:#7c7c7b">PERSONA FISICA</td>
                                                </tr>
                                                @foreach($documento_fisica as $documento)
                                                    @php $urlFis = $urlSolicitud($documento->documentoIdentificacion); @endphp
                                                    <tr>
                                                        <td>Identificación de Citado(Persona Fisica)</td>
                                                        <td>{{$documento->documentoIdentificacion}}</td>
                                                        <td>
                                                            @if($urlFis) <a target='_blank' href="{{ $urlFis }}">PDF</a>
                                                            @else <span class="text-muted">No disponible</span> @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @endif

                                        {{-- Documentos cargados manualmente --}}
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