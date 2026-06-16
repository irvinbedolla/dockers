<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="public/assets/images/logo-ccl.png" type="image/x-icon">
    <title>Si Concilio - No disponible</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-light bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand" href="">
                <img src="{{ url('public/assets/images/Logos 2.png') }}" width="250" height="90" alt="CCL">
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">Recepción de solicitudes en línea no disponible</h4>
                        <p class="mb-2">
                            En este momento no es posible levantar solicitudes porque hoy es un <strong>día inhábil</strong> o estamos fuera de <strong>horario de atención</strong>.
                        </p>

                        @if(!empty($motivo))
                            <div class="alert alert-warning mt-3 mb-0">{{ $motivo }}</div>
                        @endif

                        <div class="mt-4">
                            <a class="btn btn-secondary" href="{{ url('/') }}">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
