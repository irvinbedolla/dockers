<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- Acuse de recepción de la solicitud patronal de ratificación múltiple
         (RatificacionMultipleController::pdf). --}}
    <style>
        @page { margin: 28px 34px 40px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; color: #1F2A2B; }
        .cab { width: 100%; border-bottom: 3px solid #CEA845; padding-bottom: 8px; margin-bottom: 12px; }
        .cab td { vertical-align: middle; }
        .titulo { font-size: 15px; font-weight: bold; color: #496163; margin: 0; }
        .sub { font-size: 10px; color: #5E6E6F; margin: 2px 0 0; }
        .folio { border: 1px solid #DDE3E3; background: #F5F7F7; padding: 8px 12px; margin-bottom: 12px; }
        .folio b { font-size: 18px; font-family: 'Courier New', Courier, monospace; color: #2E3C3D; }
        h3 { font-size: 11.5px; color: #FFFFFF; background: #496163; padding: 5px 8px; margin: 14px 0 6px; }
        table.datos { width: 100%; border-collapse: collapse; }
        table.datos td { padding: 3px 6px; border-bottom: 1px solid #ECEFEF; }
        table.datos td.et { width: 30%; color: #5E6E6F; }
        table.lista { width: 100%; border-collapse: collapse; }
        table.lista th { background: #EEF2F2; color: #2E3C3D; font-size: 9.5px; padding: 4px; border: 1px solid #D9DFDF; text-align: left; }
        table.lista td { font-size: 9.5px; padding: 3px 4px; border: 1px solid #E3E8E8; }
        .num { text-align: right; }
        .nota { font-size: 9.5px; color: #4A5556; line-height: 1.45; text-align: justify; }
        .firma { margin-top: 30px; width: 55%; border-top: 1px solid #1F2A2B; text-align: center; padding-top: 4px; font-size: 9.5px; }
        .pie { position: fixed; bottom: -24px; left: 0; right: 0; font-size: 8.5px; color: #8A9899; text-align: center; }
    </style>
</head>
<body>
    @php
        $fecha = fn ($f) => $f ? $f->locale('es')->isoFormat('ddd D MMM YYYY') : '—';
        $mon = fn ($v) => '$'.number_format((float) $v, 2);
    @endphp
    <div class="pie">Acuse generado por SíConcilio · {{ now()->format('d/m/Y H:i') }} · Folio {{ $lote->folio }}</div>

    <table class="cab">
        <tr>
            <td style="width: 150px;"><img src="{{ public_path('assets/reporte_estadistico/logo_siconcilio.png') }}" style="width: 140px;"></td>
            <td>
                <p class="titulo">Acuse de recepción · Solicitud patronal de ratificación múltiple de convenios</p>
                <p class="sub">Centro de Conciliación Laboral del Estado de Michoacán de Ocampo · Oficina de {{ $lote->delegacion }}</p>
            </td>
        </tr>
    </table>

    <div class="folio">
        Folio de la solicitud: <b>{{ $lote->folio }}</b>
        &nbsp;&nbsp; Recibida el {{ $lote->created_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY, HH:mm') }} h
    </div>

    <h3>I y II. Parte patronal y representación</h3>
    <table class="datos">
        <tr><td class="et">Nombre, denominación o razón social</td><td><b>{{ $lote->empresa }}</b></td></tr>
        <tr><td class="et">Folio de representación patronal</td><td>{{ $lote->poder_folio }}</td></tr>
        @if ($lote->domicilio)
            <tr><td class="et">Domicilio del centro de trabajo</td><td>{{ implode(', ', array_filter([$lote->domicilio['calle'] ?? null, $lote->domicilio['numero'] ?? null, $lote->domicilio['colonia'] ?? null, isset($lote->domicilio['cp']) ? 'C.P. '.$lote->domicilio['cp'] : null, $lote->domicilio['municipio'] ?? null, $lote->domicilio['estado'] ?? null])) }}</td></tr>
        @endif
        <tr><td class="et">Registró la solicitud</td><td>{{ $lote->solicitante }} · {{ $lote->correo }} · {{ $lote->telefono }}</td></tr>
    </table>

    <h3>III. Datos de la solicitud</h3>
    <table class="datos">
        <tr><td class="et">Motivo de la ratificación</td><td>{{ $motivos[$lote->motivo] ?? $lote->motivo }}</td></tr>
        <tr><td class="et">Fecha propuesta</td><td>{{ $fecha($lote->fecha_propuesta) }}</td></tr>
        <tr><td class="et">Delegación regional</td><td>{{ $lote->delegacion }} · {{ $direccion }}</td></tr>
        <tr><td class="et">Número de trabajadores</td><td>{{ $lote->trabajadores }}</td></tr>
        <tr><td class="et">Importe neto total de los convenios</td><td>{{ $mon($lote->monto_total) }}</td></tr>
        @if ($lote->observaciones)
            <tr><td class="et">Observaciones</td><td>{{ $lote->observaciones }}</td></tr>
        @endif
    </table>

    <h3>IV. Relación de trabajadores y citas</h3>
    <table class="lista">
        <tr>
            <th style="width: 22px;">No.</th><th>Trabajador</th><th>Puesto</th><th class="num">Neto a pagar</th>
            <th>Folio de cita</th><th>Día</th><th>Hora</th>
        </tr>
        @foreach ($citas as $x)
            <tr>
                <td>{{ $x['trabajador']->numero }}</td>
                <td>{{ $x['trabajador']->nombre_completo }}</td>
                <td>{{ $x['trabajador']->puesto }}</td>
                <td class="num">{{ $mon($x['trabajador']->neto) }}</td>
                <td>{{ $x['cita']?->folio_cita ?? '—' }}</td>
                <td>{{ $x['cita'] ? $fecha($x['cita']->fecha) : '—' }}</td>
                <td>{{ $x['cita']?->hora?->format('H:i') ?? '—' }}</td>
            </tr>
        @endforeach
    </table>

    <h3>V. Documentación anexa</h3>
    <table class="datos">
        <tr><td class="et">Relación de trabajadores (Excel)</td><td>{{ $lote->archivo_nombre }}</td></tr>
        <tr><td class="et">Identificación y documento de personalidad</td><td>Los del folio de representación patronal {{ $lote->poder_folio }}</td></tr>
        <tr><td class="et">Constancia de situación fiscal</td><td>{{ $lote->constancia_fiscal ? 'Anexada' : 'No se anexó (opcional)' }}</td></tr>
    </table>

    <h3>VI. Declaración del solicitante</h3>
    <p class="nota">
        Manifiesto bajo protesta de decir verdad que la información proporcionada es cierta, que los datos individualizados
        corresponden a cada trabajador y que solicito la intervención del Centro de Conciliación Laboral del Estado de Michoacán
        para llevar a cabo las actuaciones correspondientes para la ratificación de los convenios presentados. Asimismo, reconozco
        que cada convenio será objeto de revisión y atención individual, y que la presentación de esta solicitud no implica su
        aprobación automática ni sustituye la comparecencia y manifestación de voluntad de las partes.
        <br><b>Aceptada en línea el {{ $lote->declaracion_at?->format('d/m/Y H:i') }} h.</b>
    </p>

    <p class="nota" style="margin-top: 10px;">
        Cada trabajador debe presentarse en su día y hora con identificación oficial vigente, junto con la parte patronal o su
        representante legal. Se elaborará un convenio y un acta por cada trabajador.
    </p>

    <div class="firma">{{ $lote->solicitante }}<br>Nombre y firma del solicitante</div>
</body>
</html>
