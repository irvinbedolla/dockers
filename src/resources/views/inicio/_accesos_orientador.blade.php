{{--
    Datos rápidos de quien atiende un módulo (orientadores y auxiliares), en
    la tarjeta del saludo del Inicio. Los datos los arma
    App\Support\AccesosOrientador::para(): quién sigue, sus citas de hoy,
    las próximas y sus solicitudes.

    "Quién sigue" se pinta con JavaScript y se actualiza cada 30 s con
    /mi-modulo/avisos (lo mismo que el aviso flotante, que en el Inicio no
    sale para no repetirlo).

    Espera $orientador.
--}}
@include('inicio._accesos_estilos')
@php $o = $orientador; @endphp
<style>
    .ao-sigue.is-pronto { border-color: #E6C766; background: #FFFBEF; box-shadow: 0 0 0 3px rgba(206, 168, 69, .15); }
    .ao-folio { font-size: 24px; font-weight: 800; color: #2E3C3D; line-height: 1.05; letter-spacing: -.01em; }
    .ao-nombre { font-size: 14px; font-weight: 700; color: #2E3C3D; overflow-wrap: anywhere; }
    .ao-linea { font-size: 12px; color: #5E6E6F; line-height: 1.35; }
    .ao-linea.is-excedido { color: #9A2B1C; }
    .ar-chip.is-sala { background: #E5F3EA; border-color: #BFE0C9; color: #1E7B3C; }
    .ar-chip.is-pronto { background: #FBF4E1; border-color: #F0DFA8; color: #7A5D10; }
    .ao-lista { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
    .ao-lista li { display: grid; grid-template-columns: auto 1fr; gap: 0 8px; align-items: baseline; font-size: 12.5px; color: #5E6E6F; }
    .ao-lista b { color: #2E3C3D; font-variant-numeric: tabular-nums; }
    .ao-lista .ao-lista__nombre { color: #2E3C3D; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ao-lista .ao-lista__meta { grid-column: 2; font-size: 11.5px; color: #8A9899; }
    .ao-boton-oro { background: #CEA845; }
    .ao-boton-oro:hover, .ao-boton-oro:focus { background: #B8952F; }
    .ao-boton-form { display: inline; margin: 0; }
    .ao-boton-form button { border: 0; }
</style>

<div class="ar">
    {{-- 1. Quién sigue (se pinta y actualiza con JS) --}}
    <div class="ar-tarjeta ao-sigue" id="aoSigue" aria-live="polite">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-person-lines-fill" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Quién sigue <span class="ar-sub" id="aoSigueModulo">Tu módulo</span></h3>
        </div>
        <div id="aoSigueCuerpo"><span class="ar-nota">Cargando…</span></div>
        <a class="ar-pie" href="{{ $o['urls']['modulo'] }}">Ir a Mi módulo <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 2. Mis citas de hoy --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Mis citas de hoy <span class="ar-sub">{{ \Illuminate\Support\Str::ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM')) }}</span></h3>
        </div>
        <div class="ar-cifra">{{ number_format($o['hoy']['total']) }}</div>
        <div class="ar-chips">
            <span class="ar-chip">Atendidas <b>{{ $o['hoy']['atendidas'] }}</b></span>
            <span class="ar-chip {{ $o['hoy']['enSala'] ? 'is-sala' : '' }}">En sala <b>{{ $o['hoy']['enSala'] }}</b></span>
            <span class="ar-chip">Por llegar <b>{{ $o['hoy']['porLlegar'] }}</b></span>
            @if ($o['hoy']['ausentes'])
                <span class="ar-chip">No se presentaron <b>{{ $o['hoy']['ausentes'] }}</b></span>
            @endif
        </div>
        @if ($o['hoy']['promedio'] !== null)
            <span class="ao-linea {{ $o['hoy']['promedio'] > $o['hoy']['duracion'] ? 'is-excedido' : '' }}">
                Atiendes en <b>{{ $o['hoy']['promedio'] }} min</b> en promedio · cada cita dura {{ $o['hoy']['duracion'] }} min
            </span>
        @else
            <span class="ar-nota">Cada cita dura {{ $o['hoy']['duracion'] }} min (Horarios de citas).</span>
        @endif
        <a class="ar-pie" href="{{ $o['urls']['modulo'] }}">Ver mis citas <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 3. Próximas citas --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Próximas citas <span class="ar-sub">De tu módulo, por hora</span></h3>
        </div>
        @if ($o['proximas'])
            <ul class="ao-lista">
                @foreach ($o['proximas'] as $p)
                    <li>
                        <b>{{ $p['hora'] }}</b>
                        <span class="ao-lista__nombre" title="{{ $p['persona'] }}">{{ $p['persona'] }}</span>
                        <span class="ao-lista__meta">{{ $p['folio'] }} · {{ $p['tipo'] }}@if ($p['enSala']) · <span style="color:#1E7B3C;font-weight:700;">ya llegó</span>@endif</span>
                    </li>
                @endforeach
            </ul>
        @else
            <span class="ar-nota">No hay más citas por hoy.</span>
        @endif
        <a class="ar-pie" href="{{ $o['urls']['modulo'] }}">Ver la lista del día <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 4. Mis solicitudes --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Mis solicitudes <span class="ar-sub">Capturadas por ti hoy</span></h3>
        </div>
        <div class="ar-cifra">{{ number_format($o['solicitudes']['hoy']) }} <small>{{ number_format($o['solicitudes']['mes']) }} en el mes</small></div>
        @if ($o['solicitudes']['prellenado'] && $o['solicitudes']['crear'])
            <div class="ar-botones">
                <a class="ar-boton ao-boton-oro" href="{{ $o['solicitudes']['crear'] }}"><i class="bi bi-magic" aria-hidden="true"></i> Continuar {{ $o['solicitudes']['prellenado']['folio'] }}</a>
            </div>
            @if ($o['solicitudes']['prellenado']['persona'])
                <span class="ar-nota">{{ \Illuminate\Support\Str::title(mb_strtolower($o['solicitudes']['prellenado']['persona'])) }}, con datos de su cita.</span>
            @endif
        @elseif ($o['solicitudes']['crear'])
            <div class="ar-botones">
                <a class="ar-boton" href="{{ $o['solicitudes']['crear'] }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva solicitud</a>
            </div>
        @endif
        <a class="ar-pie" href="{{ route('solicitudes_index') }}">Ir a Solicitudes <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
</div>

<script>
(function () {
    var URL = @json($o['urls']['avisos']);
    var TOKEN = @json(csrf_token());
    var tarjeta = document.getElementById('aoSigue');
    var cuerpo = document.getElementById('aoSigueCuerpo');
    var etiqueta = document.getElementById('aoSigueModulo');
    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    function pintar(j) {
        var mods = j.modulos || [];
        // El módulo con alguien que sigue más pronto; si nadie, el primero.
        var m = mods.filter(function (x) { return x.siguiente; })
            .sort(function (a, b) { return a.siguiente.minutos - b.siguiente.minutos; })[0] || mods[0];
        if (!m) { cuerpo.innerHTML = '<span class="ar-nota">Sin módulo activo.</span>'; return; }
        etiqueta.textContent = m.modulo;
        var s = m.siguiente, a = m.actual, h = '';
        var pronto = s && s.minutos <= (j.minutos || 10);
        tarjeta.classList.toggle('is-pronto', !!pronto);
        if (s) {
            h += '<div class="ao-folio">' + esc(s.folio) + '</div>' +
                '<div class="ao-nombre">' + esc(s.persona) + '</div>' +
                '<div class="ar-chips" style="margin-top:6px">' +
                    (s.enSala ? '<span class="ar-chip is-sala">Ya llegó</span>' : '<span class="ar-chip">Aún no llega</span>') +
                    '<span class="ar-chip' + (pronto ? ' is-pronto' : '') + '">' +
                        (s.minutos > 0 ? 'En <b>' + s.minutos + ' min</b> · ' + esc(s.empieza) : '<b>Ya le toca</b>') + '</span>' +
                    (s.prueba ? '<span class="ar-chip">Prueba</span>' : '') +
                '</div>';
        } else {
            h += '<span class="ar-nota">No hay nadie en espera por ahora.</span>';
        }
        if (a) {
            h += '<div class="ao-linea' + (a.excedido > 0 ? ' is-excedido' : '') + '" style="margin-top:6px">Ahora: <b>' + esc(a.folio) + '</b> · ' +
                (a.excedido > 0 ? 'lleva ' + a.lleva + ' min (' + a.excedido + ' más de lo planeado)' : 'termina aprox. ' + esc(a.termina)) + '</div>';
        }
        if (m.antes) {
            h += '<div class="ao-linea">Antes tenía cita ' + esc(m.antes.folio) + ' (' + esc(m.antes.hora) + ') y aún no llega.</div>';
        }
        if (s && s.iniciar) {
            h += '<div class="ar-botones" style="margin-top:8px"><form method="POST" action="' + esc(s.iniciar) + '" class="ao-boton-form">' +
                '<input type="hidden" name="_token" value="' + esc(TOKEN) + '">' +
                '<button type="submit" class="ar-boton ao-boton-oro"><i class="bi bi-magic" aria-hidden="true"></i> Preparar solicitud</button></form></div>';
        }
        cuerpo.innerHTML = h;
    }

    pintar({ minutos: @json($o['minutos']), modulos: @json($o['sigue']) });
    setInterval(function () {
        fetch(URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) { if (j) { pintar(j); } })
            .catch(function () {});
    }, 30000);
})();
</script>
