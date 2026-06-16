<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>Sí Concilio</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <!-- Bootstrap 5.3.3 -->
        <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
       
        <!-- Template CSS -->
        <link rel="icon"       href="../public/assets/images/ccl-r.png" type="image/x-icon">
        <link rel="stylesheet" href="../public/assets/css/style.css">
        <link rel="stylesheet" href="../public/assets/css/components.css">

        <style>
            .header img { 
                width: 180px; height: 45px; 
            }
            body {
                font-family: sans-serif;
                font-size: 12px;
                text-align: justify;
                color: black;
            }
            p {
                line-height: 1.5;
            }
        </style>
    </head>
    @php     
        $direccion_sede='';
        if($solicitud->delegacion === 'Morelia'){
            $direccion_sede='BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P.58260 MORELIA, MICHOACÁN DE OCAMPO';
        }    
        if($solicitud->delegacion === 'Uruapan'){
            $direccion_sede='NUEVO PARICUTÍN NO. 308, COL. JARDINES DE SAN RAFAEL, C.P.30136 URUAPAN, MICHOACÁN DE OCAMPO. SE ENCUENTRA DENTRO DEL RECINTÓ DONDE ESTA RENTAS DEL
                ESTADO, POR LA CLÍNICA DEL IMSS NO.76.';
        }
        if($solicitud->delegacion === 'Zamora') {
            $direccion_sede='JUSTO SIERRA ORIENTE NO. 290, COL. JARDINES DE CATEDRAL, C.P.59670 ZAMORA, MICHOACÁN DE OCAMPO';
        }  
        if($solicitud->delegacion === 'Zitácuaro') {
            $direccion_sede='CUAUHTEMOC ORIENTE NO. 15, COL. CUAUHTEMOC, C.P. 61506ZITÁCUARO, MICHOACÁN DE OCAMPO';
        } 
        if($solicitud->delegacion === 'Lázaro Cárdenas') {
            $direccion_sede='PARACHO NO. 26, COL. 600 CASAS, C.P.60950 LÁZARO CÁRDENAS, MICHOACÁN DE OCAMPO';
        }  
        if($solicitud->delegacion === 'Sahuayo') {
            $direccion_sede='AV. UNIVERSIDAD SUR NO. 3000, COL. LOMAS DE UNIVERSIDAD, C.P.59103 SAHUAYO DE MORELOS, MICHOACÁN DE OCAMPO';
        }  
    @endphp

    <body>
        <div class="header">
            <img src="{{ public_path('assets/images/Logos 2.png') }}" alt="Encabezado">
        </div>
        <div class="content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                           <p><b>ASUNTO: AUDIENCIA DE CONCILIACIÓN PREJUDICIAL<br>  
                              SOLICITANTE:{{ $solicitante->nombre }}<br>
                              CITADO: {{ $citado->nombre}} {{ $citado->primer_apellido}} {{ $citado->segundo_apellido}}<br>
                              OBJETO DE LA CONCILIACIÓN: <br>
                              NÚMERO DE IDENTIFICACIÓN ÚNICO: {{ $solicitud->NUE }}<br>
                              DELEGACIÓN REGIONAL DEL CENTRO DE CONCILIACIÓN LABORAL DEL ESTADO DE MICHOACÁN DE OCAMPO: Michoacán de Ocampo.
                            
                              FECHA DE EMISIÓN DE DOCUMENTOS:  {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}<br>
                            </b></p>  

                            <p>Con fecha {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y \s\i\e\n\d\o \l\a\s H:i:s') }} horas, ante esta 
                                Autoridad Conciliadora, {{ $solicitante->nombre }}, me doy por notificado (a) personalmente de la fecha para la 
                                celebracion de la Audiencia de Conciliación de la solicitud de Conciliación con número de identificación único 
                                {{ $solicitud->NUE }}, misma que tendrá verificativo el día 16 de Mayo de 2025 a las 11:30:00 horas, en la sala _______ 
                                de la Delegación Regional de <b>Michoacán de Ocampo</b> del Centro de Conciliación Laboral del Estado de Michoacán de Ocampo, 
                                con domicilio en <b>{{$direccion_sede}}.</b></p>
                            <p>Asimismo, de conformidad con la fraccion X del articulo 684- E, me hago conocedor que <b>de no comparecer se
                                archivara el presente asunto por falta de interés.</b></p>

                            
                            <center><p><b>
                                ___________________________________
                                {{ $solicitante->nombre }}</b></p><br><br>
                            <p><b>___________________________________<br>
                                Beatriz Adriana Torres González<br>
                                FUNCIONARIo CONCILIADOR<br></b></p></center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>