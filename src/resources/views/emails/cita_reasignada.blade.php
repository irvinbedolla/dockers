<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tu cita cambió de módulo</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #2E3C3D; line-height: 1.5;">
    <h2 style="color: #496163;">Hola, {{ $cita->solicitante }}</h2>
    <p>
        Tu cita de <strong>{{ $cita->tipo }}</strong> con folio
        <strong>{{ str_pad($cita->consecutivo, 5, '0', STR_PAD_LEFT) }}</strong>
        sigue el <strong>{{ \Illuminate\Support\Str::ucfirst($cita->fecha->locale('es')->isoFormat('dddd D [de] MMMM')) }}</strong>
        a las <strong>{{ $cita->hora->format('H:i') }}</strong> en la sede {{ $cita->delegacion }}.
    </p>
    <p>
        Lo único que cambió es el módulo donde te atenderán:
        @if ($moduloAnterior)<span style="text-decoration: line-through; color: #8A9899;">{{ $moduloAnterior }}</span> →@endif
        <strong>{{ $cita->lugar_auxiliar }}</strong>.
    </p>
    <p>Te adjuntamos tu acuse actualizado. Preséntalo el día de tu cita.</p>
    <p style="color: #8A9899; font-size: 13px;">Centro de Conciliación Laboral del Estado de Michoacán</p>
</body>
</html>
