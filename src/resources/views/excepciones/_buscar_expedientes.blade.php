{{--
    "Buscar expedientes" de Casos de excepción: todas las solicitudes ya
    registradas (App\Support\BuscadorExpedientes), con filtro de caso de
    excepción, sede y fechas. Los resultados llegan por JSON
    (excepciones.expedientes). "Ver" muestra la solicitud completa en el
    mismo modal, sólo consulta (excepciones.expediente → solicitudes/_vista,
    lo mismo que "Ver solicitud" en el Seguimiento).
--}}
@push('modals')
<div class="modal fade tn" id="cxExpModal" tabindex="-1" aria-labelledby="cxExpTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cxExpTitulo"><i class="bi bi-search me-1"></i> Buscar expedientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            {{-- La solicitud completa en modo consulta (solicitudes/_vista, igual que en Seguimiento). Se regresa a la búsqueda sin perderla. --}}
            <div class="modal-body cx-exp-detalle" id="cxExpDetalle" hidden>
                <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="cxExpVolver"><i class="bi bi-arrow-left"></i> Volver a los resultados</button>
                <div id="cxExpSolicitud"></div>
            </div>
            <div class="modal-body" id="cxExpLista">
                <form class="cx-filtros cx-exp-filtros mb-3" id="cxExpForm" autocomplete="off" onsubmit="return false">
                    <div class="cx-exp-q">
                        <label for="cxExpQ">Buscar</label>
                        <input type="search" class="form-control" id="cxExpQ" name="q" maxlength="80"
                               placeholder="Expediente, nombre o CURP del solicitante, citado o folio de cita">
                    </div>
                    <div>
                        <label for="cxExpCaso">Casos</label>
                        <select class="form-select" id="cxExpCaso" name="caso">
                            @foreach (\App\Support\BuscadorExpedientes::CASOS as $valor => $texto)
                                <option value="{{ $valor }}">{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="cxExpSede">Sede</label>
                        @php $sedesExp = \App\Support\BuscadorExpedientes::sedes(auth()->user()); @endphp
                        <select class="form-select" id="cxExpSede" name="sede">
                            <option value="">{{ count($sedesExp) === count(\App\Support\CasosExcepcion::SEDES) ? 'Todas' : 'Mis sedes ('.implode(' y ', $sedesExp).')' }}</option>
                            @foreach ($sedesExp as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="cxExpDesde">Desde</label>
                        <input type="date" class="form-control" id="cxExpDesde" name="desde">
                    </div>
                    <div>
                        <label for="cxExpHasta">Hasta</label>
                        <input type="date" class="form-control" id="cxExpHasta" name="hasta">
                    </div>
                    <button type="button" class="btn btn-outline-secondary" id="cxExpLimpiar">Limpiar</button>
                </form>

                <div class="cx-cabeza">
                    <span id="cxExpTotal" aria-live="polite"></span>
                    <span id="cxExpCargando" hidden><span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Buscando…</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover cx-tabla mb-0">
                        <thead>
                            <tr><th>Expediente</th><th>Fecha</th><th>Sede</th><th>Solicitante</th><th>Citado</th><th>Estatus</th><th class="text-end"></th></tr>
                        </thead>
                        <tbody id="cxExpFilas"></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <span class="small" style="color: var(--tn-tenue);">"Ver" abre la solicitud completa, sólo para consulta.</span>
                    <div class="btn-group" role="group" aria-label="Páginas">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="cxExpAnt"><i class="bi bi-chevron-left"></i> Anterior</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="cxExpSig">Siguiente <i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('body_end')
<style>
    .cx-exp-filtros .cx-exp-q { flex: 1 1 280px; }
    .cx-exp-filtros .form-control, .cx-exp-filtros .form-select { min-width: 140px; }
    .cx-exp-caso { display: table; margin-top: 3px; padding: 1px 8px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #F6E4EA; color: #8C1D40; white-space: nowrap; }
    button.cx-pestana { font-family: inherit; }
    .cx-exp-detalle { background: #F5F7F7; }
    .cx-exp-consulta { display: inline-flex; align-items: center; gap: 4px; margin-left: 8px; padding: 2px 9px; border-radius: 999px; background: #EEF2F2; color: #5E6E6F; font-size: 11px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; vertical-align: middle; }
</style>
<script>
(function () {
    var modal = document.getElementById('cxExpModal');
    if (!modal) return;
    var URL = @json(route('excepciones.expedientes'));
    var form = document.getElementById('cxExpForm');
    var filas = document.getElementById('cxExpFilas');
    var total = document.getElementById('cxExpTotal');
    var cargando = document.getElementById('cxExpCargando');
    var ant = document.getElementById('cxExpAnt');
    var sig = document.getElementById('cxExpSig');
    var pagina = 1, ultima = 1, pedido = 0, espera = null, cargado = false;

    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    function vacio(texto) {
        filas.innerHTML = '<tr><td colspan="7" class="cx-vacio"><i class="bi bi-inbox" aria-hidden="true"></i>' + esc(texto) + '</td></tr>';
    }

    function pintar(d) {
        if (!d.filas.length) {
            vacio('No hay expedientes con esos filtros.');
        } else {
            filas.innerHTML = d.filas.map(function (f) {
                return '<tr>' +
                    '<td class="cx-nombre" style="white-space:nowrap">' + esc(f.nue) +
                        (f.folio ? '<span class="cx-sub">Cita ' + esc(f.folio) + '</span>' : '') +
                        (f.caso ? '<span class="cx-exp-caso">Caso de excepción</span>' : '') + '</td>' +
                    '<td style="white-space:nowrap">' + esc(f.fecha) + '</td>' +
                    '<td>' + esc(f.sede) + '</td>' +
                    '<td>' + esc(f.solicitante) + (f.solicitantes > 1 ? '<span class="cx-sub">y ' + (f.solicitantes - 1) + ' más</span>' : '') + '</td>' +
                    '<td>' + esc(f.citado) + (f.citados > 1 ? '<span class="cx-sub">y ' + (f.citados - 1) + ' más</span>' : '') + '</td>' +
                    '<td>' + esc(f.estatus) + '</td>' +
                    '<td class="text-end">' + (f.vista
                        ? '<button type="button" class="btn btn-sm btn-outline-secondary cx-exp-ver" data-url="' + esc(f.vista) + '" data-nue="' + esc(f.nue) + '"><i class="bi bi-eye"></i> Ver</button>'
                        : '') + '</td>' +
                '</tr>';
            }).join('');
        }
        pagina = d.pagina; ultima = d.ultima;
        total.textContent = d.total
            ? d.desde.toLocaleString('es-MX') + '–' + d.hasta.toLocaleString('es-MX') + ' de ' + d.total.toLocaleString('es-MX') + (d.total === 1 ? ' expediente' : ' expedientes')
            : '';
        ant.disabled = pagina <= 1;
        sig.disabled = pagina >= ultima;
    }

    function buscar(p) {
        var datos = new URLSearchParams(new FormData(form));
        datos.set('page', p || 1);
        var este = ++pedido;
        // El indicador sólo si tarda.
        var aviso = setTimeout(function () { cargando.hidden = false; }, 300);
        fetch(URL + '?' + datos.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) {
                if (r.status === 422) { return r.json().then(function (j) { throw new Error(Object.values(j.errors || {})[0] || 'Revisa los filtros.'); }); }
                if (!r.ok) { throw new Error(r.status === 419 || r.status === 401 ? 'La sesión expiró; recarga la página.' : 'No se pudo buscar (' + r.status + ').'); }
                return r.json();
            })
            .then(function (d) { if (este === pedido) { pintar(d); } })
            .catch(function (e) { if (este === pedido) { vacio(e.message); total.textContent = ''; } })
            .finally(function () { if (este === pedido) { clearTimeout(aviso); cargando.hidden = true; } });
    }

    function enseguida() { clearTimeout(espera); buscar(1); }

    form.querySelectorAll('select, input[type="date"]').forEach(function (el) { el.addEventListener('change', enseguida); });
    document.getElementById('cxExpQ').addEventListener('input', function () {
        clearTimeout(espera);
        espera = setTimeout(function () { buscar(1); }, 350);
    });
    document.getElementById('cxExpLimpiar').addEventListener('click', function () { form.reset(); enseguida(); });
    ant.addEventListener('click', function () { if (pagina > 1) buscar(pagina - 1); });
    sig.addEventListener('click', function () { if (pagina < ultima) buscar(pagina + 1); });

    // "Ver": la solicitud completa en el mismo modal, sólo consulta.
    var lista = document.getElementById('cxExpLista');
    var detalle = document.getElementById('cxExpDetalle');
    var cuerpo = document.getElementById('cxExpSolicitud');
    var titulo = document.getElementById('cxExpTitulo');
    var tituloLista = titulo.innerHTML;
    var verPedido = 0;

    function mostrarLista() {
        verPedido++;
        detalle.hidden = true; lista.hidden = false;
        titulo.innerHTML = tituloLista;
        cuerpo.innerHTML = '';
    }

    filas.addEventListener('click', function (e) {
        var b = e.target.closest('.cx-exp-ver');
        if (!b) return;
        var este = ++verPedido;
        lista.hidden = true; detalle.hidden = false;
        titulo.innerHTML = 'Solicitud ' + esc(b.dataset.nue) + ' <span class="cx-exp-consulta"><i class="bi bi-eye"></i> Sólo consulta</span>';
        cuerpo.innerHTML = '<div class="text-center py-5" style="color:#8A9899;"><div class="spinner-border spinner-border-sm me-2"></div> Cargando la solicitud…</div>';
        detalle.scrollTop = 0;
        fetch(b.dataset.url, { headers: { 'Accept': 'text/html' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.text(); })
            .then(function (html) { if (este === verPedido) { cuerpo.innerHTML = html; } })
            .catch(function () {
                if (este === verPedido) { cuerpo.innerHTML = '<div class="alert alert-warning mb-0">No se pudo cargar la solicitud. Intenta de nuevo.</div>'; }
            });
    });
    document.getElementById('cxExpVolver').addEventListener('click', mostrarLista);

    // Primera vez que se abre: lo más reciente. Al cerrar, regresa a la lista.
    modal.addEventListener('shown.bs.modal', function () {
        if (lista.hidden) return;
        document.getElementById('cxExpQ').focus();
        if (!cargado) { cargado = true; buscar(1); }
    });
    modal.addEventListener('hidden.bs.modal', mostrarLista);
})();
</script>
@endpush
