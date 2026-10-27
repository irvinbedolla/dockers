{{--
    Revisar audiencia: encabezado del caso con las acciones siempre a la vista
    (se queda fijo arriba al bajar; ver el script de revisar_audiencia).
    Espera $revision (App\Support\RevisionAudiencia::armar) y lo que usa
    _revisar_acciones.
--}}
@php
    $r = $revision['resumen'];
    $a = $r['audiencia'];
@endphp
<div class="ra-hueco"></div>
<div class="ra-barra" id="raBarra">
    <div class="ra-barra__caso">
        <div class="ra-barra__titulo">
            <b>{{ $r['nue'] }}</b>
            <span class="ra-chip">{{ $r['tipo'] }}</span>
            <span class="ra-chip ra-chip--caso" title="Estatus de la solicitud">Caso: {{ $r['estatusCaso'] }}</span>
            @unless ($edicionAbierta)
                <span class="ra-chip ra-chip--cerrado" title="El caso ya concluyó: los datos se ven, pero no se guardan desde aquí."><i class="bi bi-lock-fill"></i> Sólo lectura</span>
            @endunless
        </div>
        @if ($a)
            <div class="ra-barra__datos">
                <span><i class="bi bi-calendar-event"></i> {{ $a['fecha'] }}@if ($a['esHoy']) <b>(hoy)</b>@endif · {{ $a['hora'] }} h</span>
                <span><i class="bi bi-door-open"></i> {{ $a['sala'] }}</span>
                <span><i class="bi bi-person-badge"></i> {{ $a['conciliador'] }}</span>
                <span><i class="bi bi-hash"></i> Audiencia {{ $a['folio'] }}</span>
                <span class="ra-chip ra-chip--audiencia">{{ $a['estatus'] }}</span>
            </div>
        @endif
    </div>
    @include('audiencias._revisar_acciones')
</div>
