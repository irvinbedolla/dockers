<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('partials.favicon')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sala de espera · {{ $sede }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
{{--
    Pantalla de sala de espera (PantallaEsperaController / App\Support\PantallaEspera).

    Pensada para una tele de 16:9 vista a varios metros, siguiendo lo usual en
    tableros de turnos:
      - todo se escala con el alto de la pantalla (1rem = 1/72 del alto), así
        que se ve igual en 720p, 1080p o 4K y nunca hay scroll;
      - letra grande, alto contraste y el número que importa (consecutivo del
        folio y número de módulo) es lo más grande;
      - un tablero fijo "turno en módulo" a la izquierda y, a la derecha,
        paneles que rotan solos (próximas citas, audiencias, cumplimientos)
        con barra de progreso para saber cuándo cambian;
      - cuando un módulo cambia de turno la tarjeta destella y, si alguien tocó
        la pantalla una vez, suena un aviso y se anuncia por voz;
      - sin nombres completos: sólo el primero y las iniciales;
      - se actualiza sola cada 15 s sin recargar, avisa si se perdió la
        conexión y se recarga sola al cambiar el día.
--}}
<style>
    :root {
        --pe-fondo: #EEF2F2;
        --pe-blanco: #FFFFFF;
        --pe-tinta: #1F2A2B;
        --pe-suave: #5E6E6F;
        --pe-tenue: #8A9899;
        --pe-borde: #DCE3E3;
        --pe-teal: #354647;
        --pe-teal-2: #496163;
        --pe-teal-3: #829A9C;
        --pe-dorado: #CEA845;
        --pe-dorado-suave: #FBF4E1;
        --pe-dorado-tinta: #7A5D10;
        --pe-verde: #1E7B3C;
        --pe-verde-suave: #E5F3EA;
    }

    * { box-sizing: border-box; }

    /* Todo en rem y 1rem = 1/72 del alto: 15px en 1080p. */
    html { font-size: calc(100vh / 72); }

    html, body { height: 100%; margin: 0; }

    body {
        font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
        background: var(--pe-fondo);
        color: var(--pe-tinta);
        overflow: hidden;
        display: grid;
        grid-template-rows: auto 1fr auto;
        font-variant-numeric: tabular-nums;
        -webkit-font-smoothing: antialiased;
    }
    body.sin-cursor, body.sin-cursor * { cursor: none !important; }

    /* ---------------- cabecera ---------------- */
    .pe-cabeza {
        display: flex;
        align-items: center;
        gap: 2.4rem;
        padding: 1.4rem 2.6rem;
        background: var(--pe-blanco);
        border-bottom: .35rem solid var(--pe-dorado);
    }
    .pe-logo { height: 5.4rem; width: auto; display: block; }
    .pe-separador { width: .15rem; align-self: stretch; background: var(--pe-borde); }
    .pe-sede { font-size: 2.9rem; font-weight: 800; color: var(--pe-teal); line-height: 1.05; }
    .pe-centro { font-size: 1.35rem; font-weight: 600; color: var(--pe-suave); margin-top: .3rem; }
    .pe-reloj { margin-left: auto; text-align: right; }
    .pe-reloj__hora { font-size: 4.6rem; font-weight: 800; color: var(--pe-teal); line-height: 1; letter-spacing: -.02em; }
    .pe-reloj__hora small { font-size: 1.8rem; font-weight: 700; margin-left: .4rem; color: var(--pe-teal-2); }
    .pe-reloj__fecha { font-size: 1.4rem; font-weight: 600; color: var(--pe-suave); margin-top: .35rem; }

    /* ---------------- cuerpo ---------------- */
    .pe-cuerpo {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 1.8rem;
        padding: 1.8rem 2.6rem;
        min-height: 0;
    }

    .pe-bloque { display: flex; flex-direction: column; min-height: 0; }
    .pe-bloque__titulo {
        display: flex; align-items: center; gap: 1rem;
        font-size: 1.55rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
        color: var(--pe-teal-2); margin: 0 0 1.1rem;
    }
    .pe-bloque__titulo::before {
        content: ''; width: .55rem; height: 1.9rem; border-radius: .3rem; background: var(--pe-dorado);
    }

    /* --- tablero de módulos --- */
    .pe-modulos {
        flex: 1;
        display: grid;
        gap: 1.4rem;
        min-height: 0;
        grid-template-columns: repeat(var(--cols, 2), minmax(0, 1fr));
        grid-auto-rows: minmax(0, 1fr);
    }

    .pe-mod {
        position: relative;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        grid-template-rows: 1fr auto;
        column-gap: 1.8rem;
        background: var(--pe-blanco);
        border: .15rem solid var(--pe-borde);
        border-radius: 1.4rem;
        padding: 1.4rem 1.8rem;
        min-height: 0;
        overflow: hidden;
    }

    .pe-mod__num {
        grid-row: 1 / 3;
        align-self: stretch;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        width: 10.5rem;
        padding: 0 .8rem;
        border-radius: 1rem;
        background: var(--pe-teal);
        color: #fff;
    }
    .pe-mod__num small { font-size: 1.15rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; opacity: .8; }
    .pe-mod__num em {
        font-style: normal; font-size: 1rem; font-weight: 600; line-height: 1.2;
        text-align: center; opacity: .85; margin-top: .5rem; max-width: 8rem;
    }
    .pe-mod__num strong { font-size: 6.6rem; font-weight: 800; line-height: .95; }

    .pe-mod__centro { align-self: center; min-width: 0; }
    .pe-folio__pre { font-size: 2.1rem; font-weight: 700; color: var(--pe-teal-3); white-space: nowrap; letter-spacing: .02em; }
    .pe-folio__num {
        display: block; font-size: 7.4rem; font-weight: 800; color: var(--pe-tinta);
        letter-spacing: -.02em; line-height: .95; white-space: nowrap;
    }
    .pe-mod__meta { display: flex; flex-wrap: wrap; align-items: center; gap: .7rem; margin-top: .6rem; }
    .pe-mod__vacio { font-size: 2rem; font-weight: 700; color: var(--pe-teal-3); margin-top: .6rem; }

    .pe-mod__sigue {
        align-self: end;
        display: flex; align-items: center; gap: .8rem;
        margin-top: .9rem; padding-top: .8rem;
        border-top: .12rem dashed var(--pe-borde);
        font-size: 1.35rem; color: var(--pe-suave); white-space: nowrap; overflow: hidden;
    }
    .pe-mod__sigue b { color: var(--pe-tinta); font-weight: 800; }

    /* Tableros de dos columnas (3 a 6 módulos): se aprieta un poco todo. */
    .pe-modulos.is-doble .pe-mod { padding: 1.1rem 1.4rem; column-gap: 1.4rem; }
    .pe-modulos.is-doble .pe-mod__num { width: 9.6rem; }
    .pe-modulos.is-doble .pe-mod__num strong { font-size: 5.6rem; }
    .pe-modulos.is-doble .pe-folio__pre { font-size: 1.9rem; }
    .pe-modulos.is-doble .pe-folio__num { font-size: 6.2rem; }
    .pe-modulos.is-doble .pe-mod__sigue { font-size: 1.25rem; gap: .5rem; margin-top: .5rem; padding-top: .5rem; }
    .pe-modulos.is-doble .pe-chip { font-size: 1.05rem; padding: .25rem .75rem; }
    .pe-modulos[data-n="5"] .pe-folio__num, .pe-modulos[data-n="6"] .pe-folio__num { font-size: 5.4rem; }
    /* El último de un número impar ocupa todo el renglón. */
    .pe-modulos.is-doble .pe-mod:last-child:nth-child(odd) { grid-column: 1 / -1; }

    /* Cambio de turno: destello dorado ~10 s. */
    .pe-mod.is-nuevo { animation: pe-llamado 1.4s ease-in-out 7; border-color: var(--pe-dorado); }
    .pe-mod.is-nuevo .pe-mod__num { background: var(--pe-dorado); color: var(--pe-teal); }
    @keyframes pe-llamado {
        0%, 100% { box-shadow: 0 0 0 0 rgba(206, 168, 69, .0); }
        50%      { box-shadow: 0 0 0 .7rem rgba(206, 168, 69, .45); }
    }

    /* --- chips --- */
    .pe-chip {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .3rem .9rem; border-radius: 99rem;
        font-size: 1.15rem; font-weight: 700; white-space: nowrap;
        background: #EEF2F2; color: var(--pe-teal-2);
    }
    .pe-chip--linea { background: var(--pe-dorado-suave); color: var(--pe-dorado-tinta); }
    .pe-chip--sala { background: var(--pe-verde-suave); color: var(--pe-verde); }
    .pe-hora { font-size: 1.5rem; font-weight: 800; color: var(--pe-teal); }

    /* --- panel que rota --- */
    .pe-lateral {
        display: flex; flex-direction: column; min-height: 0;
        background: var(--pe-blanco);
        border: .15rem solid var(--pe-borde);
        border-radius: 1.4rem;
        overflow: hidden;
    }
    .pe-pestanas { display: flex; border-bottom: .15rem solid var(--pe-borde); }
    .pe-pestana {
        flex: 1 1 auto; position: relative;
        padding: 1.3rem .6rem 1.2rem;
        text-align: center; white-space: nowrap;
        font-size: 1.2rem; font-weight: 800; letter-spacing: .03em; text-transform: uppercase;
        color: var(--pe-tenue);
    }
    .pe-pestana b { display: inline-block; min-width: 2.1rem; margin-left: .4rem; padding: .05rem .5rem; border-radius: 99rem; background: #EEF2F2; color: var(--pe-suave); font-size: 1.1rem; }
    .pe-pestana.is-activa { color: var(--pe-teal); }
    .pe-pestana.is-activa b { background: var(--pe-teal); color: #fff; }
    .pe-pestana__barra {
        position: absolute; left: 0; bottom: -.15rem; height: .35rem; width: 0;
        background: var(--pe-dorado);
    }
    .pe-pestana.is-activa .pe-pestana__barra { animation: pe-progreso var(--dur, 10s) linear forwards; }
    @keyframes pe-progreso { from { width: 0; } to { width: 100%; } }

    .pe-lista { flex: 1; display: flex; flex-direction: column; padding: .6rem 1.6rem 1rem; min-height: 0; }
    .pe-lista.is-entrando { animation: pe-aparece .5s ease-out; }
    @keyframes pe-aparece { from { opacity: 0; transform: translateY(.6rem); } to { opacity: 1; transform: none; } }

    .pe-fila {
        display: grid;
        grid-template-columns: 6.2rem minmax(0, 1fr) auto;
        align-items: center;
        gap: 1.2rem;
        padding: .95rem .2rem;
        border-bottom: .12rem solid #EDF1F1;
        min-height: 0;
    }
    .pe-fila:last-child { border-bottom: 0; }
    .pe-fila__principal { min-width: 0; }
    .pe-fila__folio { font-size: 2rem; font-weight: 800; color: var(--pe-tinta); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pe-fila__folio span { color: var(--pe-teal-3); font-weight: 700; font-size: 1.5rem; }
    .pe-fila__sub { font-size: 1.25rem; color: var(--pe-suave); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: .15rem; }
    .pe-fila__lado { text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: .35rem; }
    .pe-fila__lugar { font-size: 1.5rem; font-weight: 800; color: var(--pe-teal); max-width: 15rem; line-height: 1.2; }

    .pe-vacio {
        flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .6rem;
        text-align: center; color: var(--pe-tenue);
    }
    .pe-vacio strong { font-size: 2rem; color: var(--pe-teal-3); }
    .pe-vacio span { font-size: 1.35rem; }

    .pe-paginas { display: flex; justify-content: center; gap: .6rem; padding: 0 0 1.1rem; }
    .pe-paginas i { width: .8rem; height: .8rem; border-radius: 50%; background: var(--pe-borde); }
    .pe-paginas i.is-activa { background: var(--pe-teal-2); }

    /* ---------------- pie ---------------- */
    .pe-pie {
        display: flex; align-items: center; gap: 2rem;
        padding: 1.05rem 2.6rem;
        background: var(--pe-teal);
        color: #fff;
        font-size: 1.45rem;
    }
    .pe-aviso { flex: 1; display: flex; align-items: center; gap: 1rem; min-width: 0; font-weight: 600; }
    .pe-aviso__icono {
        flex: 0 0 auto; width: 2.6rem; height: 2.6rem; border-radius: 50%;
        display: grid; place-items: center; background: var(--pe-dorado); color: var(--pe-teal); font-weight: 800;
    }
    .pe-aviso__texto { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: opacity .5s; }
    .pe-cifras { display: flex; gap: 1.8rem; white-space: nowrap; color: #D7E0E0; }
    .pe-cifras b { color: #fff; font-weight: 800; }
    .pe-estado { display: flex; align-items: center; gap: .6rem; white-space: nowrap; color: #D7E0E0; font-size: 1.2rem; }
    .pe-estado i { width: .9rem; height: .9rem; border-radius: 50%; background: #5CC985; }
    .pe-estado.is-sin-red i { background: #E8A33D; animation: pe-parpadeo 1s infinite; }
    @keyframes pe-parpadeo { 50% { opacity: .3; } }

    .pe-sonido {
        position: fixed; right: 2rem; bottom: 6.2rem;
        padding: .8rem 1.4rem; border-radius: 99rem;
        background: rgba(31, 42, 43, .85); color: #fff; font-size: 1.2rem; font-weight: 600;
        transition: opacity .6s;
    }
    .pe-sonido[hidden] { display: none; }

    @media (prefers-reduced-motion: reduce) {
        .pe-mod.is-nuevo, .pe-lista.is-entrando { animation: none; }
    }
</style>
</head>
<body>
    <header class="pe-cabeza">
        <img class="pe-logo" src="{{ asset('assets/images/Logo pantalla.png') }}" alt="SíConcilio">
        <span class="pe-separador" aria-hidden="true"></span>
        <div>
            <div class="pe-sede">Sede {{ $sede }}</div>
            <div class="pe-centro">Centro de Conciliación Laboral del Estado de Michoacán</div>
        </div>
        <div class="pe-reloj" aria-live="off">
            <div class="pe-reloj__hora" id="pe-hora">--:--</div>
            <div class="pe-reloj__fecha" id="pe-fecha"></div>
        </div>
    </header>

    <main class="pe-cuerpo">
        <section class="pe-bloque" aria-labelledby="pe-t-modulos">
            <h1 class="pe-bloque__titulo" id="pe-t-modulos">Turno en módulo</h1>
            <div class="pe-modulos" id="pe-modulos" aria-live="polite"></div>
        </section>

        <section class="pe-lateral" aria-label="Información de la sede">
            <div class="pe-pestanas" id="pe-pestanas"></div>
            <div class="pe-lista" id="pe-lista"></div>
            <div class="pe-paginas" id="pe-paginas"></div>
        </section>
    </main>

    <footer class="pe-pie">
        <div class="pe-aviso">
            <span class="pe-aviso__icono" aria-hidden="true">i</span>
            <span class="pe-aviso__texto" id="pe-aviso"></span>
        </div>
        <div class="pe-cifras" id="pe-cifras"></div>
        <div class="pe-estado" id="pe-estado"><i></i><span>En línea</span></div>
    </footer>

    <div class="pe-sonido" id="pe-sonido">Toque la pantalla para activar los avisos con sonido</div>

<script>
(function () {
    'use strict';

    var URL_DATOS   = @json($urlDatos);
    var ZONA        = @json(config('app.timezone'));
    var CADA_DATOS  = 15000;  // ms entre actualizaciones
    var CADA_PAGINA = 10000;  // ms que se ve cada página del panel derecho
    var FILAS       = 7;      // renglones por página del panel derecho

    var AVISOS = [
        'Todos los servicios del Centro de Conciliación Laboral son gratuitos.',
        'Si agendó su cita en línea, muestre el código QR de su acuse en recepción.',
        'Las citas tienen 5 minutos de tolerancia. Después de ese tiempo deberá agendar de nuevo.',
        'Tenga a la mano su identificación oficial vigente.',
        'Cuando su folio aparezca en un módulo, acuda a él.',
        'Nadie puede cobrarle por un trámite ni por agendar una cita.'
    ];

    var datos = @json($datos);
    var desfase = 0;          // reloj del servidor menos el de la tele
    var diaCargado = null;
    var turnoPorModulo = {};  // para saber qué módulo cambió de turno
    var sonidoActivo = false;

    function $(id) { return document.getElementById(id); }

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // ---------------------------------------------------------------- reloj
    // La hora sale del servidor (desfase) y se pinta en la zona de la app:
    // así no importa si la tele tiene mal la hora o la zona.
    function formato(opciones) {
        try { return new Intl.DateTimeFormat('es-MX', Object.assign({ timeZone: ZONA }, opciones)); }
        catch (e) { return new Intl.DateTimeFormat('es-MX', opciones); }
    }
    var fmtHora  = formato({ hour: 'numeric', minute: '2-digit', hour12: true });
    var fmtFecha = formato({ weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    var fmtDia   = formato({ year: 'numeric', month: '2-digit', day: '2-digit' });

    function ahora() { return new Date(Date.now() + desfase); }

    function pintarReloj() {
        var d = ahora();
        var partes = fmtHora.formatToParts(d);
        var hm = '', periodo = '';
        partes.forEach(function (p) {
            if (p.type === 'hour' || p.type === 'minute' || p.type === 'literal' && !periodo && p.value.trim() === ':') { hm += p.value; }
            if (p.type === 'dayPeriod') { periodo = p.value; }
        });
        $('pe-hora').innerHTML = esc(hm) + '<small>' + esc(periodo) + '</small>';
        var f = fmtFecha.format(d).replace(',', '');
        $('pe-fecha').textContent = f.charAt(0).toUpperCase() + f.slice(1);

        // Al cambiar el día se recarga todo: limpia memoria y toma cambios del sistema.
        var dia = fmtDia.format(d);
        if (diaCargado && dia !== diaCargado) { location.reload(); }
        diaCargado = diaCargado || dia;
    }

    // ---------------------------------------------------------------- módulos
    function chipsCita(c) {
        var html = '<span class="pe-hora">' + esc(c.hora) + '</span>' +
                   '<span class="pe-chip">' + esc(c.tipo) + '</span>';
        if (c.enLinea) { html += '<span class="pe-chip pe-chip--linea">En línea</span>'; }
        return html;
    }

    function pintarModulos(llamados) {
        var cont = $('pe-modulos');
        var mods = datos.modulos || [];
        var n = mods.length;

        var cols = n <= 2 ? 1 : (n <= 6 ? 2 : 3);
        cont.dataset.n = n;
        cont.classList.toggle('is-doble', cols > 1);
        cont.style.setProperty('--cols', cols);

        if (!n) {
            cont.innerHTML = '<div class="pe-vacio" style="grid-column:1/-1"><strong>Sin módulos activos</strong>' +
                '<span>Consulte en recepción.</span></div>';
            return;
        }

        cont.innerHTML = mods.map(function (m) {
            var a = m.actual, s = m.siguiente;
            var centro = a
                ? '<span class="pe-folio__pre">' + esc(a.prefijo) + '</span>' +
                  '<span class="pe-folio__num">' + esc(a.numero) + '</span>' +
                  '<div class="pe-mod__meta">' + chipsCita(a) + '</div>'
                : '<div class="pe-mod__vacio">Sin turno en este momento</div>';
            var sigue = s
                ? 'Sigue <b>' + esc(s.folio) + '</b> · ' + esc(s.hora)
                : 'Sin más citas por ahora';

            return '<article class="pe-mod' + (llamados.indexOf(m.id) !== -1 ? ' is-nuevo' : '') + '">' +
                '<div class="pe-mod__num"><small>Módulo</small><strong>' + esc(m.numero || '·') + '</strong>' +
                    (m.tramites ? '<em>' + esc(m.tramites) + '</em>' : '') + '</div>' +
                '<div class="pe-mod__centro">' + centro + '</div>' +
                '<div class="pe-mod__sigue">' + sigue + '</div>' +
            '</article>';
        }).join('');
    }

    // Qué módulos cambiaron de turno desde la última vez.
    function detectarLlamados(primeraVez) {
        var llamados = [];
        (datos.modulos || []).forEach(function (m) {
            var id = m.actual ? m.actual.id : null;
            if (!primeraVez && id && turnoPorModulo[m.id] !== id) { llamados.push(m.id); }
            turnoPorModulo[m.id] = id;
        });
        return llamados;
    }

    // ---------------------------------------------------------------- panel derecho
    var PANELES = [
        { clave: 'proximas',      titulo: 'Próximas citas', vacio: ['No hay más citas por ahora', 'Las nuevas aparecerán aquí.'], siempre: true },
        { clave: 'audiencias',    titulo: 'Audiencias',     vacio: ['Sin audiencias pendientes', ''] },
        { clave: 'cumplimientos', titulo: 'Cumplimientos',  vacio: ['Sin cumplimientos pendientes', ''] }
    ];

    function filaHtml(clave, x) {
        if (clave === 'proximas') {
            return '<div class="pe-fila">' +
                '<span class="pe-hora">' + esc(x.hora) + '</span>' +
                '<div class="pe-fila__principal">' +
                    '<div class="pe-fila__folio"><span>' + esc(x.prefijo) + '</span>' + esc(x.numero) + '</div>' +
                    '<div class="pe-fila__sub">' + esc(x.tipo) + (x.enLinea ? ' · En línea' : '') + '</div>' +
                '</div>' +
                '<div class="pe-fila__lado"><span class="pe-fila__lugar">' + esc(x.modulo || 'Recepción') + '</span></div>' +
            '</div>';
        }
        if (clave === 'audiencias') {
            return '<div class="pe-fila">' +
                '<span class="pe-hora">' + esc(x.hora) + '</span>' +
                '<div class="pe-fila__principal">' +
                    '<div class="pe-fila__folio">' + esc(x.expediente) + '</div>' +
                    '<div class="pe-fila__sub">' + esc(x.solicitante) + (x.conciliador ? ' · ' + esc(x.conciliador) : '') + '</div>' +
                '</div>' +
                '<div class="pe-fila__lado"><span class="pe-chip pe-chip--sala">' + esc(x.sala || 'Sala') + '</span></div>' +
            '</div>';
        }
        return '<div class="pe-fila">' +
            '<span class="pe-hora">' + esc(x.hora) + '</span>' +
            '<div class="pe-fila__principal">' +
                '<div class="pe-fila__folio">' + esc(x.expediente) + '</div>' +
                '<div class="pe-fila__sub">' + esc(x.nombre) + '</div>' +
            '</div>' +
            '<div class="pe-fila__lado"><span class="pe-chip">' + esc(x.origen) + '</span></div>' +
        '</div>';
    }

    // La secuencia de páginas: cada panel con datos aporta sus páginas;
    // "Próximas citas" siempre está aunque vaya vacía.
    function secuencia() {
        var paginas = [];
        PANELES.forEach(function (p, i) {
            var lista = datos[p.clave] || [];
            if (!lista.length && !p.siempre) { return; }
            var total = Math.max(1, Math.ceil(lista.length / FILAS));
            for (var k = 0; k < total; k++) { paginas.push({ panel: i, pagina: k, total: total }); }
        });
        return paginas;
    }

    var paso = 0;

    function pintarPanel(animar) {
        var paginas = secuencia();
        if (paso >= paginas.length) { paso = 0; }
        var actual = paginas[paso];
        var p = PANELES[actual.panel];
        var lista = datos[p.clave] || [];

        $('pe-pestanas').innerHTML = PANELES.map(function (q, i) {
            var cuantos = (datos[q.clave] || []).length;
            return '<div class="pe-pestana' + (i === actual.panel ? ' is-activa' : '') + '" style="--dur:' + CADA_PAGINA + 'ms">' +
                esc(q.titulo) + '<b>' + cuantos + '</b><span class="pe-pestana__barra"></span></div>';
        }).join('');

        var cuerpo = $('pe-lista');
        var trozo = lista.slice(actual.pagina * FILAS, (actual.pagina + 1) * FILAS);
        cuerpo.innerHTML = trozo.length
            ? trozo.map(function (x) { return filaHtml(p.clave, x); }).join('')
            : '<div class="pe-vacio"><strong>' + esc(p.vacio[0]) + '</strong><span>' + esc(p.vacio[1]) + '</span></div>';

        if (animar) {
            cuerpo.classList.remove('is-entrando');
            void cuerpo.offsetWidth;
            cuerpo.classList.add('is-entrando');
        }

        var puntos = '';
        for (var k = 0; k < actual.total && actual.total > 1; k++) {
            puntos += '<i class="' + (k === actual.pagina ? 'is-activa' : '') + '"></i>';
        }
        $('pe-paginas').innerHTML = puntos;
    }

    function siguientePagina() {
        paso++;
        pintarPanel(true);
    }

    // ---------------------------------------------------------------- pie
    function pintarCifras() {
        var c = datos.cifras || {};
        $('pe-cifras').innerHTML =
            '<span>Atendidos hoy <b>' + (c.atendidos || 0) + '</b></span>' +
            '<span>En sala <b>' + (c.enEspera || 0) + '</b></span>' +
            '<span>Por llegar <b>' + (c.porLlegar || 0) + '</b></span>';
    }

    var aviso = 0;
    function rotarAviso() {
        var t = $('pe-aviso');
        t.style.opacity = 0;
        setTimeout(function () {
            t.textContent = AVISOS[aviso++ % AVISOS.length];
            t.style.opacity = 1;
        }, 500);
    }

    function estadoRed(bien) {
        var e = $('pe-estado');
        e.classList.toggle('is-sin-red', !bien);
        e.querySelector('span').textContent = bien ? 'En línea' : 'Reconectando…';
    }

    // ---------------------------------------------------------------- sonido y voz
    function campana() {
        try {
            var ctx = campana.ctx || (campana.ctx = new (window.AudioContext || window.webkitAudioContext)());
            [880, 1320].forEach(function (f, i) {
                var o = ctx.createOscillator(), g = ctx.createGain();
                var t0 = ctx.currentTime + i * 0.28;
                o.type = 'sine'; o.frequency.value = f;
                g.gain.setValueAtTime(0.0001, t0);
                g.gain.exponentialRampToValueAtTime(0.35, t0 + 0.02);
                g.gain.exponentialRampToValueAtTime(0.0001, t0 + 0.6);
                o.connect(g); g.connect(ctx.destination);
                o.start(t0); o.stop(t0 + 0.65);
            });
        } catch (e) { /* sin audio */ }
    }

    var DIGITOS = ['cero', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];

    function anunciar(llamados) {
        if (!sonidoActivo || !llamados.length) { return; }
        campana();
        if (!('speechSynthesis' in window)) { return; }
        (datos.modulos || []).forEach(function (m) {
            if (llamados.indexOf(m.id) === -1 || !m.actual) { return; }
            var numero = String(m.actual.numero).split('').map(function (d) { return DIGITOS[+d] || d; }).join(', ');
            var u = new SpeechSynthesisUtterance('Turno ' + numero + '. Pase al módulo ' + m.numero + '.');
            u.lang = 'es-MX';
            u.rate = 0.9;
            setTimeout(function () { window.speechSynthesis.speak(u); }, 900);
        });
    }

    function activarSonido() {
        sonidoActivo = true;
        campana();
        var aviso = $('pe-sonido');
        aviso.textContent = 'Avisos con sonido activados';
        setTimeout(function () { aviso.hidden = true; }, 2500);
        try { localStorage.setItem('pantalla-sonido', '1'); } catch (e) { /* sin almacenamiento */ }
    }
    document.addEventListener('click', function () { if (!sonidoActivo) { activarSonido(); } });
    try { if (localStorage.getItem('pantalla-sonido') === '1') { $('pe-sonido').textContent = 'Toque la pantalla para reactivar el sonido'; } } catch (e) {}
    setTimeout(function () { if (!sonidoActivo) { $('pe-sonido').style.opacity = .35; } }, 20000);

    // ---------------------------------------------------------------- datos
    function aplicar(nuevos, primeraVez) {
        datos = nuevos;
        if (datos.generado) { desfase = new Date(datos.generado).getTime() - Date.now(); }
        var llamados = detectarLlamados(primeraVez);
        pintarModulos(llamados);
        pintarPanel(false);
        pintarCifras();
        anunciar(llamados);
    }

    function actualizar() {
        fetch(URL_DATOS, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
            .then(function (j) { estadoRed(true); aplicar(j, false); })
            .catch(function () { estadoRed(false); });
    }

    // ---------------------------------------------------------------- arranque
    aplicar(datos, true);
    pintarReloj();
    rotarAviso();

    setInterval(pintarReloj, 1000);
    setInterval(actualizar, CADA_DATOS);
    setInterval(siguientePagina, CADA_PAGINA);
    setInterval(rotarAviso, 12000);

    // Que la tele no se apague mientras la pantalla esté visible.
    function mantenerEncendida() {
        if (navigator.wakeLock && document.visibilityState === 'visible') {
            navigator.wakeLock.request('screen').catch(function () {});
        }
    }
    mantenerEncendida();
    document.addEventListener('visibilitychange', mantenerEncendida);

    // El cursor estorba en una tele: se esconde a los 3 s sin moverse.
    var reposo;
    document.addEventListener('mousemove', function () {
        document.body.classList.remove('sin-cursor');
        clearTimeout(reposo);
        reposo = setTimeout(function () { document.body.classList.add('sin-cursor'); }, 3000);
    });
    document.body.classList.add('sin-cursor');
})();
</script>
</body>
</html>
