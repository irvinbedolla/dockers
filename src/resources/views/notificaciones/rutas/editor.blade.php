@extends('layouts.app')
@section('title', 'Ruta de '.$notificador->name)

{{--
    Editor de la ruta del día (RutasNotificadoresController). El mapa es el
    mismo que ve el notificador en la app (public/js/sinotifico/mapa-ruta.js).
    Cada cambio se guarda al momento y deja la ruta "por aprobar": la app sólo
    la muestra cuando se aprueba.
--}}

@php
    $fechaLegible = \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM');
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <link rel="stylesheet" href="{{ asset('assets/plugins/custom/leaflet/leaflet.bundle.css') }}">
    <style>
        .rt-layout { display: grid; grid-template-columns: minmax(0, 1fr) 400px; gap: 16px; align-items: start; }
        @media (max-width: 1100px) { .rt-layout { grid-template-columns: 1fr; } }
        .rt-mapa { height: calc(100vh - 210px); min-height: 520px; border-radius: 14px; overflow: hidden; border: 1px solid var(--tn-borde); }
        .rt-panel { display: flex; flex-direction: column; gap: 12px; }
        .rt-estado { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .rt-estado--aprobada { background: #E6F2EA; color: #1E7B3C; }
        .rt-estado--borrador { background: #FBF4E1; color: #8A6A12; }
        .rt-estado--sin { background: var(--tn-fondo); color: var(--tn-suave); }
        .rt-cifras { display: flex; gap: 16px; font-size: 13px; color: var(--tn-suave); margin-top: 6px; }
        .rt-cifras b { color: var(--tn-tinta); }
        .rt-acciones { display: flex; flex-wrap: wrap; gap: 8px; }
        .rt-lista { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; max-height: 46vh; overflow: auto; }
        .rt-parada { display: grid; grid-template-columns: 30px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 8px 10px; border: 1px solid var(--tn-borde); border-radius: 10px; background: #fff; cursor: grab; }
        .rt-parada.is-arrastrando { opacity: .5; }
        .rt-parada.is-sobre { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); }
        .rt-num { width: 28px; height: 28px; border-radius: 50%; color: #fff; font-weight: 700; font-size: 12px; display: flex; align-items: center; justify-content: center; }
        .rt-parada__exp { font-size: 11px; font-weight: 600; color: var(--tn-tenue); }
        .rt-parada__nombre { font-size: 13px; font-weight: 600; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .rt-parada__dom { font-size: 11.5px; color: var(--tn-suave); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .rt-parada__aviso { font-size: 11px; color: #8A6A12; font-weight: 600; }
        .rt-botones { display: flex; gap: 2px; }
        .rt-botones button { border: 0; background: none; color: var(--tn-suave); padding: 2px 4px; border-radius: 6px; }
        .rt-botones button:hover { background: var(--tn-fondo); color: var(--tn-tinta); }
        .rt-ubicando { outline: 3px solid #D9A92B; }
        .rt-ayuda { font-size: 12px; color: var(--tn-suave); }
        .rt-leyenda { display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; color: var(--tn-suave); margin-top: 8px; }
        .rt-resultado { font-size: 11.5px; font-weight: 600; color: #1E7B3C; }
        .rt-resultado--no { color: #A93226; }
        .rt-resultado--dev { color: #8A6A12; }
        .rt-bitacora { list-style: none; margin: 0; padding: 0; font-size: 12.5px; }
        .rt-bitacora li { padding: 6px 0; border-bottom: 1px solid var(--tn-borde); }
        .rt-bitacora li:last-child { border-bottom: 0; }
        .rt-bitacora small { color: var(--tn-tenue); }
        .rt-leyenda i { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 5px; vertical-align: -1px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('rutas.index', ['fecha' => $fecha]) }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Rutas de notificadores
                </a>
                <h3 class="page__heading mb-0">{{ $notificador->name }}</h3>
                <div class="small text-muted text-capitalize">{{ $notificador->delegacion }} · {{ $fechaLegible }}</div>
            </div>
            <a href="{{ route('rutas.historial', ['semana' => \Carbon\Carbon::parse($fecha)->format('o-\WW')]) }}" class="btn btn-outline-secondary">
                <i class="bi bi-calendar-week me-1"></i> Historial de la semana
            </a>
        </div>

        <div class="section-body">
            <div id="rtAviso" class="alert d-none" role="status"></div>

            <div class="rt-layout">
                <div>
                    <div id="rtMapa" class="rt-mapa" aria-label="Mapa de la ruta"></div>
                    <div class="rt-leyenda">
                        <span><i style="background:#c0392b"></i>Fuera de plazo</span>
                        <span><i style="background:#d9a92b"></i>Vence pronto</span>
                        <span><i style="background:#496163"></i>En plazo</span>
                        <span><i style="background:#829a9c"></i>Ya capturada</span>
                        <span>Pin con borde punteado: ubicación aproximada. Arrastra un pin para corregirlo.</span>
                    </div>
                </div>

                <div class="rt-panel">
                    <div class="tn-tarjeta mb-0">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span id="rtEstado" class="rt-estado rt-estado--sin">Sin ruta</span>
                            <span id="rtAprobada" class="small text-muted"></span>
                        </div>
                        <div class="rt-cifras">
                            <span><b id="rtParadas">0</b> paradas</span>
                            <span>≈ <b id="rtKm">0</b> km</span>
                            <span>≈ <b id="rtTiempo">0 h</b></span>
                        </div>
                        <p id="rtExplica" class="rt-ayuda mt-2 mb-0"></p>
                        <div class="rt-acciones mt-3" id="rtAcciones">
                            <button type="button" id="rtGenerar" class="btn btn-sm btn-outline-secondary"><i class="bi bi-magic me-1"></i>Proponer ruta</button>
                            <button type="button" id="rtOrdenar" class="btn btn-sm btn-outline-secondary"><i class="bi bi-signpost-split me-1"></i>Ordenar por cercanía</button>
                            <button type="button" id="rtAprobarBtn" class="btn btn-sm tn-boton-dorado ms-auto"><i class="bi bi-check2-circle me-1"></i>Aprobar ruta</button>
                        </div>
                    </div>

                    <div class="tn-tarjeta mb-0">
                        <h6 class="mb-1" style="font-size: 13px; font-weight: 700;">Paradas en orden</h6>
                        <p class="rt-ayuda mb-2" id="rtAyudaOrden">Arrastra para cambiar el orden, o usa las flechas.</p>
                        <ol id="rtLista" class="rt-lista"></ol>
                    </div>

                    <div class="tn-tarjeta mb-0" id="rtTarjetaFuera">
                        <h6 class="mb-1" style="font-size: 13px; font-weight: 700;">Pendientes fuera de la ruta <span id="rtFueraN" class="text-muted"></span></h6>
                        <p class="rt-ayuda mb-2">Visitas asignadas a este notificador que no están en la ruta de hoy.</p>
                        <ul id="rtFuera" class="rt-lista"></ul>
                    </div>

                    <div class="tn-tarjeta mb-0">
                        <h6 class="mb-2" style="font-size: 13px; font-weight: 700;">Bitácora de la ruta</h6>
                        <ul id="rtBitacora" class="rt-bitacora"></ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/custom/leaflet/leaflet.bundle.js') }}"></script>
    <script src="{{ asset('js/sinotifico/mapa-ruta.js') }}?v=1"></script>
    <script>
        (function () {
            var URLS = @json($urls);
            var datos = @json($datos);
            // Días pasados: historial de sólo lectura (no se reordena, ni se aprueba, ni se mueven pines).
            var SOLO = @json($soloLectura);
            var token = document.querySelector('meta[name="csrf-token"]').content;
            var el = {
                mapa: document.getElementById('rtMapa'), lista: document.getElementById('rtLista'), fuera: document.getElementById('rtFuera'),
                estado: document.getElementById('rtEstado'), aprobada: document.getElementById('rtAprobada'), explica: document.getElementById('rtExplica'),
                paradas: document.getElementById('rtParadas'), km: document.getElementById('rtKm'), tiempo: document.getElementById('rtTiempo'),
                aviso: document.getElementById('rtAviso'), fueraN: document.getElementById('rtFueraN'),
                generar: document.getElementById('rtGenerar'), ordenar: document.getElementById('rtOrdenar'), aprobar: document.getElementById('rtAprobarBtn'),
            };
            var ubicando = null; // parada esperando un clic en el mapa para fijar su ubicación
            var mapa = null;

            function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

            function avisar(texto, tipo) {
                el.aviso.className = 'alert alert-' + (tipo || 'success');
                el.aviso.textContent = texto;
                clearTimeout(avisar.t);
                avisar.t = setTimeout(function () { el.aviso.className = 'alert d-none'; }, 4500);
            }

            function pedir(url, metodo, cuerpo) {
                document.body.style.cursor = 'progress';
                return fetch(url, {
                    method: metodo,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: cuerpo ? JSON.stringify(cuerpo) : undefined,
                }).then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (j) {
                        if (!r.ok) throw new Error(j.message || 'No se pudo guardar.');
                        return j;
                    });
                }).finally(function () { document.body.style.cursor = ''; });
            }

            function resultadoHtml(p, reales) {
                if (!p.llegada_at) {
                    return SOLO ? '<span class="rt-resultado rt-resultado--no d-block">No se visitó</span>' : '';
                }
                var h = new Date(p.llegada_at).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
                var rev = { por_revisar: 'por revisar', validada: 'validada', devuelta: 'devuelta' }[p.revision] || '';
                var real = reales.indexOf(p.visita_id);
                return '<span class="rt-resultado d-block' + (p.revision === 'devuelta' ? ' rt-resultado--dev' : '') + '">' +
                    'Llegó ' + h + ' · ' + esc(p.resultado) + (rev ? ' · ' + rev : '') +
                    (real >= 0 && real + 1 !== p.orden ? ' · visitada ' + (real + 1) + 'ª' : '') + '</span>';
            }

            function idsActuales() { return datos.ruta ? datos.ruta.paradas.map(function (p) { return p.visita_id; }) : []; }

            function guardar(ids, mensaje) {
                return pedir(URLS.guardar, 'PUT', { visitas: ids }).then(function (d) {
                    datos = d; pintar(); avisar(mensaje || 'Cambios guardados. La ruta queda por aprobar.');
                }).catch(function (e) { avisar(e.message, 'danger'); });
            }

            function pintar() {
                var r = datos.ruta;
                var paradas = r ? r.paradas : [];

                // Estado y cifras
                el.estado.className = 'rt-estado ' + (!r ? 'rt-estado--sin' : r.estatus === 'aprobada' ? 'rt-estado--aprobada' : 'rt-estado--borrador');
                el.estado.textContent = !r ? 'Sin ruta' : r.estatus === 'aprobada' ? 'Aprobada' : 'Por aprobar';
                el.aprobada.textContent = r && r.aprobada_por ? 'por ' + r.aprobada_por : '';
                el.explica.textContent = SOLO
                    ? 'Ruta de un día pasado: es historial y ya no se puede modificar.'
                    : !r
                    ? 'Propón una ruta con sus visitas pendientes (las de plazo más cercano, ordenadas por cercanía desde la oficina).'
                    : r.estatus === 'aprobada'
                        ? 'El notificador ya la ve en su app. Si la cambias, deja de verla hasta que la vuelvas a aprobar.'
                        : 'El notificador todavía no la ve: apruébala cuando esté lista.';
                el.paradas.textContent = paradas.length;
                el.km.textContent = r ? r.distancia_km : 0;
                el.tiempo.textContent = r ? (Math.floor(r.minutos_estimados / 60) + ' h ' + (r.minutos_estimados % 60) + ' min') : '0 h';
                el.ordenar.disabled = !r || paradas.length < 2;
                el.aprobar.disabled = !r || !paradas.length || r.estatus === 'aprobada';
                el.generar.innerHTML = '<i class="bi bi-magic me-1"></i>' + (r ? 'Volver a proponer' : 'Proponer ruta');

                document.getElementById('rtAcciones').classList.toggle('d-none', SOLO);
                document.getElementById('rtTarjetaFuera').classList.toggle('d-none', SOLO);
                document.getElementById('rtAyudaOrden').textContent = SOLO
                    ? 'Orden planeado y lo que pasó en cada parada.'
                    : 'Arrastra para cambiar el orden, o usa las flechas.';

                // Orden real: según la hora de llegada de cada captura.
                var reales = paradas.filter(function (p) { return p.llegada_at && p.revision !== 'devuelta'; })
                    .sort(function (a, b) { return a.llegada_at < b.llegada_at ? -1 : 1; })
                    .map(function (p) { return p.visita_id; });

                // Paradas
                el.lista.innerHTML = '';
                paradas.forEach(function (p, i) {
                    var hecha = p.estado !== 'pendiente';
                    var color = hecha ? SiNotificoMapa.COLOR.hecha : (SiNotificoMapa.COLOR[p.plazo.estado] || '#496163');
                    var li = document.createElement('li');
                    li.className = 'rt-parada';
                    li.draggable = !SOLO;
                    if (SOLO) li.style.cursor = 'default';
                    li.dataset.indice = i;
                    li.innerHTML =
                        '<span class="rt-num" style="background:' + color + '">' + (hecha ? '✓' : p.orden) + '</span>' +
                        '<span style="min-width:0">' +
                            '<span class="rt-parada__exp">' + esc(p.expediente) + ' · ' + esc(hecha ? ({ en_revision: 'Capturada', cerrada: 'Notificada', reasignada: 'Reasignada' }[p.estado]) : SiNotificoMapa.textoPlazo(p.plazo)) + '</span>' +
                            '<span class="rt-parada__nombre d-block">' + esc(p.principal) + '</span>' +
                            '<span class="rt-parada__dom d-block" title="' + esc(p.domicilio) + '">' + esc(p.domicilio) + '</span>' +
                            (p.lat == null ? '<span class="rt-parada__aviso d-block">Sin ubicación · <a href="#" data-ubicar="' + i + '">ubicar en el mapa</a></span>' :
                                p.ubicacion === 'osm_aprox' ? '<span class="rt-parada__aviso d-block">Ubicación aproximada · arrastra su pin</span>' : '') +
                            resultadoHtml(p, reales) +
                        '</span>' +
                        (SOLO ? '<span></span>' : '<span class="rt-botones">' +
                            '<button type="button" title="Subir" data-mover="-1" data-i="' + i + '"><i class="bi bi-arrow-up"></i></button>' +
                            '<button type="button" title="Bajar" data-mover="1" data-i="' + i + '"><i class="bi bi-arrow-down"></i></button>' +
                            '<button type="button" title="Quitar de la ruta" data-quitar="' + i + '"><i class="bi bi-x-lg"></i></button>' +
                        '</span>');
                    el.lista.appendChild(li);
                });
                if (!paradas.length) el.lista.innerHTML = '<li class="rt-ayuda">Sin paradas.</li>';

                // Fuera de la ruta
                el.fuera.innerHTML = '';
                el.fueraN.textContent = '(' + datos.fuera.length + ')';
                datos.fuera.forEach(function (v) {
                    var li = document.createElement('li');
                    li.className = 'rt-parada';
                    li.style.cursor = 'default';
                    li.innerHTML =
                        '<span class="rt-num" style="background:' + (SiNotificoMapa.COLOR[v.plazo.estado] || '#496163') + '">+</span>' +
                        '<span style="min-width:0">' +
                            '<span class="rt-parada__exp">' + esc(v.expediente) + ' · ' + esc(SiNotificoMapa.textoPlazo(v.plazo)) + '</span>' +
                            '<span class="rt-parada__nombre d-block">' + esc(v.principal) + '</span>' +
                            '<span class="rt-parada__dom d-block">' + esc(v.domicilio) + '</span>' +
                            (v.fuera_del_estado ? '<span class="rt-parada__aviso d-block">Fuera de Michoacán (exhorto)</span>' : '') +
                        '</span>' +
                        '<span class="rt-botones"><button type="button" title="Agregar al final" data-agregar="' + v.visita_id + '"><i class="bi bi-plus-lg"></i></button></span>';
                    el.fuera.appendChild(li);
                });
                if (!datos.fuera.length) el.fuera.innerHTML = '<li class="rt-ayuda">Todas sus visitas pendientes están en la ruta.</li>';

                // Bitácora
                var b = document.getElementById('rtBitacora');
                var eventos = datos.eventos || [];
                var TIPO = { propuesta: 'Propuesta', editada: 'Editada', ordenada: 'Ordenada por cercanía', aprobada: 'Aprobada' };
                b.innerHTML = eventos.length ? eventos.map(function (e) {
                    var f = new Date(e.fecha);
                    return '<li><b>' + esc(TIPO[e.tipo] || e.tipo) + '</b>' + (e.reabierta ? ' <span class="rt-resultado--dev">(estaba aprobada)</span>' : '') +
                        (e.paradas ? ' · ' + e.paradas + ' paradas' : '') +
                        '<br><small>' + esc(e.usuario || 'Sistema') + ' · ' + f.toLocaleDateString('es-MX', { day: 'numeric', month: 'short' }) + ' ' +
                        f.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }) + '</small></li>';
                }).join('') : '<li class="rt-ayuda">Sin movimientos.</li>';

                // Mapa: el mismo de la app
                mapa = SiNotificoMapa.dibujar(el.mapa, datos, {
                    editable: !SOLO,
                    alMoverPin: function (p, lat, lng) {
                        pedir(URLS.ubicar.replace('__ID__', p.domicilio_id), 'PUT', { lat: lat, lng: lng })
                            .then(function () { return guardar(idsActuales(), 'Ubicación corregida. También servirá para futuras rutas.'); })
                            .catch(function (e) { avisar(e.message, 'danger'); pintar(); });
                    },
                });
            }

            // Fijar ubicación de una parada sin coordenadas: clic en el mapa.
            function esperarUbicacion(p) {
                ubicando = p;
                el.mapa.classList.add('rt-ubicando');
                avisar('Haz clic en el mapa donde está el domicilio de ' + p.principal + '.', 'info');
                mapa.once('click', function (e) {
                    el.mapa.classList.remove('rt-ubicando');
                    var objetivo = ubicando; ubicando = null;
                    pedir(URLS.ubicar.replace('__ID__', objetivo.domicilio_id), 'PUT', { lat: e.latlng.lat, lng: e.latlng.lng })
                        .then(function () { return guardar(idsActuales(), 'Domicilio ubicado.'); })
                        .catch(function (err) { avisar(err.message, 'danger'); });
                });
            }

            el.lista.addEventListener('click', function (e) {
                var b = e.target.closest('[data-mover],[data-quitar],[data-ubicar]');
                if (!b) return;
                e.preventDefault();
                var ids = idsActuales();
                if (b.dataset.ubicar !== undefined) return esperarUbicacion(datos.ruta.paradas[+b.dataset.ubicar]);
                if (b.dataset.quitar !== undefined) { ids.splice(+b.dataset.quitar, 1); return guardar(ids, 'Parada quitada. La visita sigue pendiente para otro día.'); }
                var i = +b.dataset.i, j = i + (+b.dataset.mover);
                if (j < 0 || j >= ids.length) return;
                ids.splice(j, 0, ids.splice(i, 1)[0]);
                guardar(ids);
            });

            // Arrastrar y soltar en la lista
            var desde = null;
            el.lista.addEventListener('dragstart', function (e) { var li = e.target.closest('.rt-parada'); if (!li) return; desde = +li.dataset.indice; li.classList.add('is-arrastrando'); });
            el.lista.addEventListener('dragend', function (e) { var li = e.target.closest('.rt-parada'); if (li) li.classList.remove('is-arrastrando'); });
            el.lista.addEventListener('dragover', function (e) { var li = e.target.closest('.rt-parada'); if (!li) return; e.preventDefault(); el.lista.querySelectorAll('.is-sobre').forEach(function (x) { x.classList.remove('is-sobre'); }); li.classList.add('is-sobre'); });
            el.lista.addEventListener('drop', function (e) {
                var li = e.target.closest('.rt-parada'); if (!li || desde === null) return;
                e.preventDefault();
                var hasta = +li.dataset.indice, ids = idsActuales();
                if (hasta !== desde) { ids.splice(hasta, 0, ids.splice(desde, 1)[0]); guardar(ids); }
                desde = null;
            });

            el.fuera.addEventListener('click', function (e) {
                var b = e.target.closest('[data-agregar]');
                if (!b) return;
                guardar(idsActuales().concat([+b.dataset.agregar]), 'Visita agregada al final de la ruta.');
            });

            el.generar.addEventListener('click', function () {
                if (datos.ruta && !confirm('Se reemplaza la ruta actual por una propuesta nueva. ¿Continuar?')) return;
                el.generar.disabled = true;
                avisar('Ubicando domicilios y armando la ruta… puede tardar unos segundos.', 'info');
                pedir(URLS.generar, 'POST').then(function (d) { datos = d; pintar(); avisar('Ruta propuesta. Revísala y apruébala.'); })
                    .catch(function (e) { avisar(e.message, 'danger'); })
                    .finally(function () { el.generar.disabled = false; });
            });
            el.ordenar.addEventListener('click', function () {
                pedir(URLS.ordenar, 'POST').then(function (d) { datos = d; pintar(); avisar('Paradas ordenadas por cercanía desde la oficina.'); })
                    .catch(function (e) { avisar(e.message, 'danger'); });
            });
            el.aprobar.addEventListener('click', function () {
                var sinUbicar = datos.ruta.paradas.filter(function (p) { return p.lat == null; }).length;
                if (sinUbicar && !confirm(sinUbicar + ' parada(s) no tienen ubicación en el mapa. ¿Aprobar de todos modos?')) return;
                pedir(URLS.aprobar, 'POST').then(function (d) { datos = d; pintar(); avisar('Ruta aprobada: el notificador ya la ve en SíNotifico.'); })
                    .catch(function (e) { avisar(e.message, 'danger'); });
            });

            pintar();
        })();
    </script>
@endsection
