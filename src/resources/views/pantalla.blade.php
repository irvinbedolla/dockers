<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Pantalla de Turnos</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

<style>
    :root {
        --color-guinda: #354647;
        --color-naranja: #FF4500;
        --color-fondo: #2c3e50;
        --color-tabla-header: rgba(255, 255, 255, 0.15);
        --color-tabla-border: rgba(255, 255, 255, 0.3);
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background-color: var(--color-fondo);
        margin: 0;
        overflow: hidden;
    }

    /* Contenedores principales */
    .main-container {
        display: none; 
        height: 100vh;
        padding: 20px;
    }

    .activa {
        display: block !important;
        animation: fadeIn 0.8s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .titulo-columna {
        color: white;
        font-weight: bold;
        text-align: center;
        font-size: 1.8rem;
        text-transform: uppercase;
        background: rgba(0,0,0,0.4);
        padding: 15px;
        border-radius: 10px 10px 0 0; /* Borde superior redondeado para unificar con la tabla */
        margin-bottom: 0;
    }

    /* Estilos Tabulares (Reemplazando los .turno-row) */
    .tabla-turnos {
        width: 100%;
        color: white;
        text-align: center;
        border-collapse: collapse;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 0 0 10px 10px;
    }

    .tabla-turnos th, .tabla-turnos td {
        padding: 18px 10px;
        font-size: 1.4rem;
        vertical-align: middle;
    }

    /* Cabeceras de columnas */
    .tabla-turnos thead th {
        background-color: var(--color-tabla-header);
        text-transform: uppercase;
        font-size: 1.2rem;
        font-weight: bold;
        color: #ccc;
        border-bottom: 2px solid white;
    }

    /* Filas de datos con borde punteado inferior imitando la imagen 2 */
    .tabla-turnos tbody tr {
        border-bottom: 1px dashed var(--color-tabla-border);
    }

    .tabla-turnos tbody tr:last-child {
        border-bottom: none;
    }

    .badge-tipo {
        background-color: #717776;
        color: white;
        padding: 5px 15px;
        border-radius: 5px;
        font-size: 1.2rem;
        font-weight: bold;
        text-transform: uppercase;
        display: inline-block;
    }

    .nombre-texto {
        font-size: 1.1rem;
        color: #ddd;
        text-transform: uppercase;
        display: block;
    }
    .badge-estatus {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 6px;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 1rem;
        margin-bottom: 4px;
    }

    /* Colores según el estado */
    .estatus-confirmado {
        background-color: #28a745; 
        color: #ffffff;
    }

    .estatus-expirada {
        background-color: #dc3545; 
        color: #ffffff;
    }

    .estatus-no-atendido {
        background-color: #b1770d; 
        color: #ffffff;            
    }


    .estatus-default {
        background-color: #6c757d;
        color: #ffffff;
    }
</style>

</head>
<body>


    <div id="pantalla1" class="main-container activa">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 titulo-columna">CUMPLIMIENTOS</div>
            </div>

            <table class="tabla-turnos">
                <thead>
                    <tr>
                        <th>TIPO</th>
                        <th>FOLIO (NUE)</th>
                        <th>SOLICITANTE / CITADO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cumplimientos as $cumplimiento)
                        <tr>
                            <td><span class="badge-tipo">{{ $cumplimiento->tramite }}</span></td>
                            <td><strong>{{ $cumplimiento->NUE }}</strong></td>
                            <td><span class="nombre-texto">{{ $cumplimiento->nombre }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

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
            @if($turnos->isEmpty())
                <div style="text-align: center; color: white; margin-top: 100px;">
                    <h1>Sin Ratificaciones pendientes</h1>
                </div>
            @else
                <table class="tabla-turnos">
                    <thead>
                        <tr>
                            <th>HORA CITA</th>
                            <th>FOLIO</th>
                            <th>MÓDULO</th>
                            <th>TIPO</th>
                            <th>ESTATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($turnos as $turno)
                            <tr>
                                <td><strong>{{ $turno->hora->format('H:i') }}</strong></td>
                                <td><strong>{{ str_pad($turno->NUE, 5, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>{{ $turno->modulo }}</td>
                                <td><span class="badge-tipo">{{ $turno->tramite }}</span></td>
                                <td>
                                    @php
                                        $estatusVal = strtolower(trim($turno->estatus));
                                    @endphp

                                    <span class="badge-estatus 
                                        @if($estatusVal == 'confirmada')
                                            estatus-confirmado
                                        @elseif($estatusVal == 'expirada')
                                            estatus-expirada
                                        @elseif($estatusVal == 'pendiente')
                                            estatus-no-atendido
                                        @else
                                            estatus-default
                                        @endif">
                                        {{ $turno->estatus }}
                                    </span>
                                    <br>
                                    <span class="nombre-texto">{{ $turno->nombre }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            
        </div>
    </div>

    <div id="pantalla3" class="main-container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 titulo-columna">AUDIENCIAS</div>
            </div>

            <table class="tabla-turnos">
                <thead>
                    <tr>
                        <th>TIPO</th>
                        <th>AUXILIAR</th>
                        <th>SOLICITANTE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audienencias as $audienencia)
                        <tr>
                            <td><span class="badge-tipo">{{ $audienencia->tramite }}</span></td>
                            <td><strong>{{ $audienencia->NUE }}</strong></td>
                            <td><span class="nombre-texto">{{ $audienencia->nombre }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($audienencias->isEmpty())
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
            @if($solicitudes->isEmpty())
                <div style="text-align: center; color: white; margin-top: 100px;">
                    <h1>Sin Solicitudes pendientes</h1>
                </div>
            @else
                <table class="tabla-turnos">
                    <thead>
                        <tr>
                            <th>HORA CITA</th>
                            <th>FOLIO</th>
                            <th>MÓDULO</th>
                            <th>TIPO</th>
                            <th>ESTATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($solicitudes as $solicitud)
                            <tr>
                                <td><strong>{{ $solicitud->hora->format('H:i') }}</strong></td>
                                <td><strong>{{ str_pad($solicitud->NUE, 5, '0', STR_PAD_LEFT) }}</strong></td>
                                <td>{{ $solicitud->modulo }}</td>
                                <td><span class="badge-tipo">{{ $solicitud->tramite }}</span></td>
                                <td>
                                    @php
                                        $estatusVal = strtolower(trim($solicitud->estatus));
                                    @endphp

                                    <span class="badge-estatus 
                                        @if($estatusVal == 'confirmada')
                                            estatus-confirmado
                                        @elseif($estatusVal == 'expirada')
                                            estatus-expirada
                                        @elseif($estatusVal == 'pendiente')
                                            estatus-no-atendido
                                        @else
                                            estatus-default
                                        @endif">
                                        {{ $solicitud->estatus }}
                                    </span>
                                    <br>
                                    <span class="nombre-texto">{{ $solicitud->nombre }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

    <script>
        let paso = 1;

        function rotar() {
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
                window.location.reload();
            }
        }

        setInterval(rotar, 10000);
    </script>
</body>
</html>