<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte estadístico · {{ $periodo }}</title>
    {{-- Reporte estadístico como imágenes: cada lámina se baja como PNG (1920 × 1080); todas juntas en un ZIP. --}}
    @include('estadisticas.reporte._estilos')
    <style>
        body { margin: 0; background: #E9EEEE; font-family: Arial, sans-serif; }
        .barra { position: sticky; top: 0; z-index: 2; display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap; padding: 10px 16px; background: #354647; color: #fff; font-size: 13px; }
        .barra button, .lam-pie button { border: 0; border-radius: 8px; padding: 8px 14px; font-weight: bold; cursor: pointer; background: #CEA845; color: #2E3C3D; font-size: 13px; }
        .barra button[disabled], .lam-pie button[disabled] { opacity: .6; cursor: wait; }
        .lam-caja { width: 960px; margin: 16px auto; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .lam-pie { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 6px 10px; font-size: 12px; color: #5E6E6F; border-top: 1px solid #E3E8E8; }
        .lam-pie button { padding: 5px 10px; font-size: 12px; background: #E7ECEC; }
    </style>
</head>
<body>
    <div class="barra">
        <span id="reEstado">Preparando las imágenes…</span>
        <button type="button" id="reTodas" disabled>Descargar todas (ZIP)</button>
    </div>

    <div id="reLaminas">
        @include('estadisticas.reporte._laminas')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script>
    (function () {
        // Sin acentos: algunos descompresores de Windows los muestran mal.
        var archivo = {!! json_encode(\Illuminate\Support\Str::ascii($archivo)) !!};
        var estado = document.getElementById('reEstado');
        var botonTodas = document.getElementById('reTodas');
        var cont = document.getElementById('reLaminas');
        var laminas = Array.prototype.slice.call(cont.querySelectorAll(':scope > .re-lam'));

        // Cada lámina en su caja, con su botón de descarga.
        laminas.forEach(function (lam, i) {
            var caja = document.createElement('div');
            caja.className = 'lam-caja';
            cont.insertBefore(caja, lam);
            caja.appendChild(lam);
            var pie = document.createElement('div');
            pie.className = 'lam-pie';
            pie.innerHTML = '<span>Lámina ' + (i + 1) + '</span><button type="button">Descargar esta lámina</button>';
            caja.appendChild(pie);
            pie.querySelector('button').addEventListener('click', function () {
                var b = this; b.disabled = true;
                lienzo(lam).then(function (blob) { bajar(blob, nombre(lam, i) + '.png'); })
                    .catch(fallo).then(function () { b.disabled = false; });
            });
        });

        function nombre(lam, i) { return archivo + ' - ' + (lam.dataset.lamina || String(i + 1).padStart(2, '0')); }

        function lienzo(lam) {
            return html2canvas(lam, { scale: 2, backgroundColor: '#ffffff', useCORS: true, width: 960, height: 540 })
                .then(function (c) { return new Promise(function (ok) { c.toBlob(ok, 'image/png'); }); });
        }

        function bajar(blob, nom) {
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = nom;
            document.body.appendChild(a);
            a.click();
            setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
        }

        function fallo() { estado.textContent = 'No se pudo generar la imagen. Descarga el PDF.'; }

        function todas() {
            if (typeof html2canvas === 'undefined' || typeof JSZip === 'undefined') {
                estado.textContent = 'No se pudo cargar el generador de imágenes. Revisa tu conexión o descarga el PDF.';
                return;
            }
            botonTodas.disabled = true;
            var zip = new JSZip();
            var i = 0;
            (function siguiente() {
                if (i >= laminas.length) {
                    estado.textContent = 'Comprimiendo…';
                    zip.generateAsync({ type: 'blob' }).then(function (blob) {
                        bajar(blob, archivo + '.zip');
                        estado.textContent = 'Listo: se descargó ' + archivo + '.zip (' + laminas.length + ' imágenes). También puedes bajar cada lámina por separado.';
                        botonTodas.disabled = false;
                    });
                    return;
                }
                estado.textContent = 'Generando lámina ' + (i + 1) + ' de ' + laminas.length + '…';
                lienzo(laminas[i]).then(function (blob) {
                    zip.file(nombre(laminas[i], i) + '.png', blob);
                    i++;
                    siguiente();
                }).catch(function () { fallo(); botonTodas.disabled = false; });
            })();
        }

        botonTodas.addEventListener('click', todas);
        // Las fuentes deben estar cargadas antes de dibujar.
        window.addEventListener('load', function () {
            (document.fonts ? document.fonts.ready : Promise.resolve()).then(function () { botonTodas.disabled = false; todas(); });
        });
    })();
    </script>
</body>
</html>
