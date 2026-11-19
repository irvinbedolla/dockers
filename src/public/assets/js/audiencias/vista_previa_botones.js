/*
 * Vista previa del acta de audiencia (audiencias/audiencia_revisar y
 * audiencia_revisar_patronal): cada botón muestra un spinner mientras se
 * procesa y no se puede presionar dos veces.
 *
 *   Terminar        spinner "Terminando…" desde que se envía hasta que carga la siguiente página.
 *   Convenio / Acta spinner "Generando…": se abre de inmediato una pestaña de espera (así el
 *                   navegador no la bloquea), se prepara la selección de citados y luego
 *                   se carga el PDF en esa pestaña.
 *   Regresar        spinner "Regresando…".
 *
 *   VistaPreviaBotones.iniciar()                         Terminar y Regresar.
 *   VistaPreviaBotones.abrirDocumento(btn, url, preparar) Convenio / Acta; preparar() puede
 *                                                         regresar una promesa (false = no abrir).
 */
(function (global) {
    var SPINNER = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';

    function activar(btn, texto) {
        if (!btn || btn.dataset.cargando === '1') { return false; }
        btn.dataset.cargando = '1';
        btn.dataset.htmlOriginal = btn.innerHTML;
        btn.innerHTML = SPINNER + texto;
        btn.classList.add('disabled');
        btn.setAttribute('aria-busy', 'true');
        btn.style.pointerEvents = 'none';
        return true;
    }

    function desactivar(btn) {
        if (!btn || btn.dataset.cargando !== '1') { return; }
        btn.innerHTML = btn.dataset.htmlOriginal;
        delete btn.dataset.cargando;
        delete btn.dataset.htmlOriginal;
        btn.classList.remove('disabled');
        btn.removeAttribute('aria-busy');
        btn.style.pointerEvents = '';
    }

    function aviso(icono, titulo, texto, html) {
        if (global.Swal && typeof Swal.fire === 'function') {
            return Swal.fire({ icon: icono, title: titulo, text: html ? undefined : texto, html: html, confirmButtonColor: '#496163' });
        }
    }

    /** Pestaña de espera, abierta en el mismo clic para que el navegador no la bloquee. */
    function ventanaEspera() {
        var w = global.open('', '_blank');
        if (w) {
            try {
                w.document.write('<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Generando documento…</title></head>'
                    + '<body style="margin:0;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;color:#354647;background:#F5F7F7">'
                    + '<div style="text-align:center"><div style="width:42px;height:42px;margin:0 auto 14px;border:4px solid #CED9D9;border-top-color:#496163;border-radius:50%;animation:g 1s linear infinite"></div>'
                    + 'Generando el documento…</div><style>@keyframes g{to{transform:rotate(360deg)}}</style></body></html>');
                w.document.close();
            } catch (e) { /* sin acceso a la pestaña: igual se carga el PDF */ }
        }
        return w;
    }

    function abrirDocumento(btn, url, preparar) {
        if (!activar(btn, 'Generando…')) { return; }
        var w = ventanaEspera();

        Promise.resolve(typeof preparar === 'function' ? preparar() : true)
            .then(function (ok) {
                if (ok === false) {
                    if (w) { w.close(); }
                    desactivar(btn);
                    return;
                }
                var destino = typeof url === 'function' ? url() : url;
                if (w && !w.closed) {
                    w.location.href = destino;
                } else {
                    // El navegador bloqueó la pestaña: se ofrece la liga.
                    aviso('info', 'Documento listo', null, 'Tu navegador bloqueó la pestaña nueva. <a href="' + destino + '" target="_blank" rel="noopener">Abrir el documento</a>');
                }
                // El PDF se genera en la otra pestaña; el botón se libera en un momento.
                setTimeout(function () { desactivar(btn); }, 1500);
            })
            .catch(function (err) {
                if (w) { w.close(); }
                desactivar(btn);
                aviso('error', 'No se pudo generar el documento', (err && err.message) || 'Revisa tu conexión e intenta de nuevo.');
            });
    }

    function iniciar() {
        var form = document.getElementById('form_roles');
        var enviando = false;

        if (form) {
            // "submit" sólo llega si el formulario pasó la validación del navegador.
            form.addEventListener('submit', function (e) {
                if (enviando) { e.preventDefault(); return; }
                enviando = true;
                var boton = (e.submitter && e.submitter.name === 'bandera') ? e.submitter : form.querySelector('button[name="bandera"][value="1"]');
                // No se usa "disabled": el valor de "bandera" del botón tiene que viajar con el formulario.
                activar(boton, boton && boton.value === '1' ? 'Terminando…' : 'Guardando…');
            });
        }

        document.querySelectorAll('#form_roles a[name="bandera"][value="5"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                if (a.dataset.cargando === '1') { e.preventDefault(); return; }
                activar(a, 'Regresando…');
            });
        });

        // Al volver con "Atrás" del navegador, los botones regresan a la normalidad.
        global.addEventListener('pageshow', function (e) {
            if (!e.persisted) { return; }
            enviando = false;
            document.querySelectorAll('[data-cargando="1"]').forEach(desactivar);
            var loader = document.getElementById('submit_loader');
            if (loader) { loader.style.display = 'none'; }
        });
    }

    global.VistaPreviaBotones = { iniciar: iniciar, abrirDocumento: abrirDocumento, activar: activar, desactivar: desactivar, aviso: aviso };
})(window);
