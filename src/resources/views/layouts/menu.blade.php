
@auth
    @php
        /*
        |--------------------------------------------------------------------------
        | Menú lateral
        |--------------------------------------------------------------------------
        | Fuente única de los ítems del sidebar. Para agregar una opción basta con
        | añadir una entrada aquí indicando los roles que deben verla.
        |
        | route  : nombre de la ruta (se omite el ítem si la ruta no existe)
        | url    : alternativa a 'route' para enlaces sin nombre
        | label  : texto visible
        | icon   : clase de Bootstrap Icons
        | roles  : roles que ven el ítem
        | id     : opcional, id del <a> (lo usa menu.js para el badge)
        | badge  : opcional, id del <span> del contador
        */

        $userRoles = auth()->user()->getRoleNames()->all();

        $menu = [
            // 'roles' => '*' significa que lo ve cualquiera que haya entrado.
            ['route' => 'inicio',                     'label' => 'Inicio',                  'icon' => 'bi bi-house-door',          'roles' => '*'],
            ['route' => 'configuracion',              'label' => 'Administración',          'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Delegado']],
            ['route' => 'agenda',                     'label' => 'Agenda',                  'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Conciliador', 'Notificador', 'Delegado', 'Excepcion', 'Enlace', 'Cumplimientos']],
            ['route' => 'create_asesoria',            'label' => 'Asesorias',               'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Auxiliar', 'Conciliador', 'Notificador', 'Delegado', 'Enlace', 'Cumplimientos']],
            ['route' => 'todas_audiencias',           'label' => 'Audiencias',              'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Conciliador']],
            ['route' => 'todas_notificaciones',       'label' => 'Busqueda Notificaciones', 'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Administrador']],
            ['route' => 'capacitaciones',             'label' => 'Capacitaciones',          'icon' => 'bi bi-backpack4-fill',      'roles' => ['Capacitacion Admin']],
            ['route' => 'subir_doc_masivo',           'label' => 'Carga Masiva',            'icon' => 'bi bi-bank',                'roles' => ['Super Usuario']],
            ['route' => 'excepcion',                  'label' => 'Casos de Excepción',      'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Excepcion']],
            ['route' => 'index_conciliadores',        'label' => 'Conciliadores',           'icon' => 'bi bi-bank',                'roles' => ['Super Usuario']],
            ['route' => 'audiencias.cumplimiento',    'label' => 'Cumplimientos',           'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Auxiliar', 'Cumplimientos']],
            ['route' => 'indexDireccionGeneral',      'label' => 'Dirección General',       'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Particular']],
            ['route' => 'seer.estadistica',           'label' => 'Estadísticas',            'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Super Usuario', 'Administrador', 'Delegado', 'Estadistica', 'Enlace']],
            ['route' => 'reportes_conciliador',       'label' => 'Estadisticas',            'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Conciliador']],
            ['route' => 'misestadisticas',            'label' => 'Estadísticas',            'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Cumplimientos']],
            ['route' => 'turno_estadistica',          'label' => 'Estadística Turno',       'icon' => 'bi bi-graph-up',            'roles' => ['Super Usuario']],
            ['route' => 'expedientes',                'label' => 'Expediente',              'icon' => 'bi bi-graph-down',          'roles' => ['Capacitacion Admin']],
            ['route' => 'persona.historial',          'label' => 'Historial',               'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Capacitacion Admin']],
            ['route' => 'crear_inidencia',            'label' => 'Incidencia Crear',        'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Administrador']],
            ['route' => 'crear_inidencia',            'label' => 'Incidencia',              'icon' => 'bi bi-bank',                'roles' => ['Notificador', 'Enlace', 'Cumplimientos']],
            ['route' => 'incidencias.busqueda.index', 'label' => 'Incidencias Consulta',    'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Administrador']],
            ['route' => 'incidencias.busqueda.index', 'label' => 'Incidencias',             'icon' => 'bi bi-bank',                'roles' => ['Delegado']],
            ['route' => 'Historial_Notificacador',    'label' => 'Mis Notificaciones',      'icon' => 'bi bi-file-person',         'roles' => ['Notificador']],
            ['route' => 'ratificacion',               'label' => 'Mis Ratificaciones',      'icon' => 'bi bi-bank',                'roles' => ['Solicitante']],
            ['route' => 'mis_solicitudes',            'label' => 'Mis Solicitudes',         'icon' => 'bi bi-file-person',         'roles' => ['Solicitante']],
            ['route' => 'misturnos',                  'label' => 'Mis Turnos',              'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Auxiliar']],
            ['route' => 'notificaciones_consultar',   'label' => 'Notificaciones',          'icon' => 'bi bi-file-person',         'roles' => ['Estadistica', 'Enlace']],
            ['route' => 'index_oficialia',            'label' => 'Oficialia de Partes',     'icon' => 'bi bi-journal-text',        'roles' => ['Super Usuario', 'Turnos', 'Excepcion']],
            ['route' => 'firma_citatorio',            'label' => 'Pendiente de Firma',      'icon' => 'bi bi-bank',                'roles' => ['Super Usuario'], 'id' => 'menu-pendiente-firma', 'badge' => 'badge-pendiente-firma'],
            ['route' => 'plantillas_index',           'label' => 'Plantillas',              'icon' => 'bi bi-file-text-fill',      'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Conciliador', 'Notificador', 'Delegado']],
            ['route' => 'poderes',                    'label' => 'Poderes',                 'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Conciliador', 'Delegado', 'Excepcion', 'Cumplimientos']],
            ['route' => 'notificaciones',             'label' => 'Por Notificar',           'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Administrador', 'Enlace']],
            ['route' => 'seer',                       'label' => 'Por Notificar',           'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Notificador']],
            ['route' => 'index_ratificacion',         'label' => 'Ratificaciones',          'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Conciliador', 'Estadistica', 'Enlace']],
            ['route' => 'Ratificacion',               'label' => 'Ratificaciones',          'icon' => 'bi bi-bank',                'roles' => ['Administrador Solicitante']],
            ['url'   => '#',                          'label' => 'Reporte',                 'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Excepcion']],
            ['route' => 'roles',                      'label' => 'Roles',                   'icon' => 'bi bi-person-lines-fill',   'roles' => ['Super Usuario']],
            ['route' => 'solicitudes_index',          'label' => 'Solicitudes',             'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Conciliador', 'Notificador', 'Estadistica', 'Enlace']],
            ['route' => 'solicitudes_pendientes',     'label' => 'Solicitudes',             'icon' => 'bi bi-file-person',         'roles' => ['Administrador Solicitante']],
            ['route' => 'solicitudes_pendientes',     'label' => 'Solicitudes Pendientes',  'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario']],
            ['route' => 'index_tercer_encuentro',     'label' => 'Tercer Encuentro',        'icon' => 'bi bi-bank',                'roles' => ['Tercer Encuentro']],
            ['route' => 'turnos',                     'label' => 'Turnos',                  'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Administrador', 'Auxiliar']],
            ['route' => 'turnos',                     'label' => 'Turnos',                  'icon' => 'bi bi-book',                'roles' => ['Turnos']],
            ['route' => 'usuarios',                   'label' => 'Usuarios',                'icon' => 'bi bi-people-fill',         'roles' => ['Super Usuario']],
        ];
    @endphp

    @foreach ($menu as $item)
        @php
            // Se omite el ítem si el usuario no tiene ninguno de sus roles.
            $visible = $item['roles'] === '*'
                ? true
                : (bool) array_intersect($item['roles'], $userRoles);

            // Si la ruta no existe, se omite en lugar de reventar toda la página.
            $destino = null;
            $activo  = false;

            if ($visible) {
                if (isset($item['url'])) {
                    $destino = $item['url'];
                } elseif (isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])) {
                    $destino = route($item['route']);
                    $activo  = request()->routeIs($item['route']);
                }
            }
        @endphp

        @if ($destino)
            <li class="nav-item side-menus {{ $activo ? 'active' : '' }}">
                <a class="nav-link"
                href="{{ $destino }}"
                @isset($item['id']) id="{{ $item['id'] }}" @endisset
                @if ($activo) aria-current="page" @endif>
                    <i class="{{ $item['icon'] }}"></i>
                    <span class="text-dark">{{ $item['label'] }}</span>
                    @isset($item['badge'])
                        <span id="{{ $item['badge'] }}" class="badge bg-danger ms-1" style="display: none;">0</span>
                    @endisset
                </a>
            </li>
        @endif
    @endforeach
@endauth