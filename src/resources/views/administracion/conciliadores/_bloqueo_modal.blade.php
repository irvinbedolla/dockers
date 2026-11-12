{{--
    Diálogo "Bloquear días" de un conciliador (ConciliadoresController@bloquear).
    Lo abre cualquier botón [data-cn-bloquear] con data-action y data-nombre.
--}}
@php use App\Http\Controllers\ConciliadoresController as CC; @endphp
{{-- Al final del <body> (layouts.app @stack('modals')): dentro del contenido el fondo oscuro lo tapaba. --}}
@push('modals')
<div class="modal fade tn" id="cnBloqueoModal" tabindex="-1" aria-labelledby="cnBloqueoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="#" id="cnBloqueoForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="cnBloqueoTitulo"><i class="bi bi-calendar-x me-1"></i> Bloquear días · <span data-nombre></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="cnBloqDesde" class="form-label small fw-semibold">Desde</label>
                        <input type="date" class="form-control" id="cnBloqDesde" name="desde" min="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="cnBloqHasta" class="form-label small fw-semibold">Hasta <small class="fw-normal">(opcional)</small></label>
                        <input type="date" class="form-control" id="cnBloqHasta" name="hasta" min="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-12">
                        <label for="cnBloqTipo" class="form-label small fw-semibold">Qué se bloquea</label>
                        <select class="form-select" id="cnBloqTipo" name="tipo">
                            @foreach (CC::TIPOS_BLOQUEO as $valor => $texto)
                                <option value="{{ $valor }}">{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="cnBloqTodo" name="todo_el_dia" value="1" checked>
                            <label class="form-check-label small fw-semibold" for="cnBloqTodo">Todo el día</label>
                        </div>
                    </div>
                    <div class="col-sm-6" data-cn-horas hidden>
                        <label for="cnBloqHi" class="form-label small fw-semibold">De</label>
                        <input type="time" class="form-control" id="cnBloqHi" name="hora_inicio" value="08:00">
                    </div>
                    <div class="col-sm-6" data-cn-horas hidden>
                        <label for="cnBloqHf" class="form-label small fw-semibold">A</label>
                        <input type="time" class="form-control" id="cnBloqHf" name="hora_final" value="12:00">
                    </div>
                </div>
                <p class="small mt-3 mb-0" style="color: var(--tn-tenue);">
                    Sin "Hasta" se bloquea sólo ese día. Esos días (u horas) el reparto no le da audiencias nuevas.
                    Las que ya tiene agendadas no se mueven solas: se avisa cuántas son para pasarlas con "Traspasar audiencias".
                </p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-link text-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn tn-boton-dorado" id="cnBloqEnviar"><i class="bi bi-calendar-x me-1"></i> Bloquear</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('cnBloqueoModal');
        var form = document.getElementById('cnBloqueoForm');
        if (!modal || !form || typeof bootstrap === 'undefined') { return; }
        var dlg = bootstrap.Modal.getOrCreateInstance(modal);
        var desde = form.querySelector('[name=desde]'), hasta = form.querySelector('[name=hasta]');
        var todo = document.getElementById('cnBloqTodo');

        document.addEventListener('click', function (e) {
            var b = e.target.closest('[data-cn-bloquear]');
            if (!b) { return; }
            form.action = b.dataset.action;
            form.querySelector('[data-nombre]').textContent = b.dataset.nombre;
            form.reset();
            horas();
            delete form.dataset.enviando;
            var enviar = document.getElementById('cnBloqEnviar');
            enviar.disabled = false;
            enviar.innerHTML = '<i class="bi bi-calendar-x me-1"></i> Bloquear';
            dlg.show();
        });
        modal.addEventListener('shown.bs.modal', function () { desde.focus(); });

        function horas() {
            form.querySelectorAll('[data-cn-horas]').forEach(function (el) { el.hidden = todo.checked; });
            form.querySelectorAll('[data-cn-horas] input').forEach(function (i) { i.required = !todo.checked; });
        }
        todo.addEventListener('change', horas);
        desde.addEventListener('change', function () {
            hasta.min = desde.value;
            if (hasta.value && hasta.value < desde.value) { hasta.value = desde.value; }
        });

        // Un solo envío: loader en el botón hasta que recarga la página.
        form.addEventListener('submit', function (e) {
            if (form.dataset.enviando) { e.preventDefault(); return; }
            form.dataset.enviando = '1';
            var enviar = document.getElementById('cnBloqEnviar');
            enviar.disabled = true;
            enviar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Bloqueando…';
        });
        document.addEventListener('submit', function (e) {
            var f = e.target;
            if (!f.matches('[data-cn-quitar]')) { return; }
            if (f.dataset.enviando || !confirm('¿Quitar este bloqueo? Esos días podrá volver a recibir audiencias.')) { e.preventDefault(); return; }
            f.dataset.enviando = '1';
            var b = f.querySelector('button');
            b.disabled = true;
            b.innerHTML = '<span class="spinner-border cn-bloq__spin" role="status" aria-hidden="true"></span>';
        });
    });
</script>
@endpush
