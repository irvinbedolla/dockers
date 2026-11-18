@extends('ratificacion_multiple._layout')
@section('titulo', 'Solicitud registrada')
{{-- RatificacionMultipleController::exito: folio maestro, citas y acuse. --}}
@php
    $mon = fn ($v) => '$'.number_format((float) $v, 2);
@endphp

@section('contenido')
<div class="rm-card">
    <div class="rm-card__cab">
        <h1><i class="bi bi-check-circle-fill me-2"></i>Tu solicitud de ratificación múltiple quedó registrada</h1>
        <p>Te enviamos el acuse a {{ $lote->correo }}.</p>
    </div>
    <div class="p-4">
        <div class="d-flex flex-wrap gap-4 align-items-center mb-4">
            <div>
                <div class="rm-ayuda text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 1px;">Folio de la solicitud</div>
                <div style="font-family: 'Courier New', monospace; font-size: 30px; font-weight: 700; color: var(--rm-tinta);">{{ $lote->folio }}</div>
            </div>
            <div class="rm-ayuda">
                {{ $lote->empresa }}<br>{{ $lote->trabajadores }} trabajadores · {{ $lote->delegacion }} · Neto total {{ $mon($lote->monto_total) }}
            </div>
            <a class="btn btn-rm ms-auto" href="{{ $acuse }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf me-1"></i> Descargar el acuse</a>
        </div>

        <div class="table-responsive">
            <table class="table table-sm rm-tabla">
                <thead><tr><th>No.</th><th>Trabajador</th><th>Folio de cita</th><th>Día</th><th>Hora</th><th>Módulo</th></tr></thead>
                <tbody>
                    @foreach ($citas as $x)
                        <tr>
                            <td>{{ $x['trabajador']->numero }}</td>
                            <td>{{ $x['trabajador']->nombre_completo }}</td>
                            <td><code>{{ $x['cita']?->folio_cita }}</code></td>
                            <td>{{ $x['cita'] ? ucfirst($x['cita']->fecha->locale('es')->isoFormat('ddd D MMM YYYY')) : '—' }}</td>
                            <td>{{ $x['cita']?->hora?->format('H:i') }}</td>
                            <td>{{ $x['cita']?->lugar_auxiliar }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="alert alert-light border mt-3 mb-0 rm-ayuda" style="font-size: 14px;">
            Cada trabajador debe presentarse en su día y hora con identificación oficial vigente, junto con la parte patronal o su representante legal.
            Cada convenio se revisa y se atiende por separado; se elabora un convenio y un acta por trabajador.
        </div>
    </div>
</div>
@endsection
