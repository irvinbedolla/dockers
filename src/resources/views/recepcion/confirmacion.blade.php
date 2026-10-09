<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
    <title>Confirmación de Cita - Si concilio</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --color-guinda: #496163;
            --color-guinda-dark: #530c3a;
            --color-oro: #CEA845;
            --color-oro-dark: #b59238;
            --color-gris-bg: #f8f9fa;
        }

        /* Página */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--color-gris-bg);
            color: #333;
            padding-top: 100px; /* Espacio para el navbar fixed */
        }

        /* Navbar */
        .navbar-institucional {
            background-color: #fff;
            border-bottom: 3px solid var(--color-oro);
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }

        /* Encabezados */
        .card-header-guinda {
            background-color: var(--color-guinda);
            color: #fff;
            font-weight: 600;
        }
        
        .text-guinda {
            color: var(--color-guinda);
            font-weight: 600;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light navbar-institucional fixed-top px-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="CCL Michoacán" height="65">
            </a>
            <span class="navbar-text fw-bold d-none d-md-block text-secondary">
                Centro de Conciliación Laboral del Estado de Michoacán
            </span>
        </div>
    </nav>

    <main class="container mb-5">
        <div class="row justify-content-center mt-4">
            <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6">
                
                <div class="card shadow-sm border-0">
                    <div class="card-header card-header-guinda py-3">
                        
                        <h4 class="m-0 text-center fs-5">Confirmación de Cita</h4>
                    </div>
                    
                    <div class="card-body p-4"> 
                        @if($bandera === '1')
                            <div class="alert alert-success text-center mb-4 border-0 shadow-sm" role="alert" style="background-color: #d4edda; color: #155724;">
                                <i class="bi bi-bookmark-check"></i> ¡Asistencia de folio: <strong>#{{ $cita->consecutivo }}</strong> confirmada!
                            </div>

                            <h5 class="text-guinda border-bottom pb-2 mb-3">Detalles de la Cita</h5>
                            
                            <div class="px-3" style="font-size: 1.05rem; line-height: 1.8;">
                                <p class="mb-2"><strong>Folio:</strong> #{{ $cita->consecutivo }}</p>
                                <p class="mb-2"><strong>Nombre:</strong> {{ $cita->solicitante }}</p>
                                <p class="mb-2"><strong>Fecha y Hora:</strong> {{ $fecha_hora }}</p>
                                <p class="mb-2"><strong>Estado: </strong>{{ mb_strtoupper($cita->estatus, 'UTF-8') }}</p>
                                @if($cita->estatus === 'asistencia') <p class="mb-2"><strong> {{ $cita->lugar_auxiliar }}</strong></p> @endif
                            </div>
                        @elseif($bandera === '2')
                            <div class="alert alert-warning text-center mb-4 border-0 shadow-sm" role="alert">
                                <h5 ><i class="bi bi-exclamation-triangle-fill"></i> ¡La cita ya fue confirmada! </h5>
                            </div>
                        @elseif($bandera === '3')
                            <div class="alert alert-danger text-center mb-4 border-0 shadow-sm" role="alert">
                                <h5 ><i class="bi bi-exclamation-triangle-fill"></i> ¡La cita ya expiro! </h5>
                                <h5 class="text-guinda "> Favor de generar una nueva cita</h5>
                            </div>
                        @elseif($bandera === '4')
                            <div class="alert alert-danger text-center mb-4 border-0 shadow-sm" role="alert">
                                <h5 ><i class="bi bi-exclamation-triangle-fill"></i> ¡Folio No encontrado! </h5>
                            </div>
                        
                        @else
                            <div class="alert alert-warning text-center mb-4 border-0 shadow-sm" role="alert">
                                <h5 ><i class="bi bi-exclamation-triangle-fill"></i> ¡El día de la cita no coincide! </h5>
                                <h5 class="text-guinda "> Favor de asistir el día: {{ $fecha_hora }}</h5>
                            </div>
                            
                        
                        @endif
                    </div>
                </div>
                </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>