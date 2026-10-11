{{--
    Lógica del formulario de cita: selector de día y hora, validación por pasos
    y ayudas. Espera $modo ('publico' | 'ventanilla'). En ventanilla no se manda
    externo=1, así que no aplica el límite diario del formulario público.
--}}
@php
    $ventanilla = ($modo ?? 'publico') === 'ventanilla';
@endphp
    <script>
        

        function cambiaExcepcion(elemento) {
            var valor = elemento.value;
            var inputExcepcion = document.getElementById('excepcion');
    
            if (inputExcepcion) {
                inputExcepcion.value = (valor === 'Ninguno') ? 'No' : 'Si';
            }
        }

        function blockCalendar() {
            const delegacion = document.getElementById('delegacion').value;
            const tipo = document.querySelector('select[name="tipo"]').value;
            const boton = document.getElementById("botonCalendar");

            if (delegacion !== "" && tipo !== "") {
                boton.removeAttribute("disabled");
            } else {
                boton.disabled = true;
            }

            limpiarTurnoSeleccionado();
        }

        function limpiarTurnoSeleccionado() {
            const habia = document.getElementById("fecha_turno").value;
            turnoSeleccionado = null;
            document.getElementById("fecha_turno").value = "";
            document.getElementById("hora_turno").value = "";
            document.getElementById("confirmarTurno").setAttribute("disabled", "disabled");

            // Si ya había un horario, se avisa que hay que volver a elegirlo: cambió
            // un dato (trámite, municipio o caso) que define qué horarios hay.
            const resumen = document.getElementById("resumenTurno");
            if (habia) {
                resumen.className = 'gc-aviso-cambio';
                resumen.textContent = 'Cambiaste un dato que define los horarios. Vuelve a elegir día y hora.';
                resumen.style.display = "block";
            } else if (resumen.className !== 'gc-aviso-cambio') {
                resumen.style.display = "none";
            }
            const texto = document.getElementById('gcTextoCalendario');
            if (texto) texto.textContent = 'Ver horarios disponibles';
            if (window.gcActualizarPasos) window.gcActualizarPasos();
        }

        function pad(numero) {
            return String(numero).padStart(2, "0");
        }

        function formatoFechaLocal(fecha) {
            return fecha.getFullYear() + "-" + pad(fecha.getMonth() + 1) + "-" + pad(fecha.getDate());
        }

        function formatoHoraLocal(fecha) {
            return pad(fecha.getHours()) + ":" + pad(fecha.getMinutes()) + ":" + pad(fecha.getSeconds());
        }

        // ---------- Selector de día y hora ----------
        // turnoSeleccionado conserva la forma { start: Date } que usan
        // "Confirmar" y limpiarTurnoSeleccionado().
        let turnoSeleccionado = null;

        const hc = (function () {
            const URL_HORARIOS = "{{ url('/api/obtenerTurnosDisponibles') }}";
            const SEMANAS_A_BUSCAR = 8;        // al abrir, busca la primera semana con lugar
            const el = {
                semana: document.getElementById('hcSemana'),
                anterior: document.getElementById('hcAnterior'),
                siguiente: document.getElementById('hcSiguiente'),
                dias: document.getElementById('hcDias'),
                horas: document.getElementById('hcHoras'),
                elegido: document.getElementById('hcElegido'),
                contexto: document.getElementById('hcContexto'),
                confirmar: document.getElementById('confirmarTurno')
            };
            let lunes = null;           // lunes de la semana que se ve
            let lunesMinimo = null;     // no se puede ir antes de esta semana
            let porDia = {};            // 'YYYY-MM-DD' -> ['09:20', ...] (sólo libres)
            let diaActivo = null;
            let peticion = 0;           // descarta respuestas viejas si se navega rápido

            const ymd = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
            const lunesDe = d => { const x = new Date(d.getFullYear(), d.getMonth(), d.getDate()); const w = (x.getDay() + 6) % 7; x.setDate(x.getDate() - w); return x; };
            const masDias = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
            const fmt = (d, o) => d.toLocaleDateString('es-MX', o);
            const nodo = (tag, clase, texto) => { const n = document.createElement(tag); if (clase) n.className = clase; if (texto != null) n.textContent = texto; return n; };

            function parametros() {
                return {
                    sede: document.getElementById('delegacion').value,
                    tipo: document.querySelector('select[name="tipo"]').value,
                    excepcion: document.getElementById('excepcion').value
                };
            }

            async function traerSemana(inicio) {
                const p = parametros();
                const q = new URLSearchParams({ sede: p.sede, tipo: p.tipo, excepcion: p.excepcion, {!! $ventanilla ? '' : 'externo: 1, ' !!}start: ymd(inicio), end: ymd(masDias(inicio, 4)) });
                const r = await fetch(URL_HORARIOS + '?' + q.toString(), { headers: { Accept: 'application/json' } });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const eventos = await r.json();
                const dias = {};
                for (let i = 0; i < 5; i++) dias[ymd(masDias(inicio, i))] = [];
                eventos.forEach(function (e) {
                    const estado = e.extendedProps && e.extendedProps.estado;
                    if (estado !== 'disponible' && estado !== 'turnos') return;
                    const [f, h] = String(e.start).split('T');
                    if (dias[f]) dias[f].push(h.slice(0, 5));
                });
                Object.keys(dias).forEach(k => dias[k].sort());
                return dias;
            }

            function mensaje(icono, texto, accion) {
                el.horas.textContent = '';
                const m = nodo('div', 'hc-mensaje');
                m.appendChild(nodo('i', 'bi ' + icono));
                m.appendChild(nodo('div', null, texto));
                if (accion) {
                    const b = nodo('button', 'btn btn-outline-secondary btn-sm', accion.texto);
                    b.type = 'button';
                    b.addEventListener('click', accion.fn);
                    m.appendChild(b);
                }
                el.horas.appendChild(m);
            }

            function pintarSemana() {
                const viernes = masDias(lunes, 4);
                const mismoMes = lunes.getMonth() === viernes.getMonth();
                el.semana.textContent = mismoMes
                    ? lunes.getDate() + ' al ' + viernes.getDate() + ' de ' + fmt(viernes, { month: 'long', year: 'numeric' })
                    : fmt(lunes, { day: 'numeric', month: 'short' }) + ' al ' + fmt(viernes, { day: 'numeric', month: 'short', year: 'numeric' });
                el.anterior.disabled = lunes <= lunesMinimo;

                el.dias.textContent = '';
                Object.keys(porDia).forEach(function (f) {
                    const d = new Date(f + 'T00:00:00');
                    const libres = porDia[f].length;
                    const b = nodo('button', 'hc-dia' + (f === diaActivo ? ' is-activo' : ''));
                    b.type = 'button';
                    b.setAttribute('role', 'tab');
                    b.setAttribute('aria-selected', f === diaActivo ? 'true' : 'false');
                    b.disabled = libres === 0;
                    b.appendChild(nodo('span', 'hc-dia__semana', fmt(d, { weekday: 'short' }).replace('.', '')));
                    b.appendChild(nodo('span', 'hc-dia__numero', String(d.getDate())));
                    b.appendChild(nodo('span', 'hc-dia__mes', fmt(d, { month: 'short' }).replace('.', '')));
                    b.appendChild(nodo('span', 'hc-dia__libres', libres ? (libres === 1 ? '1 horario' : libres + ' horarios') : 'Sin lugar'));
                    b.setAttribute('aria-label', fmt(d, { weekday: 'long', day: 'numeric', month: 'long' }) + ', ' + (libres ? libres + ' horarios libres' : 'sin horarios'));
                    b.addEventListener('click', function () { diaActivo = f; pintarSemana(); pintarHoras(); });
                    el.dias.appendChild(b);
                });
                const activo = el.dias.querySelector('.is-activo');
                if (activo && activo.scrollIntoView) activo.scrollIntoView({ block: 'nearest', inline: 'center' });
            }

            function pintarHoras() {
                if (!diaActivo) {
                    mensaje('bi-calendar-x', 'No hay horarios libres esta semana.', { texto: 'Ver la semana siguiente', fn: () => irA(masDias(lunes, 7)) });
                    return;
                }
                el.horas.textContent = '';
                const horas = porDia[diaActivo];
                const grupos = [['Mañana', horas.filter(h => h < '13:00')], ['Tarde', horas.filter(h => h >= '13:00')]];
                grupos.forEach(function ([titulo, lista]) {
                    if (!lista.length) return;
                    const g = nodo('div', 'hc-grupo');
                    g.appendChild(nodo('div', 'hc-grupo__titulo', titulo));
                    const cont = nodo('div', 'hc-grupo__horas');
                    lista.forEach(function (h) {
                        const b = nodo('button', 'hc-hora', h);
                        b.type = 'button';
                        const elegido = turnoSeleccionado && ymd(turnoSeleccionado.start) === diaActivo && formatoHoraLocal(turnoSeleccionado.start).slice(0, 5) === h;
                        if (elegido) b.classList.add('is-elegida');
                        b.setAttribute('aria-pressed', elegido ? 'true' : 'false');
                        b.addEventListener('click', function () { elegir(diaActivo, h); });
                        cont.appendChild(b);
                    });
                    g.appendChild(cont);
                    el.horas.appendChild(g);
                });
            }

            function elegir(fecha, hora) {
                turnoSeleccionado = { start: new Date(fecha + 'T' + hora + ':00') };
                const d = turnoSeleccionado.start;
                const texto = fmt(d, { weekday: 'long', day: 'numeric', month: 'long' }).replace(',', '');
                el.elegido.textContent = '';
                el.elegido.appendChild(document.createTextNode('Elegiste: '));
                el.elegido.appendChild(nodo('strong', null, texto.charAt(0).toUpperCase() + texto.slice(1) + ' · ' + hora + ' h'));
                el.confirmar.removeAttribute('disabled');
                pintarHoras();
            }

            async function irA(inicio, buscarHaciaAdelante, diaPreferido) {
                const mia = ++peticion;
                lunes = inicio;
                diaActivo = null;
                el.dias.textContent = '';
                el.semana.textContent = 'Buscando horarios…';
                mensaje('bi-hourglass-split', 'Buscando horarios disponibles…');
                try {
                    let intentos = buscarHaciaAdelante ? SEMANAS_A_BUSCAR : 1;
                    let dias = await traerSemana(lunes);
                    while (--intentos > 0 && !Object.values(dias).some(h => h.length)) {
                        lunes = masDias(lunes, 7);
                        dias = await traerSemana(lunes);
                        if (mia !== peticion) return;
                    }
                    if (mia !== peticion) return;
                    porDia = dias;
                    diaActivo = (diaPreferido && porDia[diaPreferido] && porDia[diaPreferido].length)
                        ? diaPreferido
                        : (Object.keys(porDia).find(f => porDia[f].length) || null);
                    pintarSemana();
                    pintarHoras();
                } catch (e) {
                    if (mia !== peticion) return;
                    el.semana.textContent = '';
                    mensaje('bi-wifi-off', 'No pudimos cargar los horarios. Revisa tu conexión.', { texto: 'Intentar de nuevo', fn: () => irA(lunes) });
                }
            }

            el.anterior.addEventListener('click', () => irA(masDias(lunes, -7)));
            el.siguiente.addEventListener('click', () => irA(masDias(lunes, 7)));

            return {
                abrir: function () {
                    turnoSeleccionado = null;
                    el.confirmar.setAttribute('disabled', 'disabled');
                    el.elegido.textContent = 'Toca un horario para elegirlo.';
                    const p = parametros();
                    const tipoTexto = document.querySelector('select[name="tipo"]').selectedOptions[0];
                    el.contexto.textContent = 'Oficina ' + p.sede + ' · ' + (tipoTexto ? tipoTexto.textContent.trim() : p.tipo);
                    // Sábado o domingo: la primera semana útil es la siguiente.
                    const hoy = new Date();
                    lunesMinimo = lunesDe(hoy.getDay() === 0 || hoy.getDay() === 6 ? masDias(hoy, 2) : hoy);

                    // Si ya había un horario confirmado, se abre en ese día y con esa hora marcada.
                    const f = document.getElementById('fecha_turno').value;
                    const h = (document.getElementById('hora_turno').value || '').slice(0, 5);
                    if (f && h) {
                        elegir(f, h);
                        const semana = lunesDe(new Date(f + 'T00:00:00'));
                        irA(semana < lunesMinimo ? lunesMinimo : semana, false, f);
                    } else {
                        irA(lunesMinimo, true);
                    }
                }
            };
        })();

        document.getElementById("botonCalendar").addEventListener("click", function () {
            hc.abrir();
        });

        document.getElementById("confirmarTurno").addEventListener("click", function () {
            if (!turnoSeleccionado) {
                alert("Por favor selecciona un horario disponible.");
                return;
            }

            const inicio = turnoSeleccionado.start;
            document.getElementById("fecha_turno").value = formatoFechaLocal(inicio);
            document.getElementById("hora_turno").value = formatoHoraLocal(inicio);

            const resumen = document.getElementById("resumenTurno");
            const dia = inicio.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).replace(',', '');
            resumen.textContent = '';
            const icono = document.createElement('i');
            icono.className = 'bi bi-calendar-check-fill';
            const texto = document.createElement('div');
            const fuerte = document.createElement('strong');
            fuerte.textContent = dia.charAt(0).toUpperCase() + dia.slice(1) + ' · ' + pad(inicio.getHours()) + ':' + pad(inicio.getMinutes()) + ' h';
            texto.appendChild(fuerte);
            texto.appendChild(document.createTextNode('{!! $ventanilla ? 'Se apartará al presionar "Agendar cita".' : 'Se apartará cuando presiones "Agendar mi cita".' !!} Si quieres otro, vuelve a abrir los horarios.'));
            resumen.appendChild(icono);
            resumen.appendChild(texto);
            resumen.style.display = "flex";
            document.getElementById('gcTextoCalendario').textContent = 'Cambiar día u hora';
            document.getElementById('gcErrorHorario').hidden = true;
            if (window.gcActualizarPasos) window.gcActualizarPasos();

            bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarModal')).hide();
        });

        document.getElementById("form_roles").addEventListener("submit", function (e) {
            const form = this;
            const btn = document.getElementById('btnGuardar');
            if (window.gcValidarCorreos) window.gcValidarCorreos();

            const sinHorario = !document.getElementById("fecha_turno").value || !document.getElementById("hora_turno").value;
            document.getElementById('gcErrorHorario').hidden = !sinHorario;

            if (!form.checkValidity() || sinHorario) {
                e.preventDefault();
                e.stopPropagation();
                form.classList.add('was-validated');
                // Lleva a la persona al primer dato que falta.
                const primero = form.querySelector(':invalid:not(fieldset)') || (sinHorario ? document.getElementById('botonCalendar') : null);
                if (primero) {
                    primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(function () { try { primero.focus({ preventScroll: true }); } catch (err) {} }, 350);
                }
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ $ventanilla ? 'Agendando la cita…' : 'Agendando tu cita…' }}';
        });

        //Dependiendo del Municipio seleccionado muestra la delegación y oficina de apoyo que le corresponde
        document.addEventListener('DOMContentLoaded', function () {
            const delegacionSelect = document.getElementById('delegacion');
            const municipioSelect = document.getElementById('municipio_citado');
            const delegacionModulo = document.getElementById('modulo_delegacion');

            const delegaciones = {
                1: ['Morelia'],
                2: ['Zitácuaro'],
                3: ['Uruapan'],
                4: ['Lázaro Cárdenas'],
                5: ['Zamora'],
                6: ['Sahuayo']
            };

            municipioSelect.addEventListener('change', function () {
                const selectedOption = municipioSelect.options[municipioSelect.selectedIndex];
                const delegacionId = selectedOption.getAttribute('data-delegacion-id');

                // Limpia el select de delegación
                delegacionSelect.innerHTML = '<option value="">Seleccione</option>';

                if (delegacionId && delegaciones[delegacionId]) {
                    delegaciones[delegacionId].forEach(delegacion => {
                        delegacionSelect.value = delegacion;
                        delegacionModulo.textContent = delegacion;
                    });
                }

                blockCalendar();
            });
        });

          
    </script>
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
        tipoCaso.addEventListener('change', pintarCaso);
        correo.addEventListener('input', validarCorreos);
        correo2.addEventListener('input', validarCorreos);
        conflicto.addEventListener('input', contar);
        form.addEventListener('input', actualizarPasos);
        form.addEventListener('change', actualizarPasos);

        pintarTramite(); pintarOficina(); pintarCaso(); contar(); pintarCalendario(); actualizarPasos();
    })();
    </script>
