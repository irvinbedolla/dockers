{{--
    Aviso al conciliador: "Llegó el trabajador de MOR/SOL/2026/03940 · Sala 4".
    Lo marca la recepción en "Audiencias de hoy" (App\Support\LlegadasAudiencia).
    Va en el layout para que le llegue esté en la pantalla que esté.

    Consulta /audiencias/llegadas/avisos cada 30 s; cada llegada se avisa una
    sola vez (se recuerda en el navegador por día). Suena una campana y, si la
    persona dio permiso, sale también la notificación del sistema operativo.
--}}
<div id="llegadaAvisos" class="llegada-avisos" aria-live="polite"></div>
<style>
    .llegada-avisos { position: fixed; left: 18px; bottom: 18px; z-index: 2000; display: flex; flex-direction: column; gap: 10px; max-width: 360px; }
    .llegada-aviso {
        background: #fff; border: 1px solid #E3E8E8; border-left: 5px solid #2F9E5B; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(31, 42, 43, .18); padding: 12px 14px; font-size: 13.5px; color: #2E3C3D;
        animation: llegadaEntra .35s ease-out;
    }
    @keyframes llegadaEntra { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    .llegada-aviso__cabeza { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .llegada-aviso__titulo { font-weight: 800; color: #1E7B3C; font-size: 14px; }
    .llegada-aviso__cerrar { border: 0; background: none; font-size: 18px; color: #8A9899; line-height: 1; cursor: pointer; }
    .llegada-aviso__nombre { font-size: 16px; font-weight: 800; margin: 4px 0 2px; overflow-wrap: anywhere; }
    .llegada-aviso__texto { color: #5E6E6F; }
    .llegada-aviso__acciones { margin-top: 10px; }
    .llegada-aviso__acciones .btn { font-size: 12.5px; font-weight: 700; background: #496163; color: #fff; }
</style>
<script>
(function () {
    var URL = @json(route('audiencias.llegadas_avisos'));
    var caja = document.getElementById('llegadaAvisos');
    var clave = 'llegadas-avisos-' + new Date().toISOString().slice(0, 10);

    function vistos() { try { return JSON.parse(localStorage.getItem(clave) || '[]'); } catch (e) { return []; } }
    function marcar(id) { try { var v = vistos(); v.push(id); localStorage.setItem(clave, JSON.stringify(v.slice(-300))); } catch (e) {} }
    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    function campana() {
        try {
            var ctx = campana.ctx || (campana.ctx = new (window.AudioContext || window.webkitAudioContext)());
            [660, 990].forEach(function (f, i) {
                var o = ctx.createOscillator(), g = ctx.createGain(), t0 = ctx.currentTime + i * 0.2;
                o.frequency.value = f; g.gain.setValueAtTime(0.0001, t0);
                g.gain.exponentialRampToValueAtTime(0.3, t0 + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, t0 + 0.5);
                o.connect(g); g.connect(ctx.destination); o.start(t0); o.stop(t0 + 0.55);
            });
        } catch (e) { /* sin audio */ }
    }

    function mostrar(a) {
        var div = document.createElement('div');
        div.className = 'llegada-aviso';
        div.setAttribute('role', 'alert');
        div.innerHTML =
            '<div class="llegada-aviso__cabeza"><span class="llegada-aviso__titulo"><i class="bi bi-person-check-fill me-1"></i>Llegó ' + esc(a.quien) + '</span>' +
            '<button type="button" class="llegada-aviso__cerrar" aria-label="Cerrar">&times;</button></div>' +
            '<div class="llegada-aviso__nombre">' + esc(a.nombre) + '</div>' +
            '<div class="llegada-aviso__texto">' + esc(a.nue) + ' · ' + esc(a.sala || 'Sin sala') + ' · audiencia de las ' + esc(a.hora) + '<br>Registrado a las ' + esc(a.llegada) + '</div>' +
            '<div class="llegada-aviso__acciones"><a class="btn btn-sm" href="' + esc(a.url) + '">Ir a la audiencia</a></div>';
        div.querySelector('.llegada-aviso__cerrar').addEventListener('click', function () { div.remove(); });
        caja.appendChild(div);
        setTimeout(function () { if (div.isConnected) { div.remove(); } }, 15 * 60 * 1000);

        campana();
        if ('Notification' in window && Notification.permission === 'granted') {
            try { new Notification('Llegó ' + a.quien + ' · ' + a.sala, { body: a.nombre + ' · ' + a.nue, tag: 'llegada-' + a.id }); } catch (e) {}
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
