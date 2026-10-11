@extends('layouts.app')
@section('title', 'Por notificar')

{{--
    Por notificar: citados sin notificador de la región.

    Asignación por lote: cada fila tiene su selector de notificador y, además,
    se pueden marcar varias y asignarles uno de golpe. Nada se guarda al
    elegir: las asignaciones quedan "preparadas" (en sessionStorage, así
    sobreviven al cambiar de página o de filtro) y el botón "Revisar y enviar"
    las manda todas juntas a NotificacionesController::asignarLote.
--}}

@php
    use Illuminate\Support\Carbon;

    $url = fn (array $cambios) => route('notificaciones', array_filter(
        array_merge(request()->except('page'), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $hoy = Carbon::today();
    $multiSede = count($sedes) > 1;
    $documentos = function ($n) {
        $r = fn ($nombre) => \Illuminate\Support\Facades\Route::has($nombre) ? route($nombre, [$n->id_citado, $n->id_solicitud]) : null;
        $esMulta = $n->tipo_notificacion === 'Multa';
        return array_filter(match ($n->estatus) {
            'Finalizado exitosamente'     => ['Notificación' => $esMulta ? null : $r('PDFRazonNoticacion'), 'Multa' => $esMulta ? $r('PDFmulta') : null],
            'No notificada'               => ['Notificación' => $esMulta ? null : $r('PDFInstructivo'),     'Multa' => $esMulta ? $r('PDFmulta') : null],
            'No exitosa se constituye'    => ['Notificación' => $esMulta ? null : $r('VerPDFNoExitConstituye'), 'Multa' => $esMulta ? $r('PDFmulta') : null],
            'No exitosa no se constituye' => ['Notificación' => $r('PDFNoExitosaInt')],
            default                       => [],
        });
    };
    $lote = session('lote');
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <style>
        .nt-lote-herramienta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            padding: 12px 16px;
            margin-bottom: 12px;
            border-radius: 10px;
            background: var(--tn-fondo);
            font-size: 13px;
        }
        .nt-lote-herramienta .form-select { width: auto; min-width: 240px; }
        .nt-lote-herramienta__marcadas { font-weight: 700; color: var(--tn-tinta); }
        .nt-lote-herramienta__ayuda { color: var(--tn-tenue); margin-left: auto; }

        .nt-tabla .form-check-input { width: 1.15em; height: 1.15em; cursor: pointer; }
        .nt-tabla .nt-select-fila { min-width: 210px; font-size: 13px; }
        .nt-fila--preparada td { background: #FDF8EA !important; }
        .nt-fila--preparada td:first-child { box-shadow: inset 4px 0 0 #CEA845; }
        .nt-fila--preparada .nt-select-fila { border-color: #CEA845; font-weight: 600; }

        .nt-audiencia { white-space: nowrap; font-size: 12.5px; }
        .nt-audiencia--pronto { color: #A3162E; font-weight: 700; }

        /* Barra del lote: fija abajo, sobre el área de contenido, mientras hay
           preparadas. Se mueve a <body> y su left/width los ajusta el script al
           ancho del contenido (cambia al abrir o cerrar el menú lateral). Con
           sticky no funcionaba: un contenedor del layout recorta el scroll. */
        .nt-barra {
            position: fixed;
            bottom: 16px;
            left: 16px;
            right: 16px;
            z-index: 1030;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 16px;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            background: #2E3C3D;
            color: #fff;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .22);
        }
        .nt-barra[hidden] { display: none; }
        @media (prefers-reduced-motion: no-preference) {
            .nt-barra { animation: nt-sube .18s ease-out; }
            @keyframes nt-sube { from { transform: translateY(12px); opacity: 0; } to { transform: none; opacity: 1; } }
        }
        .nt-barra__total { font-size: 15px; font-weight: 700; }
        .nt-barra__fuera { font-size: 12px; opacity: .75; }
        .nt-barra__chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .nt-barra__chip { padding: 2px 10px; border-radius: 999px; background: rgba(255, 255, 255, .14); font-size: 12px; }
        .nt-barra__acciones { margin-left: auto; display: flex; gap: 8px; }
        .nt-barra .btn-link { color: #fff; opacity: .85; text-decoration: none; }
        .nt-barra .btn-link:hover { opacity: 1; color: #fff; }

        .nt-resumen-grupo { border: 1px solid var(--tn-borde); border-radius: 10px; margin-bottom: 10px; overflow: hidden; }
        .nt-resumen-grupo__cabeza { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: var(--tn-fondo); font-weight: 700; }
        .nt-resumen-grupo ul { list-style: none; margin: 0; padding: 6px 14px 10px; max-height: 180px; overflow: auto; font-size: 13px; }
        .nt-resumen-grupo li { padding: 3px 0; border-top: 1px dashed var(--tn-borde); }
        .nt-resumen-grupo li:first-child { border-top: 0; }

        .nt-aviso-lote { border-radius: 12px; }
        .nt-aviso-lote ul { margin: 6px 0 0; padding-left: 18px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('index_notificaciones') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Notificaciones
                </a>
                <h3 class="page__heading mb-0">Por notificar</h3>
            </div>
            <span class="small" style="color: var(--tn-tenue);"><i class="bi bi-geo-alt"></i> {{ implode(' y ', $sedes) }}</span>
        </div>

        <div class="section-body">
            @if ($lote)
                <div class="alert {{ $lote['total'] ? 'alert-success' : 'alert-warning' }} alert-dismissible fade show nt-aviso-lote" role="alert">
                    <strong>
                        {{ $lote['total'] ? 'Se asignaron '.number_format($lote['total']).' '.($lote['total'] === 1 ? 'expediente' : 'expedientes').'.' : 'No se asignó ningún expediente.' }}
                    </strong>
                    @if ($lote['resumen'])
                        {{ collect($lote['resumen'])->map(fn ($r) => $r['notificador'].': '.$r['total'])->implode(' · ') }}
                    @endif
                    @if ($lote['omitidos'])
                        <div class="mt-2">
                            {{ count($lote['omitidos']) }} no se asignaron:
                            <ul>
                                @foreach (array_slice($lote['omitidos'], 0, 15) as $o)
                                    <li>{{ $o['nue'] }}: {{ $o['motivo'] }}</li>
                                @endforeach
                                @if (count($lote['omitidos']) > 15)<li>y {{ count($lote['omitidos']) - 15 }} más.</li>@endif
                            </ul>
                        </div>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            {{-- Filtros. GET: la vista se puede compartir con su URL. --}}
            <form method="GET" action="{{ route('notificaciones') }}" class="tn-tarjeta" id="ntFiltros">
                <input type="hidden" name="vista" value="{{ $vista }}">
                @if (! empty($filtros['por']))<input type="hidden" name="por" value="{{ $filtros['por'] }}">@endif
                <div class="nt-filtros">
                    <div>
                        <label for="ntQ">Buscar</label>
                        <input type="search" id="ntQ" name="q" class="form-control" maxlength="100"
                               placeholder="Expediente, citado, colonia o calle" value="{{ $filtros['q'] ?? '' }}">
                    </div>
                    @if ($multiSede)
                        <div>
                            <label for="ntSede">Sede</label>
                            <select id="ntSede" name="sede" class="form-select nt-auto">
                                <option value="">Todas</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="ntMunicipio">Municipio</label>
                        <select id="ntMunicipio" name="municipio" class="form-select nt-auto">
                            <option value="">Todos</option>
                            @foreach ($municipios as $m)
                                <option value="{{ $m->id }}" @selected((string) ($filtros['municipio'] ?? '') === (string) $m->id)>{{ $m->nombre }} ({{ number_format($m->total) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ntTipo">Tipo</label>
                        <select id="ntTipo" name="tipo" class="form-select nt-auto">
                            <option value="">Todos</option>
                            <option value="Citatorio" @selected(($filtros['tipo'] ?? '') === 'Citatorio')>Citatorio</option>
                            <option value="Multa" @selected(($filtros['tipo'] ?? '') === 'Multa')>Multa</option>
                        </select>
                    </div>
                    <div>
                        <label for="ntOrden">Ordenar</label>
                        <select id="ntOrden" name="orden" class="form-select nt-auto">
                            <option value="recientes" @selected($orden === 'recientes')>Más recientes</option>
                            <option value="audiencia" @selected($orden === 'audiencia')>Audiencia más próxima</option>
                            <option value="antiguos" @selected($orden === 'antiguos')>Más antiguos</option>
                        </select>
                    </div>
                    <div class="nt-filtros__acciones">
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                        @if (array_filter(request()->except(['page', 'vista', 'por'])))
                            <a href="{{ route('notificaciones', ['vista' => $vista]) }}" class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="nt-pestanas">
                <a class="nt-pestana {{ $vista === 'asignar' ? 'is-activa' : '' }}" href="{{ $url(['vista' => 'asignar', 'municipio' => null]) }}">
                    Por asignar <span class="nt-pestana__num">{{ number_format($conteos['asignar']) }}</span>
                </a>
                <a class="nt-pestana {{ $vista === 'todos' ? 'is-activa' : '' }}" href="{{ $url(['vista' => 'todos', 'municipio' => null]) }}">
                    Todos sin notificador <span class="nt-pestana__num">{{ number_format($conteos['todos']) }}</span>
                </a>
            </div>

            <div class="tn-tarjeta">
                @if ($puedeAsignar && $notificadores->isNotEmpty())
                    {{-- Asignar uno a varios: marca filas y elige notificador. --}}
                    <div class="nt-lote-herramienta" id="ntHerramienta">
                        <span class="nt-lote-herramienta__marcadas" id="ntMarcadas">0 marcadas</span>
                        <select class="form-select form-select-sm" id="ntNotificadorLote" aria-label="Notificador para las marcadas">
                            <option value="">Elige notificador…</option>
                            @foreach ($notificadores as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} · {{ $p->en_campo }} en campo</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm tn-boton-dorado" id="ntAplicarLote" disabled>
                            <i class="bi bi-check2-all me-1"></i> Asignar a marcadas
                        </button>
                        <span class="nt-lote-herramienta__ayuda">
                            <i class="bi bi-info-circle"></i> Nada se guarda hasta "Revisar y enviar".
                        </span>
                    </div>
                @elseif ($puedeAsignar)
                    <div class="alert alert-warning mb-3">No hay notificadores activos en {{ implode(' ni ', $sedes) }}.</div>
                @endif

                @if ($citados->isEmpty())
                    <div class="nt-vacio">
                        <i class="bi bi-inbox" aria-hidden="true"></i>
                        No hay expedientes con estos filtros.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover nt-tabla" id="ntTabla">
                            <thead>
                                <tr>
                                    @if ($puedeAsignar)
                                        <th style="width: 36px;">
                                            <input type="checkbox" class="form-check-input" id="ntMarcarTodas" aria-label="Marcar todas las de esta página">
                                        </th>
                                    @endif
                                    <th>Expediente</th>
                                    <th>Citado</th>
                                    <th>Dirección</th>
                                    <th>Próx. audiencia</th>
                                    <th>Estatus</th>
                                    @if ($puedeAsignar)<th>Notificador</th>@endif
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($citados as $n)
                                    @php
                                        $asignable = in_array($n->estatus, \App\Http\Controllers\NotificacionesController::ASIGNABLES, true);
                                        $citado = trim(preg_replace('/\s+/', ' ', "{$n->nombre} {$n->primer_apellido} {$n->segundo_apellido}"));
                                        $proxima = $n->proxima ? Carbon::parse($n->proxima) : null;
                                        $docs = $asignable ? [] : $documentos($n);
                                    @endphp
                                    <tr data-citado="{{ $n->id_citado }}" data-nue="{{ $n->NUE }}" data-nombre="{{ $citado }}">
                                        @if ($puedeAsignar)
                                            <td>
                                                @if ($asignable)
                                                    <input type="checkbox" class="form-check-input nt-marca" aria-label="Marcar {{ $n->NUE }}">
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            <span class="nt-nue">{{ $n->NUE ?: 'Sin NUE' }}</span>
                                            <span class="nt-sub">
                                                <span class="nt-tipo {{ $n->tipo_notificacion === 'Multa' ? 'nt-tipo--multa' : '' }}">{{ $n->tipo_notificacion ?: '—' }}</span>
                                                @if ($multiSede) · {{ $n->delegacion }}@endif
                                            </span>
                                        </td>
                                        <td><span class="nt-citado" title="{{ $citado }}">{{ $citado ?: 'Sin nombre' }}</span></td>
                                        <td>@include('notificaciones._direccion', ['n' => $n])</td>
                                        <td>
                                            @if ($proxima)
                                                <span class="nt-audiencia {{ $hoy->diffInDays($proxima) <= 7 ? 'nt-audiencia--pronto' : '' }}">
                                                    {{ $proxima->locale('es')->isoFormat('D MMM YYYY') }}
                                                </span>
                                            @else
                                                <span class="nt-sub">—</span>
                                            @endif
                                        </td>
                                        <td>@include('notificaciones._estatus', ['estatus' => $n->estatus])</td>
                                        @if ($puedeAsignar)
                                            <td>
                                                @if ($asignable)
                                                    <select class="form-select form-select-sm nt-select-fila" aria-label="Notificador para {{ $n->NUE }}">
                                                        <option value="">Sin asignar</option>
                                                        @foreach ($notificadores as $p)
                                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <span class="nt-sub">—</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            <div class="nt-acciones">
                                                @if ($asignable)
                                                    @can('por_notificar_editar')
                                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('editar_citado', $n->id_citado) }}" title="Editar datos del citado">
                                                            <i class="bi bi-pencil-square"></i> Editar
                                                        </a>
                                                    @endcan
                                                @elseif ($docs)
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-file-earmark-text"></i> Documentos
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @foreach ($docs as $etiqueta => $href)
                                                                <li><a class="dropdown-item" href="{{ $href }}" target="_blank" rel="noopener">{{ $etiqueta }}</a></li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="nt-pie">
                        <span class="nt-por">
                            Mostrando {{ number_format($citados->firstItem()) }}–{{ number_format($citados->lastItem()) }} de {{ number_format($citados->total()) }}
                            ·
                            <select class="form-select form-select-sm" id="ntPorPagina" aria-label="Filas por página">
                                @foreach ($porPagina as $p)
                                    <option value="{{ $p }}" @selected($citados->perPage() === $p)>{{ $p }} por página</option>
                                @endforeach
                            </select>
                        </span>
                        @include('turnos._paginacion', ['pagina' => $citados])
                    </div>
                @endif
            </div>

            @if ($puedeAsignar)
                {{-- Barra del lote. La llena el script con lo preparado. --}}
                <div class="nt-barra" id="ntBarra" hidden>
                    <div>
                        <div class="nt-barra__total" id="ntBarraTotal"></div>
                        <div class="nt-barra__fuera" id="ntBarraFuera"></div>
                    </div>
                    <div class="nt-barra__chips" id="ntBarraChips"></div>
                    <div class="nt-barra__acciones">
                        <button type="button" class="btn btn-link btn-sm" id="ntDescartar">Descartar</button>
                        <button type="button" class="btn tn-boton-dorado" data-bs-toggle="modal" data-bs-target="#ntModalLote">
                            <i class="bi bi-send me-1"></i> Revisar y enviar
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if ($puedeAsignar)
        <div class="modal fade" id="ntModalLote" tabindex="-1" aria-labelledby="ntModalLoteTitulo" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <form class="modal-content" method="POST" action="{{ route('notificaciones.asignar_lote') }}" id="ntFormLote">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="ntModalLoteTitulo">Revisar asignaciones</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3" style="color: var(--tn-suave);">
                            Revisa a quién se va cada expediente. Al enviar, cada uno pasa a <strong>Pendiente</strong> con su notificador.
                            Si alguien más ya asignó alguno mientras tanto, ese se omite y se te avisa.
                        </p>
                        <div id="ntResumen"></div>
                        <div id="ntCampos"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir preparando</button>
                        <button type="submit" class="btn tn-boton-dorado" id="ntEnviar">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
(function () {
    'use strict';

    // ---------- Filtros
    var filtros = document.getElementById('ntFiltros');
    document.querySelectorAll('#ntFiltros .nt-auto').forEach(function (s) {
        s.addEventListener('change', function () { filtros.submit(); });
    });
    var porPagina = document.getElementById('ntPorPagina');
    if (porPagina) {
        porPagina.addEventListener('change', function () {
            var u = new URL(location.href);
            u.searchParams.set('por', this.value);
            u.searchParams.delete('page');
            location.href = u.toString();
        });
    }

    // Menús de Documentos dentro de la tabla con scroll: Popper en 'fixed'.
    document.querySelectorAll('#ntTabla [data-bs-toggle="dropdown"]').forEach(function (b) {
        bootstrap.Dropdown.getOrCreateInstance(b, {
            popperConfig: function (c) { return Object.assign({}, c, { strategy: 'fixed' }); }
        });
    });

    var tabla = document.getElementById('ntTabla');
    var barra = document.getElementById('ntBarra');
    if (!barra) return;   // sin permiso de asignar

    // ---------- Barra fija abajo, alineada con el contenido
    var contenido = document.querySelector('.section-body');
    document.body.appendChild(barra);

    function ubicarBarra() {
        if (barra.hidden || !contenido) return;
        var r = contenido.getBoundingClientRect();
        barra.style.left = Math.max(r.left, 8) + 'px';
        barra.style.right = 'auto';
        barra.style.width = Math.min(r.width, window.innerWidth - 16) + 'px';
        // Espacio al final de la página para que la barra no tape las últimas filas.
        contenido.style.paddingBottom = (barra.offsetHeight + 28) + 'px';
    }
    window.addEventListener('resize', ubicarBarra);
    if (window.ResizeObserver && contenido) new ResizeObserver(ubicarBarra).observe(contenido);

    // ---------- Lote preparado (sobrevive a cambiar de página o filtro)
    var NOTIFICADORES = @json($notificadores->mapWithKeys(fn ($p) => [$p->id => $p->name]));
    var CLAVE = 'siconcilio-lote-notificaciones-' + @json(auth()->id());
    var ENVIADO = @json((bool) $lote);

    function leer() {
        try { return JSON.parse(sessionStorage.getItem(CLAVE)) || {}; } catch (e) { return {}; }
    }
    function guardar() {
        try { sessionStorage.setItem(CLAVE, JSON.stringify(prep)); } catch (e) {}
    }

    var prep = ENVIADO ? {} : leer();
    // Notificadores que ya no están en la lista (baja, otra sede) se descartan.
    Object.keys(prep).forEach(function (id) { if (!NOTIFICADORES[prep[id].n]) delete prep[id]; });
    guardar();

    var filas = tabla ? Array.prototype.slice.call(tabla.querySelectorAll('tbody tr[data-citado]')) : [];

    function pintarFila(tr) {
        var id = tr.getAttribute('data-citado');
        tr.classList.toggle('nt-fila--preparada', !!prep[id]);
    }

    filas.forEach(function (tr) {
        var id = tr.getAttribute('data-citado');
        var sel = tr.querySelector('.nt-select-fila');
        if (!sel) return;
        if (prep[id]) sel.value = String(prep[id].n);
        pintarFila(tr);
        sel.addEventListener('change', function () {
            if (this.value) {
                prep[id] = { n: parseInt(this.value, 10), nue: tr.getAttribute('data-nue'), nombre: tr.getAttribute('data-nombre') };
            } else {
                delete prep[id];
            }
            guardar();
            pintarFila(tr);
            pintarBarra();
        });
    });

    // ---------- Marcar varias y asignar a una persona
    var marcarTodas = document.getElementById('ntMarcarTodas');
    var marcadasTxt = document.getElementById('ntMarcadas');
    var selLote = document.getElementById('ntNotificadorLote');
    var aplicar = document.getElementById('ntAplicarLote');

    function marcas() { return Array.prototype.slice.call(document.querySelectorAll('#ntTabla .nt-marca')); }
    function marcadas() { return marcas().filter(function (c) { return c.checked; }); }

    function pintarHerramienta() {
        var n = marcadas().length, total = marcas().length;
        if (marcadasTxt) marcadasTxt.textContent = n + (n === 1 ? ' marcada' : ' marcadas');
        if (aplicar) aplicar.disabled = !(n && selLote && selLote.value);
        if (marcarTodas) {
            marcarTodas.checked = total > 0 && n === total;
            marcarTodas.indeterminate = n > 0 && n < total;
        }
    }

    marcas().forEach(function (c) { c.addEventListener('change', pintarHerramienta); });
    if (marcarTodas) {
        marcarTodas.addEventListener('change', function () {
            var v = this.checked;
            marcas().forEach(function (c) { c.checked = v; });
            pintarHerramienta();
        });
    }
    if (selLote) selLote.addEventListener('change', pintarHerramienta);

    if (aplicar) {
        aplicar.addEventListener('click', function () {
            var valor = selLote.value;
            if (!valor) return;
            marcadas().forEach(function (c) {
                var tr = c.closest('tr');
                var sel = tr.querySelector('.nt-select-fila');
                if (!sel) return;
                sel.value = valor;
                sel.dispatchEvent(new Event('change'));
                c.checked = false;
            });
            pintarHerramienta();
        });
    }

    // ---------- Barra inferior
    function porNotificador() {
        var grupos = {};
        Object.keys(prep).forEach(function (id) {
            var n = prep[id].n;
            (grupos[n] = grupos[n] || []).push(Object.assign({ id: id }, prep[id]));
        });
        return grupos;
    }

    function pintarBarra() {
        var ids = Object.keys(prep);
        barra.hidden = ids.length === 0;
        if (!ids.length) {
            if (contenido) contenido.style.paddingBottom = '';
            return;
        }

        var enPagina = {};
        filas.forEach(function (tr) { enPagina[tr.getAttribute('data-citado')] = true; });
        var fuera = ids.filter(function (id) { return !enPagina[id]; }).length;

        document.getElementById('ntBarraTotal').textContent =
            ids.length + (ids.length === 1 ? ' asignación preparada' : ' asignaciones preparadas');
        document.getElementById('ntBarraFuera').textContent =
            fuera ? (fuera + ' de otras páginas o filtros') : '';

        var chips = document.getElementById('ntBarraChips');
        chips.textContent = '';
        var grupos = porNotificador();
        Object.keys(grupos).sort(function (a, b) { return grupos[b].length - grupos[a].length; }).forEach(function (n) {
            var c = document.createElement('span');
            c.className = 'nt-barra__chip';
            c.textContent = NOTIFICADORES[n] + ': ' + grupos[n].length;
            chips.appendChild(c);
        });
        ubicarBarra();
    }

    document.getElementById('ntDescartar').addEventListener('click', function () {
        if (!confirm('¿Descartar las ' + Object.keys(prep).length + ' asignaciones preparadas?')) return;
        prep = {};
        guardar();
        filas.forEach(function (tr) {
            var sel = tr.querySelector('.nt-select-fila');
            if (sel) sel.value = '';
            pintarFila(tr);
        });
        pintarBarra();
    });

    // ---------- Revisar y enviar
    var modal = document.getElementById('ntModalLote');
    var enviar = document.getElementById('ntEnviar');

    modal.addEventListener('show.bs.modal', function () {
        var resumen = document.getElementById('ntResumen');
        var campos = document.getElementById('ntCampos');
        resumen.textContent = '';
        campos.textContent = '';

        var grupos = porNotificador();
        Object.keys(grupos).sort(function (a, b) { return grupos[b].length - grupos[a].length; }).forEach(function (n) {
            var caja = document.createElement('div');
            caja.className = 'nt-resumen-grupo';
            var cabeza = document.createElement('div');
            cabeza.className = 'nt-resumen-grupo__cabeza';
            var nombre = document.createElement('span');
            nombre.textContent = NOTIFICADORES[n];
            var cuenta = document.createElement('span');
            cuenta.textContent = grupos[n].length + (grupos[n].length === 1 ? ' expediente' : ' expedientes');
            cabeza.appendChild(nombre);
            cabeza.appendChild(cuenta);
            caja.appendChild(cabeza);

            var lista = document.createElement('ul');
            grupos[n].forEach(function (item) {
                var li = document.createElement('li');
                li.textContent = (item.nue || ('#' + item.id)) + ' · ' + (item.nombre || '');
                lista.appendChild(li);

                var campo = document.createElement('input');
                campo.type = 'hidden';
                campo.name = 'asignaciones[' + item.id + ']';
                campo.value = item.n;
                campos.appendChild(campo);
            });
            caja.appendChild(lista);
            resumen.appendChild(caja);
        });

        var total = Object.keys(prep).length;
        enviar.disabled = total === 0;
        enviar.textContent = total === 1 ? 'Enviar 1 asignación' : 'Enviar ' + total + ' asignaciones';
    });

    document.getElementById('ntFormLote').addEventListener('submit', function () {
        enviar.disabled = true;
        enviar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Enviando…';
    });

    pintarHerramienta();
    pintarBarra();
})();
</script>
@endsection
