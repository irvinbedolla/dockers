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

    @auth
        @role('Auxiliar')
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('create_asesoria') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Asesorias</span>
            </a>
            <a class="nav-link" href="{{ route('todas_audiencias') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" >Audiencias</span>
            </a>
            <a class="nav-link" href="{{ route('audiencias.cumplimiento') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" >Cumplimientos</span>
            </a>
            <a class="nav-link" href="{{ route('misturnos') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="turnos()">Mis Turnos</span>
            </a>
            <a class="nav-link" href="{{ route('plantillas_index') }}">
                <i class="bi bi-file-text-fill"></i><span class="text-dark" onclick="estadistica()">Plantillas</span>
            </a>
            <a class="nav-link" href="{{ route('poderes') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Poderes</span>
            </a>
            <a class="nav-link" href="{{ route('index_ratificacion') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Ratificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('solicitudes_index') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Solicitudes</span>
            </a>
            <a class="nav-link" href="{{ route('turnos') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="turnos()">Turnos</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Conciliador') 
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('create_asesoria') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Asesorias</span>
            </a>
            <a class="nav-link" href="{{ route('todas_audiencias') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" >Audiencias</span>
            </a>
            <a class="nav-link" href="{{ route('plantillas_index') }}">
                <i class="bi bi-file-text-fill"></i><span class="text-dark" onclick="estadistica()">Plantillas</span>
            </a>
            <a class="nav-link" href="{{ route('poderes') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Poderes</span>
            </a>
            <a class="nav-link" href="{{ route('index_ratificacion') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Ratificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('solicitudes_index') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Solicitudes</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Notificador')
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('create_asesoria') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Asesorias</span>
            </a>
            <a class="nav-link" href="{{ route('crear_inidencia') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencia</span>
            </a>
            <a class="nav-link" href="{{ route('Historial_Notificacador') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Mis Notificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('plantillas_index') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Plantillas</span>
            </a>
            <a class="nav-link" href="{{ route('seer') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Por Notificar</span>
            </a>
            <a class="nav-link" href="{{ route('solicitudes_index') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Solicitudes</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Capacitacion Admin')
            <a class="nav-link" href="{{ route('capacitaciones') }}">
                <i class="bi bi-backpack4-fill"></i><span class="text-dark" onclick="capacitaciones()">Capacitaciones</span>
            </a>
            <a class="nav-link" href="{{ route('expedientes') }}">
                <i class="bi bi-graph-down"></i><span class="text-dark" onclick="expedientes()">Expediente</span>
            </a>
            <a class="nav-link" href="{{ route('persona.historial') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Historial</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Delegado')
            <a class="nav-link" href="{{ route('configuracion') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Administración</span>
            </a>
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('create_asesoria') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Asesorias</span>
            </a>
            <a class="nav-link" href="{{ route('seer.estadistica') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Estadísticas</span>
            </a>
            <a class="nav-link" href="{{ route('incidencias.busqueda.index') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencias</span>
            </a>
            <a class="nav-link" href="{{ route('plantillas_index') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Plantillas</span>
            </a>
            <a class="nav-link" href="{{ route('poderes') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Poderes</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Estadistica')
            <a class="nav-link" href="{{ route('seer.estadistica') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Estadísticas</span>
            </a>
            <a class="nav-link" href="{{ route('index_ratificacion') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Ratificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('solicitudes_index') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Solicitudes</span>
            </a>
            <a class="nav-link" href="{{ route('notificaciones_consultar') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Notificaciones</span>
            </a>
        @endrole
    @endauth    
    @auth
        @role('Turnos')
            
            <a class="nav-link" href="{{ route('index_oficialia') }}">
                <i class="bi bi-journal-text"></i><span class="text-dark" onclick="oficialia()">Oficialia de Partes</span>
            </a>
            <a class="nav-link" href="{{ route('turnos') }}">
                <i class="bi bi-book" aria-hidden="true"></i></i><span class="text-dark" onclick="turnos()">Turnos</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Excepcion')
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('index_oficialia') }}">
                <i class="bi bi-people-fill"></i><span class="text-dark" onclick="oficialia()">Oficialia de Partes</span>
            </a>
            <a class="nav-link" href="{{ route('poderes') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Poderes</span>
            </a>
            <a class="nav-link" href="#">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Reporte</span>
            </a>
            <a class="nav-link" href="{{ route('excepcion') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Casos de Excepción</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Enlace')
            <a class="nav-link" href="{{ route('create_asesoria') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Asesorias</span>
            </a>
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('seer.estadistica') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Estadísticas</span>
            </a>
            <a class="nav-link" href="{{ route('crear_inidencia') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencia</span>
            </a>
            <a class="nav-link" href="{{ route('notificaciones_consultar') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Notificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('notificaciones') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Por Notificar</span>
            </a>
            <a class="nav-link" href="{{ route('index_ratificacion') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Ratificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('solicitudes_index') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Solicitudes</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Solicitante')
            <a class="nav-link" href="{{ route('mis_solicitudes') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Mis Solicitudes</span>
            </a>
            <a class="nav-link" href="{{ route('ratificacion') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Mis Ratificaciones</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Administrador Solicitante')
            <a class="nav-link" href="{{ route('solicitudes_pendientes') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Solicitudes</span>
            </a>
            <a class="nav-link" href="{{ route('Ratificacion') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Ratificaciones</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Cumplimientos')
            <a class="nav-link" href="{{ route('create_asesoria') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Asesorias</span>
            </a>
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('audiencias.cumplimiento') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" >Cumplimientos</span>
            </a>
            <a class="nav-link" href="{{ route('misestadisticas') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Estadísticas</span>
            </a>
            <a class="nav-link" href="{{ route('crear_inidencia') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencia</span>
            </a>
            <a class="nav-link" href="{{ route('poderes') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Poderes</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Particular')
            <a class="nav-link" href="{{ route('indexDireccionGeneral') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Dirección General</span>
            </a>
        @endrole
    @endauth
    @auth
        @role('Tercer Encuentro')
            <a class="nav-link" href="{{ route('index_tercer_encuentro') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Tercer Encuentro</span>
            </a>
        @endrole
    @endauth
    
    
</li>

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
