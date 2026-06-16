@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
    $contador = 0;
@endphp
@section('page_css')
<style>
    .btn-invisible {
        display: none;
    }
    #modalCitados table.table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.08); /* ajusta el último valor (0.08) para más o menos opacidad */
    }
</style>
@endsection
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Audiencia Patronal Iniciada</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <!--
                            <div class="card p-4 shadow-sm">
                                <h5 class="text-muted">Tiempo Restante:</h5>
                                <h1 id="temporizador" class="text-danger font-weight-bold">
                                    --:-- 
                                </h1>
                                <p id="mensaje-estado" class="mt-2"></p>
                            </div>
                            -->
                            <!--a href="" type="button" class="btn btn-info">
                                Actualizar representantes
                            </!--a-->

                            @if ($allCentro == 1)
                            <button type="button" class="btn btn-info open-modal" data-bs-toggle="modal" data-bs-target="#ModalReagendar" data-id="{{ $id }}">
                                Reagendar
                            </button>
                            @endif

                            @php
                                $totalCentroTop = 0;
                                $totalCentroSinComparecenciaTop = 0;
                                foreach ($citados as $c) {
                                    if (($c->notificacion ?? null) === 'Centro') {
                                        $totalCentroTop++;
                                        $tieneComparecenciaTop = ($c->comparecencia == 'Si');
                                        if (!$tieneComparecenciaTop) {
                                            $totalCentroSinComparecenciaTop++;
                                        }
                                    }
                                }
                                $mostrarEmitirMultasTop = ($totalCentroTop >= 1 && $totalCentroSinComparecenciaTop === $totalCentroTop);
                            @endphp

                            <button type="button" class="btn btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#ModalArchivar" data-id="{{ $id }}">
                                Archivar
                            </button>
                            <button type="button" class="btn btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#ModalIncopentencia" data-id="{{ $id }}">
                                Incompetencia
                            </button>
                            <button type="button" class="btn btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#ModalDesistimiento" data-id="{{ $id }}">
                                Desistimiento
                            </button>

                            @if($mostrarEmitirMultasTop)
                                <button type="button" class="btn btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#ModalEmitirMultas" data-id="{{ $id }}">
                                    Emitir Constancias de No Conciliación
                                </button>
                            @endif
                            <div class="table-responsive">
                                <table class="table table-striped mt-1">
                                    <thead style="background-color: #4A001F;">
                                        <tr> 
                                            <th style="display:none">ID</th>
                                            <th style="color: #ffff;">Tipo parte</th>
                                            <th style="color: #ffff;">Nombre de la parte</th>
                                            <th style="color: #ffff;">Representante legal</th>
                                            <th style="color: #ffff;">Notificación</th>
                                            <th style="color: #ffff;">Estatus Notificación</th>
                                            <th style="color: #ffff;">Acciones</th>
                                            <th style="color: #ffff;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="display:none">{{$solicitante->id}}</td>
                                            <td style="color: #000000;"><b>Solicitante</b></td>
                                            <td>{{ $solicitante->nombre }}</td>
                                            <td>@if($audiencia->poder->reprecentante == 'Si') {{ $audiencia->poder->nombre_representante }} {{ $audiencia->poder->primer_apellido_representante ?? ''}} {{ $audiencia->poder->segundo_apellido_representante ?? ''}} @else Sin representante legal @endif</td>
                                            <td></td>
                                            <td></td>
                                            <td>
                                                

                                                <div class="d-flex flex-column gap-1">
                                                    <a type="button" class="btn btn-warning w-100 open-modal mt-1" data-bs-toggle="modal" data-bs-target="#exampleModal1" data-id="{{ $id }}">
                                                        Editar
                                                    </a>

                                                    <button type="button" class="btn btn-info w-100 mt-1 mb-1" data-bs-toggle="modal" data-bs-target="#ModalSeleccionarRepresentante">
                                                        Seleccionar representante
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn-invisible">Oculto</button>
                                            </td>
                                        </tr>
                                       
                                        @foreach($citados as $citado)
                                            <tr>
                                                <td  style="display:none">{{$citado->id}}</td>
                                                <td style="color: #000000;"><b>Citado</b></td>
                                                <td>{{$citado->nombre}} {{$citado->primer_apellido}} {{$citado->segundo_apellido}}</td>
                                                <td></td>
                                                <td>
                                                    @if ($citado->notificacion == 'Trabajador') 
                                                        Solicitante
                                                    @else
                                                        {{ $citado->notificacion }}
                                                    @endif
                                                </td>
                                                <td>{{ $citado->estatus }}</td>
                                                <td>
                                                    @if($citado->comparecencia == null || $citado->comparecencia == 'No')
                                                        <button type="button" class="btn btn-primary w-100 mt-1 mb-1 text-nowrap btn-abrir-modal-comparecencia" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#ModalRegistrarComparecencia"
                                                            data-id="{{ $citado->id }}"
                                                            data-solicitud="{{ $solicitud->id }}"
                                                            data-audiencia="{{ request()->query('audiencia_id') }}"
                                                            data-tipo="{{ $citado->tipo_identificacion_comparecencia }}"
                                                            data-num="{{ $citado->num_identificacion_comparecencia }}"
                                                            data-doc="{{ $citado->identificacion_comparecencia }}">
                                                            Registrar Comparecencia
                                                        </button>
                                                    @else
                                                        <form action="{{ route('representante.quitar') }}" method="POST" class="mt-1">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $citado->id }}">
                                                            <input type="hidden" name="solicitud" value="{{$solicitud->id}}">
                                                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                                                Quitar Comparecencia
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                                <td></td>
                                            </tr>
                                            @php $contador++; @endphp
                                        @endforeach       
                                    </tbody> 
                                </table>
                            </div>
                            <a type="button" class="btn btn-success open-modal" data-bs-toggle="modal" data-bs-target="#ModalTerminar" data-id="{{ $id }}">Continuar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<!-- Modal Registrar Comparecencia -->
<div class="modal fade" id="ModalRegistrarComparecencia" tabindex="-1" aria-hidden="true">
    <!-- El "action" se apunta a un método POST nuevo en el controlador que guarde o actualice la identificación -->
    <form class='needs-validation novalidate' method='POST' action="{{ route('guardar_comparecencia_citado') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" id="comp_citado_id" value="">
        <input type="hidden" name="solicitud" id="comp_solicitud_id" value="">
        <input type="hidden" name="audiencia_id" id="comp_audiencia_id" value="">
        
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Comparecencia del Citado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_identificacion_comparecencia">Tipo de Identificación <span style="color:red;">(*)</span></label>
                            <select class="form-select" name="tipo_identificacion_comparecencia" id="comp_tipo_identificacion" required>
                                <option value="">Seleccione...</option>
                                <option value="Credencial de elector">Credencial de elector</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Cédula profesional">Cédula profesional</option>
                                <option value="Licencia de conducir">Licencia de conducir</option>
                                <option value="Credencial de inapam">Credencial de INAPAM</option>
                                <option value="Cartilla militar">Cartilla militar</option>
                                <option value="Documento migratorio">Documento migratorio</option>
                                <option value="Constancia de identidad">Constancia de identidad</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione el tipo de identificación.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="num_identificacion_comparecencia">Número de Identificación <span style="color:red;">(*)</span></label>
                            <input type="text" class="form-control" name="num_identificacion_comparecencia" id="comp_num_identificacion" oninput="this.value = this.value.toUpperCase();" required>
                            <div class="invalid-feedback">Por favor ingrese el número de identificación.</div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="identificacion_comparecencia">Documento de Identificación (PDF)</label>
                            <input type="file" class="form-control" name="identificacion_comparecencia" id="comp_doc_input" accept=".pdf">
                            
                            <!-- Botón para ver documento si ya existe -->
                            <div id="comp_doc_existente_container" class="mt-2" style="display: none;">
                                <a id="comp_btn_ver_doc" href="#" target="_blank" class="btn btn-sm btn-info">Ver Documento Actual</a>
                                <small class="text-muted ms-2">Si subes un nuevo archivo, se reemplazará el actual.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar y Registrar Comparecencia</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Solicitantes -->
<div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('editar_solicitud')}}">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Solicitante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-8">
                            <div class="form-group">
                                <label for="name">Nombre(s) y Apellidos del Solicitante<span style="color:red;"> (*)</span></label>
                                <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitante["nombre"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo nombre es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">CURP del Solicitante<span style="color:red;"> (*)</span></label>
                                <input type="text" name="curp" id="curp_input" oninput="validarInput(this)"class="form-control" value="<?=$solicitante["curp"];?>" required> 
                                <pre id="resultado"></pre>
                                <div class="invalid-feedback">
                                    El campo curp es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">RFC del Solicitante</label>
                                <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitante["rfc"];?>"> 
                                <div class="invalid-feedback">
                                    El campo RFC es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Número de Seguro Social</label>
                                <input type="text" name="seguro" minlength="11" maxlength="12" class="form-control" value="<?=$solicitante["nss"];?>"> 
                                <div class="invalid-feedback">
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Puesto<span style="color:red;"> (*)</span></label>
                                <input type="text" class="form-control" name="puesto" value="<?=$solicitante["puesto"];?>" oninput="this.value = this.value.toUpperCase()" required> 
                                <div class="invalid-feedback">
                                    El campo puesto es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Frecuencia de Pago<span style="color:red;"> (*)</span></label>
                                <select name="periodo_pago" class="form-control" value="<?=$solicitante["periodo_pago"];?>" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Diario" {{ $solicitante['periodo_pago'] == 'Diario' ? "selected" : '' }}>DIARIO</option>
                                    <option value="Semanal" {{ $solicitante['periodo_pago'] == 'Semanal' ? "selected" : '' }}>SEMANAL</option>
                                    <option value="Quincenal" {{ $solicitante['periodo_pago'] == 'Quincenal' ? "selected" : '' }}>QUINCENAL</option>
                                    <option value="Mensual" {{ $solicitante['periodo_pago'] == 'Mensual' ? "selected" : '' }}>MENSUAL</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo frecuencia de pago es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Salario<span style="color:red;"> (*)</span></label>
                                <input type="text" name="pago" class="form-control" value="<?=$solicitante["pago"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo salario es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Cantidad total de horas trabajadas por semana<span style="color:red;"> (*)</span></label>
                                <input type="number" name="horas" class="form-control" value="<?=$solicitante["horas_semana"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo cantidad de horas trabajadas es obligatorio.
                                </div>
                            </div>
                        </div>
                        <!--div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="password">¿Laboras actualmente?</label>
                                <input type="text" class="form-control" name="labora" value="<?=$solicitante["labora"];?>">   
                            </div>  
                        </div-->    
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Fecha de Ingreso<span style="color:red;"> (*)</span></label>
                                <input type="date" name="fecha_ingreso" class="form-control" value="<?=$solicitante["fecha_ingreso"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo fecha de ingreso es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Jornada<span style="color:red;"> (*)</span></label>
                                <input name="jornada" class="form-control" value="<?=$solicitante["jornada"];?>" required>
                                   {{-- <option value="">SELECCIONE</option>
                                    <option value="Diurna" {{ $solicitante['jornada'] == 'Diurna' ? "selected" : '' }}>DIURNA</option>
                                    <option value="Nocturna" {{ $solicitante['jornada'] == 'Nocturna' ? "selected" : '' }}>NOCTURNA</option>
                                    <option value="Mixta" {{ $solicitante['jornada'] == 'Mixta' ? "selected" : '' }}>MIXTA</option>
                                </select>--}}
                                <div class="invalid-feedback">
                                    El campo jornada laboral es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4" id="fecha_fin">
                            <div class="form-group">
                                <label for="name">Fecha de Salida</label>
                                <input type="date" name="fecha_salida" class="form-control" value="<?=$solicitante["fecha_salida"];?>"> 
                                <div class="invalid-feedback">
                                    El campo fecha de salida es obligatoria.
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal Buscar Representantes (Poder) -->
<div class="modal fade" id="ModalSeleccionarRepresentante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Representante Legal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                    $nP = $audiencia->poder->nombres_patronal ?? '';
                    $pP = $audiencia->poder->primer_apellido_patronal ?? '';
                    $sP = $audiencia->poder->segundo_apellido_patronal ?? '';
                    // Buscar coincidencias
                    $abogadosMatch = \App\Models\Poder::where('nombres_patronal', $nP)
                        ->where('primer_apellido_patronal', $pP)
                        ->where('segundo_apellido_patronal', $sP)
                        ->get();
                @endphp
                
                @if($abogadosMatch->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover mt-3">
                        <thead style="background-color: #4A001F;">
                            <tr>
                                <th style="color: #ffffff;">Folio</th>
                                <th style="color: #ffffff;">Nombre de la patronal</th>
                                <th style="color: #ffffff;">Nombre de representante</th>
                                <th style="color: #ffffff;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($abogadosMatch as $abogadoRow)
                            <tr>
                                <td>{{ $abogadoRow->idAbogado }}</td>
                                <td>{{ trim($abogadoRow->nombres_patronal . ' ' . $abogadoRow->primer_apellido_patronal . ' ' . $abogadoRow->segundo_apellido_patronal) }}</td> 
                                <td>
                                    @if(trim($abogadoRow->nombre_representante . $abogadoRow->primer_apellido_representante . $abogadoRow->segundo_apellido_representante) !== '')
                                        {{ trim($abogadoRow->nombre_representante . ' ' . $abogadoRow->primer_apellido_representante . ' ' . $abogadoRow->segundo_apellido_representante) }}
                                    @else
                                        Registro patronal sin representación
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('seleccionar_representante_patronal') }}">
                                        @csrf
                                        <input type="hidden" name="solicitud" value="{{ $id }}">
                                        <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
                                        <input type="hidden" name="abogado" value="{{ $abogadoRow->idAbogado }}">
                                        <input type="hidden" name="citado" value="{{ $solicitante->id }}">
                                        <input type="hidden" name="id" value="{{ $solicitante->id }}"> 
                                        <button type="submit" class="btn btn-success">Seleccionar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="alert alert-warning mt-3">No se encontraron representantes registrados para {{ trim($nP.' '.$pP.' '.$sP) }}.</div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Citados -->
<div class="modal fade" id="modalAgregarCitados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST'  enctype="multipart/form-data" name="AgregarRepresentante" id="AgregarRepresentante" action="{{route('insertar_citado')}}">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="id_citado_2" id="id_citado_2" value="">
        <input type="hidden" name="NUE" id="NUE" value={{ $NUE }}>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Representante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Tipo de persona <span style="color:red;">(*)</span></label>
                                                <select name="tipoPersona" id="tipo_persona" class="form-control" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="Fisica">Física</option>
                                                    <option value="Moral">Moral</option>
                                                </select>
                                                <div class="invalid-feedback">
                                                    El tipo de persona es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2">
                                            <a href="{{ route('cancelar_edicion', ['id' => $id, 'redirect_to' => 'publico']) }}" class="btn btn-primary" style=" background-color:#CEA845; border-color: #CEA845">Regresar</a>    
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-sm-12 col-md-12" id="persona_fisica" style="display:none;">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center" style="color:#CEA845">Información Patronal</h4>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h5 class="text-center">Datos de identificación</h5>
                                            </div>
                                        </div>
                                        <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) del Empleador(a) <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_pF" id="nombre_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primero_PF" id="primero_PF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_Pf" id="segundo_Pf" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                 <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" aria-label="CURP" name="curp_PF" id="curp_PF" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La CURP es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">RFC <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="RFC_pF" id="RFC_pF" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                        <select name="sexo_pf" id="sexo_pf" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Femenino">Femenino</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Prefiero no responder">Prefiero no responder</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-9">
                                                    <div class="form-group">
                                                        <label for="name">Giro Comercial <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="giro_pF" id="giro_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                   <div class="form-group">
                                                        <h5 class="text-center">Datos de contacto</h5>
                                                    </div>
                                                </div> 

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Correo electrónico <span style="color:red;">(*)</span></label>
                                                        <input type="email" class="form-control"  name="correo_pF" id="electrónico_pF" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                            <input type="text" class="form-control"  name="telefono_PF" id="telefono_PF" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Domicilio fiscal</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa <span style="color:red;">(*)</span></label>
                                                        <select id="estado_pF" class="form-control" name="estado_pF" placeholder="*Entidad Federativa" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre del Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                                        <select id="municipio_pF" class="form-control" name="municipio_pF" placeholder="*Municipio" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo municipio o alcaldía es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Vialidad <span style="color:red;">(*)</span></label>
                                                        <select name="vialidad_pF" id="vialidad_pF" class="form-control" placeholder="*Vialidad" >
                                                            <option value="">Seleccione</option>
                                                            <option value="AMPLIACIÓN">Ampliación</option>
                                                            <option value="ANDADOR">Andador</option>
                                                            <option value="AUTOPISTA">Autopista</option>
                                                            <option value="AVENIDA">Avenida</option>
                                                            <option value="BOULEVARD">Boulevard</option>
                                                            <option value="CALLE">Calle</option>
                                                            <option value="CALLEJÓN">Callejón</option>
                                                            <option value="CALZADA">Calzada</option>
                                                            <option value="CARRETERA">Carretera</option>
                                                            <option value="CERRADA">Cerrada</option>
                                                            <option value="CIRCUITO">Circuito</option>
                                                            <option value="CIRCUNVALACIÓN">Circunvalación</option>
                                                            <option value="CONTINUACIÓN">Continuación</option>
                                                            <option value="CORREDOR">Corredor</option>
                                                            <option value="DIAGONAL">Diagonal</option>
                                                            <option value="EJE VIAL">Eje vial</option>
                                                            <option value="PERIFÉRICO">Periférico</option>
                                                            <option value="PRIVADA">Privada</option>
                                                            <option value="PROLONGACIÓN">Prolongación</option>
                                                            <option value="RETORNO">Retorno</option>
                                                            <option value="VIADUCTO">Viaducto</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="vialidad_calle_pF" id="vialidad_calle_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El campo vialidad o calle es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Colonia <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_pF" id="colonia_pF" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La colonia es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Número exterior" name="num_ext_pF" id="num_ext_pF" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El Núm. ext. es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Int.</label>
                                                        <input type="text" class="form-control" placeholder="Número interior" name="num_int_pF"  oninput="this.value = this.value.toUpperCase()">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Código Postal <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Código Postal" name="cp_pF" id="cp_pF" minlength="5" maxlength="5" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El código postal es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">¿Desea registrar Representante Legal? <span style="color:red;">(*)</span></label>
                                                        <select name="representate" id="representate" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Si">Si</option>
                                                            <option value="No">No</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12" id="Conrepresentante" style="display:none;">
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Información del Representante Legal</h5>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Datos de identificación</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) del representante <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_representante_pF" id="nombre_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primer_representante_pF" id="primer_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_representante_pF" id="segundo_representante_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control"  aria-label="CURP" name="curp_representante_pF" id="curp_representante_pF" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La CURP es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                        <select name="sexo_representante_pF" id="sexo_representante_pF" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Femenino">Femenino</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Prefiero no responder">Prefiero no responder</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                   <div class="form-group">
                                                        <h5 class="text-center">Datos de contacto</h5>
                                                    </div>
                                                </div> 

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Correo electrónico <span style="color:red;">(*)</span></label>
                                                        <input type="email" class="form-control" name="correo_representante_pF" id="correo_representante_pF" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control"  name="telefono_representante_pF" id="telefono_representante_pF" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Datos de la documentación que acredite la personeria</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4">  
                                                    <div class="form-group">
                                                        <label for="name">Tipo de documento <span style="color:red;">(*)</span></label>
                                                        <select name="tipo_documento_pF" id="tipo_documento_pF" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Carta Poder">Carta Poder</option>
                                                                <option value="Instrumento Notarial">Instrumento Notarial</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_expedicion_pF" id="fecha_expedicion_pF" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-2"><br><label for="btncheck1" style="color:black">Sin fecha de vigencia</label>
                                                    <input name="fecha_vigencia_pF" type="checkbox" class="form-check-label" id="check_vigencia" autocomplete="off"/>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3" id="fecha_vigencia_pF">
                                                    <div class="form-group">
                                                        <label for="fecha_vigencia_pF">Fecha vigencia</label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_vigencia_pF" id="fecha_vigencia_pF" min="<?= date("Y-m-d") ?>" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="">Descripción del documento que acredite la personaria <span style="color:red;">(*)</span></label>
                                                        <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_pF" id="descripcion_pF" style="min-width: 100%; width: 100%; height: 60px; resize: vertical;"
                                                        placeholder="Ejemplo: Carta poder simple de fecha___, firmada ante dos testigos, suscrita a favor del compareciente por el (C., Lic., Ing., etc.,)_____, en cuanto ___ de la moral citada, personalidad que acredite en terminos de___ número(45 Cuarenta y Cinco), de fecha___, pasada ante la fe del(Lic., Mtro., etc.,)___, Notario Público Número ___, del Estado de ____, y cuyas facultades no han sido revocadas ni modificadas a la fecha."></textarea>
                                                        <div class="invalid-feedback">
                                                            La descripción es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Identificación Oficial  <span style="color:red;">(*)</span></label>
                                                        <select id="tipo_identificacion_pFCR" name="tipo_identificacion_pFCR" class="form-control">
                                                            <option value="">Seleccione el tipo de indentificación</option>
                                                            <option value="Credencial de elector">Credencial de Elector</option>
                                                            <option value="Pasaporte">Pasaporte</option>
                                                            <option value="Cédula profesional">Cédula Profesional</option>
                                                            <option value="Licencia de conducir">Licencia de Conducir</option>
                                                            <option value="Credencial de inapam">Credencial de INAPAM</option>
                                                            <option value="Cartilla militar">Cartilla Militar</option>
                                                            <option value="Documento migratorio">Documento Migratorio</option>
                                                            <option value="Constancia de identidad">Constancia de Identidad</option>
                                                            <option value="Otro">Otros</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Este campo identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                    <div class="form-group">
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion_pFCR" id="num_identificacion_pFCR" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El campo núm. de identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación del Empleador</label><br>
                                                        <input type="file" name="documentoIne_pF" id="documentoIne_pF" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            La Identificación es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación del Representante Legal</label><br>
                                                        <input type="file" name="documentoRepresentacion_pF" id="documentoRepresentacion_pF" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            El documento de representación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Documento que acredite la personería</label><br>
                                                        <input type="file" name="documentoPoder_pF" id="documentoPoder_pF" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo_pF" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div align="center">
                                                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                                                        <a href="{{ route('cancelar_edicion', ['id' => $id, 'redirect_to' => 'publico']) }}" class="btn btn-primary" style=" background-color:#CEA845; border-color:#CEA845;">Regresar</a>    
                                                    </div>
                                                </div> 
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12" id="Sinrepresentante" style="display:none;">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Identificación Oficial <span style="color:red;">(*)</span></label>
                                                        <select id="tipo_identificacion_pF" name="tipo_identificacion_pF" class="form-control">
                                                            <option value="">Seleccione el tipo de indentificación</option>
                                                            <option value="Credencial de elector">Credencial de Elector</option>
                                                            <option value="Pasaporte">Pasaporte</option>
                                                            <option value="Cédula profesional">Cédula Profesional</option>
                                                            <option value="Licencia de conducir">Licencia de Conducir</option>
                                                            <option value="Credencial de inapam">Credencial de INAPAM</option>
                                                            <option value="Cartilla militar">Cartilla Militar</option>
                                                            <option value="Documento migratorio">Documento Migratorio</option>
                                                            <option value="Constancia de identidad">Constancia de Identidad</option>
                                                            <option value="Otro">Otros</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Este campo identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                    <div class="form-group">
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion_pF" id="num_identificacion_pF" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El campo núm. de identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación Oficial</label><br>
                                                        <input type="file" name="documentoIne_pFSR" id="documentoIne_pFSR" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            La Identificación es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo_pFSR" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div align="center">
                                                    <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                                                    <a href="{{ route('cancelar_edicion', ['id' => $id, 'redirect_to' => 'publico']) }}" class="btn btn-primary" style=" background-color:#CEA845; border-color:#CEA845;">Regresar</a>    
                                                </div>
                                            </div> 
                                        </div>
                                    </div>


                                    <div class="col-xs-12 col-sm-12 col-md-12" id="persona_moral" style="display:none;">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center" style="color:#CEA845">Información Patronal</h4>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h5 class="text-center">Datos de identificación</h5>
                                            </div>
                                        </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="name">Razón Social <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="razon" id="razon" placeholder="Ejemplo: Patos Asados S.A. de C.V." class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">RFC <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="rfc_moral" id="rfc_moral" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Giro Comercial <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="giro_moral" id="giro_moral" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Domicilio fiscal</h5>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa <span style="color:red;">(*)</span></label>
                                                        <select id="estado_moral" class="form-control" name="estado_moral" placeholder="*Entidad Federativa" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}">{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre del Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                                        <select id="municipio_moral" class="form-control" name="municipio_moral" placeholder="*Municipio" >
                                                            <option value="">Seleccione</option>
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo municipio o alcaldía es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Vialidad <span style="color:red;">(*)</span></label>
                                                        <select name="vialidad_Moral" id="vialidad_Moral" class="form-control" placeholder="*Vialidad" >
                                                            <option value="">Seleccione</option>
                                                            <option value="AMPLIACIÓN">Ampliación</option>
                                                            <option value="ANDADOR">Andador</option>
                                                            <option value="AUTOPISTA">Autopista</option>
                                                            <option value="AVENIDA">Avenida</option>
                                                            <option value="BOULEVARD">Boulevard</option>
                                                            <option value="CALLE">Calle</option>
                                                            <option value="CALLEJÓN">Callejón</option>
                                                            <option value="CALZADA">Calzada</option>
                                                            <option value="CARRETERA">Carretera</option>
                                                            <option value="CERRADA">Cerrada</option>
                                                            <option value="CIRCUITO">Circuito</option>
                                                            <option value="CIRCUNVALACIÓN">Circunvalación</option>
                                                            <option value="CONTINUACIÓN">Continuación</option>
                                                            <option value="CORREDOR">Corredor</option>
                                                            <option value="DIAGONAL">Diagonal</option>
                                                            <option value="EJE VIAL">Eje vial</option>
                                                            <option value="PERIFÉRICO">Periférico</option>
                                                            <option value="PROLONGACIÓN">Prolongación</option>
                                                            <option value="RETORNO">Retorno</option>
                                                            <option value="VIADUCTO">Viaducto</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="vialidad_calleMoral" id="vialidad_calleMoral" class="form-control" placeholder="*Nombre vialidad" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El campo vialidad o calle es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Colonia <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_moral" id="colonia_moral" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La colonia es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Número exterior" name="num_ext_moral" id="num_ext_moral" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El Núm. ext. es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Núm. Int.</label>
                                                        <input type="text" class="form-control" placeholder="Número interior" name="num_int" oninput="this.value = this.value.toUpperCase()">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Código Postal <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" name="cp_moral" id="cp_moral"  minlength="5" maxlength="5" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            El código postal es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Información del Representante Legal</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center">Datos de identificación</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Nombre(s) del Representante Legal<span style="color:red;">(*)</span></label>
                                                        <input type="text" name="nombre_representante_Moral" id="nombre_representante_Moral" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="primer_Moral" id="primer_Moral" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                        <input type="text" name="segundo_Moral" id="segundo_Moral" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                                        <div class="invalid-feedback">
                                                            El segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>  
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">CURP <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control"  aria-label="CURP" name="curp_moral" id="curp_moral" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" >
                                                        <div class="invalid-feedback">
                                                            La CURP es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                        <select name="sexo_Moral" id="sexo_Moral" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Femenino">Femenino</option>
                                                            <option value="Masculino">Masculino</option>
                                                            <option value="Prefiero no responder">Prefiero no responder</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                   <div class="form-group">
                                                        <h5 class="text-center">Datos de contacto</h5>
                                                    </div>
                                                </div> 

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Correo electrónico <span style="color:red;">(*)</span></label>
                                                        <input type="email" class="form-control" name="correo_Moral" id="correo_Moral" >
                                                        <div class="invalid-feedback">
                                                            El Correo electrónico es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                        <input type="text" class="form-control" placeholder="*Telefono"  name="telefono_Moral" id="telefono_Moral" maxlength="10" pattern="[0-9]+" >
                                                        <div class="invalid-feedback">
                                                            El telefono es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h5 class="text-center" style="color:#CEA845">Datos de la documentación que acredite la personeria</h5>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-4">  
                                                    <div class="form-group">
                                                        <label for="name">Tipo de documento <span style="color:red;">(*)</span></label>
                                                        <select name="tipo_Moral" id="tipo_Moral" class="form-control">
                                                            <option value="">Seleccione</option>
                                                            <option value="Carta Poder">Carta Poder</option>
                                                                <option value="Instrumento Notarial">Instrumento Notarial</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_expedicicion_Moral" id="fecha_expedicicion_Moral" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-2"><br><label for="btncheck1"  style="color:black">Sin fecha de vigencia</label>
                                                    <input name="fecha_vigencia_Moral" type="checkbox" class="form-check-label" id="check_vigenciaM" autocomplete="off"/>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-3" id="fecha_vigencia_Moral">
                                                    <div class="form-group">
                                                        <label for="fecha_vigencia_Moral">Fecha vigencia</label>
                                                        <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_vigencia_Moral" id="fecha_vigencia_Moral" min="<?= date("Y-m-d") ?>" >
                                                        <div class="invalid-feedback">
                                                            La fecha es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>   
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label for="">Descripción del documento que acredite la personaria <span style="color:red;">(*)</span></label>
                                                        <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_Moral"  id="descripcion_Moral" style="min-width: 100%; width: 100%; height: 60px; resize: vertical;" 
                                                        placeholder="Ejemplo: Carta poder simple de fecha___, firmada ante dos testigos, suscrita a favor del compareciente por el (C., Lic., Ing., etc.,)_____, en cuanto ___ de la moral citada, personalidad que acredite en terminos de___ número(45 Cuarenta y Cinco), de fecha___, pasada ante la fe del(Lic., Mtro., etc.,)___, Notario Público Número ___, del Estado de ____, y cuyas facultades no han sido revocadas ni modificadas a la fecha."></textarea>
                                                        <div class="invalid-feedback">
                                                            La descripción es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label for="name">Identificación Oficial  <span style="color:red;">(*)</span></label>
                                                        <select id="tipo_identificacion_Moral" name="tipo_identificacion_Moral" class="form-control">
                                                            <option value="">Seleccione el tipo de indentificación</option>
                                                            <option value="Credencial de elector">Credencial de Elector</option>
                                                            <option value="Pasaporte">Pasaporte</option>
                                                            <option value="Cédula profesional">Cédula Profesional</option>
                                                            <option value="Licencia de conducir">Licencia de Conducir</option>
                                                            <option value="Credencial de inapam">Credencial de INAPAM</option>
                                                            <option value="Cartilla militar">Cartilla Militar</option>
                                                            <option value="Documento migratorio">Documento Migratorio</option>
                                                            <option value="Constancia de identidad">Constancia de Identidad</option>
                                                            <option value="Otro">Otros</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            Este campo identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                    <div class="form-group">
                                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                        <input type="text" name="num_identificacion_Moral" id="num_identificacion_Moral" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                                        <div class="invalid-feedback">
                                                            El campo núm. de identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h4 class="text-center" style="color:#CEA845">Documentos</h4>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Acta Constitutiva</label><br>
                                                        <input type="file" name="documentoIne_Moral" id="documentoIne_Moral" class="form-control" accept=".pdf" >
                                                                                                               <div class="invalid-feedback">
                                                            La Identificación es obligatoria.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Identificación del Representante Legal</label><br>
                                                        <input type="file" name="documentoRepresentacion_Moral" id="documentoRepresentacion_Moral" class="form-control" accept=".pdf" >
                                                        <div class="invalid-feedback">
                                                            El documento de representación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label><span style="color:red;">*</span>Documento que acredite la personería</label><br>
                                                        <input type="file" name="documentoPoder" id="documentoPoder" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label>Anexo (Documentos Complementarios)</label><br>
                                                        <input type="file" name="documentoAnexo" class="form-control" accept=".pdf">
                                                    </div>
                                                </div>

                                    
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div align="center">
                                                        <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                                                        <a href="{{ route('cancelar_edicion', ['id' => $id, 'redirect_to' => 'publico']) }}" class="btn btn-primary" style=" background-color:#CEA845; border-color:#CEA845;">Regresar</a>    
                                                    </div>
                                                </div> 
                                            </div>
                                        </div>                           
                    </div>
                </div>
                <!--<div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>-->
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="ModalArchivar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('archivar_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-archivar" name="id" value="{{ $id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo del archivo de audiencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    Se archivará la audiencia con motivo de <b>falta de interés</b>
                </div>
    
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Archivar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="ModalReagendar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('reagendar_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-reagendar" name="id" value="">
        <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
        <input type="hidden" id="fechaConfirmacion" value= "{{ $fechaConfirmacion }}">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Fecha de la reagenda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sedeReagendar" value="{{ $solicitud->delegacion ?? ($sede ?? '') }}">
                    <div id="calendarReagendar"></div>
                    <input type="hidden" name="fecha" id="fechaSeleccionada">
                    <input type="hidden" name="hora" id="horaSeleccionada">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardarReagenda" disabled>Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="ModalIncopentencia" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('incopentencia_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-incopentencia" name="id" value="{{ $id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo de Incompetencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea name="observaciones" style="width:100%"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="ModalDesistimiento" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('desistimiento_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-desistimiento" name="id" value="{{ $id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo de Desistimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea name="observaciones" style="width:100%"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="ModalTerminar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    @php
        $hayRepresentante = 0;
        foreach ($citados as $citado) {
            if ($citado->comparecencia == 'Si') {
                $hayRepresentante = 1;
                break;
            }
        }

        $totalCitados = 0;
        $totalCitadosConComparecencia = 0;
        foreach ($citados as $citado) {
            $totalCitados++;
            if ($citado->comparecencia == 'Si') {
                $totalCitadosConComparecencia++;
            }
        }

        $hayAlMenosUnaComparecencia = ($totalCitadosConComparecencia > 0);

        $todasComparecencias = ($totalCitados > 0 && $totalCitadosConComparecencia === $totalCitados);

        $hayNotificacionCentro = false;
        $totalCentro = 0;
        $totalCentroSinComparecencia = 0;
        foreach ($citados as $citado) {
            if (($citado->notificacion ?? null) === 'Centro') {
                $hayNotificacionCentro = true;
                $totalCentro++;
                $tieneComparecencia = ($citado->comparecencia == 'Si');
                if (!$tieneComparecencia) {
                    $totalCentroSinComparecencia++;
                }
            }
        }

        $casoMultasCentroSinComparecencia = ($totalCentro >= 1 && $totalCentroSinComparecencia === $totalCentro);

        $hayAlMenosUnAbogado = 0;
        foreach ($citados as $citado) {
            if ($citado->comparecencia == 'Si') {
                $hayAlMenosUnAbogado = 1;
                break;
            }
        }

        $bloquearContinuar = (!$hayNotificacionCentro && $hayAlMenosUnAbogado == 0);
        //$bloquearContinuar = (!$hayNotificacionCentro && !$todasComparecencias);

        $bandera = 0;
        foreach ($citados as $citado) {
            if (($citado->notificacion ?? null) === 'Centro') {
                $tieneRepresentante = ($citado->comparecencia == 'Si');
                if (!$tieneRepresentante) {
                    $bandera = 1;
                    break;
                }
            }
        }
    @endphp

    @if($hayNotificacionCentro)
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    @if($casoMultasCentroSinComparecencia)
                        Ningún citado notificado por el Centro presenta comparecencia. Debes emitir constancias de no conciliación.
                    @elseif($bandera != 0)
                        Continuar con la audiencia.
                    @else
                        Continuar con la audiencia.
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <form class="needs-validation novalidate" method="POST" action="{{route('audiencia_parte2')}}">
                        @csrf
                        <input type="hidden" name="id" value="{{$id}}">
                        <input type="hidden" name="bandera" value="{{$hayRepresentante}}">
                        <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
                        <button type="submit" class="btn btn-success" @if($casoMultasCentroSinComparecencia) disabled @endif>
                            Continuar
                        </button>
                    </form>                    
                </div>
            </div>
        </div>
    @else
        <div class="modal-dialog">
            <div class="modal-content modal-xl">
                <div class="modal-header">
                    @if($hayRepresentante == 0)
                        <!--span>Si no seleccionas todos los representantes debes seleccionar una fecha para que próxima audiencia.<br>
                        Notificará el centro</!--span>
                        <input type="date" name="fecha" class="form-control"-->
                        Si no se presenta el representante debes reagendar la audiencia.
                    @else
                        Continuar con la audiencia.
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <form class="needs-validation novalidate" method="POST" action="{{route('audiencia_parte2')}}">
                        @csrf
                        <input type="hidden" name="id" value="{{$id}}">
                        <input type="hidden" name="bandera" value="{{$hayRepresentante}}">
                        <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
                        <button type="submit" class="btn btn-success" @if($bloquearContinuar) disabled @endif>
                            Continuar
                        </button>
                    </form> 
                </div>
            </div>
        </div>
    @endif
</div>

<div class="modal fade" id="ModalEmitirMultas" tabindex="-1" aria-labelledby="ModalEmitirMultasLabel" aria-hidden="true">
    <form class="needs-validation novalidate" method="POST" action="{{route('emitir_multas')}}">
        @csrf
        <input type="hidden" name="id" value="{{ $id }}">
        <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalEmitirMultasLabel">Emisión de Constancias de No Conciliación.</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <br>
                    <div class="text-center">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 100px; line-height: 1; align-items: center;"></i>
                    </div>
                    <br>
                    <p class="mb-2">
                        Se procederá a emitir las Constancias de No Conciliación, por Incomparecencia de los citados.
                    </p>
                    <br>
                    <p class="mb-2">
                        Se detectó que <b>los citados fueron notificados por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo</b>, y que no ha sido registrada ninguna comparecencia.<br><br>Por lo tanto, al dar click en el botón "Emitir", se generarán la Constancias de No Conciliación.
                        Confirma para continuar con la <b>emisión de dichos documentos</b>.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Emitir</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="modalAgregarDerecho" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation' novalidate method='POST' enctype="multipart/form-data" name="AgregarPersonaFisica" id="AgregarPersonaFisica" action="{{route('insertar_citado_PF')}}">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="id_citado_pf" id="id_citado_pf" value="">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Persona Física</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Nombre del citado <span style="color:red;">(*)</span></label>
                                <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Primer apellido <span style="color:red;">(*)</span></label>
                                <input type="text" name="primer_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Segundo apellido <span style="color:red;">(*)</span></label>
                                <input type="text" name="segundo_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="name">Tipo de identificación <span style="color:red;">(*)</span></label>
                                <select name="identificacionAlta" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Credencial de elector">CREDENCIAL DE ELECTOR</option>
                                    <option value="Pasaporte">PASAPORTE</option>
                                    <option value="Cédula profesional">CÉDULA PROFESIONAL</option>
                                    <option value="Licencia de conducir">LICENCIA DE CONDUCIR</option>
                                    <option value="Credencial de inapam">CREDENCIAL DE INAPAM</option>
                                    <option value="Cartilla militar">CARTILLA MILITAR</option>
                                    <option value="Documento migratorio">DOCUMENTO MIGRATORIO</option>
                                    <option value="Constancia de identidad">CONSTANCIA DE IDENTIDAD</option>
                                    <option value="Otro">OTROS</option>
                                </select>
                                <div class="invalid-feedback">
                                    El tipo de identificaión es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Subir Identificación oficial <span style="color:red;">(*)</span></label>
                                <input type="file" name="documentoIdentificacion" class="form-control" accept=".pdf" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div>
                            <input type="hidden" name="id_usuario_registro" value="{{ Auth::id() }}">
                        </div>
                        
                    </div>                                     
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="modalActualizaCitados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' name="AgregarPersonaFisica" id="AgregarPersonaFisica" action="{{route('actualiza_citados')}}">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="id_citado_pf" id="modal-id-citado" value="">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Actualizar Citado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Nombre del citado</label>
                                <input type="text" name="nombre" class="form-control" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Primer apellido</label>
                                <input type="text" name="primer_apellido" class="form-control" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Segudno apellido</label>
                                <input type="text" name="segundo_apellido" class="form-control" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div>
                            <input type="hidden" name="id_usuario_registro" value="{{ Auth::id() }}">
                        </div>
                        
                    </div>                                     
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="nuevo_poder" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <style>
        
        .fc-event { 
            padding: 3px 6px !important; 
            border-radius: 4px !important; 
            font-size: 12px !important; 
            cursor: pointer; 
        }
        #calendarReagendar{ 
            width: 100%; 
            min-height: 500px; 
        }
        .fc-event-disponible, .fc-est-disponible{ 
            color:#fff !important; 
            background-color:#00CE1C !important; 
            border-color:#00CE1C !important; 
        }
        .fc-event-expirado, .fc-est-expirado{ 
            color:#fff !important; 
            background-color:#F59727 !important; 
            border-color:#F59727 !important; 
        }
        .fc-event-inhabil, .fc-est-inhabil{ 
            color:#fff !important; 
            background-color:#3B78DB !important; 
            border-color:#3B78DB !important; 
        }
        .fc-event-ocupado, .fc-est-ocupado{ color:#fff !important; 
            background-color:#DA0909 !important;
            border-color:#DA0909 !important; 
        }
        .fc-event-selected { 
            border: 2px solid #FFD700 !important; 
            box-shadow: 0 0 8px #FFD700; 
        }
        .fc .fc-event-main, .fc .fc-event-time { 
            color:#fff !important; 
        }
       
        .fc-list .fc-list-event.fc-event-disponible td,
        .fc-list .fc-list-event.fc-est-disponible td{ 
            background-color:#00CE1C !important; 
            color:#fff !important; 
        }
        .fc-list .fc-list-event.fc-event-expirado td,
        .fc-list .fc-list-event.fc-est-expirado td{ 
            background-color:#F59727 !important; 
            color:#fff !important; 
        }
        .fc-list .fc-list-event.fc-event-inhabil td,
        .fc-list .fc-list-event.fc-est-inhabil td{ 
            background-color:#3B78DB !important; 
            color:#fff !important; 
        }
        .fc-list .fc-list-event.fc-event-ocupado td,
        .fc-list .fc-list-event.fc-est-ocupado td{ 
            background-color:#DA0909 !important; 
            color:#fff !important; 
        }
        @media (min-width: 1200px){ .modal-xl{ --bs-modal-width: 95vw; } }
        .modal .modal-body{ max-height: calc(100vh - 200px); overflow-y: auto; }

    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
         $('.open-modal').click(function() {
            const id = $(this).data('id'); // Obtiene el valor de data-id

            document.getElementById('modal-id').value = id;
            document.getElementById('modal-id-reagendar').value = id;
            document.getElementById('id_citado_2').value = id;
            document.getElementById('id_citado_pf').value = id;
            document.getElementById('modal-id-archivar').value = id;
            document.getElementById('modal-id-reagendar').value = id;
            document.getElementById('modal-id-incopentencia').value = id;
            document.getElementById('modal-id-desistimiento').value = id;
            document.getElementById('modal-id-citado').value = id;
        });

        document.getElementById('tipo_persona').addEventListener('change', function() {
            var selectTipo = document.getElementById('tipo_persona');
            const nombreDiv = document.getElementById('persona_fisica');
            const empresaDiv = document.getElementById('persona_moral');
            
            function actualizarTipoPersona() {
                const valor = selectTipo.value;

                // Oculta ambos inicialmente
                nombreDiv.style.display = 'none';
                empresaDiv.style.display = 'none';

                if (valor === 'Fisica') {
                    nombreDiv.style.display = 'block';
                    empresaDiv.style.display = 'none';
                    //Poner los campos requeridos
                    document.getElementById('nombre_pF').setAttribute('required', 'true');
                    document.getElementById('primero_PF').setAttribute('required', 'true');
                    document.getElementById('segundo_Pf').setAttribute('required', 'true');
                    document.getElementById('curp_PF').setAttribute('required', 'true');
                    document.getElementById('RFC_pF').setAttribute('required', 'true');
                    document.getElementById('sexo_pf').setAttribute('required', 'true');
                    document.getElementById('giro_pF').setAttribute('required', 'true');
                    document.getElementById('electrónico_pF').setAttribute('required', 'true');
                    document.getElementById('telefono_PF').setAttribute('required', 'true');
                    document.getElementById('estado_pF').setAttribute('required', 'true');
                    document.getElementById('municipio_pF').setAttribute('required', 'true');
                    document.getElementById('vialidad_pF').setAttribute('required', 'true');
                    document.getElementById('vialidad_calle_pF').setAttribute('required', 'true');
                    document.getElementById('colonia_pF').setAttribute('required', 'true');
                    document.getElementById('num_ext_pF').setAttribute('required', 'true');
                    document.getElementById('cp_pF').setAttribute('required', 'true');
                    //Quitar los campos requeridos
                    document.getElementById('razon').removeAttribute('required');
                    document.getElementById('rfc_moral').removeAttribute('required');
                    document.getElementById('giro_moral').removeAttribute('required');
                    document.getElementById('estado_moral').removeAttribute('required');
                    document.getElementById('municipio_moral').removeAttribute('required');
                    document.getElementById('vialidad_Moral').removeAttribute('required');
                    document.getElementById('vialidad_calleMoral').removeAttribute('required');
                    document.getElementById('colonia_moral').removeAttribute('required');
                    document.getElementById('num_ext_moral').removeAttribute('required');
                    document.getElementById('cp_moral').removeAttribute('required');
                    document.getElementById('nombre_representante_Moral').removeAttribute('required');
                    document.getElementById('primer_Moral').removeAttribute('required');
                    document.getElementById('segundo_Moral').removeAttribute('required');
                    document.getElementById('curp_moral').removeAttribute('required');
                    document.getElementById('sexo_Moral').removeAttribute('required');
                    document.getElementById('correo_Moral').removeAttribute('required');
                    document.getElementById('telefono_Moral').removeAttribute('required');
                    document.getElementById('tipo_Moral').removeAttribute('required');
                    document.getElementById('fecha_expedicicion_Moral').removeAttribute('required');
                    document.getElementById('fecha_vigencia_Moral').removeAttribute('required');
                    document.getElementById('descripcion_Moral').removeAttribute('required');
                    document.getElementById('documentoIne_Moral').removeAttribute('required');
                    document.getElementById('documentoRepresentacion_Moral').removeAttribute('required');
                    document.getElementById('documentoPoder').removeAttribute('required');

                } else if (valor === 'Moral') {
                    empresaDiv.style.display = 'block';
                    nombreDiv.style.display = 'none';
                    //Las personas fisicas quitar requerido
                    document.getElementById('nombre_pF').removeAttribute('required');
                    document.getElementById('nombre_pF').removeAttribute('required');
                    document.getElementById('primero_PF').removeAttribute('required');
                    document.getElementById('segundo_Pf').removeAttribute('required');
                    document.getElementById('curp_PF').removeAttribute('required');
                    document.getElementById('RFC_pF').removeAttribute('required');
                    document.getElementById('sexo_pf').removeAttribute('required');
                    document.getElementById('giro_pF').removeAttribute('required');
                    document.getElementById('electrónico_pF').removeAttribute('required');
                    document.getElementById('telefono_PF').removeAttribute('required');
                    document.getElementById('estado_pF').removeAttribute('required');
                    document.getElementById('municipio_pF').removeAttribute('required');
                    document.getElementById('vialidad_pF').removeAttribute('required');
                    document.getElementById('vialidad_calle_pF').removeAttribute('required');
                    document.getElementById('colonia_pF').removeAttribute('required');
                    document.getElementById('num_ext_pF').removeAttribute('required');
                    document.getElementById('cp_pF').removeAttribute('required');
                    //Poner los campos requeridos
                    document.getElementById('razon').setAttribute('required', 'true');
                    document.getElementById('rfc_moral').setAttribute('required', 'true');
                    document.getElementById('giro_moral').setAttribute('required', 'true');
                    document.getElementById('estado_moral').setAttribute('required', 'true');
                    document.getElementById('municipio_moral').setAttribute('required', 'true');
                    document.getElementById('vialidad_Moral').setAttribute('required', 'true');
                    document.getElementById('vialidad_calleMoral').setAttribute('required', 'true');
                    document.getElementById('colonia_moral').setAttribute('required', 'true');
                    document.getElementById('num_ext_moral').setAttribute('required', 'true');
                    document.getElementById('cp_moral').setAttribute('required', 'true');
                    document.getElementById('nombre_representante_Moral').setAttribute('required', 'true');
                    document.getElementById('primer_Moral').setAttribute('required', 'true');
                    document.getElementById('segundo_Moral').setAttribute('required', 'true');
                    document.getElementById('curp_moral').setAttribute('required', 'true');
                    document.getElementById('sexo_Moral').setAttribute('required', 'true');
                    document.getElementById('correo_Moral').setAttribute('required', 'true');
                    document.getElementById('telefono_Moral').setAttribute('required', 'true');
                    document.getElementById('tipo_Moral').setAttribute('required', 'true');
                    document.getElementById('fecha_expedicicion_Moral').setAttribute('required', 'true');
                    //document.getElementById('fecha_vigencia_Moral').setAttribute('required', 'true');
                    document.getElementById('descripcion_Moral').setAttribute('required', 'true');
                    document.getElementById('documentoIne_Moral').setAttribute('required', 'true');
                    document.getElementById('documentoRepresentacion_Moral').setAttribute('required', 'true');
                    document.getElementById('documentoPoder').setAttribute('required', 'true');

                    
                }
            }

            if (selectTipo) {
                selectTipo.addEventListener('change', actualizarTipoPersona);
                // Ejecutar al cargar por si ya tiene valor
                actualizarTipoPersona();
            }
        });
        document.getElementById('representate').addEventListener('change', function() {
            var reprecentante = document.getElementById('representate');
            const razonDiv = document.getElementById('Conrepresentante');
            const propioDiv = document.getElementById('Sinrepresentante');

            function actualizarRepresentante() {
                const valor = reprecentante.value;

                // Oculta ambos inicialmente
                razonDiv.style.display = 'none';
                propioDiv.style.display = 'none';

                if (valor === 'Si') {
                    razonDiv.style.display = 'block';
                    propioDiv.style.display = 'none';
                    //Poner requeridos los campos
                    document.getElementById('nombre_representante_pF').setAttribute('required', 'true');
                    document.getElementById('primer_representante_pF').setAttribute('required', 'true');
                    document.getElementById('segundo_representante_pF').setAttribute('required', 'true');
                    document.getElementById('curp_representante_pF').setAttribute('required', 'true');
                    document.getElementById('sexo_representante_pF').setAttribute('required', 'true');
                    document.getElementById('correo_representante_pF').setAttribute('required', 'true');
                    document.getElementById('telefono_representante_pF').setAttribute('required', 'true');
                    document.getElementById('tipo_documento_pF').setAttribute('required', 'true');
                    document.getElementById('fecha_expedicion_pF').setAttribute('required', 'true');
                    //document.getElementById('fecha_vigencia_pF').setAttribute('required', 'true');
                    document.getElementById('descripcion_pF').setAttribute('required', 'true');
                    document.getElementById('documentoIne_pF').setAttribute('required', 'true');
                    document.getElementById('documentoRepresentacion_pF').setAttribute('required', 'true');
                    document.getElementById('documentoPoder_pF').setAttribute('required', 'true');              
                    //Quitar requeridos los campos
                    document.getElementById('documentoIne_pFSR').removeAttribute('required');

                } else if (valor === 'No') {
                    razonDiv.style.display = 'none';
                    propioDiv.style.display = 'block';
                    //Poner requeridos los campos
                    document.getElementById('documentoIne_pFSR').setAttribute('required', 'true');
                    //Poner requeridos los campos
                    document.getElementById('nombre_representante_pF').removeAttribute('required');
                    document.getElementById('primer_representante_pF').removeAttribute('required');
                    document.getElementById('segundo_representante_pF').removeAttribute('required');
                    document.getElementById('curp_representante_pF').removeAttribute('required');
                    document.getElementById('sexo_representante_pF').removeAttribute('required');
                    document.getElementById('correo_representante_pF').removeAttribute('required');
                    document.getElementById('telefono_representante_pF').removeAttribute('required');
                    document.getElementById('tipo_documento_pF').removeAttribute('required');
                    document.getElementById('fecha_expedicion_pF').removeAttribute('required');
                    document.getElementById('fecha_vigencia_pF').removeAttribute('required');
                    document.getElementById('descripcion_pF').removeAttribute('required');
                    document.getElementById('documentoIne_pF').removeAttribute('required');
                    document.getElementById('documentoRepresentacion_pF').removeAttribute('required');
                    document.getElementById('documentoPoder_pF').removeAttribute('required'); 
                }
            }

            if (reprecentante) {
                reprecentante.addEventListener('change', actualizarRepresentante);
                // Ejecutar al cargar por si ya tiene valor
                actualizarRepresentante();
            }
        });

         //PERSONA FÍSICA
        document.getElementById("fecha_vigencia_pF").style.display = "block";
        $(function(){
            $('#check_vigencia').on('change', validarcheckvigencia);
        })
        function validarcheckvigencia(){
            vigencia = document.getElementById("fecha_vigencia_pF").style.display;
            if (vigencia == "none") {
                document.getElementById("fecha_vigencia_pF").style.display = "block";
            }
            else{
                document.getElementById("fecha_vigencia_pF").style.display = "none";
            }
        }

        //PERSONA MORAL
        document.getElementById("fecha_vigencia_Moral").style.display = "block";
        $(function(){
            $('#check_vigenciaM').on('change', validarcheckvigenciaM);
        })
        function validarcheckvigenciaM(){
            vigenciaM = document.getElementById("fecha_vigencia_Moral").style.display;
            if (vigenciaM == "none") {
                document.getElementById("fecha_vigencia_Moral").style.display = "block";
            }
            else{
                document.getElementById("fecha_vigencia_Moral").style.display = "none";
            }
        }
    </script>


    <script>
        let calendarReagendar;
        $('#ModalReagendar').on('shown.bs.modal', function () {
            const calEl = document.getElementById('calendarReagendar');
            if (!calEl) return;
            if (calendarReagendar) { calendarReagendar.destroy(); }
            //Calculamos fecha mínima (5 días hábiles) para posicionar el calendario directamente en la primera semana válida.
            const sede = $('#sedeReagendar').val();
            const diasHabilesNotificacion = (/morelia/i.test(String(sede || '')) ? 11 : 7);
            const conciliadorId = '{{ $conciliador->id ?? "" }}';
            const hoy = new Date();
            hoy.setHours(0,0,0,0);

            function toYMD(dt) {
                const y = dt.getFullYear();
                const m = String(dt.getMonth() + 1).padStart(2, '0');
                const d = String(dt.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
                }

            function addDaysYMD(ymd, n) {
                const [y, m, d] = ymd.split('-').map(Number);
                const dt = new Date(y, m - 1, d);   // local
                dt.setDate(dt.getDate() + n);
                return toYMD(dt);
            }

            async function addNaturalAndInhabilDays(fechaConfirmacionStr, n, centro) {
                let inhabiles = [];
                try {
                    const res = await fetch(`{{ url('/api/dias-inhabiles-centro') }}?centro=${encodeURIComponent(centro)}`);
                    const data = await res.json();
                    inhabiles = data.filter(r => r.user_id === null);
                } catch(e) {
                    console.error("Error fetching dias inhabiles", e);
                }

                function isDiaInhabil(dtStr) {
                    for(let i=0; i<inhabiles.length; i++) {
                        if(dtStr >= inhabiles[i].fecha_inicio && dtStr <= inhabiles[i].fecha_final) return true;
                    }
                    return false;
                }

                const [y, m, d] = fechaConfirmacionStr.split('-').map(Number);
                let dt = new Date(y, m - 1, d);
                let added = 0;
                while (added < n) {
                    dt.setDate(dt.getDate() + 1);
                    let dtStr = toYMD(dt);
                    if (!isDiaInhabil(dtStr)) {
                        added++;
                    }
                }
                return toYMD(dt);
            }

            function isWeekend(dt){
                const day = dt.getDay();
                return day === 0 || day === 6;
            }

            function fetchEventosGlobales(startDate, endDate){
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: '{{ url('/api/obtenerAudienciasParte3') }}',
                        data: {
                            sede: sede,
                            start: startDate.toISOString(),
                            end: endDate.toISOString(),
                            conciliador: conciliadorId
                        },
                        success: (data)=> resolve(Array.isArray(data) ? data : []),
                        error: (xhr, status, err)=> reject(err || status || 'error')
                    });
                });
            }

            function buildInhabilIndex(eventos){
                const set = new Set();
                for(const ev of (eventos || [])){
                    if(!ev || !ev.start) continue;
                    const ymd = String(ev.start).slice(0,10);
                    const estado = ev.extendedProps && ev.extendedProps.estado ? ev.extendedProps.estado : null;
                    const userId = (ev.extendedProps && (ev.extendedProps.user_id ?? ev.extendedProps.userId)) ?? null;
                    if(estado === 'inhabil' && (userId === null || userId === '')){
                        set.add(ymd);
                    }
                }
                return set;
            }

            async function calcularFechaMinimaNotificacionAsync(){
                const diasHabilesNecesarios = diasHabilesNotificacion;
                let cursor = new Date(hoy);
                let contados = 0;

                const ventanaInicio = new Date(hoy);
                ventanaInicio.setHours(0,0,0,0);
                const ventanaFin = new Date(hoy);
                ventanaFin.setDate(ventanaFin.getDate() + 120);
                ventanaFin.setHours(23,59,59,999);

                let eventos = [];
                try {
                    eventos = await fetchEventosGlobales(ventanaInicio, ventanaFin);
                } catch(e){
                    eventos = [];
                }
                const inhabilSet = buildInhabilIndex(eventos);

                while(contados < diasHabilesNecesarios){
                    cursor.setDate(cursor.getDate() + 1);
                    if(isWeekend(cursor)) continue;
                    const ymd = toYMD(cursor);
                    if(inhabilSet.has(ymd)) continue;
                    contados++;
                }

                return cursor;
            }

            function calcularFechaMinima(){
                const siguiente = new Date(hoy);
                siguiente.setDate(siguiente.getDate() + 1);
                siguiente.setHours(0,0,0,0);
                return siguiente;
            }

            (async function(){

                const fechaMinima = calcularFechaMinima();
                const fechaMinimaStr = fechaMinima.toISOString().slice(0,10);

                //Fecha mínima para notificación (dinámica por sede) SOLO para aviso
                const fechaMinNotificacion = await calcularFechaMinimaNotificacionAsync();
                const fechaMinNotificacionStr = toYMD(fechaMinNotificacion);
                // Ajustar a lunes de la semana que contiene la fecha mínima para no cortar la semana
                const fechaSemanaInicio = new Date(fechaMinima);
                const desplazamientoLunes = (fechaSemanaInicio.getDay() + 6) % 7;
                fechaSemanaInicio.setDate(fechaSemanaInicio.getDate() - desplazamientoLunes);
                const startOfWeekStr = fechaSemanaInicio.toISOString().slice(0,10);

                const fechaConfirmacion = document.getElementById('fechaConfirmacion').value;
                const sede = $('#sedeReagendar').val();
                let fechaLimite = null;
                if (fechaConfirmacion && sede) {
                    fechaLimite = await addNaturalAndInhabilDays(fechaConfirmacion, 46, sede);
                } else if (fechaConfirmacion) {
                    fechaLimite = addDaysYMD(fechaConfirmacion, 46); // fallback
                }


                calendarReagendar = new FullCalendar.Calendar(calEl, {
                    locale: 'es',
                    firstDay: 1,
                    initialDate: fechaMinimaStr,
                    initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek',
                    headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                    validRange: function() {
                        const range = { start: startOfWeekStr };
                        if (fechaLimite) range.end = fechaLimite; 
                        return range;
                    },
                    events: function(fetchInfo, success, failure) {
                        $.ajax({
                            url: '{{ url('/api/obtenerAudienciasParte3') }}',
                            data: { sede: sede, start: fetchInfo.startStr, end: fetchInfo.endStr, conciliador: conciliadorId },
                            success: success,
                            error: () => failure('No se pudieron cargar eventos')
                        });
                    },
                    eventTimeFormat: { hour: '2-digit', minute: '2-digit' },
                    eventClick: function(info) {
                        const slot = new Date(info.event.start);
                        const slotYMD = slot.toISOString().slice(0,10);
                        const estadoClick = info.event.extendedProps && info.event.extendedProps.estado ? info.event.extendedProps.estado : null;
                        const titulo = (info.event && info.event.title) ? String(info.event.title) : '';

                        
                        if (estadoClick === 'ocupado') {
                            if (window.Swal && typeof Swal.fire === 'function') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Horario ocupado',
                                    text: 'Este horario ya está ocupado y no se puede seleccionar.',
                                });
                            }
                            return;
                        }

                        if (/audiencia\s*\(/i.test(titulo)) {
                            if (window.Swal && typeof Swal.fire === 'function') {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Horario con audiencia',
                                    html: 'Este horario ya cuenta con una audiencia programada. <br><br>Si continúas, la <b>audiencia se empalmará</b>.',
                                });
                            }
                        }

                        const estadoSeleccionable = (estadoClick === 'disponible' || /audiencia\s*\(/i.test(titulo));
                        const fechaSeleccionable = (slot > new Date() && slot.toISOString().slice(0,10) >= fechaMinimaStr);

                        if (estadoSeleccionable && fechaSeleccionable) {
                            $('.fc-event-selected').removeClass('fc-event-selected');
                            info.el.classList.add('fc-event-selected');
                            const fecha = slot.toISOString().split('T')[0];
                            const hora = slot.toTimeString().substring(0,5);
                            $('#fechaSeleccionada').val(fecha);
                            $('#horaSeleccionada').val(hora+':00');
                            $('#btnGuardarReagenda').prop('disabled', false);

                            if (slotYMD < fechaMinNotificacionStr) {
                                if (window.Swal && typeof Swal.fire === 'function') {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Aviso de notificación',
                                        html: 'La fecha seleccionada está <b>dentro de los ' + (diasHabilesNotificacion - 1) + ' días hábiles</b> requeridos para notificar.' +
                                            '<br><br>Fecha mínima sugerida: <b>' + fechaMinNotificacionStr + '</b>.',
                                    });
                                }
                            }
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Ups...',
                                text: 'Horario no disponible',
                            });
                        }
                    },
                    eventDidMount: function(info){
                        const estado = info.event.extendedProps.estado;
                        if(estado){ info.el.classList.add('fc-est-'+estado); info.el.classList.add('fc-event-'+estado); }
                    }
                });
                calendarReagendar.render();
                setTimeout(function(){ if (calendarReagendar) { calendarReagendar.updateSize(); calendarReagendar.refetchEvents(); } }, 200);
            })();
        });

        $('#sedeReagendar').on('change', function(){ if(calendarReagendar){ calendarReagendar.refetchEvents(); }});

        const formReagendar = document.querySelector('#ModalReagendar form');
        if(formReagendar){
            formReagendar.addEventListener('submit', function(e){
                const idAudiencia = document.getElementById('NUE').value;
                const fecha = document.getElementById('fechaSeleccionada').value;
                const hora = document.getElementById('horaSeleccionada').value;
                let mensajeHtml = '<p>Se reagendará la Audiencia con <strong>NUE: '+idAudiencia+'</strong></p>';
                if(fecha){ mensajeHtml += '<p>Fecha: <strong>'+fecha+'</strong></p>'; }
                if(hora){ mensajeHtml += '<p>Hora: <strong>'+hora.substring(0,5)+'</strong></p>'; }
                mensajeHtml += '<p>¿Confirmas?</p>';
                e.preventDefault();
                function lanzar(){
                    Swal.fire({
                        title: 'Confirmar reagenda',
                        html: mensajeHtml,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, reagendar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        focusCancel: true,
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result)=>{
                        if(result.isConfirmed){
                            formReagendar.submit();
                        }
                    });
                }
                if(window.Swal){ lanzar(); } else { setTimeout(lanzar, 200); }
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function cargarMunicipiosSolicitante(estadoId) {
                var $municipio = $('#municipio_pF');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }
                $.get(base_url + '/api/munSolicitante/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $.get(base_url + '/munSolicitante/' + estadoId, function (data) {
                        var html = '<option value="">Seleccione</option>';
                        data.forEach(function (m) {
                            html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                        });
                        $municipio.html(html);
                    }).fail(function (jq2, t2, e2) {
                        $municipio.html('<option value="">Error cargando municipios</option>');
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({
                                title: 'Error',
                                message: 'No se pudieron cargar los municipios. HTTP: ' + (jqXHR.status || jq2.status || 'N/A') + ' - ' + (errorThrown || e2 || textStatus),
                                position: 'topRight'
                            });
                        } else {
                            alert('No se pudieron cargar los municipios.');
                        }
                    });
                });
            }

            var $estadoSolicitante = $('#estado_pF');
            var base_url = "{{ url('') }}";

            if ($estadoSolicitante.length) {
                $estadoSolicitante.on('change', function () {
                    cargarMunicipiosSolicitante(this.value);
                });
                var inicial = $estadoSolicitante.val();
                if (inicial) cargarMunicipiosSolicitante(inicial);
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function cargarMunicipiosSolicitante(estadoId) {
                var $municipio = $('#municipio_moral');
                if (!$municipio.length) return;
                $municipio.html('<option value="">Cargando...</option>');
                if (!estadoId) {
                    $municipio.html('<option value="">Seleccione</option>');
                    return;
                }
                $.get(base_url + '/api/munSolicitante/' + estadoId, function (data) {
                    var html = '<option value="">Seleccione</option>';
                    data.forEach(function (m) {
                        html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                    });
                    $municipio.html(html);
                }).fail(function (jqXHR, textStatus, errorThrown) {
                    $.get(base_url + '/munSolicitante/' + estadoId, function (data) {
                        var html = '<option value="">Seleccione</option>';
                        data.forEach(function (m) {
                            html += '<option value="' + m.id + '">' + m.nombre + '</option>';
                        });
                        $municipio.html(html);
                    }).fail(function (jq2, t2, e2) {
                        $municipio.html('<option value="">Error cargando municipios</option>');
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({
                                title: 'Error',
                                message: 'No se pudieron cargar los municipios. HTTP: ' + (jqXHR.status || jq2.status || 'N/A') + ' - ' + (errorThrown || e2 || textStatus),
                                position: 'topRight'
                            });
                        } else {
                            alert('No se pudieron cargar los municipios.');
                        }
                    });
                });
            }

            var $estadoSolicitante = $('#estado_moral');
            var base_url = "{{ url('') }}";

            if ($estadoSolicitante.length) {
                $estadoSolicitante.on('change', function () {
                    cargarMunicipiosSolicitante(this.value);
                });
                var inicial = $estadoSolicitante.val();
                if (inicial) cargarMunicipiosSolicitante(inicial);
            }
        });
    </script>

    <script src="../../public/assets/js/validaciones.js"></script> 
    <script src="../../public/assets/js/poderes/general.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('.btn-abrir-modal-comparecencia').on('click', function() {
                const idCitado = $(this).data('id');
                const solicitud = $(this).data('solicitud');
                const audiencia = $(this).data('audiencia');
                
                const tipo = $(this).data('tipo') || '';
                const num = $(this).data('num') || '';
                const doc = $(this).data('doc') || '';

                $('#comp_citado_id').val(idCitado);
                $('#comp_solicitud_id').val(solicitud);
                $('#comp_audiencia_id').val(audiencia);
                
                $('#comp_tipo_identificacion').val(tipo);
                $('#comp_num_identificacion').val(num);
                
                if(doc && doc !== '') {
                    const basePath = "{{ url('storage/') }}";
                    $('#comp_doc_existente_container').show();
                    $('#comp_btn_ver_doc').attr('href', basePath + '/app/documentosSolicitud/' + doc);
                    $('#comp_doc_input').removeAttr('required');
                } else {
                    $('#comp_doc_existente_container').hide();
                    $('#comp_doc_input').attr('required', 'required');
                }
            });
        });
    </script>


@endsection