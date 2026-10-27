{{--
    Botones de Revisar audiencia (antes, en la pestaña "Acciones"). Están
    dentro del formulario: los submit mandan toquen=1 (guardar) o
    toquen=iniciar_audiencia, igual que antes.
    "Editar finalización" ahora abre la audiencia que se está revisando
    (antes tomaba la última del historial, que era la más antigua).
--}}
@php
    $citadosCount = isset($citados) && is_countable($citados) ? count($citados) : 0;
    $regresarA = $isAudiencia == 'Si' ? 'todas_audiencias' : 'todas_solicitudes';
    $yo = auth()->user();
    $guardar = false;
    if ($isAudiencia == 'No' && $general->estatus == 'Pendiente') {
        $guardar = true;
    } elseif ($fecha_actual->isSameDay($general->fecha_confirmacion) && $yo->hasAnyRole(['Auxiliar', 'Orientador'])) {
        $guardar = true;
    } elseif ($isAudiencia == 'Si' && $yo->hasRole('Conciliador')) {
        $guardar = true;
    } elseif ($yo->hasAnyRole(['Enlace', 'Super Usuario', 'Orientador'])) {
        $guardar = true;
    }
    $iniciar = $isAudiencia == 'Si' && $ultimaEstatus == 'Pendiente' && $yo->hasRole('Conciliador');
    $editarFinal = $isAudiencia == 'Si' && $audienciaCurrent
        && in_array($audienciaCurrent->estatus, ['No conciliacion', 'Conciliacion', 'Reinstalacion'], true)
        && $yo->hasAnyRole(['Enlace', 'Super Usuario', 'Conciliador'])
        && ($yo->hasRole('Super Usuario') || \Carbon\Carbon::today()->equalTo(\Carbon\Carbon::parse($general->fecha_terminacion)));
@endphp
<div class="ra-acciones">
    @can('audiencias_guardar_edicion')
        @if ($edicionAbierta)
            @if ($citadosCount > 0)
                @if ($guardar)
                    <button type="submit" class="btn btn-primary" name="toquen" value="1"><i class="bi bi-save"></i> Guardar edición</button>
                @endif
                @if ($iniciar)
                    <button type="submit" class="btn btn-success" name="toquen" value="iniciar_audiencia"><i class="bi bi-play-fill"></i> Guardar e iniciar audiencia</button>
                @endif
            @else
                <button type="button" class="btn btn-secondary" disabled title="Agregue al menos un citado para poder guardar.">Guardar edición</button>
            @endif
        @endif
        @if ($editarFinal)
            <a class="btn btn-outline-secondary" href="{{ route('edicion_audienciaConcluida', ['id' => $general->id, 'audiencia_id' => $audienciaCurrent->id]) }}" target="_blank">
                <i class="bi bi-pencil-square"></i> Editar finalización
            </a>
        @endif
    @endcan
    <a class="btn btn-outline-secondary" href="{{ route('cancelar_edicion', ['redirect_to' => $regresarA]) }}"><i class="bi bi-arrow-left"></i> Regresar</a>
</div>
@if ($citadosCount === 0 && $edicionAbierta)
    <div class="ra-nota-acciones">Debe agregar al menos un citado para poder guardar.</div>
@endif
