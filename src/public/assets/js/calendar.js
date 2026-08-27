// 1. Declaración global
var calendarPagos, calendarAudiencias, calendarRatificaciones, calendarCitas, calendarConciliador, calendarTodos;
var currentCalendar = null;
var calendarEl = document.getElementById('calendar');

// Meses en duro: el título no depende del locale del navegador ni de que el
// archivo de idioma de FullCalendar alcance a cargar.
var CAL_MESES  = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
var CAL_CORTOS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun',
                  'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

// Función para obtener los parámetros de filtro actuales
function getFilterParams() {
    const sede = document.getElementById('filtro-sede').value;
    const conciliador = document.getElementById('filter-conciliador').value;
    return `?sede=${encodeURIComponent(sede)}&conciliador=${encodeURIComponent(conciliador)}`;
}

// La pestaña "Todos" no consulta un endpoint nuevo: monta las cinco agendas como
// fuentes simultáneas de un mismo calendario, cada una con su color.
function calFuentesTodos() {
    return [
        { id: 'pagos',          url: urlPagos          + getFilterParams(), color: '#6A0F49' },
        { id: 'audiencias',     url: urlAudiencias     + getFilterParams(), color: '#496163' },
        { id: 'conciliador',    url: urlConciliadores  + getFilterParams(), color: '#2F6B6B' },
        { id: 'citas',          url: urlCitas          + getFilterParams(), color: '#7A5C8E' },
        { id: 'ratificaciones', url: urlRatificaciones + getFilterParams(), color: '#B5824A' }
    ];
}

function calEsTodos(cal) {
    return cal && cal === calendarTodos;
}

function refreshCurrentCalendar() {
    if (!currentCalendar) {
        return;
    }

    if (calEsTodos(currentCalendar)) {
        currentCalendar.setOption('eventSources', calFuentesTodos());
        return;
    }

    const currentSource = currentCalendar.getOption('events');
    const baseUrl = typeof currentSource === 'string' ? currentSource.split('?')[0] : currentSource;

    currentCalendar.setOption('events', baseUrl + getFilterParams());
}

// ---------------------------------------------------------------------------
// Esqueleto de carga
// ---------------------------------------------------------------------------
function calEsqueleto(visible, enFlujo) {
    const sk = document.getElementById('calSkeleton');
    const cal = document.getElementById('calendar');
    const zona = document.getElementById('calZona');

    if (!sk || !cal) {
        return;
    }

    if (zona) {
        zona.setAttribute('aria-busy', visible ? 'true' : 'false');
    }

    if (visible) {
        // enFlujo = la rejilla no existe (primera carga o cambio de agenda): el
        // esqueleto ocupa su lugar. Si no, va como velo encima de lo que ya hay.
        if (enFlujo) {
            sk.classList.remove('is-overlay');
            cal.classList.add('is-oculto');
        }
        sk.style.display = '';
        return;
    }

    cal.classList.remove('is-oculto');
    sk.style.display = 'none';
    sk.classList.add('is-overlay');

    // FullCalendar midió con el contenedor en display:none y las columnas salen
    // sin ancho: se remide justo al descubrirlo, no antes.
    setTimeout(function () {
        if (currentCalendar) currentCalendar.updateSize();
    }, 0);
}

// ---------------------------------------------------------------------------
// Barra superior propia (el headerToolbar de FullCalendar va apagado)
// ---------------------------------------------------------------------------
function calPintarEncabezado(view) {
    const titulo = document.getElementById('calTitulo');
    const rango = document.getElementById('calRango');

    if (!titulo || !rango) {
        return;
    }

    const inicio = view.currentStart;
    const fin = new Date(view.currentEnd.getTime() - 86400000);

    titulo.textContent = inicio.getMonth() === fin.getMonth() && inicio.getFullYear() === fin.getFullYear()
        ? CAL_MESES[inicio.getMonth()] + ' ' + inicio.getFullYear()
        : CAL_CORTOS[inicio.getMonth()] + ' – ' + CAL_CORTOS[fin.getMonth()] + ' ' + fin.getFullYear();

    rango.textContent = inicio.getDate() + ' ' + CAL_CORTOS[inicio.getMonth()] + ' ' + inicio.getFullYear() +
        ' – ' + fin.getDate() + ' ' + CAL_CORTOS[fin.getMonth()] + ' ' + fin.getFullYear();

    const selectorVista = document.getElementById('calVista');

    if (selectorVista && selectorVista.value !== view.type) {
        selectorVista.value = view.type;
    }
}

// ---------------------------------------------------------------------------
// Contenido del evento. Se arma con nodos y textContent en lugar de concatenar
// HTML: los nombres de las partes vienen de captura libre y con una plantilla
// de texto cualquier "<" del nombre se interpreta como marcado.
// ---------------------------------------------------------------------------
function calContenidoEvento(info) {
    const props = info.event.extendedProps || {};
    // En "Todos" el color no viene en el evento sino de la fuente que lo trajo.
    const color = props.color || info.event.backgroundColor || '#6A0F49';

    const tarjeta = document.createElement('div');
    tarjeta.className = 'evt-agenda';
    tarjeta.style.borderLeftColor = color;

    const hora = document.createElement('div');
    hora.className = 'evt-hora';
    hora.style.color = color;

    const icono = document.createElement('i');
    icono.className = 'bi bi-clock-fill';
    hora.appendChild(icono);

    const textoHora = document.createElement('span');
    textoHora.textContent = props.hora || 'Sin hora';
    hora.appendChild(textoHora);

    tarjeta.appendChild(hora);

    [
        ['Solicitante', props.solicitante],
        ['Citado', props.citado],
        ['Conciliador', props.conciliador]
    ].forEach(function (par) {
        const linea = document.createElement('div');
        linea.className = 'evt-linea';
        linea.title = par[0] + ': ' + (par[1] || 'N/A');

        const etiqueta = document.createElement('span');
        etiqueta.className = 'evt-etiqueta';
        etiqueta.textContent = par[0] + ': ';
        linea.appendChild(etiqueta);

        linea.appendChild(document.createTextNode(par[1] || 'N/A'));
        tarjeta.appendChild(linea);
    });

    return { domNodes: [tarjeta] };
}

document.addEventListener('DOMContentLoaded', function () {

    if (!calendarEl) {
        return;
    }

    // Listeners para los select de filtros
    document.getElementById('filtro-sede').addEventListener('change', refreshCurrentCalendar);
    document.getElementById('filter-conciliador').addEventListener('change', refreshCurrentCalendar);

    // 2. INICIALIZACIÓN DE CALENDARIOS
    function crearConfiguracion(endpoint, tipoParaModal) {
        return {
            initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek',
            locale: 'es',
            firstDay: 1,
            height: 'auto',
            dayMaxEvents: 3,
            dayHeaderFormat: { weekday: 'short' },
            // La barra la pinta el HTML de la vista, no FullCalendar.
            headerToolbar: false,
            events: endpoint + getFilterParams(),
            eventClassNames: ['evt-hueco'],
            eventClick: (info) => handleEventClick(info, tipoParaModal),
            eventContent: calContenidoEvento,
            moreLinkContent: function (arg) { return arg.num + ' más...'; },
            datesSet: function (info) { calPintarEncabezado(info.view); },
            loading: function (cargando) {
                calEsqueleto(cargando, document.getElementById('calendar').classList.contains('is-oculto'));
            }
        };
    }

    // Instanciamos cada calendario
    calendarCitas          = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlCitas, 'citas'));
    calendarPagos          = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlPagos, 'pagos'));
    calendarConciliador    = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlConciliadores, 'conciliador'));
    calendarAudiencias     = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlAudiencias, 'audiencias'));
    calendarRatificaciones = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlRatificaciones, 'ratificaciones'));

    const configTodos = crearConfiguracion('', 'todos');
    delete configTodos.events;
    configTodos.eventSources = calFuentesTodos();
    configTodos.initialView = 'dayGridMonth';
    // El tipo para el modal sale de la fuente que trajo cada evento.
    configTodos.eventClick = (info) => handleEventClick(info, info.event.source ? info.event.source.id : 'pagos');
    calendarTodos = new FullCalendar.Calendar(calendarEl, configTodos);

    // 3. LÓGICA DE LAS PESTAÑAS
    const botones = document.querySelectorAll('.btn-calendar');
    const mapeo = {
        'btn-todos': calendarTodos,
        'btn-pagos': calendarPagos,
        'btn-audiencias': calendarAudiencias,
        'btn-conciliador': calendarConciliador,
        'btn-citas': calendarCitas,
        'btn-ratificaciones': calendarRatificaciones
    };

    botones.forEach(boton => {
        boton.addEventListener('click', function (e) {
            e.preventDefault();
            const tipo = this.getAttribute('data-tipo');
            const calSeleccionado = mapeo[tipo];

            if (calSeleccionado) {
                switchCalendar(calSeleccionado, tipo === 'btn-todos' ? 'dayGridMonth' : null);
                botones.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // 4. NAVEGACIÓN DE LA BARRA PROPIA
    const btnPrev = document.getElementById('calPrev');
    const btnHoy = document.getElementById('calHoy');
    const btnNext = document.getElementById('calNext');
    const selVista = document.getElementById('calVista');

    if (btnPrev) btnPrev.addEventListener('click', function () { if (currentCalendar) currentCalendar.prev(); });
    if (btnNext) btnNext.addEventListener('click', function () { if (currentCalendar) currentCalendar.next(); });
    if (btnHoy)  btnHoy.addEventListener('click',  function () { if (currentCalendar) currentCalendar.today(); });

    if (selVista) {
        selVista.value = window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek';
        selVista.addEventListener('change', function () {
            if (currentCalendar) currentCalendar.changeView(this.value);
        });
    }

    // Un conciliador entra directo a su vista combinada del mes.
    if (typeof calArranqueTodos !== 'undefined' && calArranqueTodos && document.querySelector('[data-tipo="btn-todos"]')) {
        switchCalendar(calendarTodos, 'dayGridMonth');
    } else {
        switchCalendar(calendarPagos);
    }
});

function switchCalendar(newCalendar, vistaForzada) {
    let fecha = null;
    let vista = vistaForzada || null;

    if (currentCalendar) {
        // Cambiar de agenda ya no te regresa a la semana de hoy.
        fecha = currentCalendar.getDate();
        vista = vistaForzada || currentCalendar.view.type;
        currentCalendar.destroy();
    }

    const leyenda = document.getElementById('calLeyenda');

    if (leyenda) {
        leyenda.style.display = calEsTodos(newCalendar) ? '' : 'none';
    }

    // Al destruir, el contenedor queda vacío: el esqueleto vuelve a ocupar su
    // lugar en el flujo para que la tarjeta no se colapse mientras carga.
    calEsqueleto(true, true);

    currentCalendar = newCalendar;

    if (calEsTodos(currentCalendar)) {
        currentCalendar.setOption('eventSources', calFuentesTodos());
    } else {
        const baseUrl = currentCalendar.getOption('events').split('?')[0];
        currentCalendar.setOption('events', baseUrl + getFilterParams());
    }

    currentCalendar.render();

    if (vista && currentCalendar.view.type !== vista) {
        currentCalendar.changeView(vista);
    }

    if (fecha) {
        currentCalendar.gotoDate(fecha);
    }
}

function handleEventClick(info, calendarType) {
    const props = info.event.extendedProps;
    let modalContent = '';

    if (calendarType === 'pagos') {
        modalContent = `
            <strong>NUE:</strong> ${props.nue}<br>
            <strong>Descripción:</strong> ${props.descripcion}<br>
            <strong>Fecha:</strong> ${props.fecha}<br>
            <strong>Hora:</strong> ${props.hora}<br>
            <strong>Conciliador:</strong> ${props.conciliador}<br>
            <strong>Trabajador:</strong> ${props.trabajador}<br>
            <strong>Patronal:</strong> ${props.empresa}<br>
            <strong>Estatus:</strong> ${props.estatus}<br>
            <strong>Monto:</strong> ${props.monto}<br>
            <strong>Observaciones:</strong> ${props.observaciones}<br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="cumplimiento/consulta/${info.event.id}/${props.tipo}" class="btn btn-info">Ver Cumplimiento</a>
            </div>
        `;
    }
    else if (calendarType === 'conciliador') {
        modalContent = `
            <strong>NUE:</strong> ${props.nue}<br>
            <strong>Descripción:</strong> ${props.descripcion}<br>
            <strong>Fecha:</strong> ${props.fecha}<br>
            <strong>Hora:</strong> ${props.hora}<br>
            <strong>Conciliador:</strong> ${props.conciliador}<br>
            <strong>Trabajador:</strong> ${props.trabajador}<br>
            <strong>Patronal:</strong> ${props.empresa}<br>
            <strong>Estatus:</strong> ${props.estatus}<br>
            <strong>Monto:</strong> ${props.monto}<br>
            <strong>Observaciones:</strong> ${props.observaciones}<br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="cumplimiento/consulta/${info.event.id}/${props.tipo}" class="btn btn-info">Ver detalle</a>
            </div>
        `;
    } else if (calendarType === 'audiencias') {
        const audienciaId = props.audiencia_id ?? info.event.id;
        const idSolicitud = props.id_solicitud;
        modalContent = `
            <strong>NUE:</strong> ${info.event.title}<br>
            <strong>Conciliador:</strong> ${props.conciliador}<br>
            <strong>Fecha:</strong> ${props.fecha}<br>
            <strong>Hora:</strong> ${props.hora}<br>
            <strong>Estatus:</strong> ${props.estatus}<br>
            <strong>Delegación:</strong> ${props.delegacion}<br>
            <strong>Sala:</strong> ${props.sala}<br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                ${ props.estatus === 'Pendiente' ? 
                `<a href="solicitud/iniciar/${idSolicitud}?audiencia_id=${audienciaId}" class="btn btn-info">Ir a Audiencia</a>` 
                : '' 
                }
            </div>
        `;
    }
    else if (calendarType === 'ratificaciones') {
        modalContent = `
            <strong>Citado:</strong> ${info.event.title}<br>
            <strong>Solicitante:</strong> ${props.solicitante}<br>
            <strong>Fecha:</strong> ${props.fecha}<br>
            <strong>Hora:</strong> ${props.hora}<br>
            <strong>Estatus:</strong> ${props.estatus}<br>
            <strong>Delegación:</strong> ${props.delegacion}<br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="cumplimiento/consulta/${info.event.id}/${props.tipo}" class="btn btn-info">Ver detalle</a>
            </div>
        `;
    }
    else if (calendarType === 'citas') {
        modalContent = `
            <strong>NUE:</strong> ${props.nue}<br>
            <strong>Descripción:</strong> ${props.descripcion}<br>
            <strong>Fecha:</strong> ${props.fecha}<br>
            <strong>Hora:</strong> ${props.hora}<br>
            <strong>Conciliador:</strong> ${props.conciliador}<br>
            <strong>Estatus:</strong> ${props.estatus}<br>
            <strong>Monto:</strong> ${props.monto}<br>
            <strong>Observaciones:</strong> ${props.observaciones}<br>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="cumplimiento/consulta/${info.event.id}/${props.tipo}" class="btn btn-info">Ver detalle</a>
            </div>
        `;
    }

    // Bootstrap 5 quitó la API de plugins por jQuery: $('#evento').modal('show')
    // dejó de existir al pasar esta pantalla de Bootstrap 4 a 5.3.
    document.querySelector('#evento .modal-body').innerHTML = modalContent;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('evento')).show();
}

// Función para estilizar los eventos
function styleEvent(info) {
    const titleElements = info.el.querySelectorAll('.fc-event-title, .fc-list-item-title, .fc-list-item-title a');
    if (titleElements && titleElements.length) {
        titleElements.forEach(function (titleElement) {
            titleElement.style.whiteSpace = 'normal';
            titleElement.style.textAlign = 'left';
            titleElement.style.fontSize = '11px';
            titleElement.style.lineHeight = '1.1';
            titleElement.style.fontWeight = '600';
        });
    }

    const timeElement = info.el.querySelector('.fc-event-time, .fc-list-item-time');
    if (timeElement) {
        timeElement.style.fontSize = '11px';
        timeElement.style.opacity = '0.95';
        timeElement.style.fontWeight = '800';
    }
    if (info.el && info.el.style) {
        info.el.style.padding = '4px 6px';
        info.el.style.boxSizing = 'border-box';
    }
}