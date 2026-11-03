/*
 * Poderes (registro público, editar y agregar representante): antes de
 * enviar, revisa que los documentos sean PDF de máximo 10 MB y que entre
 * todos no pasen de 40 MB. El servidor acepta hasta 45 MB por envío; si se
 * pasa, rechaza todo y se pierde lo capturado (errors/413).
 *
 * Sólo cuenta los campos visibles y habilitados: las secciones ocultas del
 * formulario no se envían. Corre antes que las demás validaciones del
 * formulario (escucha en captura) para no mostrar "cargando" en vano.
 * Los textos van con escapes \uXXXX para que se lean bien aunque el
 * servidor no mande el charset del .js.
 */
(function () {
    'use strict';

    var MB = 1024 * 1024;
    var POR_ARCHIVO = 10 * MB;
    var TOTAL = 40 * MB;

    function megas(bytes) {
        return (bytes / MB).toFixed(1).replace(/\.0$/, '') + ' MB';
    }

    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!form || !form.querySelectorAll || (form.getAttribute('enctype') || '').toLowerCase() !== 'multipart/form-data') {
            return;
        }

        var total = 0;
        var problema = null;

        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            if (problema || input.disabled || input.offsetParent === null) {
                return;
            }
            Array.prototype.forEach.call(input.files || [], function (archivo) {
                if (problema) {
                    return;
                }
                if (!/\.pdf$/i.test(archivo.name)) {
                    problema = '\u00ab' + archivo.name + '\u00bb no es un PDF. Sube los documentos en formato PDF.';
                } else if (archivo.size > POR_ARCHIVO) {
                    problema = '\u00ab' + archivo.name + '\u00bb pesa ' + megas(archivo.size) + '; cada documento puede pesar m\u00e1ximo 10 MB.';
                }
                total += archivo.size;
            });
        });

        if (!problema && total > TOTAL) {
            problema = 'Entre todos, los documentos pesan ' + megas(total) + ' y el m\u00e1ximo por env\u00edo es 40 MB. '
                + 'Comprime los PDF (por ejemplo, escaneados en blanco y negro) e intenta de nuevo.';
        }

        if (problema) {
            evento.preventDefault();
            evento.stopImmediatePropagation();
            alert(problema);
        }
    }, true);
})();
