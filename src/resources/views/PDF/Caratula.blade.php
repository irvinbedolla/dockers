<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
         @page {
                margin: 0px 0px;
            }
            body{
                padding-top: 95px;
            }
            main{
                margin: 50px 0 50px 0; /*Para colocar el texto*/
            }
            header {
                position: fixed;
                top: -100px;
                left: 0;
                right: 0;
                height: 100px;
                text-align: center;
                font-size: 14px;
            }

            footer {
                position: fixed;
                bottom: -60px;
                left: 0;
                right: 0;
                height: 50px;
                text-align: center;
                font-size: 12px;
            }
            .content {
                font-family: sans-serif;
                font-size: 14px;
                text-align: justify;
                margin-left: 3cm;     
                margin-right: 2cm; 
                line-height: 1.3;
            }
            .fondo-membrete {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            }
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #333; line-height: 1.2; }
        
        /* Contenedor Principal */
        .container { width: 100%; border: 1px solid #ccc; padding: 10px; }
        
        /* Encabezado */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .logo-text { font-size: 24px; font-weight: bold; color: #555; }
        .header-center { text-align: center; font-weight: bold; font-size: 11px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .data-table td { border: 1px solid #000; padding: 3px; vertical-align: top; }
        
        .label { font-size: 7px; font-weight: bold; display: block; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-size: 10px; font-weight: normal; min-height: 12px; }
        
        .gray-header { background-color: #d1d1d1; text-align: center; font-weight: bold; padding: 5px !important; }
        
        .check-box { width: 12px; height: 12px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; margin-top: 2px; }
        
        .footer-table { width: 100%; margin-top: 15px; border-collapse: collapse; }
        .footer-table td { border: 1px solid #000; height: 50px; }

        .inline-container { display: inline-block; margin-right: 15px; }
        .inline-label { font-size: 7px; font-weight: bold; text-transform: uppercase; margin-right: 3px; }
        .inline-field { 
            display: inline-block; 
            margin-right: 10px; 
            vertical-align: middle;
        }
        .label-inline { 
            font-size: 7px; 
            font-weight: bold; 
            text-transform: uppercase; 
            margin-right: 4px;
        }
        .value-inline { 
            font-size: 10px; 
            font-weight: normal; 
            display: inline-block;
        }
    </style>
</head>
<body>
    <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
    <table class="header-table">
        <tr>
            <td class="header-center">
                CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO<br>
                SOLICITUD PARA INICIAR TRÁMITE DE CONCILIACIÓN LABORAL
            </td>
        </tr>
    </table>

    <table class="data-table">
        <tr>
            <td width="33%">
                <span class="label">FECHA DE INICIO DE LABORES:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ \Carbon\Carbon::parse($solicitante->fecha_ingreso)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                @else
                    <div class="value">{{ \Carbon\Carbon::parse($ratificacion->fecha_inicio)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                @endif
            </td>
            <td width="33%">
                <span class="label">FECHA DE TÉRMINO DE LABORES:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ \Carbon\Carbon::parse($solicitante->fecha_salida)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                @else
                    <div class="value">{{ \Carbon\Carbon::parse($ratificacion->fecha_termino)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                @endif
            </td>
            <td width="34%">
                <span class="label">PUESTO DESEMPEÑADO:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitante->puesto}}</div>
                @else
                    <div class="value">{{ $ratificacion->categoria}}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">NOMBRE COMPLETO DEL SOLICITANTE:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitante->nombre }}</div><br>
                    <span class="label">
                        <span style="margin-right: 50px;">
                            TRABAJADOR ( {{ $solicitud->tipo_solicitud == 1 ? 'X' : ' ' }} )
                        </span>
                        <span>
                            PATRÓN ( {{ $solicitud->tipo_solicitud == 2 ? 'X' : ' ' }} )
                        </span>
                    </span>
                @else
                    <div class="value">{{ $ratificacion->trabajador }} {{ $ratificacion->primero_trabajador }} {{ $ratificacion->segundo_trabajador }}</div><br>
                    <span class="label">
                        <span style="margin-right: 50px;">
                            TRABAJADOR ( )
                        </span>
                        <span>
                            PATRÓN ( )
                        </span>
                    </span>
                @endif
            </td>
            <td>
                <div class="inline-field">
                    <span class="label-inline">EDAD:</span>
                    @if($bandera == 'Solicitud')
                        <span class="label-inline">{{ $solicitante->edad }} AÑOS.</span>
                    @else
                        <span class="label-inline">{{ $ratificacion->edad }} AÑOS.</span>
                    @endif
                </div><br><br>
                <div class="inline-field">
                    <span class="label-inline">SEXO:</span>
                    @if($bandera == 'Solicitud')
                        <span class="label-inline">{{ $solicitante->sexo }}</span>
                    @else
                        <span class="label-inline">{{ $ratificacion->sexo }}</span>
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <span class="label">DOMICILIO DEL SOLICITANTE (CALLE, NÚMERO EXTERIOR, NÚMERO INTERIOR, COLONIA, C.P. Y MUNICIPIO):</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitante->tipo_vialidad}} {{ $solicitante->calle }} {{ $solicitante->num_ext }} @if(!empty($solicitante->num_int))
                                        INT. {{ $solicitante->num_int }}
                                    @endif COLONIA {{ $solicitante->colonia}}, {{ mb_strtoupper($solicitante->nombre_municipio_sol, 'UTF-8') }}, 
                                    {{ mb_strtoupper($solicitante->nombre_estado_sol, 'UTF-8') }} C.P. {{ $solicitante->codigo_postal }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="data-table">
        <tr>
            <td width="50%">
                <span class="label">CURP DEL SOLICITANTE:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitante->curp }}</div>
                @else
                    <div class="value">{{ $ratificacion->curp }}</div>
                @endif
            </td>
            <td width="50%">
                <span class="label">RFC DEL SOLICITANTE:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitante->rfc }}</div>
                @endif
            </td>
        </tr>
    </table>
    <table class="data-table">
        <tr>
            <td width="33%">
                <span class="label">NSS DEL SOLICITANTE:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitante->nss }}</div>
                @endif
            </td>
            <td width="34%">
                <span class="label">IDENTIFICACIÓN (TIPO Y NÚMERO):</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitante->identificacion }} <br> {{ $solicitante->num_identificacion }}</div>
                @else
                    <div class="value">{{ $ratificacion->tipo_identificacion }} <br> {{ $ratificacion->num_identificacion }}</div>
                @endif
            </td>
            <td width="33%">
                <span class="label">CORREO ELECTRÓNICO:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitante->email }}</div>
                @else
                    <div class="value">{{ $abogado->_patronal }}</div>
                @endif
            </td>
        </tr>

        <tr>
            <td>
                <span class="label-inline">TELÉFONO:</span>
                @if($bandera == 'Solicitud')
                    <span class="label-inline">{{ $solicitante->telefono1 }}</span>
                @else
                    <span class="label-inline">{{ $abogado->telefono_patronal }}</span>
                @endif
            </td>
            <td colspan="2">
                <div style="display: block; margin-bottom: 4px;">
                    <span class="label-inline">SUELDO:</span>
                    @if($bandera == 'Solicitud')
                        <span class="label-inline">${{ $solicitante->pago }}</span>
                    @else
                        <span class="label-inline">${{ $ratificacion->salario }}</span>
                    @endif
                    <span style="margin-left: 15px;"></span>
                </div>
                <div style="display: block; margin-bottom: 4px;">
                    <span style="margin-left: 15px;"></span>
                    <span class="label-inline">
                    @if($bandera == 'Solicitud')
                        DIARIO ({{ $solicitante->periodo_pago == 'DIARIO' ? 'X' : '  ' }}) &nbsp;
                        SEMANAL ({{ $solicitante->periodo_pago == 'SEMANAL' ? 'X' : '  ' }}) &nbsp;
                        QUINCENAL ({{ $solicitante->periodo_pago == 'QUINCENAL' ? 'X' : '  ' }}) &nbsp;
                        MENSUAL ({{ $solicitante->periodo_pago == 'MENSUAL' ? 'X' : '  ' }})
                    @else
                        DIARIO ({{ $ratificacion->frecuencia == 'DIARIO' ? 'X' : '  ' }}) &nbsp;
                        SEMANAL ({{ $ratificacion->frecuencia == 'SEMANAL' ? 'X' : '  ' }}) &nbsp;
                        QUINCENAL ({{ $ratificacion->frecuencia == 'QUINCENAL' ? 'X' : '  ' }}) &nbsp;
                        MENSUAL ({{ $ratificacion->frecuencia == 'MENSUAL' ? 'X' : '  ' }})
                    @endif
                    </span>
                </div>
                <hr style="border: none; border-top: 1px solid black; margin: 3px -3px; width: auto;">
                <div style="display: block;">
                    <span class="label-inline">BUZÓN ELECTRÓNICO:</span>
                    <span class="label-inline" style="margin-left: 10px;">
                        SI (  ) 
                        &nbsp;&nbsp;&nbsp;
                        NO (  )
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div style="display: block; margin-bottom: 2px;">
                    <span class="label-inline">HORARIO Y HORAS TRABAJADAS A LA SEMANA:</span>
                    @if($bandera == 'Solicitud')
                        <br><span class="value">{{ $solicitante->jornada }} <br> {{ $solicitante->horas_semana }} hrs</span>
                    @else
                        <br><span class="value">{{ $ratificacion->dias }} DÍAS TRABAJADOS A LA SEMANA</span>
                    @endif
                </div>
            </td>
            <td>
                <div style="display: inline-block; ">
                    <span class="label-inline">¿RECIBOS DE NÓMINA?:</span>
                    <span class="label-inline" >
                        SI ( ) 
                        &nbsp;&nbsp;
                        NO ( )
                    </span>
                </div>
                <hr style="border: none; border-top: 1px solid black; margin: 3px -3px; width: auto;">
                <div style="display: inline-block; width: 70%;">
                    <span class="label-inline">QUIÉN ENTREGA CITATORIO:</span>
                    <span class="label-inline" style="margin-left: 5px;">
                        @if($bandera == 'Solicitud')
                            SOLICITANTE ( {{ $notifica->contains('Trabajador') ? 'X' : '' }} ) 
                            &nbsp;&nbsp;
                            NOTIFICADOR CCLEM ( {{ $notifica->contains('Centro') ? 'X' : '' }} ) 
                        @else
                            SOLICITANTE (  ) 
                            &nbsp;&nbsp;
                            NOTIFICADOR CCLEM (  ) 
                        @endif
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        @if($bandera == 'Solicitud')
        <tr>
            <td colspan="{{ $motivos->count() }}" class="gray-header">
                MOTIVO(S) DE CONCILIACIÓN
            </td>
        </tr>
        <tr align="center">
            @foreach($motivos as $motivo)
                <td width="{{ 100 / $motivos->count() }}%" style="padding: 0;">
                    <div style="font-size: 7px; font-weight: bold; padding: 5px; min-height: 20px; display: block;">
                        {{ mb_strtoupper($motivo->motivo, 'UTF-8') }}
                    </div>
                </td>
            @endforeach
           
        </tr>
        @else
            <tr>
                <td class="gray-header">
                    MOTIVO(S) DE CONCILIACIÓN
                </td>
            </tr>
            <tr align="center">
                <td width="100%" style="padding: 0;">
                    <div style="font-size: 7px; font-weight: bold; padding: 5px; min-height: 20px; display: block;">
                        {{ mb_strtoupper($ratificacion->motivo, 'UTF-8') }}
                    </div>
                </td>
            </tr>
        @endif 
    </table>

    <table class="data-table">
        <tr>
            <td colspan="2">
                <span class="label">RAZÓN SOCIAL DE LA EMPRESA Y/O NOMBRE COMERCIAL Y/O NOMBRE DEL CITADO (S):</span>
                <div class="value">
                    @if($bandera == 'Solicitud')
                        @foreach($citados as $index => $citado)
                            <strong>{{ $index + 1 }}.-</strong> {{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }}<br>
                        @endforeach
                    @else
                        {{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal ?? '' }} {{ $abogado->segundo_apellido_patronal ?? ''}}
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td width="50%">
                <span class="label">¿A QUÉ SE DEDICA LA EMPRESA O ESTABLECIMIENTO?</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ $solicitud->actividad }}</div>
                @else
                    <div class="value">{{ $abogado->giroComercial }}</div>
                @endif
            </td>
            <td width="50%">
                <span class="label">CURP O RFC DEL CITADO (S):</span>
                <div class="value">
                    @if($bandera == 'Solicitud')
                        @foreach($citados as $index => $citado)
                            {{ $citado->rfc }}<br>
                        @endforeach
                    @else
                        {{ $abogado->rfc_patronal }}
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">DOMICILIO DE LA EMPRESA O CITADO (S) (CALLE, NÚMERO EXTERIOR, NÚMERO INTERIOR, COLONIA, C.P., MUNICIPIO):</span>
                <div class="value">
                    @if($bandera == 'Solicitud')
                        @foreach($citados as $index => $citado)
                            <strong>{{ $index + 1 }}.-</strong> 
                            {{ mb_strtoupper($citado->tipo_vialidad, 'UTF-8') }} {{ mb_strtoupper($citado->calle, 'UTF-8') }}, 
                            {{ $citado->n_ext }}
                            {{ $citado->n_int ? 'INT. '.$citado->n_int : '' }}
                            COL. {{ mb_strtoupper($citado->colonia, 'UTF-8') }}, 
                            {{ mb_strtoupper($citado->nombre_municipio, 'UTF-8') }}, 
                            {{ mb_strtoupper($citado->nombre_estado, 'UTF-8') }}, 
                            C.P. {{ $citado->cp }}
                            <br>
                        @endforeach
                @else
                    {{ mb_strtoupper($ratificacion->tipo_vialidad, 'UTF-8') }} {{ mb_strtoupper($ratificacion->calle, 'UTF-8') }}, 
                    {{ $ratificacion->num_ext }}
                    {{ $ratificacion->num_int ? 'INT. '.$ratificacion->num_int : '' }}
                    COL. {{ mb_strtoupper($ratificacion->colonia, 'UTF-8') }}, 
                    {{ mb_strtoupper($ratificacion->municipio_domicilio, 'UTF-8') }}, 
                    {{ mb_strtoupper($ratificacion->estado_domicilio, 'UTF-8') }}, 
                    C.P. {{ $ratificacion->codigo_postal }}
                @endif
                </div>
            </td>
        </tr>
    </table>

    {{--<table class="data-table">
        <tr>
            <td width="40%">
                <span class="label">TELÉFONO DE LA EMPRESA O CITADO (S):</span>
                <div class="value">{{ $citado->edad }}</div>
            </td>
            <td width="60%">
                <span class="label">FIRMA DEL SOLICITANTE (A):</span>
                <div style="height: 30px;"></div>
            </td>
        </tr>
    </table>

    <table class="data-table" style="margin-top: 10px;">
        <tr><td colspan="2" class="gray-header">DATOS DE LA PERSONA RESPONSABLE QUE ATENDIO LA SOLICITUD</td></tr>
        <tr>
            <td width="50%">
                <span class="label">NOMBRE COMPLETO Y FIRMA:</span>
                <div style="height: 40px;"></div>
                <hr style="border: none; border-top: 1px solid black; margin: 3px -3px; width: auto;">
                <span class="label">Fecha de captura:</span>
                <div class="value">{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
            </td>
            <td width="50%">
                <span class="label">OBSERVACIONES DE LA SOLICITUD:</span>
                <div class="value"></div>
            </td>
        </tr>
    </table>--}}
</body>
</html>