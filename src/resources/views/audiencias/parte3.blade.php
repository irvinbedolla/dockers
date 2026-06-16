@extends('layouts.app')

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

                            <!--
                            <div class="card p-4 shadow-sm">
                                <h5 class="text-muted">Tiempo Restante:</h5>
                                <h1 id="temporizador" class="text-danger font-weight-bold">
                                    --:-- 
                                </h1>
                                <p id="mensaje-estado" class="mt-2"></p>
                            </div>
                            -->
                            
                            <!--Se realiza el envío de datos con formulario de Laravel Collective-->
                            <form class='needs-validation novalidate' id='form_roles' method='POST' action="{{route('concluir_audiencia_conciliador')}}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $id }}">
                                <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
                                <input type="hidden" name="audiencia_hora" id="audiencia_hora" value="{{ $audiencia_hora }}">
                                <input type="hidden" name="audiencia_fecha" id="audiencia_fecha" value="{{ $audiencia_fecha }}">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12"  style="border:1px solid black;">
                                        <div class="form-group">
                                            <label for="name">RESOLUCIÓN PRIMERA MANIFESTACIÓN</label>
                                            <textarea name="primera" class="form-control" required></textarea>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-2 col-sm-2 col-md-2"><br>
                                        <a class="btn btn-primary" onclick="mostrar_resolicion()">Continuar</a>
                                    </div>
                                    <br>                                 
                                    <!--
                                    <div id="" class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <label for="name">RESOLUCIÓN PROPUESTAS TRABAJADORES</label>
                                            <textarea name="trabajadores" class="form-control" required></textarea>
                                            <div class="invalid-feedback">
                                                El campo es obligatorio. 
                                            </div>
                                        </div>
                                    </div>
                                    -->
                                    <div id="justificacion" style="display:none"><br>
                                        <div class="col-xs-12 col-sm-12 col-md-12" style="border:1px solid black;">
                                            <div class="form-group">
                                                <label for="name">RESOLUCIÓN JUSTIFICACIÓN PROPUESTA</label>
                                                <textarea name="justificacion" class="form-control" required></textarea>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xs-2 col-sm-2 col-md-2"><br>
                                            <a class="btn btn-primary" onclick="mostrar_segunda()">Continuar</a>
                                        </div>
                                    </div><br>
                                    
                                    <div id="segunda" style="display:none"><br>
                                        <div class="col-xs-12 col-sm-12 col-md-12" style="border:1px solid black;">
                                            <div class="form-group">
                                                <label for="name">RESOLUCIÓN SEGUNDA MANIFESTACIÓN</label>
                                                <textarea name="segunda" class="form-control" required></textarea>
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-6"><br>
                                            <input type="hidden" name="bandera_regresar" value="{{ $bandera }}">
                                            <label for="name">Final de la audiencia</label>
                                            <select id="tipo_de_conclucion" name="conclucion" class="form-control">
                                                <option>Seleccione</option>
                                                <option value="Conciliacion">Hubo Convenio</option>
                                                <option value="No conciliacion">No Hubo Convenio</option>
                                                <option value="Reagenda">No Hubo Convenio (Se desea reagendar)</option>
                                                <option value="Reinstalacion">Reinstalación</option>
                                                <option value="Archivada por incomparecencia">Archivar</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div id="dias" class="row gx-2 align-items-end" style="display:none">
                                        <div class="col-xs-12 col-sm-12 col-md-2"><br>
                                            <div class="form-group">
                                                <label for="name">Días de vacaciones</label>
                                                <input type="number" step="0.001" name="vacaciones" class="form-control" placeholder="Ingrese solo 3 decimales" > 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-2"><br>
                                            <div class="form-group">
                                                <label for="name">Días de Aguinaldo</label>
                                                <input type="number" step="0.001" name="aguinaldo" class="form-control" placeholder="Ingrese solo 3 decimales" > 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-1">
                                            <div class="form-group">
                                                <label for="name">Otros</label>
                                                <input type="number" step="0.001" name="otros" class="form-control" placeholder="Ingrese solo 3 decimales" > 
                                                <div class="invalid-feedback">
                                                    El campo otro es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                            <div class="form-group">
                                                <label for="name">Horario laboral</label>
                                                <input type="text" name="horario" maxlength="120" class="form-control" placeholder="Ejemplo: De lunes a viernes de 9Am a 5PM y Sábados de 9 Am a 2 PM" > 
                                                <div class="invalid-feedback">
                                                    El campo es obligatorio.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label for="name">Horario de comida</label>
                                                <input type="text" name="comida" maxlength="50" class="form-control" placeholder="De 2PM a 3 PM o 13:30 a 15:00" > 
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
                                                <input type="text" name="direccion_convenio" maxlength="150" class="form-control" value="<?=$direccion_c?>" oninput="this.value = this.value.toUpperCase()"  placeholder=" " > 
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
                                                    <option value="Presencial">Presencial</option>
                                                    <option value="Virtual">Virtual</option>
                                                </select>
                                            </div> 
                                        </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <br><button type="submit" class="btn btn-primary" name="valor" value="1">Vista Previa</button>
                                        </div>
                                    </div>
 
                                    <div id="no_conciliacion" style="display:none"><br>
                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                            <label>Motivo del porque no hubo convenio</label>
                                            <textarea name="observaciones" class="form-control"></textarea>
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

<!-- Modal para archivar audiencia-->
<div class="modal fade" id="ModalArchivar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('archivar_audiencia_parte3')}}">
        @csrf
        <input type="text" id="solicitud-id" name="id" value="{{ $id }}">
        <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
        <input type="hidden" name="primera" id="archivar_primera" value="">
        <input type="hidden" name="justificacion" id="archivar_justificacion" value="">
        <input type="hidden" name="segunda" id="archivar_segunda" value="">
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

<div id="nuevo_turno" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

<div class="modal fade" id="ModalReagendar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('reagendar_audiencia_parte3')}}">
        @csrf
        <input type="hidden" id="modal-id-reagendar" name="id" value="">
        <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">
        <input type="hidden" id="fechaConfirmacion" value= "{{ $fechaConfirmacion }}">
        <input type="hidden" name="primera" id="reagenda_primera" value="">
        <input type="hidden" name="justificacion" id="reagenda_justificacion" value="">
        <input type="hidden" name="segunda" id="reagenda_segunda" value="">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Fecha de la reagenda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sedeReagendar" value="{{ $sede ?? '' }}">
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
        
        /*#resumenCita .alert-info {
            background-color: #e0e7ff;
            color: #1e293b;              
            border: 1px solid #6366f1;   
            border-radius: 8px;
            font-size: 10px;
            padding: 8px 16px;
            margin-bottom: 0;
            box-shadow: 0 2px 8px rgba(99,102,241,0.08);
        }*/

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

        function isFinalAudienciaSelected() {
            return ($('#tipo_de_conclucion').val() || '') !== '' && ($('#tipo_de_conclucion').val() || '') !== 'Seleccione';
        }

        function ensureReinstalacionOption($select) {
            if (!$select || !$select.length) return;
            if (!isFinalAudienciaSelected()) {
                $select.find('option[value="Reinstalación"]').remove();
                return;
            }
            if ($select.find('option[value="Reinstalación"]').length === 0) {
                $select.append('<option value="Reinstalación">Reinstalación</option>');
            }
        }

        function toggleMontoPrestacionForReinstalacion($select) {
            if (!$select || !$select.length) return;
            const selected = $select.val();
            const $row = $select.closest('.row');
            const $montoCol = $row.find('input[name="monto_pago[]"]').closest('.col-xs-12, .col-sm-12, .col-md-6');
            const $montoInput = $row.find('input[name="monto_pago[]"]');

            if (selected === 'Reinstalación') {
                $montoInput.val('0.00');
                $montoInput.prop('disabled', false);
                $montoCol.hide();
                calcularTotal();
            } else {
                $montoCol.show();
            }
        }

        function isReinstalacionSelectedInPrestaciones() {
            return $('.tipo-pago-select').filter(function() { return $(this).val() === 'Reinstalación'; }).length > 0;
        }

        function applyCumplimientoMontoReinstalacionRule($cumplimientoRow) {
            if (!$cumplimientoRow || !$cumplimientoRow.length) return;
            const $monto = $cumplimientoRow.find('input[name="monto_pagos[]"]');
            if (!$monto.length) return;
            $monto.val('0.00').prop('readonly', true);
        }

        function clearCumplimientoMontoReinstalacionRule($cumplimientoRow) {
            if (!$cumplimientoRow || !$cumplimientoRow.length) return;
            const $monto = $cumplimientoRow.find('input[name="monto_pagos[]"]');
            if (!$monto.length) return;
            $monto.prop('readonly', false);
        }

        function applyReinstalacionRuleToFirstCumplimientoIfNeeded() {
            if (!isReinstalacionSelectedInPrestaciones()) {
                $('#newRowaPago').find('.inputFormRow2').each(function() {
                    clearCumplimientoMontoReinstalacionRule($(this));
                    $(this).find('.reinstalacion-notice').remove();
                });
                return;
            }

            const $rows = $('#newRowaPago').find('.inputFormRow2');
            if (!$rows.length) return;

            const $firstCreated = $rows.filter('[data-first-cumplimiento="1"]').first();

            $rows.each(function() {
                const $row = $(this);
                const isFirstCreated = $firstCreated.length && $row.is($firstCreated);

                if (isFirstCreated) {
                    addReinstalacionNoticeIfNeeded($row);
                    applyCumplimientoMontoReinstalacionRule($row);
                } else {
                    $row.find('.reinstalacion-notice').remove();
                    clearCumplimientoMontoReinstalacionRule($row);
                }
            });
        }

        function addReinstalacionNoticeIfNeeded($cumplimientoRow) {
            if (!isReinstalacionSelectedInPrestaciones()) return;
            const isFirst = $('.inputFormRow2').length === 1;
            if (!isFirst) return;
            if ($cumplimientoRow.find('.reinstalacion-notice').length) return;

            const html = '<div class="alert alert-warning reinstalacion-notice mt-2" style="width: 100%;">' +
                'El horario seleccionado para este cumplimiento será la fecha indicada para la <b>Reinstalación</b>' +
                '</div>';
            $cumplimientoRow.find('.form-group').first().append(html);
        }

        let filaActualParaAgendar = null; 

       document.getElementById("no_conciliacion").style.display = "none";
       document.getElementById("archivada").style.display = "none";
       document.getElementById("dias").style.display = "none";
       document.getElementById("pagos").style.display = "none";
       
        $( document ).ready(function() {

            $('#tipo_de_conclucion').on('change', function() {
                $('.tipo-pago-select').each(function() {
                    ensureReinstalacionOption($(this));
                    toggleMontoPrestacionForReinstalacion($(this));
                });

                applyReinstalacionRuleToFirstCumplimientoIfNeeded();
            });

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

                const $lastSelect = $('#newRow').find('.tipo-pago-select').last();
                ensureReinstalacionOption($lastSelect);

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
            html += '<div class="inputFormRow2 row mb-2 align-items-end" ' + (numeroPago === 1 ? 'data-first-cumplimiento="1"' : '') + '>';
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
            // Tipo de Agenda
            /*html += '<div class="col-xs-12 col-sm-12 col-md-12">';
            html += '<div class="form-group">';
            html += '<label for="password">Tipo De Agenda</label>';
            html += '<select class="form-control" name="tipo_pago[]" >';
            html += '<option value="">Seleccione</option>';
            html += '<option value="Por el Conciliador">Por el Conciliador</option>';
            html += '<option value="Agendar en calendario">Agendar en calendario de Cumpliemientos</option>';
            html += '</select>';
            html += '<div class="invalid-feedback">La Dirección es obligatoria.</div>';
            html += '</div></div>';*/
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

            applyReinstalacionRuleToFirstCumplimientoIfNeeded();
            
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

            /*var selectHtml = '<div class="form-group mt-2" id="selectHoraAudienciaDiv">';
            selectHtml += '<label>Selecciona la hora para pagar en la audiencia:</label>';
            selectHtml += '<select class="form-control" id="selectHoraAudiencia">';
            selectHtml += '<option value="">Seleccione una hora</option>';
            selectHtml += '<option value="09:00:00">9:00 AM</option>';
            selectHtml += '<option value="09:30:00">9:30 AM</option>';
            selectHtml += '<option value="10:00:00">10:00 AM</option>';
            selectHtml += '<option value="10:30:00">10:30 AM</option>';
            selectHtml += '<option value="11:00:00">11:00 AM</option>';
            selectHtml += '<option value="11:30:00">11:30 AM</option>';
            selectHtml += '<option value="12:00:00">12:00 PM</option>';
            selectHtml += '<option value="12:30:00">12:30 PM</option>';
            selectHtml += '<option value="13:00:00">1:00 PM</option>';
            selectHtml += '<option value="13:30:00">1:30 PM</option>';
            selectHtml += '<option value="14:00:00">2:00 PM</option>';
            selectHtml += '<option value="14:30:00">2:30 PM</option>';
            selectHtml += '<option value="15:00:00">3:00 PM</option>';
            selectHtml += '<option value="15:30:00">3:30 PM</option>';
            selectHtml += '<option value="16:00:00">4:00 PM</option>';
            selectHtml += '<option value="16:30:00">4:30 PM</option>';
            selectHtml += '</select>';
            selectHtml += '<button type="button" class="btn btn-primary mt-2" id="confirmarHoraAudiencia">Confirmar hora</button>';
            selectHtml += '</div>';
            $('#selectHoraAudienciaDiv').remove();
            $('#btnPagarAudiencia').parent().after(selectHtml);*/

            //$('#btnPagarAudiencia').off('click').on('click', function() {
                var horaSeleccionada = (document.getElementById('audiencia_hora') && document.getElementById('audiencia_hora').value) ? document.getElementById('audiencia_hora').value : '';
                /*if (!horaSeleccionada) {
                    alert('Selecciona una hora válida');
                    return;
                }*/

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
                //$('#selectHoraAudienciaDiv').remove();
            //});
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
            applyReinstalacionRuleToFirstCumplimientoIfNeeded();
            setTimeout(function(){ if (typeof calcularTotal === 'function') { calcularTotal(); } }, 100);
        });
        //Actualiza los números de pago
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
            applyReinstalacionRuleToFirstCumplimientoIfNeeded();
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

        const tipo_iden = document.getElementById('tipo_de_conclucion');

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
                /*windowResize: function(view) {
                    if (window.innerWidth < 768) {
                        calendar.changeView('listWeek');
                    } else{
                        calendar.changeView('dayGridWeek');
                    }
                },*/
                validRange: {
                    start: (() => {
                        const now = new Date();
                        return new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0];
                    })(),
                    end: (() => {
                        const now = new Date();
                        return new Date(now.getFullYear(), now.getMonth() + 12, 0).toISOString().split('T')[0];
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

            ensureReinstalacionOption($(this));
            toggleMontoPrestacionForReinstalacion($(this));
            applyReinstalacionRuleToFirstCumplimientoIfNeeded();

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
            let totalPagosDiferidos = 0;

            // SUMA PRESTACIONES
            $('input[name="monto_pago[]"]').each(function () {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) totalPrestaciones += val;
            });

            // SUMA DEDUCCIONES
            $('input[name="monto_deduccion[]"]').each(function () {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) totalDeducciones += val;
            });

            let total = totalPrestaciones - totalDeducciones;

            $('input[name="monto_pagos[]"]').each(function () {
            let val = parseFloat($(this).val());
            if (!isNaN(val)) totalPagosDiferidos += val;
            });

            $('#totalCalculado').text("$" + formatoMoneda(total));
                $("#totalPagosDiferidos").text('$' + formatoMoneda(totalPagosDiferidos));
            }
        </script>
        <!--script>
            // Validación: los montos de pago deben existir y ser numéricos > 0
            (function(){
                const form = document.getElementById('form_roles');
                if(!form) return;
                form.addEventListener('submit', function(e){
                    // validar monto_pagos[] (pagos diferidos)
                    const pagosDif = Array.from(document.querySelectorAll('input[name="monto_pagos[]"]'));
                    for(let i=0;i<pagosDif.length;i++){
                        const el = pagosDif[i];
                        const val = el.value ? el.value.trim() : '';
                        const num = parseFloat(val.replace(/,/g, '.'));
                        if(val === '' || isNaN(num) || num <= 0){
                            e.preventDefault();
                            const msg = 'Todos los montos de pagos diferidos deben ser números mayores a cero.';
                            if(window.Swal && typeof Swal.fire === 'function'){
                                Swal.fire({ icon: 'warning', title: 'Valores inválidos', text: msg }).then(()=> el.focus());
                            } else { alert(msg); el.focus(); }
                            return false;
                        }
                    }

                    // validar monto_pago[] (prestaciones)
                    const pagos = Array.from(document.querySelectorAll('input[name="monto_pago[]"]'));
                    for(let i=0;i<pagos.length;i++){
                        const el = pagos[i];
                        const val = el.value ? el.value.trim() : '';
                        const num = parseFloat(val.replace(/,/g, '.'));
                        if(val === '' || isNaN(num) || num <= 0){
                            e.preventDefault();
                            const msg = 'Todos los montos de prestaciones deben ser números mayores a cero.';
                            if(window.Swal && typeof Swal.fire === 'function'){
                                Swal.fire({ icon: 'warning', title: 'Valores inválidos', text: msg }).then(()=> el.focus());
                            } else { alert(msg); el.focus(); }
                            return false;
                        }
                    }

                    // Validar que cada pago agendado tenga fecha y hora
                    const paymentBlocks = Array.from(document.querySelectorAll('.inputFormRow2'));
                    for (let i = 0; i < paymentBlocks.length; i++){
                        const block = paymentBlocks[i];
                        const diasInput = block.querySelector('input[name="dias_pagos[]"]');
                        const horaInput = block.querySelector('input[name="hora_pagos[]"]');
                        const montoInput = block.querySelector('input[name="monto_pagos[]"]') || block.querySelector('input[name="monto_pago[]"]');
                        if (!diasInput || !horaInput || !diasInput.value || !horaInput.value){
                            e.preventDefault();
                            const msg = 'Para cada pago programado debes seleccionar una fecha y hora.';
                            if(window.Swal && typeof Swal.fire === 'function'){
                                Swal.fire({ icon: 'warning', title: 'Falta fecha/hora', text: msg }).then(()=> { if(montoInput) montoInput.focus(); });
                            } else { alert(msg); if(montoInput) montoInput.focus(); }
                            return false;
                        }
                    }
                });
            })();

        </!script-->
        

    <script>
        let calendarReagendar;
        $('#ModalReagendar').on('shown.bs.modal', function () {
            const calEl = document.getElementById('calendarReagendar');
            if (!calEl) return;
            if (calendarReagendar) { calendarReagendar.destroy(); }
            // Calcular fecha mínima (16 días hábiles) para posicionar el calendario directamente en la primera semana válida.
            const sede = $('#sedeReagendar').val();
            const conciliadorId = Number(@json($conciliadorId));
            const hoy = new Date();
            hoy.setHours(0,0,0,0);
            let fechaCursor = new Date(hoy);
            let habilesContados = 0;
            function calcularFechaMinima(){
                const siguiente = new Date(hoy);
                siguiente.setDate(siguiente.getDate() + 1);
                siguiente.setHours(0,0,0,0);
                return siguiente;
            }

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

            function addBusinessDaysYMD(ymd, n) {
                const [y, m, d] = ymd.split('-').map(Number);
                let dt = new Date(y, m - 1, d); // local
                let added = 0;
                while (added < n) {
                    dt.setDate(dt.getDate() + 1);
                    added++;
                }
                return toYMD(dt);
            }

            (async function(){

                const fechaMinima = calcularFechaMinima();
                const fechaMinimaStr = fechaMinima.toISOString().slice(0,10);
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
                    fechaLimite = addDaysYMD(fechaConfirmacion, 45); // fallback
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
                            success: function(data){
                                success(data);
                            },
                            error: function(xhr,status,err){
                                console.error('calendarReagendar: error al cargar audiencias', status, err, xhr && xhr.responseText);
                                failure('No se pudieron cargar eventos');
                            }
                        });
                    },
                    eventTimeFormat: { hour: '2-digit', minute: '2-digit' },
                    eventClick: function(info) {
                        const slot = new Date(info.event.start);
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

        $('#sede').on('change', function(){
            $('#sedeReagendar').val($('#sede').val());
            if(typeof calendarModal !== 'undefined' && calendarModal){ calendarModal.refetchEvents(); }
            if(typeof calendarReagendar !== 'undefined' && calendarReagendar){ calendarReagendar.refetchEvents(); }
        });

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
                if(data.conclucion) { $('#tipo_de_conclucion').val(data.conclucion).trigger('change'); }
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

    <script>
        $(document).ready(function(){
            @if(isset($bandera) && $bandera == 5)
                var sel = $('#tipo_de_conclucion');
                if (sel.length) {
                    sel.prop('selectedIndex', 0);
                }
            @endif
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