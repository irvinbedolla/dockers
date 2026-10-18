// 1. Declaración global
// Un unico calendario. Antes habia seis instancias —una por pestana— que se
// destruian y recreaban al cambiar; con pastillas padre que pintan dos agendas
// a la vez eso ya no alcanzaba, asi que ahora lo que cambia son las fuentes.
var currentCalendar = null;
var calendarEl = document.getElementById('calendar');

// Meses en duro: el título no depende del locale del navegador ni de que el
// archivo de idioma de FullCalendar alcance a cargar.
var CAL_MESES  = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
var CAL_CORTOS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun',
                  'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
var CAL_DIAS   = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

// Tope de eventos por día en la vista semanal. Sin él, un día con doce
// audiencias estiraba la casilla y con ella el alto de toda la pantalla.
// A partir del sexto se agrupan en el "+N más..." que ya usaba la vista de
// mes, con el mismo popover; sólo cambia el número.
var CAL_MAX_EVENTOS_SEMANA = 5;

function getConciliadorSeleccionado() {
    // El rol Conciliador trae su id fijo en un input oculto; los demas roles
    // lo eligen con las pastillas que sustituyeron al antiguo desplegable.
    const fijo = document.getElementById('filter-conciliador');
    if (fijo) {
        return (fijo.value || '').trim();
    }

    const activa = document.querySelector('.cal-persona.active');
    return activa ? (activa.getAttribute('data-conciliador') || '').trim() : '';
}

function hayConciliadorSeleccionado() {
    return Boolean(getConciliadorSeleccionado());
}

function calMontarPersonas() {
    document.querySelectorAll('.cal-persona').forEach(function (pastilla) {
        pastilla.addEventListener('click', function () {
            document.querySelectorAll('.cal-persona').forEach(function (p) {
                p.classList.remove('active');
            });
            this.classList.add('active');
            refreshCurrentCalendar();
        });
    });

    calFiltrarPersonasPorSede();
}

// La sede decide que conciliadores se ven. Si el que estaba elegido pertenece
// a otra sede se regresa a "Todos": dejarlo activo pero oculto dejaba el
// calendario filtrado por alguien que ya no aparece en pantalla.
function calFiltrarPersonasPorSede() {
    const selector = document.getElementById('filtro-sede');
    if (!selector) {
        return;
    }

    const sede = selector.value;
    let seOculhoLaActiva = false;

    document.querySelectorAll('.cal-persona[data-delegacion]').forEach(function (pastilla) {
        const visible = sede === 'Todos' || sede === '' || pastilla.getAttribute('data-delegacion') === sede;
        pastilla.hidden = !visible;

        if (!visible && pastilla.classList.contains('active')) {
            pastilla.classList.remove('active');
            seOculhoLaActiva = true;
        }
    });

    if (seOculhoLaActiva) {
        const todos = document.querySelector('.cal-persona[data-conciliador=""]');
        if (todos) {
            todos.classList.add('active');
        }
    }
}

function getDayMaxEventsOption() {
    // Si hay un conciliador seleccionado (o usuario con rol conciliador),
    // se desactiva el límite para expandir la casilla y mostrar todos los eventos.
    // Si están "Todos los conciliadores", se limitan a 3 para no saturar la vista mensual.
    return hayConciliadorSeleccionado() ? false : 3;
}

// Vista con la que abre la agenda, sea cual sea el rol o la pestaña. Antes el
// conciliador entraba en mes: veía su carga del mes completo pero no el día a
// día, que es lo que ocupa para trabajar. Un solo lugar para cambiarla.
//
// En pantallas chicas se usa listWeek, que cubre la misma semana en lista
// porque siete columnas no caben en un teléfono. Si se quiere dayGridWeek
// también ahí, se quita el ternario y se deja 'dayGridWeek' a secas.
// Sabado y domingo arrancan minimizados: entre los dos se llevaban dos
// septimas partes del ancho y casi nunca traen audiencias. Se encogen, no se
// apagan: con weekends:false FullCalendar recorta el rango que pide al
// servidor a lunes-viernes, y con el se irian los eventos de fin de semana y
// el rango del boton Exportar.
var CAL_FINDE_LLAVE = 'agenda.finde';

function calFindeMinimizado() {
    // El localStorage puede reventar (modo privado, cookies bloqueadas); ante
    // la duda vale el valor por defecto, que es minimizado.
    try {
        return window.localStorage.getItem(CAL_FINDE_LLAVE) !== 'abierto';
    } catch (e) {
        return true;
    }
}

function calGuardarFinde(minimizado) {
    try {
        window.localStorage.setItem(CAL_FINDE_LLAVE, minimizado ? 'minimo' : 'abierto');
    } catch (e) { /* si no se puede recordar, se pierde al recargar y ya */ }
}

function calEsFinde(fecha) {
    const dia = fecha.getDay();
    return dia === 0 || dia === 6;
}

// El encabezado de una columna encogida no admite "Sabado 5": va la inicial.
function calEncabezadoDia(arg) {
    const dia = arg.date.getDay();

    if (calFindeMinimizado() && calEsFinde(arg.date)) {
        return CAL_DIAS[dia].charAt(0);
    }

    return CAL_DIAS[dia] + ' ' + arg.date.getDate();
}

// Marca las casillas encogidas que si traen eventos para que el CSS les pinte
// el contador. Sin esto, un dia de fin de semana con audiencias se veria
// vacio y nadie sabria que hay algo detras.
function calMarcarFinde() {
    document.querySelectorAll('.fc-daygrid-day.fc-day-sat, .fc-daygrid-day.fc-day-sun').forEach(function (celda) {
        const marco = celda.querySelector('.fc-daygrid-day-frame');
        if (!marco) return;

        const cuantos = celda.querySelectorAll('.fc-daygrid-event-harness').length;

        if (cuantos) {
            marco.setAttribute('data-eventos', cuantos);
        } else {
            marco.removeAttribute('data-eventos');
        }
    });
}

function calAplicarFinde() {
    if (!calendarEl) return;

    const minimo = calFindeMinimizado();
    calendarEl.classList.toggle('finde-min', minimo);

    const boton = document.getElementById('calFinde');
    if (boton) {
        boton.setAttribute('aria-pressed', minimo ? 'false' : 'true');
        boton.classList.toggle('activo', !minimo);
    }

    // Reescribe los encabezados de la vista semana: los de fin de semana
    // cambian de "Sabado 5" a "S" y de vuelta segun el estado.
    //
    // Va por setOption('views') y no por setOption('dayHeaderContent'): el
    // segundo es global y le gana al de cada vista, asi que la vista mes
    // acababa rotulada "Lunes 5, Martes 6" en lugar del nombre del dia.
    if (currentCalendar) {
        calAplicarVistas();
        calMarcarFinde();
    }
}

// Dias inhabiles de la sede: se pintan como dias deshabilitados, no como
// eventos. Se guardan aparte de las fuentes de FullCalendar porque no son
// citas sino una propiedad del dia, y porque el mismo mapa sirve para las
// cinco agendas sin volver a pedirlo.
var CAL_INHABILES = {};        // { 'Y-m-d': {sedes:[], tipos:[], todas:bool} }
var calInhabilesRango = null;  // para no repetir la peticion del mismo rango

// Que modulo mira cada pastilla. Un bloqueo con tipo 'Audiencias' cierra el
// dia para audiencias pero no para ratificaciones, asi que el dia solo se
// deshabilita si el tipo aplica a lo que se esta viendo.
var CAL_PASTILLA_MODULO = {
    'btn-audiencias':         'Audiencias',
    'btn-cumplimientos':      'Cumplimientos',
    'btn-cumpl-audiencias':   'Cumplimientos',
    'btn-cumpl-generales':    'Cumplimientos',
    'btn-ratificaciones':     'Ratificaciones',
    'btn-rati-cumplimientos': 'Ratificaciones'
};

function calInhabilDelDia(fecha) {
    const dato = CAL_INHABILES[calFechaISO(fecha)];
    if (!dato) {
        return null;
    }

    const modulo = CAL_PASTILLA_MODULO[pastillaActiva] || null;

    // 'Todos' cierra la sede completa. Un tipo concreto solo cuenta si es el
    // modulo que se esta viendo; en "Todos" y "Solicitudes" cuentan todos.
    const aplica = dato.tipos.some(function (t) {
        return t === 'Todos' || !modulo || t === modulo;
    });

    return aplica ? dato : null;
}

function calTraerInhabiles(vista) {
    if (typeof urlInhabiles === 'undefined') {
        return;
    }

    const desde = calFechaISO(vista.activeStart);
    const hasta = calFechaISO(new Date(vista.activeEnd.getTime() - 86400000));
    const sede = (document.getElementById('filtro-sede') || {}).value || 'Todos';
    const firma = desde + '|' + hasta + '|' + sede;

    if (firma === calInhabilesRango) {
        return;
    }

    calInhabilesRango = firma;

    fetch(urlInhabiles + '?start=' + desde + '&end=' + hasta + '&sede=' + encodeURIComponent(sede), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function (r) { return r.ok ? r.json() : { dias: {} }; })
        .then(function (data) {
            CAL_INHABILES = data.dias || {};
            calPintarInhabiles();
        })
        // Un fallo aqui no puede tumbar la agenda: se queda sin marcar y ya.
        .catch(function () { CAL_INHABILES = {}; });
}

// FullCalendar ya dibujo las casillas cuando llega la respuesta, asi que las
// clases se ponen sobre el DOM en vez de por dayCellClassNames, que solo se
// evalua durante el render.
function calPintarInhabiles() {
    if (!calendarEl) return;

    calendarEl.querySelectorAll('.fc-daygrid-day, .fc-list-day').forEach(function (celda) {
        const fecha = celda.getAttribute('data-date');
        const dato = fecha ? calInhabilDelDia(new Date(fecha + 'T12:00:00')) : null;

        celda.classList.toggle('dia-inhabil', Boolean(dato));
        celda.classList.toggle('dia-inhabil-parcial', Boolean(dato) && !dato.todas);

        const marco = celda.querySelector('.fc-daygrid-day-frame') || celda;

        if (dato) {
            marco.setAttribute('data-inhabil', dato.todas ? 'Inhabil' : 'Inhabil parcial');
            marco.setAttribute('title', dato.todas
                ? 'Dia inhabil en todas las sedes en pantalla'
                : 'Dia inhabil solo en: ' + dato.sedes.join(', '));
        } else {
            marco.removeAttribute('data-inhabil');
            marco.removeAttribute('title');
        }
    });
}

function calVistaInicial() {
    return window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek';
}

function actualizarClaseVista(tipoVista) {
    if (!calendarEl) {
        calendarEl = document.getElementById('calendar');
    }
    if (!calendarEl) return;
    calendarEl.setAttribute('data-vista', tipoVista);
    calendarEl.classList.remove('vista-dayGridMonth', 'vista-dayGridWeek', 'vista-listWeek');
    calendarEl.classList.add('vista-' + tipoVista);
}

// Función para obtener los parámetros de filtro actuales
function getFilterParams() {
    const sede = document.getElementById('filtro-sede').value;
    const conciliador = getConciliadorSeleccionado();
    return `?sede=${encodeURIComponent(sede)}&conciliador=${encodeURIComponent(conciliador)}`;
}

// Fecha en Y-m-d tomada del calendario local. No se usa toISOString() porque
// convierte a UTC y con -06:00 el primer día del rango se recorre al anterior.
function calFechaISO(fecha) {
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return fecha.getFullYear() + '-' + mes + '-' + dia;
}

// Descarga la agenda del rango que se está viendo. Se usa currentStart /
// currentEnd —no activeStart— para que el archivo cubra exactamente lo que
// dice la etiqueta de rango: en vista mes, el mes, sin los días de relleno.
// currentEnd es exclusivo; el reporte trabaja con un rango cerrado.
function calExportarAgenda() {
    if (!currentCalendar || typeof urlAgendaExportar === 'undefined') {
        return;
    }

    const view = currentCalendar.view;
    const fin = new Date(view.currentEnd.getTime() - 86400000);

    const params = new URLSearchParams({
        start: calFechaISO(view.currentStart),
        end: calFechaISO(fin),
        sede: document.getElementById('filtro-sede').value,
        conciliador: getConciliadorSeleccionado()
    });

    window.location = urlAgendaExportar + '?' + params.toString();
}

// La pestaña "Todos" no consulta un endpoint nuevo: monta las cinco agendas como
// fuentes simultáneas de un mismo calendario, cada una con su color.
// Fuentes de datos, una por agenda. El id viaja al modal (handleEventClick lo
// usa para decidir que pinta), asi que no se cambia a la ligera.
function calFuente(id) {
    const urls = {
        solicitudes:    typeof urlSolicitudes !== 'undefined' ? urlSolicitudes : '',
        audiencias:     urlAudiencias,
        conciliador:    urlConciliadores,
        pagos:          urlPagos,
        ratificaciones: urlRatificaciones,
        citas:          urlCitas,
        // Citas en linea de recepcion (tabla recepcion). Ojo: no confundir con
        // "citas", que a pesar del nombre son cumplimientos de ratificacion.
        citasLinea:     typeof urlCitasLinea !== 'undefined' ? urlCitasLinea : '',
        // Notificaciones pendientes del notificador, en el dia de su audiencia.
        misNotificaciones: typeof urlMisNotificaciones !== 'undefined' ? urlMisNotificaciones : ''
    };

    return { id: id, url: urls[id] + getFilterParams() };
}

// Que agendas pinta cada pastilla. Las padre traen las de sus hijas juntas:
// "Cumplimientos" y "Ratificaciones" son filtro y contenedor a la vez.
var CAL_PASTILLAS = {
    'btn-todos':              ['solicitudes', 'audiencias', 'conciliador', 'pagos', 'ratificaciones', 'citas'],
    'btn-solicitudes':        ['solicitudes'],
    'btn-audiencias':         ['audiencias'],
    'btn-cumplimientos':      ['conciliador', 'pagos'],
    'btn-cumpl-audiencias':   ['conciliador'],
    'btn-cumpl-generales':    ['pagos'],
    'btn-ratificaciones':     ['ratificaciones', 'citas'],
    'btn-rati-cumplimientos': ['citas'],
    'btn-citas-linea':        ['citasLinea'],
    'btn-mis-notificaciones': ['misNotificaciones']
};

// Que semaforo describe a cada pastilla. Las claves son las de
// App\Support\SemaforoAgenda::leyenda(), que llega en CAL_LEYENDAS.
var CAL_PASTILLA_LEYENDA = {
    'btn-todos':              'todos',
    'btn-solicitudes':        'solicitudes',
    'btn-audiencias':         'audiencias',
    'btn-cumplimientos':      'cumplimientos',
    'btn-cumpl-audiencias':   'cumplimientos',
    'btn-cumpl-generales':    'cumplimientos',
    'btn-ratificaciones':     'ratificaciones',
    'btn-rati-cumplimientos': 'cumplimientos',
    'btn-citas-linea':        'citas_linea',
    'btn-mis-notificaciones': 'mis_notificaciones'
};

// La recepcion no tiene "Todos": abre en su unica pastilla.
// El notificador abre en "Mis notificaciones".
var pastillaActiva = (typeof CAL_SOLO_CITAS !== 'undefined' && CAL_SOLO_CITAS)
    ? 'btn-citas-linea'
    : ((typeof CAL_SOLO_NOTIFICADOR !== 'undefined' && CAL_SOLO_NOTIFICADOR) ? 'btn-mis-notificaciones' : 'btn-todos');

function calFuentes(tipo) {
    return (CAL_PASTILLAS[tipo] || []).map(calFuente);
}

function calSeleccionarPastilla(tipo) {
    if (!CAL_PASTILLAS[tipo]) {
        return;
    }

    pastillaActiva = tipo;

    document.querySelectorAll('.cal-tab').forEach(function (b) { b.classList.remove('active'); });

    const boton = document.querySelector('.cal-tab[data-tipo="' + tipo + '"]');
    if (boton) {
        boton.classList.add('active');
    }

    // Si la elegida es una hija, su padre se queda marcado y su fila abierta.
    const fila = boton ? boton.closest('.cal-subtabs') : null;
    if (fila) {
        const botonPadre = document.querySelector('.cal-tab[data-hijas="' + fila.id + '"]');
        if (botonPadre) {
            botonPadre.classList.add('active');
        }
    }

    calMostrarSubpastillas(fila ? fila.id : (boton ? boton.getAttribute('data-hijas') : null));
    calPintarLeyenda(tipo);
    calPintarInhabiles();

    if (currentCalendar) {
        currentCalendar.setOption('eventSources', calFuentes(tipo));
    }
    calRecargarMovil();
}

// ---------------------------------------------------------------------------
// Celulares: agenda-movil.js pinta su propia vista (tira de días + lista) con
// las mismas fuentes y filtros. En escritorio nada de esto corre.
// ---------------------------------------------------------------------------
var CAL_MOVIL = window.matchMedia ? window.matchMedia('(max-width: 767.98px)') : null;

function calEsMovil() {
    return Boolean(CAL_MOVIL && CAL_MOVIL.matches && document.getElementById('calMovil') && typeof CalMovil !== 'undefined');
}

function calRecargarMovil() {
    const wrap = calendarEl ? calendarEl.closest('.cal-wrap') : null;
    if (typeof CalMovil !== 'undefined' && wrap && wrap.classList.contains('is-movil')) {
        CalMovil.recargar();
    }
}

function calModoMovil(movil) {
    const wrap = calendarEl ? calendarEl.closest('.cal-wrap') : null;
    const caja = document.getElementById('calMovil');
    if (!wrap || !caja) {
        return;
    }

    wrap.classList.toggle('is-movil', movil);
    caja.hidden = !movil;

    if (movil) {
        if (CalMovil.activa()) { CalMovil.recargar(); } else { CalMovil.iniciar(); }
    } else if (!currentCalendar) {
        // Si el teléfono se gira o la ventana se agranda, se arma la de escritorio.
        calCrearEscritorio();
    } else {
        currentCalendar.updateSize();
    }
}

function calMostrarSubpastillas(idVisible) {
    document.querySelectorAll('.cal-subtabs').forEach(function (f) {
        f.hidden = f.id !== idVisible;
    });
}

// La leyenda se arma con nodos y no con HTML en cadena: los textos vienen de
// PHP, pero el criterio es el mismo que en calContenidoEvento.
function calPintarLeyenda(tipo) {
    const caja = document.getElementById('calLeyenda');
    if (!caja || typeof CAL_LEYENDAS === 'undefined') {
        return;
    }

    const items = CAL_LEYENDAS[CAL_PASTILLA_LEYENDA[tipo]] || [];
    caja.textContent = '';

    items.forEach(function (item) {
        const span = document.createElement('span');
        const punto = document.createElement('i');
        punto.className = 'leyenda';
        punto.style.background = item.color;
        span.appendChild(punto);
        span.appendChild(document.createTextNode(' ' + item.texto));
        caja.appendChild(span);
    });

    caja.style.display = items.length ? '' : 'none';
}

// Opciones por vista. Vive aparte porque tres lugares las necesitan iguales:
// la configuracion inicial, el refresco de filtros y el cambio de fin de
// semana. El encabezado propio solo se aplica a la vista semana; la de mes usa
// dayHeaderFormat y ahi la fecha estorba.
function calOpcionesVistas() {
    return {
        dayGridWeek: {
            dayMaxEvents: CAL_MAX_EVENTOS_SEMANA,
            dayHeaderContent: calEncabezadoDia
        },
        dayGridMonth: {
            dayMaxEvents: hayConciliadorSeleccionado() ? false : 3
        }
    };
}

function calAplicarVistas() {
    if (currentCalendar) {
        currentCalendar.setOption('views', calOpcionesVistas());
    }
}

function refreshCurrentCalendar() {
    calRecargarMovil();

    if (!currentCalendar) {
        return;
    }

    calAplicarVistas();

    currentCalendar.setOption('eventSources', calFuentes(pastillaActiva));
}

// ---------------------------------------------------------------------------
// Esqueleto de carga
// ---------------------------------------------------------------------------
var calVigia = null;

function calEsqueleto(visible) {
    const sk = document.getElementById('calSkeleton');
    const cal = document.getElementById('calendar');
    const zona = document.getElementById('calZona');

    if (!sk || !cal) {
        return;
    }

    // El contenedor del calendario ya nunca se esconde: si el aviso de fin de
    // carga no llega, se ve la rejilla en lugar de un esqueleto eterno.
    cal.classList.remove('is-oculto');

    if (zona) {
        zona.setAttribute('aria-busy', visible ? 'true' : 'false');
        zona.classList.toggle('is-cargando', !!visible);
    }

    sk.style.display = visible ? '' : 'none';

    clearTimeout(calVigia);

    if (visible) {
        // Red de seguridad: pase lo que pase, el velo se quita.
        calVigia = setTimeout(function () { calEsqueleto(false); }, 6000);
        return;
    }

    sk.classList.add('is-overlay');

    // FullCalendar pudo haber medido con la zona a medio armar
    setTimeout(function () {
        if (currentCalendar) { currentCalendar.updateSize(); }
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
    const color = props.color || info.event.backgroundColor || '#496163';

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

    // Una fuente puede mandar sus propios renglones en props.lineas: una cita
    // en linea no tiene citado ni conciliador todavia, y pintar dos "N/A"
    // en cada tarjeta solo estorba.
    const lineas = Array.isArray(props.lineas) ? props.lineas : [
        ['Solicitante', props.solicitante],
        ['Citado', props.citado],
        ['Conciliador', props.conciliador]
    ];

    lineas.forEach(function (par) {
        const linea = document.createElement('div');
        linea.className = 'evt-linea';
        linea.title = par[0] + ': ' + (par[1] || 'N/A');

        const etiqueta = document.createElement('span');
        etiqueta.className = 'evt-etiqueta';
        etiqueta.textContent = par[0] + ': ';
        linea.appendChild(etiqueta);

        linea.appendChild(document.createTextNode(par[1] || 'N/A'));
        // Ratificaciones: el registro patronal se ve de un vistazo.
        if (par[0] === 'Registro') {
            linea.classList.add(par[1] === 'Validado' ? 'evt-registro--si' : 'evt-registro--no');
        }
        tarjeta.appendChild(linea);
    });

    return { domNodes: [tarjeta] };
}

document.addEventListener('DOMContentLoaded', function () {

    if (!calendarEl) {
        return;
    }

    document.getElementById('filtro-sede').addEventListener('change', function () {
        calFiltrarPersonasPorSede();
        // La sede cambia que dias son inhabiles: el 21 de octubre solo cierra
        // Uruapan. Se invalida la firma para que se vuelvan a pedir.
        calInhabilesRango = null;
        if (currentCalendar) calTraerInhabiles(currentCalendar.view);
        refreshCurrentCalendar();
    });

    calMontarPersonas();

    const btnExportar = document.getElementById('calExportar');
    if (btnExportar) {
        btnExportar.addEventListener('click', calExportarAgenda);
    }

    const btnFinde = document.getElementById('calFinde');
    if (btnFinde) {
        btnFinde.addEventListener('click', function () {
            calGuardarFinde(!calFindeMinimizado());
            calAplicarFinde();
        });
    }

    // El contador de una columna encogida tiene que poder abrirse: si dice
    // que hay dos audiencias el sabado, el clic natural es sobre ella y no
    // sobre el boton de la barra. Va delegado porque las celdas se vuelven a
    // crear en cada render de FullCalendar.
    calendarEl.addEventListener('click', function (e) {
        if (!calFindeMinimizado()) {
            return;
        }

        const celda = e.target.closest('.fc-day-sat, .fc-day-sun');
        if (!celda || !calendarEl.contains(celda)) {
            return;
        }

        calGuardarFinde(false);
        calAplicarFinde();
    });

    // En celulares no se arma FullCalendar: la vista la pinta agenda-movil.js.
    if (!calEsMovil()) {
        calCrearEscritorio();
    }

    document.querySelectorAll('.cal-tab').forEach(function (boton) {
        boton.addEventListener('click', function (e) {
            e.preventDefault();
            calSeleccionarPastilla(this.getAttribute('data-tipo'));
        });
    });

    // Abre en "Todos", salvo recepcion, que abre en "Citas en linea".
    calSeleccionarPastilla(pastillaActiva);

    const btnPrev = document.getElementById('calPrev');
    const btnHoy = document.getElementById('calHoy');
    const btnNext = document.getElementById('calNext');
    const selVista = document.getElementById('calVista');

    if (btnPrev) btnPrev.addEventListener('click', function () { if (currentCalendar) currentCalendar.prev(); });
    if (btnNext) btnNext.addEventListener('click', function () { if (currentCalendar) currentCalendar.next(); });
    if (btnHoy)  btnHoy.addEventListener('click',  function () { if (currentCalendar) currentCalendar.today(); });

    if (selVista) {
        selVista.value = calVistaInicial();
        selVista.addEventListener('change', function () {
            actualizarClaseVista(this.value);
            if (currentCalendar) currentCalendar.changeView(this.value);
        });
    }

    // Va al final: la pastilla inicial ya quedó elegida y la vista móvil pide
    // sus datos una sola vez.
    if (calEsMovil()) {
        calModoMovil(true);
    }
    if (CAL_MOVIL && typeof CalMovil !== 'undefined' && document.getElementById('calMovil')) {
        const alCambiar = function (e) { calModoMovil(e.matches); };
        if (CAL_MOVIL.addEventListener) { CAL_MOVIL.addEventListener('change', alCambiar); } else if (CAL_MOVIL.addListener) { CAL_MOVIL.addListener(alCambiar); }
    }
});

// Calendario de escritorio (FullCalendar). Sin cambios: sólo se movió a una
// función para no crearlo en celulares.
function calCrearEscritorio() {
    currentCalendar = new FullCalendar.Calendar(calendarEl, {
        initialView: calVistaInicial(),
        locale: 'es',
        firstDay: 1,
        height: 'auto',
        contentHeight: 'auto',
        expandRows: false,
        dayMaxEvents: getDayMaxEventsOption(),
        views: calOpcionesVistas(),
        dayHeaderFormat: { weekday: 'short' },
        // La barra la pinta el HTML de la vista, no FullCalendar.
        headerToolbar: false,
        eventSources: calFuentes(pastillaActiva),
        eventClassNames: ['evt-hueco'],
        // El tipo del modal sale de la fuente que trajo el evento, no de la
        // pastilla: en "Todos" y en las padre conviven varias agendas.
        eventClick: (info) => handleEventClick(info, info.event.source ? info.event.source.id : 'pagos'),
        eventContent: calContenidoEvento,
        moreLinkContent: function (arg) { return arg.num + ' mas...'; },
        datesSet: function (info) {
            calPintarEncabezado(info.view);
            actualizarClaseVista(info.view.type);
            calTraerInhabiles(info.view);
        },
        eventsSet: function () {
            calMarcarFinde();
            calPintarInhabiles();
        },
        loading: function (cargando) { calEsqueleto(cargando); }
    });

    actualizarClaseVista(calVistaInicial());
    currentCalendar.render();
    calAplicarFinde();
}


// Detalle de una cita en linea. Se arma con nodos y textContent, no con una
// plantilla de texto: el nombre y el correo los escribe el ciudadano en el
// formulario publico, y con innerHTML cualquier "<" se interpretaria como
// marcado. Es la misma regla que ya sigue calContenidoEvento.
function calModalCita(info) {
    const props = info.event.extendedProps || {};
    const cuerpo = document.querySelector('#evento .modal-body');
    if (!cuerpo) {
        return;
    }
    const tituloModal = document.getElementById('tituloEvento');
    if (tituloModal) { tituloModal.textContent = 'Detalles de la cita en línea'; }

    const estatus = {
        pendiente:  'Sin confirmar',
        confirmada: 'Confirmada',
        atendido:   'Atendida',
        expirada:   'No se presentó'
    };

    const datos = [
        ['Solicitante', props.solicitante],
        ['Trámite',     props.tipo + (props.excepcion ? ' (caso de excepción)' : '')],
        ['Fecha',       info.event.start ? info.event.start.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long' }) : ''],
        ['Hora',        props.hora],
        ['Estatus',     estatus[props.estatus] || props.estatus],
        ['Sede',        props.delegacion],
        ['Atiende',     props.atiende],
        ['Módulo',      props.modulo],
        ['Teléfono',    props.telefono],
        ['Correo',      props.correo],
        ['Folio',       props.folio]
    ];

    cuerpo.textContent = '';

    datos.forEach(function (par) {
        if (par[1] === null || par[1] === undefined || par[1] === '') {
            return;
        }
        const etiqueta = document.createElement('strong');
        etiqueta.textContent = par[0] + ': ';
        cuerpo.appendChild(etiqueta);
        cuerpo.appendChild(document.createTextNode(String(par[1])));
        cuerpo.appendChild(document.createElement('br'));
    });

    const pie = document.createElement('div');
    pie.className = 'modal-footer';

    const cerrar = document.createElement('button');
    cerrar.type = 'button';
    cerrar.className = 'btn btn-secondary';
    cerrar.setAttribute('data-bs-dismiss', 'modal');
    cerrar.textContent = 'Cerrar';
    pie.appendChild(cerrar);

    // Solo el acuse, que es de lectura. La confirmacion de asistencia NO va
    // aqui: es la URL del QR y cambia el estatus segun la hora en que se
    // abre, asi que un clic en otro dia marcaria la cita como expirada.
    if (typeof urlAcuseCita !== 'undefined') {
        const acuse = document.createElement('a');
        acuse.className = 'btn btn-info';
        acuse.href = urlAcuseCita + '/' + encodeURIComponent(info.event.id) + '/documento';
        acuse.target = '_blank';
        acuse.rel = 'noopener';
        acuse.textContent = 'Ver acuse';
        pie.appendChild(acuse);
    }

    // Conciliación, Género e Igualdad: el caso de excepción se atiende desde
    // aquí (la URL sólo existe para quien puede atender; CasosExcepcion).
    if (props.excepcion && typeof urlAtenderExcepcion !== 'undefined' && urlAtenderExcepcion) {
        const atender = document.createElement('a');
        atender.className = 'btn btn-primary';
        atender.href = urlAtenderExcepcion + '/' + encodeURIComponent(info.event.id);
        atender.textContent = 'Atender caso';
        pie.appendChild(atender);
    }

    cuerpo.appendChild(pie);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('evento')).show();
}

// Texto seguro para el detalle (se arma con template strings) y con un
// letrero en lugar de "null" cuando el dato viene vacio.
function calTexto(valor, siVacio) {
    const texto = (valor === null || valor === undefined) ? '' : String(valor).trim();
    if (texto === '' || texto.toLowerCase() === 'null') {
        return siVacio || '';
    }
    return texto.replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

// -----------------------------------------------------------------------------
// Detalle de un evento (modal #evento).
//
// Antes cada tipo pintaba renglones sueltos "Etiqueta: valor" y los vacios
// salian como "null". Ahora todos usan el mismo armado: cabecera con el tipo,
// el nombre principal, estatus en color y la fecha (el tipo va en el titulo
// del modal); luego secciones con
// etiqueta/valor; y abajo las acciones. Todo valor pasa por calTexto.
// -----------------------------------------------------------------------------

// Registro patronal de una ratificacion (tabla abogados, el folio es su
// idAbogado). Guia para la recepcion: solo "existe" si ya esta validado.
function calFilasRegistro(props) {
    const r = props.registro_patronal;
    const filas = [];

    if (!r) {
        filas.push(['Estado', '<span class="cal-registro cal-registro--no"><i class="bi bi-x-circle"></i> No existe registro aún</span>']);
        filas.push(['Folio', '<span class="cal-det__vacio">No existe aún</span>']);
        return filas;
    }

    if (r.validado) {
        filas.push(['Estado', '<span class="cal-registro cal-registro--si"><i class="bi bi-check-circle"></i> Registro validado</span>']);
        filas.push(['Folio', '<strong>' + calTexto(r.folio) + '</strong>']);
        if (r.representante) {
            filas.push(['Representante', calTexto(r.representante)]);
        } else if (r.patron) {
            filas.push(['Comparece', calTexto(r.patron) + ' <span class="cal-det__vacio">(sin representante)</span>']);
        }
        if (r.vigencia) {
            filas.push(['Vigente al', calTexto(r.vigencia)]);
        }
    } else {
        filas.push(['Estado', '<span class="cal-registro cal-registro--no"><i class="bi bi-x-circle"></i> No existe registro aún</span>']);
        filas.push(['Folio', calTexto(r.folio) + ' <span class="cal-det__vacio">(capturado, pendiente de validar)</span>']);
    }
    return filas;
}

function calFechaLarga(info, props) {
    const inicio = info.event.start;
    let fecha = inicio
        ? inicio.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
        : calTexto(props.fecha);
    fecha = fecha.charAt(0).toUpperCase() + fecha.slice(1);
    const hora = calTexto(props.hora).slice(0, 5);
    return fecha + (hora ? ' · ' + hora + ' h' : '');
}

function calMonto(valor) {
    const n = Number(valor);
    if (valor === null || valor === undefined || valor === '' || isNaN(n)) {
        return calTexto(valor, '<span class="cal-det__vacio">Sin monto</span>');
    }
    return n.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
}

// d = { tipo, titulo, sub, estatus, color, fecha, secciones: [{titulo, filas: [[etiqueta, html]]}], accion: {href, texto} }
function calArmarDetalle(d) {
    const vacio = '<span class="cal-det__vacio">Sin dato</span>';
    let html = '<div class="cal-det">';

    html += '<div class="cal-det__cabeza">' +
        '<h4 class="cal-det__titulo">' + calTexto(d.titulo, 'Sin nombre') + '</h4>' +
        (d.sub ? '<div class="cal-det__sub">' + d.sub + '</div>' : '') +
        '<div class="cal-det__meta">' +
            (d.estatus ? '<span class="cal-det__estatus" style="--cal-color:' + (/^#[0-9a-f]{3,8}$/i.test(d.color || '') ? d.color : '#829A9C') + '">' + calTexto(d.estatus) + '</span>' : '') +
            '<span class="cal-det__fecha"><i class="bi bi-calendar-event"></i> ' + d.fecha + '</span>' +
        '</div>' +
    '</div>';

    (d.secciones || []).forEach(function (sec) {
        const filas = (sec.filas || []).filter(function (f) { return f; });
        if (!filas.length) { return; }
        html += '<section class="cal-det__seccion">' +
            '<h5 class="cal-det__seccion-titulo">' + sec.titulo + '</h5><dl class="cal-det__lista">';
        filas.forEach(function (f) {
            html += '<dt>' + f[0] + '</dt><dd>' + (f[1] === '' || f[1] === null || f[1] === undefined ? vacio : f[1]) + '</dd>';
        });
        html += '</dl></section>';
    });

    html += '<div class="modal-footer cal-det__pie">' +
        '<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>' +
        (d.accion ? '<a href="' + d.accion.href + '" class="btn btn-info cal-det__accion">' + d.accion.texto + ' <i class="bi bi-arrow-right"></i></a>' : '') +
    '</div></div>';

    return html;
}

function handleEventClick(info, calendarType) {
    if (calendarType === 'citasLinea') {
        calModalCita(info);
        return;
    }

    const props = info.event.extendedProps;
    const t = calTexto;

    if (calendarType === 'misNotificaciones') {
        const dias = props.dias;
        const plazo = dias === null || dias === undefined ? 'Sin audiencia'
            : (dias < 0 ? 'La audiencia ya pasó' : (dias === 0 ? 'La audiencia es hoy' : 'Faltan ' + dias + ' día' + (dias === 1 ? '' : 's') + ' para la audiencia'));
        const d0 = {
            tipo: 'Notificación pendiente',
            titulo: props.citado || info.event.title,
            sub: 'Expediente ' + t(props.nue),
            estatus: plazo, color: props.color,
            fecha: props.audiencia ? 'Audiencia: ' + t(props.audiencia) : 'Sin audiencia',
            secciones: [
                { titulo: 'Dónde', filas: [
                    ['Domicilio', t(props.domicilio)],
                    ['Colonia / municipio', t(props.lugar)],
                    ['Sede', t(props.delegacion)]
                ] },
                { titulo: 'Notificación', filas: [['Tipo', t(props.tipo)]] }
            ],
            accion: props.url ? { href: props.url, texto: 'Diligenciar' } : null
        };
        const tituloN = document.getElementById('tituloEvento');
        if (tituloN) { tituloN.textContent = 'Detalles de la notificación'; }
        document.querySelector('#evento .modal-body').innerHTML = calArmarDetalle(d0);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('evento')).show();
        return;
    }
    const id = encodeURIComponent(info.event.id);
    const tipoUrl = encodeURIComponent(props.tipo === undefined ? '' : props.tipo);
    const fecha = calFechaLarga(info, props);
    let d = null;

    if (calendarType === 'pagos' || calendarType === 'conciliador' || calendarType === 'citas') {
        d = {
            tipo: calendarType === 'citas' ? 'Cumplimiento de ratificación' : 'Cumplimiento',
            titulo: props.nue,
            sub: t(props.descripcion),
            estatus: props.estatus, color: props.color, fecha: fecha,
            secciones: [
                { titulo: 'Partes', filas: [
                    calendarType === 'citas' ? null : ['Trabajador', t(props.trabajador)],
                    calendarType === 'citas' ? null : ['Patrón', t(props.empresa)],
                    ['Conciliador', t(props.conciliador)]
                ] },
                { titulo: 'Pago', filas: [
                    ['Monto', calMonto(props.monto)],
                    ['Observaciones', t(props.observaciones, '<span class="cal-det__vacio">No tiene observaciones</span>')]
                ] }
            ],
            accion: { href: 'cumplimiento/consulta/' + id + '/' + tipoUrl, texto: calendarType === 'pagos' ? 'Ver cumplimiento' : 'Ver detalle' }
        };
    }
    else if (calendarType === 'audiencias') {
        const audienciaId = encodeURIComponent(props.audiencia_id ?? info.event.id);
        d = {
            tipo: 'Audiencia',
            titulo: info.event.title,
            estatus: props.estatus, color: props.color, fecha: fecha,
            secciones: [
                { titulo: 'Audiencia', filas: [
                    ['Conciliador', t(props.conciliador)],
                    ['Sede', t(props.delegacion)],
                    ['Sala', t(props.sala)]
                ] }
            ],
            accion: props.estatus === 'Pendiente'
                ? { href: 'solicitud/iniciar/' + encodeURIComponent(props.id_solicitud) + '?audiencia_id=' + audienciaId, texto: 'Ir a la audiencia' }
                : null
        };
    }
    else if (calendarType === 'ratificaciones') {
        // En una ratificacion solicita la empresa; el citado es el trabajador.
        d = {
            tipo: 'Ratificación',
            titulo: props.solicitante || info.event.title,
            sub: 'Folio de ratificación ' + t(props.folio_audiencia),
            estatus: props.estatus, color: props.color, fecha: fecha,
            secciones: [
                { titulo: 'Partes', filas: [
                    ['Solicitante', t(props.solicitante || info.event.title)],
                    ['Citado', t(props.citado)],
                    ['Conciliador', t(props.conciliador, '<span class="cal-det__vacio">Sin asignar</span>')],
                    ['Sede', t(props.delegacion)]
                ] },
                { titulo: 'Registro patronal', filas: calFilasRegistro(props) }
            ],
            accion: { href: 'cumplimiento/consulta/' + id + '/' + tipoUrl, texto: 'Ver detalle' }
        };
    }
    else if (calendarType === 'solicitudes') {
        d = {
            tipo: 'Solicitud',
            titulo: props.nue,
            estatus: props.estatus, color: props.color, fecha: fecha,
            secciones: [
                { titulo: 'Partes', filas: [
                    ['Solicitante', t(props.solicitante)],
                    ['Citado', t(props.citado)],
                    ['Conciliador', t(props.conciliador, '<span class="cal-det__vacio">Sin asignar</span>')],
                    ['Sede', t(props.delegacion)]
                ] }
            ],
            accion: { href: 'solicitud/iniciar/' + encodeURIComponent(props.id_solicitud), texto: 'Ver solicitud' }
        };
    }

    if (!d) {
        return;
    }

    // Recepcion ve el evento pero no entra a la audiencia ni al cumplimiento.
    if (typeof CAL_ES_RECEPCION !== 'undefined' && CAL_ES_RECEPCION) {
        d.accion = null;
    }

    const titulo = document.getElementById('tituloEvento');
    if (titulo) { titulo.textContent = 'Detalles de ' + d.tipo.toLowerCase(); }

    // Bootstrap 5 quitó la API de plugins por jQuery: $('#evento').modal('show')
    // dejó de existir al pasar esta pantalla de Bootstrap 4 a 5.3.
    document.querySelector('#evento .modal-body').innerHTML = calArmarDetalle(d);
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