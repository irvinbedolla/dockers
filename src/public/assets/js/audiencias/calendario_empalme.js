/*
 * Calendario de reagenda de audiencias (api/obtenerAudienciasParte2):
 * cada horario dice si al elegirlo hay empalme o no.
 *
 *   Libre · sin empalme       el conciliador no tiene audiencia a esa hora.
 *   Con empalme               ya tiene audiencia(s) y todavía cabe otra: se empalma.
 *   Ocupado                   ya no cabe (cupo lleno o el horario no admite empalme).
 *   No disponible / Inhábil   como siempre.
 *
 * Se usa en audiencias/parte3 (al concluir) y en audiencias/edita_audienciaConcluida
 * (editar finalización):
 *   eventContent:  CalendarioEmpalme.contenido
 *   eventDidMount: CalendarioEmpalme.montar
 *   y CalendarioEmpalme.leyenda(elemento) para pintar la leyenda arriba del calendario.
 */
(function (global) {
    var ESTILO_ID = 'calendario-empalme-estilos';

    function estilos() {
        if (document.getElementById(ESTILO_ID)) { return; }
        var css = ''
            + '.fc .fc-event.ce-libre{background-color:#00CE1C !important;border-color:#00CE1C !important;color:#fff !important;}'
            + '.fc .fc-event.ce-empalme{background-color:#F2B705 !important;border-color:#C99400 !important;color:#3A2A00 !important;}'
            + '.fc .fc-event.ce-ocupado{background-color:#DA0909 !important;border-color:#DA0909 !important;color:#fff !important;}'
            + '.fc .fc-event.ce-empalme .fc-event-main,.fc .fc-event.ce-empalme .ce-sub{color:#3A2A00 !important;}'
            + '.ce-evento{display:flex;flex-direction:column;align-items:flex-start;text-align:left;line-height:1.15;padding:1px 3px;white-space:normal;overflow:hidden;width:100%;}'
            + '.ce-fila{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-start;gap:2px 4px;font-weight:700;font-size:11.5px;}'
            + '.ce-sub{font-size:10.5px;font-weight:500;opacity:.95;}'
            + '.ce-leyenda{display:flex;flex-wrap:wrap;gap:6px 14px;margin:0 0 10px;font-size:12.5px;color:#2E3C3D;}'
            + '.ce-leyenda span{display:inline-flex;align-items:center;gap:6px;}'
            + '.ce-leyenda i.ce-cuadro{width:14px;height:14px;border-radius:3px;display:inline-block;}'
            + '.fc-list-event.ce-empalme td{background:#FFF6D6 !important;}';
        var tag = document.createElement('style');
        tag.id = ESTILO_ID;
        tag.textContent = css;
        document.head.appendChild(tag);
    }

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /** Qué es el horario: libre, empalme, ocupado, expirado o inhábil, con su texto. */
    function clasificar(evento) {
        var p = evento.extendedProps || {};
        var estado = p.estado || '';
        var n = Number(p.audiencias_en_slot || 0);
        var permite = p.permite_empalme !== false; // si el servidor no lo manda, se asume que sí
        var plural = function (k) { return k === 1 ? '1 audiencia' : k + ' audiencias'; };

        if (estado === 'disponible' && n === 0) {
            return { tipo: 'libre', titulo: 'Libre', sub: permite ? 'Sin empalme' : 'Sin empalme · solo 1',
                ayuda: permite ? 'No hay audiencias a esta hora: no hay empalme.' : 'No hay audiencias a esta hora: no hay empalme. Es un horario corto: no admite empalme.' };
        }
        if (estado === 'disponible' && n > 0) {
            return { tipo: 'empalme', titulo: 'Con empalme', sub: 'Ya hay ' + plural(n),
                ayuda: 'Ya hay ' + plural(n) + ' a esta hora. Si eliges este horario, la audiencia se empalma.' };
        }
        if (estado === 'ocupado') {
            return { tipo: 'ocupado', titulo: 'Ocupado', sub: permite ? 'Cupo lleno (' + plural(n) + ')' : 'No admite empalme',
                ayuda: permite
                    ? 'Ya hay ' + plural(n) + ' a esta hora: el horario está lleno.'
                    : 'Este horario no admite empalme y ya tiene audiencia.' };
        }
        if (estado === 'inhabil') {
            return { tipo: 'inhabil', titulo: 'Inhábil', sub: '', ayuda: 'Día u horario inhábil.' };
        }
        return { tipo: 'expirado', titulo: 'No disponible', sub: n > 0 ? plural(n) : '', ayuda: 'Horario no disponible.' };
    }

    function contenido(arg) {
        estilos();
        var c = clasificar(arg.event);
        var icono = { libre: 'bi-check-circle-fill', empalme: 'bi-exclamation-triangle-fill', ocupado: 'bi-x-octagon-fill', inhabil: 'bi-slash-circle', expirado: 'bi-dash-circle' }[c.tipo];
        return {
            html: '<div class="ce-evento">'
                + '<div class="ce-fila"><span>' + esc(arg.timeText) + '</span><i class="bi ' + icono + '"></i><span>' + esc(c.titulo) + '</span></div>'
                + (c.sub ? '<div class="ce-sub">' + esc(c.sub) + '</div>' : '')
                + '</div>'
        };
    }

    function montar(info) {
        estilos();
        var p = info.event.extendedProps || {};
        var c = clasificar(info.event);
        // Se conservan las clases de siempre (fc-event-ocupado, etc.); "libre" y "empalme" llevan las suyas.
        if (p.estado && c.tipo !== 'empalme') {
            info.el.classList.add('fc-est-' + p.estado, 'fc-event-' + p.estado);
        }
        info.el.classList.add('ce-' + c.tipo);
        info.el.setAttribute('title', c.ayuda);
    }

    function leyenda(el) {
        estilos();
        if (!el) { return; }
        el.className = 'ce-leyenda';
        el.innerHTML = [
            ['#00CE1C', 'Libre · sin empalme'],
            ['#F2B705', 'Con empalme (ya hay audiencia; se empalma)'],
            ['#DA0909', 'Ocupado (ya no cabe)'],
            ['#F59727', 'No disponible'],
            ['#3B78DB', 'Inhábil']
        ].map(function (x) { return '<span><i class="ce-cuadro" style="background:' + x[0] + '"></i>' + x[1] + '</span>'; }).join('');
    }

    global.CalendarioEmpalme = { contenido: contenido, montar: montar, leyenda: leyenda, clasificar: clasificar };
})(window);
