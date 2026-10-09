@extends('layouts.app')
@section('title', 'Escanear cita')

{{--
    Escáner de QR de citas. La cámara del navegador lee el QR del acuse; si no
    hay cámara o no lee, se puede usar un lector USB (teclea el código y Enter)
    o escribir el folio. La validación la hace el servidor (EscanerCitasController
    + ValidadorCita); aquí sólo se lee y se muestra el resultado.

    Requisitos del navegador: HTTPS (o localhost) y permiso de cámara. La
    cabecera Permissions-Policy sólo la permite en esta ruta.
--}}

@section('page_css')
    @include('turnos._estilos')
    <style>
        .es-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            gap: 16px;
            align-items: start;
        }
        @media (max-width: 992px) { .es-grid { grid-template-columns: 1fr; } }

        /* Cámara */
        .es-camara {
            position: relative;
            aspect-ratio: 4 / 3;
            border-radius: 12px;
            overflow: hidden;
            background: #1F2A2B;
        }
        .es-camara video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .es-marco {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            pointer-events: none;
        }
        .es-marco__caja {
            position: relative;
            width: min(62%, 300px);
            aspect-ratio: 1;
            border-radius: 14px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, .35);
        }
        .es-marco__caja::before,
        .es-marco__caja::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 14px;
            border: 3px solid transparent;
            border-top-color: #CEA845;
            border-bottom-color: #CEA845;
            clip-path: polygon(0 0, 22% 0, 22% 100%, 0 100%, 0 0, 100% 0, 100% 100%, 78% 100%, 78% 0);
        }
        .es-marco__caja::after {
            border-color: transparent;
            border-left-color: #CEA845;
            border-right-color: #CEA845;
            clip-path: polygon(0 0, 100% 0, 100% 22%, 0 22%, 0 0, 0 78%, 100% 78%, 100% 100%, 0 100%);
        }
        .es-linea {
            position: absolute;
            left: 8%;
            right: 8%;
            height: 2px;
            top: 50%;
            background: rgba(206, 168, 69, .85);
            box-shadow: 0 0 10px rgba(206, 168, 69, .9);
            animation: es-barrido 2.2s ease-in-out infinite;
        }
        @keyframes es-barrido {
            0%, 100% { transform: translateY(-120px); }
            50%      { transform: translateY(120px); }
        }
        @media (prefers-reduced-motion: reduce) { .es-linea { animation: none; } }
        .es-camara.is-pausada .es-linea { animation-play-state: paused; opacity: .3; }

        .es-aviso {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            text-align: center;
            color: #fff;
            background: #1F2A2B;
            font-size: 14.5px;
        }
        .es-aviso i { display: block; font-size: 38px; margin-bottom: 8px; color: #CEA845; }
        .es-aviso[hidden] { display: none; }

        .es-controles {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin-top: 12px;
        }
        .es-controles .form-select { width: auto; max-width: 100%; }
        .es-estado { font-size: 13px; color: var(--tn-tenue); margin-left: auto; }

        .es-manual { margin-top: 14px; }
        .es-manual label { font-size: 12px; font-weight: 600; color: var(--tn-tenue); margin-bottom: 4px; }

        /* Resultado */
        .es-resultado { padding: 0; overflow: hidden; }
        .es-banner {
            display: flex;
            gap: 14px;
            align-items: center;
            padding: 18px 20px;
            color: #fff;
            background: #829A9C;
        }
        .es-banner i { font-size: 38px; line-height: 1; }
        .es-banner__titulo { margin: 0; font-size: 21px; font-weight: 800; line-height: 1.15; }
        .es-banner__mensaje { margin: 3px 0 0; font-size: 14px; opacity: .95; }
        .es-banner--ok    { background: #1E7B3C; }
        .es-banner--info  { background: #2F6F8F; }
        .es-banner--aviso { background: #B4530F; }
        .es-banner--error { background: #A3162E; }

        .es-datos { padding: 16px 20px 18px; }
        .es-datos__nombre { font-size: 18px; font-weight: 700; color: var(--tn-tinta); margin: 0 0 10px; }
        .es-datos dl {
            display: grid;
            grid-template-columns: 90px minmax(0, 1fr);
            gap: 5px 12px;
            margin: 0 0 12px;
            font-size: 14px;
        }
        .es-datos dt { color: var(--tn-tenue); font-weight: 600; }
        .es-datos dd { margin: 0; color: var(--tn-tinta); }
        .es-modulo { font-size: 20px; font-weight: 800; color: var(--tn-verde); }

        .es-espera { padding: 34px 20px; text-align: center; color: var(--tn-tenue); }
        .es-espera i { font-size: 40px; display: block; margin-bottom: 8px; color: var(--tn-verde-claro); }

        .es-historial { list-style: none; margin: 0; padding: 0; }
        .es-historial li {
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 8px 0;
            border-top: 1px solid var(--tn-borde);
            font-size: 13px;
        }
        .es-historial li:first-child { border-top: 0; }
        .es-historial__hora { color: var(--tn-tenue); font-variant-numeric: tabular-nums; }
        .es-historial__quien { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--tn-tinta); }
        .es-chip { padding: 2px 8px; border-radius: 999px; font-size: 11.5px; font-weight: 700; color: #fff; white-space: nowrap; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                @if (Route::has('turnos'))
                    <a href="{{ route('turnos') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                        <i class="bi bi-arrow-left"></i> Turnos
                    </a>
                @endif
                <h3 class="page__heading mb-0">Escanear cita</h3>
            </div>
            <span class="small" style="color: var(--tn-tenue);">
                <i class="bi bi-geo-alt"></i> {{ count($sedes) > 2 ? 'Todas las sedes' : implode(' y ', $sedes) }}
                · Tolerancia {{ $tolerancia }} min
            </span>
        </div>

        <div class="section-body">
            <div class="es-grid">
                <div class="tn-tarjeta">
                    <div class="es-camara" id="esCamara">
                        <video id="esVideo" playsinline muted aria-label="Vista de la cámara"></video>
                        <div class="es-marco" aria-hidden="true">
                            <div class="es-marco__caja"><span class="es-linea"></span></div>
                        </div>
                        <div class="es-aviso" id="esAviso">
                            <div>
                                <i class="bi bi-camera-video"></i>
                                <span id="esAvisoTexto">Encendiendo la cámara…</span>
                            </div>
                        </div>
                    </div>

                    <div class="es-controles">
                        <button type="button" class="btn tn-boton-dorado" id="esBoton">
                            <i class="bi bi-camera-video me-1"></i> <span>Encender cámara</span>
                        </button>
                        <select class="form-select" id="esCamaras" aria-label="Cámara" hidden></select>
                        <span class="es-estado" id="esEstado" role="status" aria-live="polite"></span>
                    </div>

                    {{-- Lector USB o a mano: el lector "teclea" el código y Enter. --}}
                    <form class="es-manual" id="esManual" autocomplete="off">
                        <label for="esCodigo">¿No lee? Escribe el folio o usa el lector</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="esCodigo" maxlength="500"
                                   placeholder="Folio, por ejemplo 00012" inputmode="numeric">
                            <button class="btn btn-outline-secondary" type="submit">Validar</button>
                        </div>
                    </form>
                </div>

                <div class="d-grid gap-3">
                    <div class="tn-tarjeta es-resultado" id="esResultado" aria-live="assertive">
                        <div class="es-espera">
                            <i class="bi bi-qr-code-scan" aria-hidden="true"></i>
                            Acerca el código QR del acuse a la cámara.
                        </div>
                    </div>

                    <div class="tn-tarjeta">
                        <p class="tn-titulo-seccion">Escaneadas en esta sesión</p>
                        <ul class="es-historial" id="esHistorial">
                            <li style="border:0; grid-template-columns: 1fr; color: var(--tn-tenue);">Todavía ninguna.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
    (function () {
        'use strict';

        var URL_VALIDAR = @json(route('recepcion.escaner.validar'));
        var TOKEN = document.querySelector('meta[name="csrf-token"]')
            ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            : @json(csrf_token());

        var video     = document.getElementById('esVideo');
        var caja      = document.getElementById('esCamara');
        var aviso     = document.getElementById('esAviso');
        var avisoTxt  = document.getElementById('esAvisoTexto');
        var boton     = document.getElementById('esBoton');
        var selector  = document.getElementById('esCamaras');
        var estado    = document.getElementById('esEstado');
        var resultado = document.getElementById('esResultado');
        var historial = document.getElementById('esHistorial');

        var stream = null;
        var detector = null;
        var lienzo = document.createElement('canvas');
        var ctx = lienzo.getContext('2d', { willReadFrequently: true });
        var ocupado = false;           // hay una validación en curso
        var ultimo = { codigo: '', t: 0 };
        var ESPERA_MISMO = 4000;       // ms antes de aceptar el mismo QR otra vez
        var ultimoCuadro = 0;

        var ESTILO = {
            confirmada:    { clase: 'ok',    icono: 'bi-check-circle-fill',       chip: '#1E7B3C' },
            ya_confirmada: { clase: 'info',  icono: 'bi-info-circle-fill',        chip: '#2F6F8F' },
            atendida:      { clase: 'info',  icono: 'bi-person-check-fill',       chip: '#2F6F8F' },
            otro_dia:      { clase: 'aviso', icono: 'bi-calendar-event-fill',     chip: '#B4530F' },
            pasada:        { clase: 'aviso', icono: 'bi-calendar-x-fill',         chip: '#B4530F' },
            tarde:         { clase: 'error', icono: 'bi-alarm-fill',              chip: '#A3162E' },
            expirada:      { clase: 'error', icono: 'bi-x-octagon-fill',          chip: '#A3162E' },
            otra_sede:     { clase: 'error', icono: 'bi-geo-alt-fill',            chip: '#A3162E' },
            no_encontrada: { clase: 'error', icono: 'bi-question-octagon-fill',   chip: '#A3162E' },
            error:         { clase: 'error', icono: 'bi-exclamation-triangle-fill', chip: '#A3162E' }
        };

        // ---------- Cámara
        function mostrarAviso(texto, icono) {
            avisoTxt.textContent = texto;
            aviso.querySelector('i').className = 'bi ' + (icono || 'bi-camera-video');
            aviso.hidden = false;
        }

        function textoBoton(encendida) {
            boton.querySelector('span').textContent = encendida ? 'Apagar cámara' : 'Encender cámara';
        }

        async function encender(deviceId) {
            if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                mostrarAviso('La cámara sólo funciona con conexión segura (https). Usa el lector o escribe el folio.', 'bi-shield-lock');
                boton.disabled = true;
                return;
            }
            apagar();
            mostrarAviso('Encendiendo la cámara…');
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: deviceId
                        ? { deviceId: { exact: deviceId } }
                        : { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }
                });
                video.srcObject = stream;
                await video.play();
                aviso.hidden = true;
                textoBoton(true);
                estado.textContent = 'Buscando código…';
                await listarCamaras();
                requestAnimationFrame(leer);
            } catch (e) {
                stream = null;
                textoBoton(false);
                var mensajes = {
                    NotAllowedError:  'Sin permiso para usar la cámara. Actívalo en el candado de la barra de direcciones y vuelve a encenderla.',
                    NotFoundError:    'No se encontró ninguna cámara. Usa el lector o escribe el folio.',
                    NotReadableError: 'La cámara está ocupada por otra aplicación (Teams, Zoom…). Ciérrala y vuelve a intentar.',
                    OverconstrainedError: 'Esa cámara no está disponible. Elige otra.'
                };
                mostrarAviso(mensajes[e.name] || ('No se pudo encender la cámara (' + e.name + ').'), 'bi-camera-video-off');
            }
        }

        function apagar() {
            if (stream) {
                stream.getTracks().forEach(function (t) { t.stop(); });
            }
            stream = null;
            video.srcObject = null;
            textoBoton(false);
            estado.textContent = '';
        }

        async function listarCamaras() {
            try {
                var dispositivos = (await navigator.mediaDevices.enumerateDevices()).filter(function (d) { return d.kind === 'videoinput'; });
                if (dispositivos.length < 2) { selector.hidden = true; return; }
                var actual = stream && stream.getVideoTracks()[0] ? stream.getVideoTracks()[0].getSettings().deviceId : '';
                selector.textContent = '';
                dispositivos.forEach(function (d, i) {
                    var o = document.createElement('option');
                    o.value = d.deviceId;
                    o.textContent = d.label || ('Cámara ' + (i + 1));
                    o.selected = d.deviceId === actual;
                    selector.appendChild(o);
                });
                selector.hidden = false;
            } catch (e) { selector.hidden = true; }
        }

        // ---------- Lectura
        async function leer(t) {
            if (!stream) return;
            // ~7 lecturas por segundo: suficiente y no calienta el equipo.
            if (!ocupado && t - ultimoCuadro > 140 && video.readyState >= 2) {
                ultimoCuadro = t;
                var codigo = await decodificar();
                if (codigo) recibir(codigo);
            }
            requestAnimationFrame(leer);
        }

        async function decodificar() {
            try {
                if (detector) {
                    var hallados = await detector.detect(video);
                    return hallados.length ? hallados[0].rawValue : null;
                }
                if (typeof jsQR !== 'function') return null;
                var ancho = Math.min(640, video.videoWidth || 640);
                var alto = Math.round(ancho * (video.videoHeight || 480) / (video.videoWidth || 640));
                lienzo.width = ancho;
                lienzo.height = alto;
                ctx.drawImage(video, 0, 0, ancho, alto);
                var img = ctx.getImageData(0, 0, ancho, alto);
                var qr = jsQR(img.data, ancho, alto, { inversionAttempts: 'dontInvert' });
                return qr ? qr.data : null;
            } catch (e) { return null; }
        }

        function recibir(codigo) {
            codigo = String(codigo).trim();
            if (!codigo) return;
            var ahora = Date.now();
            if (codigo === ultimo.codigo && ahora - ultimo.t < ESPERA_MISMO) return;
            ultimo = { codigo: codigo, t: ahora };
            validar(codigo);
        }

        // ---------- Servidor
        async function validar(codigo) {
            ocupado = true;
            caja.classList.add('is-pausada');
            estado.textContent = 'Validando…';
            var datos;
            try {
                var r = await fetch(URL_VALIDAR, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN },
                    body: JSON.stringify({ codigo: codigo }),
                    credentials: 'same-origin'
                });
                if (r.status === 419 || r.status === 401) {
                    datos = { resultado: 'error', titulo: 'La sesión expiró', mensaje: 'Recarga la página e inicia sesión de nuevo.' };
                } else if (r.status === 429) {
                    datos = { resultado: 'error', titulo: 'Demasiados intentos', mensaje: 'Espera un momento y vuelve a intentar.' };
                } else if (!r.ok) {
                    datos = { resultado: 'error', titulo: 'No se pudo validar', mensaje: 'Error del servidor (' + r.status + ').' };
                } else {
                    datos = await r.json();
                }
            } catch (e) {
                datos = { resultado: 'error', titulo: 'Sin conexión', mensaje: 'No se pudo contactar al servidor.' };
            }
            pintar(datos);
            agregarHistorial(datos);
            sonar(datos.resultado === 'confirmada' || datos.resultado === 'ya_confirmada' || datos.resultado === 'atendida');
            estado.textContent = stream ? 'Buscando código…' : '';
            // Un respiro para que lean el resultado antes del siguiente.
            setTimeout(function () { ocupado = false; caja.classList.remove('is-pausada'); }, 1500);
        }

        // ---------- Pintar (todo con textContent: los datos vienen de la cita)
        function el(etiqueta, clase, texto) {
            var e = document.createElement(etiqueta);
            if (clase) e.className = clase;
            if (texto !== undefined && texto !== null) e.textContent = texto;
            return e;
        }

        function pintar(d) {
            var s = ESTILO[d.resultado] || ESTILO.error;
            resultado.textContent = '';

            var banner = el('div', 'es-banner es-banner--' + s.clase);
            banner.appendChild(el('i', 'bi ' + s.icono));
            var txt = el('div');
            txt.appendChild(el('p', 'es-banner__titulo', d.titulo || ''));
            if (d.mensaje) txt.appendChild(el('p', 'es-banner__mensaje', d.mensaje));
            banner.appendChild(txt);
            resultado.appendChild(banner);

            if (!d.cita) return;
            var c = d.cita;
            var cuerpo = el('div', 'es-datos');
            cuerpo.appendChild(el('p', 'es-datos__nombre', c.solicitante || 'Sin nombre'));
            var dl = el('dl');
            [
                ['Folio', c.folio],
                ['Trámite', c.tipo + (c.excepcion ? ' · Caso de excepción' : '')],
                ['Día', c.fecha],
                ['Hora', c.hora],
                ['Sede', c.sede],
                ['Atiende', c.atiende || 'Sin asignar']
            ].forEach(function (par) {
                dl.appendChild(el('dt', null, par[0]));
                dl.appendChild(el('dd', null, par[1]));
            });
            dl.appendChild(el('dt', null, 'Módulo'));
            var dd = el('dd');
            dd.appendChild(el('span', 'es-modulo', c.modulo || '—'));
            dl.appendChild(dd);
            cuerpo.appendChild(dl);

            var acuse = el('a', 'btn btn-sm btn-outline-secondary');
            acuse.href = c.acuse;
            acuse.target = '_blank';
            acuse.rel = 'noopener';
            acuse.appendChild(el('i', 'bi bi-file-pdf me-1'));
            acuse.appendChild(document.createTextNode('Ver acuse'));
            cuerpo.appendChild(acuse);
            resultado.appendChild(cuerpo);
        }

        var vacio = true;
        function agregarHistorial(d) {
            if (vacio) { historial.textContent = ''; vacio = false; }
            var s = ESTILO[d.resultado] || ESTILO.error;
            var li = el('li');
            var ahora = new Date();
            li.appendChild(el('span', 'es-historial__hora',
                String(ahora.getHours()).padStart(2, '0') + ':' + String(ahora.getMinutes()).padStart(2, '0')));
            li.appendChild(el('span', 'es-historial__quien',
                d.cita ? (d.cita.folio + ' · ' + (d.cita.solicitante || '')) : (d.mensaje || '')));
            var chip = el('span', 'es-chip', d.titulo || '');
            chip.style.background = s.chip;
            li.appendChild(chip);
            historial.insertBefore(li, historial.firstChild);
            while (historial.children.length > 12) historial.removeChild(historial.lastChild);
        }

        // ---------- Sonido y vibración
        var audio = null;
        function sonar(bien) {
            try {
                audio = audio || new (window.AudioContext || window.webkitAudioContext)();
                var tonos = bien ? [[880, 0, .12]] : [[220, 0, .15], [220, .2, .15]];
                tonos.forEach(function (t) {
                    var o = audio.createOscillator(), g = audio.createGain();
                    o.frequency.value = t[0];
                    o.connect(g); g.connect(audio.destination);
                    g.gain.setValueAtTime(.15, audio.currentTime + t[1]);
                    o.start(audio.currentTime + t[1]);
                    o.stop(audio.currentTime + t[1] + t[2]);
                });
            } catch (e) {}
            if (navigator.vibrate) navigator.vibrate(bien ? 80 : [80, 60, 80]);
        }

        // ---------- Eventos
        boton.addEventListener('click', function () { stream ? (apagar(), mostrarAviso('Cámara apagada.', 'bi-camera-video-off')) : encender(selector.value || null); });
        selector.addEventListener('change', function () { encender(this.value); });

        document.getElementById('esManual').addEventListener('submit', function (e) {
            e.preventDefault();
            var campo = document.getElementById('esCodigo');
            var valor = campo.value.trim();
            if (!valor) return;
            ultimo = { codigo: '', t: 0 };   // a mano siempre se valida
            validar(valor);
            campo.value = '';
            campo.focus();
        });

        // Libera la cámara al cambiar de pestaña y la retoma al volver.
        var estabaEncendida = false;
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) { estabaEncendida = !!stream; apagar(); }
            else if (estabaEncendida) { encender(selector.value || null); }
        });
        window.addEventListener('pagehide', apagar);

        // Detector nativo si el navegador lo trae (Android, Mac); si no, jsQR.
        (async function () {
            try {
                if ('BarcodeDetector' in window) {
                    var formatos = await window.BarcodeDetector.getSupportedFormats();
                    if (formatos.indexOf('qr_code') !== -1) detector = new window.BarcodeDetector({ formats: ['qr_code'] });
                }
            } catch (e) { detector = null; }
            encender(null);
        })();
    })();
    </script>
@endsection
