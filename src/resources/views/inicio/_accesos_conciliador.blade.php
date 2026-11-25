{{--
    Datos rápidos del conciliador, en la tarjeta del saludo del Inicio. Los
    arma App\Support\AccesosConciliador:

      1. Mi siguiente audiencia (con quién llegó y el semáforo de "¿Está
         lista la audiencia?");
      2. Mi día;
      3. Lo que sigue hoy;
      4. Próximos días (mañana, la semana y los citados sin notificar).

    Las tres primeras se pintan con JavaScript y se actualizan cada 30 s con
    InicioController::conciliador; la cuarta se arma al cargar.

    Espera $conciliador = ['hoy' => ..., 'proximos' => ...].
--}}
@include('inicio._accesos_estilos')
@php $px = $conciliador['proximos']; @endphp
<style>
    .ac-siguiente, .ac-proximos { grid-column: span 2; }
    .ac-proximos { grid-column: 1 / -1; }
    @media (max-width: 560px) { .ac-siguiente, .ac-proximos { grid-column: auto; } }

    .ac-hora { display: flex; align-items: baseline; flex-wrap: wrap; gap: 8px 12px; }
    .ac-hora__num { font-size: 30px; font-weight: 800; color: #2E3C3D; line-height: 1; font-variant-numeric: tabular-nums; }
    .ac-hora__donde { font-size: 13px; font-weight: 700; color: #2E3C3D; }
    .ac-hora__donde small { display: block; font-size: 11.5px; font-weight: 500; color: #8A9899; }
    .ar-chip.is-ok { background: #E5F3EA; border-color: #BFE0C9; color: #1E7B3C; }
    .ar-chip.is-pronto { background: #FBF4E1; border-color: #F0DFA8; color: #7A5D10; }
    .ar-chip.is-tarde { background: #FDECEA; border-color: #F5C6C0; color: #B42318; }
    .ar-chip.is-reag { background: #E8EEF8; border-color: #CCD6F0; color: #2B4C7E; }

    .ac-partes { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
    .ac-partes li { display: grid; grid-template-columns: 78px minmax(0, 1fr) auto; gap: 8px; align-items: center; font-size: 12.5px; }
    .ac-partes__rol { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #8A9899; }
    .ac-partes__nombre { font-weight: 600; color: #2E3C3D; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ac-llego { font-size: 11.5px; font-weight: 700; color: #1E7B3C; white-space: nowrap; }
    .ac-falta { font-size: 11.5px; font-weight: 600; color: #8A9899; white-space: nowrap; }

    .ac-obs { font-size: 12px; color: #5E4A0D; background: #FFF8E6; border: 1px solid #F0DFB0; border-radius: 8px; padding: 6px 9px; }
    .ac-obs.is-tuya { color: #23407A; background: #EEF1FB; border-color: #CCD6F0; }
    .ac-obs b { font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; margin-right: 4px; }

    .ac-semaforo { border-radius: 8px; padding: 7px 10px; font-size: 12px; }
    .ac-semaforo.is-lista { background: #E5F3EA; color: #1E7B3C; font-weight: 700; }
    .ac-semaforo.is-pendiente { background: #fff; border: 1px solid #E3E8E8; color: #5E6E6F; }
    .ac-semaforo__cab { font-weight: 700; color: #2E3C3D; margin-bottom: 3px; }
    .ac-semaforo ul { margin: 0; padding-left: 0; list-style: none; display: flex; flex-direction: column; gap: 2px; }
    .ac-semaforo li { display: flex; gap: 6px; align-items: baseline; }
    .ac-punto { flex: none; width: 8px; height: 8px; border-radius: 50%; display: inline-block; transform: translateY(-1px); }
    .ac-punto.is-falta { background: #D92D20; }
    .ac-punto.is-aviso { background: #CEA845; }

    .ac-linea { font-size: 12px; color: #5E6E6F; }
    .ac-linea.is-alerta { color: #B42318; font-weight: 700; }

    .ac-lista { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 7px; }
    .ac-lista li { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0 8px; align-items: baseline; font-size: 12.5px; color: #5E6E6F; }
    .ac-lista b { color: #2E3C3D; font-variant-numeric: tabular-nums; }
    .ac-lista a { color: #2E3C3D; font-weight: 600; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ac-lista a:hover { text-decoration: underline; }
    .ac-lista .ac-lista__meta { grid-column: 2; font-size: 11.5px; color: #8A9899; }
    .ac-lista .is-ok { color: #1E7B3C; font-weight: 700; }
    .ac-lista .is-tarde { color: #B42318; font-weight: 700; }

    .ac-proximos__cuerpo { display: grid; grid-template-columns: minmax(180px, auto) minmax(0, 1fr); gap: 12px 22px; align-items: start; }
    @media (max-width: 720px) { .ac-proximos__cuerpo { grid-template-columns: 1fr; } }
    .ac-proximos__lista { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 8px; }
    .ac-aviso { display: flex; flex-direction: column; gap: 2px; padding: 8px 10px; border-radius: 8px; background: #fff; border: 1px solid #F0DFA8; text-decoration: none; }
    .ac-aviso:hover { border-color: #CEA845; }
    .ac-aviso b { font-size: 12.5px; color: #2E3C3D; }
    .ac-aviso span { font-size: 11.5px; color: #7A5D10; }
    .ac-aviso span.is-falta { color: #B42318; font-weight: 700; }
</style>

<div class="ar">
    {{-- 1. Mi siguiente audiencia --}}
    <div class="ar-tarjeta ac-siguiente" id="acSiguiente" aria-live="polite">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-briefcase" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Mi siguiente audiencia <span class="ar-sub">Se actualiza sola</span></h3>
        </div>
        <div id="acSiguienteCuerpo" style="display:flex;flex-direction:column;gap:10px"></div>
    </div>

    {{-- 2. Mi día --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Mi día <span class="ar-sub">{{ \Illuminate\Support\Str::ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM')) }}</span></h3>
        </div>
        <div id="acDia" style="display:flex;flex-direction:column;gap:10px"></div>
    </div>

    {{-- 3. Lo que sigue hoy --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Lo que sigue hoy <span class="ar-sub">Tus audiencias, por hora</span></h3>
        </div>
        <div id="acSiguen"></div>
        <a class="ar-pie" href="{{ $conciliador['hoy']['urls']['llegadas'] }}">Ver quién llegó <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 4. Próximos días --}}
    <div class="ar-tarjeta ac-proximos">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-calendar-week" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Próximos días <span class="ar-sub">Tus audiencias pendientes de los siguientes {{ $px['dias'] }} días</span></h3>
        </div>
        <div class="ac-proximos__cuerpo">
            <div style="display:flex;flex-direction:column;gap:8px">
                <div class="ar-cifra">{{ number_format($px['manana']) }} <small>mañana</small></div>
                <div class="ar-chips">
                    <span class="ar-chip">En {{ $px['dias'] }} días <b>{{ number_format($px['semana']) }}</b></span>
                    <span class="ar-chip {{ $px['conPendientes'] ? 'is-pronto' : 'is-ok' }}">Con citados sin notificar <b>{{ number_format($px['conPendientes']) }}</b></span>
                </div>
                @if ($px['urls']['agenda'])
                    <a class="ar-pie" href="{{ $px['urls']['agenda'] }}">Ver mi agenda <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endif
            </div>
            <div>
                @if ($px['lista'])
                    <div class="ac-proximos__lista">
                        @foreach ($px['lista'] as $a)
                            <a class="ac-aviso" @if ($a['url']) href="{{ $a['url'] }}" @endif>
                                <b>{{ $a['cuando'] }} · {{ $a['nue'] ?: 'Sin NUE' }}</b>
                                <span>{{ $a['sin'] === 1 ? '1 citado sin notificar' : $a['sin'].' citados sin notificar' }}</span>
                                @if ($a['fallidas'])
                                    <span class="is-falta">{{ $a['fallidas'] === 1 ? 'En 1 no se logró notificar' : 'En '.$a['fallidas'].' no se logró notificar' }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                    @if ($px['conPendientes'] > count($px['lista']))
                        <span class="ar-nota">Y {{ $px['conPendientes'] - count($px['lista']) }} más en la semana.</span>
                    @endif
                @elseif ($px['semana'])
                    <span class="ar-nota"><i class="bi bi-check-circle" aria-hidden="true"></i> Todos los citados de tus próximas audiencias ya están notificados.</span>
                @else
                    <span class="ar-nota">No tienes audiencias pendientes en los próximos {{ $px['dias'] }} días.</span>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var URL = @json(route('inicio.conciliador'));
    var TOLERANCIA = @json(\App\Support\LlegadasHoy::TOLERANCIA_MINUTOS);
    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function tiempo(min) {
        if (min < 60) { return min + ' min'; }
        var h = Math.floor(min / 60), m = min % 60;
        return h + ' h' + (m ? ' ' + m + ' min' : '');
    }

    function siguiente(s) {
        if (!s) { return '<span class="ar-nota">No tienes más audiencias pendientes hoy.</span>'; }
        var cuando = s.minutos < 0
            ? '<span class="ar-chip' + (s.minutos >= -15 ? ' is-pronto' : '') + '">En <b>' + tiempo(-s.minutos) + '</b></span>'
            : (s.minutos <= TOLERANCIA ? '<span class="ar-chip is-pronto"><b>Ya es hora</b></span>'
                : '<span class="ar-chip is-tarde">Empezó hace <b>' + tiempo(s.minutos) + '</b></span>');
        var h = '<div class="ac-hora"><span class="ac-hora__num">' + esc(s.hora) + '</span>' +
            '<span class="ac-hora__donde">' + esc(s.nue) + '<small>' + esc(s.lugar) + '</small></span>' +
            '<span class="ar-chips">' + cuando +
                '<span class="ar-chip' + (s.completa ? ' is-ok' : '') + '">' + (s.completa ? 'Todos llegaron' : 'Llegaron <b>' + s.llegaron + ' de ' + s.partes.length + '</b>') + '</span>' +
                (s.reagendada ? '<span class="ar-chip is-reag"><i class="bi bi-arrow-repeat"></i> Reagendada</span>' : '') +
            '</span></div>';

        if (s.partes.length) {
            h += '<ul class="ac-partes">' + s.partes.map(function (p) {
                return '<li><span class="ac-partes__rol">' + esc(p.rol) + '</span><span class="ac-partes__nombre" title="' + esc(p.nombre) + '">' + esc(p.nombre) + '</span>' +
                    (p.llegada ? '<span class="ac-llego"><i class="bi bi-check-circle-fill"></i> Llegó ' + esc(p.llegada) + '</span>' : '<span class="ac-falta">Aún no llega</span>') + '</li>';
            }).join('') + '</ul>';
        }

        (s.obs || []).forEach(function (o) {
            h += '<div class="ac-obs' + (o.quien === 'Tú' ? ' is-tuya' : '') + '"><b>' + esc(o.quien) + '</b>' + o.textos.map(esc).join(' · ') + '</div>';
        });

        var r = s.revision;
        if (r) {
            if (r.lista) {
                h += '<div class="ac-semaforo is-lista"><i class="bi bi-check-circle-fill"></i> Lista para la audiencia</div>';
            } else {
                var partes = [];
                if (r.falta) { partes.push(r.falta + (r.falta === 1 ? ' falta' : ' faltan')); }
                if (r.aviso) { partes.push(r.aviso + ' por revisar'); }
                h += '<div class="ac-semaforo is-pendiente"><div class="ac-semaforo__cab">¿Está lista? ' + esc(partes.join(' · ')) + '</div><ul>' +
                    r.puntos.map(function (p) { return '<li><span class="ac-punto is-' + p.nivel + '"></span><span>' + esc(p.texto) + '</span></li>'; }).join('') +
                    '</ul></div>';
            }
        }

        var botones = '';
        if (s.urls.revisar) { botones += '<a class="ar-boton ar-boton--claro" href="' + esc(s.urls.revisar) + '"><i class="bi bi-clipboard-check"></i> Revisar audiencia</a>'; }
        if (s.urls.iniciar) { botones += '<a class="ar-boton" href="' + esc(s.urls.iniciar) + '"><i class="bi bi-play-fill"></i> Iniciar audiencia</a>'; }
        if (botones) { h += '<div class="ar-botones">' + botones + '</div>'; }
        return h;
    }

    function dia(d) {
        var h = '<div class="ar-cifra">' + d.total + ' <small>' + (d.total === 1 ? 'audiencia' : 'audiencias') + '</small></div>' +
            '<div class="ar-chips">' +
                '<span class="ar-chip">Concluidas <b>' + d.concluidas + '</b></span>' +
                '<span class="ar-chip' + (d.convenio ? ' is-ok' : '') + '">Con convenio <b>' + d.convenio + '</b></span>' +
                (d.reagendadas ? '<span class="ar-chip is-reag">Reagendadas <b>' + d.reagendadas + '</b></span>' : '') +
                '<span class="ar-chip">Pendientes <b>' + d.pendientes + '</b></span>' +
            '</div>';
        if (d.partes) { h += '<span class="ac-linea">Ya están en el Centro <b>' + d.enCentro + ' de ' + d.partes + '</b> partes de tus audiencias pendientes</span>'; }
        if (d.pasadas) { h += '<span class="ac-linea is-alerta"><i class="bi bi-alarm"></i> ' + (d.pasadas === 1 ? '1 pasó su hora sin cerrarse' : d.pasadas + ' pasaron su hora sin cerrarse') + '</span>'; }
        return h;
    }

    function siguen(lista) {
        if (!lista.length) { return '<span class="ar-nota">Después de ésta, no tienes más audiencias hoy.</span>'; }
        return '<ul class="ac-lista">' + lista.map(function (a) {
            var estado = a.completa ? '<span class="is-ok">Todos llegaron</span>' : 'Llegaron ' + a.llegaron + ' de ' + a.total;
            var nue = a.url ? '<a href="' + esc(a.url) + '">' + esc(a.nue) + '</a>' : '<span>' + esc(a.nue) + '</span>';
            return '<li><b>' + esc(a.hora) + '</b>' + nue + '<span class="ac-lista__meta">' + esc(a.lugar) + ' · ' + estado +
                (a.pasada ? ' · <span class="is-tarde">pasó su hora</span>' : '') + '</span></li>';
        }).join('') + '</ul>';
    }

    function pintar(j) {
        document.getElementById('acSiguienteCuerpo').innerHTML = siguiente(j.siguiente);
        document.getElementById('acDia').innerHTML = dia(j.dia);
        document.getElementById('acSiguen').innerHTML = siguen(j.siguen || []);
    }

    pintar(@json($conciliador['hoy']));
    setInterval(function () {
        fetch(URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) { if (j) { pintar(j); } })
            .catch(function () {});
    }, 30000);
})();
</script>
