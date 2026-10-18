{{--
    Fase 3 del flujo cita → solicitud: quién sigue en el módulo del auxiliar
    y cuándo. Va en el layout, así que lo ve esté en la pantalla que esté
    (capturando una solicitud, por ejemplo).

      - Tarjeta "Sigue": folio y nombre de quien sigue (quien ya llegó y
        confirmó su cita, si hay), a qué hora empieza según la duración de
        Horarios de citas y cómo va el turno de ahora. Se puede minimizar.
      - Avisos con campana: "Ya llegó" cuando la persona que sigue confirma
        su llegada y "Prepárate" cuando faltan 10 min o menos para que siga.
        Cada aviso sale una sola vez (se recuerda en el navegador por día) y,
        si la persona dio permiso, también como notificación del sistema.

    Consulta /mi-modulo/avisos cada 30 s (MiModuloController::avisos).
    Sólo se incluye si MiModuloController::puedeVer().
--}}
{{-- En Mi módulo y en el Inicio ya está "quién sigue" en la pantalla: ahí sólo salen los avisos. --}}
@php $flujoEnMiModulo = request()->routeIs('mi_modulo') || request()->routeIs('inicio'); @endphp
<div id="flujoAvisos" class="flujo-avisos" aria-live="polite"></div>
<style>
    .flujo-avisos { position: fixed; right: 18px; bottom: 18px; z-index: 2000; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; width: 360px; max-width: calc(100vw - 24px); pointer-events: none; }
    .flujo-avisos > * { pointer-events: auto; }
    .flujo-avisos, .flujo-avisos * { box-sizing: border-box; }
    @media (max-width: 576px) { .flujo-avisos { right: 12px; bottom: 12px; } }
    .flujo-aviso, .flujo-sigue {
        width: 100%; background: #fff; border: 1px solid #E3E8E8; border-left: 5px solid #CEA845; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(31, 42, 43, .18); padding: 12px 14px; font-size: 13.5px; color: #2E3C3D;
    }
    .flujo-aviso { animation: flujoEntra .35s ease-out; }
    .flujo-aviso--llego { border-left-color: #2E8B57; }
    @keyframes flujoEntra { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .flujo-aviso { animation: none; } }
    .flujo-aviso__cabeza { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .flujo-aviso__titulo { font-weight: 800; color: #354647; font-size: 14px; }
    .flujo-aviso__cerrar { border: 0; background: none; font-size: 18px; color: #8A9899; line-height: 1; cursor: pointer; padding: 0 2px; }
    .flujo-aviso__folio { font-size: 22px; font-weight: 800; letter-spacing: -.01em; margin: 4px 0 0; }
    .flujo-aviso__nombre { font-size: 15px; font-weight: 700; color: #1F2A2B; margin-bottom: 2px; overflow-wrap: anywhere; }
    .flujo-aviso__texto { color: #5E6E6F; line-height: 1.4; }
    .flujo-aviso__acciones { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
    .flujo-aviso__acciones .btn { font-size: 12.5px; font-weight: 700; }
    .flujo-aviso__chip { border-radius: 99px; padding: 1px 8px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .flujo-aviso__chip--prueba { background: #FBF4E1; color: #7A5D10; }
    .flujo-aviso__chip--sala { background: #E5F3EA; color: #1E7B3C; }
    .flujo-aviso__chip--llega { background: #EEF2F2; color: #496163; }

    .flujo-sigue { border-left-color: #496163; }
    .flujo-sigue.is-pronto { border-left-color: #CEA845; background: #FFFDF6; }
    .flujo-sigue__linea { margin-top: 6px; padding-top: 6px; border-top: 1px dashed #E3E8E8; font-size: 12.5px; color: #5E6E6F; }
    .flujo-sigue__linea.is-excedido { color: #9A2B1C; }
    .flujo-sigue__mini {
        border: 0; border-radius: 999px; background: #496163; color: #fff; font-weight: 700; font-size: 13px;
        padding: 8px 14px; box-shadow: 0 6px 18px rgba(31, 42, 43, .25); cursor: pointer;
    }
    .flujo-sigue__mini.is-pronto { background: #A9862B; }
</style>
<script>
(function () {
    var URL = @json(route('mi_modulo.avisos'));
    var URL_MODULO = @json(route('mi_modulo'));
    var TOKEN = @json(csrf_token());
    var CON_TARJETA = @json(! $flujoEnMiModulo); // En Mi módulo e Inicio ya está quién sigue.
    var caja = document.getElementById('flujoAvisos');
    var hoy = new Date().toISOString().slice(0, 10);
    var clave = 'flujo-avisos-' + hoy;
    var claveMini = 'flujo-sigue-mini';
    var tarjeta = null;
    var ultimo = null;

    function leer(k, def) { try { var v = JSON.parse(localStorage.getItem(k)); return v == null ? def : v; } catch (e) { return def; } }
    function guardar(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
    function visto(k) { return leer(clave, []).indexOf(k) !== -1; }
    function marcar(k) { var v = leer(clave, []); if (v.indexOf(k) === -1) { v.push(k); guardar(clave, v.slice(-300)); } }
    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    function campana() {
        try {
            var ctx = campana.ctx || (campana.ctx = new (window.AudioContext || window.webkitAudioContext)());
            [880, 1320, 1760].forEach(function (f, i) {
                var o = ctx.createOscillator(), g = ctx.createGain(), t0 = ctx.currentTime + i * 0.22;
                o.frequency.value = f; g.gain.setValueAtTime(0.0001, t0);
                g.gain.exponentialRampToValueAtTime(0.3, t0 + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, t0 + 0.5);
                o.connect(g); g.connect(ctx.destination); o.start(t0); o.stop(t0 + 0.55);
            });
        } catch (e) { /* sin audio */ }
    }
    function notificar(titulo, cuerpo, tag) {
        if ('Notification' in window && Notification.permission === 'granted') {
            try { new Notification(titulo, { body: cuerpo, tag: tag }); } catch (e) {}
        }
    }

    function cuando(s) {
        return s.minutos > 0 ? 'en <b>' + s.minutos + ' min</b> (aprox. ' + esc(s.empieza) + ')' : '<b>ahora</b>';
    }
    function chips(s) {
        return (s.enSala ? '<span class="flujo-aviso__chip flujo-aviso__chip--sala">Ya llegó</span>' : '<span class="flujo-aviso__chip flujo-aviso__chip--llega">Aún no llega</span>') +
            (s.prueba ? ' <span class="flujo-aviso__chip flujo-aviso__chip--prueba">Prueba</span>' : '');
    }
    function botones(s) {
        return '<div class="flujo-aviso__acciones">' +
            (s.iniciar ? '<form method="POST" action="' + esc(s.iniciar) + '" class="m-0"><input type="hidden" name="_token" value="' + esc(TOKEN) + '">' +
                '<button type="submit" class="btn btn-sm text-white" style="background:#496163;"><i class="bi bi-magic me-1"></i>Preparar solicitud</button></form>' : '') +
            '<a class="btn btn-sm btn-outline-secondary" href="' + esc(URL_MODULO) + '">Mi módulo</a></div>';
    }

    // Aviso que sale una vez: "Prepárate" o "Ya llegó".
    function avisar(m, s, tipo) {
        var div = document.createElement('div');
        div.className = 'flujo-aviso' + (tipo === 'llego' ? ' flujo-aviso--llego' : '');
        div.setAttribute('role', 'alert');
        var titulo = tipo === 'llego' ? '<i class="bi bi-person-check-fill me-1"></i>Ya llegó quien sigue' : '<i class="bi bi-bell-fill me-1"></i>Prepárate';
        var texto = tipo === 'llego'
            ? 'Confirmó su cita de las ' + esc(s.hora) + '. Sigue en ' + esc(m.modulo) + ' ' + cuando(s) + '.'
            : (s.minutos > 0 ? 'En <b>' + s.minutos + ' min</b> sigue en ' : 'Ya sigue en ') + esc(m.modulo) + ' · ' + esc(s.tipo) + ' de las ' + esc(s.hora) +
              (s.enSala ? ' · <b>ya llegó y confirmó</b>' : ' · aún no confirma su llegada');
        div.innerHTML =
            '<div class="flujo-aviso__cabeza"><span class="flujo-aviso__titulo">' + titulo + '</span>' +
            '<button type="button" class="flujo-aviso__cerrar" aria-label="Cerrar">&times;</button></div>' +
            '<div class="flujo-aviso__folio">' + esc(s.folio) + '</div>' +
            '<div class="flujo-aviso__nombre">' + esc(s.persona) + '</div>' +
            '<div class="flujo-aviso__texto">' + texto + '</div>' + botones(s);
        div.querySelector('.flujo-aviso__cerrar').addEventListener('click', function () { div.remove(); });
        caja.insertBefore(div, tarjeta && tarjeta.parentNode === caja ? tarjeta : null);
        setTimeout(function () { if (div.isConnected) { div.remove(); } }, 10 * 60 * 1000);

        campana();
        notificar(tipo === 'llego' ? 'Ya llegó: ' + s.folio : 'Prepárate: ' + s.folio,
            s.persona + (tipo === 'llego' ? ' confirmó su cita; sigue en ' + m.modulo : (s.minutos > 0 ? ' sigue en ' + s.minutos + ' min' : ' sigue ahora')),
            'cita-' + tipo + '-' + s.id);
    }

    // Tarjeta permanente "Sigue" (o su versión minimizada).
    function pintarTarjeta(j) {
        if (!CON_TARJETA) { return; }
        var conSigue = (j.modulos || []).filter(function (m) { return m.siguiente; });
        if (!tarjeta) { tarjeta = document.createElement('div'); tarjeta.style.width = '100%'; tarjeta.style.display = 'flex'; tarjeta.style.flexDirection = 'column'; tarjeta.style.alignItems = 'flex-end'; tarjeta.style.gap = '8px'; }
        if (!conSigue.length) { tarjeta.remove(); return; }

        var pronto = function (s) { return s.minutos <= j.minutos; };
        if (leer(claveMini, false)) {
            var s0 = conSigue[0].siguiente;
            tarjeta.innerHTML = '<button type="button" class="flujo-sigue__mini' + (pronto(s0) ? ' is-pronto' : '') + '" data-flujo="abrir" title="Ver quién sigue">' +
                '<i class="bi bi-person-lines-fill me-1"></i>Sigue ' + esc(s0.folio) + ' · ' + (s0.minutos > 0 ? s0.minutos + ' min' : 'ahora') + '</button>';
        } else {
            tarjeta.innerHTML = conSigue.map(function (m, i) {
                var s = m.siguiente, a = m.actual;
                return '<div class="flujo-sigue' + (pronto(s) ? ' is-pronto' : '') + '" role="status">' +
                    '<div class="flujo-aviso__cabeza"><span class="flujo-aviso__titulo"><i class="bi bi-person-lines-fill me-1"></i>Sigue · ' + esc(m.modulo) + '</span>' +
                    (i === 0 ? '<button type="button" class="flujo-aviso__cerrar" data-flujo="minimizar" aria-label="Minimizar" title="Minimizar">&minus;</button>' : '') + '</div>' +
                    '<div class="flujo-aviso__folio">' + esc(s.folio) + ' ' + chips(s) + '</div>' +
                    '<div class="flujo-aviso__nombre">' + esc(s.persona) + '</div>' +
                    '<div class="flujo-aviso__texto">' + esc(s.tipo) + ' de las ' + esc(s.hora) + ' · empieza ' + cuando(s) + '</div>' +
                    (a ? '<div class="flujo-sigue__linea' + (a.excedido > 0 ? ' is-excedido' : '') + '">Ahora: <b>' + esc(a.folio) + '</b> · ' +
                        (a.excedido > 0 ? 'lleva ' + a.lleva + ' min, ' + a.excedido + ' más de lo planeado (' + a.duracion + ' min)' : 'termina aprox. ' + esc(a.termina) + ' (' + a.duracion + ' min por cita)') + '</div>' : '') +
                    (m.antes ? '<div class="flujo-sigue__linea">Antes tenía cita <b>' + esc(m.antes.folio) + '</b> a las ' + esc(m.antes.hora) + ' y aún no llega.</div>' : '') +
                    botones(s) + '</div>';
            }).join('');
        }
        if (tarjeta.parentNode !== caja) { caja.appendChild(tarjeta); }
    }

    function procesar(j) {
        ultimo = j;
        (j.modulos || []).forEach(function (m) {
            var s = m.siguiente;
            if (!s) { return; }
            var kPrep = 'prep-' + s.id, kLlego = 'llego-' + s.id;
            var toca = s.minutos <= j.minutos;
            if (toca && !visto(kPrep)) {
                marcar(kPrep); if (s.enSala) { marcar(kLlego); }
                avisar(m, s, 'prep');
            } else if (s.enSala && !visto(kLlego)) {
                marcar(kLlego);
                avisar(m, s, 'llego');
            }
        });
        pintarTarjeta(j);
    }

    caja.addEventListener('click', function (e) {
        var b = e.target.closest('[data-flujo]');
        if (!b) { return; }
        guardar(claveMini, b.getAttribute('data-flujo') === 'minimizar');
        if (ultimo) { pintarTarjeta(ultimo); }
    });

    function revisar() {
        fetch(URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) { if (j) { procesar(j); } })
            .catch(function () {});
    }

    revisar();
    setInterval(revisar, 30000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { revisar(); } });
})();
</script>
