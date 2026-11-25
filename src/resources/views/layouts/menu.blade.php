
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
        | url    : alternativa a 'route' para Enlace sin nombre
        | label  : texto visible
        | icon   : clase de Bootstrap Icons
        | roles  : roles que ven el ítem
        | id     : opcional, id del <a> (lo usa menu.js para el badge)
        | badge  : opcional, id del <span> del contador
        | si     : opcional, closure que también debe dar true (interruptores).
        | especialidad : opcional, permiso que necesita quien entra SÓLO como
        |          Auxiliar (ver App\Support\Especialidades). Así el de
        |          ratificaciones no ve Solicitudes ni el de solicitudes ve
        |          Ratificaciones; los demás roles de la lista no cambian.
        */

        $userRoles = auth()->user()->getRoleNames()->all();
        $soloAuxiliar = \App\Support\Especialidades::soloAuxiliar(auth()->user());

        // Los tres roles de recepción. Sus nombres viven en un solo lugar
        // (App\Support\Recepcion) para que el menú y las rutas no se separen.
        $recepcion = \App\Support\Recepcion::ROLES;

        $menu = [
            // 'roles' => '*' significa que lo ve cualquiera que haya entrado.
            ['route' => 'inicio',                     'label' => 'Inicio',                  'icon' => 'bi bi-house-door',          'roles' => '*'],
            // Flujo nuevo cita → solicitud: sólo si el interruptor lo habilita para esta persona.
            ['route' => 'mi_modulo',                  'label' => 'Mi módulo',               'icon' => 'bi bi-person-workspace',    'roles' => '*',
                'si' => fn () => \App\Http\Controllers\MiModuloController::puedeVer(auth()->user())],
            // Llegadas hoy: la recepción (Morelia 02 y la General) marca quién llegó; el conciliador sólo consulta.
            ['route' => 'llegadas.index',             'label' => 'Llegadas hoy',            'icon' => 'bi bi-person-check',        'roles' => \App\Support\LlegadasHoy::roles(),
                'si' => fn () => \App\Support\LlegadasHoy::puedeVer(auth()->user())],
            ['route' => 'configuracion',              'label' => 'Administración',          'icon' => 'bi bi-lock-fill',           'roles' => ['Super Usuario', 'Delegado', 'Enlace', 'Directivo']],
            ['route' => 'agenda',                     'label' => 'Agenda',                  'icon' => 'bi bi-calendar-week',       'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Orientador', 'Conciliador', 'Notificador', 'Delegado', 'Excepcion', \App\Support\CasosExcepcion::ROL,  'Cumplimientos', 'Directivo','Turnos', ...$recepcion]],
            ['route' => 'capacitaciones',             'label' => 'Capacitaciones',          'icon' => 'bi bi-backpack4-fill',      'roles' => ['Capacitacion Admin']],
            ['route' => 'subir_doc_masivo',           'label' => 'Carga Masiva',            'icon' => 'bi bi-bank',                'roles' => []],
            // Casos de excepción: sus roles y quien tenga el distintivo "Enlace de Género e Igualdad".
            ['route' => 'excepcion',                  'label' => 'Casos de Excepción',      'icon' => 'bi-person-vcard',           'roles' => '*', 'activo' => ['excepcion', 'excepciones.*', 'atender_excepcion'],
                'si' => fn () => \App\Support\CasosExcepcion::consulta(auth()->user())],
            // Sólo el departamento (ni el Enlace de Género e Igualdad). Super Usuario y Directivo la abren desde el Panel estadístico.
            ['route' => 'estadisticas.excepciones',   'label' => 'Estadísticas de excepción', 'icon' => 'bi bi-bar-chart-line',     'roles' => [\App\Support\CasosExcepcion::ROL]],
            ['route' => 'nueva_cita',                 'label' => 'Crear turno',             'icon' => 'bi bi-calendar-plus',       'roles' => [\App\Support\CasosExcepcion::ROL]],
            ['route' => 'indexDireccionGeneral',      'label' => 'Dirección General',       'icon' => 'bi bi-bank',                'roles' => []],
            ['route' => 'expedientes',                'label' => 'Expediente',              'icon' => 'bi bi-graph-down',          'roles' => ['Capacitacion Admin']],
            ['route' => 'crear_inidencia',            'label' => 'Incidencia Crear',        'icon' => 'bi bi-bank',                'roles' => []],
            ['route' => 'crear_inidencia',            'label' => 'Incidencia',              'icon' => 'bi bi-bank',                'roles' => []],
            //['route' => 'Historial_Notificacador',    'label' => 'Mis Notificaciones',      'icon' => 'bi bi-file-person',         'roles' => ['Notificador']],
            ['route' => 'ratificacion',               'label' => 'Mis Ratificaciones',      'icon' => 'bi bi-bank',                'roles' => ['Solicitante']],
            ['route' => 'mis_solicitudes',            'label' => 'Mis Solicitudes',         'icon' => 'bi bi-file-person',         'roles' => ['Solicitante']],
            ['route' => 'misturnos',                  'label' => 'Mis Turnos',              'icon' => 'bi bi-file-person',         'roles' => []],
            //['route' => 'index_oficialia',            'label' => 'Oficialía de Partes',     'icon' => 'bi bi-file-post',           'roles' => ['Super Usuario', 'Excepcion', 'Directivo', 'Delegado']],
            ['route' => 'firma_citatorio',            'label' => 'Pendiente de Firma',      'icon' => 'bi bi-pencil-square',       'roles' => [], 'id' => 'menu-pendiente-firma', 'badge' => 'badge-pendiente-firma'],
            ['route' => 'poderes',                    'label' => 'Poderes',                 'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Orientador', 'Conciliador', 'Delegado', 'Excepcion', 'Cumplimientos', 'Directivo','Excepcion']],
            //['route' => 'notificaciones',             'label' => 'Por Notificar',           'icon' => 'bi bi-envelope-paper',      'roles' => ['Super Usuario', 'Administrador', 'Enlace', 'Directivo']],
            //['route' => 'seer',                       'label' => 'Por Notificar',           'icon' => 'bi bi-envelope-paper',      'roles' => ['Notificador']],
            ['url'   => '#',                          'label' => 'Reporte',                 'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Excepcion']],
            
            ['route' => 'solicitudes_index',          'label' => 'Solicitudes',             'icon' => 'bi bi-file-earmark-text-fill', 'roles' => ['Super Usuario', 'Administrador', 'Orientador', 'Auxiliar', 'Conciliador',  'Cumplimientos', 'Directivo','Excepcion', 'Delegado'], 'especialidad' => 'solicitudes_revisar'],
            ['route' => 'solicitudes_pendientes',     'label' => 'Solicitudes',             'icon' => 'bi bi-file-earmark-text-fill', 'roles' => ['Administrador Solicitante']],
            ['route' => 'todas_ratificaciones',         'label' => 'Ratificaciones',          'icon' => 'bi bi-bank',                'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Excepcion','Directivo', 'Delegado', 'Conciliador'], 'especialidad' => 'ratificaciones_consultar'],
            ['route' => 'Ratificacion',               'label' => 'Ratificaciones',          'icon' => 'bi bi-bank',                'roles' => ['Administrador Solicitante']],
            ['route' => 'audiencias.cumplimiento',    'label' => 'Cumplimientos',           'icon' => 'bi bi-cash-coin',           'roles' => ['Super Usuario', 'Auxiliar', 'Cumplimientos', 'Directivo', 'Delegado', 'Orientador'], 'especialidad' => 'cumplimientos_consultar'],
            ['route' => 'todas_audiencias',           'label' => 'Audiencias',              'icon' => 'bi bi-people-fill',         'roles' => ['Super Usuario', 'Administrador', 'Conciliador', 'Directivo','Excepcion', 'Delegado']],
            ['route' => 'index_notificaciones',       'label' => 'Notificaciones',          'icon' => 'bi bi-envelope',            'roles' => ['Super Usuario', 'Enlace', 'Directivo','Excepcion','Notificador', 'Delegado']],
            ['route' => 'create_asesoria',            'label' => 'Asesorías',               'icon' => 'bi bi-person-check-fill',   'roles' => ['Super Usuario', 'Orientador', 'Delegado', 'Auxiliar'], 'especialidad' => 'asesoria_crear'],
            ['route' => 'estadisticas.index',        'label' => 'Panel estadístico',       'icon' => 'bi bi-bar-chart-line-fill', 'roles' => ['Super Usuario', 'Estadistica', 'Directivo'], 'activo' => 'estadisticas.*'],
            // Estadística lo tiene en el menú; Super Usuario y Directivo, como botón en el Panel estadístico.
            ['route' => 'datos_referencia.index',     'label' => 'Datos de referencia',     'icon' => 'bi bi-database-fill-gear',  'roles' => ['Estadistica'], 'activo' => 'datos_referencia.*'],
            ['route' => 'seer.estadistica',           'label' => 'Estadísticas',            'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Super Usuario', 'Auxiliar', 'Orientador', 'Administrador', 'Enlace', 'Estadistica', 'Directivo', 'Notificador', 'Delegado','Excepcion', 'Cumplimientos']],
            ['route' => 'reportes_conciliador',       'label' => 'Estadísticas',            'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Conciliador']],
            ['route' => 'misestadisticas',            'label' => 'Estadísticas',            'icon' => 'bi bi-clipboard-data-fill', 'roles' => ['Cumplimientos']],
            ['route' => 'turno_estadistica',          'label' => 'Estadística Turno',       'icon' => 'bi bi-graph-up',            'roles' => []],
            ['route' => 'plantillas_index',           'label' => 'Plantillas',              'icon' => 'bi bi-file-text-fill',      'roles' => ['Super Usuario', 'Administrador', 'Auxiliar', 'Orientador', 'Conciliador', 'Notificador', 'Delegado', 'Cumplimientos', 'Directivo']],
            ['route' => 'solicitudes_pendientes',     'label' => 'Solicitudes Pendientes',  'icon' => 'bi bi-file-earmark-text-fill', 'roles' => []],
            ['route' => 'index_tercer_encuentro',     'label' => 'Tercer Encuentro',        'icon' => 'bi bi-bank',                'roles' => ['Tercer Encuentro']],
            ['route' => 'turnos',                     'label' => 'Turnos',                  'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Turnos', ...$recepcion]],
            // Directivo siempre; el Delegado sólo con el permiso turnos_ver (ve su región).
            ['route' => 'turnos.todos',               'label' => 'Turnos',                  'icon' => 'bi bi-file-person',         'roles' => ['Directivo', 'Delegado'],
                // Quien ya tiene el "Turnos" de recepción (arriba) no lo ve dos veces: desde ahí llega a Todos los turnos.
                'si' => fn () => ! auth()->user()->hasAnyRole(['Super Usuario', 'Turnos', ...$recepcion])
                    && (auth()->user()->hasRole('Directivo') || auth()->user()->can('turnos_ver'))],
            ['route' => 'recepcion.escaner',          'label' => 'Escanear cita',           'icon' => 'bi bi-qr-code-scan',        'roles' => ['Super Usuario', ...$recepcion]],
            // Sólo la recepción Morelia 01 por ahora; la pantalla dice "Próximamente".
            ['route' => 'correspondencia',            'label' => 'Correspondencia',         'icon' => 'bi bi-envelope-paper',      'roles' => [\App\Support\Recepcion::MORELIA_01]],
            //['route' => 'turnos',                     'label' => 'Turnos',                  'icon' => 'bi bi-file-person',         'roles' => ['Super Usuario', 'Administrador','Directivo', 'Delegado']],
            //['route' => 'turnos.listado',             'label' => 'Turnos',                  'icon' => 'bi bi-book',                'roles' => ['Turnos']],
            ['route' => 'roles',                      'label' => 'Roles',                   'icon' => 'bi bi-person-lines-fill',   'roles' => ['Super Usuario']],
            ['route' => 'usuarios',                   'label' => 'Usuarios',                'icon' => 'bi bi-people-fill',         'roles' => ['Super Usuario']],
        ];
    @endphp

    @php
        // Primero se resuelve qué ítems ve y a dónde lleva cada uno.
        $visibles = [];
        foreach ($menu as $item) {
            // Se omite el ítem si el usuario no tiene ninguno de sus roles.
            $visible = $item['roles'] === '*'
                ? true
                : (bool) array_intersect($item['roles'], $userRoles);

            // Quien entra sólo como Auxiliar ve lo de su especialidad.
            if ($visible && $soloAuxiliar && isset($item['especialidad'])) {
                $visible = auth()->user()->can($item['especialidad']);
            }

            // Condición extra opcional ('si' => closure), p. ej. el interruptor del flujo de citas.
            if ($visible && isset($item['si'])) {
                $visible = (bool) ($item['si'])();
            }

            if (! $visible) {
                continue;
            }

            // Si la ruta no existe, se omite en lugar de reventar toda la página.
            $destino = null;
            $activo  = false;
            if (isset($item['url'])) {
                $destino = $item['url'];
            } elseif (isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])) {
                $destino = route($item['route']);
                // 'activo' (opcional): patrón de rutas que también marcan el renglón, p. ej. sus secciones.
                $activo  = request()->routeIs($item['activo'] ?? $item['route']);
            }

            if ($destino) {
                $visibles[] = ['item' => $item, 'destino' => $destino, 'activo' => $activo];
            }
        }

        /*
        | Super Usuario: el menú en grupos desplegables (ve unas veinte
        | opciones). Los demás roles ven pocas y siguen con la lista de siempre.
        | Cada grupo lleva sus rutas en el orden en que se muestran; lo que no
        | esté en ningún grupo (Inicio) queda suelto arriba, y un grupo sin
        | nada visible no se pinta.
        */
        $grupos = [];
        if (in_array('Super Usuario', $userRoles, true)) {
            $definicion = [
                'recepcion'    => ['Recepción',             'bi bi-bell',             ['mi_modulo', 'llegadas.index', 'turnos', 'recepcion.escaner']],
                'tramites'     => ['Trámites',              'bi bi-folder2-open',     ['solicitudes_index', 'index_notificaciones', 'todas_audiencias', 'todas_ratificaciones', 'audiencias.cumplimiento', 'create_asesoria', 'excepcion']],
                'registro'     => ['Registro',              'bi bi-journal-bookmark', ['agenda', 'poderes', 'plantillas_index']],
                'estadisticas' => ['Estadísticas',          'bi bi-bar-chart-line',   ['estadisticas.index', 'seer.estadistica']],
                'sistema'      => ['Sistema',               'bi bi-gear',             ['configuracion', 'usuarios', 'roles']],
            ];

            $sueltos = [];
            foreach ($visibles as $v) {
                $ruta = $v['item']['route'] ?? null;
                $clave = null;
                foreach ($definicion as $k => $d) {
                    if ($ruta !== null && in_array($ruta, $d[2], true)) {
                        $clave = $k;
                        break;
                    }
                }
                if ($clave === null) {
                    $sueltos[] = $v;
                } else {
                    $grupos[$clave]['items'][array_search($ruta, $definicion[$clave][2], true)] = $v;
                }
            }

            $ordenados = [];
            foreach ($definicion as $k => [$etiqueta, $icono]) {
                if (! empty($grupos[$k]['items'])) {
                    ksort($grupos[$k]['items']);
                    $items = array_values($grupos[$k]['items']);
                    $ordenados[$k] = [
                        'etiqueta' => $etiqueta,
                        'icono'    => $icono,
                        'items'    => $items,
                        'activo'   => in_array(true, array_column($items, 'activo'), true),
                    ];
                }
            }
            $grupos   = $ordenados;
            $visibles = $sueltos;
        }
    @endphp

    @foreach ($visibles as $v)
        @include('layouts._menu_item', $v)
    @endforeach

    @foreach ($grupos as $clave => $g)
        {{-- Grupo desplegable (sólo Super Usuario). Abierto si trae la
             pantalla actual o si así lo dejó (localStorage, ver abajo). --}}
        <li class="nav-item side-menus menu-grupo {{ $g['activo'] ? 'is-abierto tiene-activo' : '' }}" data-grupo="{{ $clave }}">
            <a href="#" class="nav-link menu-grupo__boton" role="button"
               aria-expanded="{{ $g['activo'] ? 'true' : 'false' }}" aria-controls="menu-grupo-{{ $clave }}"
               aria-label="{{ $g['etiqueta'] }}">
                <i class="{{ $g['icono'] }}" aria-hidden="true"></i>
                <span class="text-dark">{{ $g['etiqueta'] }}</span>
                <i class="bi bi-chevron-down menu-grupo__flecha" aria-hidden="true"></i>
            </a>
            <ul class="menu-grupo__lista" id="menu-grupo-{{ $clave }}">
                @foreach ($g['items'] as $v)
                    @include('layouts._menu_item', $v)
                @endforeach
            </ul>
        </li>
    @endforeach

@once
    @push('body_end')
        {{--
            Globito con el nombre de la pantalla para el menú colapsado.

            Va con position:fixed y un solo elemento reutilizado, no con un
            ::after en cada ítem, porque .main-sidebar lleva overflow-y:auto: un
            globito posicionado dentro se recortaría en el borde del menú, que
            es justo donde tiene que asomarse.

            No se usa el atributo title nativo por dos razones: tarda cerca de
            un segundo en salir, que es más de lo que alguien espera antes de
            hacer clic a ciegas, y no aparece al navegar con el tabulador. El
            nombre accesible del enlace lo da el aria-label de arriba, así que
            esto es puro apoyo visual y se esconde del lector de pantalla.
        --}}
        <div id="menu-etiqueta" class="menu-etiqueta" role="presentation" aria-hidden="true"></div>

        <style>
            .menu-etiqueta {
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                max-width: 220px;
                padding: 7px 11px;
                border-radius: 7px;
                background: #354647;
                color: #fff;
                font-size: 12.5px;
                font-weight: 600;
                line-height: 1.25;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                box-shadow: 0 6px 18px rgba(46, 60, 61, .22);
                pointer-events: none;
                opacity: 0;
                visibility: hidden;
                transform: translateX(-4px);
            }

            .menu-etiqueta.se-ve {
                opacity: 1;
                visibility: visible;
                transform: translateX(0);
            }

            /* La puntita que lo amarra al icono. */
            .menu-etiqueta::before {
                content: '';
                position: absolute;
                top: 50%;
                left: -5px;
                width: 10px;
                height: 10px;
                margin-top: -5px;
                background: #354647;
                transform: rotate(45deg);
                border-radius: 2px;
            }

            @media (prefers-reduced-motion: no-preference) {
                .menu-etiqueta { transition: opacity .12s ease, transform .12s ease; }
            }
        </style>

        <script>
        (function () {
            var globo = document.getElementById('menu-etiqueta');
            var menu  = document.querySelector('.main-sidebar');
            if (!globo || !menu) { return; }

            // Sólo en escritorio y sólo con el menú angosto. Abajo de 1025px el
            // menú se abre encima y ya enseña los nombres.
            function aplica() {
                return window.innerWidth > 1024 && document.body.classList.contains('sidebar-mini');
            }

            function mostrar(enlace) {
                var texto = enlace.getAttribute('data-etiqueta');
                if (!texto || !aplica()) { return; }

                globo.textContent = texto;
                globo.classList.add('se-ve');

                // Se mide ya con el texto puesto: el alto depende de él.
                var caja = enlace.getBoundingClientRect();
                var alto = globo.offsetHeight;
                var top  = caja.top + (caja.height / 2) - (alto / 2);

                // Sin dejar que se salga por arriba ni por abajo de la ventana.
                top = Math.max(8, Math.min(top, window.innerHeight - alto - 8));

                globo.style.left = (caja.right + 12) + 'px';
                globo.style.top  = top + 'px';
            }

            function esconder() {
                globo.classList.remove('se-ve');
            }

            function enlaceDe(e) {
                return e.target && e.target.closest
                    ? e.target.closest('.sidebar-menu .nav-link:not(.menu-grupo__boton)')
                    : null;
            }

            menu.addEventListener('mouseover', function (e) {
                var enlace = enlaceDe(e);
                // Pasar por el fondo del menú, entre un ítem y otro, también
                // lo esconde: si no, se queda colgado señalando a nada.
                if (enlace) { mostrar(enlace); } else { esconder(); }
            });

            menu.addEventListener('mouseleave', esconder);

            // Con el tabulador también: el title nativo nunca sale con el foco.
            menu.addEventListener('focusin', function (e) {
                var enlace = enlaceDe(e);
                if (enlace) { mostrar(enlace); }
            });
            menu.addEventListener('focusout', esconder);

            // Si el menú se desplaza, la posición ya no corresponde a nada.
            menu.addEventListener('scroll', esconder, { passive: true });
            window.addEventListener('resize', esconder);

            // Al abrir o cerrar el menú el globito deja de tener sentido.
            new MutationObserver(esconder).observe(document.body, {
                attributes: true,
                attributeFilter: ['class']
            });
        })();
        </script>
    @endpush
@endonce

@if ($grupos)
    @push('body_end')
        <style>
            /* Grupos desplegables del menú (Super Usuario). */
            .main-sidebar .sidebar-menu .menu-grupo__lista { list-style: none; margin: 0; padding: 0; display: none; }
            .main-sidebar .sidebar-menu .menu-grupo.is-abierto > .menu-grupo__lista { display: block; }
            .main-sidebar .sidebar-menu .menu-grupo__lista li a { height: 40px; padding-left: 38px; font-size: 13.5px; }
            .main-sidebar .sidebar-menu .menu-grupo__lista li a i { font-size: 15px; }
            .main-sidebar .sidebar-menu .menu-grupo__boton .menu-grupo__flecha {
                margin-left: auto !important; margin-right: 0 !important; width: auto !important;
                font-size: 12px; color: #868e96; transition: transform .2s ease;
            }
            .main-sidebar .sidebar-menu .menu-grupo.is-abierto > .menu-grupo__boton .menu-grupo__flecha { transform: rotate(180deg); }
            /* El grupo que trae la pantalla actual se distingue sin competir con el renglón dorado. */
            .main-sidebar .sidebar-menu .menu-grupo.tiene-activo > .menu-grupo__boton i:first-child,
            .main-sidebar .sidebar-menu .menu-grupo.tiene-activo > .menu-grupo__boton span { color: #496163 !important; font-weight: 700 !important; }

            /* Menú angosto: sólo el ícono del grupo; sus opciones salen en el flotante. */
            body.sidebar-mini .main-sidebar .sidebar-menu .menu-grupo__lista,
            body.sidebar-mini .main-sidebar .sidebar-menu .menu-grupo__flecha { display: none !important; }
            body.sidebar-mini .main-sidebar .sidebar-menu .menu-grupo.tiene-activo > .menu-grupo__boton { background-color: #CEA845 !important; }

            .menu-flotante {
                position: fixed; z-index: 1001; min-width: 220px; max-width: 280px; padding: 6px;
                background: #fff; border: 1px solid #E3E8E8; border-radius: 10px;
                box-shadow: 0 10px 28px rgba(46, 60, 61, .18);
            }
            .menu-flotante__titulo { padding: 6px 10px 8px; font-size: 11.5px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #6B7A7B; }
            .menu-flotante__enlace { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 7px; color: #354647; font-size: 13.5px; font-weight: 600; text-decoration: none; }
            .menu-flotante__enlace span { color: inherit !important; }
            .menu-flotante__enlace:hover, .menu-flotante__enlace:focus { background: #F1F4F4; color: #496163; text-decoration: none; }
            .menu-flotante__enlace.is-activo { background: #CEA845; color: #496163; font-weight: 700; }
            .menu-flotante__enlace i { width: 18px; text-align: center; font-size: 15px; }
        </style>

        <script>
        (function () {
            var menu = document.querySelector('.main-sidebar');
            if (!menu) { return; }
            var grupos = Array.prototype.slice.call(menu.querySelectorAll('.menu-grupo'));

            // Qué grupos dejó abiertos (por navegador). El de la pantalla actual siempre abre.
            var CLAVE = 'siconcilio.menuGrupos', guardados = {};
            try { guardados = JSON.parse(localStorage.getItem(CLAVE) || '{}') || {}; } catch (e) { guardados = {}; }
            function guardar() { try { localStorage.setItem(CLAVE, JSON.stringify(guardados)); } catch (e) {} }

            function poner(li, abierto) {
                li.classList.toggle('is-abierto', abierto);
                li.querySelector('.menu-grupo__boton').setAttribute('aria-expanded', abierto ? 'true' : 'false');
            }
            grupos.forEach(function (li) {
                if (!li.classList.contains('tiene-activo') && guardados[li.getAttribute('data-grupo')]) { poner(li, true); }
            });

            function mini() { return window.innerWidth > 1024 && document.body.classList.contains('sidebar-mini'); }

            // En captura: antes del clic de layouts/app que cierra el menú en móvil
            // al tocar cualquier enlace (abrir un grupo no debe cerrarlo).
            menu.addEventListener('click', function (e) {
                var boton = e.target.closest ? e.target.closest('.menu-grupo__boton') : null;
                if (!boton) { return; }
                e.preventDefault();
                e.stopPropagation();
                var li = boton.closest('.menu-grupo');
                if (mini()) { abrirFlotante(li); return; }
                var abierto = !li.classList.contains('is-abierto');
                poner(li, abierto);
                guardados[li.getAttribute('data-grupo')] = abierto;
                guardar();
            }, true);

            // Menú angosto: las opciones del grupo en un flotante (position:fixed,
            // porque .main-sidebar lleva overflow y lo recortaría).
            var panel = document.createElement('nav');
            panel.className = 'menu-flotante';
            panel.hidden = true;
            document.body.appendChild(panel);
            var actual = null, espera = null;

            function abrirFlotante(li) {
                clearTimeout(espera);
                if (actual === li && !panel.hidden) { return; }
                actual = li;
                var titulo = li.querySelector('.menu-grupo__boton span').textContent;
                panel.setAttribute('aria-label', titulo);
                panel.innerHTML = '<div class="menu-flotante__titulo"></div>';
                panel.firstChild.textContent = titulo;
                li.querySelectorAll('.menu-grupo__lista > li').forEach(function (renglon) {
                    var a = renglon.querySelector('a').cloneNode(true);
                    a.className = 'menu-flotante__enlace' + (renglon.classList.contains('active') ? ' is-activo' : '');
                    a.removeAttribute('id');
                    panel.appendChild(a);
                });
                panel.hidden = false;
                var caja = li.getBoundingClientRect();
                var alto = panel.offsetHeight;
                panel.style.left = (caja.right + 8) + 'px';
                panel.style.top = Math.max(8, Math.min(caja.top, window.innerHeight - alto - 8)) + 'px';
            }
            function ocultar() { panel.hidden = true; actual = null; }
            function cerrarLuego() { clearTimeout(espera); espera = setTimeout(ocultar, 180); }

            grupos.forEach(function (li) {
                li.addEventListener('mouseenter', function () { if (mini()) { abrirFlotante(li); } });
                li.addEventListener('mouseleave', function () { if (mini()) { cerrarLuego(); } });
            });
            panel.addEventListener('mouseenter', function () { clearTimeout(espera); });
            panel.addEventListener('mouseleave', cerrarLuego);
            menu.addEventListener('scroll', ocultar, { passive: true });
            window.addEventListener('resize', ocultar);
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { ocultar(); } });
            document.addEventListener('click', function (e) { if (!panel.hidden && !panel.contains(e.target) && !menu.contains(e.target)) { ocultar(); } });
            // Al abrir o cerrar el menú angosto, el flotante ya no corresponde.
            new MutationObserver(ocultar).observe(document.body, { attributes: true, attributeFilter: ['class'] });
        })();
        </script>
    @endpush
@endif
@endauth
