{{--
    Scripts de la agenda. Lo comparten la pantalla /agenda y el Inicio, así que
    el calendario existe una sola vez: cualquier arreglo aquí llega a las dos.
--}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales-all.min.js"></script>
    <script>
        const urlCitas          = "{{ route('citas.eventos') }}";
        const urlPagos          = "{{ route('pagos.eventos') }}";
        const urlConciliadores  = "{{ route('conciliador.eventos') }}";
        const urlAudiencias     = "{{ route('audiencias.eventos') }}";
        const urlRatificaciones = "{{ route('ratificaciones.eventos') }}";
        const urlSolicitudes    = "{{ route('solicitudes.eventos') }}";
        const urlInhabiles      = "{{ route('agenda.inhabiles') }}";
        const urlCitasLinea     = "{{ route('agenda.citas_linea') }}";
        // Base del acuse en PDF de una cita (citas/{id}/documento). Es sólo
        // lectura. A propósito NO se expone citas/{id}/confirmar: es el destino
        // del QR y cambia el estatus según la hora en que se abre.
        const urlAcuseCita      = "{{ url('citas') }}";

        // La recepción abre en su única pastilla, "Citas en línea", en vez de
        // "Todos". Se decide en PHP con la misma regla que el resto del
        // sistema (App\Support\Recepcion) y no adivinando desde el DOM.
        const CAL_SOLO_CITAS    = @json(\App\Support\Recepcion::es(auth()->user()));
        // La recepción consulta, no opera: el detalle de un evento no lleva
        // los botones que mandan a pantallas que no son suyas.
        const CAL_ES_RECEPCION  = CAL_SOLO_CITAS;

        // Catalogo del semaforo, servido desde PHP para que la leyenda y los
        // colores de los eventos no puedan desincronizarse: los dos salen de
        // App\Support\SemaforoAgenda.
        const CAL_LEYENDAS = @json(\App\Support\SemaforoAgenda::leyenda());
        // Descarga de la agenda; no es una fuente de eventos, pero comparte
        // los mismos filtros de la barra.
        const urlAgendaExportar = "{{ route('agenda.exportar') }}";
        // Por si también la usas dentro de tu configuración de FullCalendar:
        const urlBloqueos       = "{{ route('calendario.bloqueos') }}"; 
    </script>

    <script src="{{ asset('assets/js/calendar.js') }}"></script>
    <script src="{{ asset('assets/js/general/menu.js') }}"></script>
