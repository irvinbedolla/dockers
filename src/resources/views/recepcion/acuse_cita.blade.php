<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0px;
            size: A4 portrait;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #000000;
        }

        .fondo-membrete {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .capa-datos {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

      
        .campo-solicitante {
            position: absolute;
            top: 183px;
            left: 185px;
            font-size: 13px;
            font-weight: bold;
        }

   
        .campo-domicilio {
            position: absolute;
            top: 365px;
            left: 35px;
            width: 125px;
            font-size: 10px;
            text-align: center;
        }

        .campo-folio {
            position: absolute;
            top: 390px;
            left: 170px;
            width: 80px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
        }
        .campo-tramite {
            position: absolute;
            top: 390px;
            left: 275px;
            width: 80px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
        }

        .campo-modulo {
            position: absolute;
            top: 390px;
            left: 370px;
            width: 85px;
            font-size: 13px;
            text-align: center;
        }

        .campo-fecha {
            position: absolute;
            top: 390px;
            left: 480px;
            width: 80px;
            font-size: 14px;
            text-align: center;
        }

        .campo-horario {
            position: absolute;
            top: 390px;
            left: 570px;
            width: 80px;
            font-size: 14px;
            text-align: center;
        }

        .campo-qr {
            position: absolute;
            top: 315px;
            left: 690px;
            width: 12%;
            height: 11%;
        }
        
    </style>
</head>
<body>

     @if($cita->tipo !== 'Ratificación')<img src="{{ public_path('assets/images/acuse_cita.jpg') }}" class="fondo-membrete">@else<img src="{{ public_path('assets/images/acuse_cita2.jpg') }}" class="fondo-membrete">@endif

    <div class="capa-datos">
        
        <div class="campo-solicitante">
            {{ $cita->solicitante }}
        </div>

        <div class="campo-domicilio">
            {{ $direccion }}
        </div>

        <div class="campo-folio">
            {{ str_pad($cita->consecutivo, 5, '0', STR_PAD_LEFT) }}
        </div>

        <div class="campo-tramite">
            {{ str_pad($cita->tipo, 5, '0', STR_PAD_LEFT) }}
        </div>

        <div class="campo-modulo">
            {{ $cita->lugar_auxiliar }}
        </div>

        <div class="campo-fecha">
            {{ $fecha }}
        </div>

        <div class="campo-horario">
            {{ $hora }}
        </div>
        

        <div class="campo-qr">
            @if(isset($qrCode))
                <img src="data:image/png;base64, {!! base64_encode($qrCode) !!}" width="100%" height="100%">
            @endif
        </div>
        

    </div>

</body>
</html>