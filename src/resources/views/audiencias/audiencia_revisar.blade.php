@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');
    $contador = 0;
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
                                            <th style="color: #ffff;">Representante legal</th>
                                            <th style="color: #ffff;">Convenio</th>
                                            <th style="color: #ffff;">Acciones</th>
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
                                            <td></td>
                                            <td></td>
                                            <td>
                                                <a type="button" class="btn btn-warning open-modal" data-bs-toggle="modal" data-bs-target="#exampleModal1" data-id="{{ $id }}">Editar</a>
                                            </td>
                                            <td> </td>
                                        </tr>
                                        @php
                                            $fechaActual = date('Y-m-d');
                                            $contador = 0;
                                            

                                            // 1. Inicializar la bandera que controlará la visibilidad de los botones
                                            // 0 = Mostrar los botones "Acta" y "Convenio" (se asume que todo está bien o no aplica la restricción)
                                            // 1 = Ocultar los botones "Acta" y "Convenio" (la condición de restricción se cumple)
                                            $ocultarBotonesFinales = 0;
                                            foreach($representantes as $representante){
                                                if ($representante->tipo_identificacion == "") {
                                                    // Si la identificación está vacía en AL MENOS UN REPRESENTANTE, 
                                                    // establecemos la bandera para ocultar los botones y detenemos el bucle.
                                                    $ocultarBotonesFinales = 1; 
                                                    break;
                                                }
                                                $bandera = $ocultarBotonesFinales;
                                            }
                                        @endphp
                                        @foreach($representantes as $representante)
                                            <tr>
                                                <td  style="display:none">{{$representante->id}}</td>
                                                <td style="color: #000000;"><b>Citado</b></td>
                                                <td>{{$representante->nombre}} {{$representante->primer_apellido}} {{$representante->segundo_apellido}}</td>
                                                <td>{{ $representante->notificacion }}</td>
                                                <td>{{ $representante->estatus }}</td>
                                                <td>
                                                    @if($representante->id_abogado == null && $representante->id_fisica == null)
                                                        Por asignar
                                                    @else
                                                        @if($representante->id_abogado != null && $representante->id_fisica == null)
                                                            {{ $representante->nombre_abogado }} {{ $representante->primero_abogado }} {{ $representante->segundo_abogado }}
                                                        @else
                                                            {{ $representante->nombre_fisica }} {{ $representante->primer_fisica }} {{ $representante->segundo_fisica }}
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>   
                                                @if($representante->id_abogado != null)
                                                    <input form="form_roles" type="checkbox" name="aparece_convenio[{{ $representante->id }}]" value="1" {{ $representante->aparece_convenio == 1 ? 'checked' : '' }}>
                                                @else
                                                    Sin representante asignado
                                                @endif
                                                </td>
                                                <td>
                                                    @if($representante->id_abogado == null && $representante->id_fisica == null)
                                                        <button type="button" class="btn btn-primary open-modal" data-id="{{ $representante->id }}" data-bs-toggle="modal" data-bs-target="#modalCitados"> Registrar Comparecencia </button>
                                                    @else
                                                        <form action="{{ route('representante.quitar') }}" method="POST" class="mt-1">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $representante->id }}">
                                                            <input type="hidden" name="solicitud" value="{{ $solicitud->id }}">
                                                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                                                Quitar representante
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{--@if($representante->id_abogado != null)
                                                        <a class="btn btn-success mb-1 w-100" href="{{ route('PDFcompareceSP', $solicitud->id) }}"  target="_blank">Comparecencia sin Acreditación de Facultades</a>
                                                    @endif--}}
                                                    @if($representante->id_abogado != null)
                                                        <a class="btn btn-success mb-1 w-100" href="{{ route('PDFcompareceSP', $solicitud->id) }}" target="_blank">Comparecencia sin Acreditación de Facultades</a>
                                                    @else
                                                        <button class="btn btn-secondary mb-1 w-100" disabled title="No hay una comparecencia registrada">Comparecencia sin Acreditación de Facultades</button>
                                                    @endif
                                                </td>
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
                                                            $concl = $conciliadores["conclucion"] ?? null;
                                                            $esReinstalacion = ($concl === 'Reinstalacion');
                                                            $esMontoCero = is_numeric($concepto->monto) && (float) $concepto->monto == 0.0;
                                                        @endphp
                                                        @if($esReinstalacion && $esMontoCero)
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
                                                            $concl = $conciliadores["conclucion"] ?? null;
                                                            $esReinstalacion = ($concl === 'Reinstalacion');
                                                            $esMontoCero = is_numeric($pago->monto) && (float) $pago->monto == 0.0;
                                                        @endphp
                                                        @if($esReinstalacion && $esMontoCero)
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
<div class="modal fade" id="modalCitados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                    <input type="hidden" name="origen" value="previa">
                    <table id="tabla1" class="table-striped" style="width:100%">
                        <thead style="background-color: #4A001F;">   
                            <!--<th style="display: none;">ID</th>-->
                            <th style="color: #fff;">Folio</th>
                            <th style="color: #fff;">Nombre</th>
                            <th style="color: #fff;">RFC</th>
                            <th style="color: #fff;">Representante</th>
                            <th style="color: #fff;">Acciones</th>
                        </thead>
                        <tbody class="contenidobusqueda">
                            @foreach($abogados as $abogado)
                                <tr>
                                    <td>{{$abogado->idAbogado}}</td>
                                    <td>{{$abogado->nombres_patronal}} {{$abogado->primer_apellido_patronal}} {{$abogado->segundo_apellido_patronal}}</td>
                                    <td>{{$abogado->rfc_patronal}}</td>
                                    <td>{{$abogado->nombre_representante}} {{$abogado->primer_apellido_representante}} {{$abogado->segundo_apellido_representante}}</td>
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
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalAgregarCitados">Agregar Registro Patronal</button>
                <!--
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalAgregarDerecho">Agregar por propio derecho</button>
                <button type="button" class="btn btn-primary" data-id="{{ $id }}" data-bs-toggle="modal" data-bs-target="#modalActualizaCitados">Actualizar citado</button>
                -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
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
<!-- Modal Agregar Citados -->
<div class="modal fade" id="modalAgregarCitados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST'  enctype="multipart/form-data" name="AgregarRepresentante" id="AgregarRepresentante" action="{{route('insertar_citados_audiencia')}}">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <input type="hidden" name="id_citado_2" id="id_citado_2" value="">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Representante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <h4 class="text-center">Datos del representante</h4>
                            </div>
                        </div>  
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="name">Nombres</label>
                                <input type="text" class="form-control" placeholder="*Nombre(s)" name="nombresAbogadoAlta" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    El nombre es obligatorio.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">Primer Apellido</label>
                                <input type="text" class="form-control" placeholder="*Apellidos" name="primer_apellido" id="primer_apellido" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    El primer apellido es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">Segundo Apellido</label>
                                <input type="text" class="form-control" placeholder="*Apellidos" name="segundo_apellido" id="segundo_apellido" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    El segundo apellido es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">Teléfono</label>
                                <input type="text" class="form-control" placeholder="*Telefono"  name="telefonoAbogadoAlta" maxlength="10" pattern="[0-9]+" required>
                                <div class="invalid-feedback">
                                    El telefono es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">Correo</label>
                                <input type="email" class="form-control" placeholder="*Correo" name="correoAbogadoAlta" id="correoAbogadoAlta" required>
                                <div class="invalid-feedback">
                                    El correo es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">CURP</label>
                                <input type="text" class="form-control" placeholder="*CURP" aria-label="CURP" name="curpAbogadoAlta" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    La CURP es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <h4 class="text-center">Datos de la empresa</h4>
                            </div>
                        </div>  

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">Empresa</label>
                                <input type="text" class="form-control" placeholder="*Empresa representación" name="empresaAbogadoAlta" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    La empresa es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="password">Entidad Federativa</label>
                                <select id="estado_poder" class="form-control" name="estado_poder" placeholder="*Entidad Federativa" required>
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
                                <label for="name">Nombre del Municipio o Alcaldía (*)</label>
                                <select id="municipio_poder" class="form-control" name="municipio_poder" placeholder="*Municipio" required>
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
                                <label for="name">Tipo de Vialidad (*)</label>
                                <select name="vialidadPoder" id="vialidadPoder" class="form-control" placeholder="*Vialidad" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Calle">CALLE</option>
                                    <option value="Avenida">AVENIDA</option>
                                    <option value="Calzada">CALZADA</option>
                                    <option value="Boulevard">BOULEVARD</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo vialidad es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="name">Nombre de la Vialidad (*)</label>
                                <input type="text" name="vialidad_callePoder" id="vialidad_callePoder" class="form-control" placeholder="*Nombre vialidad" oninput="this.value = this.value.toUpperCase()" required> 
                                <div class="invalid-feedback">
                                    El campo vialidad o calle es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="">Colonia</label>
                                <input type="text" class="form-control" placeholder="*Colonia" name="coloniaAbogadoAlta" id="coloniaAbogadoAlta" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    El domicilio es obligatoria.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="">Núm. Ext.</label>
                                <input type="text" class="form-control" placeholder="*Número exterior" name="NExtAbogadoAlta" id="NExtAbogadoAlta" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    El domicilio es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="">Núm. Int.</label>
                                <input type="text" class="form-control" placeholder="Número interior" name="NIntAbogadoAlta" id="NIntAbogadoAlta" oninput="this.value = this.value.toUpperCase()">
                                <div class="invalid-feedback">
                                    El domicilio es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="">Código postal</label>
                                <input type="text" class="form-control" placeholder="*Código postal" name="cpAbogadoAlta" id="cpAbogadoAlta" oninput="this.value = this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    El domicilio es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">RFC</label>
                                <input type="text" class="form-control" placeholder="RFC Empresa" name="RFCAbogadoAlta" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">Fecha vigencia</label>
                                <input type="date" class="form-control" aria-describedby="basic-addon1" name="fechaVigenciaAlta" id="fechaVigenciaAlta" min="<?= date("Y-m-d") ?>" required>
                                <div class="invalid-feedback">
                                    La fecha es obligatoria.
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="">Industria</label>
                                <input type="text" class="form-control" placeholder="Giro Comercial" name="industriaAlta" required>
                                <div class="invalid-feedback">
                                    La industria es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <span class="" id="basic-addon1">*Seleccione la region(nes).</i></i></span>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="moreliaSucursal" value="Si">
                                    <label class="form-check-label" for="flexCheckDefault">Morelia</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="uruapanSucursal" value="Si" >
                                    <label class="form-check-label" for="flexCheckChecked">Uruapan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="zamoraSucursal" value="Si">
                                    <label class="form-check-label" for="flexCheckDefault">Zamora</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <label for="">Descripción del poder</label>
                                <textarea class="form-control" aria-describedby="basic-addon1" name="descripcionpoderAlta" required></textarea>
                                <div class="invalid-feedback">
                                    La descripción es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>*Identificación oficial</label><br>
                                <input type="file" name="documentoIne" class="form-control" accept=".pdf">
                                <div class="invalid-feedback">
                                    La Identificación es obligatoria.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>*Documento que acredite la representación</label><br>
                                <input type="file" name="documentoRepresentacion" class="form-control" accept=".pdf">
                                <div class="invalid-feedback">
                                    El documento de representación es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Anexos</label><br>
                                <input type="file" name="documentoAnexo" class="form-control" accept=".pdf">
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Anexos 2</label><br>
                                <input type="file" name="documentoPoder" class="form-control" accept=".pdf">
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
    <!--citados a mostrar en convenio -->
    <script>
let yaGuardado = false;

function clonarCheckboxes() {
    let form = document.getElementById('form_roles');

    // limpiar clones previos
    document.querySelectorAll('.clon-checkbox').forEach(el => el.remove());

    // clonar todos los checkboxes aparece_convenio
    document.querySelectorAll('input[type=checkbox][name^="aparece_convenio"]').forEach(cb => {
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = cb.name;
        input.value = cb.checked ? 1 : 0;
        input.classList.add('clon-checkbox');
        form.appendChild(input);
    });

    yaGuardado = true;
}

    // Actualizar → guarda inmediatamente (solo si existe el botón)
    const btnActualizar = document.getElementById('btn-actualizar');
    if (btnActualizar) {
        btnActualizar.addEventListener('click', function(e) {
            clonarCheckboxes();
        });
    }

    // Terminar → guarda solo si no se guardó antes
    document.getElementById('btn-terminar').addEventListener('click', function(e) {
        if (!yaGuardado) {
            clonarCheckboxes();
        }
    });
    </script>
<script>
    // Deshabilitar los botones "Terminar", "Convenio" y "Acta" si ningún checkbox está marcado
    const tipoSolicitud = String(@json($tipo_solicitud)); 
    (function () {
    // Obtener referencias a los botones
        const termBtn = document.querySelector('button[name="bandera"][value="1"]');
        const convBtn = document.getElementById('btn-convenio1'); // Asumo que el ID es btn-convenio
        const actaBtn = document.getElementById('btn-acta');     // Nuevo: ID del botón Acta de Audiencia

      function updateVisibilidadBotones() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="aparece_convenio"]');
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        
                // Array de botones a habilitar/deshabilitar (Convenio y Acta)
                const buttonsToControl = [convBtn, actaBtn];

                buttonsToControl.forEach(btn => {
                    if (!btn) return;

                    if (anyChecked) {
                        btn.classList.remove('disabled');
                        btn.style.pointerEvents = '';
                        btn.removeAttribute('aria-disabled');
                    } else {
                        btn.classList.add('disabled');
                        btn.style.pointerEvents = 'none';
                        btn.setAttribute('aria-disabled', 'true');
                    }
                });

        // Control del botón Terminar
        if (termBtn) {
          termBtn.disabled = !anyChecked;
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
            if(tipoSolicitud === "1"){
                updateVisibilidadBotones();
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target && e.target.matches('input[type="checkbox"][name^="aparece_convenio"]')) {
                if(tipoSolicitud === "1"){
                    updateVisibilidadBotones();
                }
            }
        });

        // Ejecutar inmediatamente por si el DOM ya está listo
        if(tipoSolicitud === "1"){
            updateVisibilidadBotones();
        }
      
    })();
  </script>
    <script>
        
        // Deshabilitar el botón "Terminar" si ningún checkbox está marcado
        (function () {
            const termBtn = document.querySelector('button[name="bandera"][value="1"]');
            const convBtn = document.getElementById('btnConvenio');
            const actaBtn = document.getElementById('btn-acta');

            function updateTerminar() {
                if (!termBtn) return;
                const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="aparece_convenio"]');
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                termBtn.disabled = !anyChecked;
            }

            function updateConvenio() {
                if (!convBtn) return;
                const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="aparece_convenio"]');
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                if (anyChecked) {
                    convBtn.classList.remove('disabled');
                    convBtn.style.pointerEvents = '';
                    convBtn.removeAttribute('aria-disabled');
                } else {
                    convBtn.classList.add('disabled');
                    convBtn.style.pointerEvents = 'none';
                    convBtn.setAttribute('aria-disabled', 'true');
                }
            }
            if(tipoSolicitud ==="1"){
                // Actualizar al cargar y cuando cambien checkboxes
                document.addEventListener('DOMContentLoaded', function() {
                    updateTerminar();
                    updateConvenio();
                });
                document.addEventListener('change', function (e) {
                    if (e.target && e.target.matches('input[type="checkbox"][name^="aparece_convenio"]')) {
                        updateTerminar();
                        updateConvenio();
                    }
                });

                // Ejecutar inmediatamente por si el DOM ya está listo
                updateTerminar();
                updateConvenio();
            }
        })();
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Lógica para el botón CONVENIO con AJAX
            const btnConvenio = document.getElementById('btn-convenio1');
            
            if(btnConvenio){
                btnConvenio.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevenir cualquier envío tradicional
                    
                    // 1. Obtener ID de la solicitud
                    // Buscamos el input hidden del form principal. Ajusta el selector si tienes múltiples forms.
                    // En tu archivo veo <input type="hidden" name="id" value="{{ $id }}"> dentro del form 'form_roles'
                    let inputId = document.querySelector('#form_roles input[name="id"]');
                    if(!inputId) { 
                        alert("No se encontró el ID de la solicitud"); 
                        return; 
                    }
                    let idSolicitud = inputId.value;

                    let audienciaId = document.querySelector('input[name="audiencia_id"]')?.value || document.querySelector('input[name="id_audiencia_recurso"]')?.value || '{{ request("audiencia_id") }}';
                    // 2. Recolectar IDs de los checkboxes marcados
                    let idsSeleccionados = [];
                    // Buscamos los checkboxes que empiezan con "aparece_convenio" y están marcados
                    document.querySelectorAll('input[type=checkbox][name^="aparece_convenio"]:checked').forEach(cb => {
                        // El name es "aparece_convenio[123]". Usamos Regex para sacar el ID (123).
                        let match = cb.name.match(/\[(\d+)\]/);
                        if(match && match[1]) {
                            idsSeleccionados.push(match[1]);
                        }
                    });

                    if(idsSeleccionados.length === 0 && tipoSolicitud === "1"){
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atención',
                            text: 'Debes seleccionar al menos un citado para generar el convenio.'
                        });
                        return;
                    }

                    // 3. Enviar a Laravel vía AJAX (Fetch) para guardar en sesión
                    fetch("{{ route('guardar_seleccion_convenio') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_solicitud: idSolicitud,
                            ids_seleccionados: idsSeleccionados
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            // 4. Si se guardó en sesión exitosamente, abrimos el PDF en nueva pestaña
                            // Convenio: si la conclusión es Reinstalacion, usamos su PDF específico
                            // Ajusta la URL base si tu ruta tiene prefijos
                            let urlPdf = "{{ ($conciliadores["conclucion"] ?? null) === 'Reinstalacion' ? route('PDFconvenioreinstalacion', ':id') : route('PDFconveniosolicitud', ':id') }}";
                            urlPdf = urlPdf.replace(':id', idSolicitud);
                            if (typeof audienciaId !== 'undefined' && audienciaId) {
                                urlPdf += '?audiencia_id=' + audienciaId;
                            }
                            
                            window.open(urlPdf, "_blank");
                        } else {
                            Swal.fire('Error', 'No se pudo procesar la selección.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Ocurrió un error de conexión.', 'error');
                    });
                });
            }

            // Lógica para el botón ACTA con AJAX (igual que convenio pero abre VerPDFAudiencia)
            const btnActa = document.getElementById('btn-acta');
            if (btnActa) {
                btnActa.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Obtener id de la solicitud
                    let inputId = document.querySelector('#form_roles input[name="id"]');
                    if(!inputId) { 
                        alert("No se encontró el ID de la solicitud");
                        return; 
                    }
                    let idSolicitud = inputId.value;

                    // Recolectar IDs marcados
                    let idsSeleccionados = [];
                    document.querySelectorAll('input[type=checkbox][name^="aparece_convenio"]:checked').forEach(cb => {
                        let match = cb.name.match(/\[(\d+)\]/);
                        if(match && match[1]) idsSeleccionados.push(match[1]);
                    });

                    if (idsSeleccionados.length === 0 && tipoSolicitud === "1") {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atención',
                            text: 'Debes seleccionar al menos un citado para generar el acta.'
                        });
                        return;
                    }

                    // Guardar en sesión
                    fetch("{{ route('guardar_seleccion_acta') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ id_solicitud: idSolicitud, ids_seleccionados: idsSeleccionados })
                    })
                    .then(resp => resp.json())
                    .then(data => {
                        if (data.status === 'success') {
                            let urlPdf = "{{ route('VerPDFAudiencia', ':id') . '?audiencia_id=' . request()->query('audiencia_id') }}";
                            urlPdf = urlPdf.replace(':id', idSolicitud);
                            window.open(urlPdf, '_blank');
                        } else {
                            Swal.fire('Error', 'No se pudo procesar la selección.', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'No se pudo procesar la selección.', 'error');
                    });
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