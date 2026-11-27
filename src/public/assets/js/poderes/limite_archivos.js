/*
 * Poderes (registro público, editar y agregar representante): revisa que los
 * documentos sean PDF dentro de su límite:
 *  - El acta constitutiva y el documento que acredita la personería (CAMPOS_GRANDES):
 *    hasta 150 MB; los de más de 20 MB se reducen en el servidor.
 *  - Los demás: hasta 10 MB.
 *  - Entre todos, máximo 400 MB por envío. El servidor (pool "poderes" de
 *    PHP-FPM y nginx) acepta hasta 410 MB; si se pasa, rechaza todo y se
 *    pierde lo capturado (errors/413).
 * Los límites deben coincidir con PoderController (PDF_MAX_KB, PDF_GRANDE_KB,
 * DOCUMENTOS_GRANDES).
 *
 * Al elegir un archivo avisa y limpia el campo si se pasa. Al enviar revisa
 * otra vez sólo los campos visibles y habilitados (las secciones ocultas no se
 * envían) y corre antes que las demás validaciones (escucha en captura) para
 * no mostrar "cargando" en vano.
 *
 * Un archivo repetido en dos campos se cuenta una vez, porque viaja una sola
 * vez (documentos_duplicados.js); si uno de los dos campos es grande, el
 * archivo viaja en ése y tiene su límite (window.PoderesDocumentos.esGrande).
 * Este archivo debe cargarse antes que documentos_duplicados.js.
 * Los textos van con escapes \uXXXX para que se lean bien aunque el servidor
 * no mande el charset del .js.
 */
(function () {
    'use strict';

    var MB = 1024 * 1024;
    var POR_ARCHIVO = 10 * MB;
    var POR_ARCHIVO_GRANDE = 150 * MB;
    var TOTAL = 400 * MB;
    var CAMPOS_GRANDES = ['documentoIne_Moral', 'documentoActa_Moral', 'documentoPoder', 'documentoPoder_pF'];

    function megas(bytes) {
        return (bytes / MB).toFixed(1).replace(/\.0$/, '') + ' MB';
    }

    function esGrande(input) {
        return CAMPOS_GRANDES.indexOf(input.name) !== -1;
    }

    function limite(input) {
        return esGrande(input) ? POR_ARCHIVO_GRANDE : POR_ARCHIVO;
    }

    function esFormularioDeArchivos(form) {
        return form && form.querySelectorAll && (form.getAttribute('enctype') || '').toLowerCase() === 'multipart/form-data';
    }

    function camposGrandes(form) {
        return Array.prototype.filter.call(form.querySelectorAll('input[type="file"]'), esGrande);
    }

    function demasiado(archivo, input) {
        var mensaje = '«' + archivo.name + '» pesa ' + megas(archivo.size) + '; este documento puede pesar máximo '
            + megas(limite(input)) + '.';
        if (!esGrande(input) && camposGrandes(input.form).length) {
            mensaje += ' Si es el mismo PDF que el acta constitutiva o que el documento que acredita la personería, '
                + 'súbelo primero en ese campo y después aquí.';
        }
        return mensaje;
    }

    /*
     * Al elegir: un campo de 10 MB con un PDF más pesado se deja pasar sólo si
     * algún campo grande ya trae un archivo del mismo tamaño (documentos_duplicados.js
     * confirma que es el mismo y, si no lo es, se rechaza al enviar).
     */
    document.addEventListener('change', function (evento) {
        var input = evento.target;
        if (!input || input.type !== 'file' || !esFormularioDeArchivos(input.form) || !input.files || !input.files.length) {
            return;
        }
        var archivo = input.files[0];
        if (!/\.pdf$/i.test(archivo.name) || archivo.size <= limite(input)) {
            return;
        }
        var mismoTamano = !esGrande(input) && archivo.size <= POR_ARCHIVO_GRANDE && camposGrandes(input.form).some(function (grande) {
            return grande.files && grande.files.length && grande.files[0].size === archivo.size;
        });
        if (mismoTamano) {
            return;
        }
        alert(demasiado(archivo, input));
        input.value = '';
    }, true);

    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!esFormularioDeArchivos(form)) {
            return;
        }

        var total = 0;
        var problema = null;

        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            if (problema || input.disabled || input.offsetParent === null || input.dataset.duplicadoDe) {
                return;
            }
            Array.prototype.forEach.call(input.files || [], function (archivo) {
                if (problema) {
                    return;
                }
                if (!/\.pdf$/i.test(archivo.name)) {
                    problema = '«' + archivo.name + '» no es un PDF. Sube los documentos en formato PDF.';
                } else if (archivo.size > limite(input)) {
                    problema = demasiado(archivo, input);
                }
                total += archivo.size;
            });
        });

        if (!problema && total > TOTAL) {
            problema = 'Entre todos, los documentos pesan ' + megas(total) + ' y el máximo por envío es 400 MB. '
                + 'Comprime los PDF (por ejemplo, escaneados en blanco y negro) e intenta de nuevo.';
        }

        if (problema) {
            evento.preventDefault();
            evento.stopImmediatePropagation();
            alert(problema);
        }
    }, true);

    window.PoderesDocumentos = { esGrande: esGrande };
})();
