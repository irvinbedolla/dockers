<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · {{ $periodo }}</title>
    <style>
        body { margin: 0; background: #E9EEEE; font-family: Arial, sans-serif; }
        .barra { position: sticky; top: 0; z-index: 2; display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap; padding: 10px 16px; background: #354647; color: #fff; font-size: 13px; }
        .barra button { border: 0; border-radius: 8px; padding: 8px 14px; font-weight: bold; cursor: pointer; background: #CEA845; color: #2E3C3D; font-size: 13px; }
        .barra button[disabled] { opacity: .6; cursor: wait; }
        .hoja { width: 1200px; margin: 16px auto; padding: 24px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .hoja .et { font-size: 11px; }
        .hoja .et-t th { font-size: 9.5px; padding: 6px 4px; }
        .hoja .et-t td { padding: 5px 4px; }
        .hoja .et-t td small, .hoja .et-ley, .hoja .et-nota, .hoja .et-glo td { font-size: 9.5px; }
        .hoja .et-gr td { height: 11px; }
    </style>
</head>
<body>
    <div class="barra">
        <span id="etEstado">Preparando la imagen…</span>
        <button type="button" id="etDescargar" disabled>Descargar imagen (PNG)</button>
    </div>
    <div class="hoja" id="etHoja">
        @include('estadisticas.turnos._contenido')
    </div>

    {{-- La misma librería que usa la agenda de turnos (turnos/agenda/imagen). --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
    (function () {
        var boton = document.getElementById('etDescargar');
        var estado = document.getElementById('etEstado');
        var hoja = document.getElementById('etHoja');
        var nombre = {!! json_encode($archivo.'.png') !!};

        function descargar() {
            if (typeof html2canvas === 'undefined') {
                estado.textContent = 'No se pudo cargar el generador de imágenes. Descarga el PDF.';
                return;
            }
            boton.disabled = true;
            estado.textContent = 'Generando la imagen…';
            // Lienzos muy altos fallan en el navegador: se baja la escala.
            var escala = Math.min(2, 30000 / Math.max(hoja.scrollHeight, 1));
            if (escala < 0.6) {
                estado.textContent = 'Es demasiado largo para una imagen. Usa Excel o PDF, o agrupa por semana o por mes.';
                boton.disabled = false;
                return;
            }
            html2canvas(hoja, { scale: escala, backgroundColor: '#ffffff', useCORS: true }).then(function (lienzo) {
                lienzo.toBlob(function (blob) {
                    var a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = nombre;
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
                    estado.textContent = 'Listo: se descargó ' + nombre + '. Puedes cerrar esta pestaña.';
                    boton.disabled = false;
                }, 'image/png');
            }).catch(function () {
                estado.textContent = 'No se pudo generar la imagen. Descarga el PDF.';
                boton.disabled = false;
            });
        }

        boton.addEventListener('click', descargar);
        window.addEventListener('load', function () { boton.disabled = false; descargar(); });
    })();
    </script>
</body>
</html>
