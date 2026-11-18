@extends('ratificacion_multiple._layout')
@section('titulo', 'Revisión de tu formato')
{{--
    Resultado de RatificacionMultipleController::revisar: qué encontró la
    revisión del Excel renglón por renglón y, si todo está bien, la agenda
    propuesta para confirmar.
--}}
@php
    use App\Support\RatificacionMultiple as RM;
    $mon = fn ($v) => '$'.number_format((float) $v, 2);
    $ts = $leido['trabajadores'];
    $conError = count(array_filter($ts, fn ($t) => $t['errores']));
    $conAviso = count(array_filter($ts, fn ($t) => ! $t['errores'] && $t['avisos']));
    $empresa = trim(implode(' ', array_filter([$poder->nombres_patronal, $poder->primer_apellido_patronal, $poder->segundo_apellido_patronal])));
    $diaLargo = fn ($f) => ucfirst(\Illuminate\Support\Carbon::parse($f)->locale('es')->isoFormat('dddd D [de] MMMM'));
@endphp

@section('contenido')
<div class="rm-card">
    <div class="rm-card__cab">
        <h1><i class="bi bi-clipboard-check me-2"></i>Revisión de tu solicitud</h1>
        <p>{{ $empresa }} · Folio de representación {{ $poder->idAbogado }} · {{ $datos['delegacion'] }} · {{ $motivos[$datos['motivo']] }}</p>
    </div>
    <div class="p-4">
        @if (! $valido)
            <div class="alert alert-danger">
                <h2 class="fs-6 fw-bold mb-1"><i class="bi bi-x-octagon-fill me-1"></i> Tu formato tiene cosas por corregir</h2>
                <p class="mb-1">Todavía no se agenda nada. Corrige en tu Excel lo que se marca abajo y vuelve a subirlo.</p>
                @if ($leido['errores'])
                    <ul class="mb-0">@foreach ($leido['errores'] as $e)<li>{{ $e }}</li>@endforeach</ul>
                @endif
            </div>
        @elseif (! $alcanza)
            <div class="alert alert-warning">
                <h2 class="fs-6 fw-bold mb-1"><i class="bi bi-calendar-x me-1"></i> No hay lugar suficiente en esas fechas</h2>
                Tu formato está correcto, pero a partir del {{ $diaLargo($datos['fecha_propuesta']) }} sólo hay lugar para
                {{ $agenda ? count($agenda['citas']) : 0 }} de los {{ count($ts) }} trabajadores en {{ RM::MAXIMO_DIAS }} días hábiles.
                Elige otra fecha propuesta y vuelve a subir el formato.
            </div>
        @else
            <div class="alert alert-success">
                <h2 class="fs-6 fw-bold mb-1"><i class="bi bi-check-circle-fill me-1"></i> Tu formato está correcto</h2>
                Revisa la relación y las citas propuestas. Al confirmar se agendan y te enviamos el acuse a <b>{{ $datos['correo'] }}</b>.
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="rm-chip rm-chip--ok">{{ count($ts) }} trabajadores</span>
            @if ($conError)<span class="rm-chip rm-chip--error">{{ $conError }} con errores</span>@endif
            @if ($conAviso)<span class="rm-chip rm-chip--aviso">{{ $conAviso }} con avisos</span>@endif
            <span class="rm-chip" style="background:#EEF2F2;color:#2E3C3D;">Neto total {{ $mon(array_sum(array_column($ts, 'neto'))) }}</span>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-sm rm-tabla align-middle">
                <thead>
                    <tr>
                        <th>No.</th><th>Trabajador</th><th>Puesto</th><th class="rm-num">Salario diario</th><th>Periodo</th>
                        <th class="rm-num">Total</th><th class="rm-num">Impuesto</th><th class="rm-num">Neto</th><th>Revisión</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ts as $t)
                        <tr class="{{ $t['errores'] ? 'rm-error' : ($t['avisos'] ? 'rm-aviso' : '') }}">
                            <td>{{ $t['numero'] }}</td>
                            <td><b>{{ $t['nombre_completo'] ?: '—' }}</b></td>
                            <td>{{ $t['puesto'] ?: '—' }}</td>
                            <td class="rm-num">{{ $t['salario_diario'] !== null ? $mon($t['salario_diario']) : '—' }}</td>
                            <td style="white-space:nowrap;">{{ $t['fecha_ingreso'] ? \Illuminate\Support\Carbon::parse($t['fecha_ingreso'])->format('d/m/Y') : '—' }} – {{ $t['fecha_termino'] ? \Illuminate\Support\Carbon::parse($t['fecha_termino'])->format('d/m/Y') : '—' }}</td>
                            <td class="rm-num">{{ $mon($t['total']) }}</td>
                            <td class="rm-num">{{ $mon($t['impuesto']) }}</td>
                            <td class="rm-num"><b>{{ $mon($t['neto']) }}</b></td>
                            <td style="min-width: 260px;">
                                @if (! $t['errores'] && ! $t['avisos'])
                                    <span class="rm-chip rm-chip--ok"><i class="bi bi-check"></i> Correcto</span>
                                @endif
                                @foreach ($t['errores'] as $e)<div class="text-danger small"><i class="bi bi-x-circle-fill"></i> {{ $e }}</div>@endforeach
                                @foreach ($t['avisos'] as $a)<div class="small" style="color:#7A5D10;"><i class="bi bi-exclamation-triangle-fill"></i> {{ $a }}</div>@endforeach
                                @if ($t['minimos'] && ! $t['errores'])
                                    <div class="small text-muted mt-1">Mínimos: vacaciones {{ $mon($t['minimos']['vacaciones']) }} · prima {{ $mon($t['minimos']['prima_vacacional']) }} · aguinaldo {{ $mon($t['minimos']['aguinaldo']) }}</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($valido && $alcanza)
            <h2 class="fs-6 fw-bold mb-2" style="color: var(--rm-verde);"><i class="bi bi-calendar-week me-1"></i> Citas propuestas</h2>
            <p class="rm-ayuda">Cada trabajador tiene una cita de {{ RM::MINUTOS_CITA }} minutos. A la misma hora pueden ir hasta 3, con auxiliares distintos.</p>
            @php($porDia = collect($agenda['citas'])->map(fn ($c, $i) => $c + ['t' => $ts[$i]])->groupBy('fecha'))
            <div class="row g-3 mb-4">
                @foreach ($porDia as $fecha => $citas)
                    <div class="col-md-4">
                        <div class="rm-dia">
                            <h3>{{ $diaLargo($fecha) }}</h3>
                            <div class="rm-ayuda mb-1">{{ $citas->count() }} {{ $citas->count() === 1 ? 'trabajador' : 'trabajadores' }}</div>
                            @foreach ($citas->groupBy('hora') as $hora => $aLaHora)
                                <div class="small"><b>{{ substr($hora, 0, 5) }}</b> · {{ $aLaHora->map(fn ($c) => $c['t']['nombre_completo'])->implode(', ') }}</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('ratificacion_multiple.confirmar') }}" id="rmConfirmar" class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <a href="{{ route('ratificacion_multiple') }}" class="btn btn-link px-0"><i class="bi bi-arrow-left"></i> Empezar de nuevo</a>
                <button type="submit" class="btn btn-rm-oro btn-lg">
                    <span class="rm-boton-texto"><i class="bi bi-calendar-check me-1"></i> Confirmar y agendar {{ count($ts) }} citas</span>
                    <span class="rm-cargando"><span class="spinner-border spinner-border-sm me-1"></span> Agendando…</span>
                </button>
            </form>
        @else
            <div class="d-flex justify-content-between">
                <a href="{{ route('ratificacion_multiple') }}" class="btn btn-rm"><i class="bi bi-arrow-repeat me-1"></i> Corregir y volver a subir</a>
                <a href="{{ route('ratificacion_multiple.formato') }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-excel me-1"></i> Descargar el formato</a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var form = document.getElementById('rmConfirmar');
    if (!form) return;
    var listo = false;
    form.addEventListener('submit', function (e) {
        if (listo) return;
        e.preventDefault();
        Swal.fire({
            icon: 'question', title: '¿Agendar las {{ count($ts) }} citas?',
            text: 'Se registra la solicitud con su folio y te enviamos el acuse por correo.',
            showCancelButton: true, confirmButtonText: 'Sí, agendar', cancelButtonText: 'Revisar otra vez',
            confirmButtonColor: '#496163', reverseButtons: true
        }).then(function (r) {
            if (!r.isConfirmed) return;
            listo = true;
            form.classList.add('is-enviando');
            form.querySelector('button[type=submit]').disabled = true;
            form.submit();
        });
    });
})();
</script>
@endsection
