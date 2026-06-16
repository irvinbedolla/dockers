@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
    $contador = 0;
    $audienciaConclucionData = session('audiencia_conclucion_data_' . ($id ?? ''));
    $conclucion = session('conclucion')
        ?? (is_array($audienciaConclucionData) ? ($audienciaConclucionData['conclucion'] ?? null) : null);
@endphp
@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Audiencia Vista Previa</h3>
        </div>
        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Correcto</strong>
                {{ session()->get('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mt-1">
                                    <thead style="background-color: #4A001F;">
                                        <tr> 
                                            <th style="display:none">ID</th>
                                            <th style="color: #ffff;">Tipo parte</th>
                                            <th style="color: #ffff;">Nombre de la parte</th>
                                            <th style="color: #ffff;">Notificación</th>
                                            <th style="color: #ffff;">Estatus Notificación</th>
                                            <!--th style="color: #ffff;">Acciones</th-->
                                            <th style="color: #ffff;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="display:none">{{$solicitante->id}}</td>
                                            <td style="color: #000000;"><b>Solicitante</b></td>
                                            <td>{{ $solicitante->nombre }}</td>
                                            <td></td>
                                            <td></td>
                                            <!--td>
                                                @if(isset($solicitante->poder))
                                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditarPoder">
                                                        Editar Poder Solicitante
                                                    </button>
                                                @endif
                                            </td-->
                                            <td></td>
                                        </tr>
                                        @php
                                            $fechaActual = date('Y-m-d');
                                            $contador = 0;
                                        @endphp
                                        @foreach($representantes as $representante)
                                            <tr>
                                                <td style="display:none">{{$representante->id}}</td>
                                                <td style="color: #000000;"><b>Citado</b></td>
                                                <td>{{$representante->nombre}} {{$representante->primer_apellido}} {{$representante->segundo_apellido}}</td>
                                                <td>{{ $representante->notificacion }}</td>
                                                <td>{{ $representante->estatus }}</td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            @php $contador++; @endphp
                                        @endforeach       
                                    </tbody> 
                                </table>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h4 class="text-center">Concepto de Pago</h4>
                                </div>
                            </div>
                            <table class="table table-striped mt-1">
                                            <thead style="background-color: #4A001F;">
                                                <tr> 
                                                    <th style="display:none">ID</th>
                                                    <th style="color: #ffff;">Tipo pago</th>
                                                    <th style="color: #ffff;">Monto</th>
                                                    <!--th style="color: #ffff;">Acciones</!--th-->
                                                    <th style="color: #ffff;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($conceptos as $concepto)
                                                    <tr>
                                                    <td  style="display:none">{{$concepto->id}}</td>
                                                        <td>{{ $concepto->descripcion}}</td>
                                                        @php
                                                            $esReinstalacionConcl = (($conciliadores['conclucion'] ?? null) === 'Reinstalacion') || (($conclucion ?? null) === 'Reinstalacion');
                                                            $esMontoReinstalacionConcepto = $esReinstalacionConcl && is_numeric($concepto->monto) && (float) $concepto->monto == 0.0;
                                                        @endphp
                                                        @if($esMontoReinstalacionConcepto)
                                                            <td>Reinstalación</td>
                                                        @elseif($concepto->monto)
                                                            <td>${{ number_format($concepto->monto,2) }}</td>
                                                        @else
                                                            <td>No Aplica</td>
                                                        @endif
                                                        <td>
                                                            @if($concepto->id)
                                                                <form method="POST" action="{{ route('concepto_eliminar_pago', $concepto->id) }} ">
                                                                    @csrf
                                                                    <input type="hidden" name="_method" value="DELETE">
                                                                    <!--button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</!--button-->
                                                                </form>
                                                            @elseif(isset($concepto->session_index))
                                                                <form method="POST" action="{{ route('eliminar_item_sesion', $id) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="type" value="concepto">
                                                                    <input type="hidden" name="index" value="{{ $concepto->session_index }}">
                                                                    <!--button class="btn btn-secondary" style="background-color:#6c757d; border-color:#6c757d;" type="submit">Quitar (V)</!--button-->
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $contador++; @endphp
                                                @endforeach       
                                            </tbody> 
                            </table>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h4 class="text-center">Deducciones</h4>
                                </div>
                            </div>
                            <table class="table table-striped mt-1">
                                            <thead style="background-color: #4A001F;">
                                                <tr> 
                                                    <th style="display:none">ID</th>
                                                    <th style="color: #ffff;">Tipo pago</th>
                                                    <th style="color: #ffff;">Monto</th>
                                                    <!--th style="color: #ffff;">Acciones</!--th-->
                                                    <th style="color: #ffff;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($deducciones as $concepto)
                                                    <tr>
                                                    <td  style="display:none">{{$concepto->id}}</td>
                                                        <td>{{ $concepto->descripcion}}</td>
                                                        <td>${{ number_format($concepto->monto,2) }}</td>
                                                        <td>
                                                            @if($concepto->id)
                                                                <form method="POST" action="{{ route('eliminar_deduccion_audiencia', $concepto->id) }} ">
                                                                    @csrf
                                                                    <input type="hidden" name="_method" value="DELETE">
                                                                    <!--button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</!--button-->
                                                                </form>
                                                            @elseif(isset($concepto->session_index))
                                                                <form method="POST" action="{{ route('eliminar_item_sesion', $id) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="type" value="deduccion">
                                                                    <input type="hidden" name="index" value="{{ $concepto->session_index }}">
                                                                    <!--button class="btn btn-secondary" style="background-color:#6c757d; border-color:#6c757d;" type="submit">Quitar (V)</!--button-->
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $contador++; @endphp
                                                @endforeach       
                                            </tbody> 
                            </table>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <h4 class="text-center">Pagos</h4>
                                    </div>
                                </div>
                            <table class="table table-striped mt-1">
                                            <thead style="background-color: #4A001F;">
                                                <tr> 
                                                    <th style="display:none">ID</th>
                                                    <th style="color: #ffff;">Fecha y Hora</th>
                                                    <th style="color: #ffff;">Descripción</th>
                                                    <th style="color: #ffff;">Monto</th>
                                                    <!--th style="color: #ffff;">Acciones</-th-->
                                                    <th style="color: #ffff;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pagos as $pago)
                                                    <tr>
                                                        <td  style="display:none">{{$pago->id}}</td>
                                                        <td> {{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d/m/y') }}<br>{{ \Carbon\Carbon::parse($pago->hora)->format('H:i') }} hrs.</td>
                                                        <td>{{ $pago->descripcion}}</td>
                                                        @php
                                                            $esReinstalacionConcl = (($conciliadores['conclucion'] ?? null) === 'Reinstalacion') || (($conclucion ?? null) === 'Reinstalacion');
                                                            $esMontoReinstalacionPago = $esReinstalacionConcl && is_numeric($pago->monto) && (float) $pago->monto == 0.0;
                                                        @endphp
                                                        @if($esMontoReinstalacionPago)
                                                            <td>Reinstalación</td>
                                                        @elseif($pago->monto)
                                                            <td>${{ number_format($pago->monto,2) }}</td>
                                                        @else
                                                            <td>No Aplica</td>
                                                        @endif
                                                        <td>
                                                            @if($pago->id)
                                                                <form method="POST" action="{{ route('pago_eliminar_pago', $pago->id) }} ">
                                                                    @csrf
                                                                    <input type="hidden" name="_method" value="DELETE">
                                                                    <!--button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</!--button-->
                                                                </form>
                                                            @elseif(isset($pago->session_index))
                                                                <form method="POST" action="{{ route('eliminar_item_sesion', $id) }}">
                                                                    @csrf
                                                                    <input type="hidden" name="type" value="pago">
                                                                    <input type="hidden" name="index" value="{{ $pago->session_index }}">
                                                                    <!--button class="btn btn-secondary" style="background-color:#6c757d; border-color:#6c757d;" type="submit">Quitar (V)</!--button-->
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $contador++; @endphp
                                                @endforeach       
                                            </tbody> 
                            </table>
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('terminar_audiencia')}}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $id }}">
                                <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
                                <div class="row">
                                    <div id="justificacion"><br>
                                        <div class="col-xs-12 col-sm-12 col-md-12"  style="border:1px solid black;">
                                            <div class="form-group">
                                                <label for="name">RESOLUCIÓN PRIMERA MANIFESTACIÓN</label>
                                                <textarea name="primera" class="form-control" readonly> {{$conciliadores->resolicion_primera}}</textarea>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    </div><br>
                                    <div id="justificacion"><br>
                                        <div class="col-xs-12 col-sm-12 col-md-12" style="border:1px solid black;">
                                            <div class="form-group">
                                                <label for="name">RESOLUCIÓN JUSTIFICACIÓN PROPUESTA</label>
                                                <textarea name="justificacion" class="form-control" readonly>{{$conciliadores->resolicion_justificacion}}</textarea>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    </div><br>
                                    <div id="segunda" ><br>
                                        <div class="col-xs-12 col-sm-12 col-md-12" style="border:1px solid black;">
                                            <div class="form-group">
                                                <label for="name">RESOLUCIÓN SEGUNDA MANIFESTACIÓN</label>
                                                <textarea name="segunda" class="form-control" readonly>{{$conciliadores->resolicion_segunda}}</textarea>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6"><br>
                                            <label for="name">Final de la audiencia</label>
                                            <select style="pointer-events: none; background-color: #eee;" id="conclucion" name="conclucion" class="form-control">
                                                <option>Seleccione</option>
                                                <option value="Conciliacion" {{ $conciliadores["conclucion"] == "Conciliacion" ? "selected" : '' }}>Hubo Convenio</option>
                                                <option value="Reinstalacion" {{ $conciliadores["conclucion"] == "Reinstalacion" ? "selected" : '' }}>Reinstalación</option>
                                                <option value="No conciliacion" {{ $conciliadores["conclucion"] == "No conciliacion" ? "selected" : '' }}>No hubo Convenio</option>
                                                <!--option value="Archivada por incomparecencia" {{ $conciliadores["conclucion"] == "Archivada por incomparecencia" ? "selected" : '' }}>Archivar</!--option-->
                                            </select>
                                        </div>
                                    </div>
                                    <div id="dias" class="row home-shape">
                                        <div class="col-xs-12 col-sm-12 col-md-2"><br>
                                            <div class="form-group">
                                                <label for="name">Días de vacaciones</label>
                                                <input type="number" name="vacaciones" class="form-control" value="{{ $conciliadores["vacaciones"] }}" readonly> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2"><br>
                                            <div class="form-group">
                                                <label for="name">Días de Aguinaldo</label>
                                                <input type="number" name="aguinaldo" class="form-control" value="{{ $conciliadores["aguinaldo"] }}" readonly> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2"><br>
                                            <div class="form-group">
                                                <label for="name">Otros</label>
                                                <input type="text" name="otros" class="form-control" value="{{ $conciliadores["otros"] }}" readonly> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                                            <div class="form-group">
                                                <label for="name">Horario laboral</label>
                                                <input type="text" name="horario" class="form-control" value="{{ $conciliadores["horario"] }}" readonly> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                                            <div class="form-group">
                                                <label for="name">Horario de comida</label>
                                                <input type="text" name="comida" class="form-control" value="{{ $conciliadores["comida"] }}" readonly> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                                            <div class="form-group">
                                                <label for="name">Pena convencional</label>
                                                <input type="text" name="pena_convencional" class="form-control" value="{{ $conciliadores["pena_convencional"] }}" readonly> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                                            <div class="form-group">
                                                <label for="name">Dirección que aparece en convenio</label>
                                                <input type="text" name="direccion_convenio" class="form-control" value="{{ $conciliadores["direccion_convenio"] }}" readonly> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="pagos" class="row home-shape">
                                        <div class="col-xs-12 col-sm-12 col-md-12"></div>
                                        <div class="col-xs-12 col-sm-6 col-md-12">
                                            <!--button id="addRow" type="button" class="btn btn-info">Agregar Concepto de Pago</!--button-->
                                        </div>                                        
                                        <div id="newRow"></div>
                                       
                                        <div class="col-xs-12 col-sm-6 col-md-12"><br>
                                            <!--button id="addRetencion" type="button" class="btn btn-info">Agregar Deducción</!--button-->
                                        </div>
                                        
                                        <div id="newRowDeduccion"></div>

                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div id="div_pagos_diferidos1"><br>
                                                <!--button id="addPago" type="button" class="btn btn-info">Agregar Pago</!--button-->
                                                <div id="newRowaPago"></div>
                                            </div>
                                        </div>
                                       
                                        <div id="div_pagos_diferidos"></div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <h4 class="text-center" style="margin-top:20px;">Total a pagar:</h4>
                                            <h3 id="totalCalculado" class="text-center" style="color:green;">$0.00</h3>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-6"><br>
                                                <label for="name">Tipo de audiencia</label>
                                                <select style="pointer-events: none; background-color: #eee;" name="tipo_audiencia" class="form-control" required> 
                                                    <option value="">Seleccione</option>
                                                    <option value="Presencial" {{ $conciliadores["tipo_audiencia"] == "Presencial" ? "selected" : '' }}>Presencial</option>
                                                    <option value="Virtual" {{ $conciliadores["tipo_audiencia"] == "Virtual" ? "selected" : '' }}>Virtual</option>
                                                </select>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                        <br><button id="btn-terminar" type="submit" class="btn btn-success" name="bandera" value="1">Terminar</button>
                                    </div>
                                    <!--div class="col-xs-12 col-sm-12 col-md-2">
                                        <br><button id="btn-terminar" type="submit" class="btn btn-success" name="bandera" value="2">Actualizar</button>
                                    </!--div-->
                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                        <br><button id="btn-convenio1" type="button" class="btn btn-info" name="bandera" value="3" target="_blank">Convenio</button>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-2">
                                        <br><button id="btn-acta" type="button" class="btn btn-info" name="bandera" value="4">Acta de Audiencia</button>
                                    </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2">
                                            <br><a href="{{ route('audiencias.parte3', $id) . '?bandera=5&audiencia_id=' . request()->query('audiencia_id') }}" class="btn btn-danger" name="bandera" value="5">Regresar</a>
                                        </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<!-- Modal Solicitantes -->
<div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('editar_solicitud_audiencia')}}">
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
                                <label for="name">Nombre(s) y Apellidos del Solicitante (*) </label>
                                <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitante["nombre"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo nombre es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">CURP del Solicitante (*)</label>
                                <input type="text" name="curp" id="curp_input" oninput="validarInput(this)"class="form-control" value="<?=$solicitante["curp"];?>" required> 
                                <pre id="resultado"></pre>
                                <div class="invalid-feedback">
                                    El campo curp es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">RFC del Solicitante (*)</label>
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
                                <label for="name">Puesto (*)</label>
                                <input type="text" class="form-control" name="puesto" value="<?=$solicitante["puesto"];?>" oninput="this.value = this.value.toUpperCase()" required> 
                                <div class="invalid-feedback">
                                    El campo puesto es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Frecuencia de Pago (*)</label>
                                <select name="periodo_pago" class="form-control" value="<?=$solicitante["periodo_pago"];?>" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Diario" {{ $solicitante['periodo_pago'] == 'Diario' ? "selected" : '' }}>DIARIO</option>
                                    <option value="Semanal" {{ $solicitante['periodo_pago'] == 'Semanal' ? "selected" : '' }}>SEMANAL</option>
                                    <option value="Quincenal" {{ $solicitante['periodo_pago'] == 'Quincenal' ? "selected" : '' }}>QUINCENAL</option>
                                    <option value="Mensual" {{ $solicitante['periodo_pago'] == 'Mensual' ? "selected" : '' }}>MENSUAL</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo frecuencia de pagos es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Salario (*)</label>
                                <input type="text" name="pago" class="form-control" value="<?=$solicitante["pago"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo salario es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Cantidad total de horas trabajadas por semana (*)</label>
                                <input type="number" name="horas" class="form-control" value="<?=$solicitante["horas_semana"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo cantidad de horas trabajadas es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="check_fecha">¿Laboras actualmente?</label>
                                <input type="checkbox" id="check_fecha" name="labora" {{ $solicitante['labora'] == 'Si' ? 'checked' : '' }} />
                            </div>  
                        </div>    
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Fecha de Ingreso (*)</label>
                                <input type="date" name="fecha_ingreso" class="form-control" value="<?=$solicitante["fecha_ingreso"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo fecha de ingreso es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Jornada (*)</label>
                                <select name="jornada" class="form-control" value="<?=$solicitante["jornada"];?>" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Diurna" {{ $solicitante['jornada'] == 'Diurna' ? "selected" : '' }}>DIURNA</option>
                                    <option value="Nocturna" {{ $solicitante['jornada'] == 'Nocturna' ? "selected" : '' }}>NOCTURNA</option>
                                    <option value="Mixta" {{ $solicitante['jornada'] == 'Mixta' ? "selected" : '' }}>MIXTA</option>
                                </select>
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
<!-- Modal Citados -->

{{--<div class="modal fade" id="modalCitados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Representantes Legales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <form method="POST" action="{{ route('seleccionar_abogado_audiencia') }} ">
                    @csrf
                    <input type="hidden" id="modal-id" name="citado" value="">
                    <input type="hidden" name="solicitud" value="{{$solicitud->id}}">
                    <table id="tabla1" class="table-striped" style="width:100%">
                        <thead style="background-color: #4A001F;">   
                            <!--<th style="display: none;">ID</th>-->
                            <th style="color: #fff;">Folio</th>
                            <th style="color: #fff;">Nombre</th>
                            <th style="color: #fff;">RFC</th>
                            <th style="color: #fff;">Empresa</th>
                            <th style="color: #fff;">Acciones</th>
                        </thead>
                        <tbody class="contenidobusqueda">
                            @foreach($abogados as $abogado)
                                <tr>
                                    <td>{{$abogado->idAbogado}}</td>
                                    <td>{{$abogado->nombres}} {{$abogado->primer_apellido}} {{$abogado->segundo_apellido}}</td>
                                    <td>{{$abogado->rfc}}</td>
                                    <td>{{$abogado->empresa}}</td>
                                    <td>
                                        <button class="btn btn-info" onclick=editar_rol(); type="submit" name="abogado" value="{{$abogado->idAbogado}}">Seleccionar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalAgregarCitados">Agregar en representación</button>
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalAgregarDerecho">Agregar por propio derecho</button>
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalActualizaCitados">Actualizar citado</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>--}}
<!-- Modal Agregar Citados -->

<div class="modal fade" id="ModalArchivar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('archivar_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-archivar" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo del archivo de audiencia</h5>
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
<div class="modal fade" id="ModalReagendar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('reagendar_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-reagendar" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Fecha de la reagenda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="date" class="form-control" name="fecha">
                    <input type="time" class="form-control" name="hora">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="ModalIncopentencia" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('incopentencia_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-incopentencia" name="id" value="">
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
<div class="modal fade" id="modalAgregarDerecho" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' enctype="multipart/form-data" name="AgregarPersonaFisica" id="AgregarPersonaFisica" action="{{route('insertar_citado_PF')}}">
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
                                <label>Segundo apellido</label>
                                <input type="text" name="segundo_apellido" class="form-control" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="name">Tipo de identificación (*)</label>
                                <select name="identificacionAlta" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="ine">INE</option>
                                    <option value="pasaporte">PASAPORTE</option>
                                    <option value="cedula">CÉDULA PROFESIONAL</option>
                                    <option value="licencia">LICENCIA PARA CONDUCIR</option>
                                    <option value="otros">OTROS</option>
                                </select>
                                <div class="invalid-feedback">
                                    El tipo de identificaión es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Identificación oficial</label>
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
    <form class='needs-validation novalidate'  method='POST' name="AgregarPersonaFisica" id="AgregarPersonaFisica" action="{{route('actualiza_citados_audiencia')}}">
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

<div id="submit_loader" style="display:none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@section('scripts')
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
            document.getElementById('modal-id-citado').value = id;
        });

        $( document ).ready(function() {
            // Agregar registro
            $("#addRow").click(function () {
                var html = '';
                html += '<div id="inputFormRow1" class="col-xs-12 col-sm-6 col-md-12">';

                // Tipo de pago
                html +='<div class="col-xs-12 col-sm-12 col-md-12">';
                    html +='<div class="form-group">';
                    html +='<label for="confirm-password"><br>Prestación</label>';
                    html +='<select class="form-control tipo-pago-select" name="tipo_pago[]" >';
                    html +='<option value="">Seleccione</option>';
                    html +='<option value="Aguinaldo">Días de aguinaldo</option>';
                    html +='<option value="Días de sueldo">Días de sueldo</option>';
                    html +='<option value="Vacaciones">Días de vacaciones</option>';
                    html +='<option value="Prima Vacacional">Prima vacacional</option>';
                    html +='<option value="Gratificación A">Graficación A (Con base al salario integrado)</option>';
                    html +='<option value="Gratificación B">Graficación B (20 Días por año cumplido)</option>';
                    html +='<option value="Gratificación C">Graficación C (Prima de antigüedad topada)</option>';
                    html +='<option value="Gratificación D">Graficación D (Incluye cualquier otra prestación)</option>';
                    html +='<option value="Gratificación E">Graficación E (Prestaciones en especie)</option>';
                    html +='<option value="Gratificación F">Graficación F (Reconocimiento de derechos)</option>';
                    html +='<option value="Otras">Otros concepto de pago</option>';
                    html +='</select>';
                    // Campo para escribir otra prestación (solo si se selecciona "Otras")
                        html += '<div class="otra-prestacion-input" style="display: none; margin-top: 10px;">';
                        html += '<input type="text" class="form-control" name="otra_prestacion[]" placeholder="Especifique la prestación" />';
                        html += '</div>';
                        html +='<div class="invalid-feedback">El tipo de pago es obligatorio.</div>';
                        html += '</div> </div>';

                // Monto a pagar
                html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                html += '<div class="form-group">';
                html += '<label for="password">Monto a pagar</label>';
                html +='<input type="text" class="form-control" name="monto_pago[]" oninput="validarNumero(this)" placeholder="$"  placeholder="$ Solo números y puntos">';
                html += '<div class="invalid-feedback">La Dirección es obligatoria.</div>';
                html += '</div> </div>';

                html += '<div class="input-group-append">';
                html += '<button class="removeRow btn btn-danger" type="button">Borrar</button>';
                html += '</div>';
                html += '</div>';

            $('#newRow').append(html);
        });

        // Borrar concepto
        $(document).on('click', '.removeRow', function () {
            $(this).closest('.col-xs-12').remove();
        });
        // Agregar pago
        $("#addPago").click(function () {
                var html = '';
                html += '<div id="inputFormRow2" class="col-xs-12 col-sm-6 col-md-12">';
                
                //TIPO DE PAGO
                html +='<div class="col-xs-12 col-sm-12 col-md-12">';
                html +='<div class="form-group">';

                //DÍA A PAGAR
                html +='<div class="col-xs-12 col-sm-12 col-md-12">';
                html +='<div class="form-group">';
                html +='<label for="confirm-password"><br>Días de pago</label>';
                html +='<input type="date" class="form-control" name="dias_pagos[]" >';
                html +='</div> </div>';                                
                
                //HORARIO A PAGAR
                html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                html += '<div class="form-group">';
                html += '<label for="password">Hora de pago</label>';
                html +='<input type="text" class="form-control" name="hora_pagos[]"  oninput="this.value = this.value.toUpperCase()" >';
                html += '<div class="invalid-feedback">';
                html += 'La Dirección es obligatoria.';
                html += '</div> </div> </div>';

                //MONTO A PAGAR
                html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                html += '<div class="form-group">';
                html += '<label for="password">Monto a pagar</label>';
                html +='<input type="text" class="form-control" name="monto_pagos[]"  oninput="validarNumero(this)" placeholder="$"  placeholder="$ Solo números y puntos" >';
                html += '<div class="invalid-feedback">';
                html += 'La Dirección es obligatoria.';
                html += '</div> </div> </div>';

                //DESCRIPCIÓN DE PAGO
                html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                html += '<div class="form-group">';
                html += '<label for="password">Descripción</label>';
                html +='<input type="text" class="form-control numero_pago" name="descripcion_pagos[]"  readonly >';
                html += '<div class="invalid-feedback">';
                html += 'La Dirección es obligatoria.';
                html += '</div> </div> </div>';

                html += '<div class="input-group-append">';
                html += '<button class="removeRow2 btn btn-danger" type="button">Borrar</button>';
                html += '</div>';
                html += '</div>';

            $('#newRowaPago').append(html);
            actualizaNumeroPago();
        });

        function actualizaNumeroPago() {
            let pagos = $('.numero_pago');
            if (pagos.length === 1) {
                pagos.eq(0).val("Cumplimiento total de convenio");
            } else {
                pagos.each(function(index) {
                   $(this).val("Cumplimiento " + (index + 1));
                });
            }
        }

        // Borrar pago
        $(document).on('click', '.removeRow2', function () {
            $(this).closest('.col-xs-12').remove();
        });

         // Agregar deducción
        $("#addRetencion").click(function () {
                var html = '';
                html += '<div id="inputFormRow3" class="row">';
                
                //TIPO DE PAGO
                html +='<div class="col-xs-12 col-sm-12 col-md-12"><br>';
                //html +='<div class="form-group">';

                    //DESCRIPCIÓN DE PAGO
                    html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                    html += '<div class="form-group">';
                    html += '<label for="password">Descripción</label>';
                    html +='<input type="text" class="form-control" name="descripcion_deduccion[]"  oninput="this.value = this.value.toUpperCase()" >';
                    html += '<div class="invalid-feedback">';
                    html += 'La Descripción es obligatoria.';
                    html += '</div> </div> </div>';

                    //MONTO A PAGAR
                    html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                    html += '<div class="form-group">';
                    html += '<label for="password">Monto a pagar</label>';
                    html +='<input type="text" class="form-control" name="monto_deduccion[]"  oninput="validarNumero(this)" placeholder="$"  placeholder="$ Solo números y puntos" >';
                    html += '<div class="invalid-feedback">';
                    html += 'El monto es obligatorio.';
                    html += '</div> </div> </div>';

                    html += '<div class="input-group-append">';
                    html += '<button class="removeRow3 btn btn-danger" type="button">Borrar</button>';
                    html += '</div>';

                html += '</div>';

            $('#newRowDeduccion').append(html);
        });

        // Borrar pago
        $(document).on('click', '.removeRow3', function () {
            $(this).closest('.col-xs-12').remove();
        });

    });

        function validarNumero(input) {
            // La expresión regular permite cualquier número (0-9) y un solo punto (.)
            // El 'g' al final asegura que se reemplace globalmente
            let valor = input.value;
            input.value = valor.replace(/[^0-9.]/g, '');

            // Esta parte se encarga de que solo haya un punto en el valor
            let partes = input.value.split('.');
            if (partes.length > 2) {
                input.value = partes[0] + '.' + partes.slice(1).join('');
            }
        }
        //Muestra un input cuando en prestaciones se selecciona la opción Otros concepto de pago
        $(document).on('change', '.tipo-pago-select', function () {
            var selected = $(this).val();
            var container = $(this).closest('.form-group').find('.otra-prestacion-input');

            if (selected === 'Otras') {
                container.show();
                container.find('input').attr('required', true);
            } else {
                container.hide();
                container.find('input').val('').removeAttr('required');
            }
        });
        //Muestra el total a pagar en base a las prestaciones y deducciones capturadas
        function formatoMoneda(num) {
            return num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    
        function calcularTotal() {
            let totalPrestaciones = 0;
            let totalDeducciones = 0;

            // NUEVAS PRESTACIONES
            $('input[name="monto_pago[]"]').each(function () {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) totalPrestaciones += val;
            });

            // NUEVAS DEDUCCIONES
            $('input[name="monto_deduccion[]"]').each(function () {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) totalDeducciones += val;
            });

            // PRESTACIONES YA GUARDADAS
            @foreach($conceptos as $c)
                totalPrestaciones += {{ floatval($c->monto) }};
            @endforeach

            // DEDUCCIONES YA GUARDADAS
            @foreach($deducciones as $d)
                totalDeducciones += {{ floatval($d->monto) }};
            @endforeach

            let total = totalPrestaciones - totalDeducciones;
            $("#totalCalculado").text('$' + formatoMoneda(total));
        }

        $(document).on('input', 'input[name="monto_pago[]"]', calcularTotal);
        $(document).on('input', 'input[name="monto_deduccion[]"]', calcularTotal);
        $(document).on('click', '.removeRow, .removeRow3', function () {
            setTimeout(calcularTotal, 100);
        });
        calcularTotal();
    </script>

    <!-- Botones Convenio / Acta en vista patronal: solo abren PDF, no envían el formulario -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnConvenio = document.getElementById('btn-convenio1');
            const btnActa = document.getElementById('btn-acta');
            const btnActaMultiple = document.getElementById('btn-acta-multiple');

            if (btnConvenio) {
                btnConvenio.addEventListener('click', function (e) {
                    e.preventDefault();
                    // En vista patronal ya no usamos checkboxes: todos los citados aparecen en convenio
                    let urlPdf = "{{ ($conciliadores['conclucion'] ?? null) === 'Reinstalacion' ? route('PDFconvenioreinstalacion', $id) : route('PDFconveniosolicitud', $id) }}";
                    let audienciaId = document.querySelector('input[name="audiencia_id"]')?.value || document.querySelector('input[name="id_audiencia_recurso"]')?.value || '{{ request("audiencia_id") }}';
                    if (audienciaId) {
                        urlPdf += '?audiencia_id=' + audienciaId;
                    }
                    window.open(urlPdf, '_blank');
                });
            }

            if (btnActa) {
                btnActa.addEventListener('click', function (e) {
                    e.preventDefault();
                    let audienciaId = document.querySelector('input[name="audiencia_id"]')?.value || document.querySelector('input[name="id_audiencia_recurso"]')?.value || '{{ request("audiencia_id") }}';
                    let urlPdfActa = "{{ route('VerPDFAudiencia', $id) }}";
                    if (audienciaId) {
                        urlPdfActa += '?audiencia_id=' + audienciaId;
                    }
                    window.open(urlPdfActa, '_blank');
                });
            }

            if (btnActaMultiple) {
                btnActaMultiple.addEventListener('click', function (e) {
                    e.preventDefault();
                    $('#modalActaM').modal('show');
                });
            }
        });
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form_roles');
        if (!form) return;

        form.addEventListener('submit', function () {
            $('#submit_loader').show();
        });
    });
</script>

<script src="../../public/assets/js/validaciones.js"></script> 
<script src="../../public/assets/js/poderes/general.js"></script>
@endsection

<div class="modal fade" id="modalEditarPoder" tabindex="-1" aria-labelledby="modalEditarPoderLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarPoderLabel">Editar Datos del Poder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class='needs-validation novalidate' method='POST' action="{{ route('poderes.update' ,$solicitante->poder->idAbogado) }}" enctype='multipart/form-data'>
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">
                    <input type="hidden" name="from_audiencia_patronal" value="1">
                    <input type="hidden" name="solicitud_id" value="{{ $id }}">
                    <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">

                    @php
                        $tipoActual = $solicitante->poder->tipo ?? 'Moral';
                        $tieneRepresentante = $solicitante->poder->reprecentante ?? 'Si';
                    @endphp

                    <div class="row mb-3">
                        <div class="col-xs-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label for="tipoPersonaPoder">Tipo de persona</label>
                                <select name="tipoPersona" id="tipoPersonaPoder" class="form-control">
                                    <option value="Moral" {{ $tipoActual == 'Moral' ? 'selected' : '' }}>Moral</option>
                                    <option value="Fisica" {{ $tipoActual == 'Fisica' ? 'selected' : '' }}>Física</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-4" id="grupoRepresentate" style="{{ $tipoActual == 'Fisica' ? '' : 'display:none;' }}">
                            <div class="form-group">
                                <label for="representatePoder">¿Tiene representante?</label>
                                <select name="representate" id="representatePoder" class="form-control">
                                    <option value="Si" {{ $tieneRepresentante == 'Si' ? 'selected' : '' }}>Sí</option>
                                    <option value="No" {{ $tieneRepresentante == 'No' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE PERSONA MORAL (se habilita cuando tipoPersona = Moral) --}}
                    <div id="bloqueMoral" style="{{ $tipoActual == 'Moral' ? '' : 'display:none;' }}">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <label for="name">Razón Social <span style="color:red;">(*)</span></label>
                                    <input type="text" name="razon" value="{{$solicitante->poder->nombres_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">RFC <span style="color:red;">(*)</span></label>
                                    <input type="text" name="rfc_moral" value="{{$solicitante->poder->rfc_patronal}}" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()"> 
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Giro Comercial <span style="color:red;">(*)</span></label>
                                    <input type="text" name="giro_moral" value="{{$solicitante->poder->giroComercial}}" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>
                            {{-- Domicilio laboral --}}
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h5 class="text-center">Domicilio laboral</h5>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="password">Entidad Federativa</label>
                                    <select id="estado_moral" class="form-control" name="estado_moral" placeholder="*Entidad Federativa">
                                        <option value="">Seleccione</option>
                                        @foreach($estados as $est)
                                            <option value="{{$est['id']}}" {{ $solicitante->poder["estado_patronal"] == $est["id"] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">El campo Estado es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Nombre del Municipio o Alcaldía (*)</label>
                                    <select id="municipio_moral" class="form-control" name="municipio_moral" placeholder="*Municipio">
                                        <option value="">Seleccione</option>
                                        @foreach($municipios as $mun)
                                            <option value="{{$mun['id']}}" {{ $solicitante->poder["municipio_patronal"] == $mun["id"] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">El campo municipio o alcaldía es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Tipo de Vialidad <span style="color:red;">(*)</span></label>
                                    <select name="vialidad_Moral" id="vialidad_Moral" class="form-control" placeholder="*Vialidad">
                                        <option value="">SELECCIONE</option>
                                        <option value="AMPLIACIÓN"  {{ $solicitante->poder["tipo_vialidad_patronal"] == "AMPLIACIÓN" ? "selected" : '' }}>Ampliación</option>
                                        <option value="ANDADOR"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "ANDADOR" ? "selected" : '' }}>Andador</option>
                                        <option value="AUTOPISTA"   {{ $solicitante->poder["tipo_vialidad_patronal"] == "AUTOPISTA" ? "selected" : '' }}>Autopista</option>
                                        <option value="AVENIDA"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "AVENIDA" ? "selected" : '' }}>Avenida</option>
                                        <option value="BOULEVARD"   {{ $solicitante->poder["tipo_vialidad_patronal"] == "BOULEVARD" ? "selected" : '' }}>Boulevard</option>
                                        <option value="CALLE"       {{ $solicitante->poder["tipo_vialidad_patronal"] == "CALLE" ? "selected" : '' }}>Calle</option>
                                        <option value="CALLEJÓN"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "CALLEJÓN" ? "selected" : '' }}>Callejón</option>
                                        <option value="CALZADA"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "CALZADA" ? "selected" : '' }}>Calzada</option>
                                        <option value="CARRETERA"   {{ $solicitante->poder["tipo_vialidad_patronal"] == "CARRETERA" ? "selected" : '' }}>Carretera</option>
                                        <option value="CERRADA"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "CERRADA" ? "selected" : '' }}>Cerrada</option>
                                        <option value="CIRCUITO"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "CIRCUITO" ? "selected" : '' }}>Circuito</option>
                                        <option value="CIRCUNVALACIÓN"  {{ $solicitante->poder["tipo_vialidad_patronal"] == "CIRCUNVALACIÓN" ? "selected" : '' }}>Circunvalación</option>
                                        <option value="CONTINUACIÓN"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "CONTINUACIÓN" ? "selected" : '' }}>Continuación</option>
                                        <option value="CORREDOR"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "CORREDOR" ? "selected" : '' }}>Corredor</option>
                                        <option value="DIAGONAL"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "DIAGONAL" ? "selected" : '' }}>Diagonal</option>
                                        <option value="EJE VIAL"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "EJE VIAL" ? "selected" : '' }}>Eje vial</option>
                                        <option value="PERIFÉRICO"  {{ $solicitante->poder["tipo_vialidad_patronal"] == "PERIFÉRICO" ? "selected" : '' }}>Periférico</option>
                                        <option value="PROLONGACIÓN" {{ $solicitante->poder["tipo_vialidad_patronal"] == "PROLONGACIÓN" ? "selected" : '' }}>Prolongación</option>
                                        <option value="RETORNO"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "RETORNO" ? "selected" : '' }}>Retorno</option>
                                        <option value="VIADUCTO"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "VIADUCTO" ? "selected" : '' }}>Viaducto</option>
                                    </select>
                                    <div class="invalid-feedback">El campo vialidad es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Nombre de la Vialidad (*)</label>
                                    <input type="text" name="vialidad_calleMoral" value="{{$solicitante->poder->vialidad_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                    <div class="invalid-feedback">El campo vialidad o calle es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Colonia <span style="color:red;">(*)</span></label>
                                    <input type="text" class="form-control" name="colonia_moral" value="{{$solicitante->poder->colonia_patronal}}" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El domicilio es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                    <input type="text" class="form-control" value="{{$solicitante->poder->num_ext_patronal}}" name="num_ext_moral"  oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El domicilio es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Núm. Int.</label>
                                    <input type="text" class="form-control" value="{{$solicitante->poder->mun_int_patronal}}" name="num_int" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El domicilio es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Código Postal <span style="color:red;">(*)</span></label>
                                    <input type="text" class="form-control" name="cp_moral" value="{{$solicitante->poder->cp_patronal}}" minlength="5" maxlength="5" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El domicilio es obligatoria.</div>
                                </div>
                            </div>
                            {{-- Información del Representante Legal --}}
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
                                    <input type="text" name="nombre_representante_Moral" value="{{$solicitante->poder->nombre_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                    <input type="text" name="primer_Moral" value="{{$solicitante->poder->primer_apellido_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El primer apellido es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                    <input type="text" name="segundo_Moral" value="{{$solicitante->poder->segundo_apellido_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El segundo apellido es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="">CURP</label>
                                    <input type="text" class="form-control" name="curp_moral" value="{{$solicitante->poder->curp_representante}}" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">La CURP es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                    <select name="sexo_Moral" id="sexo_Moral" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Femenino"    {{ $solicitante->poder["sexo_representante"] == "Femenino" ? "selected" : '' }}>Femenino</option>
                                        <option value="Masculino"   {{ $solicitante->poder["sexo_representante"] == "Masculino" ? "selected" : '' }}>Masculino</option>
                                        <option value="Prefiero no responder">Prefiero no responder</option>
                                    </select>
                                    <div class="invalid-feedback">El tipo de persona es obligatorio.</div>
                                </div>
                            </div>
                            {{-- Datos de contacto --}}
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h5 class="text-center">Datos de contacto</h5>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="">Correo electrónico <span style="color:red;">(*)</label>
                                    <input type="email" class="form-control" name="correo_Moral" value="{{$solicitante->poder->correo_representante}}">
                                    <div class="invalid-feedback">El Correo electrónico es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="">Teléfono <span style="color:red;">(*)</label>
                                    <input type="text" class="form-control" name="telefono_Moral" value="{{$solicitante->poder->numero_representante}}" maxlength="10" pattern="[0-9]+">
                                    <div class="invalid-feedback">El telefono es obligatorio.</div>
                                </div>
                            </div>
                            {{-- Documentos de personeria --}}
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
                                        <option value="Carta Poder" {{ $solicitante->poder["tipo_documento_representante"] == "Carta Poder" ? "selected" : '' }}>Carta Poder</option>
                                        <option value="Instrumento Notarial" {{ $solicitante->poder["tipo_documento_representante"] == "Instrumento Notarial" ? "selected" : '' }}>Instrumento Notarial</option>
                                    </select>
                                    <div class="invalid-feedback">El campo es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                    <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_expedicicion_Moral" value="{{$solicitante->poder->fechaRegistro}}">
                                    <div class="invalid-feedback">La fecha es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Fecha vigencia</label>
                                    <input type="date" class="form-control" aria-describedby="basic-addon1" name="fecha_vigencia_Moral" value="{{$solicitante->poder->fechaVigencia}}">
                                    <div class="invalid-feedback">La fecha es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <label for="">Descripción del documento que acredite la personaria</label>
                                    <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_Moral">{{$solicitante->poder->descipcion_poder}}</textarea>
                                    <div class="invalid-feedback">La descripción es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Identificación Oficial  <span style="color:red;">(*)</span></label>
                                    <select name="tipo_identificacion_Moral" class="form-control">
                                        <option value="">Seleccione el tipo de indentificación</option>
                                        <option value="Credencial de elector" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de elector" ? "selected" : '' }}>Credencial de Elector</option>
                                        <option value="Pasaporte" {{ $solicitante->poder["tipo_identificacion"] == "Pasaporte" ? "selected" : '' }}>Pasaporte</option>
                                        <option value="Cédula profesional" {{ $solicitante->poder["tipo_identificacion"] == "Cédula profesional" ? "selected" : '' }}>Cédula Profesional</option>
                                        <option value="Licencia de conducir" {{ $solicitante->poder["tipo_identificacion"] == "Licencia de conducir" ? "selected" : '' }}>Licencia de Conducir</option>
                                        <option value="Credencial de inapam" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de inapam" ? "selected" : '' }}>Credencial de INAPAM</option>
                                        <option value="Cartilla militar" {{ $solicitante->poder["tipo_identificacion"] == "Cartilla militar" ? "selected" : '' }}>Cartilla Militar</option>
                                        <option value="Documento migratorio" {{ $solicitante->poder["tipo_identificacion"] == "Documento migratorio" ? "selected" : '' }}>Documento Migratorio</option>
                                        <option value="Constancia de identidad" {{ $solicitante->poder["tipo_identificacion"] == "Constancia de identidad" ? "selected" : '' }}>Constancia de Identidad</option>
                                        <option value="Otro" {{ $solicitante->poder["tipo_identificacion"] == "Otro" ? "selected" : '' }}>Otros</option>
                                    </select>
                                    <div class="invalid-feedback">Este campo identificación es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">Núm de identificación <span style="color:red;">(*)</span></label>
                                    <input type="text" name="num_identificacion_Moral" class="form-control" value="{{$solicitante->poder->num_identificacion}}" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El campo núm. de identificación es obligatorio.</div>
                                </div>
                            </div>
                            {{-- Documentos --}}
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h4 class="text-center" style="color:#CEA845">Documentos</h4>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label>*Acta Constitutiva</label><br>
                                    <input type="file" name="documentoIne_Moral" id="documentoIne_Moral" class="form-control" accept=".pdf">
                                    <a target="_blank" class="btn btn-primary mt-1" href="../../storage/app/documentos_abogados/{{$solicitante->poder->ineDocumento}}">Existente</a>
                                    <div class="invalid-feedback">La Identificación es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label>*Identificación del Representante Legal</label><br>
                                    <input type="file" name="documentoRepresentacion_Moral" id="documentoRepresentacion_Moral" class="form-control" accept=".pdf">
                                    @if($solicitante->poder->representacionDocumento != NULL)
                                        <a target="_blank" class="btn btn-primary mt-1" href="../../storage/app/documentos_abogados/{{$solicitante->poder->representacionDocumento}}">Existente</a>
                                    @endif
                                    <div class="invalid-feedback">El documento de representación es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label>*Documento que acredite la personería</label><br>
                                    <input type="file" name="documentoPoder" id="documentoPoder" class="form-control" accept=".pdf">
                                    @if($solicitante->poder->cedulaDocumento != NULL)
                                        <a target="_blank" class="btn btn-primary mt-1" href="../../storage/app/documentos_abogados/{{$solicitante->poder->cedulaDocumento}}">Existente</a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label>Anexo (Documentos Complementarios)</label><br>
                                    <input type="file" name="documentoAnexo" class="form-control" accept=".pdf">
                                    @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                        <a target="_blank" class="btn btn-primary mt-1" href="../../storage/app/documentos_abogados/{{$solicitante->poder->anexo_documeto}}">Existente</a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Validación</label>
                                    <select name="validacion" class="form-control">
                                        <option value="">Seleccionar</option>
                                        <option value="Validado" {{ $solicitante->poder["estatus"] == "Validado" ? "selected" : '' }}>Validar</option>
                                        <option value="Pendiente" {{ $solicitante->poder["estatus"] == "Pendiente" ? "selected" : '' }}>Rechazar</option>
                                    </select>
                                    <div class="invalid-feedback">El campo es obligatorio.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE PERSONA FÍSICA (se habilita cuando tipoPersona = Fisica) --}}
                    <div id="bloqueFisica" style="{{ $tipoActual == 'Fisica' ? '' : 'display:none;' }}">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label for="name">Nombre(s) del Empleador<span style="color:red;">(*)</span></label>
                                    <input type="text" name="nombre_pF" value="{{$solicitante->poder->nombres_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                    <input type="text" name="primero_PF" value="{{$solicitante->poder->primer_apellido_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El primer apellido es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                    <input type="text" name="segundo_Pf" value="{{$solicitante->poder->segundo_apellido_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El segundo apellido es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="">CURP <span style="color:red;">(*)</span></label>
                                    <input type="text" class="form-control" name="curp_PF" value="{{$solicitante->poder->curp_patronal}}" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">La CURP es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="name">RFC <span style="color:red;">(*)</span></label>
                                    <input type="text" name="RFC_pF" value="{{$solicitante->poder->rfc_patronal}}" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                    <select name="sexo_pf" id="sexo_pf" class="form-control">
                                        <option value="">Seleccione</option>
                                        <option value="Femenino" {{ $solicitante->poder["sexo_patronal"] == "Femenino" ? "selected" : '' }}>Femenino</option>
                                        <option value="Masculino" {{ $solicitante->poder["sexo_patronal"] == "Masculino" ? "selected" : '' }}>Masculino</option>
                                        <option value="Prefiero no responder">Prefiero no responder</option>
                                    </select>
                                    <div class="invalid-feedback">El tipo de persona es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-9">
                                <div class="form-group">
                                    <label for="name">Giro Comercial <span style="color:red;">(*)</span></label>
                                    <input type="text" name="giro_pF" value="{{$solicitante->poder->giroComercial}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h5 class="text-center">Datos de contacto</h5>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="">Correo electrónico</label>
                                    <input type="email" class="form-control" value="{{$solicitante->poder->email_patronal}}" name="correo_pF" id="electrónico_pF">
                                    <div class="invalid-feedback">El Correo electrónico es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6">
                                <div class="form-group">
                                    <label for="">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono_PF" value="{{$solicitante->poder->telefono_patronal}}" maxlength="10" pattern="[0-9]+">
                                    <div class="invalid-feedback">El telefono es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h5 class="text-center">Domicilio laboral</h5>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="password">Entidad Federativa</label>
                                    <select id="estado_pF" class="form-control" name="estado_pF" placeholder="*Entidad Federativa">
                                        <option value="">Seleccione</option>
                                        @foreach($estados as $est)
                                            <option value="{{$est['id']}}" {{ $est["id"] == $solicitante->poder["estado_patronal"] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">El campo Estado es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Nombre del Municipio o Alcaldía (*)</label>
                                    <select id="municipio_pF" class="form-control" name="municipio_pF" placeholder="*Municipio">
                                        <option value="">Seleccione</option>
                                        @foreach($municipios as $mun)
                                            <option value="{{$mun['id']}}" {{ $mun["id"] == $solicitante->poder["municipio_patronal"] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">El campo municipio o alcaldía es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Tipo de Vialidad (*)</label>
                                    <select name="vialidad_pF" id="vialidad_pF" class="form-control" placeholder="*Vialidad">
                                        <option value="">SELECCIONE</option>
                                        <option value="AMPLIACIÓN"  {{ $solicitante->poder["tipo_vialidad_patronal"] == "AMPLIACIÓN" ? "selected" : '' }}>Ampliación</option>
                                        <option value="ANDADOR"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "ANDADOR" ? "selected" : '' }}>Andador</option>
                                        <option value="AUTOPISTA"   {{ $solicitante->poder["tipo_vialidad_patronal"] == "AUTOPISTA" ? "selected" : '' }}>Autopista</option>
                                        <option value="AVENIDA"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "AVENIDA" ? "selected" : '' }}>Avenida</option>
                                        <option value="BOULEVARD"   {{ $solicitante->poder["tipo_vialidad_patronal"] == "BOULEVARD" ? "selected" : '' }}>Boulevard</option>
                                        <option value="CALLE"       {{ $solicitante->poder["tipo_vialidad_patronal"] == "CALLE" ? "selected" : '' }}>Calle</option>
                                        <option value="CALLEJÓN"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "CALLEJÓN" ? "selected" : '' }}>Callejón</option>
                                        <option value="CALZADA"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "CALZADA" ? "selected" : '' }}>Calzada</option>
                                        <option value="CARRETERA"   {{ $solicitante->poder["tipo_vialidad_patronal"] == "CARRETERA" ? "selected" : '' }}>Carretera</option>
                                        <option value="CERRADA"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "CERRADA" ? "selected" : '' }}>Cerrada</option>
                                        <option value="CIRCUITO"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "CIRCUITO" ? "selected" : '' }}>Circuito</option>
                                        <option value="CIRCUNVALACIÓN"  {{ $solicitante->poder["tipo_vialidad_patronal"] == "CIRCUNVALACIÓN" ? "selected" : '' }}>Circunvalación</option>
                                        <option value="CONTINUACIÓN"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "CONTINUACIÓN" ? "selected" : '' }}>Continuación</option>
                                        <option value="CORREDOR"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "CORREDOR" ? "selected" : '' }}>Corredor</option>
                                        <option value="DIAGONAL"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "DIAGONAL" ? "selected" : '' }}>Diagonal</option>
                                        <option value="EJE VIAL"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "EJE VIAL" ? "selected" : '' }}>Eje vial</option>
                                        <option value="PERIFÉRICO"  {{ $solicitante->poder["tipo_vialidad_patronal"] == "PERIFÉRICO" ? "selected" : '' }}>Periférico</option>
                                        <option value="PROLONGACIÓN" {{ $solicitante->poder["tipo_vialidad_patronal"] == "PROLONGACIÓN" ? "selected" : '' }}>Prolongación</option>
                                        <option value="RETORNO"     {{ $solicitante->poder["tipo_vialidad_patronal"] == "RETORNO" ? "selected" : '' }}>Retorno</option>
                                        <option value="VIADUCTO"    {{ $solicitante->poder["tipo_vialidad_patronal"] == "VIADUCTO" ? "selected" : '' }}>Viaducto</option>
                                    </select>
                                    <div class="invalid-feedback">El campo vialidad es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="name">Nombre de la Vialidad <span style="color:red;">(*)</span></label>
                                    <input type="text" name="vialidad_calle_pF" value="{{$solicitante->poder->vialidad_patronal}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El campo vialidad o calle es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Colonia <span style="color:red;">(*)</span></label>
                                    <input type="text" class="form-control" name="colonia_pF" value="{{$solicitante->poder->colonia_patronal}}" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El domicilio es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Núm. Ext. <span style="color:red;">(*)</span></label>
                                    <input type="text" class="form-control" name="num_ext_pF" value="{{$solicitante->poder->num_ext_patronal}}" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El domicilio es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Núm. Int.</label>
                                    <input type="text" class="form-control" name="num_int_pF" value="{{$solicitante->poder->mun_int_patronal}}" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El domicilio es obligatoria.</div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-3">
                                <div class="form-group">
                                    <label for="">Código Postal <span style="color:red;">(*)</span></label>
                                    <input type="text" class="form-control" name="cp_pF" value="{{$solicitante->poder->cp_patronal}}" minlength="5" maxlength="5" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">El domicilio es obligatoria.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Sub-bloque FISICA con representante --}}
                        <div id="fisicaConRep" style="{{ $tieneRepresentante == 'Si' ? '' : 'display:none;' }}">
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
                                        <label for="name">Nombre(s) del representante<span style="color:red;">(*)</span></label>
                                        <input type="text" name="nombre_representante_pF" value="{{$solicitante->poder->nombre_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                        <div class="invalid-feedback">El nombre es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                        <input type="text" name="primer_representante_pF" value="{{$solicitante->poder->primer_apellido_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                        <div class="invalid-feedback">El primer apellido es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                        <input type="text" name="segundo_representante_pF" value="{{$solicitante->poder->segundo_apellido_representante}}" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                        <div class="invalid-feedback">El segundo apellido es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="">CURP</label>
                                        <input type="text" class="form-control" name="curp_representante_pF" value="{{$solicitante->poder->curp_representante}}" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()">
                                        <div class="invalid-feedback">La CURP es obligatoria.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                        <select name="sexo_representante_pF" id="sexo_representante_pF" class="form-control">
                                            <option value="">Seleccione</option>
                                            <option value="Femenino" {{ $solicitante->poder["sexo_representante"] == "Femenino" ? "selected" : '' }}>Femenino</option>
                                            <option value="Masculino" {{ $solicitante->poder["sexo_representante"] == "Masculino" ? "selected" : '' }}>Masculino</option>
                                            <option value="Prefiero no responder">Prefiero no responder</option>
                                        </select>
                                        <div class="invalid-feedback">El tipo de persona es obligatorio.</div>
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
                                        <input type="email" class="form-control" name="correo_representante_pF" value="{{$solicitante->poder->correo_representante}}">
                                        <div class="invalid-feedback">El Correo electrónico es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                        <input type="text" class="form-control" name="telefono_representante_pF" value="{{$solicitante->poder->numero_representante}}" maxlength="10" pattern="[0-9]+">
                                        <div class="invalid-feedback">El telefono es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Identificación Oficial<span style="color:red;">(*)</span></label>
                                        <select name="tipo_identificacion_pFCR" class="form-control">
                                            <option value="">Seleccione el tipo de indentificación</option>
                                            <option value="Credencial de elector" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de elector" ? "selected" : '' }}>Credencial de Elector</option>
                                            <option value="Pasaporte" {{ $solicitante->poder["tipo_identificacion"] == "Pasaporte" ? "selected" : '' }}>Pasaporte</option>
                                            <option value="Cédula profesional" {{ $solicitante->poder["tipo_identificacion"] == "Cédula profesional" ? "selected" : '' }}>Cédula Profesional</option>
                                            <option value="Licencia de conducir" {{ $solicitante->poder["tipo_identificacion"] == "Licencia de conducir" ? "selected" : '' }}>Licencia de Conducir</option>
                                            <option value="Credencial de inapam" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de inapam" ? "selected" : '' }}>Credencial de INAPAM</option>
                                            <option value="Cartilla militar" {{ $solicitante->poder["tipo_identificacion"] == "Cartilla militar" ? "selected" : '' }}>Cartilla Militar</option>
                                            <option value="Documento migratorio" {{ $solicitante->poder["tipo_identificacion"] == "Documento migratorio" ? "selected" : '' }}>Documento Migratorio</option>
                                            <option value="Constancia de identidad" {{ $solicitante->poder["tipo_identificacion"] == "Constancia de identidad" ? "selected" : '' }}>Constancia de Identidad</option>
                                            <option value="Otro" {{ $solicitante->poder["tipo_identificacion"] == "Otro" ? "selected" : '' }}>Otros</option>
                                        </select>
                                        <div class="invalid-feedback">Este campo identificación es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span></label>
                                        <input type="text" name="num_identificacion_pFCR" class="form-control" oninput="this.value = this.value.toUpperCase()" value="{{$solicitante->poder->num_identificacion}}">
                                        <div class="invalid-feedback">El campo núm. de identificación es obligatorio.</div>
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
                                            <option value="Carta Poder" {{ $solicitante->poder["tipo_documento_representante"] == "Carta Poder" ? "selected" : '' }}>Carta Poder</option>
                                            <option value="Instrumento Notarial" {{ $solicitante->poder["tipo_documento_representante"] == "Instrumento Notarial" ? "selected" : '' }}>Instrumento Notarial</option>
                                        </select>
                                        <div class="invalid-feedback">El campo es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                        <input type="date" class="form-control" name="fecha_expedicion_pF" value="{{$solicitante->poder->fechaRegistro}}">
                                        <div class="invalid-feedback">La fecha es obligatoria.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-4">
                                    <div class="form-group">
                                        <label for="">Fecha vigencia</label>
                                        <input type="date" class="form-control" name="fecha_vigencia_pF" value="{{$solicitante->poder->fechaVigencia}}">
                                        <div class="invalid-feedback">La fecha es obligatoria.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <label for="">Descripción del documento que acredite la personaria</label>
                                        <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_pF">{{$solicitante->poder->descipcion_poder}}</textarea>
                                        <div class="invalid-feedback">La descripción es obligatoria.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>*Identificación del Empleador</label><br>
                                        <input type="file" name="documentoIne_pF" class="form-control" accept=".pdf">
                                        <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$solicitante->poder->ineDocumento}}">Existente</a>
                                        <div class="invalid-feedback">La Identificación es obligatoria.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>*Identificación del Representante Legal</label><br>
                                        <input type="file" name="documentoRepresentacion_pF" class="form-control" accept=".pdf">
                                        @if($solicitante->poder->representacionDocumento != NULL)
                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$solicitante->poder->representacionDocumento}}">Existente</a>
                                        @endif
                                        <div class="invalid-feedback">El documento de representación es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>*Documento que acredite la personería</label><br>
                                        <input type="file" name="documentoPoder_pF" class="form-control" accept=".pdf">
                                        @if($solicitante->poder->cedulaDocumento != NULL)
                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$solicitante->poder->cedulaDocumento}}">Existente</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>Anexo (Documentos Complementarios)</label><br>
                                        <input type="file" name="documentoAnexo_pF" class="form-control" accept=".pdf">
                                        @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$solicitante->poder->anexo_documeto}}">Existente</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="name">Validación</label>
                                        <select name="validacion" class="form-control">
                                            <option value="">Seleccionar</option>
                                            <option value="Validado" {{ $solicitante->poder["estatus"] == "Validado" ? "selected" : '' }}>Validar</option>
                                            <option value="Pendiente" {{ $solicitante->poder["estatus"] == "Pendiente" ? "selected" : '' }}>Rechazar</option>
                                        </select>
                                        <div class="invalid-feedback">El campo es obligatorio.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sub-bloque FISICA sin representante --}}
                        <div id="fisicaSinRep" style="{{ $tieneRepresentante == 'No' ? '' : 'display:none;' }}">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                        <h5 class="text-center" style="color:#CEA845">Cargar Documentos</h5>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Identificación Oficial <span style="color:red;">(*)</span></label>
                                        <select name="tipo_identificacion_pF" class="form-control">
                                            <option value="">Seleccione el tipo de indentificación</option>
                                            <option value="Credencial de elector" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de elector" ? "selected" : '' }}>Credencial de Elector</option>
                                            <option value="Pasaporte" {{ $solicitante->poder["tipo_identificacion"] == "Pasaporte" ? "selected" : '' }}>Pasaporte</option>
                                            <option value="Cédula profesional" {{ $solicitante->poder["tipo_identificacion"] == "Cédula profesional" ? "selected" : '' }}>Cédula Profesional</option>
                                            <option value="Licencia de conducir" {{ $solicitante->poder["tipo_identificacion"] == "Licencia de conducir" ? "selected" : '' }}>Licencia de Conducir</option>
                                            <option value="Credencial de inapam" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de inapam" ? "selected" : '' }}>Credencial de INAPAM</option>
                                            <option value="Cartilla militar" {{ $solicitante->poder["tipo_identificacion"] == "Cartilla militar" ? "selected" : '' }}>Cartilla Militar</option>
                                            <option value="Documento migratorio" {{ $solicitante->poder["tipo_identificacion"] == "Documento migratorio" ? "selected" : '' }}>Documento Migratorio</option>
                                            <option value="Constancia de identidad" {{ $solicitante->poder["tipo_identificacion"] == "Constancia de identidad" ? "selected" : '' }}>Constancia de Identidad</option>
                                            <option value="Otro" {{ $solicitante->poder["tipo_identificacion"] == "Otro" ? "selected" : '' }}>Otros</option>
                                        </select>
                                        <div class="invalid-feedback">Este campo identificación es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name">Núm de identificación <span style="color:red;">(*)</span></label>
                                        <input type="text" name="num_identificacion_pF" class="form-control" oninput="this.value = this.value.toUpperCase()" value="{{$solicitante->poder->num_identificacion}}">
                                        <div class="invalid-feedback">El campo núm. de identificación es obligatorio.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>*Identificación Oficial</label><br>
                                        <input type="file" name="documentoIne_pFSR" id="documentoIne_pFSR" class="form-control" accept=".pdf">
                                        <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$solicitante->poder->ineDocumento}}">Existente</a>
                                        <div class="invalid-feedback">La Identificación es obligatoria.</div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6">
                                    <div class="form-group">
                                        <label>Anexo (Documentos Complementarios)</label><br>
                                        <input type="file" name="documentoAnexo_pFSR" class="form-control" accept=".pdf">
                                        @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                            <a target="_blank" class="btn btn-primary" href="../../storage/app/documentos_abogados/{{$solicitante->poder->anexo_documeto}}">Existente</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label for="name">Validación</label>
                                        <select name="validacion" class="form-control">
                                            <option value="">Seleccionar</option>
                                            <option value="Validado" {{ $solicitante->poder["estatus"] == "Validado" ? "selected" : '' }}>Validar</option>
                                            <option value="Pendiente" {{ $solicitante->poder["estatus"] == "Pendiente" ? "selected" : '' }}>Rechazar</option>
                                        </select>
                                        <div class="invalid-feedback">El campo es obligatorio.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipoPersonaPoder');
        const repSelect = document.getElementById('representatePoder');
        const bloqueMoral = document.getElementById('bloqueMoral');
        const bloqueFisica = document.getElementById('bloqueFisica');
        const grupoRepresentate = document.getElementById('grupoRepresentate');
        const fisicaConRep = document.getElementById('fisicaConRep');
        const fisicaSinRep = document.getElementById('fisicaSinRep');
        const formModalPoder = document.querySelector('#modalEditarPoder form');

        if (!tipoSelect) return;

        function limpiarYDeshabilitar(selector) {
            const elementos = document.querySelectorAll(selector + ' input, ' + selector + ' select, ' + selector + ' textarea');
            elementos.forEach(function (el) {
                if (!el.name) return;
                if (el.name === 'tipoPersona' || el.name === 'representate') return;
                if (el.type === 'hidden') return;

                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = false;
                } else {
                    el.value = '';
                }
                el.disabled = true;
                el.removeAttribute('required');
            });
        }

        function habilitarBloque(selector) {
            const elementos = document.querySelectorAll(selector + ' input, ' + selector + ' select, ' + selector + ' textarea');
            elementos.forEach(function (el) {
                if (!el.name) return;
                if (el.type === 'hidden') return;
                el.disabled = false;
            });
        }

        function actualizarRepresentante() {
            if (!repSelect || !bloqueFisica) return;
            if (tipoSelect.value !== 'Fisica') return;

            if (repSelect.value === 'Si') {
                if (fisicaConRep) {
                    fisicaConRep.style.display = '';
                }
                if (fisicaSinRep) {
                    fisicaSinRep.style.display = 'none';
                }
            } else {
                if (fisicaSinRep) {
                    fisicaSinRep.style.display = '';
                }
                if (fisicaConRep) {
                    fisicaConRep.style.display = 'none';
                }
            }
        }

        function actualizarTipoPersona() {
            if (tipoSelect.value === 'Moral') {
                if (bloqueMoral) {
                    bloqueMoral.style.display = '';
                }
                if (bloqueFisica) {
                    bloqueFisica.style.display = 'none';
                }
                if (grupoRepresentate) {
                    grupoRepresentate.style.display = 'none';
                }
            } else {
                if (bloqueFisica) {
                    bloqueFisica.style.display = '';
                }
                if (bloqueMoral) {
                    bloqueMoral.style.display = 'none';
                }
                if (grupoRepresentate) {
                    grupoRepresentate.style.display = '';
                }
                actualizarRepresentante();
            }
        }

        // Al cambiar tipo/representante solo mostramos/ocultamos, NO limpiamos
        tipoSelect.addEventListener('change', actualizarTipoPersona);
        if (repSelect) {
            repSelect.addEventListener('change', actualizarRepresentante);
        }

        // Antes de enviar el formulario, limpiamos y deshabilitamos solo los bloques que no aplican
        if (formModalPoder) {
            formModalPoder.addEventListener('submit', function () {
                // Siempre habilitar los bloques principales antes de decidir qué limpiar
                if (bloqueMoral) habilitarBloque('#bloqueMoral');
                if (bloqueFisica) habilitarBloque('#bloqueFisica');
                if (fisicaConRep) habilitarBloque('#fisicaConRep');
                if (fisicaSinRep) habilitarBloque('#fisicaSinRep');

                if (tipoSelect.value === 'Moral') {
                    // No aplica Fisica -> limpiar todo ese bloque (incluye con/sin representante)
                    if (bloqueFisica) {
                        limpiarYDeshabilitar('#bloqueFisica');
                    }
                } else if (tipoSelect.value === 'Fisica') {
                    // No aplica Moral
                    if (bloqueMoral) {
                        limpiarYDeshabilitar('#bloqueMoral');
                    }

                    // Dentro de Fisica, limpiar el sub-bloque que no corresponda
                    if (repSelect) {
                        if (repSelect.value === 'Si') {
                            // Con representante: limpiar el bloque sin representante
                            if (fisicaSinRep) {
                                limpiarYDeshabilitar('#fisicaSinRep');
                            }
                        } else if (repSelect.value === 'No') {
                            // Sin representante: limpiar el bloque con representante
                            if (fisicaConRep) {
                                limpiarYDeshabilitar('#fisicaConRep');
                            }
                        }
                    }
                }
            });
        }

        // Inicializar estados al cargar (solo visibilidad)
        actualizarTipoPersona();
        actualizarRepresentante();
    });
</script>

<div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('editar_solicitud_audiencia')}}">
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
                                <label for="name">Nombre(s) y Apellidos del Solicitante (*) </label>
                                <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" value="<?=$solicitante["nombre"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo nombre es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">CURP del Solicitante (*)</label>
                                <input type="text" name="curp" id="curp_input" oninput="validarInput(this)"class="form-control" value="<?=$solicitante["curp"];?>" required> 
                                <pre id="resultado"></pre>
                                <div class="invalid-feedback">
                                    El campo curp es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">RFC del Solicitante (*)</label>
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
                                <label for="name">Puesto (*)</label>
                                <input type="text" class="form-control" name="puesto" value="<?=$solicitante["puesto"];?>" oninput="this.value = this.value.toUpperCase()" required> 
                                <div class="invalid-feedback">
                                    El campo puesto es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Frecuencia de Pago (*)</label>
                                <select name="periodo_pago" class="form-control" value="<?=$solicitante["periodo_pago"];?>" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Diario" {{ $solicitante['periodo_pago'] == 'Diario' ? "selected" : '' }}>DIARIO</option>
                                    <option value="Semanal" {{ $solicitante['periodo_pago'] == 'Semanal' ? "selected" : '' }}>SEMANAL</option>
                                    <option value="Quincenal" {{ $solicitante['periodo_pago'] == 'Quincenal' ? "selected" : '' }}>QUINCENAL</option>
                                    <option value="Mensual" {{ $solicitante['periodo_pago'] == 'Mensual' ? "selected" : '' }}>MENSUAL</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo frecuencia de pagos es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Salario (*)</label>
                                <input type="text" name="pago" class="form-control" value="<?=$solicitante["pago"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo salario es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Cantidad total de horas trabajadas por semana (*)</label>
                                <input type="number" name="horas" class="form-control" value="<?=$solicitante["horas_semana"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo cantidad de horas trabajadas es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="check_fecha">¿Laboras actualmente?</label>
                                <input type="checkbox" id="check_fecha" name="labora" {{ $solicitante['labora'] == 'Si' ? 'checked' : '' }} />
                            </div>  
                        </div>    
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Fecha de Ingreso (*)</label>
                                <input type="date" name="fecha_ingreso" class="form-control" value="<?=$solicitante["fecha_ingreso"];?>" required> 
                                <div class="invalid-feedback">
                                    El campo fecha de ingreso es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4">
                            <div class="form-group">
                                <label for="name">Jornada (*)</label>
                                <select name="jornada" class="form-control" value="<?=$solicitante["jornada"];?>" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Diurna" {{ $solicitante['jornada'] == 'Diurna' ? "selected" : '' }}>DIURNA</option>
                                    <option value="Nocturna" {{ $solicitante['jornada'] == 'Nocturna' ? "selected" : '' }}>NOCTURNA</option>
                                    <option value="Mixta" {{ $solicitante['jornada'] == 'Mixta' ? "selected" : '' }}>MIXTA</option>
                                </select>
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

{{--<div class="modal fade" id="modalCitados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Representantes Legales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                <form method="POST" action="{{ route('seleccionar_abogado_audiencia') }} ">
                    @csrf
                    <input type="hidden" id="modal-id" name="citado" value="">
                    <input type="hidden" name="solicitud" value="{{$solicitud->id}}">
                    <table id="tabla1" class="table-striped" style="width:100%">
                        <thead style="background-color: #4A001F;">   
                            <!--<th style="display: none;">ID</th>-->
                            <th style="color: #fff;">Folio</th>
                            <th style="color: #fff;">Nombre</th>
                            <th style="color: #fff;">RFC</th>
                            <th style="color: #fff;">Empresa</th>
                            <th style="color: #fff;">Acciones</th>
                        </thead>
                        <tbody class="contenidobusqueda">
                            @foreach($abogados as $abogado)
                                <tr>
                                    <td>{{$abogado->idAbogado}}</td>
                                    <td>{{$abogado->nombres}} {{$abogado->primer_apellido}} {{$abogado->segundo_apellido}}</td>
                                    <td>{{$abogado->rfc}}</td>
                                    <td>{{$abogado->empresa}}</td>
                                    <td>
                                        <button class="btn btn-info" onclick=editar_rol(); type="submit" name="abogado" value="{{$abogado->idAbogado}}">Seleccionar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalAgregarCitados">Agregar en representación</button>
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalAgregarDerecho">Agregar por propio derecho</button>
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalActualizaCitados">Actualizar citado</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>--}}

<div class="modal fade" id="ModalArchivar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('archivar_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-archivar" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo del archivo de audiencia</h5>
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
<div class="modal fade" id="ModalReagendar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('reagendar_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-reagendar" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Fecha de la reagenda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="date" class="form-control" name="fecha">
                    <input type="time" class="form-control" name="hora">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="ModalIncopentencia" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('incopentencia_audiencia')}}">
        @csrf
        <input type="hidden" id="modal-id-incopentencia" name="id" value="">
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
<div class="modal fade" id="modalAgregarDerecho" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' enctype="multipart/form-data" name="AgregarPersonaFisica" id="AgregarPersonaFisica" action="{{route('insertar_citado_PF')}}">
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
                                <label>Segundo apellido</label>
                                <input type="text" name="segundo_apellido" class="form-control" required>
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="name">Tipo de identificación (*)</label>
                                <select name="identificacionAlta" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="ine">INE</option>
                                    <option value="pasaporte">PASAPORTE</option>
                                    <option value="cedula">CÉDULA PROFESIONAL</option>
                                    <option value="licencia">LICENCIA PARA CONDUCIR</option>
                                    <option value="otros">OTROS</option>
                                </select>
                                <div class="invalid-feedback">
                                    El tipo de identificaión es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Identificación oficial</label>
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
    <form class='needs-validation novalidate'  method='POST' name="AgregarPersonaFisica" id="AgregarPersonaFisica" action="{{route('actualiza_citados_audiencia')}}">
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

<div id="submit_loader" style="display:none;">
    <div>.</div>
    <div class="loader"></div>
</div>