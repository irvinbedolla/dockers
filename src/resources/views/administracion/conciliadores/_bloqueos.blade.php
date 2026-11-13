{{--
    Días bloqueados de un conciliador (dias_inhabiles con su user_id, los mismos
    que se capturan en Administración → Sedes). Esos días el reparto no le da
    audiencias y "Traspasar audiencias" lo marca como bloqueado.
    Espera $conciliador (User) y $susBloqueos (Collection de DiasInhabiles, de hoy en adelante).
    El botón abre el diálogo de _bloqueo_modal (incluirlo una vez en la página).
--}}
@php use App\Http\Controllers\ConciliadoresController as CC; @endphp
<div class="cn-bloqueos">
    @foreach ($susBloqueos as $b)
        <form method="POST" action="{{ route('conciliadores.bloqueos.quitar', [$conciliador->id, $b->id]) }}" class="cn-bloq {{ CC::bloqueoHoy($b) ? 'is-hoy' : '' }}"
              title="{{ CC::bloqueoTexto($b, true) }}" data-cn-quitar>
            @csrf
            @method('DELETE')
            <span><i class="bi bi-calendar-x"></i> {{ CC::bloqueoTexto($b) }}</span>
            <button type="submit" aria-label="Quitar el bloqueo {{ CC::bloqueoTexto($b) }}" title="Quitar este bloqueo">&times;</button>
        </form>
    @endforeach
    <button type="button" class="btn btn-sm btn-link p-0 cn-bloq-abrir" data-cn-bloquear
            data-action="{{ route('conciliadores.bloqueos.guardar', $conciliador->id) }}" data-nombre="{{ $conciliador->name }}">
        <i class="bi bi-calendar-plus"></i> Bloquear días
    </button>
</div>
