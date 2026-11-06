{{--
    Fecha y hora de citas/{origen} (citas.blade.php). Espera $esInterno.
      - interno: la lista de horas del día (/api/horasRatificacion). Las ocupadas
        también se pueden elegir, con un aviso antes.
      - cualquier otro: la tira de días de /generar-cita (_cita_scripts) con los
        mismos datos (/api/obtenerTurnosDisponibles), sólo horarios con lugar.
--}}
    <script>
        const pad = n => String(n).padStart(2, '0');
        const nodo = (tag, clase, texto) => { const n = document.createElement(tag); if (clase) n.className = clase; if (texto != null) n.textContent = texto; return n; };
        const fechaLarga = f => new Date(f + 'T00:00:00').toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long' }).replace(',', '');

        function ponerHorario(fecha, hora) {
            document.getElementById('fechaSeleccionada').value = fecha;
            document.getElementById('horaSeleccionada').value = hora;
            document.getElementById('fechaResumen').textContent = fecha;
            document.getElementById('horaResumen').textContent = hora;
            document.getElementById('resumenCita').style.display = 'block';
            document.getElementById('fechaHoraWarning').style.display = 'none';
        }

        function limpiarHorario() {
            document.getElementById('fechaSeleccionada').value = '';
            document.getElementById('horaSeleccionada').value = '';
            document.getElementById('resumenCita').style.display = 'none';
        }

        function mensajeHoras(contenedor, icono, texto, accion) {
            contenedor.textContent = '';
            const m = nodo('div', 'hc-mensaje');
            m.appendChild(nodo('i', 'bi ' + icono));
            m.appendChild(nodo('div', null, texto));
            if (accion) {
                const b = nodo('button', 'btn btn-outline-secondary btn-sm', accion.texto);
                b.type = 'button';
                b.addEventListener('click', accion.fn);
                m.appendChild(b);
            }
            contenedor.appendChild(m);
        }
    </script>

    @if ($esInterno)
    <script>
        const cargarHorasInternas = (function () {
            const URL_HORAS = "{{ url('/api/horasRatificacion') }}";
            const NOTAS = { inhabil: 'Inhábil', expirado: 'Ya pasó' };
            const sede = document.getElementById('sede');
            const fecha = document.getElementById('fechaInterna');
            const caja = document.getElementById('horasInternas');
            const lista = document.getElementById('listaHoras');
            const aviso = document.getElementById('avisoOcupado');
            let horas = [];
            let porConfirmar = null;
            let peticion = 0;           // descarta respuestas viejas si se cambia rápido de fecha

            function elegir(hora) {
                ponerHorario(fecha.value, hora);
                pintar();
            }

            function pintar() {
                lista.textContent = '';
                if (!horas.length) {
                    mensajeHoras(lista, 'bi-calendar-x', 'No hay horarios ese día. Elige otra fecha.');
                    return;
                }
                const elegida = document.getElementById('horaSeleccionada').value;
                const rejilla = nodo('div', 'hc-grupo__horas');
                horas.forEach(function (h) {
                    const esElegida = h.hora === elegida;
                    const ocupada = h.estado === 'ocupado';
                    const b = nodo('button', 'hc-hora' + (esElegida ? ' is-elegida' : ocupada ? ' is-ocupada' : ''), h.hora);
                    b.type = 'button';
                    b.disabled = h.estado === 'inhabil' || h.estado === 'expirado';
                    b.setAttribute('aria-pressed', esElegida ? 'true' : 'false');
                    // Con cupo todavía se elige sin aviso, pero se ve cuántas hay.
                    const nota = NOTAS[h.estado] || (h.ocupadas ? (h.ocupadas === 1 ? '1 ocupada' : h.ocupadas + ' ocupadas') : null);
                    if (nota) b.appendChild(nodo('span', 'hc-hora__nota', nota));
                    b.addEventListener('click', function () {
                        if (!ocupada) { elegir(h.hora); return; }
                        porConfirmar = h.hora;
                        document.getElementById('avisoOcupadoTexto').textContent = (h.ocupadas === 1 ? 'Ya hay 1 ratificación que ocupa' : 'Ya hay ' + h.ocupadas + ' ratificaciones ocupadas')
                            + ' en ' + sede.value + ' el ' + fechaLarga(fecha.value) + ' a las ' + h.hora + '.';
                        bootstrap.Modal.getOrCreateInstance(aviso).show();
                    });
                    rejilla.appendChild(b);
                });
                lista.appendChild(rejilla);
            }

            async function cargar() {
                const mia = ++peticion;
                fecha.disabled = sede.value === '';
                caja.hidden = sede.value === '' || fecha.value === '';
                if (caja.hidden) return;

                mensajeHoras(lista, 'bi-hourglass-split', 'Buscando horarios…');
                try {
                    const q = new URLSearchParams({ sede: sede.value, fecha: fecha.value });
                    const r = await fetch(URL_HORAS + '?' + q.toString(), { headers: { Accept: 'application/json' } });
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    const datos = await r.json();
                    if (mia !== peticion) return;
                    horas = datos.horas;
                    pintar();
                } catch (e) {
                    if (mia !== peticion) return;
                    mensajeHoras(lista, 'bi-wifi-off', 'No pudimos cargar los horarios.', { texto: 'Intentar de nuevo', fn: cargar });
                }
            }

            fecha.addEventListener('change', function () { limpiarHorario(); cargar(); });
            document.getElementById('avisoOcupadoConfirmar').addEventListener('click', function () {
                elegir(porConfirmar);
                bootstrap.Modal.getOrCreateInstance(aviso).hide();
            });

            return cargar;
        })();
    </script>
    @else
    <script>
        (function () {
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
            const sede = document.getElementById('sede');
            let lunes = null;           // lunes de la semana que se ve
            let lunesMinimo = null;     // no se puede ir antes de esta semana
            let porDia = {};            // 'YYYY-MM-DD' -> ['09:20', ...] (sólo libres)
            let diaActivo = null;
            let elegido = null;         // { fecha, hora }
            let peticion = 0;           // descarta respuestas viejas si se navega rápido

            const ymd = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
            const lunesDe = d => { const x = new Date(d.getFullYear(), d.getMonth(), d.getDate()); x.setDate(x.getDate() - (x.getDay() + 6) % 7); return x; };
            const masDias = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
            const fmt = (d, o) => d.toLocaleDateString('es-MX', o);

            async function traerSemana(inicio) {
                const q = new URLSearchParams({ sede: sede.value, tipo: 'Ratificación', excepcion: 'No', externo: 1, start: ymd(inicio), end: ymd(masDias(inicio, 4)) });
                const r = await fetch(URL_HORARIOS + '?' + q.toString(), { headers: { Accept: 'application/json' } });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const dias = {};
                for (let i = 0; i < 5; i++) dias[ymd(masDias(inicio, i))] = [];
                (await r.json()).forEach(function (e) {
                    const estado = e.extendedProps && e.extendedProps.estado;
                    if (estado !== 'disponible' && estado !== 'turnos') return;
                    const [f, h] = String(e.start).split('T');
                    if (dias[f]) dias[f].push(h.slice(0, 5));
                });
                Object.keys(dias).forEach(k => dias[k].sort());
                return dias;
            }

            function pintarSemana() {
                const viernes = masDias(lunes, 4);
                el.semana.textContent = lunes.getMonth() === viernes.getMonth()
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
                    mensajeHoras(el.horas, 'bi-calendar-x', 'No hay horarios libres esta semana.', { texto: 'Ver la semana siguiente', fn: () => irA(masDias(lunes, 7)) });
                    return;
                }
                el.horas.textContent = '';
                const horas = porDia[diaActivo];
                [['Mañana', horas.filter(h => h < '13:00')], ['Tarde', horas.filter(h => h >= '13:00')]].forEach(function ([titulo, lista]) {
                    if (!lista.length) return;
                    const g = nodo('div', 'hc-grupo');
                    g.appendChild(nodo('div', 'hc-grupo__titulo', titulo));
                    const cont = nodo('div', 'hc-grupo__horas');
                    lista.forEach(function (h) {
                        const b = nodo('button', 'hc-hora', h);
                        b.type = 'button';
                        const esElegida = elegido && elegido.fecha === diaActivo && elegido.hora === h;
                        if (esElegida) b.classList.add('is-elegida');
                        b.setAttribute('aria-pressed', esElegida ? 'true' : 'false');
                        b.addEventListener('click', function () { elegir(diaActivo, h); });
                        cont.appendChild(b);
                    });
                    g.appendChild(cont);
                    el.horas.appendChild(g);
                });
            }

            function elegir(fecha, hora) {
                elegido = { fecha, hora };
                const texto = fechaLarga(fecha);
                el.elegido.textContent = '';
                el.elegido.appendChild(document.createTextNode('Elegiste: '));
                el.elegido.appendChild(nodo('strong', null, texto.charAt(0).toUpperCase() + texto.slice(1) + ' · ' + hora + ' h'));
                el.confirmar.removeAttribute('disabled');
                if (diaActivo) pintarHoras();
            }

            async function irA(inicio, buscarHaciaAdelante, diaPreferido) {
                const mia = ++peticion;
                lunes = inicio;
                diaActivo = null;
                el.dias.textContent = '';
                el.semana.textContent = 'Buscando horarios…';
                mensajeHoras(el.horas, 'bi-hourglass-split', 'Buscando horarios disponibles…');
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
                    mensajeHoras(el.horas, 'bi-wifi-off', 'No pudimos cargar los horarios. Revisa tu conexión.', { texto: 'Intentar de nuevo', fn: () => irA(lunes) });
                }
            }

            el.anterior.addEventListener('click', () => irA(masDias(lunes, -7)));
            el.siguiente.addEventListener('click', () => irA(masDias(lunes, 7)));

            document.getElementById('botonCalendar').addEventListener('click', function () {
                elegido = null;
                el.confirmar.setAttribute('disabled', 'disabled');
                el.elegido.textContent = 'Toca un horario para elegirlo.';
                el.contexto.textContent = 'Oficina ' + sede.value + ' · Ratificación';
                // Sábado o domingo: la primera semana útil es la siguiente.
                const hoy = new Date();
                lunesMinimo = lunesDe(hoy.getDay() === 0 || hoy.getDay() === 6 ? masDias(hoy, 2) : hoy);

                // Si ya había un horario confirmado, se abre en ese día y con esa hora marcada.
                const f = document.getElementById('fechaSeleccionada').value;
                const h = document.getElementById('horaSeleccionada').value;
                if (f && h) {
                    elegir(f, h);
                    const semana = lunesDe(new Date(f + 'T00:00:00'));
                    irA(semana < lunesMinimo ? lunesMinimo : semana, false, f);
                } else {
                    irA(lunesMinimo, true);
                }
            });

            el.confirmar.addEventListener('click', function () {
                ponerHorario(elegido.fecha, elegido.hora);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('calendarModal')).hide();
            });
        })();
    </script>
    @endif
