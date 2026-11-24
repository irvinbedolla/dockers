{{--
    Cuadros de texto que crecen hasta mostrar todo su contenido, sin barra
    de desplazamiento: <textarea class="form-control js-autoalto">.
    Se usa en "Describe brevemente el motivo de tu solicitud" (revisar
    solicitud y revisar audiencia) para leer el motivo de corrido.

    Muchos están en pestañas o secciones que arrancan ocultas (altura 0), así
    que se vuelven a medir cuando cambia su ancho (al mostrarse, al pasar de
    lectura a edición o al cambiar la ventana) y al escribir.

    Incluir una vez por página, dentro de la sección de scripts.
--}}
<style>
    textarea.js-autoalto { min-height: 4.5em; overflow: hidden; resize: vertical; line-height: 1.5; }
</style>
<script>
    (function () {
        if (window.autoAltoListo) { return; }
        window.autoAltoListo = true;

        function ajustar(t) {
            if (!t.offsetParent) { return; } // oculto: se mide al mostrarse
            t.style.height = 'auto';
            t.style.height = (t.scrollHeight + 2) + 'px';
        }

        var observador = window.ResizeObserver
            ? new ResizeObserver(function (entradas) {
                entradas.forEach(function (e) {
                    // Sólo cuando cambia el ancho, no por la altura que le ponemos.
                    var ancho = Math.round(e.contentRect.width);
                    if (ancho > 0 && e.target.dataset.autoaltoAncho !== String(ancho)) {
                        e.target.dataset.autoaltoAncho = ancho;
                        ajustar(e.target);
                    }
                });
            })
            : null;

        function preparar() {
            document.querySelectorAll('textarea.js-autoalto').forEach(function (t) {
                if (t.dataset.autoalto) { return; }
                t.dataset.autoalto = '1';
                t.addEventListener('input', function () { ajustar(t); });
                if (observador) { observador.observe(t); }
                ajustar(t);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', preparar);
        } else {
            preparar();
        }

        // Respaldo sin ResizeObserver: tras cualquier clic (pestañas, Editar).
        if (!observador) {
            document.addEventListener('click', function () {
                setTimeout(function () { document.querySelectorAll('textarea.js-autoalto').forEach(ajustar); }, 0);
            });
        }
    })();
</script>
