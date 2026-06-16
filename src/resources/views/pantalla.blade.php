<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Pantalla de Turnos</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

<style>
    :root {
        --color-guinda: #4A001F;
        --color-naranja: #FF4500;
        --color-fondo: #2c3e50;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background-color: var(--color-fondo);
        margin: 0;
        overflow: hidden;
    }

    /* Ocultar secciones por defecto */
    .main-container {
        display: none; 
        height: 100vh;
        padding: 20px;
    }

    /* Clase para mostrar la sección activa con una transición suave */
    .activa {
        display: block !important;
        animation: fadeIn 0.8s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .turno-row {
        background: white;
        border-radius: 12px;
        margin-bottom: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        height: calc(88vh / 9); 
        overflow: hidden;
    }

    .tramite-badge {
        background-color: var(--color-naranja);
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        font-size: 1.8rem;
        font-weight: bold;
        text-transform: uppercase;
        margin-right: 20px;
    }

    .info-box {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: flex-start; 
        padding-left: 20px;
    }

    .info-box h2 {
        margin: 0;
        font-size: 1.1rem;
        color: #777;
        margin-right: 15px;
        font-weight: bold;
    }

    .datos-tramite {
        display: flex;
        flex-direction: row; 
        align-items: baseline;
        gap: 15px;
    }

    .nue-texto {
        font-size: 2.0rem;
        font-weight: bold;
        color: #000;
        margin: 0;
    }

    .nombre-texto {
        font-size: 0.8rem;
        color: #444;
        text-transform: uppercase;
    }

    .titulo-columna {
        color: white;
        font-weight: bold;
        text-align: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
        text-transform: uppercase;
        background: rgba(0,0,0,0.3);
        padding: 10px;
        border-radius: 10px;
    }
</style>

</head>
<body>


    <div id="pantalla1" class="main-container activa">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 titulo-columna">CUMPLIMIENTOS</div>
            </div>

            @foreach($cumplimientos as $cumplimiento)
                <div class="turno-row">
                    <div class="info-box">
                        <h2>TRÁMITE:</h2>
                        <div class="tramite-badge">
                            {{ $cumplimiento->tramite }} 
                        </div>

                        <div class="datos-tramite">
                            <span class="nue-texto">{{ $cumplimiento->NUE }}</span>
                            <span class="nombre-texto">{{ $cumplimiento->nombre }}</span>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($cumplimientos->isEmpty())
                <div style="text-align: center; color: white; margin-top: 100px;">
                    <h1>Sin Cumplimientos pendientes</h1>
                </div>
            @endif
        </div>
    </div>

    <div id="pantalla2" class="main-container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 titulo-columna">RATIFICACIONES</div>
            </div>

            @foreach($turnos as $turno)
                <div class="turno-row">
                    <div class="info-box">
                        <h2>TRÁMITE:</h2>
                        <div class="tramite-badge">
                            {{ $turno->tramite }} 
                        </div>

                        <div class="datos-tramite">
                            <span class="nue-texto">{{ $turno->NUE }}</span>
                            <span class="nombre-texto">{{ $turno->nombre }}</span>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($turnos->isEmpty())
                <div style="text-align: center; color: white; margin-top: 100px;">
                    <h1>Sin Ratificaciones pendientes</h1>
                </div>
            @endif
        </div>
    </div>

    <div id="pantalla3" class="main-container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 titulo-columna">AUDIENCIAS</div>
            </div>

            @foreach($audienencias as $audienencia)
                <div class="turno-row">
                    <div class="info-box">
                        <h2>TRÁMITE:</h2>
                        <div class="tramite-badge">
                            {{ $audienencia->tramite }} 
                        </div>

                        <div class="datos-tramite">
                            <span class="nue-texto">{{ $audienencia->NUE }}</span>
                            <span class="nombre-texto">{{ $audienencia->nombre }}</span>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($turnos->isEmpty())
                <div style="text-align: center; color: white; margin-top: 100px;">
                    <h1>Sin Audiencias pendientes</h1>
                </div>
            @endif
        </div>
    </div>

    <div id="pantalla4" class="main-container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 titulo-columna">SOLICITUDES</div>
            </div>

            @foreach($solicitudes as $solicitud)
                <div class="turno-row">
                    <div class="info-box">
                        <h2>TRÁMITE:</h2>
                        <div class="tramite-badge">
                            {{ $solicitud->tramite }} 
                        </div>

                        <div class="datos-tramite">
                            <span class="nue-texto">{{ $solicitud->NUE }}</span>
                            <span class="nombre-texto">{{ $solicitud->nombre }}</span>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($solicitudes->isEmpty())
                <div style="text-align: center; color: white; margin-top: 100px;">
                    <h1>Sin Solicitudes pendientes</h1>
                </div>
            @endif
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <script>
        let paso = 1;

        function rotar() {
            // Quitamos la clase 'activa' de todas las pantallas
            $(".main-container").removeClass("activa");

            if (paso === 1) {
                $("#pantalla2").addClass("activa");
                paso = 2;
            } else if (paso === 2) {
                $("#pantalla3").addClass("activa");
                paso = 3;
            } else if (paso === 3) {
                $("#pantalla4").addClass("activa");
                paso = 4;
            } else {
                // Si ya pasó la tercera, recargamos para traer datos nuevos de la BD
                window.location.reload();
            }
        }

        // Ejecutar rotación cada 10 segundos
        setInterval(rotar, 10000);
    </script>
</body>
</html>