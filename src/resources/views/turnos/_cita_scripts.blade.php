{{--
    Lógica del formulario de cita: selector de día y hora, validación por pasos
    y ayudas. Espera $modo ('publico' | 'ventanilla'). En ventanilla no se manda
    externo=1, así que no aplica el límite diario del formulario público.
--}}
@php
    $ventanilla = ($modo ?? 'publico') === 'ventanilla';
@endphp
    @include('turnos._cita_horario_scripts', ['modo' => $modo ?? 'publico'])
    {{-- Ayudas del formulario: explicación del trámite, oficina, avance por pasos,
         confirmación del correo, contador del motivo y horario que caduca si
         cambia algo que lo determina. --}}
    <script>
    (function () {
        'use strict';
        var form = document.getElementById('form_roles');
        if (!form) return;

        var AYUDA_TRAMITE = {
            'Asesoría':     'Te orientamos sobre tus derechos laborales y qué puedes hacer en tu caso.',
            'Solicitud':    'Inicias la conciliación: citamos a tu patrón (o a la otra parte) para buscar un acuerdo sin ir a juicio.',
            'Ratificación': 'Ya tienen un acuerdo (por ejemplo, un finiquito) y vienen a firmarlo ante el Centro para que tenga validez.'
        };

        var tipo = document.getElementById('tipo');
        var ayudaTramite = document.getElementById('gcAyudaTramite');
        var municipio = document.getElementById('municipio_citado');
        var oficina = document.getElementById('gcOficina');
        var oficinaNombre = document.getElementById('modulo_delegacion');
        var delegacion = document.getElementById('delegacion');
        var tipoCaso = document.getElementById('gcTipoCaso');
        var ayudaCaso = document.getElementById('gcAyudaCaso');
        var correo = document.getElementById('gcCorreo');
        var correo2 = document.getElementById('gcCorreo2');
        var conflicto = document.getElementById('gcConflicto');
        var contador = document.getElementById('gcContador');
        var ayudaCal = document.getElementById('gcAyudaCalendario');
        var fecha = document.getElementById('fecha_turno');
        var hora = document.getElementById('hora_turno');
        var resumen = document.getElementById('resumenTurno');

        // Paso 1
        function pintarTramite() {
            var t = AYUDA_TRAMITE[tipo.value];
            ayudaTramite.textContent = t || 'Elige una opción y te explicamos de qué se trata.';
            ayudaTramite.classList.toggle('is-activa', !!t);
        }
        function pintarOficina() {
            // El listener original pone la sede en el campo oculto; aquí sólo se refleja.
            var lista = !!delegacion.value && !!municipio.value;
            oficina.classList.toggle('is-lista', lista);
            if (!lista) oficinaNombre.textContent = 'Elige tu municipio';
        }

        // Paso 3
        function pintarCaso() {
            // La pregunta de casos de excepción ya no está en el formulario público.
            if (!tipoCaso || !ayudaCaso) return;
            if (!tipoCaso.value) {
                ayudaCaso.textContent = 'Si no estás seguro, elige "No, ninguno de estos".';
            } else if (tipoCaso.value === 'Ninguno') {
                ayudaCaso.textContent = 'Tu cita será con un auxiliar de conciliación.';
            } else {
                ayudaCaso.textContent = 'Por tu caso te atenderá personal especializado. Los horarios disponibles pueden ser distintos.';
            }
        }
        function contar() { contador.textContent = conflicto.value.length; }

        // Correo
        function validarCorreos() {
            var distinto = correo2.value && correo.value.trim().toLowerCase() !== correo2.value.trim().toLowerCase();
            correo2.setCustomValidity(distinto ? 'Los correos no coinciden.' : '');
        }
        window.gcValidarCorreos = validarCorreos;

        function pintarCalendario() {
            var listo = !!tipo.value && !!delegacion.value;
            ayudaCal.textContent = listo
                ? 'Los horarios en verde están libres. Toca uno y luego "Confirmar".'
                : 'Primero elige el trámite y el municipio (paso 1).';
        }

        // Avance por pasos
        function pasoCompleto(n) {
            if (n === 4) return !!fecha.value && !!hora.value;
            var campos = form.querySelectorAll('fieldset[data-paso="' + n + '"] [required]');
            return Array.prototype.every.call(campos, function (c) { return c.checkValidity(); });
        }
        function actualizarPasos() {
            document.querySelectorAll('#gcPasos li').forEach(function (li) {
                li.classList.toggle('is-listo', pasoCompleto(parseInt(li.getAttribute('data-paso'), 10)));
            });
        }
        window.gcActualizarPasos = actualizarPasos;

        // Restaura la clase del resumen cuando se confirma un horario nuevo.
        var confirmar = document.getElementById('confirmarTurno');
        if (confirmar) confirmar.addEventListener('click', function () { resumen.className = 'gc-turno'; }, true);

        tipo.addEventListener('change', function () { pintarTramite(); pintarCalendario(); });
        municipio.addEventListener('change', function () {
            // Si se regresa a "Elige el municipio", la sede anterior no debe quedarse.
            if (!municipio.value) { delegacion.value = ''; blockCalendar(); }
            setTimeout(function () { pintarOficina(); pintarCalendario(); }, 0);
        });
        if (tipoCaso) tipoCaso.addEventListener('change', pintarCaso);
        correo.addEventListener('input', validarCorreos);
        correo2.addEventListener('input', validarCorreos);
        conflicto.addEventListener('input', contar);
        form.addEventListener('input', actualizarPasos);
        form.addEventListener('change', actualizarPasos);

        pintarTramite(); pintarOficina(); pintarCaso(); contar(); pintarCalendario(); actualizarPasos();
    })();

    (function () {
        'use strict';
        var tipo   = document.getElementById('tipo');
        var puesto = document.getElementById('puesto');
        var folio  = document.getElementById('folio_patronal');
        var cajaCalidad = document.getElementById('calidad');
        var cajaFolio   = document.getElementById('si_ratificacion');
        var ayudaFolio  = document.getElementById('ayuda_folio_patronal');
        if (!tipo || !puesto || !folio) return;

        // Solicitud y ratificación preguntan quién agenda (App\Support\CitaPatronal):
        //  - Trabajador: sus datos, como siempre. En la ratificación, su nombre
        //    va desglosado (lo usa el formulario de ratificación en Mi módulo).
        //  - Empleador: el paso 2 son los datos de la persona trabajadora; el
        //    nombre, correo y teléfono son el contacto de quien agenda.
        //    Con folio, además se escribe y valida el folio.
        var TRAMITES = ['Solicitud', 'Ratificación'];
        var ayudas = {
            'Ratificación': {{ Js::from($ventanilla ? 'Si ya tiene folio, se captura en el paso 2. Si no, se capturan los datos de la persona trabajadora y el folio se registra en su cita.' : 'Si ya tienes folio, lo escribirás en el paso 2. Si no, escribe los datos de la persona trabajadora; el folio se registra el día de tu cita.') }},
            'Solicitud':    {{ Js::from($ventanilla ? 'Si ya tiene folio, se captura en el paso 2. Si no, se capturan los datos de la persona trabajadora y el folio se registra en su cita.' : 'Si ya tienes folio, lo escribirás en el paso 2. Si no, escribe los datos de la persona trabajadora; el folio se registra el día de tu cita.') }}
        };
        var ETIQUETA_NOMBRE = {
            normal:    'Nombre completo',
            empleador: {{ Js::from($ventanilla ? 'Nombre de quien agenda (contacto)' : 'Tu nombre completo (contacto)') }}
        };

        var bloqueFolio    = document.getElementById('gcPatronalFolio');
        var bloqueCitado   = document.getElementById('gcPatronalCitado');
        var cajaNombre     = document.getElementById('gcNombreCaja');
        var etiquetaNombre = document.getElementById('gcNombreEtiqueta');
        var poderFolio     = document.getElementById('gcPoderFolio');
        var poderInfo      = document.getElementById('gcPoderInfo');
        var poderError     = document.getElementById('gcPoderFolioError');
        var nombre         = document.getElementById('gcNombre');
        var URL_FOLIO      = @json(url('/validar_folio_abogado'));
        var nombrePuesto   = '';   // el nombre que se puso solo desde el folio
        var espera = null, consulta = 0;
        var folioActivo = false;

        function habilitar(caja, si) {
            if (!caja) return;
            caja.querySelectorAll('input, select, textarea').forEach(function (el) { el.disabled = !si; });
        }
        function mostrar(caja, si) {
            if (!caja) return;
            caja.hidden = !si;
            habilitar(caja, si);
        }

        function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

        function infoFolio(clase, html) {
            poderInfo.hidden = !html;
            poderInfo.className = 'gc-poder' + (clase ? ' gc-poder--' + clase : '');
            poderInfo.innerHTML = html || '';
        }

        // Mismo servicio que "Datos del Solicitante Patronal": sólo un folio elegible deja continuar.
        function validarFolio() {
            clearTimeout(espera);
            var valor = poderFolio.value.trim();
            if (!valor) {
                poderFolio.setCustomValidity('Escribe tu folio de representante patronal.');
                poderError.textContent = 'Escribe el folio.';
                infoFolio('', '');
                return;
            }
            poderFolio.setCustomValidity('Validando el folio…');
            poderError.textContent = 'Validando el folio…';
            infoFolio('', '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Buscando el folio…');
            var esta = ++consulta;
            espera = setTimeout(function () {
                fetch(URL_FOLIO + '/' + encodeURIComponent(valor), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
                    .then(function (d) {
                        if (esta !== consulta) return;
                        if (!d || d.success === false) { return Promise.reject(404); }
                        var rep = [d.nombre_representante, d.primer_apellido_representante, d.segundo_apellido_representante].filter(Boolean).join(' ').trim();
                        var ok = d.status === 'elegible';
                        var msg = ok ? '' : (d.message || 'El folio no es elegible.');
                        poderFolio.setCustomValidity(msg);
                        poderError.textContent = msg || 'Escribe un folio válido.';
                        infoFolio(ok ? 'ok' : 'error',
                            '<b>' + esc(d.nombre || '') + '</b>' +
                            (rep ? '<br>Representante: ' + esc(rep) : '') +
                            '<br>' + (ok ? '<i class="bi bi-check-circle-fill"></i> Folio elegible' : '<i class="bi bi-exclamation-triangle-fill"></i> ' + esc(msg)));
                        // Contacto: el representante (o la parte patronal), si no lo habían escrito.
                        var sugerido = (rep || d.nombre || '').toUpperCase();
                        if (ok && nombre && sugerido && (!nombre.value || nombre.value === nombrePuesto)) {
                            nombre.value = nombrePuesto = sugerido;
                            nombre.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (window.gcActualizarPasos) window.gcActualizarPasos();
                    })
                    .catch(function (e) {
                        if (esta !== consulta) return;
                        var msg = e === 404 ? 'El folio no existe. Revisa el número.' : 'No se pudo validar el folio. Inténtalo de nuevo.';
                        poderFolio.setCustomValidity(msg);
                        poderError.textContent = msg;
                        infoFolio('error', '<i class="bi bi-exclamation-triangle-fill"></i> ' + esc(msg));
                        if (window.gcActualizarPasos) window.gcActualizarPasos();
                    });
            }, 450);
        }

        function pintar() {
            var conTramite = TRAMITES.indexOf(tipo.value) !== -1;
            if (!conTramite) puesto.value = '';
            var esEmpleador = conTramite && puesto.value === 'empleador';
            if (!esEmpleador) folio.value = '';
            var conFolio = esEmpleador && folio.value === 'si';
            // Nombre desglosado de la persona trabajadora.
            var desglosado = esEmpleador || (tipo.value === 'Ratificación' && puesto.value === 'trabajador');
            // En la ratificación del propio trabajador su nombre ya va desglosado.
            var conNombre = !(tipo.value === 'Ratificación' && puesto.value === 'trabajador');

            cajaCalidad.style.display = conTramite ? '' : 'none';
            puesto.disabled = !conTramite;
            cajaFolio.style.display = esEmpleador ? '' : 'none';
            folio.disabled = !esEmpleador;
            if (ayudaFolio && ayudas[tipo.value]) ayudaFolio.textContent = ayudas[tipo.value];

            mostrar(bloqueFolio, conFolio);
            mostrar(bloqueCitado, desglosado);
            if (cajaNombre) {
                cajaNombre.style.display = conNombre ? '' : 'none';
                nombre.disabled = !conNombre;
            }
            if (etiquetaNombre) etiquetaNombre.textContent = esEmpleador ? ETIQUETA_NOMBRE.empleador : ETIQUETA_NOMBRE.normal;

            // Empleador (con o sin folio): los datos y el caso que se capturan son del trabajador.
            document.querySelectorAll('[data-etq-empleador]').forEach(function (el) {
                el.textContent = el.getAttribute(esEmpleador ? 'data-etq-empleador' : 'data-etq-normal');
            });

            if (conFolio && !folioActivo) validarFolio();
            folioActivo = conFolio;
            if (window.gcActualizarPasos) window.gcActualizarPasos();
        }

        tipo.addEventListener('change', pintar);
        puesto.addEventListener('change', pintar);
        folio.addEventListener('change', pintar);
        if (poderFolio) poderFolio.addEventListener('input', validarFolio);

        // Si el servidor regresó el formulario (p. ej. horario ocupado), vuelve a como estaba.
        var anterior = { calidad: @json(old('calidad')), tieneFolio: @json(old('tiene_folio')) };
        if (TRAMITES.indexOf(tipo.value) !== -1 && anterior.calidad) {
            puesto.value = anterior.calidad;
            if (anterior.calidad === 'empleador' && anterior.tieneFolio) folio.value = anterior.tieneFolio;
        }

        // Por si el trámite ya viene elegido (?tipo=... o un solo trámite).
        pintar();
    })();
    </script>
    @if ($ventanilla && session()->hasOldInput())
    {{-- Ventanilla: si al guardar salió un error (RecepcionController@turnos_guardar
         regresa con withInput), se vuelve a poner lo capturado para no perderlo.
         Se disparan los mismos eventos que al capturar a mano, para que se llenen
         la sede, el caso de excepción y el avance. El día y la hora no se
         restauran: hay que volver a elegirlos en el calendario. --}}
    @php
        $capturaAnterior = collect(session()->getOldInput())->only([
            'tipo', 'municipio_solicitante', 'nombre', 'edad', 'sexo', 'correo',
            'telefono', 'vulnerables', 'tipo_caso', 'motivo_excepcion', 'conflicto',
        ]);
    @endphp
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';
        var anterior = @json($capturaAnterior);

        function poner(id, valor, evento) {
            var campo = document.getElementById(id);
            if (!campo || valor === undefined || valor === null || valor === '') { return; }
            campo.value = Array.isArray(valor) ? valor[0] : valor;
            campo.dispatchEvent(new Event(evento || 'input', { bubbles: true }));
        }

        poner('tipo', anterior.tipo, 'change');
        poner('municipio_citado', anterior.municipio_solicitante, 'change');
        poner('gcNombre', anterior.nombre);
        poner('gcEdad', anterior.edad);
        poner('gcSexo', anterior.sexo, 'change');
        poner('gcCorreo', anterior.correo);
        poner('gcCorreo2', anterior.correo);
        poner('gcTelefono', anterior.telefono);
        poner('gcVulnerables', anterior.vulnerables, 'change');
        poner('gcConflicto', anterior.conflicto);

        // Tipo de caso: varias opciones comparten value; se elige la del motivo exacto.
        var tipoCaso = document.getElementById('gcTipoCaso');
        if (tipoCaso && anterior.tipo_caso) {
            var opcion = anterior.motivo_excepcion
                ? tipoCaso.querySelector('option[data-motivo="' + CSS.escape(anterior.motivo_excepcion) + '"]')
                : null;
            if (opcion) { opcion.selected = true; } else { tipoCaso.value = anterior.tipo_caso; }
            tipoCaso.dispatchEvent(new Event('change', { bubbles: true }));
        }

        var resumen = document.getElementById('resumenTurno');
        resumen.className = 'gc-aviso-cambio';
        resumen.textContent = 'Se conservaron los datos capturados. Vuelve a elegir día y hora.';
        resumen.style.display = 'block';
        if (window.gcActualizarPasos) { window.gcActualizarPasos(); }
    });
    </script>
    @endif
