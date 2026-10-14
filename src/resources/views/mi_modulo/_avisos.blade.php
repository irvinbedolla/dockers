{{--
    Fase 3 del flujo cita → solicitud: "Prepárate, la próxima persona llega en
    X minutos". Va en el layout, así que el auxiliar lo recibe esté en la
    pantalla que esté (capturando otra solicitud, por ejemplo).

    Consulta /mi-modulo/avisos cada 30 s. Cada cita se avisa una sola vez
    (se recuerda en el navegador por día). Suena una campana y, si la persona
    dio permiso, sale también la notificación del sistema operativo.

    Sólo se incluye si MiModuloController::puedeVer() (el interruptor).
--}}
<div id="flujoAvisos" class="flujo-avisos" aria-live="polite"></div>
<style>
    .flujo-avisos { position: fixed; right: 18px; bottom: 18px; z-index: 2000; display: flex; flex-direction: column; gap: 10px; max-width: 360px; }
    .flujo-aviso {
        background: #fff; border: 1px solid #E3E8E8; border-left: 5px solid #CEA845; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(31, 42, 43, .18); padding: 12px 14px; font-size: 13.5px; color: #2E3C3D;
        animation: flujoEntra .35s ease-out;
    }
    @keyframes flujoEntra { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    .flujo-aviso__cabeza { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .flujo-aviso__titulo { font-weight: 800; color: #354647; font-size: 14px; }
    .flujo-aviso__cerrar { border: 0; background: none; font-size: 18px; color: #8A9899; line-height: 1; cursor: pointer; }
    .flujo-aviso__folio { font-size: 22px; font-weight: 800; letter-spacing: -.01em; margin: 4px 0 2px; }
    .flujo-aviso__texto { color: #5E6E6F; }
    .flujo-aviso__acciones { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
    .flujo-aviso__acciones .btn { font-size: 12.5px; font-weight: 700; }
    .flujo-aviso__prueba { background: #FBF4E1; color: #7A5D10; border-radius: 99px; padding: 1px 8px; font-size: 11px; font-weight: 700; }
</style>
<script>
(function () {
    var URL = @json(route('mi_modulo.avisos'));
    var URL_MODULO = @json(route('mi_modulo'));
    var TOKEN = @json(csrf_token());
    var caja = document.getElementById('flujoAvisos');
    var clave = 'flujo-avisos-' + new Date().toISOString().slice(0, 10);

    function vistos() { try { return JSON.parse(localStorage.getItem(clave) || '[]'); } catch (e) { return []; } }
    function marcar(id) { try { var v = vistos(); v.push(id); localStorage.setItem(clave, JSON.stringify(v.slice(-200))); } catch (e) {} }
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

    function mostrar(a) {
        var div = document.createElement('div');
        div.className = 'flujo-aviso';
        div.setAttribute('role', 'alert');
        div.innerHTML =
            '<div class="flujo-aviso__cabeza"><span class="flujo-aviso__titulo"><i class="bi bi-bell-fill me-1"></i>Prepárate</span>' +
            (a.prueba ? '<span class="flujo-aviso__prueba">Prueba</span>' : '') +
            '<button type="button" class="flujo-aviso__cerrar" aria-label="Cerrar">&times;</button></div>' +
            '<div class="flujo-aviso__folio">' + esc(a.folio) + '</div>' +
            '<div class="flujo-aviso__texto">' + esc(a.persona) + ' · ' + esc(a.tipo) + ' a las ' + esc(a.hora) + '<br>' +
            (a.minutos > 0 ? 'Llega a ' + esc(a.modulo) + ' en <b>' + a.minutos + ' min</b>' : 'Es su hora en ' + esc(a.modulo)) +
            (a.enSala ? ' · <b>ya está en sala</b>' : '') + '</div>' +
            '<div class="flujo-aviso__acciones">' +
            (a.iniciar ? '<form method="POST" action="' + esc(a.iniciar) + '" class="m-0"><input type="hidden" name="_token" value="' + esc(TOKEN) + '">' +
                '<button type="submit" class="btn btn-sm text-white" style="background:#496163;">Preparar solicitud</button></form>' : '') +
            '<a class="btn btn-sm btn-outline-secondary" href="' + esc(URL_MODULO) + '">Mi módulo</a></div>';
        div.querySelector('.flujo-aviso__cerrar').addEventListener('click', function () { div.remove(); });
        caja.appendChild(div);
        setTimeout(function () { if (div.isConnected) { div.remove(); } }, 10 * 60 * 1000);

        campana();
        if ('Notification' in window && Notification.permission === 'granted') {
            try { new Notification('Prepárate: ' + a.folio, { body: a.persona + ' llega a ' + a.modulo + (a.minutos > 0 ? ' en ' + a.minutos + ' min' : ' ahora'), tag: 'cita-' + a.id }); } catch (e) {}
        }
    }

    function revisar() {
        fetch(URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : { avisos: [] }; })
            .then(function (j) {
                var v = vistos();
                (j.avisos || []).forEach(function (a) {
                    if (v.indexOf(a.id) === -1) { marcar(a.id); mostrar(a); }
                });
            })
            .catch(function () {});
    }

    revisar();
    setInterval(revisar, 30000);
})();
</script>
