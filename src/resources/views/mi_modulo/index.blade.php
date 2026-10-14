@extends('layouts.app')
@section('title', 'Mi módulo')

{{--
    "Mi módulo" (MiModuloController), fase 1 del flujo cita → solicitud.
    Sólo consulta: turno actual, el que sigue con cuántos minutos faltan y la
    lista del día. Se actualiza solo cada 20 s sin recargar.
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
            display: inline-flex; align-items: baseline; gap: 6px; margin-top: 10px;
            padding: 6px 12px; border-radius: 10px; background: #EEF2F2; color: var(--tn-verde); font-weight: 700;
        }
        .mm-cuenta strong { font-size: 24px; }
        .mm-cuenta.is-pronto { background: #FBF4E1; color: #7A5D10; }

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
                @php $estado = \App\Support\FlujoCita::estado(); @endphp
                @if ($estado === 'pruebas')
                    <span class="mm-chip mm-chip--pruebas"><i class="bi bi-cone-striped"></i> Modo pruebas</span>
                @endif
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
                            <thead><tr><th>Hora</th><th>Folio</th><th>Persona</th><th>Trámite</th><th>Estatus</th><th class="text-end">Llega en</th></tr></thead>
                            <tbody id="mmLista"></tbody>
                        </table>
                    </div>
                    @if (auth()->user()->hasRole('Super Usuario'))
                        <p class="mm-nota mb-0">Fase 1 (sólo consulta). Siguen: prellenado de la solicitud, aviso con sonido {{ \App\Support\FlujoCita::minutosAviso() }} min antes y botones Llamar / Atender conectados a la pantalla.</p>
                    @endif
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
    var datos = @json($datos);

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function chips(c) {
        return '<span class="tn-estatus tn-estatus--' + esc(c.estatus) + '">' + esc(c.estatus) + '</span>' +
            (c.enLinea ? ' <span class="mm-chip mm-chip--linea">En línea</span>' : '') +
            (c.prueba ? ' <span class="mm-chip mm-chip--prueba">Prueba</span>' : '');
    }
    function faltan(c) {
        if (c.estatus === 'atendido' || c.estatus === 'expirada') { return '—'; }
        if (c.minutos > 0) { return c.minutos + ' min'; }
        if (c.minutos === 0) { return 'ahora'; }
        return 'hace ' + (-c.minutos) + ' min';
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
            (conCuenta && c.minutos >= 0 && c.estatus !== 'atendido'
                ? '<div class="mm-cuenta' + (c.porLlegar ? ' is-pronto' : '') + '">' + (c.minutos === 0 ? '<strong>Ya</strong> es su hora' : 'Llega en <strong>' + c.minutos + '</strong> min') + '</div>'
                : '') +
            '<div class="mm-nombre">' + esc(c.solicitante || 'Sin nombre') + '</div>' +
            '<dl class="mm-datos">' +
                dato('Edad', c.edad) + dato('Municipio', c.municipio) + dato('Teléfono', c.telefono) + dato('Correo', c.correo) +
                dato('Caso', c.caso) + dato('Motivo', c.motivo) + dato('Grupo vulnerable', c.vulnerables) +
            '</dl>';
    }
    function pintar() {
        document.getElementById('mmActual').innerHTML = ficha(datos.actual, 'Ahora', false);
        document.getElementById('mmSiguiente').innerHTML = ficha(datos.siguiente, 'Sigue', true);
        var k = datos.cifras;
        document.getElementById('mmCifras').innerHTML =
            '<span>Total <b>' + k.total + '</b></span><span>Atendidas <b>' + k.atendidas + '</b></span>' +
            '<span>En sala <b>' + k.enSala + '</b></span><span>Por llegar <b>' + k.porLlegar + '</b></span>';
        document.getElementById('mmLista').innerHTML = datos.citas.length
            ? datos.citas.map(function (c) {
                var hecha = c.estatus === 'atendido' || c.estatus === 'expirada';
                return '<tr class="' + (c.esActual ? 'is-actual' : '') + (hecha ? ' is-hecha' : '') + '">' +
                    '<td><b>' + esc(c.hora) + '</b></td><td>' + esc(c.folio) + '</td><td>' + esc(c.solicitante) + '</td>' +
                    '<td>' + esc(c.tipo) + '</td><td>' + chips(c) + '</td>' +
                    '<td class="text-end mm-faltan' + (c.porLlegar ? ' is-pronto' : '') + '">' + faltan(c) + '</td></tr>';
            }).join('')
            : '<tr><td colspan="6" class="mm-vacio text-center">Sin citas hoy.</td></tr>';
        var g = new Date(datos.generado);
        document.getElementById('mmActualizado').textContent = 'Actualizado ' + g.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }
    function actualizar() {
        fetch(URL, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
            .then(function (j) { datos = j; pintar(); })
            .catch(function () { document.getElementById('mmActualizado').textContent = 'Sin conexión, reintentando…'; });
    }
    pintar();
    setInterval(actualizar, 20000);
})();
</script>
@endif
@endsection
