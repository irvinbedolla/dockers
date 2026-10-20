{{--
    Barra "cargando" arriba de la página: aparece en cuanto se da clic a un
    enlace o se envía un formulario que cambia de página, para que se note
    que el sistema ya está trabajando mientras el servidor responde.

    No sale en enlaces que abren otra pestaña, descargan, son anclas (#) o
    abren modales, ni en lo marcado con data-sin-carga (p. ej. formularios
    que sólo descargan un archivo). Si la página no cambia (una descarga),
    se quita sola a los 20 s. Sólo aparece si la espera pasa de 300 ms: en
    un servidor rápido no parpadea.
--}}
<div id="cargaPagina" class="carga-pagina" role="progressbar" aria-label="Cargando" aria-hidden="true"><span></span></div>
<style>
    .carga-pagina { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 3000; pointer-events: none; opacity: 0; transition: opacity .2s; }
    .carga-pagina span { display: block; height: 100%; width: 0; background: linear-gradient(90deg, #CEA845, #E6C766); box-shadow: 0 0 8px rgba(206, 168, 69, .7); }
    .carga-pagina.is-activa { opacity: 1; }
    .carga-pagina.is-activa span { animation: cargaAvanza 8s cubic-bezier(.1, .7, .3, 1) forwards; }
    @keyframes cargaAvanza { 0% { width: 0; } 20% { width: 45%; } 60% { width: 75%; } 100% { width: 92%; } }
    body.carga-activa, body.carga-activa a, body.carga-activa button { cursor: progress; }
</style>
<script>
(function () {
    var barra = document.getElementById('cargaPagina');
    if (!barra) { return; }
    var reloj = null;
    var espera = null;

    // Sólo si tarda: una página que responde rápido no debe parpadear.
    function pronto() {
        clearTimeout(espera);
        espera = setTimeout(mostrar, 300);
    }

    function mostrar() {
        barra.classList.remove('is-activa');
        void barra.offsetWidth; // reinicia la animación
        barra.classList.add('is-activa');
        barra.setAttribute('aria-hidden', 'false');
        document.body.classList.add('carga-activa');
        clearTimeout(reloj);
        reloj = setTimeout(ocultar, 20000);
    }
    function ocultar() {
        clearTimeout(espera);
        barra.classList.remove('is-activa');
        barra.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('carga-activa');
        clearTimeout(reloj);
    }
    window.cargaPagina = { mostrar: mostrar, ocultar: ocultar };

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
        var a = e.target.closest('a[href]');
        if (!a || a.closest('[data-sin-carga]')) { return; }
        var href = a.getAttribute('href') || '';
        if (href === '' || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) { return; }
        if ((a.target && a.target !== '_self') || a.hasAttribute('download') || a.hasAttribute('data-bs-toggle') || a.hasAttribute('data-toggle')) { return; }
        var destino;
        try { destino = new URL(a.href, location.href); } catch (err) { return; }
        if (destino.origin !== location.origin) { return; }
        if (destino.pathname === location.pathname && destino.search === location.search && destino.hash) { return; }
        // Deja que el resto de los manejadores decidan; si alguno canceló, no se muestra.
        setTimeout(function () { if (!e.defaultPrevented) { pronto(); } }, 0);
    });

    document.addEventListener('submit', function (e) {
        var f = e.target;
        var quien = e.submitter;
        if (f.closest('[data-sin-carga]') || (quien && quien.hasAttribute('data-sin-carga'))) { return; }
        var target = (quien && quien.getAttribute('formtarget')) || f.getAttribute('target');
        if (target && target !== '_self') { return; }
        setTimeout(function () { if (!e.defaultPrevented) { pronto(); } }, 0);
    });

    // Al volver con "Atrás" (página en caché) la barra no debe quedarse puesta.
    window.addEventListener('pageshow', ocultar);
})();
</script>
