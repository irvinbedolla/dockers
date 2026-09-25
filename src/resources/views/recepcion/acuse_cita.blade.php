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
            left: 80px;
            width: 125px;
            font-size: 10px;
            text-align: center;
        }

        .campo-folio {
            position: absolute;
            top: 390px;
            left: 225px;
            width: 80px;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
        }

        .campo-modulo {
            position: absolute;
            top: 390px;
            left: 320px;
            width: 85px;
            font-size: 13px;
            text-align: center;
        }

        .campo-fecha {
            position: absolute;
            top: 390px;
            left: 425px;
            width: 80px;
            font-size: 14px;
            text-align: center;
        }

        .campo-horario {
            position: absolute;
            top: 390px;
            left: 520px;
            width: 80px;
            font-size: 14px;
            text-align: center;
        }

        .campo-qr {
            position: absolute;
            top: 308px;
            left: 650px;
            width: 13%;
            height: 12%;
        }
        .campo-requisitos {
            position: absolute;
            top: 495px;
            left: 207px;
            font-size: 17px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <img src="{{ public_path('assets/images/acuse_cita.jpg') }}" class="fondo-membrete">

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
        <div class="campo-requisitos">
            REQUISITOS PARA SU SOLICITUD {{  mb_strtoupper($cita->tipo, 'UTF-8') }}
        </div>

    </div>

</body>
</html>