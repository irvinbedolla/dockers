// =============================================================================
// Agenda en celulares (≤ 767 px).
//
// La agenda de escritorio (FullCalendar, calendar.js) no se toca. En un
// teléfono no se crea FullCalendar: esta vista pide las mismas fuentes con los
// mismos filtros (sede, conciliador, pastilla activa) y las pinta así:
//
//   - arriba el mes en grande, con anterior / hoy / siguiente;
//   - una tira de días del mes que se desliza de lado (o, con el botón de
//     cuadrícula, el mes completo como vista panorámica);
//   - abajo las actividades del día elegido, en tarjetas. Al tocar una se abre
//     el mismo detalle que en escritorio (handleEventClick).
//
// Los días inhábiles salen rayados en la tira y en la cuadrícula, y con un
// aviso en el día. Los permisos por rol no cambian: las fuentes son las mismas
// URLs de siempre y el servidor sigue decidiendo qué ve cada quien.
//
// Depende de calendar.js (calFuentes, pastillaActiva, CAL_INHABILES,
// calInhabilDelDia, calFechaISO, CAL_MESES, CAL_DIAS, handleEventClick).
// =============================================================================
var CalMovil = (function () {
    'use strict';

    var LETRAS = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
    var CORTOS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    // Propiedades que FullCalendar reconoce; lo demás del JSON va a extendedProps.
    var ESTANDAR = ['id', 'groupId', 'allDay', 'start', 'end', 'title', 'url', 'classNames', 'className',
        'editable', 'display', 'color', 'backgroundColor', 'borderColor', 'textColor', 'extendedProps',
        'startTime', 'endTime', 'daysOfWeek', 'startRecur', 'endRecur', 'overlap', 'constraint'];

    var raiz = null;
    var mes = null;          // Date del día 1 del mes en pantalla
    var elegido = null;      // 'Y-m-d' del día elegido
    var panoramica = false;  // cuadrícula del mes en lugar de la tira
    var porDia = {};         // 'Y-m-d' -> [eventos]
    var cargando = false;
    var turno = 0;           // descarta respuestas viejas si se cambia de mes rápido

    function $(id) { return document.getElementById(id); }
    function hoyISO() { return calFechaISO(new Date()); }
    function fechaDe(iso) { var p = iso.split('-'); return new Date(+p[0], +p[1] - 1, +p[2], 12); }
    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // ------------------------------------------------------------ datos
    function rangoMes() {
        var fin = new Date(mes.getFullYear(), mes.getMonth() + 1, 0, 12);
        return { desde: calFechaISO(mes), hasta: calFechaISO(fin), finExclusivo: calFechaISO(new Date(fin.getTime() + 86400000)) };
    }

    function normalizar(ev, fuente) {
        var props = {};
        Object.keys(ev).forEach(function (k) { if (ESTANDAR.indexOf(k) === -1) { props[k] = ev[k]; } });
        Object.assign(props, ev.extendedProps || {});
        var inicio = String(ev.start || '');
        var dia = inicio.slice(0, 10);
        var conHora = inicio.length > 10 && !ev.allDay;
        return {
            dia: dia,
            orden: conHora ? inicio.slice(11, 16) : '00:00',
            fuente: fuente,
            // Lo que handleEventClick y el detalle esperan de un evento de FullCalendar.
            evento: {
                id: ev.id,
                title: ev.title || '',
                start: conHora ? new Date(inicio) : fechaDe(dia),
                end: ev.end ? new Date(ev.end) : null,
                allDay: !conHora,
                extendedProps: props,
                backgroundColor: ev.backgroundColor || ev.color || '',
                source: { id: fuente }
            }
        };
    }

    function cargar() {
        var r = rangoMes();
        var mio = ++turno;
        cargando = true;
        pintarLista();

        var sede = ($('filtro-sede') || {}).value || 'Todos';
        var pedidos = calFuentes(pastillaActiva).filter(function (f) { return f.url; }).map(function (f) {
            var url = f.url + (f.url.indexOf('?') === -1 ? '?' : '&') + 'start=' + r.desde + '&end=' + r.finExclusivo;
            return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (resp) { return resp.ok ? resp.json() : []; })
                .then(function (lista) { return (Array.isArray(lista) ? lista : []).map(function (ev) { return normalizar(ev, f.id); }); })
                .catch(function () { return []; });
        });

        // Días inhábiles del mes: el mismo mapa que usa escritorio (CAL_INHABILES).
        if (typeof urlInhabiles !== 'undefined') {
            pedidos.push(
                fetch(urlInhabiles + '?start=' + r.desde + '&end=' + r.hasta + '&sede=' + encodeURIComponent(sede), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (resp) { return resp.ok ? resp.json() : { dias: {} }; })
                    .then(function (d) { if (mio === turno) { CAL_INHABILES = d.dias || {}; calInhabilesRango = null; } return []; })
                    .catch(function () { if (mio === turno) { CAL_INHABILES = {}; } return []; })
            );
        }

        Promise.all(pedidos).then(function (partes) {
            if (mio !== turno) { return; }
            porDia = {};
            [].concat.apply([], partes).forEach(function (e) {
                if (!e.dia || e.dia < r.desde || e.dia > r.hasta) { return; }
                (porDia[e.dia] = porDia[e.dia] || []).push(e);
            });
            Object.keys(porDia).forEach(function (d) {
                porDia[d].sort(function (a, b) { return a.orden < b.orden ? -1 : (a.orden > b.orden ? 1 : 0); });
            });
            cargando = false;
            pintar();
        });
    }

    // ------------------------------------------------------------ pintar
    function pintar() {
        pintarCabeza();
        panoramica ? pintarRejilla() : pintarTira();
        pintarLista();
    }

    function pintarCabeza() {
        $('cmMes').textContent = CAL_MESES[mes.getMonth()].charAt(0).toUpperCase() + CAL_MESES[mes.getMonth()].slice(1) + ' ' + mes.getFullYear();
        var total = 0;
        Object.keys(porDia).forEach(function (d) { total += porDia[d].length; });
        $('cmSub').textContent = cargando ? 'Cargando…' : (total ? total + ' actividad' + (total === 1 ? '' : 'es') + ' en el mes' : 'Sin actividades en el mes');
        $('cmVista').setAttribute('aria-pressed', panoramica ? 'true' : 'false');
        $('cmVista').innerHTML = panoramica ? '<i class="bi bi-distribute-horizontal"></i>' : '<i class="bi bi-calendar3"></i>';
        $('cmVista').title = panoramica ? 'Ver días en tira' : 'Ver el mes completo';
        $('cmTira').hidden = panoramica;
        $('cmRejilla').hidden = !panoramica;
    }

    function claseDia(iso) {
        var c = [];
        var f = fechaDe(iso);
        if (iso === elegido) { c.push('is-elegido'); }
        if (iso === hoyISO()) { c.push('is-hoy'); }
        if (f.getDay() === 0 || f.getDay() === 6) { c.push('is-finde'); }
        var inhabil = calInhabilDelDia(f);
        if (inhabil) { c.push(inhabil.todas ? 'is-inhabil' : 'is-inhabil-parcial'); }
        return c.join(' ');
    }

    function puntos(iso) {
        var n = (porDia[iso] || []).length;
        if (!n) { return '<span class="cm-puntos"></span>'; }
        var colores = (porDia[iso] || []).slice(0, 3).map(function (e) {
            return '<i style="background:' + esc(e.evento.extendedProps.color || e.evento.backgroundColor || '#496163') + '"></i>';
        }).join('');
        return '<span class="cm-puntos">' + colores + (n > 3 ? '<b>+' + (n - 3) + '</b>' : '') + '</span>';
    }

    function pintarTira() {
        var tira = $('cmTira');
        var dias = new Date(mes.getFullYear(), mes.getMonth() + 1, 0).getDate();
        var html = '';
        for (var d = 1; d <= dias; d++) {
            var f = new Date(mes.getFullYear(), mes.getMonth(), d, 12);
            var iso = calFechaISO(f);
            html += '<button type="button" class="cm-chip ' + claseDia(iso) + '" data-dia="' + iso + '" role="option" aria-selected="' + (iso === elegido) + '"' +
                ' aria-label="' + CAL_DIAS[f.getDay()] + ' ' + d + ((porDia[iso] || []).length ? ', ' + porDia[iso].length + ' actividades' : '') + '">' +
                '<span class="cm-chip__letra">' + LETRAS[f.getDay()] + '</span>' +
                '<span class="cm-chip__num">' + d + '</span>' + puntos(iso) + '</button>';
        }
        tira.innerHTML = html;
        var activo = tira.querySelector('.is-elegido');
        if (activo) {
            // Centra el día elegido sin mover la página completa.
            var destino = activo.offsetLeft - (tira.clientWidth - activo.offsetWidth) / 2;
            tira.scrollTo({ left: Math.max(0, destino), behavior: tira.dataset.listo ? 'smooth' : 'auto' });
            tira.dataset.listo = '1';
        }
    }

    function pintarRejilla() {
        var caja = $('cmRejilla');
        var dias = new Date(mes.getFullYear(), mes.getMonth() + 1, 0).getDate();
        var hueco = (mes.getDay() + 6) % 7; // la semana empieza en lunes
        var html = '<div class="cm-rejilla__cab">' + ['L', 'M', 'M', 'J', 'V', 'S', 'D'].map(function (l) { return '<span>' + l + '</span>'; }).join('') + '</div><div class="cm-rejilla__dias">';
        for (var i = 0; i < hueco; i++) { html += '<span class="cm-celda is-vacia"></span>'; }
        for (var d = 1; d <= dias; d++) {
            var iso = calFechaISO(new Date(mes.getFullYear(), mes.getMonth(), d, 12));
            var n = (porDia[iso] || []).length;
            html += '<button type="button" class="cm-celda ' + claseDia(iso) + '" data-dia="' + iso + '">' +
                '<span class="cm-celda__num">' + d + '</span>' +
                (n ? '<span class="cm-celda__cuenta">' + n + '</span>' : '<span class="cm-celda__cuenta is-cero"></span>') + '</button>';
        }
        caja.innerHTML = html + '</div>';
    }

    function tarjeta(e, i) {
        var p = e.evento.extendedProps;
        var color = p.color || e.evento.backgroundColor || '#496163';
        var lineas = Array.isArray(p.lineas) ? p.lineas : [['Solicitante', p.solicitante], ['Citado', p.citado], ['Conciliador', p.conciliador]];
        var hora = p.hora ? String(p.hora).slice(0, 5) : (e.evento.allDay ? 'Todo el día' : e.orden);
        if (p.hora && !/^\d{1,2}:\d{2}/.test(String(p.hora))) { hora = String(p.hora); }
        return '<button type="button" class="cm-evt" data-i="' + i + '" style="--cm-color:' + esc(color) + '">' +
            '<span class="cm-evt__hora"><i class="bi bi-clock"></i> ' + esc(hora) + '</span>' +
            '<span class="cm-evt__titulo">' + esc(e.evento.title || 'Sin título') + '</span>' +
            lineas.filter(function (l) { return l && l[1]; }).slice(0, 3).map(function (l) {
                return '<span class="cm-evt__linea"><b>' + esc(l[0]) + ':</b> ' + esc(l[1]) + '</span>';
            }).join('') +
            (p.estatus ? '<span class="cm-evt__estatus">' + esc(p.estatus) + '</span>' : '') +
            '</button>';
    }

    function pintarLista() {
        if (!raiz || !elegido) { return; }
        var f = fechaDe(elegido);
        $('cmDiaTitulo').textContent = CAL_DIAS[f.getDay()] + ' ' + f.getDate() + ' de ' + CAL_MESES[f.getMonth()];

        var lista = porDia[elegido] || [];
        $('cmDiaCuenta').textContent = cargando ? '' : (lista.length ? lista.length + ' actividad' + (lista.length === 1 ? '' : 'es') : '');

        var inhabil = calInhabilDelDia(f);
        var aviso = $('cmAviso');
        aviso.hidden = !inhabil;
        if (inhabil) {
            aviso.innerHTML = '<i class="bi bi-slash-circle"></i> ' + (inhabil.todas
                ? 'Día inhábil'
                : 'Inhábil sólo en: ' + esc((inhabil.sedes || []).join(', ')));
        }

        var caja = $('cmLista');
        if (cargando) {
            caja.innerHTML = '<div class="cm-cargando"><span></span><span></span><span></span></div>';
            return;
        }
        caja.innerHTML = lista.length
            ? lista.map(tarjeta).join('')
            : '<div class="cm-vacio"><i class="bi bi-calendar2-check"></i>' + (inhabil ? 'Día inhábil: sin actividades.' : 'Sin actividades este día.') + '</div>';
    }

    // ------------------------------------------------------------ acciones
    function elegir(iso) {
        elegido = iso;
        panoramica ? pintarRejilla() : pintarTira();
        pintarLista();
    }

    function irMes(delta, dia) {
        mes = new Date(mes.getFullYear(), mes.getMonth() + delta, 1, 12);
        var ultimo = new Date(mes.getFullYear(), mes.getMonth() + 1, 0).getDate();
        elegido = calFechaISO(new Date(mes.getFullYear(), mes.getMonth(), Math.min(dia || 1, ultimo), 12));
        porDia = {};
        pintar();
        cargar();
    }

    function irDia(delta) {
        var f = fechaDe(elegido);
        f.setDate(f.getDate() + delta);
        if (f.getMonth() !== mes.getMonth()) {
            mes = new Date(f.getFullYear(), f.getMonth(), 1, 12);
            elegido = calFechaISO(f);
            porDia = {};
            pintar();
            cargar();
            return;
        }
        elegir(calFechaISO(f));
    }

    function hoy() {
        var h = new Date();
        if (h.getMonth() === mes.getMonth() && h.getFullYear() === mes.getFullYear()) { elegir(hoyISO()); return; }
        mes = new Date(h.getFullYear(), h.getMonth(), 1, 12);
        elegido = hoyISO();
        porDia = {};
        pintar();
        cargar();
    }

    function exportar() {
        if (typeof urlAgendaExportar === 'undefined') { return; }
        var r = rangoMes();
        var params = new URLSearchParams({
            start: r.desde, end: r.hasta,
            sede: ($('filtro-sede') || {}).value || 'Todos',
            conciliador: getConciliadorSeleccionado()
        });
        window.location = urlAgendaExportar + '?' + params.toString();
    }

    function iniciar() {
        raiz = $('calMovil');
        if (!raiz || raiz.dataset.iniciado) { return; }
        raiz.dataset.iniciado = '1';

        var h = new Date();
        mes = new Date(h.getFullYear(), h.getMonth(), 1, 12);
        elegido = hoyISO();

        $('cmPrev').addEventListener('click', function () { irMes(-1, fechaDe(elegido).getDate()); });
        $('cmNext').addEventListener('click', function () { irMes(1, fechaDe(elegido).getDate()); });
        $('cmHoy').addEventListener('click', hoy);
        $('cmVista').addEventListener('click', function () { panoramica = !panoramica; pintar(); });
        if ($('cmExportar')) { $('cmExportar').addEventListener('click', exportar); }

        raiz.addEventListener('click', function (ev) {
            var dia = ev.target.closest('[data-dia]');
            if (dia && raiz.contains(dia)) { elegir(dia.getAttribute('data-dia')); return; }
            var evt = ev.target.closest('.cm-evt');
            if (evt) {
                var e = (porDia[elegido] || [])[+evt.getAttribute('data-i')];
                if (e) { handleEventClick({ event: e.evento, el: evt, jsEvent: ev }, e.fuente); }
            }
        });

        // Deslizar la lista del día a los lados cambia de día.
        var x0 = null, y0 = null;
        var lista = $('cmLista');
        lista.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; }, { passive: true });
        lista.addEventListener('touchend', function (e) {
            if (x0 === null) { return; }
            var dx = e.changedTouches[0].clientX - x0, dy = e.changedTouches[0].clientY - y0;
            x0 = null;
            if (Math.abs(dx) > 70 && Math.abs(dx) > Math.abs(dy) * 1.5) { irDia(dx < 0 ? 1 : -1); }
        }, { passive: true });

        pintar();
        cargar();
    }

    return {
        iniciar: iniciar,
        // calendar.js la llama al cambiar sede, conciliador o pastilla.
        recargar: function () { if (raiz && raiz.dataset.iniciado) { cargar(); } },
        activa: function () { return Boolean(raiz && raiz.dataset.iniciado); }
    };
})();
