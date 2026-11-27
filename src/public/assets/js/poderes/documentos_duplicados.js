/*
 * Poderes (registro público, editar y agregar representante): detecta cuando
 * el mismo PDF se elige en dos campos (p. ej. el Acta Constitutiva también es
 * el Documento que acredita la personería) y lo manda UNA sola vez.
 *
 * - Al elegir un archivo compara, byte por byte, contra los otros campos del
 *   formulario que pesan exactamente lo mismo y avisa debajo del campo repetido.
 * - Al enviar (si ninguna otra validación lo detuvo) deshabilita el campo
 *   repetido para que no viaje y agrega documentos_iguales[campo] = campo que
 *   sí lleva el archivo. El servidor guarda el PDF una vez y enlaza los dos
 *   campos al mismo documento (App\Support\DocumentosPoder). Si el aviso no
 *   alcanzó a salir, el servidor también lo detecta por el contenido.
 *
 * Se compara sin crypto.subtle porque ese sólo existe en HTTPS. Los textos van
 * con escapes \uXXXX para que se lean bien aunque el servidor no mande el
 * charset del .js.
 */
(function () {
    'use strict';

    var CAMPO_IGUALES = 'documentos_iguales';
    var TROZO = 4 * 1024 * 1024;

    function esFormularioDeArchivos(form) {
        return form && form.querySelectorAll && (form.getAttribute('enctype') || '').toLowerCase() === 'multipart/form-data';
    }

    function leer(archivo, inicio) {
        return archivo.slice(inicio, inicio + TROZO).arrayBuffer();
    }

    /** ¿Mismo contenido? Se detiene en el primer trozo distinto. */
    function mismoContenido(a, b) {
        if (a === b) {
            return Promise.resolve(true);
        }
        if (a.size !== b.size) {
            return Promise.resolve(false);
        }
        function desde(inicio) {
            if (inicio >= a.size) {
                return Promise.resolve(true);
            }
            return Promise.all([leer(a, inicio), leer(b, inicio)]).then(function (trozos) {
                var x = new Uint8Array(trozos[0]);
                var y = new Uint8Array(trozos[1]);
                for (var i = 0; i < x.length; i++) {
                    if (x[i] !== y[i]) {
                        return false;
                    }
                }
                return desde(inicio + TROZO);
            });
        }
        return desde(0);
    }

    function etiqueta(input) {
        var grupo = input.closest('.form-group') || input.parentNode;
        var label = grupo && grupo.querySelector('label');
        var texto = label ? label.textContent.replace(/\*/g, '').trim() : '';
        return texto || input.name;
    }

    /** input repetido => [su archivo, el del campo original] cuando se compararon. */
    var comparados = new WeakMap();

    function quitarAviso(input) {
        delete input.dataset.duplicadoDe;
        comparados.delete(input);
        var aviso = input.parentNode && input.parentNode.querySelector('[data-aviso-duplicado="' + input.name + '"]');
        if (aviso) {
            aviso.remove();
        }
    }

    function ponerAviso(input, original) {
        quitarAviso(input);
        input.dataset.duplicadoDe = original.name;
        comparados.set(input, [input.files[0], original.files[0]]);
        var aviso = document.createElement('small');
        aviso.className = 'd-block text-success';
        aviso.setAttribute('data-aviso-duplicado', input.name);
        aviso.textContent = 'Es el mismo archivo que «' + etiqueta(original) + '»: se enviará una sola vez '
            + 'y los dos campos abrirán el mismo documento.';
        input.insertAdjacentElement('afterend', aviso);
    }

    var turnos = new WeakMap();

    /** Revisa todos los campos de archivo del formulario, en el orden en que aparecen. */
    function revisar(form) {
        var turno = (turnos.get(form) || 0) + 1;
        turnos.set(form, turno);

        var conArchivo = Array.prototype.filter.call(form.querySelectorAll('input[type="file"]'), function (input) {
            return input.name && input.files && input.files.length === 1;
        });
        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            if (conArchivo.indexOf(input) === -1) {
                quitarAviso(input);
            }
        });

        // Primero los campos que aceptan PDF grandes (limite_archivos.js): si
        // el mismo documento está en uno de ellos y en uno de 10 MB, viaja en
        // el grande. El servidor ordena igual (PoderController::prepararDocumentos).
        var esGrande = window.PoderesDocumentos ? window.PoderesDocumentos.esGrande : function () { return false; };
        conArchivo = conArchivo.filter(esGrande).concat(conArchivo.filter(function (input) {
            return !esGrande(input);
        }));

        // Cada campo se compara con los anteriores que pesan lo mismo; el
        // primero de un grupo de iguales es el que lleva el archivo.
        var cadena = Promise.resolve();
        conArchivo.forEach(function (input, i) {
            cadena = cadena.then(function () {
                var archivo = input.files[0];
                var candidatos = conArchivo.slice(0, i).filter(function (anterior) {
                    return !anterior.dataset.duplicadoDe && anterior.files[0].size === archivo.size;
                });
                var busqueda = Promise.resolve(null);
                candidatos.forEach(function (anterior) {
                    busqueda = busqueda.then(function (encontrado) {
                        return encontrado || mismoContenido(anterior.files[0], archivo).then(function (igual) {
                            return igual ? anterior : null;
                        });
                    });
                });
                return busqueda.then(function (original) {
                    if (turnos.get(form) !== turno) {
                        return;
                    }
                    if (original) {
                        ponerAviso(input, original);
                    } else {
                        quitarAviso(input);
                    }
                });
            });
        });
        cadena.catch(function () { /* si no se puede leer, decide el servidor */ });
    }

    document.addEventListener('change', function (evento) {
        var input = evento.target;
        if (input && input.type === 'file' && esFormularioDeArchivos(input.form)) {
            revisar(input.form);
        }
    });

    /** Deshace lo que se hizo en un envío anterior que no se completó. */
    function restaurar(form) {
        form.querySelectorAll('input[data-omitido-por-duplicado]').forEach(function (input) {
            input.disabled = false;
            delete input.dataset.omitidoPorDuplicado;
        });
        form.querySelectorAll('input[data-igual-generado]').forEach(function (oculto) {
            oculto.remove();
        });
    }

    // Antes de las validaciones: todo como lo dejó el usuario.
    document.addEventListener('submit', function (evento) {
        if (esFormularioDeArchivos(evento.target)) {
            restaurar(evento.target);
        }
    }, true);

    // Después de las validaciones (fase de burbuja en document): si el envío
    // sigue, el campo repetido no viaja y se avisa cuál sí lleva el archivo.
    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (evento.defaultPrevented || !esFormularioDeArchivos(form)) {
            return;
        }
        form.querySelectorAll('input[type="file"][data-duplicado-de]').forEach(function (input) {
            var original = form.querySelector('input[type="file"][name="' + input.dataset.duplicadoDe + '"]');
            var par = comparados.get(input);
            // Sólo si los dos campos siguen con los mismos archivos que se
            // compararon (un campo se puede limpiar por código sin "change").
            if (input.disabled || !original || original.disabled || !par
                || !input.files || input.files[0] !== par[0]
                || !original.files || original.files[0] !== par[1]) {
                return;
            }
            input.disabled = true;
            input.dataset.omitidoPorDuplicado = '1';

            var oculto = document.createElement('input');
            oculto.type = 'hidden';
            oculto.name = CAMPO_IGUALES + '[' + input.name + ']';
            oculto.value = original.name;
            oculto.setAttribute('data-igual-generado', '1');
            form.appendChild(oculto);
        });
    });

    // Al volver con "Atrás" el navegador puede restaurar la página tal cual.
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form').forEach(function (form) {
            if (esFormularioDeArchivos(form)) {
                restaurar(form);
            }
        });
    });
})();
