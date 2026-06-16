@extends('layouts.app1')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Audiencia</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Concluir Audiencia</h3>
                            
                            @if (session('show_modal'))
                                <script>
                                    $(document).ready(function(){
                                        $('#miModal').modal('show');
                                    });
                                </script>
                            @endif
                            
                            <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            <!--<span class="badge badge-danger">{{ $error }}</span>-->
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if($conceptos->count() > 0)
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <div class="form-group">
                                         <h4 class="text-center py-1" style="background-color: #4A001F; color: #ffffff; border-radius: 5px 5px 0 0; margin-bottom: 0;">
                                            Prestaciones
                                        </h4>
                                    
                                <table class="table">
                                    <thead style="background-color: #ffffff;">
                                        <tr> 
                                            <th style="display:none">ID</th>
                                            <th style="color: #000000;">Tipo pago</th>
                                            <th style="color: #000000;">Monto</th>
                                            <th style="color: #000000;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>   
                                        @foreach($conceptos as $concepto)
                                            <tr>
                                                <td  style="display:none">{{$concepto->id}}</td>
                                                <td>{{ $concepto->descripcion}}</td>
                                                <td>${{ number_format($concepto->monto,2) }}</td>
                                                <td>
                                                    @if($concepto->id)
                                                        <form method="POST" action="{{ route('concepto_eliminar_pago', $concepto->id) }} ">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>              
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>        
                                        @endforeach  
                                    </tbody>
                                </table>
                                </div>
                                </div>
                            @endif
                            @if($deducciones->count() > 0)
                                <div class="col-xs-12 col-sm-12 col-md-12 mt-4">
                                    <div class="form-group">
                                        <h4 class="text-center py-2" style="background-color: #4A001F; color: #ffffff; border-radius: 5px 5px 0 0; margin-bottom: 0;">
                                            Deducciones
                                        </h4>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped mt-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="display:none">ID</th>
                                                <th class="text-dark">Descripción del Concepto</th>
                                                <th class="text-center text-dark">Monto</th>
                                                <th class="text-center text-dark">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($deducciones as $concepto)
                                                <tr>
                                                    <td style="display:none">{{ $concepto->id }}</td>
                                                    <td class="align-middle">{{ $concepto->descripcion }}</td>
                                                    <td class="text-right align-middle font-weight-bold">
                                                        ${{ number_format($concepto->monto, 2) }}
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        @if($concepto->id)
                                                            <form method="POST" action="{{ route('eliminar_deduccion_audiencia', $concepto->id) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-outline-danger btn-sm" type="submit" title="Eliminar Deducción">
                                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                                
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <h4 class="text-center">Pagos</h4>
                                </div>
                            </div>
                            <table class="table table-striped mt-1">
                                <thead style="background-color: #4A001F;">
                                    <tr> 
                                        <th style="display:none">ID</th>
                                        <th style="color: #ffffff;">Fecha y Hora</th>
                                        <th style="color: #ffffff;">Descripción</th>
                                        <th style="color: #ffffff;">Monto</th>
                                        <th style="color: #ffffff;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pagos as $pago)
                                        <tr>
                                            <td  style="display:none">{{$pago->id}}</td>
                                            <td> {{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d/m/y') }}<br>{{ \Carbon\Carbon::parse($pago->hora)->format('H:i') }} hrs.</td>
                                            <td>{{ $pago->descripcion}}</td>
                                            @if($pago->monto)
                                                <td>${{ number_format($pago->monto,2) }}</td>
                                            @else
                                                <td>No Aplica</td>
                                            @endif
                                            <td>
                                                @if($pago->id)
                                                    <form method="POST" action="{{ route('pago_eliminar_pago', $pago->id) }} ">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>                          
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>  
                                    @endforeach 
                                </tbody> 
                            </table>
                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{ route('Guarda_edicion_solConcluida') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $id }}">
                                <input type="hidden" name="audiencia_id" value="{{ $audiencia_id }}">
                                <input type="hidden" name="audiencia_hora" id="audiencia_hora" value="{{ $audiencia_hora }}">
                                <input type="hidden" name="audiencia_fecha" id="audiencia_fecha" value="{{ $audiencia_fecha }}">
                                
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12"  style="border:1px solid black;">
                                        <div class="form-group">
                                            <label for="name">RESOLUCIÓN PRIMERA MANIFESTACIÓN</label>
                                            <textarea name="primera" class="form-control" required>{{ old('primera', $datosConciliador->resolicion_primera ?? '') }}</textarea>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>                               
                                    <br>                                 
                                    <div id="justificacion" ><br>
                                        <div class="col-xs-12 col-sm-12 col-md-12" style="border:1px solid black;">
                                            <div class="form-group">
                                                <label for="name">RESOLUCIÓN JUSTIFICACIÓN PROPUESTA</label>
                                                <textarea name="justificacion" class="form-control" required>{{ old('justificacion', $datosConciliador->resolicion_justificacion ?? '') }}</textarea>
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
                                                <textarea name="segunda" class="form-control" required>{{ old('segunda', $datosConciliador->resolicion_segunda ?? '') }}</textarea>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                       
                                        <div class="col-xs-12 col-sm-12 col-md-6"><br>
                                            {{--<input type="hidden" name="bandera_regresar" value="{{ $bandera }}">--}}
                                            <label for="name">Final de la audiencia</label>
                                            <select id="tipo_de_conclucion_visual" class="form-control" disabled>
                                                <option value="">Seleccione</option>
                                                <option value="Conciliacion" @selected(old('conclucion', $datosConciliador->conclucion ?? '') == 'Conciliacion')>Hubo Convenio</option>
                                                <option value="No conciliacion" @selected(old('conclucion', $datosConciliador->conclucion ?? '') == 'No conciliacion')>No Hubo Convenio</option>
                                                {{--<option value="Reinstalacion" @selected(old('conclucion', $datosConciliador->conclucion ?? '') == 'Reinstalacion')>Reinstalación</option>--}}
                                            </select>
                                            <input type="hidden" name="conclucion" id="tipo_de_conclucion" value="{{ old('conclucion', $datosConciliador->conclucion ?? '') }}">
                                        </div>                                          
                                    </div>

                                    <div id="dias" class="row gx-2 align-items-end">
                                        <div class="col-xs-12 col-sm-12 col-md-2"><br>
                                            <div class="form-group">
                                                <label for="name">Días de vacaciones</label>
                                                <input type="number" step="0.001" name="vacaciones" class="form-control" value="{{ old('vacaciones', $datosConciliador->vacaciones ?? '') }}"> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2"><br>
                                            <div class="form-group">
                                                <label for="name">Días de Aguinaldo</label>
                                                <input type="number" step="0.001" name="aguinaldo" class="form-control" value="{{ old('aguinaldo', $datosConciliador->aguinaldo ?? '') }}"> 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-1">
                                            <div class="form-group">
                                                <label for="name">Otros</label>
                                                <input type="number" step="0.001" name="otros" class="form-control" value="{{ old('otros', $datosConciliador->otros ?? '') }}"> 
                                                <div class="invalid-feedback">
                                                    El campo otro es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Horario laboral</label>
                                                <input type="text" name="horario" maxlength="120" class="form-control" value="{{ old('horario', $datosConciliador->horario ?? '') }}">
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Horario de comida</label>
                                                <input type="text" name="comida" maxlength="50" class="form-control" value="{{ old('comida', $datosConciliador->comida ?? '') }}">
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4"><br>
                                            <div class="form-group">
                                                <label for="name">Especifique el monto que quedará señalado para el concepto de pena convencional</label>
                                                <input type="number" step="0.001" name="pena_convencional" class="form-control"  value="<?=number_format($montoPena, 2)?>" oninput="this.value" placeholder="Ingrese solo 3 decimales" > 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-7">
                                            <div class="form-group">
                                                <label for="name">Especifique el domicilio laboral que aparecerá en el convenio</label>
                                                <input type="text" name="direccion_convenio" maxlength="150" class="form-control" value="<?=$direccion_convenio?>" oninput="this.value = this.value.toUpperCase()"  placeholder=" " > 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                      
                                    <div id="pagos" style="display:none">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <div class="form-group">
                                                <h4 class="text-center">Montos</h4>
                                            </div>
                                        </div>

                                        <div class="col-xs-12 col-sm-6 col-md-12">
                                            <button id="addRow" type="button" class="btn btn-info">Agregar Concepto de Pago</button>
                                        </div>
                                        
                                        <div id="newRow"></div>

                                        <div class="col-xs-12 col-sm-6 col-md-12"><br>
                                            <button id="addRetencion" type="button" class="btn btn-info">Agregar deducción</button>
                                        </div>
                                        
                                        <div id="newRowDeduccion"></div>

                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                            <div id="div_pagos_diferidos1"><br>
                                                <button id="addPago" type="button" class="btn btn-info">Agregar Cumplimiento</button>
                                                <div id="newRowaPago"></div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <h4 class="text-center" style="margin-top:20px;">Total a pagar:</h4>
                                            <h3 id="totalCalculado" class="text-center" style="color:green;">$0.00</h3>
                                        </div>
                                        <div id="div_pagos_diferidos"></div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-6"><br>
                                                <label for="name">Tipo de audiencia</label>
                                                <select id="tipo_audiencia" name="tipo_audiencia" class="form-control">
                                                    <option value="">Seleccione</option>
                                                    <option value="Presencial" @selected(old('tipo_audiencia', $datosConciliador->tipo_audiencia ?? '') == 'Presencial')>Presencial</option>
                                                    <option value="Virtual" @selected(old('tipo_audiencia', $datosConciliador->tipo_audiencia ?? '') == 'Virtual')>Virtual</option>
                                                </select>
                                            </div> 
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <br><button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </div>
 
                                    <div id="no_conciliacion" style="display:none"><br>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <label>Motivo del porque no hubo convenio</label>
                                            <textarea name="observaciones" class="form-control">{{ old('observaciones', $solicitudOriginal->observaciones ?? '') }}</textarea>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <br><button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </div>

                                    <div id="archivada" style="display:none">
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <br><button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
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

<div id="nuevo_turno" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

<style>
    .fc-event {
            padding: 3px 6px !important;
            border-radius: 4px !important;
            font-size: 12px !important;
            cursor: pointer;
        }

        #calendar {
            width: 100%;
            min-height: 500px;
        }

        .fc-event-disponible {
            color: #ffff !important;
            background-color: #00CE1C !important;
            border-color: #00CE1C !important;
            cursor: pointer;
        }

        .fc-event-expirado {
            color: #ffff !important;
            background-color: #F59727 !important;
            border-color: #F59727 !important;
            cursor: not-allowed;
        }

        .fc-event-inhabil {
            color: #ffff !important;
            background-color: #3B78DB !important;
            border-color: #3B78DB !important;
            cursor: not-allowed;
        }

        .fc-event-ocupado {
            color: #ffff !important;
            background-color: #DA0909 !important;
            border-color: #DA0909 !important;
            cursor: not-allowed;
        }

        .fc-event-selected {
            border: 2px solid #FFD700 !important;
            box-shadow: 0 0 8px #FFD700;
        }

        .resumenCita .alert-info {
            background-color: #e0e7ff;
            color: #1e293b;              
            border: 1px solid #6366f1;   
            border-radius: 8px;
            font-size: 10px;
            padding: 8px 16px;
            margin-bottom: 0;
            box-shadow: 0 2px 8px rgba(99,102,241,0.08);
        }

        .btn-custom-morado {
            height: 55px;
            font-size: 10px;
            padding: 5px 10px;
            margin-bottom: 5px;
            background-color: #6A0F49 !important;
            color: #fff !important;
            border: none;
        }
        
        .btn-custom-morado:hover, .btn-custom-morado:focus {
            background-color: #530c3a !important;
            color: #fff !important;
        }
</style>

@section('scripts')
    <script src="../../public/assets/js/turnos/turnos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css">
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script>
       let filaActualParaAgendar = null; 
       let pagosExistentes = {{ $pagos->count() }};
       document.getElementById("no_conciliacion").style.display = "none";
       //document.getElementById("archivada").style.display = "none";
       document.getElementById("dias").style.display = "none";
       document.getElementById("pagos").style.display = "none";
       
        $( document ).ready(function() {
            // Agregar registro
            $("#addRow").click(function () {
                var html = '';
                html += '<div id="inputFormRow1" class="row mb-2 align-items-end">';

                // Tipo de pago
                html +='<div class="col-xs-12 col-sm-12 col-md-6">';
                    html +='<div class="form-group">';
                    html +='<label for="confirm-password"><br>Prestación</label>';
                    html +='<select class="form-control tipo-pago-select" name="tipo_pago[]" >';
                    html +='<option value="">Seleccione</option>';
                    html +='<option value="Aguinaldo">Días de aguinaldo</option>';
                    html +='<option value="Días de sueldo">Días de sueldo</option>';
                    html +='<option value="Vacaciones">Días de vacaciones</option>';
                    html +='<option value="Prima Vacacional">Prima vacacional</option>';
                    html +='<option value="Gratificación A">Gratificación A (Con base al salario integrado)</option>';
                    html +='<option value="Gratificación B">Gratificación B (20 Días por año cumplido)</option>';
                    html +='<option value="Gratificación C">Gratificación C (Prima de antigüedad topada)</option>';
                    html +='<option value="Gratificación D">Gratificación D (Incluye cualquier otra prestación)</option>';
                    html +='<option value="Gratificación E">Gratificación E (Prestaciones en especie)</option>';
                    html +='<option value="Gratificación F">Gratificación F (Reconocimiento de derechos)</option>';
                    html +='<option value="Otras">Otros concepto de pago</option>';
                    html +='</select>';
                    // Campo para escribir otra prestación (solo si se selecciona "Otras")
                    html += '<div class="otra-prestacion-input" style="display: none; margin-top: 10px;">';
                    html += '<input type="text" class="form-control" name="otra_prestacion[]" maxlength="200" placeholder="Especifique la prestación" />';
                    html += '</div>';
                    html +='<div class="invalid-feedback">El tipo de pago es obligatorio.</div>';
                    html += '</div> </div>';
                
                // Monto a pagar
                html += '<div class="col-xs-12 col-sm-12 col-md-6">';
                html += '<div class="form-group">';
                html += '<label for="password">Monto a pagar</label>';
                html +='<input type="text" class="form-control" name="monto_pago[]" oninput="validarNumero(this)" placeholder="$">';
                html += '<div class="invalid-feedback">El monto es obligatorio.</div>';
                html += '</div> </div>';

                html += '<div class="input-group-append">';
                html += '<button class="removeRow btn btn-danger" type="button">Borrar</button>';
                html += '</div>';
                html += '</div>';

                $('#newRow').append(html);
                //Aseguramos que el nuevo select respete required según el estatus actual
                if (typeof syncPrestacionesRequired === 'function') {
                    syncPrestacionesRequired();
                }
            });

            // Borrar concepto
            $(document).on('click', '.removeRow', function () {
                $(this).closest('#inputFormRow1').remove();
                //calcularTotal();
            });

        // Agregar pago
        $("#addPago").click(function () {
            let numeroPago = $('.numero_pago').length + 1;
            var html = '';
            html += '<div class="inputFormRow2 row mb-2 align-items-end">';
            html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            html += '<label for="confirm-password"><br>Fecha y hora de pago</label>';
            html += '<div class="row">';
            html += '<div class="row">';

            if (numeroPago === 1) {
                html += '<label for="tipoPago">Seleccione una opción:</label>';
                html += '<select name="tipoPago" id="tipoPago" class="form-control">';
                html += '<option value="">-- Por favor, seleccione --</option>';
                html += '<option value="pagarAudiencia">Pagar en Audiencia</option>';
                html += '<option value="agendar">Agendar Pago</option>';
                html += '</select>';
            } else {
                //Botón de selección de horario
                html += '<div class="col-12">';
                html += '<button type="button" class="btn btn-custom-morado w-75 btn-open-calendar" data-bs-toggle="modal" data-bs-target="#calendarModal"> Seleccionar Horario</button>';
                html += '</div>';
                html += '</div>';
                html += '<div class="row">';
            }
            //Alerta de seleción de horario
            html += '<div class="col-12 mt-2">';
            html += '<div class="resumenCita" style="display:none;width:100%;">';
            html += '<div class="alert alert-info w-75">';
            html += '<strong>Cita seleccionada:</strong> <span class="fechaResumen"></span> a las <span class="horaResumen"></span>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            //botón dinámico
            html += '<div class="contenedor-boton-pago"></div>';
            html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            //Monto a pagar
            html += '<label for="password">Monto a pagar</label>';
            html += '<input type="text" class="form-control" name="monto_pagos[]" required oninput="validarNumero(this)" >';
            html += '<div class="invalid-feedback">La Dirección es obligatoria.</div>';
            html += '</div></div>';
            // Descripción de pago
            html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            html += '<label for="password">Descripción</label>';
            html += '<input type="text" class="form-control numero_pago" name="descripcion_pagos[]" readonly >';
            html += '<div class="invalid-feedback">La Dirección es obligatoria.</div>';
            html += '</div></div>';
            html += '<div class="input-group-append">';
            html += '<button class="removeRow2 btn btn-danger" type="button">Borrar</button>';
            html += '</div>';
            html += '</div>';
            $('#newRowaPago').append(html);
            actualizaNumeroPago();
            
            //Esto reemplaza el container por los botones de opciones, para que aparezca debajo del selector de opciones
            $('#newRowaPago').find('#tipoPago').last().on('change', function() {
                var opcionPago = $(this).val();
                var parent = $(this).closest('.inputFormRow2');
                var contenedor = parent.find('.contenedor-boton-pago');
                contenedor.empty()
                parent.find('input[name="tipo_pagoAgenda[]"]').remove();
                if (opcionPago === "pagarAudiencia") {
                    parent.append('<input type="hidden" name="tipo_pagoAgenda[]" value="Conciliador">');
                    contenedor.replaceWith('<div class="contenedor-boton-pago col-12 mb-2 mt-2"><button type="button" class="btn btn-success h-100 w-75" id="btnPagarAudiencia">Pagar en la audiencia</button></div>');
                    $(document).on('click', '#btnPagarAudiencia', function() {
                        filaActualParaAgendar = $(this).closest('.inputFormRow2');
                        if (typeof applyAudienciaDateToRow === 'function') {
                            applyAudienciaDateToRow(filaActualParaAgendar);
                        } else {
                            mostrarSelectHorasAudiencia();
                        }
                    });
                } else if (opcionPago === "agendar") {
                    parent.append('<input type="hidden" name="tipo_pagoAgenda[]" value="Audiencia">');
                    contenedor.replaceWith('<div class="contenedor-boton-pago col-12 mb-2 mt-2"><button type="button" class="btn btn-custom-morado w-75 btn-open-calendar" data-bs-toggle="modal" data-bs-target="#calendarModal"> Seleccionar Horario</button></div>');
                } else {
                    contenedor.replaceWith('<div class="contenedor-boton-pago"></div>');
                }
            });
        });

        $(document).on('click', '.btn-open-calendar', function() {
            // Guardamos la referencia de la fila (inputFormRow2) específica donde se dio clic
            filaActualParaAgendar = $(this).closest('.inputFormRow2');
        });

        window.mostrarSelectHorasAudiencia = function() {
            var hoy = new Date();
            var fechaHoy = hoy.toISOString().split('T')[0];
            var pagoBlock = (typeof filaActualParaAgendar !== 'undefined' && filaActualParaAgendar) ? filaActualParaAgendar : $(document).find('.inputFormRow2').last();
            pagoBlock.find('input[name="dias_pagos[]"], input[name="hora_pagos[]"]').remove();
            var fechaAud = (document.getElementById('audiencia_fecha') && document.getElementById('audiencia_fecha').value) ? document.getElementById('audiencia_fecha').value : fechaHoy;
            pagoBlock.append('<input type="hidden" name="dias_pagos[]" value="'+fechaAud+'">');
            pagoBlock.append('<input type="hidden" name="hora_pagos[]" value="'+horaSeleccionada+'">');

            var fechaAudTxt = (document.getElementById('audiencia_fecha') && document.getElementById('audiencia_fecha').value) ? document.getElementById('audiencia_fecha').value : fechaHoy;
            pagoBlock.find('.fechaResumen').text(fechaAudTxt);

            var horaToShow = '';
            if (/^\d{2}:\d{2}(:\d{2})?$/.test(horaSeleccionada)) {
                horaToShow = horaSeleccionada.substring(0,5);
            } else if (horaSeleccionada && horaSeleccionada.length >= 5) {

                var idx = horaSeleccionada.indexOf(':');
                if (idx !== -1) {
                    horaToShow = horaSeleccionada.substring(idx-2, idx+3);
                }
            }
            pagoBlock.find('.horaResumen').text(horaToShow);
            pagoBlock.find('.resumenCita').show();
        };

        window.applyAudienciaDateToRow = function(fila) {
            var pagoBlock = (fila && $(fila).length) ? $(fila) : ((typeof filaActualParaAgendar !== 'undefined' && filaActualParaAgendar) ? $(filaActualParaAgendar) : $(document).find('.inputFormRow2').last());
            if (!pagoBlock || pagoBlock.length === 0) return;

            // limpiar valores previos
            pagoBlock.find('input[name="dias_pagos[]"], input[name="hora_pagos[]"]').remove();

            var fechaAud = (document.getElementById('audiencia_fecha') && document.getElementById('audiencia_fecha').value) ? document.getElementById('audiencia_fecha').value : new Date().toISOString().split('T')[0];
            var horaSeleccionada = (document.getElementById('audiencia_hora') && document.getElementById('audiencia_hora').value) ? document.getElementById('audiencia_hora').value.trim() : '';

            // Solo agregar hora si es válida (HH:MM o HH:MM:SS)
            var horaValida = /^\d{2}:\d{2}(:\d{2})?$/.test(horaSeleccionada);

            // siempre agregamos la fecha; la hora solo si es válida
            pagoBlock.append('<input type="hidden" name="dias_pagos[]" value="'+fechaAud+'">');
            if (horaValida) {
                pagoBlock.append('<input type="hidden" name="hora_pagos[]" value="'+horaSeleccionada+'">');
            }

            // actualizar resumen visible
            pagoBlock.find('.fechaResumen').text(fechaAud);
            var horaToShow = '';
            if (horaValida) {
                horaToShow = horaSeleccionada.substring(0,5);
            }
            pagoBlock.find('.horaResumen').text(horaToShow);
            pagoBlock.find('.resumenCita').show();
        };

        // Borrar pago
        $(document).on('click', '.removeRow2', function () {
            $(this).closest('.inputFormRow2').remove();
            actualizaNumeroPago();
            setTimeout(function(){ if (typeof calcularTotal === 'function') { calcularTotal(); } }, 100);
        });
        //Actualiza los números de pago
        function actualizaNumeroPago() {
            let pagosNuevos = $('.numero_pago');
            // total = pagos existentes en BD + nuevos agregados
            let totalPagos = pagosExistentes + pagosNuevos.length;
            // Si solamente habrá un pago total
            if (totalPagos === 1) {
                pagosNuevos.each(function () {
                    $(this).val("Cumplimiento total de convenio");
                });
            } else {
                pagosNuevos.each(function(index) {
                    // El consecutivo inicia desde los existentes
                    let numeroParcialidad = pagosExistentes + index + 1;
                    $(this).val("Parcialidad " + numeroParcialidad);
                });
            }
        }
        /*function actualizaNumeroPago() {
            let pagos = $('.numero_pago');
            if (pagos.length === 1) {
                pagos.eq(0).val("Cumplimiento total de convenio");
            } else {
                pagos.each(function(index) {
                   $(this).val("Cumplimiento " + (index + 1));
                });
            }
        }
        /*function actualizaNumeroPago() {
            let pagos = $('.numero_pago');
            if (pagos.length === 1) {
                pagos.eq(0).val("Pago único");
            } else {
                pagos.each(function(index) {
                   $(this).val("Parcialidad " + (index + 1));
                });
            }
        }

        // Borrar pago 
        $(document).on('click', '.removeRow2', function () {
            $(this).closest('.inputFormRow2').remove();
        });*/
       
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
                    html +='<input type="text" class="form-control" name="descripcion_deduccion[]" maxlength="150" required oninput="this.value = this.value.toUpperCase()" >';
                    html += '<div class="invalid-feedback">';
                    html += 'La Descripción es obligatoria.';
                    html += '</div> </div> </div>';

                    //MONTO A PAGAR
                    html += '<div class="col-xs-12 col-sm-12 col-md-12">';
                    html += '<div class="form-group">';
                    html += '<label for="password">Monto a pagar</label>';
                    html +='<input type="text" class="form-control" name="monto_deduccion[]" required oninput="validarNumero(this)" placeholder="$ Solo números y puntos" >';
                    html += '<div class="invalid-feedback">';
                    html += 'El monto es obligatorio.';
                    html += '</div> </div> </div>';

                    html += '<div class="input-group-append">';
                    html += '<button class="removeRow3 btn btn-danger" type="button">Borrar</button>';
                    html += '</div>';

                html += '</div>';

            $('#newRowDeduccion').append(html);
            
            var $row = $('#newRowDeduccion').children().last();
            $row.find('input[name="descripcion_deduccion[]"]').prop('required', true);
            $row.find('input[name="monto_deduccion[]"]').prop('required', true);
        });

        // Borrar pago
        $(document).on('click', '.removeRow3', function () {
            $(this).closest('.col-xs-12').remove();
        });
        });
        
        //CALCULO DE PAGO TOTAL
        // Calcular prestaciones
        $(document).on('input', 'input[name="monto_pago[]"]', function () {
            calcularTotal();
        });

        // Calcular deducciones
        $(document).on('input', 'input[name="monto_deduccion[]"]', function () {
            calcularTotal();
        });
        $(document).on('click', '.removeRow, .removeRow3', function () {
            setTimeout(calcularTotal, 100);
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
        
        function mostrar_resolicion() {
            document.getElementById("justificacion").style.display = "block";
        }
        function mostrar_segunda() {
            document.getElementById("segunda").style.display = "block";
        }

        //Tipo de finalización de la audiencia, muestra campos dependiendo de la finalización
        $(document).ready(function() {
            // Función que evalúa el valor y muestra/oculta secciones
            function evaluarEstadoInicial() {
                const valor = $("#tipo_de_conclucion").val(); 
                console.log("Estado de la audiencia detectado:", valor); // Para depuración

                if (valor === 'Conciliacion' || valor === 'Reinstalacion') {
                    $("#pagos").show(); // Muestra el div que contiene los botones de agregar
                    $("#dias").show();
                    $("#no_conciliacion").hide();
                    $("#archivada").hide();
                    
                    // Si el total a pagar es 0 al inicio, forzar cálculo
                    setTimeout(calcularTotal, 500); 
                } 
                else if (valor === 'No conciliacion') {
                    $("#no_conciliacion").show();
                    $("#pagos").hide();
                    $("#dias").hide();
                }
            }
            evaluarEstadoInicial();
        });

        //Re-aplicar required a los selects de prestaciones.
        function syncPrestacionesRequired() {
            if (!tipo_iden) return;
            const valor = tipo_iden.value;
            const convenio = (valor === 'Conciliacion' || valor === 'Reinstalacion');

            document.querySelectorAll('select[name="tipo_pago[]"]').forEach(function(sel) {
                sel.required = convenio;
                if (!convenio) sel.classList.remove('is-invalid');
            });

            //El monto de cada prestación solo es requerido cuando aplica convenio
            document.querySelectorAll('input[name="monto_pago[]"]').forEach(function(inp) {
                inp.required = convenio;
                if (!convenio) inp.classList.remove('is-invalid');
            });

            document.querySelectorAll('.otra-prestacion-input').forEach(function(container) {
                const input = container.querySelector('input[name="otra_prestacion[]"]');
                if (!input) return;
                const select = container.closest('.form-group') ? container.closest('.form-group').querySelector('select[name="tipo_pago[]"]') : null;
                const otras = !!(select && select.value === 'Otras');
                input.required = convenio && otras;
                if (!input.required) input.classList.remove('is-invalid');
            });

            document.querySelectorAll('select#tipoPago').forEach(function(sel) {
                sel.required = convenio;
                sel.disabled = !convenio;
                if (!convenio) {
                    sel.classList.remove('is-invalid');
                    sel.value = '';
                }
            });
            document.querySelectorAll('input[name="monto_pagos[]"]').forEach(function(inp) {
                inp.required = convenio;
                if (!convenio) inp.classList.remove('is-invalid');
            });
        }

        if (tipo_iden) {
            tipo_iden.addEventListener('change', function() {
                const valorSeleccionado = this.value;

                syncPrestacionesRequired();

                // Realiza la validación o acciones necesarias
                if (valorSeleccionado === 'Conciliacion' || valorSeleccionado === 'Reinstalacion') {
                    document.getElementById('no_conciliacion').style.display = "none";
                    document.getElementById('archivada').style.display = "none";
                    document.getElementById("pagos").style.display = "block";
                    document.getElementById('dias').style.display = "block";

                    var vac = document.querySelector('input[name="vacaciones"]');
                    var agu = document.querySelector('input[name="aguinaldo"]');
                    var hor = document.querySelector('input[name="horario"]');
                    var com = document.querySelector('input[name="comida"]');
                    var pen = document.querySelector('input[name="pena_convencional"]');
                    var dirc = document.querySelector('input[name="direccion_convenio"]');

                    //var tau = document.querySelector('[name="tipo_audiencia"]');
                    if(vac) vac.required = true;
                    if(agu) agu.required = true;
                    if(hor) hor.required = true;
                    if(com) com.required = true;
                    if(pen) pen.required = true;
                    if(dirc) dirc.required = true;
                    //if(tau) tau.required = true;
                    const tau = document.getElementById('tipo_audiencia');
                    tau.required = true;
                }
                else if (valorSeleccionado === 'No conciliacion'){
                    document.getElementById('no_conciliacion').style.display = "block";
                    document.getElementById('archivada').style.display = "none"
                    document.getElementById("pagos").style.display = "none";
                    document.getElementById('dias').style.display = "none";

                    var vac2 = document.querySelector('input[name="vacaciones"]');
                    var agu2 = document.querySelector('input[name="aguinaldo"]');
                    var hor2 = document.querySelector('input[name="horario"]');
                    var com2 = document.querySelector('input[name="comida"]');
                    var pen2 = document.querySelector('input[name="pena_convencional"]');
                    var dirc2 = document.querySelector('input[name="direccion_convenio"]');
                    //var tau2 = document.querySelector('[name="tipo_audiencia"]');
                    if(vac2) vac2.required = false;
                    if(agu2) agu2.required = false;
                    if(hor2) hor2.required = false;
                    if(com2) com2.required = false;
                    if(pen2) pen2.required = false;
                    if(dirc2) dirc2.required = false;
                    //if(tau2) tau2.required = false;
                    const tau2 = document.getElementById('tipo_audiencia');
                    tau2.required = false;
                } 
            else if (valorSeleccionado === 'Reagenda'){
                document.getElementById('no_conciliacion').style.display = "none";
                document.getElementById('archivada').style.display = "none"
                document.getElementById("pagos").style.display = "none";
                document.getElementById('dias').style.display = "none";

                var penR = document.querySelector('input[name="pena_convencional"]');
                var dircR = document.querySelector('input[name="direccion_convenio"]');
                if(penR) penR.required = false;
                if(dircR) dircR.required = false;

                const tau2 = document.getElementById('tipo_audiencia');
                tau2.required = false;

                function abrirModalReagendar(){
                    var solicitudEl = document.getElementById('solicitud-id');
                    var solicitudId = solicitudEl ? solicitudEl.value : '{{ $id ?? '' }}';
                    var modalInput = document.getElementById('modal-id-reagendar');
                    if(modalInput) modalInput.value = solicitudId;

                    var primeraVal = $('textarea[name="primera"]').val() || '';
                    var justificacionVal = $('textarea[name="justificacion"]').val() || '';
                    var segundaVal = $('textarea[name="segunda"]').val() || '';
                    $('#reagenda_primera').val(primeraVal);
                    $('#reagenda_justificacion').val(justificacionVal);
                    $('#reagenda_segunda').val(segundaVal);

                    $('#ModalReagendar').modal('show');
                }

                if (window.Swal && typeof Swal.fire === 'function'){
                    Swal.fire({
                        title: 'Calendario de Reagenda',
                        text: 'Se mostrará el calendario para seleccionar nueva fecha y hora.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Abrir calendario',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        customClass: { confirmButton: 'btn btn-success', cancelButton: 'btn btn-secondary' },
                        buttonsStyling: false
                    }).then(function(result){
                        if(result.isConfirmed){
                            abrirModalReagendar();
                        } else {
                            tipo_iden.value = 'Seleccione';
                        }
                    });
                } else {
                    var confirmar = confirm('Se abrirá el calendario para reagendar. ¿Deseas continuar?');
                    if(confirmar){
                        abrirModalReagendar();
                    } else {
                        tipo_iden.value = 'Seleccione';
                    }
                }
            }
            else if (valorSeleccionado === 'Archivada por incomparecencia') {
                const confirmar = confirm("¿Estás seguro de que deseas archivar esta audiencia?");
                if (confirmar) {
                    var primeraValA = $('textarea[name="primera"]').val() || '';
                    var justificacionValA = $('textarea[name="justificacion"]').val() || '';
                    var segundaValA = $('textarea[name="segunda"]').val() || '';
                    $('#archivar_primera').val(primeraValA);
                    $('#archivar_justificacion').val(justificacionValA);
                    $('#archivar_segunda').val(segundaValA);

                    $('#ModalArchivar').modal('show');
                    document.getElementById('no_conciliacion').style.display = "none";
                    document.getElementById('archivada').style.display = "block";
                    document.getElementById("pagos").style.display = "none";
                    document.getElementById('dias').style.display = "none";

                    var penA = document.querySelector('input[name="pena_convencional"]');
                    var dircA = document.querySelector('input[name="direccion_convenio"]');
                    if(penA) penA.required = false;
                    if(dircA) dircA.required = false;

                    const tau3 = document.getElementById('tipo_audiencia');
                    tau3.required = false;
                } else {
                    document.getElementById('no_conciliacion').style.display = "none";
                    document.getElementById('archivada').style.display = "none";
                    document.getElementById("pagos").style.display = "none";
                    document.getElementById('dias').style.display = "none";
                    this.value = "Seleccione"; // Regresa al estado inicial

                    var vac3 = document.querySelector('input[name="vacaciones"]');
                    var agu3 = document.querySelector('input[name="aguinaldo"]');
                    var hor3 = document.querySelector('input[name="horario"]');
                    var com3 = document.querySelector('input[name="comida"]');
                    var pen3 = document.querySelector('input[name="pena_convencional"]');
                    var dirc3 = document.querySelector('input[name="direccion_convenio"]');
                    //var tau3 = document.querySelector('[name="tipo_audiencia"]');
                    if(vac3) vac3.required = false;
                    if(agu3) agu3.required = false;
                    if(hor3) hor3.required = false;
                    if(com3) com3.required = false;
                    if(pen3) pen3.required = false;
                    if(dirc3) dirc3.required = false;
                    if(tau3) tau3.required = false;
                    const tau3 = document.getElementById('tipo_audiencia');
                    tau3.required = false;

                }
            }
            else if (valorSeleccionado === 'Seleccione'){
                document.getElementById('no_conciliacion').style.display = "none";
                document.getElementById('archivada').style.display = "none";
                document.getElementById("pagos").style.display = "none";
                document.getElementById('dias').style.display = "none";

                var vac4 = document.querySelector('input[name="vacaciones"]');
                var agu4 = document.querySelector('input[name="aguinaldo"]');
                var hor4 = document.querySelector('input[name="horario"]');
                var com4 = document.querySelector('input[name="comida"]');
                var pen4 = document.querySelector('input[name="pena_convencional"]');
                var dirc4 = document.querySelector('input[name="direccion_convenio"]');
                //var tau4 = document.querySelector('[name="tipo_audiencia"]');
                if(vac4) vac4.required = false;
                if(agu4) agu4.required = false;
                if(hor4) hor4.required = false;
                if(com4) com4.required = false;
                if(pen4) pen4.required = false;
                if(dirc4) dirc4.required = false;
                if(tau4) tau4.required = false;
                const tau4 = document.getElementById('tipo_audiencia');
                tau4.required = false;
            }
        });    
    }

        (function(){
            if(!tipo_iden) return;

            //Sync inicial al cargar la vista.
            syncPrestacionesRequired();

            const convenio = (tipo_iden.value === 'Conciliacion' || tipo_iden.value === 'Reinstalacion');
            var penInit = document.querySelector('input[name="pena_convencional"]');
            var dircInit = document.querySelector('input[name="direccion_convenio"]');
            if(penInit) penInit.required = convenio;
            if(dircInit) dircInit.required = convenio;
        })();

         $('.open-modal').click(function() {
            const id = $(this).data('id'); // Obtiene el valor de data-id
            document.getElementById('modal-id').value = id;
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('tipo_de_conclucion');
            const dias = document.getElementById('dias');

            // Escuchar cuando se cambie la opción del select
            select.addEventListener('change', function () {
                if (select.value === 'Conciliacion' || select.value === 'Reinstalacion') {
                    dias.style.display = 'flex'; // o 'block' si quieres que sea vertical
                } else {
                    dias.style.display = 'none';
                }
            });
        });
    </script>

    <script>
        let calendarModal;
        $('#calendarModal').on('shown.bs.modal', function () {
            if (calendarModal) {
                calendarModal.destroy();
            }
            var calendarEl = document.getElementById('calendar');
            
            calendarModal = new FullCalendar.Calendar(calendarEl, {
                initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                },
               
                validRange: {
                    start: (() => {
                        const now = new Date();
                        return new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0];
                    })(),
                    end: (() => {
                        const now = new Date();
                        return new Date(now.getFullYear(), now.getMonth() + 7, 0).toISOString().split('T')[0];
                    })()
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    // Obtener sede seleccionada
                    var sede = document.getElementById('sede').value;
                    // Hacer petición AJAX con parámetro sede
                    $.ajax({
                        url: '{{ url("/api/obtenerCumplimientosFiltrado") }}',
                        method: 'GET',
                        data: {
                            sede: sede,
                            conciliador_id: {{ $conciliadorId }},
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr
                        },
                        success: function(data) {
                            successCallback(data);
                        },
                        error: function(xhr, status, err) {
                            console.error('calendarModal: error al cargar eventos', status, err, xhr && xhr.responseText);
                            failureCallback('Error al cargar eventos');
                        }
                    });
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    //second: '2-digit',
                    //hour12: false
                },
                eventClick: function(info) {
                    let ahora = new Date();
                    let slotDate = new Date(info.event.start);
                    let estado = info.event.extendedProps && info.event.extendedProps.estado ? info.event.extendedProps.estado : null;

                    if (estado === 'disponible' && slotDate > ahora) {
                        // Deseleccionar evento anterior
                        document.querySelectorAll('.fc-event-selected').forEach(el => {
                            el.classList.remove('fc-event-selected');
                        });
                        // Seleccionar este evento
                        info.el.classList.add('fc-event-selected');
                        window.selectedEvent = info.event;
                    } else {
                        alert('Este horario no está disponible. Por favor seleccione otro.');
                    }
                },
                eventDidMount: function(info) {
                    // Añade clases CSS según el tipo de evento
                    if (info.event.extendedProps.estado === 'disponible') {
                        info.el.classList.add('fc-event-disponible');
                    } else if (info.event.extendedProps.estado === 'expirado') {
                        info.el.classList.add('fc-event-expirado');
                    } else if (info.event.extendedProps.estado === 'inhabil') {
                        info.el.classList.add('fc-event-inhabil');
                    } else {
                        info.el.classList.add('fc-event-ocupado');
                    }
                },
            });
            calendarModal.render();
            setTimeout(function(){ if (calendarModal) { calendarModal.updateSize(); calendarModal.refetchEvents(); } }, 200);

            function updateCalendarView() {
                if (window.innerWidth < 768) {
                    calendarModal.changeView('listWeek');
                } else {
                    calendarModal.changeView('dayGridWeek');
                }
            }

            window.addEventListener('resize', updateCalendarView);
        });

        // Confirmar selección
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('confirmarSeleccion').addEventListener('click', function() {
                if (window.selectedEvent && filaActualParaAgendar) {
                    const fechaHora = new Date(window.selectedEvent.start);
                    const fecha = fechaHora.toISOString().split('T')[0];
                    const hora = fechaHora.toTimeString().substring(0, 8);

                    var pagoBlock = filaActualParaAgendar;
                    
                    pagoBlock.find('input[name="dias_pagos[]"], input[name="hora_pagos[]"]').remove();
                    
                    pagoBlock.append('<input type="hidden" name="dias_pagos[]" value="'+fecha+'">');
                    pagoBlock.append('<input type="hidden" name="hora_pagos[]" value="'+hora+'">');
                    pagoBlock.find('.fechaResumen').text(fecha);
                    pagoBlock.find('.horaResumen').text(hora.substring(0,5));
                    pagoBlock.find('.resumenCita').show();

                    // Cerrar modal
                    document.activeElement.blur();
                    $('#calendarModal').modal('hide');
                
                    filaActualParaAgendar = null;
                } else {
                    alert('Por favor selecciona un horario disponible');
                }
            });
        });

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
    <script>
        window.__previewData = {!! isset($previewData) && $previewData ? json_encode($previewData, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) : 'null' !!};
        function generatePrestacionRow(tipoPagoVal, montoVal, otraPrestacionVal) {
            var html = '';
            html += '<div id="inputFormRow1" class="row mb-2 align-items-end">';
            html +='<div class="col-xs-12 col-sm-12 col-md-6">';
                html +='<div class="form-group">';
                html +='<label for="confirm-password"><br>Prestación</label>'; 
                html +='<select class="form-control tipo-pago-select" name="tipo_pago[]" >';
                html +='<option value="">Seleccione</option>';
                var options = ['Aguinaldo','Días de sueldo','Vacaciones','Prima Vacacional','Gratificación A','Gratificación B','Gratificación C','Gratificación D','Gratificación E','Gratificación F','Otras'];
                options.forEach(function(opt){
                    var sel = opt === tipoPagoVal ? ' selected' : '';
                    html += '<option value="'+opt+'"'+sel+'>'+opt.replace(/</g,'&lt;')+'</option>';
                });
                html +='</select>';
                html += '<div class="otra-prestacion-input" style="' + (tipoPagoVal === 'Otras' ? 'display:block; margin-top:10px;' : 'display:none; margin-top:10px;') + '">';
                html += '<input type="text" class="form-control" name="otra_prestacion[]" value="'+ (otraPrestacionVal ? String(otraPrestacionVal).replace(/"/g,'&quot;') : '') +'" placeholder="Especifique la prestación" />';
                html += '</div>';
                html +='<div class="invalid-feedback">El tipo de pago es obligatorio.</div>';
                html += '</div> </div>';
            html += '<div class="col-xs-12 col-sm-12 col-md-6">';
            html += '<div class="form-group">';
            html += '<label for="password">Monto a pagar</label>';
            html +='<input type="text" class="form-control" name="monto_pago[]" oninput="validarNumero(this)" placeholder="$" value="'+ (montoVal ? String(montoVal).replace(/"/g,'&quot;') : '') +'">';
            html += '<div class="invalid-feedback">El monto es obligatorio.</div>';
            html += '</div> </div>';
            html += '<div class="input-group-append">';
            html += '<button class="removeRow btn btn-danger" type="button">Borrar</button>';
            html += '</div>';
            html += '</div>';
            return html;
        }

        function generateDeduccionRow(descripcionVal, montoVal) {
            var html = '';
            html += '<div id="inputFormRow3" class="row">';
            html +='<div class="col-xs-12 col-sm-12 col-md-12"><br>';
            html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            html += '<label for="password">Descripción</label>';
            html +='<input type="text" class="form-control" name="descripcion_deduccion[]" value="'+ (descripcionVal ? String(descripcionVal).replace(/"/g,'&quot;') : '') +'" oninput="this.value = this.value.toUpperCase()" >';
            html += '<div class="invalid-feedback">La Descripción es obligatoria.</div> </div> </div>';
            html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            html += '<label for="password">Monto a pagar</label>';
            html +='<input type="text" class="form-control" name="monto_deduccion[]" oninput="validarNumero(this)" placeholder="$ Solo números y puntos" value="'+ (montoVal ? String(montoVal).replace(/"/g,'&quot;') : '') +'">';
            html += '<div class="invalid-feedback">El monto es obligatorio.</div> </div> </div>';
            html += '<div class="input-group-append"><button class="removeRow3 btn btn-danger" type="button">Borrar</button></div>';
            html += '</div>';
            return html;
        }

        function generatePagoRow(montoVal, diasVal, horaVal, descripcionVal, tipoAgendaVal) {
            var html = '';
            var esPagoAudiencia = (tipoAgendaVal === 'Conciliador');

            html += '<div class="inputFormRow2 row mb-2 align-items-end">';
            html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            html += '<label for="confirm-password"><br>Fecha y hora de pago</label>';
            html += '<div class="row">';
            
            html += '<div class="contenedor-boton-pago col-12 mb-2 mt-2">';
            if (esPagoAudiencia) {
                html += '<button type="button" class="btn btn-success h-100 w-75 btn-pagar-audiencia-accion">Pagar en la audiencia</button>';
            } else {
                html += '<button type="button" class="btn btn-custom-morado w-75 btn-open-calendar" data-bs-toggle="modal" data-bs-target="#calendarModal"> Seleccionar Horario</button>';
            }

            html += '</div>';
            html += '</div>';
            html += '<div class="col-12 mt-2 resumenCita">';
            html += '<div class="resumenCita" style="display:'+(diasVal? 'block':'none')+';width:100%;">';
            html += '<div class="alert alert-info w-75">';
            html += '<strong>Cita seleccionada:</strong> <span class="fechaResumen">'+ (diasVal? diasVal : '') +'</span> a las <span class="horaResumen">'+ (horaVal? (horaVal.substring? horaVal.substring(0,5): horaVal) : '') +'</span>';
            html += '</div></div></div>';
            html += '</div>';
            html += '<div class="contenedor-boton-pago"></div>';
            html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            html += '<label for="password">Monto a pagar</label>';
            html += '<input type="text" class="form-control" name="monto_pagos[]" required oninput="validarNumero(this)" value="'+ (montoVal ? String(montoVal).replace(/"/g,'&quot;') : '') +'">';
            html += '<div class="invalid-feedback">La Dirección es obligatoria.</div>';
            html += '</div></div>';
            html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            html += '<label for="password">Descripción</label>';
            html += '<input type="text" class="form-control numero_pago" name="descripcion_pagos[]" readonly value="'+ (descripcionVal ? String(descripcionVal).replace(/"/g,'&quot;') : '') +'">';
            html += '<div class="invalid-feedback">La Dirección es obligatoria.</div>'; 
            html += '</div></div>';
            html += '<div class="input-group-append">';
            html += '<button class="removeRow2 btn btn-danger" type="button">Borrar</button>';
            html += '</div>';
            html += '</div>';
            return html;
        }

        $(document).ready(function(){
            var data = window.__previewData;
            if(!data) return;
            try {
                // Basic fields
                if(data.primera) $('textarea[name="primera"]').val(data.primera);
                if(data.justificacion) $('textarea[name="justificacion"]').val(data.justificacion);
                if(data.segunda) $('textarea[name="segunda"]').val(data.segunda);
                if(data.vacaciones) $('input[name="vacaciones"]').val(data.vacaciones);
                if(data.aguinaldo) $('input[name="aguinaldo"]').val(data.aguinaldo);
                if(data.otros) $('input[name="otros"]').val(data.otros);
                if(data.horario) $('input[name="horario"]').val(data.horario);
                if(data.comida) $('input[name="comida"]').val(data.comida);
                if(data.pena_convencional) $('input[name="pena_convencional"]').val(data.pena_convencional);
                if(data.direccion_convenio) $('input[name="direccion_convenio"]').val(data.direccion_convenio);
                //if(data.conclucion) { $('#tipo_de_conclucion').val(data.conclucion).trigger('change'); }
                if(data.tipo_audiencia) $('select[name="tipo_audiencia"]').val(data.tipo_audiencia);

                // Prestaciones
                if(Array.isArray(data['tipo_pago'])){
                    for(var i=0;i<data['tipo_pago'].length;i++){
                        var tipo = data['tipo_pago'][i];
                        var monto = data['monto_pago'] && data['monto_pago'][i] ? data['monto_pago'][i] : '';
                        var otra = data['otra_prestacion'] && data['otra_prestacion'][i] ? data['otra_prestacion'][i] : '';
                        $('#newRow').append(generatePrestacionRow(tipo, monto, otra));
                    }
                    // Asegura required correcto en selects generados desde preview
                    if (typeof syncPrestacionesRequired === 'function') {
                        syncPrestacionesRequired();
                    }
                }

                // Deducciones
                if(Array.isArray(data['descripcion_deduccion'])){
                    for(var j=0;j<data['descripcion_deduccion'].length;j++){
                        var desc = data['descripcion_deduccion'][j];
                        var md = data['monto_deduccion'] && data['monto_deduccion'][j] ? data['monto_deduccion'][j] : '';
                        $('#newRowDeduccion').append(generateDeduccionRow(desc, md));
                    }
                }

                // Pagos diferidos
                if(Array.isArray(data['monto_pagos'])){
                    for(var k=0;k<data['monto_pagos'].length;k++){
                        var mp = data['monto_pagos'][k];
                        var dias = data['dias_pagos'] && data['dias_pagos'][k] ? data['dias_pagos'][k] : '';
                        var hora = data['hora_pagos'] && data['hora_pagos'][k] ? data['hora_pagos'][k] : '';
                        var descp = data['descripcion_pagos'] && data['descripcion_pagos'][k] ? data['descripcion_pagos'][k] : '';
                        var tipoAgenda = data['tipo_pagoAgenda'] && data['tipo_pagoAgenda'][k] ? data['tipo_pagoAgenda'][k] : '';
                        $('#newRowaPago').append(generatePagoRow(mp, dias, hora, descp, tipoAgenda));
                    }
                    // Reenumerar los pagos (Cumplimiento 1, 2, etc)
                    actualizaNumeroPago();
                }

                setTimeout(function(){ calcularTotal(); }, 200);
            } catch(e){ console.error('Error populating preview data', e); }
        });

        $(document).ready(function(){
            var data = window.__previewData;
            if(!data) return;

            var tipoAgenda0 = (data['tipo_pagoAgenda'] && data['tipo_pagoAgenda'][0]) ? data['tipo_pagoAgenda'][0] : '';
            var sel0 = $('#newRowaPago').find('#tipoPago').first();
            if(sel0.length){
                if(tipoAgenda0 === 'Conciliador'){
                    sel0.val('pagarAudiencia');
                } else if(tipoAgenda0 === 'Audiencia'){
                    sel0.val('agendar');
                }
                if(sel0.val()){
                    sel0.trigger('change');
                }
            }
        });

        $(document).ready(function(){
            $('input[name="monto_pago[]"], input[name="monto_deduccion[]"], input[name="monto_pagos[]"]').each(function(){
                var v = $(this).val();
                if(v){ var clean = String(v).replace(/[^0-9.]/g,''); $(this).val(clean); }
            });

            $('input[name="monto_pago[]"], input[name="monto_deduccion[]"], input[name="monto_pagos[]"]').trigger('input');

            calcularTotal();

            $('#form_roles').on('submit', function(e){
                var valid = true;

                //Solo exigimos validación de pagos/agenda cuando aplica convenio
                var tipoConcl = $('#tipo_de_conclucion').val();
                var requiereConvenio = (tipoConcl === 'Conciliacion' || tipoConcl === 'Reinstalacion');

                $('.inputFormRow2').each(function(index){
                    var block = $(this);

                    //Si NO aplica convenio, no validamos fecha/hora/opciones de pago y limpiamos algún input rezagado
                    if (!requiereConvenio) {
                        block.find('input[name="dias_pagos[]"], input[name="hora_pagos[]"], input[name="tipo_pagoAgenda[]"]').remove();
                        return;
                    }
                    
                    var tipoSelect = block.find('#tipoPago');
                    //Solo validar cuando el selector aplica (no está deshabilitado)
                    if(tipoSelect.length > 0 && !tipoSelect.prop('disabled') && !tipoSelect.val()){
                         alert('Por favor selecciona una opción de pago para el pago #' + (index + 1));
                         valid = false;
                         return false; 
                    }

                    var fecha = block.find('.fechaResumen').text().trim();
                    var hora = block.find('.horaResumen').text().trim();
                    if(fecha && block.find('input[name="dias_pagos[]"]').length === 0){
                        block.append('<input type="hidden" name="dias_pagos[]" value="'+fecha+'">');
                    }
                    if(hora && block.find('input[name="hora_pagos[]"]').length === 0){
                        var horaVal = '';
                        if (/^\d{2}:\d{2}$/.test(hora)) {
                            horaVal = hora + ':00';
                        } else if (/^\d{2}:\d{2}:\d{2}$/.test(hora)) {
                            horaVal = hora;
                        } 
                        if(horaVal) block.append('<input type="hidden" name="hora_pagos[]" value="'+horaVal+'">');
                    }
                    
                    var hasDias = block.find('input[name="dias_pagos[]"]').val();
                    var hasHora = block.find('input[name="hora_pagos[]"]').val();
                    
                    if(!hasDias || !hasHora){
                         alert('Por favor selecciona fecha y hora para el pago #' + (index + 1) + '.');
                         valid = false;
                         return false;
                    }
                    
                    if(block.find('input[name="tipo_pagoAgenda[]"]').length === 0){
                        block.append('<input type="hidden" name="tipo_pagoAgenda[]" value="Audiencia">');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    var btn = $(this).find('button[type="submit"]');
                    btn.prop('disabled', false).removeClass('disabled btn-progress');
                    $('.loader').parent().hide();
                    return false;
                }

                $('input[name="monto_pago[]"], input[name="monto_deduccion[]"], input[name="monto_pagos[]"]').each(function(){

                    var v = $(this).val();
                    var clean = v ? String(v).replace(/[^0-9.]/g,'') : '';
                    $(this).val(clean);
                });

                calcularTotal();
                return true;
            });
        });
    </script>
@endsection

<!-- Modal para seleccionar fecha y horario -->
<input type="hidden" id="sede" value="{{ $sede }}">

<div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Fecha y Horario de {{ $sede }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="calendar"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarSeleccion">Confirmar</button>
            </div>
        </div>
    </div>
</div>