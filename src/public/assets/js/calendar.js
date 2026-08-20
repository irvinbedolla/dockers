// 1. Declaración global
var calendarPagos, calendarAudiencias, calendarRatificaciones, calendarCitas, calendarConciliador;
var currentCalendar = null;
var calendarEl = document.getElementById('calendar'); // Asegúrate que este ID exista en tu HTML

// Función para obtener los parámetros de filtro actuales
    function getFilterParams() {
        const sede = document.getElementById('filtro-sede').value;
        const conciliador = document.getElementById('filter-conciliador').value;
        return `?sede=${encodeURIComponent(sede)}&conciliador=${encodeURIComponent(conciliador)}`;
    }

    function refreshCurrentCalendar() {
        if (currentCalendar) {
            // Obtenemos la URL base eliminando cualquier parámetro previo (?)
            const currentSource = currentCalendar.getOption('events');
            const baseUrl = typeof currentSource === 'string' ? currentSource.split('?')[0] : currentSource;
            
            // Aplicamos la nueva URL con los filtros actuales
            const nuevaUrl = baseUrl + getFilterParams();
            
            console.log("Actualizando calendario a:", nuevaUrl); // Para depuración
            currentCalendar.setOption('events', nuevaUrl);
        }
    }

document.addEventListener('DOMContentLoaded', function () {
    
    // Listeners para los select de filtros
    document.getElementById('filtro-sede').addEventListener('change', function() {
        // IMPORTANTE: Usamos la función maestra que actualiza la URL completa
        refreshCurrentCalendar(); 
    });
    document.getElementById('filter-conciliador').addEventListener('change', refreshCurrentCalendar);

    // 2. INICIALIZACIÓN DE CALENDARIOS
    // Usamos una función para evitar repetir toda la configuración 5 veces
    // Usamos una función para evitar repetir toda la configuración 5 veces
// Usamos una función para evitar repetir toda la configuración 5 veces
function crearConfiguracion(endpoint, tipoParaModal) {
    return {
        initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mensual',
            week: 'Semanal'
        },
        events: endpoint + getFilterParams(),
        eventClick: (info) => handleEventClick(info, tipoParaModal),
        windowResize: function(arg) {
            let view = window.innerWidth < 768 ? 'listWeek' : 'dayGridWeek';
            if (this.view.type !== view) { this.changeView(view); }
        },
        
        // HTML DEL EVENTO (Acomodado y con color solo en la hora)
        eventContent: function (info) {
            // Obtenemos el color o le ponemos tu morado por defecto
            const colorEvento = info.event.extendedProps.color || '#6A0F49';
            
            return {
                html: `
                    <div class="custom-event-content" style="padding: 4px; text-align: left; color: #4c6365; font-weight: bold;">
                        <div style="font-weight: bold; font-size: 12px; margin-bottom: 3px; color: ${colorEvento};">
                            <i class="bi bi-clock-fill"></i> ${info.event.extendedProps.hora || 'Sin hora'}
                        </div>
                        
                        <div class="text-truncate" style="font-size: 11px;" title="Sol: ${info.event.extendedProps.solicitante}">
                            <span style="color:#4c6365;">Solicitante:</span> ${info.event.extendedProps.solicitante || 'N/A'}
                        </div>
                        <div class="text-truncate" style="font-size: 11px;" title="Cit: ${info.event.extendedProps.citado}">
                            <span style="color:#4c6365;">Citado:</span> ${info.event.extendedProps.citado || 'N/A'}
                        </div>
                        <div class="text-truncate" style="font-size: 11px;" title="Conc: ${info.event.extendedProps.conciliador}">
                            <span style="color:#4c6365;">Conciliador:</span> ${info.event.extendedProps.conciliador || 'N/A'}
                        </div>
                    </div>
                `
            };
        }
    };
}

    // Instanciamos cada calendario
    calendarCitas          = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlCitas, 'citas'));
    calendarPagos          = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlPagos, 'pagos'));
    calendarConciliador    = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlConciliadores, 'conciliador'));
    calendarAudiencias     = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlAudiencias, 'audiencias'));
    calendarRatificaciones = new FullCalendar.Calendar(calendarEl, crearConfiguracion(urlRatificaciones, 'ratificaciones'));

    // 3. LÓGICA DE LOS BOTONES (Función Maestra)
    const botones = document.querySelectorAll('.btn-calendar');
    const mapeo = {
        'btn-pagos': calendarPagos,
        'btn-audiencias': calendarAudiencias,
        'btn-conciliador': calendarConciliador,
        'btn-citas': calendarCitas,
        'btn-ratificaciones': calendarRatificaciones
    };

    botones.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            const tipo = this.getAttribute('data-tipo');
            const calSeleccionado = mapeo[tipo];

            if (calSeleccionado) {
                switchCalendar(calSeleccionado);
                // Opcional: Resaltar botón activo
                botones.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // Renderizar el primero por defecto
    switchCalendar(calendarPagos);
});

    function switchCalendar(newCalendar) {
        if (currentCalendar) {
            currentCalendar.destroy();
        }
        
        currentCalendar = newCalendar;

        // Antes de renderizar, inyectamos los filtros actuales del DOM
        const baseUrl = currentCalendar.getOption('events').split('?')[0];
        currentCalendar.setOption('events', baseUrl + getFilterParams());
        
        currentCalendar.render();
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="cumplimiento/consulta/${info.event.id}/${props.tipo}" class="btn btn-info">Ver detalle</a>
            </div>
        `;
    }

    $('.modal-body').html(modalContent);
    $('#evento').modal('show');
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