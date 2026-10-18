{{--
    Aviso de "solicitud con datos de la cita" (App\Support\PrellenadoCita)
    para las pantallas de la captura del trabajador. Espera $prellenado
    (PrellenadoCita::paraVista()) y $texto (qué se prellenó aquí).
--}}
<div class="sa-prellenado" role="status">
    <span class="sa-prellenado__icono"><i class="bi bi-magic" aria-hidden="true"></i></span>
    <div class="sa-prellenado__texto">
        <strong>Solicitud con datos de la cita {{ $prellenado['folio'] }}@if (! empty($prellenado['persona'])) · {{ $prellenado['persona'] }}@endif</strong>
        <span>{!! $texto !!}</span>
    </div>
    <form method="POST" action="{{ route('mi_modulo.descartar') }}" class="m-0 ms-auto">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary">Descartar prellenado</button>
    </form>
</div>
