<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <title>Sí Concilio - Citas en línea fuera de servicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{--
        Aviso mientras las citas en línea están apagadas (config app.citas_en_linea).
        HomeController::citas y turnos_publico redirigen aquí.
    --}}
    <style>
        :root {
            --color-guinda: #496163;
            --color-oro: #CEA845;
            --color-gris-bg: #f8f9fa;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--color-gris-bg);
            color: #333;
            padding-top: 100px;
        }
        .navbar-institucional {
            background-color: #fff;
            border-bottom: 3px solid var(--color-oro);
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
        }
        .card-header-guinda {
            background-color: var(--color-guinda);
            color: #fff;
            font-weight: 600;
        }
        .aviso-icono { font-size: 56px; color: var(--color-oro); line-height: 1; }
        .sede { border-left: 4px solid var(--color-guinda); background: #f1f3f5; border-radius: 6px; padding: 10px 14px; height: 100%; }
        .sede b { color: var(--color-guinda); }
    </style>
</head>
<body>
    <nav class="navbar navbar-light navbar-institucional fixed-top px-4">
        <div class="container-fluid">
            <span class="navbar-brand">
                <img src="{{ asset('assets/images/Logos 2.png') }}" alt="CCL Michoacán" height="65">
            </span>
            <span class="navbar-text fw-bold d-none d-md-block text-secondary">
                Centro de Conciliación Laboral del Estado de Michoacán
            </span>
        </div>
    </nav>

    <main class="container mb-5">
        <div class="card shadow-sm border-0">
            <div class="card-header card-header-guinda py-3">
                <h1 class="m-0 text-center fs-5"><i class="bi bi-calendar-x me-2"></i>Citas en línea</h1>
            </div>
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="aviso-icono mb-3"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <h2 class="fs-4 fw-bold">Servicio fuera de línea</h2>
                    <p class="fs-6 mb-1">Por el momento el registro de citas en línea se encuentra <b>fuera de servicio</b>.</p>
                    <p class="fs-6">Te invitamos a <b>registrarte de forma presencial</b> en cualquiera de nuestras sedes.</p>
                </div>

                <div class="row g-3">
                    @foreach (\App\Support\HorarioCitas::DIRECCIONES_SEDES as $sede => $direccion)
                        <div class="col-12 col-md-6">
                            <div class="sede small">
                                <b><i class="bi bi-geo-alt-fill me-1"></i>{{ $sede }}</b><br>
                                {{ $direccion }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-center text-muted small mt-4 mb-0">Lamentamos las molestias. Estamos trabajando para restablecer el servicio.</p>
            </div>
        </div>
    </main>
</body>
</html>
