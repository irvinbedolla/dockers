<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { 
            margin: 0px; 
        }
        body { 
            margin: 0px; 
            padding: 0px;
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px;
            font-weight: bold;
            color: #000; 
        }

        .fondo-membrete {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
        }

        .contenido-principal {
            padding-top: 130px; 
            padding-bottom: 50px;
            padding-left: 40px;
            padding-right: 40px;
        }

        .pill {
            background-color: #e2e6e9; 
            border-radius: 15px; 
            padding: 5px 12px;
            min-height: 14px;
            display: block;
            font-weight: bold; 
            color: #333;
            font-size: 14px;
        }

        
        .pill-checkbox {
            background-color: #e2e6e9;
            border-radius: 8px;
            width: 25px;
            height: 20px;
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            font-weight: normal;
            font-size: 15px;
        }

        
        .layout-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 12px; 
        }
        .layout-table td {
            vertical-align: middle;
            
        }
        .label {
            text-transform: uppercase;
            font-size: 15px;
            white-space: normal;
        }
        .label-page2 {
            text-transform: uppercase;
            font-size: 13px;
            white-space: normal;
        }
        .nueva-pagina {
                page-break-before: always;
                
            }
        .data-table-ultima { width: 100%; border-collapse: collapse; margin-bottom: 5px; font-size: 5px; }
        .data-table-ultima td { border: 1px solid #000; padding: 3px; vertical-align: top; }
    </style>
</head>
<body>
    <img src="{{ public_path('assets/images/pdf_Siconcilio.jpg') }}" class="fondo-membrete">
    
    <div class="contenido-principal">
        
        <table class="layout-table" style="margin-bottom: 20px;">
            <tr>
                <td width="45%" style="vertical-align: top;">
                    <div class="label" style="margin-bottom: 5px; ">FOLIO:</div>
                    <div class="pill" style="width: 80%; text-align: center;" >{{$solicitud->consecutivo}}</div>
                </td>
                <td width="55%">
                    <table style="width: 100%; border-spacing: 0 8px;">
                        <tr>
                            <td>
                                <div class="label" style="margin-bottom: 3px; ">NOMBRE DEL CONCILIADOR:</div>
                                <div class="pill" style="height: 20px; text-align: center;">{{$conciliador_name}}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="label" style="margin-bottom: 3px;">SALA:</div>
                                <div class="pill" style="height: 20px; text-align: center;">{{$ultima_audiencia->sala}}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="label" style="margin-bottom: 3px; ">AÑO:</div>
                                <div class="pill" style="height: 20px; text-align: center;">{{$solicitud->año}}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-bottom: 25px;">
            <div class="label" style="margin-bottom: 5px;">NÚMERO DE IDENTIFICACIÓN ÚNICO</div>
            <div class="pill" style="width: 60%; margin: 0 auto; height: 20px;">{{$solicitud->NUE}}</div>
        </div>

        <table class="layout-table">
            <tr>
                <td width="20%" class="label">SOLICITANTE:</td>
                <td width="80%"><div class="pill" style="height: 20px; text-align: center;">{{$solicitante->nombre}}</div></td>
            </tr>
            <tr>
                <td class="label">CITADO (S):</td>
                <td><div class="pill" style="min-height: 30px; padding-top: 10px; padding-bottom: 10px; font-size:11px;">
                    @foreach($citados as $index => $citado)
                        <strong>{{ $index + 1 }}.-</strong> {{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }}<br>
                    @endforeach    
                </div></td>
            </tr>
            <tr>
                <td class="label">OBJETO DE LA SOLICITUD:</td>
                <td><div class="pill" style="height: 20px; font-size: 13px;">
                @foreach($motivos as $index => $motivo)
                        <strong>{{ $index + 1 }}.-</strong>{{ mb_strtoupper($motivo->motivo, 'UTF-8') }} 
                @endforeach    
                </div></td>
            </tr>
        </table>

        <table class="layout-table">
            <tr>
                <td width="10%" class="label">INICIÓ:</td>
                <td width="35%"><div class="pill" style="height: 20px; text-align: center;">{{$solicitud->fecha_confirmacion}}</div></td>
                <td width="25%" class="label" style="text-align: right; padding-right: 10px;">FECHA DE CONCLUSIÓN:</td>
                <td width="30%"><div class="pill" style="height: 20px; text-align: center;"> @if($tipo == 'seguimiento'){{$conciliadores?->fecha}} @endif</div></td>
            </tr>
        </table>

        <table style="width: 100%; margin-top: 15px; border-collapse: separate; border-spacing: 0 12px;">
            <tr>
                <td width="35%" class="label" style="white-space: nowrap;">ARCHIVO POR FALTA DE INTERÉS:</td>
                <td width="10%"><div class="pill-checkbox" style="font-size: 15px;" >@if($tipo == 'seguimiento'){{ $ultima_audiencia->estatus == 'Archivada' ? 'X' : '' }}@endif</div></td>
                
                <td width="15%" class="label" >CONVENIO:</td>
                <td width="40%"><div class="pill-checkbox" style="font-size: 15px;" >@if($tipo == 'seguimiento'){{ $ultima_audiencia->estatus == 'Conciliacion' ? 'X' : '' }}@endif</div></td>
            </tr>
            <tr>
                <td class="label" style="white-space: nowrap;">CONSTANCIA DE NO CONCILIACIÓN:</td>
                <td><div class="pill-checkbox" style="font-size: 15px;">@if($tipo == 'seguimiento'){{ $ultima_audiencia->estatus == 'No conciliacion' ? 'X' : '' }}@endif</div></td>
                
                <td class="label">MULTA:</td>
                <td><div class="pill-checkbox" style="font-size: 15px;"> {{ $multas ? 'X' : '' }}</div></td>
            </tr>
            <tr>
                <td class="label">NOTIFICA: SOLICITANTE <span class="pill-checkbox" style="margin-left: 10px; font-size: 15px;">{{ $notifica->contains('Trabajador') ? 'X' : '' }} </span></td>
                <td></td>
                
                <td class="label" colspan="2">NOTIFICADOR DEL CCL <span class="pill-checkbox" style="margin-left: 10px; font-size: 15px;"> {{ $notifica->contains('Centro') ? 'X' : '' }} </span></td>
            </tr>
        </table>

        <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr>
                <td width="20%" class="label" style="vertical-align: center; padding-bottom: 5px;">OBSERVACIONES:</td>
                <td width="80%"><div class="pill" style="min-height: 30px; padding-top: 10px; padding-bottom: 10px;">{{ mb_strtoupper($solicitud->observaciones, 'UTF-8') }}</div></td>
            </tr>
        </table>

    </div>
    <div class = "nueva-pagina">
        <div class="contenido-principal">
        
            <table class="layout-table" style="margin-bottom: 20px; border-collapse: separate; ">
                <tr>
                    <td width="50%" style="vertical-align: top;">
                        <table style="width: 100%; border-spacing: 0 8px;">
                            <tr>
                                <td width="70%">
                                    <div class="label-page2" style="margin-bottom: 5px; white-space: nowrap;">DÍAS PARA SU CONCLUSIÓN:</div>
                                </td>
                                <td width="30%">
                                    <div class="pill" style="width: 80%; text-align: center;">@if($tipo == 'seguimiento'){{$solicitud->dias}}@endif</div>
                                </td>
                            </tr>
                            <tr>
                                <td width="70%">
                                    <div class="label-page2" style="margin-bottom: 5px; white-space: nowrap;">NÚMERO DE AUDIENCIAS:</div>
                                </td>
                                <td width="30%">
                                    <div class="pill" style="width: 80%; text-align: center;">@if($tipo == 'seguimiento'){{ $audiencias->count() }}@endif</div>
                                </td>
                            </tr>
                            <tr >
                                <td colspan="3">
                                    <div class="label-page2" style="margin-bottom: 5px; font-size: 14px; text-align: center;">FECHA DE AUDIENCIA:</div>
                                    @if($tipo == 'seguimiento')
                                    @foreach($audiencias as $index => $audiencia)
                                        <div class="label-page2" style=" font-size: 11px; ">{{ $index + 1 }}.- {{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d / F / Y') }} A LAS {{ \Carbon\Carbon::parse($audiencia->hora)->translatedFormat('H:i') }} HORAS<br></div>
                                    @endforeach   
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td width="60%">
                                    <div class="label-page2" style="margin-bottom: 5px; ">POSIBLE CASO DE EXCEPCIÓN:</div>
                                </td>
                                
                                <td class="label-page2 "><center> SÍ <span class="pill-checkbox" style="margin-left: 10px; font-size: 15px;"> {{ $solicitud->caso_excepcion == 'Si' ? 'X' : '' }} </span> NO <span class="pill-checkbox" style="margin-left: 10px; font-size: 15px;"> {{ $solicitud->caso_excepcion == 'No' ? 'X' : '' }}</span></center></td><td></td> 
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <div class="label-page2" style="margin-bottom: 3px; font-size: 12px; text-align: center;">CONSTANCIA DE NO CONCILIACIÓN:</div>
                                    
                                </td>
                                
                            </tr>
                            <tr>
                                <td>
                                    <div class="label-page2" style="margin-bottom: 3px; ">POR INCOMPARECENCIA DEL CITADO</div>
                                    
                                </td>
                                <td width="40%"><div class="pill-checkbox" style="font-size:15px">@if($tipo == 'seguimiento'){{ $multas  ? 'X' : '' }}@endif </div></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="label-page2" style="margin-bottom: 3px; ">FALTA DE ACUERDO ENTRE LAS PARTES</div>
                                    
                                </td>
                                <td width="40%"><div class="pill-checkbox"></div></td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <div class="label-page2" style="margin-bottom: 3px; font-size: 14px; text-align: center;">CONVENIO</div>
                                    
                                </td>
                                
                            </tr>
                            <tr>
                                <td width="30%" class="label-page2 "> MONTO: <span class="pill" style="margin-left: 10px; text-align:center;">@if($tipo == 'seguimiento')${{number_format($monto, 2, '.', ',')}}@endif</span></td>
                                <td class="label-page2" > NÚMERO DE PAGOS: <span class="pill" style="margin-left: 10px; text-align:center;">@if($tipo == 'seguimiento'){{$pagos->count()}}@endif</span></td>
                            </tr>
                            <tr>
                                <td width="20%">
                                    <div class="label-page2" style="margin-bottom: 5px; ">DÍAS EN QUE CONCLUYÓ EL EXPEDIENTE:</div>
                                </td>
                                <td width="80%">
                                    <div class="pill" style="width: 80%; text-align: center; " > @if($tipo == 'seguimiento'){{$solicitud->dias}}@endif</div>
                                </td>
                            </tr>
                            
                            
                            
                        </table>
                    </td>
                    
                    <td width="50%" style="vertical-align: top;">
                        <table style="width: 100%; border-spacing: 0 8px;">
                            <tr >
                                <td colspan="3">
                                    <div class="label-page2" style="margin-bottom: 3px; font-size: 14px; text-align: center;">FECHAS DE PAGO</div>
                                    
                                </td>
                            </tr>
                            @if($tipo == 'seguimiento')
                            @foreach ($pagos as  $index => $pago)
                                <tr>
                                    <td>
                                        <div class="label-page2" style=" font-size: 12px; white-space: nowrap;">PAGO {{ $index + 1 }}.- {{ \Carbon\Carbon::parse($pago->fecha)->translatedFormat('d / F / Y') }} A LAS {{ \Carbon\Carbon::parse($pago->hora)->translatedFormat('H:i') }} HORAS</div>
                                        
                                    </td>
                               

                                </tr>
                            <tr>
                                    <td width="60%" class="label-page2 " font-size: 5px;> MONTO: <span class="pill" style="margin-left: 10px; text-align:center;">${{number_format($pago->monto, 2, '.', ',')}}</span></td>
                                    
                                    <td class="label-page2 " font-size: 5px;><span >@if($pago->estatus == 'Pagado') CUMPLIMIENTO @elseif($pago->estatus == 'No pagado') INCUMPLIMIENTO @else {{ mb_strtoupper($pago->estatus, 'UTF-8') }} @endif<s</td>
                                </tr>
                            @endforeach
                            @endif
                            
                            
                        </table>
                    </td>
                </tr>
            </table>

            <table class="data-table-ultima">
                <tr>
                    <td style= "text-align: center; ">
                        <span >CHECK LIST</span><br>
                        <span >DELEGACIÓN REGIONAL / OFICINA DE APOYO DE: </span><br>
                        <span >NOMBRE DEL CONCILIADOR: </span><br>
                    </td>
                    
                </tr>
                
            </table>
            <table class="data-table-ultima">
                <tr>
                    <td style= "text-align: center; ">
                        <span >No. Expediente</span>
                        
                    </td>
                        
                    <td style= "text-align: center; ">
                        <span>Fecha de inicio</span>
                    </td>
                    
                    <td style= "text-align: center; ">
                        <span >Fecha de termino</span>
                    </td>
                    <td style= "text-align: center; ">
                        <span >Tipo de Resolución</span>
                    </td>
                    
                </tr>
                <tr>
                    <td>
                        <br>
                    </td>
                    <td>
                        <br>
                    </td>
                    <td>
                        <br>
                    </td>
                    <td>
                        <br>
                    </td>
                </tr>
                
            </table>

            <table class="data-table-ultima">
                <tr style= "text-align: center; "> 
                    <td width="10%" >
                        <span >No.</span>
                        
                    </td>
                        
                    <td width="40%">
                        <span >Documento</span>
                    </td>
                    
                    <td width="12%" >
                        <span >Expediente Fisico</span>
                    </td>
                    <td width="12%" >
                        <span >Expediente Digital</span>
                    </td>
                    <td width="26%">
                        <span >Puede ser opcional pero asentado datos precisos, fecha y lugar de expedición en el acta respectiva, NOTA:</span>
                    </td>
                    
                </tr>
                <tr style= "text-align: center; ">
                    <td>1</td> <td>Caratula</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>2</td> <td>Formato de solicitud escrita con aviso de privacidad</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>3</td> <td>Decálogo de Derechos y Obligaciones</td> <td></td> <td></td> <td>Firmado por partes.</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>4</td> <td>Identificación de solicitante</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>5</td> <td>CURP</td> <td></td> <td></td> <td>NOTA: Se debe precisar el dato en el apartado corresponidente</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>6</td> <td>Carta Poder</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>7</td> <td>Instrumento Notarial (patrón)</td> <td></td> <td></td> <td>Físico o digital, en ambos casos previo cotejo con el original y asentando datos precisos del documento: lugar, fecha de expedición, fedatario y clausula donde se advientan las facultades.</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>8</td> <td>Asignación de Buzón electronico</td> <td></td> <td></td> <td>Solo en casi de aceptación, de lo contrario se asentará que no acepta la asignación. <br> NOTA: Deben Precisar si ambas partes aceptaron o solo uno</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>9</td> <td>Citatorio</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>10</td> <td>Notificación o razón actuarial</td> <td></td> <td></td> <td>NOTA: No localizable, domicilio incorrecto</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>11</td> <td>Archivo por falta de interés</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>12</td> <td>Acta de audiencia</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>13</td> <td>Convenio o acta señalando nueva sudiencia</td> <td></td> <td></td> <td>NOTA: Se celebraron 2 audiencias</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>14</td> <td>Constancia de no conciliación</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>15</td> <td>Actas de cumplimiento de pagos diferidos</td> <td></td> <td></td> <td>Solo en caso de fijarse fechas de pago</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>16</td> <td>Constancia de cumplimiento de pago</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>17</td> <td>Constancia de incumplimiento de pago</td> <td></td> <td></td> <td></td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>18</td> <td>Documento(s) o recibo(s) que acredita el pago</td> <td></td> <td></td> <td>NOTA: Cheque, transferencia</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>19</td> <td>Recibo de pago en efectivo</td> <td></td> <td></td> <td>Cuando el pago se realiza en efectivo se dará fe en el acta y se asentará el recibo de la cantidad con huella dactilar y firma del trabajador</td>
                </tr>
                <tr style= "text-align: center; ">
                    <td>20</td> <td>Otro documento:</td> <td></td> <td></td> <td>Describir el documento que se anexa (certificación, poderes, etc)</td>
                </tr>

                <tr style= "text-align: center; "> 
                    <td colspan="5" >
                        <span >RESPONSABLE DE ARCHIVO</span><br>
                        <span ></span>
                    </td>
                    
                    
                </tr>
                
            </table>
            

        </div>
    </div>
</body>
</html>