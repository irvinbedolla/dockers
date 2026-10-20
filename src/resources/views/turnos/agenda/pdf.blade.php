<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }} · {{ $periodo }}</title>
    <style>
        @page { margin: 22px 24px 30px; }
        body { margin: 0; }
    </style>
</head>
<body>
    @include('turnos.agenda._contenido', ['saltoPorSede' => true])
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text($pdf->get_width() - 110, $pdf->get_height() - 22, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 7, [0.45, 0.5, 0.5]);
        }
    </script>
</body>
</html>
