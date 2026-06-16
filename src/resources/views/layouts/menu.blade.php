
<li class="side-menus {{ Request::is('*') ? 'active' : '' }}">
    @auth
        @role('Super Usuario')
            <a class="nav-link" href="{{ route('configuracion') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Administración</span>
            </a>
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('create_asesoria') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Asesorias</span>
            </a>
            <a class="nav-link" href="{{ route('todas_audiencias') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" >Audiencias</span>
            </a>
            <a class="nav-link" href="{{ route('todas_notificaciones') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Busqueda Notificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('subir_doc_masivo') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Carga Masiva</span>
            </a>
            <a class="nav-link" href="{{ route('audiencias.cumplimiento') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" >Cumplimientos</span>
            </a>
            <a class="nav-link" href="{{ route('index_conciliadores') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Conciliadores</span>
            </a>
            <a class="nav-link" href="{{ route('indexDireccionGeneral') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Dirección General</span>
            </a>
            <a class="nav-link" href="{{ route('seer.estadistica') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Estadísticas</span>
            </a>
            <a class="nav-link" href="{{ route('turno_estadistica') }}">
                <i class="bi bi-graph-up"></i><span class="text-dark" onclick="estadistica_turno()">Estadística Turno</span>
            </a>
            <a class="nav-link" href="{{ route('persona.historial') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Historial</span>
            </a>
            <a class="nav-link" href="{{ route('crear_inidencia') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencia Crear</span>
            </a>
            <a class="nav-link" href="{{ route('incidencias.busqueda.index') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencias Consulta</span>
            </a>
            <a class="nav-link" href="{{ route('misturnos') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="turnos()">Mis Turnos</span>
            </a>
            <a class="nav-link" href="{{ route('plantillas_index') }}">
                <i class="bi bi-file-text-fill"></i><span class="text-dark" onclick="estadistica()">Plantillas</span>
            </a>
            <a id="menu-pendiente-firma" class="nav-link" href="{{ route('firma_citatorio') }}">
                <i class="bi bi-bank"></i>
                <span class="text-dark" onclick="mis_citas()">Pendiente de Firma
                    <!--<span id="badge-pendiente-firma" class="menu-badge" style="display:none;">0</span>-->
                </span>
            </a>
            <a class="nav-link" href="{{ route('notificaciones') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Por Notificar</span>
            </a>
            <a class="nav-link" href="{{ route('poderes') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Poderes</span>
            </a>
            <a class="nav-link" href="{{ route('index_ratificacion') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Ratificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('roles') }}">
                <i class="bi bi-person-lines-fill"></i><span class="text-dark" onclick="roles()">Roles</span>
            </a>
            <a class="nav-link" href="{{ route('solicitudes_index') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Solicitudes</span>
            </a>
            <a class="nav-link" href="{{ route('solicitudes_pendientes') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Solicitudes Pendientes</span>
            </a>
            <a class="nav-link" href="{{ route('turnos') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="turnos()">Turnos</span>
            </a>
            <a class="nav-link" href="{{ route('usuarios') }}">
                <i class="bi bi-people-fill"></i><span class="text-dark" onclick="usuarios()">Usuarios</span>
            </a>
        @endrole
    @endauth

    @auth
        @role('Administrador')
            <a class="nav-link" href="{{ route('agenda') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Agenda</span>
            </a>
            <a class="nav-link" href="{{ route('todas_audiencias') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" >Audiencias</span>
            </a>
            <a class="nav-link" href="{{ route('todas_notificaciones') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Busqueda Notificaciones</span>
            </a>
            <a class="nav-link" href="{{ route('seer.estadistica') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Estadísticas</span>
            </a>
            <a class="nav-link" href="{{ route('crear_inidencia') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencia Crear</span>
            </a>
            <a class="nav-link" href="{{ route('incidencias.busqueda.index') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencias Consulta</span>
            </a>
            <a class="nav-link" href="{{ route('plantillas_index') }}">
                <i class="bi bi-file-text-fill"></i><span class="text-dark" onclick="estadistica()">Plantillas</span>
            </a>
            <a class="nav-link" href="{{ route('notificaciones') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="consultar_estadistica()">Por Notificar</span>
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
            <a class="nav-link" href="{{ route('reportes_conciliador') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Estadisticas</span>
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
            <a class="nav-link" href="{{ route('indexDireccionGeneral') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Dirección General</span>
            </a>
            <a class="nav-link" href="{{ route('crear_inidencia') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencia</span>
            </a>
            <a class="nav-link" href="{{ route('poderes') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Poderes</span>
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
            <a class="nav-link" href="{{ route('poderes') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="poderes()">Poderes</span>
            </a>
            <a class="nav-link" href="{{ route('misturnos') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="turnos()">Mis Turnos</span>
            </a>
            <a class="nav-link" href="{{ route('tarjeta_informativa') }}">
                <i class="bi bi-file-person"></i><span class="text-dark" onclick="turnos()">Tarjeta Informativa</span>
            </a>
            <a class="nav-link" href="{{ route('reporte_excepcion') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">Reporte</span>
            </a>
            <a class="nav-link" href="{{ route('seer') }}">
                <i class="bi bi-clipboard-data-fill"></i><span class="text-dark" onclick="estadistica()">SEER</span>
            </a>
            <a class="nav-link" href="{{ route('crear_inidencia') }}">
                <i class="bi bi-bank"></i><span class="text-dark" onclick="mis_citas()">Incidencia</span>
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


