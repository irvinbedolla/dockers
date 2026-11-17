<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte estadístico · {{ $periodo }}</title>
    @include('estadisticas.reporte._estilos')
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; }
        .re-lam { page-break-after: always; }
        .re-lam:last-child { page-break-after: auto; }
    </style>
</head>
<body>
    @include('estadisticas.reporte._laminas')
</body>
</html>
