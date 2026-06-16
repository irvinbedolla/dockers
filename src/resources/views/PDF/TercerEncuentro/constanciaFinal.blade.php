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
            top: 520px; /*Posición del nombre del participante*/   
            left: 0;
            width: 100%;
            text-align: center; 
        }

        /* Estilo del nombre */
        .nombre-participante {
            font-family: "Gibson";
            font-size: 38px;      
            font-weight: bold;     
            color: #1a1a1a;        
        }

    </style>
</head>
<body>
    <img src="{{ public_path('assets/images/constancia_final.jpg') }}" class="fondo-membrete">

    <div class="content">
        <div class="nombre-participante">
            {{ $nombre }}
        </div>
    </div>
</body>
</html>