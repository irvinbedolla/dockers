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
        
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #333; line-height: 1.2; padding-top: 95px;padding-bottom: 50px; padding-left: 40px; padding-right: 40px; }
        
        /* Contenedor Principal */
        .container { width: 100%; border: 1px solid #ccc; padding: 10px; }
        
        /* Encabezado */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .logo-text { font-size: 24px; font-weight: bold; color: #555; }
        .header-center { text-align: center; font-weight: bold; font-size: 11px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .data-table td { border: 1px solid #000; padding: 3px; vertical-align: top; }
        
        .label { font-size: 11px; font-weight: bold; display: block; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-size: 10px; font-weight: normal; min-height: 12px; }
        .label-trasero { font-size: 8px;  display: block; margin-bottom: 2px; }
        
        .gray-header { background-color: #d1d1d1; text-align: center; font-weight: bold; padding: 5px; font-size: 12px; !important; }
        .firma-header { font-size: 11px; text-align: center; font-weight: bold; padding: 5px !important; }
        .tabla-header { font-size: 8px; text-align: center; font-weight: bold; padding: 5px !important; }
        .trasera-header { font-size: 8px; text-align: center; font-weight: bold; padding: 5px !important; }

        
        .check-box { width: 12px; height: 12px; border: 1px solid #000; display: inline-block; text-align: center; line-height: 12px; margin-top: 2px; }
        
        .footer-table { width: 100%; margin-top: 15px; border-collapse: collapse; }
        .footer-table td { border: 1px solid #000; height: 50px; }

        .inline-container { display: inline-block; margin-right: 15px; }
        .inline-label { font-size: 8px; font-weight: bold; text-transform: uppercase; margin-right: 3px; }
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
        .nueva-pagina {
                page-break-before: always;
                margin-left: 50px; 
                margin-right: 50px;
            }
        .contenedor-firmas {
            width: 100%;
            margin-top: 50px;
            font-family: Arial, sans-serif;
            color: #000;
        }
        p {
            text-align: justify; 
            font-size: 8px;    
            
        }
        li {
            margin-right: 10px;
            text-align: justify; 
            padding-left: 10px; 
        }
        ol{
            padding-left: 17px; 
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
            <td width="38%">
                <span class="label">FECHA DE INICIO DE LABORES:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ \Carbon\Carbon::parse($solicitante->fecha_ingreso)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                @else
                    <div class="value">{{ \Carbon\Carbon::parse($ratificacion->fecha_inicio)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                @endif
            </td>
            <td width="37%">
                <span class="label">FECHA DE TÉRMINO DE LABORES:</span>
                @if($bandera == 'Solicitud')
                    <div class="value">{{ \Carbon\Carbon::parse($solicitante->fecha_salida)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                @else
                    <div class="value">{{ \Carbon\Carbon::parse($ratificacion->fecha_termino)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                @endif
            </td>
            <td width="25%">
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
                            PATRÓN (X)
                        </span>
                    </span>
                @endif
            </td>
            <td>
                <div class="inline-field">
                    <span class="label">EDAD:</span>
                    @if($bandera == 'Solicitud')
                        <span class="value">{{ $solicitante->edad }} AÑOS.</span>
                    @else
                        <span class="value">{{ $ratificacion->edad }} AÑOS.</span>
                    @endif
                </div><br><br>
                <hr style="border: none; border-top: 1px solid black; margin: 3px -4px; width: auto;">
                <div style="display: block;">
                    <span class="label">SEXO:</span>
                    @if($bandera == 'Solicitud')
                        <span class="value">{{ $solicitante->sexo }}</span>
                    @else
                        <span class="value">{{ $ratificacion->sexo }}</span>
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
            @if($bandera == 'Solicitud')
                <td width="50%">
                    <span class="label">NSS DEL SOLICITANTE:</span>
                    <div class="value">{{ $solicitante->nss }}</div>
                   
                </td>
                <td width="25%">
                    <span class="label">IDENTIFICACIÓN (TIPO Y NÚMERO):</span>
                    <div class="value">{{ mb_strtoupper($solicitante->identificacion, 'UTF-8') }} <br> {{ $solicitante->num_identificacion }} </div>
                    
                </td>
                <td width="25%">
                    <span class="label">CORREO ELECTRÓNICO:</span>
                    <div class="value">{{ $solicitante->email }}</div> 
                </td>
            @else
                <td colspan="2" width="50%">
                    <span class="label">IDENTIFICACIÓN (TIPO Y NÚMERO):</span>
                    <div class="value">{{ mb_strtoupper($ratificacion->identificacion, 'UTF-8') }} <br> {{ $ratificacion->num_identificacion }}</div>
                </td>
                <td width="50%">
                    <span class="label">CORREO ELECTRÓNICO:</span>
                    <div class="value">{{ $abogado->email_patronal }}</div>
                </td>

            @endif
            
        </tr>

        <tr>
            <td  width="50%">
                <span class="label">TELÉFONO:</span>
                @if($bandera == 'Solicitud')
                    <span class="value">{{ $solicitante->telefono1 }}</span>
                @else
                    <span class="value">{{ $abogado->telefono_patronal }}</span>
                @endif
            </td>
            <td width="25%">
                <div style="display: block; margin-bottom: 4px;">
                    <span class="label">SUELDO:</span>
                    @if($bandera == 'Solicitud')
                        <span class="value">${{ $solicitante->pago }}</span>
                    @else
                        <span class="value">${{ $ratificacion->salario }}</span>
                    @endif
                    <span style="margin-left: 15px;"></span>
                </div>
                <div style="display: block; margin-bottom: 4px;">
                    <span style="margin-left: 15px;"></span>
                    <span class="label">
                    @if($bandera == 'Solicitud')
                        DIARIO ({{ $solicitante->periodo_pago == 'Diario' ? 'X' : '  ' }}) &nbsp;
                        SEMANAL ({{ $solicitante->periodo_pago == 'Semanal' ? 'X' : '  ' }}) &nbsp;
                        QUINCENAL ({{ $solicitante->periodo_pago == 'Quincenal' ? 'X' : '  ' }}) &nbsp;
                        MENSUAL ({{ $solicitante->periodo_pago == 'Mensual' ? 'X' : '  ' }})
                    @else
                        DIARIO ({{ $ratificacion->frecuencia == 'Diario' ? 'X' : '  ' }}) &nbsp;
                        SEMANAL ({{ $ratificacion->frecuencia == 'Semanal' ? 'X' : '  ' }}) &nbsp;
                        QUINCENAL ({{ $ratificacion->frecuencia == 'Quincenal' ? 'X' : '  ' }}) &nbsp;
                        MENSUAL ({{ $ratificacion->frecuencia == 'Mensual' ? 'X' : '  ' }})
                    @endif
                    </span>
                </div>
            </td>
            <td   width="25%">
                <div style="display: block;">
                    <span class="label">BUZÓN ELECTRÓNICO:</span>
                    <span class="value" style="margin-left: 10px;">
                        SI (  ) 
                        &nbsp;&nbsp;&nbsp;
                        NO (  )
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <td width="50%">
                <div>
                    <span class="label">HORARIO Y HORAS TRABAJADAS A LA SEMANA:</span>
                    @if($bandera == 'Solicitud')
                        <br><span class="value">{{ $solicitante->jornada }} <br> {{ $solicitante->horas_semana }} hrs</span>
                    @else
                        <br><span class="value">{{ $ratificacion->dias }} DÍAS TRABAJADOS A LA SEMANA</span>
                    @endif
                </div>
            </td>
            
            @if($bandera == 'Solicitud')
                <td width="25%">
                    <div>
                        <span class="label">¿RECIBOS DE NÓMINA?:</span>
                        <span class="value">
                            SI ( ) 
                            &nbsp;&nbsp;
                            NO ( )
                        </span>
                    </div>
                </td>
                <td width="25%">
                    <div>
                        <span class="label">QUIÉN ENTREGA CITATORIO:</span>
                        <span class="value" style="margin-left: 5px;">
                            SOLICITANTE ( {{ $notifica->contains('Trabajador') ? 'X' : '' }} )<br> 
                            &nbsp;
                            NOTIFICADOR(A) CCLEM ( {{ $notifica->contains('Centro') ? 'X' : '' }} ) 
                        </span>
                    </div>
                </td>
            @else
                <td colspan="2" width="50%">
                    <div>
                        <span class="label">¿RECIBOS DE NÓMINA?:</span>
                        <span class="value">
                            SI ( ) 
                            &nbsp;&nbsp;
                            NO ( )
                        </span>
                    </div>
                </td>
            @endif
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
                    <div style="font-size: 10px; font-weight: bold; padding: 5px; min-height: 20px; display: block; ">
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
                    <div style="font-size: 10px; font-weight: bold; padding: 5px; min-height: 20px; display: block;">
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
                <br>
                </div>
            </td>
        </tr>
        
    </table>

    <table class="data-table">
        
            <tr>
                <td class="gray-header">
                    DATOS DE LA PERSONA RESPONSABLE QUE ATENDIO LA @if($bandera == 'Solicitud') SOLICITUD: @else RATIFICACIÓN: @endif
                </td>
            </tr>
            
    </table>

    <table class="data-table">
        <tr>
            <td width="75%" style="padding: 1;">
                <div >
                    <span class="label"> NOMBRE COMPLETO Y FIRMA:</span>
                    
                </div>
                <div class="value">{{ mb_strtoupper($user_name, 'UTF-8') }}</div>                    

                <br>
            </td>
            <td >
                <div style="display: block;">
                    <span class="label">FECHA DE CAPTURA:</span>
                </div>
                <div class="value">
                    @if($bandera == 'Solicitud')
                    <div class="value">{{ \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                    @else
                    <div class="value">{{ \Carbon\Carbon::parse($ratificacion->fecha)->translatedFormat('d \d\e F \d\e\l Y') }}</div>
                    @endif
                </div>
                    
                
            </td>
        </tr>
        <tr>
            <td colspan="2">
                
                @if($bandera == 'Solicitud')
                    <span class="label">OBSERVACIONES DE LA SOLICITUD:</span>
                    @if($solicitud->observaciones)
                        <div class="value">{{ mb_strtoupper($solicitud->observaciones, 'UTF-8') }}</div>
                    @else
                        <br><br><br><br><br>
                    @endif
                @else
                <span class="label">OBSERVACIONES DE LA RATIFICACIÓN:</span>
                    @if($ratificacion->observaciones)
                        <div class="value">{{ mb_strtoupper($ratificacion->observaciones, 'UTF-8') }}</div>
                    @else
                        <br><br><br><br><br>
                    @endif
                @endif
                <br>
                
            </td>
            
            
        </tr>
        
    </table>
    <table class="data-table">
        
            <tr>
                <td class="firma-header">
                    FIRMA DEL SOLICITANTE(A):
                    <br><br><br><br><br>
                </td>
            </tr>
            
    </table>
    <div class="nueva-pagina">
        <div class="trasera-header">
            <span>CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO</span>
        </div>
        <p> El Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, con domicilio en el número 1575, del Boulevard García de 
            León, de la Colonia Chapultepec Oriente, con Código Postal 58260 en esta Ciudad de Morelia, Michoacán de Ocampo; en cuanto
            sujeto obligado y responsable del tratamiento de los datos personales que se recaban derivados de la solicitud de conciliación
            laboral presentada por las partes interesadas, los cuales se compromete a proteger de acuerdo a lo dispuesto en el artículo 97 y
            101 de la Ley de Transparencia, Acceso a la Información Pública y Protección de Datos Personales del Estado de Michoacán de
            Ocampo y normativa que resulte aplicable, por tanto refiere que: 

        </p>
        <p>
            Los datos personales que se recaben en el Proceso de conciliación son:
        </p>
        <p>
            Tratándose del SOLICITANTE es: @if($bandera == 'Solicitud'){{ $solicitante->nombre }}, @else {{ $ratificacion->trabajador }} {{ $ratificacion->primero_trabajador }} {{ $ratificacion->segundo_trabajador }}, @endif 
            dirección personal @if($bandera == 'Solicitud')
                                    {{ $solicitante->tipo_vialidad}} {{ $solicitante->calle }} {{ $solicitante->num_ext }} @if(!empty($solicitante->num_int))
                                                        INT. {{ $solicitante->num_int }}
                                                    @endif COLONIA {{ $solicitante->colonia}}, {{ mb_strtoupper($solicitante->nombre_municipio_sol, 'UTF-8') }}, 
                                                    {{ mb_strtoupper($solicitante->nombre_estado_sol, 'UTF-8') }} C.P. {{ $solicitante->codigo_postal }}
                                @endif
            y de correo electrónico @if($bandera == 'Solicitud'){{ $solicitante->email }}, @else{{ $abogado->email_patronal }},@endif
            CURP @if($bandera == 'Solicitud'){{ $solicitante->curp }},@else{{ $ratificacion->curp }},@endif 
            comprobante de domicilio,
            identificación oficial @if($bandera == 'Solicitud'){{ mb_strtoupper($solicitante->identificacion, 'UTF-8') }}  {{ $solicitante->num_identificacion }} @else{{ mb_strtoupper($ratificacion->identificacion, 'UTF-8') }} {{ $ratificacion->num_identificacion }}@endif
            y número telefónico @if($bandera == 'Solicitud')
                    {{ $solicitante->telefono1 }}
                @else
                    {{ $abogado->telefono_patronal }}
                @endif. 
        </p>
        <p>
            Tratándose de CITADO (S):@if($bandera == 'Solicitud')
                                    @foreach($citados as $index => $citado)
                                         {{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }},
                                    @endforeach 
                                    @else{{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal ?? '' }} {{ $abogado->segundo_apellido_patronal ?? ''}}
                                    @endif
            dirección personal @if($bandera == 'Solicitud')
                                        @foreach($citados as $index => $citado)
                                             <strong>{{ $index + 1 }}.-</strong> 
                                            {{ mb_strtoupper($citado->tipo_vialidad, 'UTF-8') }} {{ mb_strtoupper($citado->calle, 'UTF-8') }}, 
                                            {{ $citado->n_ext }}
                                            {{ $citado->n_int ? 'INT. '.$citado->n_int : '' }}
                                            COL. {{ mb_strtoupper($citado->colonia, 'UTF-8') }}, 
                                            {{ mb_strtoupper($citado->nombre_municipio, 'UTF-8') }}, 
                                            {{ mb_strtoupper($citado->nombre_estado, 'UTF-8') }}, 
                                            C.P. {{ $citado->cp }},
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
            y de correo electrónico, 
            CURP o RFC @if($bandera == 'Solicitud')
                            @foreach($citados as $index => $citado)
                                 {{ $citado->rfc }},
                            @endforeach
                        @else
                            {{ $abogado->rfc_patronal }}
                        @endif
            comprobante de domicilio, 
            identificación oficial, 
            número telefónico y documento con el que acrediten su personalidad. 
        </p>
        <p>
            Finalidad para recabar los datos personales: Los datos personales que se recaban son necesarios para iniciar la solicitud del 
            procedimiento de conciliación prejudicial obligatorio, ante este centro y ante el sistema que se implemente, por tanto, los datos 
            proporcionados por las partes, así como el convenio de conciliación que se llegara a derivar de la conciliación no podrán ser trasladados 
            a terceras personas o compartidos a terceros ajenos al procedimiento de conciliación.
        </p>
        <p>
            Lo anterior, conforme al artículo 101 de la Ley de Transparencia, Acceso a la Información Pública y Protección de Datos Personales del Estado 
            de Michoacán de Ocampo que dispone: “Para que los sujetos obligados puedan permitir el acceso a información confidencial requieren 
            obtener el consentimiento de los particulares titulares de la información”; por lo que resulte necesario su consentimiento para recabar 
            y tratar sus datos personales, comprometiéndose el Centro de Conciliación Laboral a no realizar ningún tipo de transferencia de 
            los mismos, salvo en los casos que sea necesario atender algún tipo de requerimiento de Autoridad competente de conformidad a la misma 
            Ley. 
        </p>

        <div class="trasera-header" style="margin-left: 30px; margin-right: 30px;">
            <span>DECÁLOGO DE DERECHOS Y OBLIGACIONES DE LOS USUARIOS QUE ACUDEN AL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN. </span>
            <p style="text-align: center;">Expediente:@if($bandera == 'Solicitud') {{ $solicitud->NUE ? $solicitud->NUE : '________________' }} @else {{ $ratificacion->NUE ? $ratificacion->NUE : '________________' }} @endif 
                Morelia, Michoacán, 
                @if($bandera == 'Solicitud')
                    {{ $solicitud->fecha ? \Carbon\Carbon::parse($solicitud->fecha)->translatedFormat('d \d\e F \d\e\l Y') :  'a____________de____________del año_____________.'}}
                    
                    @else
                    {{ $ratificacion->fecha ? \Carbon\Carbon::parse($ratificacion->fecha)->translatedFormat('d \d\e F \d\e\l Y') :  'a____________de____________del año_____________.'}}
                    @endif
                </p>

        </div>

        <p>
            Toda persona como usuario de las Delegaciones Regionales del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo gozarán 
            de los Derechos y harán cumplir y respetarán las Obligaciones que a continuación se indican, sin perjuicio de cualquier otro que pueda 
            corresponderle de acuerdo a la normativa aplicable 
        </p>
        
            <table class="data-table" >
                <tr >
                <td width="50%">
                    <div class ="tabla-header"  >
                        <span >Decálogo de Derechos:</span>
                    </div>
                    <ol>
                        <li>Derecho a recibir orientación y asesoría jurídica en todo momento del procedimiento de conciliación. </li>
                        <li>Derecho de recibir un servicio expedito y apegado a los términosque rigen el procedimiento de conciliación prejudicial,  el personal delcentro, deberá abstener de dilatar o entorpecer la solicitud y procedimiento de conciliación. </li>
                        <li>Derecho a la protección de sus datos personales, toda la información que proporcioné durante el procedimiento será  confidencial y plenamente resguardada y utilizada únicamente  para el procedimiento conciliatorio. </li>
                        <li>Derecho a que en los convenios y acuerdos que participe seanjustos y equitativos y se respete su voluntad.</li>
                        <li>Derecho de recibir el servicio de Conciliación de manera gratuita, el personal del centro tiene prohibido recibir pagos, gratificaciones, dadivas, regalos o compensaciones o poner condiciones para prestar sus servicios.</li>
                        <li>Derecho a un servicio de manera igualitaria, libre de toda clase de discriminación, a ser tratado con respeto y no revictimizado. </li>
                        <li>Derecho a recibir los servicios del Centro sin que existan privilegios, los servidores públicos deben abstenerse de favorecer a cualquiera de las partes actuaran bajo el principio de neutralidad e imparcialidad. </li>
                        <li>Derecho a tener certeza en la legalidad de las actuaciones realizados por el personal del centro, en cualquier etapa del procedimiento. </li>
                        <li>Derecho a que el personal del Centro se conduzca con ética, respeto, profesionalismo y vocación de servicio público. </li>
                        <li>Derecho a presentar quejas y sugerencias respecto del trato recibido o cualquier falta al cumplimiento de sus derechos aquí referidos, por parte de los funcionarios del Centro de Conciliación Laboral, incluido algún acto de corrupción.</li>
                    </ol>
                    
                </td>
                <td width="50%">
                    <div class ="tabla-header">
                        <span>Decálogo de Obligaciones:</span>  
                    </div>
                    <ol>
                        <li>Acatar los principios y reglas de procedimiento del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo. </li>
                        <li>Asumir actitud de respeto y cordialidad hacia el personal del Centro,así como para la otra parte en el proceso de conciliación. </li>
                        <li>Proporcionar toda la información que le sea requerida para el inicio de su procedimiento de conciliación prejudicial. </li>
                        <li>Asistir de manera puntual a las audiencias de conciliación o de cumplimiento de pago.</li>
                        <li>No dañar las instalaciones, equipo y mobiliario del Centro. </li>
                        <li>No ofrecer pagos, gratificaciones, dadivas, regalos o compensaciones a los servidores públicos y denunciar a quien las solicite.</li>
                        <li>Conducirse con verdad ante los funcionarios del Centro, recordando que estos actúan de buena fe durante el procedimiento.</li>
                        <li>No entorpecer o retrasar los plazos y procedimientos de Conciliaciónde manera ventajosa e injustificada, ni pretender tener trato preferencial.</li>
                        <li>Acatar los resultados o consecuencias de la inasistencia a las audiencias de Conciliación. </li>
                        <li>Cumplir con los acuerdos y convenios a que se lleguen como resultado del procedimiento de conciliación. </li>
                    </ol>
                </td>
                </tr>

            </table>

            <p>Por mi propio derecho, manifiesto <b>BAJO PROTESTA DE DECIR VERDAD</b>, que he revisado, conozco y entiendo los derechos y obligaciones 
            que me fueron leídos alacudir al Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, los cuales quedaron plenamente 
            explicados. </p><br><br><br>
    
                    
                    <div class="row" style="text-align: center;">
                        <div class="col-12 text-center">
                            <div style="display: inline-block; margin-right: 30px;">
                                <p><center><b>___________________________________<br>  <br> @if($bandera == 'Solicitud'){{ $solicitante->nombre }} @else {{ $ratificacion->trabajador }} {{ $ratificacion->primero_trabajador }} {{ $ratificacion->segundo_trabajador }} @endif <br></b></center></p>
                            </div>
                                    &nbsp;&nbsp;&nbsp;
                            <div style="display: inline-block;">
                                <p><center><b>___________________________________<br><br>@if($bandera == 'Solicitud'){{ $citados->first()->nombre }} {{ $citados->first()->primer_apellido }} {{ $citados->first()->segundo_apellido }} @else {{ $abogado->nombres_patronal }} {{ $abogado->primer_apellido_patronal ?? '' }} {{ $abogado->segundo_apellido_patronal ?? ''}} @endif<br></b></center></p>
                            </div>
                        </div>
                    </div>
                    
                        
               
        


        
    </div>

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