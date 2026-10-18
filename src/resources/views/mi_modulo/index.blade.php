@extends('layouts.app')
@section('title', 'Mi módulo')

{{--
    "Mi módulo" (MiModuloController), flujo cita → solicitud.
    Turno actual, el que sigue con cuántos minutos faltan y la lista del día
    (se actualiza solo cada 20 s sin recargar). En las citas del flujo nuevo,
    los botones Llamar / Iniciar atención / Terminar / No se presentó mueven
    la pantalla de espera de la sede (fase 4).
--}}

@section('page_css')
    @include('turnos._estilos')
    <style>
        .mm-cabeza { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .mm-modulos { display: flex; flex-wrap: wrap; gap: 6px; }
        .mm-modulos a {
            padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff;
            color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none;
        }
        .mm-modulos a.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .mm-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .mm-chip--pruebas { background: #FBF4E1; color: #7A5D10; }
        .mm-chip--activo { background: #E5F3EA; color: #1E7B3C; }
        .mm-chip--prueba { background: #FBF4E1; color: #7A5D10; }
        .mm-chip--linea { background: #EEF2F2; color: var(--tn-verde); }

        .mm-par { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 14px; margin-bottom: 14px; }
        .mm-turno { position: relative; }
        .mm-turno__etiqueta { font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--tn-tenue); margin: 0 0 8px; }
        .mm-folio { line-height: 1; }
        .mm-folio span { display: block; font-size: 16px; font-weight: 700; color: var(--tn-verde-claro); }
        .mm-folio strong { font-size: 52px; font-weight: 800; color: var(--tn-tinta); letter-spacing: -.02em; }
        .mm-meta { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-top: 8px; font-size: 13px; color: var(--tn-suave); }
        .mm-meta b { color: var(--tn-tinta); font-size: 15px; }
        .mm-nombre { font-size: 17px; font-weight: 700; color: var(--tn-tinta); margin-top: 12px; }
        .mm-datos { display: grid; grid-template-columns: 110px 1fr; gap: 3px 10px; margin: 10px 0 0; font-size: 13px; }
        .mm-datos dt { color: var(--tn-tenue); font-weight: 600; }
        .mm-datos dd { margin: 0; color: var(--tn-tinta); overflow-wrap: anywhere; }
        .mm-vacio { color: var(--tn-tenue); font-size: 15px; padding: 18px 0; }
        .mm-cuenta {
            display: inline-flex; flex-wrap: wrap; align-items: baseline; gap: 4px 6px; margin-top: 10px;
            padding: 6px 12px; border-radius: 10px; background: #EEF2F2; color: var(--tn-verde); font-weight: 700;
        }
        .mm-cuenta strong { font-size: 24px; }
        .mm-cuenta.is-pronto { background: #FBF4E1; color: #7A5D10; }
        .mm-cuenta.is-excedido { background: #FDECEA; color: #9A2B1C; }

        .mm-cifras { display: flex; flex-wrap: wrap; gap: 18px; font-size: 13px; color: var(--tn-suave); }
        .mm-cifras b { color: var(--tn-tinta); font-size: 16px; }

        .mm-tabla { width: 100%; font-size: 13.5px; }
        .mm-tabla th { font-size: 11.5px; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); font-weight: 700; padding: 8px 10px; border-bottom: 1px solid var(--tn-borde); }
        .mm-tabla td { padding: 9px 10px; border-bottom: 1px solid #EDF1F1; vertical-align: middle; }
        .mm-tabla tr.is-actual td { background: #F2F7F7; }
        .mm-tabla tr.is-hecha td { color: var(--tn-tenue); }
        .mm-faltan { font-weight: 700; white-space: nowrap; }
        .mm-faltan.is-pronto { color: #7A5D10; }
        .mm-nota { font-size: 12px; color: var(--tn-tenue); margin-top: 10px; }
        .mm-iniciar { margin-top: 14px; }
        .mm-iniciar .btn { font-weight: 700; }
        .mm-acciones { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .mm-acciones .btn { font-weight: 700; }
        .mm-acciones form { margin: 0; }
        .mm-acciones--fila { margin: 0; gap: 5px; justify-content: flex-end; flex-wrap: nowrap; }
        .mm-acciones--fila .btn { padding: 2px 9px; font-size: 12px; white-space: nowrap; }
        .mm-chip--llamado { background: #E7EEF9; color: #2A4F8A; }
        .mm-chip--atencion { background: #E5F3EA; color: #1E7B3C; }
        .mm-mensaje { font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 10px; margin-bottom: 12px; background: #E5F3EA; color: #1E7B3C; }
        .mm-mensaje.is-error { background: #FBE9E7; color: #9A2B1C; }
        @media (max-width: 900px) { .mm-par { grid-template-columns: 1fr; } }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mm-cabeza">
            <div>
                <h3 class="page__heading mb-0">Mi módulo</h3>
                <span class="small" style="color: var(--tn-tenue);" id="mmSub">
                    @if ($modulo) {{ preg_replace('/^m[oó]dulo\b/i', 'Módulo', $modulo->nombre) }} · {{ $modulo->delegacion }} @endif
                </span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="mmPermiso" hidden>
                    <i class="bi bi-bell"></i> Activar avisos del navegador
                </button>
                <span class="small" style="color: var(--tn-tenue);" id="mmActualizado"></span>
            </div>
        </div>

        <div class="section-body">
            @if ($opciones->count() > 1)
                <div class="mm-modulos mb-3">
                    @foreach ($opciones as $op)
                        <a href="{{ route('mi_modulo', ['modulo' => $op->id]) }}" class="{{ $modulo && $op->id === $modulo->id ? 'is-activo' : '' }}">
                            {{ $op->delegacion }} · {{ preg_replace('/^m[oó]dulo\b/i', 'Módulo', $op->nombre) }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if (! $modulo)
                <div class="tn-tarjeta mm-vacio">No tienes un módulo activo asignado. Pídelo en Administración → Módulos.</div>
            @else
                <div class="mm-mensaje" id="mmMensaje" role="status" hidden></div>
                <div class="mm-par">
                    <div class="tn-tarjeta mm-turno" id="mmActual"></div>
                    <div class="tn-tarjeta mm-turno" id="mmSiguiente"></div>
                </div>

                <div class="tn-tarjeta">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <p class="tn-titulo-seccion mb-0">Hoy</p>
                        <div class="mm-cifras" id="mmCifras"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="mm-tabla">
                            <thead><tr><th>Hora</th><th>Folio</th><th>Persona</th><th>Trámite</th><th>Estatus</th><th class="text-end">Llega en</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody id="mmLista"></tbody>
                        </table>
                    </div>
                    <p class="mm-nota mb-0" id="mmNota">
                        Cada cita dura <b>{{ $datos['duracion'] }} min</b> (Administración → Horarios de citas · Solicitud y asesoría).
                        Sigue quien ya llegó y confirmó su cita; te avisamos {{ $datos['minutosAviso'] }} min antes de que le toque, aunque estés capturando una solicitud.
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
@if ($modulo)
<script>
(function () {
    var URL = @json($urlDatos);
    var URL_PRELLENAR = @json(route('mi_modulo.prellenar', ['id' => '__ID__']));
    var URL_ACCION = @json(route('mi_modulo.accion', ['id' => '__ID__', 'accion' => '__ACCION__']));
    var TOKEN = @json(csrf_token());
    var datos = @json($datos);

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function chips(c) {
        return '<span class="tn-estatus tn-estatus--' + esc(c.estatus) + '">' + esc(c.estatus) + '</span>' +
            (c.enLinea ? ' <span class="mm-chip mm-chip--linea">En línea</span>' : '') +
            (c.prueba ? ' <span class="mm-chip mm-chip--prueba">Prueba</span>' : '') +
            (vigente(c) && c.enAtencion ? ' <span class="mm-chip mm-chip--atencion">En atención</span>'
                : (vigente(c) && c.llamado ? ' <span class="mm-chip mm-chip--llamado">Llamado ' + esc(c.llamado) + '</span>' : ''));
    }
    function vigente(c) { return c.estatus === 'pendiente' || c.estatus === 'confirmada'; }
    function faltan(c) {
        if (c.estatus === 'atendido' || c.estatus === 'expirada') { return '—'; }
        if (c.minutos > 0) { return c.minutos + ' min'; }
        if (c.minutos === 0) { return 'ahora'; }
        return 'hace ' + (-c.minutos) + ' min';
    }
    // Fase 2: abre la captura de siempre con los datos de la cita.
    function iniciar(c) {
        if (!c.flujoNuevo || c.tipo !== 'Solicitud' || !vigente(c)) { return ''; }
        return '<form method="POST" action="' + esc(URL_PRELLENAR.replace('__ID__', c.id)) + '">' +
            '<input type="hidden" name="_token" value="' + esc(TOKEN) + '">' +
            '<button type="submit" class="btn tn-boton-dorado"><i class="bi bi-magic me-1"></i> Preparar solicitud con estos datos</button></form>';
    }

    // Fase 4: botones según dónde va la cita. Sólo citas del flujo nuevo.
    function boton(c, accion, texto, icono, clase, bloqueo) {
        return '<button type="button" class="btn ' + clase + '" data-accion="' + accion + '" data-id="' + c.id + '" data-folio="' + esc(c.folio) + '"' +
            (bloqueo ? ' disabled title="' + esc(bloqueo) + '"' : '') + '>' +
            '<i class="bi bi-' + icono + ' me-1"></i>' + texto + '</button>';
    }
    function acciones(c, enFila) {
        if (!c.flujoNuevo || !vigente(c)) { return ''; }
        var b = [];
        if (c.enAtencion) {
            if (!enFila) { b.push(iniciar(c)); }
            b.push(boton(c, 'terminar', 'Terminar', 'check2-circle', 'btn-success'));
        } else if (c.llamado) {
            if (!enFila) { b.push(iniciar(c)); }
            b.push(boton(c, 'atender', 'Iniciar atención', 'person-check', enFila ? 'btn-outline-success' : 'btn-success'));
            b.push(boton(c, 'llamar', 'Volver a llamar', 'megaphone', 'btn-outline-secondary'));
            b.push(boton(c, 'ausente', 'No se presentó', 'person-x', 'btn-outline-danger'));
        } else {
            // El módulo atiende de una en una (el servidor también lo cuida).
            var abierta = (datos.citas || []).filter(function (x) { return x.id !== c.id && x.flujoNuevo && vigente(x) && x.llamado; })[0];
            b.push(boton(c, 'llamar', 'Llamar', 'megaphone', enFila ? 'btn-outline-primary' : 'btn-primary',
                abierta ? 'Primero termina o marca como no presentado el folio ' + abierta.folio : null));
            if (!enFila && !abierta) { b.push(iniciar(c)); }
            b.push(boton(c, 'ausente', 'No se presentó', 'person-x', 'btn-outline-danger'));
        }
        return '<div class="mm-acciones' + (enFila ? ' mm-acciones--fila' : '') + '">' + b.join('') + '</div>';
    }
    // Cuándo termina el turno de ahora y cuándo empieza el que sigue (Horarios de citas).
    function tiempo(c, etiqueta) {
        var t = datos.tiempos || {};
        if (etiqueta === 'Ahora' && t.actual) {
            return t.actual.excedido > 0
                ? '<div class="mm-cuenta is-excedido">Lleva <strong>' + t.actual.lleva + '</strong> min · <b>' + t.actual.excedido + ' min</b> más de lo planeado (' + t.actual.duracion + ' min)</div>'
                : '<div class="mm-cuenta">Desde ' + esc(t.actual.desde) + ' · termina aprox. <strong>' + esc(t.actual.termina) + '</strong> (' + t.actual.duracion + ' min)</div>';
        }
        if (etiqueta === 'Sigue' && t.siguiente) {
            var pronto = t.siguiente.minutos <= datos.minutosAviso;
            return '<div class="mm-cuenta' + (pronto ? ' is-pronto' : '') + '">' +
                (t.siguiente.minutos > 0 ? 'Empieza aprox. <strong>' + esc(t.siguiente.empieza) + '</strong> · en <strong>' + t.siguiente.minutos + '</strong> min' : '<strong>Ya</strong> le toca') +
                ' · ' + (c.estatus === 'confirmada' ? '<b>ya llegó y confirmó</b>' : 'aún no confirma su llegada') + '</div>';
        }
        return '';
    }
    function ficha(c, etiqueta, conCuenta) {
        if (!c) {
            return '<p class="mm-turno__etiqueta">' + etiqueta + '</p><div class="mm-vacio">' +
                (etiqueta === 'Ahora' ? 'Sin turno en este momento.' : 'No hay más citas por ahora.') + '</div>';
        }
        var dato = function (k, v) { return v ? '<dt>' + k + '</dt><dd>' + esc(v) + '</dd>' : ''; };
        return '<p class="mm-turno__etiqueta">' + etiqueta + '</p>' +
            '<div class="mm-folio"><span>' + esc(c.prefijo) + '</span><strong>' + esc(c.numero) + '</strong></div>' +
            '<div class="mm-meta"><b>' + esc(c.hora) + '</b> · ' + esc(c.tipo) + ' ' + chips(c) + '</div>' +
            tiempo(c, etiqueta) +
            '<div class="mm-nombre">' + esc(c.solicitante || 'Sin nombre') + '</div>' +
            '<dl class="mm-datos">' +
                dato('Edad', c.edad) + dato('Municipio', c.municipio) + dato('Teléfono', c.telefono) + dato('Correo', c.correo) +
                dato('Caso', c.caso) + dato('Motivo', c.motivo) + dato('Grupo vulnerable', c.vulnerables) +
            '</dl>' + (c.flujoNuevo ? acciones(c, false) : iniciar(c));
    }
    function pintar() {
        document.getElementById('mmActual').innerHTML = ficha(datos.actual, 'Ahora', false);
        document.getElementById('mmSiguiente').innerHTML = ficha(datos.siguiente, 'Sigue', true);
        var k = datos.cifras;
        document.getElementById('mmCifras').innerHTML =
            '<span>Total <b>' + k.total + '</b></span><span>Atendidas <b>' + k.atendidas + '</b></span>' +
            '<span>En sala <b>' + k.enSala + '</b></span><span>Por llegar <b>' + k.porLlegar + '</b></span>' +
            (k.esperaProm != null ? '<span title="De su hora a que se le llamó">Espera prom. <b>' + k.esperaProm + '</b> min</span>' : '') +
            (k.atencionProm != null ? '<span title="De iniciar atención a terminar">Atención prom. <b>' + k.atencionProm + '</b> min</span>' : '');
        document.getElementById('mmLista').innerHTML = datos.citas.length
            ? datos.citas.map(function (c) {
                var hecha = c.estatus === 'atendido' || c.estatus === 'expirada';
                return '<tr class="' + (c.esActual ? 'is-actual' : '') + (hecha ? ' is-hecha' : '') + '">' +
                    '<td><b>' + esc(c.hora) + '</b></td><td>' + esc(c.folio) + '</td><td>' + esc(c.solicitante) + '</td>' +
                    '<td>' + esc(c.tipo) + '</td><td>' + chips(c) + '</td>' +
                    '<td class="text-end mm-faltan' + (c.porLlegar ? ' is-pronto' : '') + '">' + faltan(c) + '</td>' +
                    '<td class="text-end">' + (c.esActual ? '' : acciones(c, true)) + '</td></tr>';
            }).join('')
            : '<tr><td colspan="7" class="mm-vacio text-center">Sin citas hoy.</td></tr>';
        var g = new Date(datos.generado);
        document.getElementById('mmActualizado').textContent = 'Actualizado ' + g.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }
    function actualizar() {
        fetch(URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
            .then(function (j) { datos = j; pintar(); })
            .catch(function () { document.getElementById('mmActualizado').textContent = 'Sin conexión, reintentando…'; });
    }
    function mensaje(texto, error) {
        var m = document.getElementById('mmMensaje');
        m.textContent = texto;
        m.classList.toggle('is-error', !!error);
        m.hidden = false;
        clearTimeout(mensaje.t);
        mensaje.t = setTimeout(function () { m.hidden = true; }, error ? 9000 : 5000);
    }
    function confirmar(titulo, texto, si) {
        if (typeof swal === 'function') {
            swal({ title: titulo, text: texto, type: 'warning', showCancelButton: true,
                   confirmButtonText: 'Sí', cancelButtonText: 'Cancelar' }, function (ok) { if (ok) { si(); } });
        } else if (window.confirm(titulo + '\n' + texto)) {
            si();
        }
    }
    function ejecutar(boton) {
        var accion = boton.getAttribute('data-accion');
        boton.disabled = true;
        fetch(URL_ACCION.replace('__ID__', boton.getAttribute('data-id')).replace('__ACCION__', accion), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j, status: r.status }; }); })
            .then(function (res) {
                if (res.ok) { mensaje(res.j.mensaje || 'Listo.'); }
                else if (res.status === 419) { mensaje('La sesión expiró; recarga la página.', true); }
                else { mensaje(res.j.message || 'No se pudo completar la acción.', true); }
                actualizar();
            })
            .catch(function () { mensaje('Sin conexión; intenta de nuevo.', true); boton.disabled = false; });
    }
    // Un solo escuchador: los botones se repintan en cada actualización.
    document.querySelector('.tn .section-body').addEventListener('click', function (e) {
        var boton = e.target.closest('button[data-accion]');
        if (!boton || boton.disabled) { return; }
        var folio = boton.getAttribute('data-folio');
        var accion = boton.getAttribute('data-accion');
        if (accion === 'ausente') {
            confirmar('¿No se presentó?', 'El folio ' + folio + ' quedará como expirado y saldrá de la pantalla.', function () { ejecutar(boton); });
        } else if (accion === 'terminar') {
            confirmar('¿Terminar la atención?', 'El folio ' + folio + ' quedará como atendido.', function () { ejecutar(boton); });
        } else {
            ejecutar(boton);
        }
    });

    pintar();
    setInterval(actualizar, 20000);

    // Fase 3: las notificaciones del sistema operativo piden permiso con un clic.
    var permiso = document.getElementById('mmPermiso');
    if ('Notification' in window && Notification.permission === 'default') {
        permiso.hidden = false;
        permiso.addEventListener('click', function () {
            Notification.requestPermission().then(function () { permiso.hidden = true; });
        });
    }
})();
</script>
@endif
@endsection
