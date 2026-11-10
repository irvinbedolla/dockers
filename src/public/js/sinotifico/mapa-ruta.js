/*
 * Mapa de la ruta del día: el MISMO que ve el notificador en SíNotifico
 * (src/features/mapa/mapaRuta.ts en la app). Si cambias colores, íconos o la
 * tarjeta de cada parada aquí, cámbialos allá también.
 *
 * Uso: SiNotificoMapa.dibujar(elemento, { ruta, oficina }, { editable, alMoverPin, alTocarParada })
 */
(function (global) {
    'use strict';

    var COLOR = { fuera_de_plazo: '#c0392b', ultimo_dia: '#d9a92b', proximo: '#d9a92b', en_plazo: '#496163', hecha: '#829a9c' };
    var ETIQUETA_ESTADO = { en_revision: 'Capturada · en revisión', cerrada: 'Ya notificada', reasignada: 'Ya no está asignada' };

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function textoPlazo(p) {
        var d = p.dias_habiles_restantes;
        if (p.estado === 'fuera_de_plazo') return 'Fuera de plazo';
        if (p.estado === 'ultimo_dia') return 'Último día para notificar';
        return d === 1 ? 'Queda 1 día hábil' : 'Quedan ' + d + ' días hábiles';
    }

    function fechaCorta(iso) {
        var f = new Date(iso + 'T12:00:00');
        return f.toLocaleDateString('es-MX', { weekday: 'short', day: 'numeric', month: 'short' });
    }

    function iconoParada(L, p) {
        var hecha = p.estado !== 'pendiente';
        var color = hecha ? COLOR.hecha : COLOR[p.plazo.estado] || COLOR.en_plazo;
        var aprox = p.ubicacion === 'osm_aprox';
        var html =
            '<div class="snm-pin' + (aprox ? ' snm-pin--aprox' : '') + '" style="background:' + color + '">' +
            (hecha ? '✓' : p.orden) +
            '</div>';
        return L.divIcon({ className: 'snm-icono', html: html, iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16] });
    }

    function iconoOficina(L) {
        return L.divIcon({
            className: 'snm-icono',
            html:
                '<div class="snm-oficina" title="Oficina de la sede"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21V8l8-5 8 5v13"/><path d="M9 21v-6h6v6"/></svg></div>',
            iconSize: [34, 34],
            iconAnchor: [17, 17],
        });
    }

    function tarjeta(p) {
        return (
            '<div class="snm-tarjeta">' +
            '<div class="snm-tarjeta__exp">#' + p.orden + ' · ' + esc(p.expediente) + '</div>' +
            '<div class="snm-tarjeta__nombre">' + esc(p.principal) + '</div>' +
            (p.citados_n > 1 ? '<div class="snm-tarjeta__mas">+ ' + (p.citados_n - 1) + ' citado(s) más</div>' : '') +
            '<div class="snm-tarjeta__dom">' + esc(p.domicilio) + '</div>' +
            '<div class="snm-tarjeta__pie">Aud. ' + fechaCorta(p.audiencia.fecha) + ' · ' + esc(p.audiencia.hora) + ' — ' +
            (p.estado !== 'pendiente' ? esc(ETIQUETA_ESTADO[p.estado] || '') : textoPlazo(p.plazo)) + '</div>' +
            (p.ubicacion === 'osm_aprox' ? '<div class="snm-tarjeta__aviso">Ubicación aproximada (colonia)</div>' : '') +
            '</div>'
        );
    }

    var CSS =
        '.snm-icono{background:none;border:0}' +
        '.snm-pin{width:32px;height:32px;border-radius:50%;color:#fff;font:700 13px/26px Poppins,system-ui,sans-serif;text-align:center;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35)}' +
        '.snm-pin--aprox{border-style:dashed}' +
        '.snm-oficina{width:34px;height:34px;border-radius:10px;background:#22333a;display:flex;align-items:center;justify-content:center;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35)}' +
        '.snm-tarjeta{font:12px/1.35 Poppins,system-ui,sans-serif;color:#22333a;max-width:240px}' +
        '.snm-tarjeta__exp{font-size:10.5px;font-weight:600;color:#6b7c7e;letter-spacing:.02em}' +
        '.snm-tarjeta__nombre{font-weight:600;font-size:13px;margin-top:2px}' +
        '.snm-tarjeta__mas{color:#829a9c;font-size:11px}' +
        '.snm-tarjeta__dom{color:#6b7c7e;margin-top:3px}' +
        '.snm-tarjeta__pie{color:#496163;font-weight:600;margin-top:4px;font-size:11px}' +
        '.snm-tarjeta__aviso{color:#7a5a12;font-size:11px;margin-top:3px}';

    function dibujar(el, datos, opciones) {
        var L = global.L;
        opciones = opciones || {};
        if (!document.getElementById('snm-estilos')) {
            var st = document.createElement('style');
            st.id = 'snm-estilos';
            st.textContent = CSS;
            document.head.appendChild(st);
        }

        var mapa = el._snmMapa;
        if (!mapa) {
            mapa = L.map(el, { zoomControl: true, attributionControl: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap',
            }).addTo(mapa);
            el._snmMapa = mapa;
            el._snmCapa = L.layerGroup().addTo(mapa);
        }
        var capa = el._snmCapa;
        capa.clearLayers();

        var puntos = [];
        if (datos.oficina) {
            L.marker([datos.oficina.lat, datos.oficina.lng], { icon: iconoOficina(L), zIndexOffset: -1000 })
                .bindTooltip('Oficina de la sede')
                .addTo(capa);
            puntos.push([datos.oficina.lat, datos.oficina.lng]);
        }

        var paradas = (datos.ruta && datos.ruta.paradas) || [];
        paradas.forEach(function (p) {
            if (p.lat == null) return;
            var m = L.marker([p.lat, p.lng], { icon: iconoParada(L, p), draggable: !!opciones.editable && !!p.domicilio_id });
            m.bindPopup(tarjeta(p));
            if (opciones.alMoverPin) {
                m.on('dragend', function () {
                    var ll = m.getLatLng();
                    opciones.alMoverPin(p, ll.lat, ll.lng);
                });
            }
            if (opciones.alTocarParada) m.on('click', function () { opciones.alTocarParada(p); });
            m.addTo(capa);
            puntos.push([p.lat, p.lng]);
        });

        if (puntos.length > 1) {
            L.polyline(puntos, { color: '#ffffff', weight: 8, opacity: 0.9 }).addTo(capa);
            L.polyline(puntos, { color: '#496163', weight: 4, opacity: 0.9, dashArray: '1 9', lineCap: 'round' }).addTo(capa);
        }
        // Marcadores encima de la línea.
        capa.eachLayer(function (c) { if (c.setZIndexOffset) c.setZIndexOffset(c.options.zIndexOffset || 0); });

        if (puntos.length) mapa.fitBounds(puntos, { padding: [40, 40], maxZoom: 16 });
        else mapa.setView([19.70, -101.19], 12);
        setTimeout(function () { mapa.invalidateSize(); }, 50);
        return mapa;
    }

    global.SiNotificoMapa = { dibujar: dibujar, textoPlazo: textoPlazo, COLOR: COLOR };
})(window);
