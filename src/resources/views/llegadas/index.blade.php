@extends('layouts.app')
@section('title', 'Llegadas hoy')

{{--
    Llegadas hoy (LlegadasHoyController / App\Support\LlegadasHoy).
    Audiencias, ratificaciones y cumplimientos del día con quién ya llegó.
    La recepción marca a cada persona y el conciliador asignado recibe un
    aviso con sonido; el conciliador ve esta misma pantalla sólo para
    consultar. Las ratificaciones cuya cita ya se validó con QR aparecen
    marcadas solas. Lo abierto cuya hora ya pasó y le falta alguien sale en
    rojo ("Se pasó la hora"); lo que ya se cerró hoy sale en gris con su
    estatus y sin poder marcar. Se actualiza cada 30 s.

    No sustituye la comparecencia, que el conciliador registra con
    identificación dentro de la audiencia o la ratificación.

    Observaciones: la recepción anota en cada audiencia si la persona viene en
    silla de ruedas, ocupa traductor o es menor de edad, más un texto libre;
    en las ratificaciones, sólo el texto. El conciliador las ve aquí y en su
    aviso (LlegadasHoy::guardarObservaciones).
--}}

@section('page_css')
    @include('turnos._estilos')
    <style>
        .ll-cabeza { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
        .ll-cabeza p { margin: 2px 0 0; font-size: 13px; color: var(--tn-tenue); }
        .ll-consulta { display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; border-radius: 10px; background: #EEF4F8; color: #284B63; font-size: 13px; margin-bottom: 14px; }
        .ll-consulta .bi { font-size: 16px; }

        .ll-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; border-bottom: 1px solid var(--tn-borde); }
        .ll-tab { border: 0; background: none; padding: 9px 14px 10px; font-size: 14px; font-weight: 700; color: var(--tn-tenue); border-bottom: 3px solid transparent; margin-bottom: -1px; cursor: pointer; display: inline-flex; gap: 8px; align-items: center; }
        .ll-tab b { min-width: 24px; padding: 1px 8px; border-radius: 999px; background: var(--tn-fondo); font-size: 12px; color: var(--tn-suave); text-align: center; }
        .ll-tab.is-activo { color: var(--tn-verde); border-bottom-color: var(--tn-dorado); }
        .ll-tab.is-activo b { background: var(--tn-verde); color: #fff; }

        .ll-barra { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 14px; }
        .ll-barra .form-control { max-width: 360px; }
        .ll-filtros { display: flex; flex-wrap: wrap; gap: 6px; }
        .ll-filtro { display: inline-flex; align-items: center; gap: 7px; padding: 6px 13px; border-radius: 10px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-tinta); font-size: 13px; font-weight: 600; cursor: pointer; }
        .ll-filtro b { min-width: 22px; padding: 0 7px; border-radius: 999px; background: var(--tn-fondo); font-size: 12px; text-align: center; }
        .ll-filtro.is-activo { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); }
        .ll-filtro--faltan b { background: #FBF4E1; color: #7A5D10; }
        .ll-filtro--completas b { background: #E5F3EA; color: #1E7B3C; }
        .ll-filtro--tarde b { background: #FDECEA; color: #B42318; }
        .ll-filtro--cerradas b { background: #E7ECEC; color: #354647; }
        .ll-mias { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--tn-tinta); margin-left: auto; }

        .ll-lista { display: grid; gap: 10px; }
        .ll-item { display: grid; grid-template-columns: 96px minmax(0, 1fr); gap: 16px; background: #fff; border: 1px solid var(--tn-borde); border-left: 5px solid #D5DEDE; border-radius: 12px; padding: 14px 16px; }
        .ll-item.is-parcial { border-left-color: #CEA845; }
        .ll-item.is-completa { border-left-color: #2F9E5B; }
        .ll-item.is-mia { box-shadow: 0 0 0 2px rgba(73, 97, 99, .18); }
        .ll-item.is-tarde { border-left-color: #D92D20; background: #FFFBFA; }
        .ll-item.is-cerrada { border-left-color: #B8C4C4; background: #F8FAFA; }
        .ll-item.is-cerrada .ll-hora, .ll-item.is-cerrada .ll-nombre, .ll-item.is-cerrada .ll-nue { color: #6B7A7B; }
        .ll-tarde { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 999px; background: #FDECEA; color: #B42318; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .ll-cerrada { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 999px; background: #E7ECEC; color: #354647; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .ll-nollego { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px; background: #F3F5F5; color: #8A9899; font-weight: 600; font-size: 12.5px; white-space: nowrap; }
        .ll-hora { font-size: 24px; font-weight: 800; color: var(--tn-tinta); line-height: 1; font-variant-numeric: tabular-nums; }
        .ll-lugar { margin-top: 6px; display: inline-block; padding: 2px 9px; border-radius: 999px; background: #EEF2F2; color: var(--tn-verde); font-size: 11.5px; font-weight: 700; }
        .ll-cab { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 6px 12px; align-items: baseline; margin-bottom: 6px; }
        .ll-tipo { display: inline-block; padding: 1px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; margin-right: 6px; vertical-align: 1px; }
        .ll-tipo--audiencia { background: #E3EEF9; color: #1F4E79; }
        .ll-tipo--ratificacion { background: #EFE7F6; color: #5B3A82; }
        .ll-tipo--cumplimiento { background: #FBF4E1; color: #7A5D10; }
        .ll-nue { font-weight: 800; color: var(--tn-tinta); font-variant-numeric: tabular-nums; }
        .ll-conc { font-size: 12.5px; color: var(--tn-tenue); }
        .ll-estado { font-size: 12px; font-weight: 700; color: var(--tn-tenue); }
        .ll-item.is-completa .ll-estado { color: #1E7B3C; }
        .ll-item.is-parcial .ll-estado { color: #7A5D10; }
        .ll-obs { margin: 6px 0 8px; padding: 8px 10px; border-radius: 10px; background: #FFF8E6; border: 1px solid #F0DFB0; font-size: 12.5px; }
        .ll-obs--vacia { background: none; border: 0; padding: 0; margin: 2px 0 6px; }
        .ll-obs--editar { background: #fff; border-color: var(--tn-borde); display: grid; gap: 6px; }
        .ll-obs__chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 4px; }
        .ll-obs__chip { background: #FBE3E3; color: #8A1717; border-radius: 999px; padding: 2px 9px; font-weight: 700; font-size: 12px; }
        .ll-obs__texto { color: var(--tn-tinta); white-space: pre-line; }
        .ll-obs__pie { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: 2px; }
        .ll-obs__meta { color: var(--tn-tenue); font-size: 11.5px; }
        .ll-obs__editar { border: 0; background: none; color: var(--tn-verde); font-weight: 700; font-size: 12.5px; padding: 0; margin-left: auto; cursor: pointer; }
        .ll-obs__casillas { display: flex; flex-wrap: wrap; gap: 6px 16px; }
        .ll-obs__casilla { display: inline-flex; gap: 6px; align-items: center; font-weight: 600; margin: 0; }
        .ll-obs__botones { display: flex; gap: 6px; justify-content: flex-end; }
        .ll-obs__autor { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #7A5D10; margin-bottom: 4px; }
        .ll-obs--conciliador { background: #EEF1FB; border-color: #CCD6F0; }
        .ll-obs--conciliador .ll-obs__autor { color: #2B4C7E; }
        .ll-obs--conciliador .ll-obs__chip { background: #DCE4F7; color: #23407A; }
        .ll-obs--editar.ll-obs--conciliador { background: #fff; border-color: #CCD6F0; }
        .ll-cita { font-size: 12px; color: var(--tn-suave); margin-bottom: 4px; }
        .ll-reag-chip { display: inline-flex; align-items: center; gap: 4px; padding: 1px 8px; border-radius: 999px; background: #E8EEF8; color: #2B4C7E; font-size: 11.5px; font-weight: 800; margin-left: 6px; vertical-align: 1px; }
        .ll-reag { margin: 2px 0 6px; font-size: 12.5px; color: #2B4C7E; }
        .ll-reag summary { cursor: pointer; font-weight: 700; list-style: none; display: inline-flex; align-items: center; gap: 6px; }
        .ll-reag summary::-webkit-details-marker { display: none; }
        .ll-reag summary::after { content: '\F282'; font-family: 'bootstrap-icons'; font-size: 11px; transition: transform .15s; }
        .ll-reag[open] summary::after { transform: rotate(180deg); }
        .ll-reag ol { margin: 6px 0 2px; padding: 8px 10px 8px 28px; border-radius: 10px; background: #F3F6FB; border: 1px solid #DCE5F2; color: var(--tn-tinta); }
        .ll-reag li + li { margin-top: 6px; }
        .ll-reag__cab { font-weight: 700; }
        .ll-reag__nadie { color: var(--tn-tenue); }
        .ll-ok--heredada { background: #E8EEF8; color: #2B4C7E; }
        .ll-cita .bi { color: var(--tn-verde); }

        .ll-parte { display: grid; grid-template-columns: 128px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 7px 0; border-top: 1px dashed #EDF1F1; }
        .ll-parte:first-of-type { border-top: 0; }
        .ll-rol { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--tn-tenue); }
        .ll-nombre { font-size: 14px; font-weight: 600; color: var(--tn-tinta); overflow-wrap: anywhere; }
        .ll-accion { display: flex; align-items: center; gap: 8px; justify-content: flex-end; }
        .btn.ll-llego { border: 1px solid #2F9E5B; color: #1E7B3C; background: #fff; font-weight: 700; font-size: 13px; padding: 5px 14px; border-radius: 9px; white-space: nowrap; }
        .btn.ll-llego:not(.btn-social):not(.btn-social-icon):hover { background: #E5F3EA; color: #1E7B3C; border-color: #2F9E5B !important; }
        .ll-ok { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px; background: #E5F3EA; color: #1E7B3C; font-weight: 700; font-size: 13px; white-space: nowrap; }
        .ll-espera { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px; background: #F3F5F5; color: var(--tn-tenue); font-weight: 600; font-size: 12.5px; white-space: nowrap; }
        .ll-deshacer { border: 0; background: none; color: var(--tn-tenue); font-size: 12px; text-decoration: underline; cursor: pointer; padding: 0; }
        .ll-vacio { text-align: center; color: var(--tn-tenue); padding: 40px 12px; background: #fff; border: 1px dashed var(--tn-borde); border-radius: 12px; }
        .ll-mensaje { font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 10px; margin-bottom: 12px; background: #FBE9E7; color: #9A2B1C; }

        /* Ratificaciones: botón y ventana del poder de la empresa (sólo lectura). */
        .ll-poder-btn { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; border: 1px solid #D5DEDE; background: #fff; color: var(--tn-verde); font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; }
        .ll-poder-btn:hover, .ll-poder-btn:focus { border-color: var(--tn-verde); background: #F1F4F4; }
        .ll-poder-btn.is-sin { color: #9A6B00; border-color: #F0DFA8; background: #FFFBEF; }
        .pd-cab { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin-bottom: 12px; }
        .pd-chip { display: inline-flex; align-items: center; gap: 5px; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #EEF2F2; color: #354647; }
        .pd-chip.is-ok { background: #E5F3EA; color: #1E7B3C; }
        .pd-chip.is-aviso { background: #FBF4E1; color: #7A5D10; }
        .pd-chip.is-mal { background: #FDECEA; color: #B42318; }
        .pd-seccion { border: 1px solid var(--tn-borde); border-radius: 10px; padding: 10px 12px; margin-bottom: 10px; }
        .pd-seccion h6 { margin: 0 0 6px; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); }
        .pd-seccion.is-quien { border-color: #BFE0C9; background: #F6FBF8; }
        .pd-dato { display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 2px 10px; font-size: 13px; padding: 2px 0; }
        .pd-dato span:first-child { color: var(--tn-tenue); }
        .pd-dato span:last-child { color: var(--tn-tinta); font-weight: 600; overflow-wrap: anywhere; }
        .pd-nombre { font-size: 16px; font-weight: 800; color: var(--tn-tinta); margin-bottom: 4px; }
        .pd-docs { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; }
        .pd-docs li { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; padding: 4px 0; border-top: 1px dashed #EDF1F1; }
        .pd-docs li:first-child { border-top: 0; }
        .pd-docs .is-si { color: #1E7B3C; font-weight: 700; white-space: nowrap; }
        .pd-docs .is-no { color: #B42318; font-weight: 700; white-space: nowrap; }
        .pd-vacio { text-align: center; padding: 22px 10px; color: var(--tn-tenue); }
        .pd-vacio .bi { font-size: 30px; color: #CEA845; display: block; margin-bottom: 6px; }
        @media (max-width: 520px) { .pd-dato { grid-template-columns: 1fr; } }

        @media (max-width: 720px) {
            .ll-item { grid-template-columns: 1fr; gap: 8px; }
            .ll-parte { grid-template-columns: 1fr auto; }
            .ll-rol { grid-column: 1 / -1; }
            .ll-ok__extra { display: none; }
            .ll-mias { margin-left: 0; }
        }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header ll-cabeza">
            <div>
                <h3 class="page__heading mb-0">Llegadas hoy</h3>
                <p>
                    {{ \Illuminate\Support\Str::ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM')) }} ·
                    @if ($marca)
                        Marca a cada persona en cuanto llegue; su conciliador recibe el aviso.
                    @else
                        Quién ya llegó a las audiencias, ratificaciones y cumplimientos de hoy.
                    @endif
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if (count($sedes) > 1)
                    <form method="GET" action="{{ route('llegadas.index') }}">
                        @if ($tipo !== 'todas')<input type="hidden" name="tipo" value="{{ $tipo }}">@endif
                        <select name="sede" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Sede">
                            @foreach ($sedes as $s)
                                <option value="{{ $s }}" @selected($s === $sede)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                <span class="small" style="color: var(--tn-tenue);" id="llActualizado"></span>
            </div>
        </div>

        <div class="section-body">
            @unless ($marca)
                <div class="ll-consulta">
                    <i class="bi bi-eye"></i>
                    <div>
                        La recepción marca las llegadas; cuando llegue alguien de lo tuyo te avisamos con sonido, estés en la pantalla que estés.
                        @if ($autorObs === 'conciliador')
                            En tus audiencias puedes anotar tus propias observaciones; se ven aparte de las de la recepción.
                        @endif
                    </div>
                </div>
            @endunless

            <div class="ll-mensaje" id="llMensaje" role="alert" hidden></div>

            <div class="ll-tabs" id="llTabs" role="tablist"></div>

            <div class="ll-barra">
                <input type="search" class="form-control" id="llBuscar" placeholder="Buscar por nombre, NUE, sala o conciliador" autocomplete="off">
                <div class="ll-filtros" id="llFiltros"></div>
                @unless ($marca)
                    <label class="ll-mias form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="llMias" @checked($soloMias)>
                        <span>Sólo las mías</span>
                    </label>
                @endunless
            </div>

            <div class="ll-lista" id="llLista"></div>
        </div>
    </section>

    {{-- Poder de la empresa de una ratificación (sólo lectura; sin los archivos). --}}
    <div class="modal fade" id="llPoderModal" tabindex="-1" aria-labelledby="llPoderTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="llPoderTitulo">Poder de la empresa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="llPoderCuerpo"></div>
                <div class="modal-footer">
                    <span class="me-auto small" style="color: var(--tn-tenue);"><i class="bi bi-eye"></i> Sólo consulta</span>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    var URL_DATOS = @json($urlDatos);
    var URL_MARCAR = @json($marca ? route('llegadas.marcar', ['tipo' => '__T__', 'id' => '__ID__']) : null);
    var URL_PODER = @json(route('llegadas.poder', ['id' => '__ID__']));
    var TOKEN = @json(csrf_token());
    var URL_OBS = {!! json_encode($autorObs ? route('llegadas.observaciones', ['tipo' => '__T__', 'id' => '__ID__']) : null) !!};
    // Como quién anoto observaciones: 'recepcion', 'conciliador' o null.
    var MI_AUTOR = @json($autorObs);
    var AUTORES = @json(\App\Support\LlegadasHoy::AUTORES);
    var OBS = @json(\App\Support\LlegadasHoy::OBSERVACIONES);
    var OBSERVAN = @json(\App\Support\LlegadasHoy::OBSERVAN);
    // Observaciones que se están editando (sobreviven a la actualización cada 30 s).
    var editando = null, borrador = null;
    var MARCA = @json($marca);
    var YO = @json($yo);
    var TIPOS = @json($tipos);
    var CORTO = @json(\App\Support\LlegadasHoy::SINGULAR);
    var datos = @json($datos);
    var tab = @json($tipo);
    var filtro = 'todas';
    var texto = '';
    var mias = @json($soloMias && ! $marca);
    // Lo mío: soy su conciliador o uno de sus auxiliares.
    function esMia(r) { return r.conciliador_id === YO || (r.responsables || []).indexOf(YO) !== -1; }

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function tiempo(min) {
        if (min < 60) { return min + ' min'; }
        var h = Math.floor(min / 60), m = min % 60;
        return h + ' h' + (m ? ' ' + m + ' min' : '');
    }
    function sinAcentos(t) { return String(t || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); }

    // Lo que se ve antes de la pestaña y el filtro de llegada.
    function base() {
        return (datos.registros || []).filter(function (r) {
            if (mias && !esMia(r)) { return false; }
            if (!texto) { return true; }
            var donde = sinAcentos(r.nue + ' ' + r.lugar + ' ' + r.conciliador + ' ' + r.partes.map(function (p) { return p.nombre; }).join(' '));
            return donde.indexOf(texto) !== -1;
        });
    }

    function pintarTabs(lista) {
        var op = [['todas', 'Todas']].concat(Object.keys(TIPOS).map(function (k) { return [k, TIPOS[k]]; }));
        document.getElementById('llTabs').innerHTML = op.map(function (o) {
            var n = o[0] === 'todas' ? lista.length : lista.filter(function (r) { return r.tipo === o[0]; }).length;
            return '<button type="button" role="tab" class="ll-tab' + (tab === o[0] ? ' is-activo' : '') + '" data-ll-tab="' + o[0] + '" aria-selected="' + (tab === o[0]) + '">' + o[1] + ' <b>' + n + '</b></button>';
        }).join('');
    }

    var FILTROS = {
        todas: function () { return true; },
        faltan: function (r) { return r.abierto && !r.completa; },
        tarde: function (r) { return r.atraso !== null; },
        completas: function (r) { return r.completa; },
        cerradas: function (r) { return !r.abierto; }
    };

    function pintarFiltros(lista) {
        var cuenta = function (k) { return lista.filter(FILTROS[k]).length; };
        var op = [
            ['todas', 'Todas', lista.length, ''],
            ['faltan', 'Faltan por llegar', cuenta('faltan'), 'll-filtro--faltan'],
            ['tarde', 'Se pasó la hora', cuenta('tarde'), 'll-filtro--tarde'],
            ['completas', 'Completas', cuenta('completas'), 'll-filtro--completas'],
            ['cerradas', 'Ya cerradas', cuenta('cerradas'), 'll-filtro--cerradas']
        ];
        document.getElementById('llFiltros').innerHTML = op.map(function (o) {
            return '<button type="button" class="ll-filtro ' + o[3] + (filtro === o[0] ? ' is-activo' : '') + '" data-filtro="' + o[0] + '">' + o[1] + ' <b>' + o[2] + '</b></button>';
        }).join('');
    }

    function parteHtml(r, p) {
        var accion;
        if (p.llegada) {
            accion = p.heredada
                ? '<span class="ll-ok ll-ok--heredada" title="' + esc(p.por) + '"><i class="bi bi-arrow-repeat"></i> Llegó ' + esc(p.llegada) + '<span class="ll-ok__extra">&nbsp;· audiencia anterior</span></span>'
                : '<span class="ll-ok" title="' + esc(p.por) + '"><i class="bi ' + (p.cita ? 'bi-qr-code' : 'bi-check-circle-fill') + '"></i> Llegó ' + esc(p.llegada) + '</span>';
            if (MARCA && r.abierto) {
                accion += '<button type="button" class="ll-deshacer" data-tipo="' + r.tipo + '" data-id="' + r.id + '" data-clave="' + esc(p.clave) + '" data-llego="0" title="' + esc(p.por) + '">Deshacer</button>';
            }
        } else if (!r.abierto) {
            accion = '<span class="ll-nollego">No se registró su llegada</span>';
        } else if (MARCA) {
            accion = '<button type="button" class="btn ll-llego" data-tipo="' + r.tipo + '" data-id="' + r.id + '" data-clave="' + esc(p.clave) + '" data-llego="1"><i class="bi bi-person-check"></i> Llegó</button>';
        } else {
            accion = '<span class="ll-espera"><i class="bi bi-hourglass-split"></i> Aún no llega</span>';
        }
        return '<div class="ll-parte">' +
            '<span class="ll-rol">' + esc(p.rol) + '</span>' +
            '<span class="ll-nombre">' + esc(p.nombre) + '</span>' +
            '<span class="ll-accion">' + accion + '</span></div>';
    }

    // Observaciones: las de la recepción (audiencias y ratificaciones) y las
    // del conciliador (sus audiencias), con las mismas casillas, cada una en
    // su recuadro. Cada quien edita sólo las suyas.
    function puedeAnotar(r, autor) {
        if (!URL_OBS || autor !== MI_AUTOR) { return false; }
        return autor === 'recepcion' || (r.tipo === 'audiencia' && r.conciliador_id === YO);
    }
    function obsBloque(r, autor, o) {
        var clave = r.tipo + ':' + r.id + ':' + autor;
        var puede = puedeAnotar(r, autor);
        var cls = 'll-obs ll-obs--' + autor;
        var autorHtml = '<div class="ll-obs__autor"><i class="bi ' + (autor === 'conciliador' ? 'bi-person-badge' : 'bi-door-open') + '"></i> ' + esc(AUTORES[autor]) + '</div>';
        if (puede && editando === clave && borrador) {
            var casillas = (OBSERVAN[r.tipo] || []).map(function (c) {
                return '<label class="ll-obs__casilla"><input type="checkbox" class="form-check-input m-0" data-obs-campo="' + c + '"' + (borrador[c] ? ' checked' : '') + '> ' + esc(OBS[c]) + '</label>';
            }).join('');
            return '<div class="' + cls + ' ll-obs--editar" data-obs="' + clave + '">' + autorHtml +
                (casillas ? '<div class="ll-obs__casillas">' + casillas + '</div>' : '') +
                '<textarea class="form-control form-control-sm" rows="2" maxlength="1000" data-obs-campo="texto" placeholder="Observaciones">' + esc(borrador.texto || '') + '</textarea>' +
                '<div class="ll-obs__botones"><button type="button" class="btn btn-sm btn-outline-secondary" data-obs-cancelar>Cancelar</button>' +
                '<button type="button" class="btn btn-sm btn-primary" data-obs-guardar data-tipo="' + r.tipo + '" data-id="' + r.id + '"><i class="bi bi-save"></i> Guardar</button></div></div>';
        }
        var boton = puede ? '<button type="button" class="ll-obs__editar" data-obs-abrir="' + clave + '"><i class="bi bi-pencil"></i> ' + (o ? 'Editar observaciones' : (autor === 'conciliador' ? 'Agregar mis observaciones' : 'Agregar observaciones')) + '</button>' : '';
        if (!o) { return puede ? '<div class="ll-obs ll-obs--vacia"><div class="ll-obs__pie">' + boton + '</div></div>' : ''; }
        var chips = (o.etiquetas || []).map(function (e) { return '<span class="ll-obs__chip"><i class="bi bi-exclamation-diamond"></i> ' + esc(e) + '</span>'; }).join('');
        return '<div class="' + cls + '">' + autorHtml + (chips ? '<div class="ll-obs__chips">' + chips + '</div>' : '') +
            (o.texto ? '<div class="ll-obs__texto"><i class="bi bi-chat-left-text"></i> ' + esc(o.texto) + '</div>' : '') +
            '<div class="ll-obs__pie">' + (o.por ? '<span class="ll-obs__meta">Anotó ' + esc(o.por) + ' · ' + esc(o.hora) + '</span>' : '<span></span>') + boton + '</div></div>';
    }
    function obsHtml(r) {
        if (!r.conObs) { return ''; }
        return obsBloque(r, 'recepcion', r.obs) + (r.tipo === 'audiencia' ? obsBloque(r, 'conciliador', r.obsConc) : '');
    }

    // Audiencias reagendadas: de dónde viene (con quién llegó a cada una) y
    // para cuándo quedó la nueva. El historial abierto se recuerda al repintar.
    var historialAbierto = {};
    function historialHtml(r) {
        var h = r.historial;
        if (!h) { return ''; }
        var html = '';
        if (h.siguiente) {
            html += '<div class="ll-reag"><i class="bi bi-arrow-repeat"></i> Se reagendó para <b>' + esc(h.siguiente) + '</b></div>';
        }
        if (h.previas && h.previas.length) {
            var items = h.previas.map(function (p) {
                var quien = p.llegadas.length
                    ? 'Llegaron: ' + p.llegadas.map(function (l) { return esc(l.quien) + ' ' + esc(l.nombre) + ' (' + esc(l.hora) + ')'; }).join(', ')
                    : '<span class="ll-reag__nadie">No se registró ninguna llegada</span>';
                return '<li><div class="ll-reag__cab">' + esc(p.numero) + ' · ' + esc(p.cuando) + ' · ' + esc(p.estatus) + '</div>' + quien + '</li>';
            }).join('');
            var clave = r.tipo + ':' + r.id;
            html += '<details class="ll-reag" data-historial="' + clave + '"' + (historialAbierto[clave] ? ' open' : '') + '>' +
                '<summary><i class="bi bi-clock-history"></i> Viene de ' + (h.previas.length === 1 ? 'una audiencia reagendada' : h.previas.length + ' audiencias reagendadas') + ' · ver historial</summary>' +
                '<ol>' + items + '</ol></details>';
        }
        return html;
    }

    function itemHtml(r) {
        var estado = !r.abierto ? 'is-cerrada' : (r.completa ? 'is-completa' : (r.atraso !== null ? 'is-tarde' : (r.llegaron > 0 ? 'is-parcial' : '')));
        var etiqueta = !r.abierto
            ? '<span class="ll-cerrada"><i class="bi bi-lock"></i> Cerrada · ' + esc(r.estatus) + '</span>'
            : (r.atraso !== null ? '<span class="ll-tarde"><i class="bi bi-alarm"></i> Se pasó la hora · ' + tiempo(r.atraso) + '</span>' : '');
        var cita = '';
        if (r.cita) {
            cita = '<div class="ll-cita"><i class="bi bi-qr-code"></i> Cita ' + esc(r.cita.folio) +
                (r.cita.valido ? ' · validada en recepción a las ' + esc(r.cita.valido) + (r.cita.tarde ? ' (llegó tarde; se atiende de todos modos)' : '') : ' · aún no se valida en recepción') + '</div>';
        }
        return '<article class="ll-item ' + estado + (esMia(r) ? ' is-mia' : '') + '">' +
            '<div><div class="ll-hora">' + esc(r.hora) + '</div><span class="ll-lugar">' + esc(r.lugar) + '</span></div>' +
            '<div><div class="ll-cab"><div>' +
                (tab === 'todas' ? '<span class="ll-tipo ll-tipo--' + r.tipo + '">' + CORTO[r.tipo] + '</span>' : '') +
                '<span class="ll-nue">' + esc(r.nue) + '</span>' +
                (r.historial && r.historial.previas && r.historial.previas.length ? '<span class="ll-reag-chip"><i class="bi bi-arrow-repeat"></i> Reagendada</span>' : '') +
                ' <span class="ll-conc">· ' + esc(r.conciliador || 'Sin conciliador') + '</span></div>' +
                '<span class="d-inline-flex gap-2 align-items-center flex-wrap">' + etiqueta + '<span class="ll-estado">' + (r.completa ? 'Todos llegaron' : 'Llegaron ' + r.llegaron + ' de ' + r.partes.length) + '</span>' +
                (r.tipo === 'ratificacion'
                    ? '<button type="button" class="ll-poder-btn' + (r.poder ? '' : ' is-sin') + '" data-poder-turno="' + r.id + '" data-poder-nue="' + esc(r.nue) + '">' +
                        '<i class="bi ' + (r.poder ? 'bi-person-vcard' : 'bi-exclamation-triangle') + '"></i> ' + (r.poder ? 'Ver poder' : 'Sin poder') + '</button>'
                    : '') +
                '</span></div>' +
            cita + historialHtml(r) + obsHtml(r) +
            (r.partes.length ? r.partes.map(function (p) { return parteHtml(r, p); }).join('') : '<span class="ll-conc">Sin partes registradas.</span>') +
            '</div></article>';
    }

    function pintar() {
        var lista = base();
        pintarTabs(lista);
        var deTab = tab === 'todas' ? lista : lista.filter(function (r) { return r.tipo === tab; });
        pintarFiltros(deTab);
        var visibles = deTab.filter(FILTROS[filtro] || FILTROS.todas);

        var vacio = (datos.registros || []).length
            ? (mias && !lista.length ? 'Hoy no tienes nada asignado en ' + esc(datos.sede) + '. Quita "Sólo las mías" para ver todo.' : 'Nada coincide.')
            : 'No hay audiencias, ratificaciones ni cumplimientos hoy en ' + esc(datos.sede) + '.';

        document.getElementById('llLista').innerHTML = visibles.length ? visibles.map(itemHtml).join('') : '<div class="ll-vacio">' + vacio + '</div>';

        var g = new Date(datos.generado);
        document.getElementById('llActualizado').textContent = 'Actualizado ' + g.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }

    function actualizar() {
        // "Actualizando…" sólo si la respuesta tarda; si es rápida no parpadea.
        var etiqueta = document.getElementById('llActualizado');
        var lento = setTimeout(function () {
            etiqueta.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Actualizando…';
        }, 400);
        return fetch(URL_DATOS, { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
            .finally(function () { clearTimeout(lento); })
            .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
            // Mientras se escriben observaciones no se repinta (se perdería el cursor); al guardar sí.
            .then(function (j) { datos = j; if (!editando) { pintar(); } })
            .catch(function () { document.getElementById('llActualizado').textContent = 'Sin conexión, reintentando…'; });
    }

    function aviso(t) {
        var m = document.getElementById('llMensaje');
        m.textContent = t; m.hidden = false;
        clearTimeout(aviso.t); aviso.t = setTimeout(function () { m.hidden = true; }, 8000);
    }

    if (MARCA) {
        document.getElementById('llLista').addEventListener('click', function (e) {
            var b = e.target.closest('[data-clave]');
            if (!b || b.disabled) { return; }
            b.disabled = true;
            // Mientras el servidor responde: el botón muestra que está guardando.
            if (b.classList.contains('ll-llego')) {
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando…';
            } else {
                b.textContent = 'Quitando…';
            }
            fetch(URL_MARCAR.replace('__T__', b.getAttribute('data-tipo')).replace('__ID__', b.getAttribute('data-id')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ clave: b.getAttribute('data-clave'), llego: b.getAttribute('data-llego') === '1' })
            })
                .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j, status: r.status }; }); })
                .then(function (res) {
                    if (!res.ok) { aviso(res.status === 419 ? 'La sesión expiró; recarga la página.' : (res.j.message || 'No se pudo guardar.')); }
                    return actualizar();
                })
                .catch(function () { aviso('Sin conexión; intenta de nuevo.'); pintar(); });
        });
    }

    if (URL_OBS) {
        var lista = document.getElementById('llLista');
        // clave = tipo:id:autor
        var buscar = function (clave) { return (datos.registros || []).filter(function (r) { return clave.indexOf(r.tipo + ':' + r.id + ':') === 0; })[0]; };
        var campo = function (e) {
            var c = e.target.getAttribute && e.target.getAttribute('data-obs-campo');
            if (c && borrador) { borrador[c] = e.target.type === 'checkbox' ? e.target.checked : e.target.value; }
        };
        lista.addEventListener('input', campo);
        lista.addEventListener('change', campo);
        lista.addEventListener('click', function (e) {
            var abrir = e.target.closest('[data-obs-abrir]');
            if (abrir) {
                var r = buscar(abrir.getAttribute('data-obs-abrir'));
                editando = abrir.getAttribute('data-obs-abrir');
                borrador = { silla_ruedas: false, traductor: false, menor_edad: false, texto: '' };
                var actual = r ? (MI_AUTOR === 'conciliador' ? r.obsConc : r.obs) : null;
                if (actual) { Object.keys(borrador).forEach(function (k) { borrador[k] = actual[k] || borrador[k]; }); }
                pintar();
                var t = lista.querySelector('[data-obs="' + editando + '"] textarea');
                if (t) { t.focus(); }
                return;
            }
            if (e.target.closest('[data-obs-cancelar]')) { editando = null; borrador = null; pintar(); return; }
            var g = e.target.closest('[data-obs-guardar]');
            if (!g || g.disabled) { return; }
            g.disabled = true;
            g.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando…';
            fetch(URL_OBS.replace('__T__', g.getAttribute('data-tipo')).replace('__ID__', g.getAttribute('data-id')), {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(borrador)
            })
                .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j, status: r.status }; }); })
                .then(function (res) {
                    if (!res.ok) {
                        aviso(res.status === 419 ? 'La sesión expiró; recarga la página.' : (res.j.message || 'No se pudieron guardar las observaciones.'));
                        g.disabled = false; g.innerHTML = '<i class="bi bi-save"></i> Guardar';
                        return;
                    }
                    editando = null; borrador = null;
                    return actualizar();
                })
                .catch(function () { aviso('Sin conexión; intenta de nuevo.'); g.disabled = false; g.innerHTML = '<i class="bi bi-save"></i> Guardar'; });
        });
    }

    document.getElementById('llTabs').addEventListener('click', function (e) {
        // data-ll-tab y no data-tab: scripts.js (Stisla) le pone a todo [data-tab] un
        // click con "return false" al cargar la página, y las pestañas no respondían
        // hasta que se volvían a dibujar (filtros, búsqueda o la actualización de 30 s).
        var b = e.target.closest('[data-ll-tab]');
        if (!b) { return; }
        tab = b.getAttribute('data-ll-tab'); filtro = 'todas'; pintar();
        try { var u = new URL(window.location.href); if (tab === 'todas') { u.searchParams.delete('tipo'); } else { u.searchParams.set('tipo', tab); } history.replaceState(null, '', u); } catch (err) {}
    });
    document.getElementById('llFiltros').addEventListener('click', function (e) {
        var b = e.target.closest('[data-filtro]');
        if (b) { filtro = b.getAttribute('data-filtro'); pintar(); }
    });
    document.getElementById('llBuscar').addEventListener('input', function () { texto = sinAcentos(this.value.trim()); pintar(); });
    var chk = document.getElementById('llMias');
    if (chk) { chk.addEventListener('change', function () { mias = this.checked; pintar(); }); }

    // El historial abierto sigue abierto cuando la lista se actualiza.
    document.getElementById('llLista').addEventListener('toggle', function (e) {
        var d = e.target;
        if (d && d.getAttribute && d.getAttribute('data-historial')) { historialAbierto[d.getAttribute('data-historial')] = d.open; }
    }, true);

    // Ratificaciones: el poder de la empresa, en una ventana de sólo lectura.
    var poderModal = document.getElementById('llPoderModal');
    var poderCuerpo = document.getElementById('llPoderCuerpo');
    function dato(etq, val) { return val ? '<div class="pd-dato"><span>' + esc(etq) + '</span><span>' + esc(val) + '</span></div>' : ''; }
    function poderHtml(j) {
        var p = j.poder;
        if (!p) {
            return '<div class="pd-vacio"><i class="bi bi-exclamation-triangle"></i><b>Esta ratificación no tiene un poder relacionado.</b>' +
                '<div class="small mt-1">Expediente ' + esc(j.nue || 'sin NUE') + '.</div></div>';
        }
        var est = p.estatus === 'Validado' ? 'is-ok' : (p.estatus === 'Pendiente' ? 'is-aviso' : 'is-mal');
        var vig = p.vigencia
            ? (p.vigencia.vencido ? '<span class="pd-chip is-mal"><i class="bi bi-calendar-x"></i> Venció el ' + esc(p.vigencia.fecha) + '</span>'
                : '<span class="pd-chip is-ok"><i class="bi bi-calendar-check"></i> Vigente al ' + esc(p.vigencia.fecha) + '</span>')
            : '<span class="pd-chip">Sin fecha de vigencia</span>';
        var h = '<div class="pd-cab"><span class="pd-chip">Folio ' + esc(p.folio) + '</span><span class="pd-chip ' + est + '">' + esc(p.estatus) + '</span>' + vig +
            '<span class="pd-chip">' + esc(p.tipo) + '</span></div>';

        // Quién debe presentarse: el representante o, sin él, el propio patrón.
        var rep = p.representante;
        h += '<div class="pd-seccion is-quien"><h6><i class="bi bi-person-check"></i> ' + (p.comparece === 'representante' ? 'Quien comparece: representante legal' : 'Quien comparece: el propio patrón') + '</h6>' +
            '<div class="pd-nombre">' + esc(rep ? rep.nombre : p.patron.nombre) + '</div>' +
            dato('CURP', rep ? rep.curp : p.patron.curp) +
            dato('Identificación', [p.identificacion.tipo, p.identificacion.numero].filter(Boolean).join(' · ')) +
            dato('Teléfono', rep ? rep.telefono : p.patron.telefono) +
            dato('Correo', rep ? rep.correo : p.patron.correo) + '</div>';

        h += '<div class="pd-seccion"><h6><i class="bi bi-building"></i> Patrón / empresa</h6>' +
            '<div class="pd-nombre" style="font-size:14.5px">' + esc(p.patron.nombre || 'Sin nombre') + '</div>' +
            dato('RFC', p.patron.rfc) + (rep ? dato('CURP', p.patron.curp) : '') + dato('Giro', p.patron.giro) +
            (rep ? dato('Teléfono', p.patron.telefono) + dato('Correo', p.patron.correo) : '') + '</div>';

        if (p.personeria) {
            h += '<div class="pd-seccion"><h6><i class="bi bi-file-earmark-text"></i> Personería</h6>' +
                dato('Documento', p.personeria.tipo) + dato('Descripción', p.personeria.descripcion) + dato('Registrado', p.registro) + '</div>';
        }

        h += '<div class="pd-seccion"><h6><i class="bi bi-folder-check"></i> Documentos en el poder</h6><ul class="pd-docs">' +
            p.documentos.map(function (d) {
                return '<li><span>' + esc(d.nombre) + '</span>' + (d.esta ? '<span class="is-si"><i class="bi bi-check-circle-fill"></i> Sí está</span>' : '<span class="is-no"><i class="bi bi-x-circle-fill"></i> No está</span>') + '</li>';
            }).join('') + '</ul></div>';
        return h;
    }
    document.getElementById('llLista').addEventListener('click', function (e) {
        var b = e.target.closest('[data-poder-turno]');
        if (!b || !poderModal || !window.bootstrap) { return; }
        document.getElementById('llPoderTitulo').textContent = 'Poder de la empresa · ' + (b.getAttribute('data-poder-nue') || '');
        poderCuerpo.innerHTML = '<div class="pd-vacio"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando…</div>';
        bootstrap.Modal.getOrCreateInstance(poderModal).show();
        fetch(URL_PODER.replace('__ID__', b.getAttribute('data-poder-turno')), { headers: { 'Accept': 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j }; }); })
            .then(function (res) {
                poderCuerpo.innerHTML = res.ok ? poderHtml(res.j) : '<div class="pd-vacio">' + esc(res.j.message || 'No se pudo consultar el poder.') + '</div>';
            })
            .catch(function () { poderCuerpo.innerHTML = '<div class="pd-vacio">Sin conexión; intenta de nuevo.</div>'; });
    });

    pintar();
    setInterval(actualizar, 30000);
})();
</script>
@endsection
