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
        // El conciliador entra directo a "Todos", en vista de mes.
        const calArranqueTodos  = {{ ($userRole[0] ?? '') === 'Conciliador' ? 'true' : 'false' }};
        
        // Por si también la usas dentro de tu configuración de FullCalendar:
        const urlBloqueos       = "{{ route('calendario.bloqueos') }}"; 
    </script>

    <script src="{{ asset('assets/js/calendar.js') }}"></script>
    <script src="{{ asset('assets/js/general/menu.js') }}"></script>
