<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Constancia de participación</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 0px 0px;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
        }

        .fondo-membrete {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .content {
            position: absolute;
            top: 410px; /*Posición del nombre del participante*/   
            left: 0;
            width: 100%;
            text-align: center; 
        }

        /* Estilo del nombre */
        .nombre-participante {
            font-family: "Gibson";
            font-size: 30px;      
            font-weight: bold;     
            color: #1a1a1a;        
        }

        .texto-secundario {
            margin: 0 auto 0 auto;
            width: 82%;
            font-size: 19px;
            font-family: "Poppins", sans-serif;
            text-align: justify;
        }
    </style>
</head>
<body>
    <img src="{{ public_path('assets/images/reconocimiento-asistentes.png') }}" class="fondo-membrete">
   

    <div class="content">
        <div class="nombre-participante">
            {{ $participante->nombre }} {{ $participante->primer_apellido }} {{ $participante->segundo_apellido }}
        </div>
    </div>
</body>
</html>
