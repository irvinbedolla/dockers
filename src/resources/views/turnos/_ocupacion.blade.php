{{--
    Turnos → Todos los turnos → "Ocupación": calendario de cuántos turnos hay
    agendados (en línea y ventanilla) contra el cupo de Horarios de citas, por
    mes, semana o día. Marca el límite diario de citas en línea, los días
    inhábiles y aparte los casos de excepción. Datos: turnos.ocupacion
    (App\Support\OcupacionTurnos). Sólo lectura.

    Recibe $sedesPermitidas y $hoy de turnos/todos.
--}}
@push('modals')
<div class="modal fade tn" id="ocModal" tabindex="-1" aria-labelledby="ocTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ocTitulo"><i class="bi bi-calendar3 me-1"></i> Ocupación de la agenda de turnos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body oc">
                <div class="oc-barra-sup">
                    <div class="btn-group" role="group" aria-label="Vista">
                        <button type="button" class="btn btn-outline-secondary btn-sm is-activo" data-vista="mes">Mes</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-vista="semana">Semana</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-vista="dia">Día</button>
                    </div>
                    <div class="oc-nav">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-mover="-1" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-mover="0">Hoy</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-mover="1" aria-label="Siguiente"><i class="bi bi-chevron-right"></i></button>
                        <input type="date" class="form-control form-control-sm" id="ocFecha" aria-label="Ir a la fecha">
                    </div>
                    <h6 class="oc-periodo" id="ocPeriodo"></h6>
                </div>

                <div class="oc-filtros">
                    <select class="form-select form-select-sm" id="ocSede" aria-label="Sede">
                        @if (count($sedesPermitidas) > 1)
                            <option value="">{{ count($sedesPermitidas) >= count(\App\Support\Recepcion::TODAS_LAS_SEDES) ? 'Todas las sedes' : 'Mis sedes ('.implode(' y ', $sedesPermitidas).')' }}</option>
                        @endif
                        @foreach ($sedesPermitidas as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm" id="ocGrupo" aria-label="Trámite">
                        <option value="">Todos los trámites</option>
                        @foreach (\App\Support\OcupacionTurnos::GRUPOS as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                    <label class="form-check form-switch m-0 oc-switch">
                        <input class="form-check-input" type="checkbox" id="ocExcepcion" checked>
                        <span>Casos de excepción</span>
                    </label>
                </div>

                <div class="oc-resumen" id="ocResumen"></div>

                <div class="oc-leyenda">
                    <span><i class="oc-c oc-c--bajo"></i>Con lugar</span>
                    <span><i class="oc-c oc-c--medio"></i>Se va llenando (60 %)</span>
                    <span><i class="oc-c oc-c--alto"></i>Casi lleno o lleno (90 %)</span>
                    <span><i class="oc-c oc-c--linea"></i>En línea</span>
                    <span><i class="oc-c oc-c--ventanilla"></i>Ventanilla</span>
                    <span><i class="oc-c oc-c--excepcion"></i>Caso de excepción</span>
                    <span><i class="oc-c oc-c--inhabil"></i>Inhábil</span>
                </div>

                <div class="oc-vista" id="ocVista" aria-live="polite"></div>
                <p class="oc-nota">El cupo sale de Administración → Horarios de citas (lugares por horario, sin las horas inhábiles). El <b>límite en línea</b> es el máximo de citas que se pueden agendar por internet al día; ventanilla no tiene límite. Los casos de excepción tienen su propio calendario y no gastan cupo. No se cuentan las citas expiradas.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .oc { --oc-bajo: #2F9E5B; --oc-medio: #CEA845; --oc-alto: #D92D20; --oc-linea: #3B78DB; --oc-ventanilla: #496163; --oc-exc: #C2185B; }
    .oc-barra-sup { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 10px; }
    .oc-barra-sup .btn-group .btn.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
    .oc-nav { display: flex; gap: 6px; align-items: center; }
    .oc-nav .form-control { width: 150px; }
    .oc-periodo { margin: 0 0 0 auto; font-size: 17px; font-weight: 800; color: var(--tn-tinta); }
    .oc-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 12px; }
    .oc-filtros .form-select { width: auto; min-width: 180px; }
    .oc-switch { display: inline-flex; gap: 8px; align-items: center; font-size: 13px; font-weight: 600; }

    .oc-resumen { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; margin-bottom: 10px; }
    .oc-cifra { padding: 9px 12px; border-radius: 10px; background: var(--tn-fondo); }
    .oc-cifra b { display: block; font-size: 19px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
    .oc-cifra span { font-size: 11.5px; color: var(--tn-suave); }
    .oc-cifra--exc b { color: var(--oc-exc); }
    .oc-cifra--alerta b { color: var(--oc-alto); }

    .oc-leyenda { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 11.5px; color: var(--tn-suave); margin-bottom: 10px; }
    .oc-c { display: inline-block; width: 11px; height: 11px; border-radius: 3px; margin-right: 5px; vertical-align: -1px; }
    .oc-c--bajo { background: var(--oc-bajo); } .oc-c--medio { background: var(--oc-medio); } .oc-c--alto { background: var(--oc-alto); }
    .oc-c--linea { background: var(--oc-linea); } .oc-c--ventanilla { background: var(--oc-ventanilla); } .oc-c--excepcion { background: var(--oc-exc); }
    .oc-c--inhabil { background: repeating-linear-gradient(45deg, #D5DDDD 0 3px, #F3F5F5 3px 6px); }

    .oc-vista { position: relative; min-height: 260px; }
    .oc-cargando { position: absolute; inset: 0; display: grid; place-items: center; background: rgba(255,255,255,.7); z-index: 2; font-size: 13px; color: var(--tn-suave); border-radius: 12px; }
    .oc-nota { font-size: 11.5px; color: var(--tn-tenue); margin: 10px 0 0; }

    /* Mes */
    .oc-mes { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)) repeat(2, minmax(0, .55fr)); gap: 6px; }
    .oc-sem { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); text-align: center; padding-bottom: 2px; }
    .oc-dia { position: relative; min-height: 104px; padding: 7px 8px; border-radius: 10px; border: 1px solid var(--tn-borde); background: #fff; cursor: pointer; display: flex; flex-direction: column; gap: 4px; text-align: left; font: inherit; color: inherit; }
    .oc-dia:hover { border-color: var(--tn-verde-claro); box-shadow: 0 4px 12px rgba(46, 60, 61, .08); }
    .oc-dia.is-otro { opacity: .45; }
    .oc-dia.is-finde { background: #F8FAFA; min-height: 70px; }
    .oc-dia.is-hoy { box-shadow: inset 0 0 0 2px var(--tn-dorado); }
    .oc-dia.is-inhabil { background: repeating-linear-gradient(45deg, #EEF2F2 0 6px, #F8FAFA 6px 12px); }
    .oc-dia.n-bajo { background: #F1F9F4; } .oc-dia.n-medio { background: #FDF7E7; } .oc-dia.n-alto { background: #FDECEA; }
    .oc-dia__cab { display: flex; justify-content: space-between; align-items: center; gap: 4px; }
    .oc-dia__num { font-weight: 800; font-size: 14px; color: var(--tn-tinta); }
    .oc-pct { font-size: 11px; font-weight: 800; padding: 0 6px; border-radius: 999px; color: #fff; }
    .oc-pct.n-bajo { background: var(--oc-bajo); } .oc-pct.n-medio { background: var(--oc-medio); color: #3D2F05; } .oc-pct.n-alto { background: var(--oc-alto); }
    .oc-cuenta { font-size: 12px; color: var(--tn-suave); font-variant-numeric: tabular-nums; }
    .oc-cuenta b { font-size: 18px; color: var(--tn-tinta); }
    .oc-tubo { height: 8px; border-radius: 99px; background: #E7ECEC; overflow: hidden; display: flex; }
    .oc-tubo span { display: block; height: 100%; }
    .oc-tubo .l { background: var(--oc-linea); } .oc-tubo .v { background: var(--oc-ventanilla); } .oc-tubo .x { background: var(--oc-exc); }
    .oc-linea-lim { font-size: 10.5px; color: var(--tn-suave); }
    .oc-linea-lim.is-tope { color: var(--oc-alto); font-weight: 800; }
    .oc-exc { position: absolute; right: 7px; bottom: 6px; font-size: 10.5px; font-weight: 800; color: var(--oc-exc); background: #F6E4EA; border-radius: 999px; padding: 0 6px; }
    .oc-inhabil-txt { font-size: 11px; font-weight: 700; color: #6B7A7B; }

    /* Semana */
    .oc-tabla { width: 100%; border-collapse: separate; border-spacing: 4px; table-layout: fixed; }
    .oc-tabla th { font-size: 12px; font-weight: 800; color: var(--tn-tinta); text-align: center; padding: 4px; cursor: pointer; }
    .oc-tabla th small { display: block; font-weight: 600; color: var(--tn-tenue); }
    .oc-tabla th.oc-h { width: 62px; cursor: default; text-align: right; color: var(--tn-tenue); font-variant-numeric: tabular-nums; }
    .oc-celda { height: 38px; border-radius: 8px; text-align: center; font-size: 12.5px; font-weight: 700; color: var(--tn-tinta); background: #F3F5F5; position: relative; font-variant-numeric: tabular-nums; }
    .oc-celda.n-vacio { background: #F1F9F4; color: #8A9899; font-weight: 600; }
    .oc-celda.n-bajo { background: #CDEBD8; } .oc-celda.n-medio { background: #F6E3A8; } .oc-celda.n-alto { background: #F6B8B2; } .oc-celda.n-lleno { background: var(--oc-alto); color: #fff; }
    .oc-celda.n-sin { background: transparent; color: #C4CDCD; font-weight: 400; }
    .oc-celda i { position: absolute; top: 3px; right: 4px; width: 8px; height: 8px; border-radius: 50%; background: var(--oc-exc); }

    /* Día */
    .oc-grupos { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 8px; margin-bottom: 12px; }
    .oc-grupo { padding: 10px 12px; border-radius: 10px; border: 1px solid var(--tn-borde); }
    .oc-grupo b { font-size: 14px; color: var(--tn-tinta); }
    .oc-grupo small { display: block; color: var(--tn-suave); font-size: 12px; }
    .oc-horas { display: grid; gap: 6px; }
    .oc-hora { display: grid; grid-template-columns: 56px minmax(0, 1fr) 150px; gap: 10px; align-items: center; }
    .oc-hora__t { font-weight: 800; color: var(--tn-tinta); font-variant-numeric: tabular-nums; text-align: right; }
    .oc-lugares { display: flex; gap: 4px; flex-wrap: wrap; }
    .oc-lugar { width: 28px; height: 22px; border-radius: 6px; background: #E7ECEC; border: 1px dashed #C9D3D3; }
    .oc-lugar.l { background: var(--oc-linea); border: 0; } .oc-lugar.v { background: var(--oc-ventanilla); border: 0; } .oc-lugar.x { background: var(--oc-exc); border: 0; }
    .oc-lugar.sobre { outline: 2px solid var(--oc-alto); outline-offset: 1px; }
    .oc-hora__txt { font-size: 12px; color: var(--tn-suave); }
    .oc-vacio { text-align: center; color: var(--tn-tenue); padding: 40px 12px; border: 1px dashed var(--tn-borde); border-radius: 12px; }

    @media (max-width: 720px) {
        .oc-periodo { margin-left: 0; width: 100%; }
        .oc-mes { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .oc-mes .oc-sem.f, .oc-mes .oc-dia.is-finde { display: none; }
        .oc-dia { min-height: 78px; padding: 5px; }
        .oc-cuenta b { font-size: 14px; }
        .oc-linea-lim { display: none; }
        .oc-hora { grid-template-columns: 46px minmax(0, 1fr); }
        .oc-hora__txt { grid-column: 2; }
    }
</style>

<script>
(function () {
    var modal = document.getElementById('ocModal');
    if (!modal) { return; }
    var URL = @json(route('turnos.ocupacion'));
    var HOY = @json($hoy);
    var MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    var DIAS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    var DIAS_LARGO = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    var GRUPOS = @json(\App\Support\OcupacionTurnos::GRUPOS);

    var estado = { vista: 'mes', fecha: HOY, datos: null, pedido: 0 };
    var el = function (id) { return document.getElementById(id); };

    function f(d) { return d.toISOString().slice(0, 10); }
    function fecha(s) { return new Date(s + 'T12:00:00Z'); }
    function sumar(s, n) { var d = fecha(s); d.setUTCDate(d.getUTCDate() + n); return f(d); }
    function lunes(s) { var d = fecha(s); var w = (d.getUTCDay() + 6) % 7; d.setUTCDate(d.getUTCDate() - w); return f(d); }
    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function nivel(total, cap) {
        if (!cap) { return total ? 'alto' : 'sin'; }
        var r = total / cap;
        if (total === 0) { return 'vacio'; }
        if (r >= 1) { return 'lleno'; }
        return r >= 0.9 ? 'alto' : (r >= 0.6 ? 'medio' : 'bajo');
    }
    function nivelDia(n) { return n === 'lleno' ? 'alto' : n; }

    function rango() {
        if (estado.vista === 'dia') { return [estado.fecha, estado.fecha]; }
        if (estado.vista === 'semana') { var l = lunes(estado.fecha); return [l, sumar(l, 6)]; }
        var d = fecha(estado.fecha), primero = f(new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), 1, 12)));
        var ultimo = f(new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + 1, 0, 12)));
        return [lunes(primero), sumar(lunes(ultimo), 6)];
    }

    function titulo() {
        var d = fecha(estado.fecha);
        if (estado.vista === 'mes') { return MESES[d.getUTCMonth()].replace(/^./, function (c) { return c.toUpperCase(); }) + ' ' + d.getUTCFullYear(); }
        if (estado.vista === 'semana') {
            var l = fecha(lunes(estado.fecha)), v = fecha(sumar(lunes(estado.fecha), 4));
            return 'Semana del ' + l.getUTCDate() + ' de ' + MESES[l.getUTCMonth()] + ' al ' + v.getUTCDate() + ' de ' + MESES[v.getUTCMonth()];
        }
        return DIAS_LARGO[d.getUTCDay()].replace(/^./, function (c) { return c.toUpperCase(); }) + ' ' + d.getUTCDate() + ' de ' + MESES[d.getUTCMonth()] + ' de ' + d.getUTCFullYear();
    }

    function cargar() {
        var r = rango();
        var q = new URLSearchParams({ desde: r[0], hasta: r[1] });
        if (el('ocSede').value) { q.set('sede', el('ocSede').value); }
        if (el('ocGrupo').value) { q.set('grupo', el('ocGrupo').value); }
        if (estado.vista !== 'mes') { q.set('horas', '1'); }
        var n = ++estado.pedido;
        el('ocPeriodo').textContent = titulo();
        el('ocFecha').value = estado.fecha;
        el('ocVista').insertAdjacentHTML('beforeend', '<div class="oc-cargando" id="ocCargando"><span><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cargando la agenda…</span></div>');
        fetch(URL + '?' + q.toString(), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
            .then(function (res) { if (!res.ok) { throw new Error(res.status); } return res.json(); })
            .then(function (j) { if (n !== estado.pedido) { return; } estado.datos = j; pintar(); })
            .catch(function () {
                if (n !== estado.pedido) { return; }
                el('ocVista').innerHTML = '<div class="oc-vacio">No se pudo cargar la agenda. Intenta de nuevo.</div>';
            });
    }

    function conExc() { return el('ocExcepcion').checked; }

    function pintarResumen(dias) {
        var s = { total: 0, capacidad: 0, linea: 0, ventanilla: 0, excepcion: 0, llenos: 0, tope: 0 };
        var r = rango(), mesActual = fecha(estado.fecha).getUTCMonth();
        dias.forEach(function (d) {
            if (estado.vista === 'mes' && fecha(d.fecha).getUTCMonth() !== mesActual) { return; }
            s.total += d.total; s.capacidad += d.capacidad; s.linea += d.linea; s.ventanilla += d.ventanilla; s.excepcion += d.excepcion;
            if (d.capacidad && d.total >= d.capacidad) { s.llenos++; }
            if (d.limiteAlcanzado) { s.tope++; }
        });
        var pct = s.capacidad ? Math.round(s.total / s.capacidad * 100) : 0;
        var c = function (v, t, cls) { return '<div class="oc-cifra ' + (cls || '') + '"><b>' + v + '</b><span>' + t + '</span></div>'; };
        el('ocResumen').innerHTML =
            c(s.total + ' / ' + s.capacidad, 'Agendados / cupo') +
            c(pct + ' %', 'Ocupación') +
            c(s.linea, 'En línea') +
            c(s.ventanilla, 'Ventanilla') +
            (conExc() ? c(s.excepcion, 'Casos de excepción', 'oc-cifra--exc') : '') +
            c(s.llenos, s.llenos === 1 ? 'Día lleno' : 'Días llenos', s.llenos ? 'oc-cifra--alerta' : '') +
            c(s.tope, 'Días con el límite en línea', s.tope ? 'oc-cifra--alerta' : '');
    }

    function tubo(d) {
        var cap = Math.max(d.capacidad, d.total + (conExc() ? d.excepcion : 0), 1);
        var w = function (n) { return (n / cap * 100).toFixed(1) + '%'; };
        return '<div class="oc-tubo"><span class="l" style="width:' + w(d.linea) + '"></span><span class="v" style="width:' + w(d.ventanilla) + '"></span>' +
            (conExc() ? '<span class="x" style="width:' + w(d.excepcion) + '"></span>' : '') + '</div>';
    }

    function pintarMes(dias) {
        var mesActual = fecha(estado.fecha).getUTCMonth();
        var html = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'].map(function (t, i) { return '<div class="oc-sem' + (i > 4 ? ' f' : '') + '">' + t + '</div>'; }).join('');
        dias.forEach(function (d) {
            var dt = fecha(d.fecha), finde = !d.habil;
            var n = nivelDia(nivel(d.total, d.capacidad));
            var clases = ['oc-dia'];
            if (dt.getUTCMonth() !== mesActual) { clases.push('is-otro'); }
            if (finde) { clases.push('is-finde'); }
            if (d.fecha === HOY) { clases.push('is-hoy'); }
            if (d.inhabil === 'total') { clases.push('is-inhabil'); } else if (d.capacidad && d.total) { clases.push('n-' + n); }
            var cuerpo = '';
            if (d.inhabil === 'total') {
                cuerpo = '<span class="oc-inhabil-txt">' + esc(d.motivoInhabil || 'Inhábil') + '</span>';
            } else if (finde && !d.total && !d.excepcion) {
                cuerpo = '';
            } else {
                cuerpo = '<div class="oc-cuenta"><b>' + d.total + '</b> / ' + d.capacidad + '</div>' + tubo(d) +
                    (d.limite !== null ? '<div class="oc-linea-lim' + (d.limiteAlcanzado ? ' is-tope' : '') + '">En línea ' + d.linea + ' / ' + d.limite + (d.limiteAlcanzado ? ' · límite' : '') + '</div>' : '') +
                    (d.inhabil === 'parcial' ? '<span class="oc-inhabil-txt">Inhábil parcial</span>' : '');
            }
            html += '<button type="button" class="' + clases.join(' ') + '" data-ir="' + d.fecha + '">' +
                '<div class="oc-dia__cab"><span class="oc-dia__num">' + dt.getUTCDate() + '</span>' +
                (d.porcentaje !== null && d.inhabil !== 'total' && (d.total || !finde) ? '<span class="oc-pct n-' + nivelDia(nivel(d.total, d.capacidad)) + '">' + d.porcentaje + '%</span>' : '') + '</div>' +
                cuerpo +
                (conExc() && d.excepcion ? '<span class="oc-exc" title="Casos de excepción">◆ ' + d.excepcion + '</span>' : '') +
                '</button>';
        });
        el('ocVista').innerHTML = '<div class="oc-mes">' + html + '</div>';
    }

    function pintarSemana(dias) {
        var visibles = dias.filter(function (d) { return d.habil || d.total || d.excepcion; });
        var horas = {};
        visibles.forEach(function (d) { (d.horas || []).forEach(function (h) { horas[h.hora] = true; }); });
        var lista = Object.keys(horas).sort();
        if (!lista.length) { el('ocVista').innerHTML = '<div class="oc-vacio">No hay horarios configurados en esta semana.</div>'; return; }
        var html = '<table class="oc-tabla"><thead><tr><th class="oc-h"></th>' + visibles.map(function (d) {
            var dt = fecha(d.fecha);
            return '<th data-ir="' + d.fecha + '"' + (d.fecha === HOY ? ' style="color:var(--tn-dorado)"' : '') + '>' + DIAS[dt.getUTCDay()] + ' ' + dt.getUTCDate() +
                '<small>' + (d.inhabil === 'total' ? 'Inhábil' : d.total + ' / ' + d.capacidad + (d.limite !== null ? ' · línea ' + d.linea + '/' + d.limite : '')) + '</small></th>';
        }).join('') + '</tr></thead><tbody>';
        lista.forEach(function (hora) {
            html += '<tr><th class="oc-h">' + hora + '</th>' + visibles.map(function (d) {
                var h = (d.horas || []).filter(function (x) { return x.hora === hora; })[0];
                if (!h) { return '<td class="oc-celda n-sin">·</td>'; }
                var tot = h.linea + h.ventanilla;
                var cls = h.capacidad ? nivel(tot, h.capacidad) : (tot ? 'alto' : 'sin');
                var txt = h.capacidad ? tot + '/' + h.capacidad : (tot ? tot : '—');
                return '<td class="oc-celda n-' + cls + '" data-ir="' + d.fecha + '" title="' + hora + ' · en línea ' + h.linea + ', ventanilla ' + h.ventanilla + (h.excepcion ? ', excepción ' + h.excepcion : '') + '">' + txt +
                    (conExc() && h.excepcion ? '<i></i>' : '') + '</td>';
            }).join('') + '</tr>';
        });
        el('ocVista').innerHTML = html + '</tbody></table>';
    }

    function pintarDia(d) {
        if (!d) { el('ocVista').innerHTML = '<div class="oc-vacio">Sin datos.</div>'; return; }
        var grupos = Object.keys(GRUPOS).filter(function (g) { return !el('ocGrupo').value || el('ocGrupo').value === g; }).map(function (g) {
            var x = d.grupos[g];
            return '<div class="oc-grupo"><b>' + esc(GRUPOS[g]) + '</b> · ' + x.total + ' de ' + x.capacidad +
                '<small>En línea ' + x.linea + (x.limite !== null ? ' de ' + x.limite + ' permitidas' : '') + ' · ventanilla ' + x.ventanilla +
                (x.limite !== null && x.linea >= x.limite ? ' · <b style="color:var(--oc-alto)">se alcanzó el límite en línea</b>' : '') + '</small></div>';
        }).join('');
        if (conExc()) { grupos += '<div class="oc-grupo" style="border-color:#F0C4D3"><b style="color:var(--oc-exc)">Casos de excepción</b> · ' + d.excepcion + '<small>En línea ' + d.excepcionLinea + ' · tienen su propio calendario</small></div>'; }

        var horas = (d.horas || []);
        var filas = horas.map(function (h) {
            var cajas = [], i;
            for (i = 0; i < h.linea; i++) { cajas.push('l'); }
            for (i = 0; i < h.ventanilla; i++) { cajas.push('v'); }
            if (conExc()) { for (i = 0; i < h.excepcion; i++) { cajas.push('x'); } }
            var ocupados = h.linea + h.ventanilla;
            var lugares = '';
            for (i = 0; i < Math.max(h.capacidad, cajas.length); i++) {
                var c = cajas[i] || '';
                lugares += '<span class="oc-lugar ' + c + (c && c !== 'x' && i >= h.capacidad ? ' sobre' : '') + '" title="' + (c === 'l' ? 'En línea' : c === 'v' ? 'Ventanilla' : c === 'x' ? 'Caso de excepción' : 'Lugar libre') + '"></span>';
            }
            var libres = Math.max(0, h.capacidad - ocupados);
            return '<div class="oc-hora"><span class="oc-hora__t">' + h.hora + '</span><div class="oc-lugares">' + (lugares || '<span class="oc-hora__txt">Sin lugares</span>') + '</div>' +
                '<span class="oc-hora__txt">' + (h.capacidad ? (libres ? libres + (libres === 1 ? ' libre' : ' libres') : '<b style="color:var(--oc-alto)">Lleno</b>') + ' de ' + h.capacidad : 'Fuera de horario') + '</span></div>';
        }).join('');

        var aviso = d.inhabil === 'total' ? '<div class="oc-vacio">Día inhábil: ' + esc(d.motivoInhabil || '') + '</div>'
            : (!d.habil && !horas.length ? '<div class="oc-vacio">Fin de semana: no hay horarios.</div>' : '');
        el('ocVista').innerHTML = '<div class="oc-grupos">' + grupos + '</div>' + (aviso || '<div class="oc-horas">' + (filas || '<div class="oc-vacio">No hay horarios configurados este día.</div>') + '</div>');
    }

    function pintar() {
        var dias = Object.values(estado.datos.dias || {});
        pintarResumen(dias);
        if (estado.vista === 'mes') { pintarMes(dias); }
        else if (estado.vista === 'semana') { pintarSemana(dias); }
        else { pintarDia(dias[0]); }
    }

    function vista(v) {
        estado.vista = v;
        modal.querySelectorAll('[data-vista]').forEach(function (b) { b.classList.toggle('is-activo', b.getAttribute('data-vista') === v); });
        cargar();
    }

    modal.querySelectorAll('[data-vista]').forEach(function (b) { b.addEventListener('click', function () { vista(b.getAttribute('data-vista')); }); });
    modal.querySelectorAll('[data-mover]').forEach(function (b) {
        b.addEventListener('click', function () {
            var m = parseInt(b.getAttribute('data-mover'), 10);
            if (m === 0) { estado.fecha = HOY; }
            else if (estado.vista === 'mes') { var d = fecha(estado.fecha); estado.fecha = f(new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + m, 1, 12))); }
            else if (estado.vista === 'semana') { estado.fecha = sumar(estado.fecha, 7 * m); }
            else { estado.fecha = sumar(estado.fecha, m); }
            cargar();
        });
    });
    el('ocFecha').addEventListener('change', function () { if (this.value) { estado.fecha = this.value; cargar(); } });
    el('ocSede').addEventListener('change', cargar);
    el('ocGrupo').addEventListener('change', cargar);
    el('ocExcepcion').addEventListener('change', function () { if (estado.datos) { pintar(); } });
    el('ocVista').addEventListener('click', function (e) {
        var t = e.target.closest('[data-ir]');
        if (!t) { return; }
        estado.fecha = t.getAttribute('data-ir');
        vista('dia');
    });
    modal.addEventListener('show.bs.modal', function () { if (!estado.datos) { cargar(); } });
})();
</script>
@endpush
