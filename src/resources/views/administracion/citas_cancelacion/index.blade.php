@extends('layouts.app')
@section('title', 'Cancelación/Reagenda de citas')

{{--
    Administración → Cancelación/Reagenda de citas (CancelacionCitasController).
    Dos pestañas con filtros (App\Support\CitasCancelacion):
      Citas        rango de fechas, sede, trámite, estatus, origen y búsqueda;
                   las pendientes se cancelan o se reagendan.
      Movimientos  cada cancelación y reagenda hecha aquí: cuándo, quién,
                   motivo y de qué horario a cuál.
    Las dos se descargan en Excel con los mismos filtros.
--}}

@section('page_css')
    <style>
        :root { --color-guinda: #496163; --color-oro: #CEA845; }
        .rg-encabezado { background: var(--color-guinda); }
        .rg-actual { font-size: 13.5px; color: #6C7A7B; margin-bottom: 12px; }
        .rg-actual strong { color: #2E3C3D; }
        .rg-motivo { border-top: 1px solid #E3E8E8; padding-top: 14px; margin-top: 4px; }

        .rg-titulo { font-size: 15px; font-weight: 600; color: #2E3C3D; margin: 0; }
        .rg-titulo small { font-weight: 400; color: #6C7A7B; }
        .rg-titulo-fila { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin: 4px 0 10px; }
        .rg-dia { display: flex; align-items: center; gap: 6px; }

        .rg-estatus { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .rg-estatus--pendiente  { background: #FBF4E1; color: #7A5D10; }
        .rg-estatus--confirmada { background: #E3EEF9; color: #1F4E79; }
        .rg-estatus--atendido   { background: #E5F3EA; color: #1E7B3C; }
        .rg-estatus--expirada   { background: #F1F1F1; color: #6B6B6B; }
        .rg-estatus--cancelada  { background: #FBE4E6; color: #A3162E; }
        .rg-sin-accion { font-size: 12px; color: #8A9899; }

        /* Pestañas */
        .rg-pestanas { display: flex; gap: 4px; border-bottom: 1px solid #E3E8E8; margin: 0 0 14px; padding: 0; list-style: none; }
        .rg-pestanas a {
            display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; font-weight: 600; font-size: 14px;
            color: #6C7A7B; text-decoration: none; border-bottom: 3px solid transparent; margin-bottom: -1px;
        }
        .rg-pestanas a:hover { color: var(--color-guinda); }
        .rg-pestanas a.is-activa { color: var(--color-guinda); border-bottom-color: var(--color-oro); }

        /* Filtros */
        .rg-filtros { background: #F7F9F9; border: 1px solid #E3E8E8; border-radius: 10px; padding: 14px; margin-bottom: 14px; }
        .rg-filtros__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px 12px; }
        .rg-filtros__grid .rg-filtros__buscar { grid-column: span 2; }
        .rg-filtros label { font-size: 12px; font-weight: 700; color: #5B6B6C; margin-bottom: 3px; }
        .rg-filtros .form-control, .rg-filtros .form-select { font-size: 13.5px; }
        .rg-filtros__pie { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
        .rg-atajos { display: flex; flex-wrap: wrap; gap: 6px; }
        .rg-atajo {
            padding: 4px 11px; border-radius: 999px; border: 1px solid #D5DDDD; background: #fff;
            color: #4A5A5B; font-size: 12.5px; font-weight: 600; text-decoration: none;
        }
        .rg-atajo:hover { border-color: var(--color-guinda); color: var(--color-guinda); }
        .rg-atajo.is-activo { background: var(--color-guinda); border-color: var(--color-guinda); color: #fff; }
        .rg-filtros__botones { display: flex; gap: 8px; }
        .rg-nota { font-size: 12.5px; color: #6C7A7B; margin: 8px 0 0; }

        /* Tarjetas de resumen */
        .rg-kpis { display: grid; grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)); gap: 10px; margin-bottom: 14px; }
        .rg-kpi {
            display: block; padding: 10px 12px; border: 1px solid #E3E8E8; border-radius: 10px; background: #fff;
            text-decoration: none; color: #2E3C3D; transition: border-color .15s;
        }
        a.rg-kpi:hover { border-color: var(--color-guinda); color: #2E3C3D; }
        .rg-kpi.is-activo { border-color: var(--color-oro); box-shadow: 0 0 0 2px rgba(206, 168, 69, .25); }
        .rg-kpi b { display: block; font-size: 22px; line-height: 1.1; }
        .rg-kpi span { font-size: 12px; color: #6C7A7B; font-weight: 600; }
        .rg-kpi--pendiente b { color: #7A5D10; }
        .rg-kpi--cancelada b, .rg-kpi--cancelacion b { color: #A3162E; }
        .rg-kpi--atendido b { color: #1E7B3C; }
        .rg-kpi--reagenda b, .rg-kpi--confirmada b { color: #1F4E79; }

        .rg-desglose { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 10px; margin-bottom: 14px; }
        .rg-caja { border: 1px solid #E3E8E8; border-radius: 10px; padding: 10px 12px; background: #fff; }
        .rg-caja h6 { font-size: 12px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #8A9899; margin: 0 0 8px; }
        .rg-barra-fila { display: grid; grid-template-columns: minmax(0, 1fr) 34px; gap: 2px 8px; align-items: center; font-size: 13px; margin-bottom: 6px; }
        .rg-barra-fila a, .rg-barra-fila span.rg-barra-nombre { color: #2E3C3D; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .rg-barra-fila a:hover { color: var(--color-guinda); text-decoration: underline; }
        .rg-barra-fila b { text-align: right; }
        .rg-barra-fila i { grid-column: 1 / -1; display: block; height: 5px; border-radius: 4px; background: #EEF2F2; overflow: hidden; }
        .rg-barra-fila i::after { content: ''; display: block; height: 100%; width: var(--p); background: var(--color-guinda); border-radius: 4px; }

        /* Tablas */
        .rg-tabla td { font-size: 13.5px; }
        .rg-tabla th { white-space: nowrap; }
        .rg-contacto { display: block; font-size: 11.5px; color: #8A9899; }
        .rg-chip { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
        .rg-chip--linea { background: #EEF2F2; color: var(--color-guinda); }
        .rg-chip--ventanilla { background: #F3F1EC; color: #5A5446; }
        .rg-chip--reagenda { background: #E3EEF9; color: #1F4E79; }
        .rg-chip--cancelacion { background: #FBE4E6; color: #A3162E; }
        .rg-cambio { white-space: nowrap; font-size: 13px; }
        .rg-cambio s, .rg-cambio b { display: block; }
        .rg-cambio s { color: #8A9899; }
        .rg-dias { font-size: 11.5px; color: #6C7A7B; display: block; }
        .rg-motivo-txt { min-width: 180px; max-width: 320px; white-space: normal; }
        .rg-pie-tabla { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
        .rg-pie-tabla .pagination { margin: 0; }
        .rg-otro { color: #5A5446; }
        .rg-ratif { margin-top: 12px; padding: 10px 12px; border: 1px dashed #D5DDDD; border-radius: 8px; background: #F7F9F9; font-size: 13.5px; }
        .rg-ratif__actual { color: #6C7A7B; font-size: 12.5px; }
        .rg-ratif__cerrada { color: #6C7A7B; font-size: 12.5px; }

        /* Diálogo: sede y trámite (sólo Super Usuario) */
        .rg-cambiar { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 12px; padding-bottom: 14px; margin-bottom: 14px; border-bottom: 1px solid #E3E8E8; }
        .rg-cambiar .form-label { font-size: 12.5px; font-weight: 700; color: #5B6B6C; margin-bottom: 3px; }
        .rg-cambiar__nota { grid-column: 1 / -1; margin: 0; padding: 8px 10px; border-radius: 8px; background: #FBF7EA; color: #5A4A14; font-size: 12.5px; }
        @media (max-width: 520px) { .rg-cambiar { grid-template-columns: 1fr; } }
        @media (max-width: 640px) { .rg-filtros__grid .rg-filtros__buscar { grid-column: auto; } }
    </style>
    @include('turnos._horario_estilos')
@endsection

@section('content')
@php
    use App\Support\CitasCancelacion as CC;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $esMov  = $f['vista'] === 'movimientos';
    $ruta   = fn (array $cambios = []) => route('citas_cancelacion.index', CC::query($f, $cambios));
    $hoyC   = Carbon::today();
    $diaTxt = fn ($d) => Str::ucfirst(Carbon::parse($d)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    $rango  = $f['unDia']
        ? $diaTxt($f['desde']).($f['desde'] === $hoy ? ' (hoy)' : '')
        : 'Del '.Carbon::parse($f['desde'])->format('d/m/Y').' al '.Carbon::parse($f['hasta'])->format('d/m/Y');

    // Atajos de fechas: [desde, hasta].
    $atajos = $esMov ? [
        'Hoy'             => [$hoy, $hoy],
        'Últimos 7 días'  => [$hoyC->copy()->subDays(6)->toDateString(), $hoy],
        'Últimos 30 días' => [$hoyC->copy()->subDays(29)->toDateString(), $hoy],
        'Este mes'        => [$hoyC->copy()->startOfMonth()->toDateString(), $hoy],
        'Este año'        => [$hoyC->copy()->startOfYear()->toDateString(), $hoy],
    ] : [
        'Hoy'             => [$hoy, $hoy],
        'Mañana'          => [$hoyC->copy()->addDay()->toDateString(), $hoyC->copy()->addDay()->toDateString()],
        'Esta semana'     => [$hoyC->copy()->startOfWeek()->toDateString(), $hoyC->copy()->startOfWeek()->addDays(4)->toDateString()],
        'Próximos 7 días' => [$hoy, $hoyC->copy()->addDays(6)->toDateString()],
        'Este mes'        => [$hoyC->copy()->startOfMonth()->toDateString(), $hoyC->copy()->endOfMonth()->toDateString()],
    ];

    $hayFiltros = $f['buscando'] || array_filter([$f['sede'], $f['tramite'], $f['estatus'], $f['origen'], $f['movimiento'], $f['usuario']]);
    $cambiaVista = fn (string $vista) => route('citas_cancelacion.index', array_filter([
        'vista' => $vista === 'citas' ? null : $vista, 'sede' => $f['sede'], 'tramite' => $f['tramite'], 'origen' => $f['origen'],
    ]));
    $barras = function (array $conteo, ?string $filtro = null) use ($ruta) {
        $max = max($conteo ?: [1]);
        $html = '';
        foreach ($conteo as $nombre => $n) {
            $p = round($n * 100 / max($max, 1));
            $etiqueta = e($nombre);
            $html .= '<div class="rg-barra-fila" style="--p: '.$p.'%">'
                .($filtro ? '<a href="'.e($ruta([$filtro => $nombre, 'page' => null])).'" title="Filtrar por '.$etiqueta.'">'.$etiqueta.'</a>' : '<span class="rg-barra-nombre">'.$etiqueta.'</span>')
                .'<b>'.$n.'</b><i></i></div>';
        }

        return $html ?: '<p class="rg-sin-accion mb-0">Sin datos.</p>';
    };
@endphp
<section class="section">
    <div class="section-header d-flex justify-content-between flex-wrap gap-2">
        <h3 class="page__heading">Cancelación/Reagenda de citas</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('citas_cancelacion.descargar', CC::query($f)) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1" aria-hidden="true"></i>Descargar Excel
            </a>
            @role('Super Usuario')
                <a href="{{ route('administracion_historial', 'cancelacion_reagenda_cita') }}" class="btn btn-outline-secondary btn-sm">Historial</a>
            @endrole
        </div>
    </div>
    <div class="section-body">
        <div class="card"><div class="card-body">

            @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $color)
                @if (session($clave))
                    <div class="alert alert-{{ $color }} alert-dismissible fade show" role="alert">
                        {{ session($clave) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            @endforeach
            @if ($errors->any())
                <div class="alert alert-dark"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <ul class="rg-pestanas" role="tablist">
                <li><a href="{{ $cambiaVista('citas') }}" class="{{ $esMov ? '' : 'is-activa' }}" @if (! $esMov) aria-current="page" @endif><i class="bi bi-calendar2-week" aria-hidden="true"></i> Citas</a></li>
                <li><a href="{{ $cambiaVista('movimientos') }}" class="{{ $esMov ? 'is-activa' : '' }}" @if ($esMov) aria-current="page" @endif><i class="bi bi-clock-history" aria-hidden="true"></i> Movimientos</a></li>
            </ul>

            {{-- Filtros: los selectores se aplican solos; fechas y búsqueda, con "Filtrar". --}}
            <form method="GET" class="rg-filtros" id="rgFiltros">
                @if ($esMov)<input type="hidden" name="vista" value="movimientos">@endif
                <div class="rg-filtros__grid">
                    <div class="rg-filtros__buscar">
                        <label for="rgBuscar">Buscar</label>
                        <input type="search" id="rgBuscar" name="buscar" value="{{ $f['buscar'] }}" class="form-control" minlength="3"
                               placeholder="{{ $esMov ? 'Folio, CURP o nombre' : 'Folio, CURP, nombre, correo o teléfono' }}">
                    </div>
                    <div>
                        <label for="rgDesde">{{ $esMov ? 'Movimientos desde' : 'Citas desde' }}</label>
                        <input type="date" id="rgDesde" name="desde" value="{{ $f['desde'] }}" class="form-control">
                    </div>
                    <div>
                        <label for="rgHasta">Hasta</label>
                        <input type="date" id="rgHasta" name="hasta" value="{{ $f['hasta'] }}" class="form-control">
                    </div>
                    <div>
                        <label for="rgSede">Sede</label>
                        <select id="rgSede" name="sede" class="form-select" data-auto>
                            <option value="">{{ count($sedes) > 1 ? 'Todas' : 'La mía' }}</option>
                            @foreach ($sedes as $s)<option value="{{ $s }}" @selected($f['sede'] === $s)>{{ $s }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="rgTramite">Trámite</label>
                        <select id="rgTramite" name="tramite" class="form-select" data-auto>
                            <option value="">Todos</option>
                            @foreach (array_keys(CC::TRAMITES) as $t)<option value="{{ $t }}" @selected($f['tramite'] === $t)>{{ $t }}</option>@endforeach
                        </select>
                    </div>
                    @if ($esMov)
                        <div>
                            <label for="rgMovimiento">Movimiento</label>
                            <select id="rgMovimiento" name="movimiento" class="form-select" data-auto>
                                <option value="">Todos</option>
                                @foreach (CC::MOVIMIENTOS as $clave => $texto)<option value="{{ $clave }}" @selected($f['movimiento'] === $clave)>{{ $texto }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="rgUsuario">Hecho por</label>
                            <select id="rgUsuario" name="usuario" class="form-select" data-auto>
                                <option value="">Cualquiera</option>
                                @foreach ($usuarios as $id => $nombre)<option value="{{ $id }}" @selected($f['usuario'] === $id)>{{ $nombre }}</option>@endforeach
                            </select>
                        </div>
                    @else
                        <div>
                            <label for="rgEstatus">Estatus</label>
                            <select id="rgEstatus" name="estatus" class="form-select" data-auto>
                                <option value="">Todos</option>
                                @foreach (CC::ESTATUS as $clave => $texto)<option value="{{ $clave }}" @selected($f['estatus'] === $clave)>{{ $texto }}</option>@endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="rgOrigen">Origen</label>
                        <select id="rgOrigen" name="origen" class="form-select" data-auto>
                            <option value="">Todos</option>
                            @foreach (CC::ORIGENES as $clave => $texto)<option value="{{ $clave }}" @selected($f['origen'] === $clave)>{{ $texto }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="rg-filtros__pie">
                    <div class="rg-atajos" aria-label="Rangos rápidos">
                        @foreach ($atajos as $texto => [$d, $h])
                            <a href="{{ $ruta(['desde' => $d, 'hasta' => $h, 'buscar' => '', 'page' => null]) }}"
                               class="rg-atajo {{ ! $f['buscando'] && $f['desde'] === $d && $f['hasta'] === $h ? 'is-activo' : '' }}">{{ $texto }}</a>
                        @endforeach
                    </div>
                    <div class="rg-filtros__botones">
                        @if ($hayFiltros)
                            <a href="{{ route('citas_cancelacion.index', $esMov ? ['vista' => 'movimientos'] : []) }}" class="btn btn-outline-secondary btn-sm">Limpiar filtros</a>
                        @endif
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Filtrar</button>
                    </div>
                </div>
                @if ($f['buscando'])
                    <p class="rg-nota"><i class="bi bi-info-circle" aria-hidden="true"></i> La búsqueda incluye todas las fechas; los demás filtros sí se aplican.</p>
                @elseif ($f['recortado'])
                    <p class="rg-nota"><i class="bi bi-info-circle" aria-hidden="true"></i> Se muestra hasta un año a partir de la fecha inicial.</p>
                @endif
            </form>

            @if ($esMov)
                @include('administracion.citas_cancelacion._movimientos')
            @else
                @include('administracion.citas_cancelacion._citas')
            @endif
        </div></div>
    </div>
</section>
@endsection

@push('modals')
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><form method="POST" class="modal-content">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Cancelar cita <span data-folio></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body">
            <label class="form-label">Motivo</label>
            <textarea name="motivo" class="form-control" rows="3" minlength="10" maxlength="500" required></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button class="btn btn-danger">Cancelar cita</button>
        </div>
    </form></div>
</div>

{{--
    Reagendar: el mismo selector de día y hora de /generar-cita (estilos en
    turnos._horario_estilos) y los mismos datos, /api/obtenerTurnosDisponibles,
    así que sólo ofrece horarios con lugar. Al guardar se vuelve a revisar.
--}}
<div class="modal fade" id="modalReagendar" tabindex="-1" aria-labelledby="rgTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg hc-dialogo"><form method="POST" class="modal-content">
        @csrf
        <input type="hidden" name="fecha">
        <input type="hidden" name="hora">
        <div class="modal-header rg-encabezado py-3">
            <div>
                <h5 class="modal-title text-white mb-0" id="rgTitulo"><i class="bi bi-calendar-week me-2" aria-hidden="true"></i>{{ \App\Http\Controllers\CancelacionCitasController::puedeCambiarTodo(auth()->user()) ? 'Cambiar cita' : 'Reagendar cita' }} <span data-folio></span></h5>
                <span class="hc-contexto" data-contexto></span>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body hc-cuerpo">
            <div class="rg-actual" data-actual></div>
            <p class="rg-cambiar__nota mb-3" data-aviso-confirmada hidden>
                <i class="bi bi-person-check" aria-hidden="true"></i> Esta persona <b>ya validó su cita</b>. Si la pasas a otro día, quedará pendiente
                y tendrá que validar su QR al llegar; si eliges otra hora de hoy, sigue confirmada y su módulo la llamará a la nueva hora.
            </p>

            @if (\App\Http\Controllers\CancelacionCitasController::puedeCambiarTodo(auth()->user()))
                {{-- Sólo el Super Usuario: también la sede y el trámite; los horarios se buscan en el calendario nuevo. --}}
                <div class="rg-cambiar">
                    <div>
                        <label class="form-label" for="rgSedeNueva">Sede</label>
                        <select id="rgSedeNueva" name="sede" class="form-select" data-sede-nueva>
                            @foreach (array_keys(\App\Support\FolioCita::SEDES) as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="rgTipoNuevo">Trámite</label>
                        <select id="rgTipoNuevo" name="tipo" class="form-select" data-tipo-nuevo>
                            @foreach (array_keys(\App\Support\CitasCancelacion::TRAMITES) as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                        </select>
                    </div>
                    <p class="rg-cambiar__nota" data-nota-cambio hidden>
                        <i class="bi bi-info-circle" aria-hidden="true"></i> Se muestran los horarios libres de <b data-destino></b>.
                        Al guardar, la cita recibe un folio nuevo y se le manda su acuse; su código QR sigue sirviendo.
                    </p>
                </div>
            @endif

            <div class="hc-semana">
                <button type="button" class="hc-flecha" data-anterior aria-label="Semana anterior"><i class="bi bi-chevron-left"></i></button>
                <span class="hc-semana__texto" data-semana aria-live="polite"></span>
                <button type="button" class="hc-flecha" data-siguiente aria-label="Semana siguiente"><i class="bi bi-chevron-right"></i></button>
            </div>
            <div class="hc-dias" data-dias role="tablist" aria-label="Días de la semana"></div>
            <div class="hc-horas" data-horas aria-live="polite"></div>

            <div class="rg-motivo">
                <label class="form-label" for="rgMotivo">{{ \App\Http\Controllers\CancelacionCitasController::puedeCambiarTodo(auth()->user()) ? 'Motivo del cambio' : 'Motivo de la reagenda' }}</label>
                <textarea id="rgMotivo" name="motivo" class="form-control" rows="2" minlength="10" maxlength="500" required></textarea>

                {{-- Cita de una ratificación: su fecha y hora sólo se mueven si se marca. --}}
                <div class="rg-ratif" data-ratif hidden>
                    <div class="form-check" data-ratif-opcion>
                        <input class="form-check-input" type="checkbox" name="actualizar_ratificacion" value="1" id="rgActRatif">
                        <label class="form-check-label" for="rgActRatif">
                            Actualizar también la fecha y hora de la ratificación <b data-ratif-nue></b>
                            <span class="rg-ratif__actual">(hoy: <span data-ratif-cuando></span>)</span>
                        </label>
                    </div>
                    <p class="rg-ratif__cerrada mb-0" data-ratif-cerrada hidden>
                        <i class="bi bi-lock" aria-hidden="true"></i> La ratificación <b data-ratif-nue2></b> está <span data-ratif-estatus></span>: su fecha no se mueve.
                    </p>
                </div>
            </div>
        </div>

        <div class="modal-footer hc-pie">
            <div class="hc-elegido" data-elegido>Toca un horario para elegirlo.</div>
            <div class="hc-pie__botones">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" data-guardar disabled><i class="bi bi-check2 me-1" aria-hidden="true"></i>{{ \App\Http\Controllers\CancelacionCitasController::puedeCambiarTodo(auth()->user()) ? 'Guardar cambios' : 'Reagendar' }}</button>
            </div>
        </div>
    </form></div>
</div>
@endpush

@push('scripts')
<script>
    // Cada botón trae la URL de su cita; el diálogo la toma al abrirse.
    document.querySelectorAll('#modalCancelar, #modalReagendar').forEach(modal => {
        modal.addEventListener('show.bs.modal', e => {
            const boton = e.relatedTarget;
            modal.querySelector('form').reset();
            modal.querySelector('form').action = boton.dataset.accion;
            modal.querySelector('[data-folio]').textContent = boton.dataset.folio;
        });
    });

    // Selector de día y hora: la misma tira de días de /generar-cita
    // (_cita_scripts), con la sede, el trámite y la excepción de la cita.
    (function () {
        const URL_HORARIOS = "{{ url('/api/obtenerTurnosDisponibles') }}";
        const SEMANAS_A_BUSCAR = 8;    // al abrir, busca la primera semana con lugar
        const modal = document.getElementById('modalReagendar');
        const $ = s => modal.querySelector(s);
        const el = {
            semana: $('[data-semana]'), anterior: $('[data-anterior]'), siguiente: $('[data-siguiente]'),
            dias: $('[data-dias]'), horas: $('[data-horas]'), elegido: $('[data-elegido]'),
            contexto: $('[data-contexto]'), actual: $('[data-actual]'), guardar: $('[data-guardar]'),
            fecha: $('[name=fecha]'), hora: $('[name=hora]'),
            // Sólo existen para el Super Usuario.
            sedeNueva: $('[data-sede-nueva]'), tipoNuevo: $('[data-tipo-nuevo]'), nota: $('[data-nota-cambio]'), destino: $('[data-destino]')
        };
        let cita = null;            // sede, tipo, excepcion, fecha y hora con que se buscan horarios
        let original = null;        // cómo está la cita: sede y trámite del catálogo y tal como están guardados
        let lunes = null, lunesMinimo = null, porDia = {}, diaActivo = null, elegido = null;
        let peticion = 0;           // descarta respuestas viejas si se navega rápido

        const pad = n => String(n).padStart(2, '0');
        const ymd = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        const lunesDe = d => { const x = new Date(d.getFullYear(), d.getMonth(), d.getDate()); x.setDate(x.getDate() - (x.getDay() + 6) % 7); return x; };
        const masDias = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
        const fmt = (d, o) => d.toLocaleDateString('es-MX', o);
        const largo = f => { const t = fmt(new Date(f + 'T00:00:00'), { weekday: 'long', day: 'numeric', month: 'long' }).replace(',', ''); return t.charAt(0).toUpperCase() + t.slice(1); };
        const nodo = (tag, clase, texto) => { const n = document.createElement(tag); if (clase) n.className = clase; if (texto != null) n.textContent = texto; return n; };

        async function traerSemana(inicio) {
            const q = new URLSearchParams({ sede: cita.sede, tipo: cita.tipo, excepcion: cita.excepcion, start: ymd(inicio), end: ymd(masDias(inicio, 4)) });
            const r = await fetch(URL_HORARIOS + '?' + q.toString(), { headers: { Accept: 'application/json' } });
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const dias = {};
            for (let i = 0; i < 5; i++) dias[ymd(masDias(inicio, i))] = [];
            (await r.json()).forEach(function (e) {
                const estado = e.extendedProps && e.extendedProps.estado;
                if (estado !== 'disponible' && estado !== 'turnos') return;
                const [f, h] = String(e.start).split('T');
                // Su propio horario no es una opción si nada más cambia: sería no moverla.
                if (dias[f] && !(sinCambios() && f === original.fecha && h.slice(0, 5) === original.hora)) dias[f].push(h.slice(0, 5));
            });
            // Si sólo cambia el trámite dentro del mismo calendario, su lugar sigue siendo suyo
            // aunque el horario se vea lleno (lo ocupa ella misma).
            if (!sinCambios() && mismoCalendario() && dias[original.fecha] && !dias[original.fecha].includes(original.hora)
                && new Date(original.fecha + 'T' + original.hora) > new Date()) {
                dias[original.fecha].push(original.hora);
            }
            Object.keys(dias).forEach(k => dias[k].sort());
            return dias;
        }

        // Sede y trámite elegidos (si no es Super Usuario, los de la cita).
        const sedeElegida = () => el.sedeNueva ? el.sedeNueva.value : original.sede;
        const tipoElegido = () => el.tipoNuevo ? el.tipoNuevo.value : original.tipo;
        const grupo = t => t === 'Ratificación' ? 'Ratificación' : 'Solicitud';
        const sinCambios = () => sedeElegida() === original.sede && tipoElegido() === original.tipo;
        const mismoCalendario = () => original.excepcion === 'Si'
            || (sedeElegida() === original.sede && grupo(tipoElegido()) === grupo(original.tipo));

        function contexto() {
            el.contexto.textContent = 'Oficina ' + sedeElegida() + ' · ' + tipoElegido() + (original.excepcion === 'Si' ? ' · Excepción' : '');
        }

        // El Super Usuario cambió la sede o el trámite: se buscan horarios en ese calendario.
        function cambiarDestino() {
            const cambia = !sinCambios();
            cita.sede = sedeElegida() === original.sede ? original.sedeGuardada : sedeElegida();
            cita.tipo = tipoElegido() === original.tipo ? original.tipoGuardado : tipoElegido();
            el.nota.hidden = !cambia;
            el.destino.textContent = tipoElegido() + ' en ' + sedeElegida();
            contexto();
            elegido = null;
            el.fecha.value = el.hora.value = '';
            el.guardar.disabled = true;
            el.elegido.textContent = 'Toca un horario para elegirlo.';
            irA(lunesMinimo, true);
        }

        function mensaje(icono, texto, accion) {
            el.horas.textContent = '';
            const m = nodo('div', 'hc-mensaje');
            m.appendChild(nodo('i', 'bi ' + icono));
            m.appendChild(nodo('div', null, texto));
            if (accion) {
                const b = nodo('button', 'btn btn-outline-secondary btn-sm', accion.texto);
                b.type = 'button';
                b.addEventListener('click', accion.fn);
                m.appendChild(b);
            }
            el.horas.appendChild(m);
        }

        function pintarSemana() {
            const viernes = masDias(lunes, 4);
            el.semana.textContent = lunes.getMonth() === viernes.getMonth()
                ? lunes.getDate() + ' al ' + viernes.getDate() + ' de ' + fmt(viernes, { month: 'long', year: 'numeric' })
                : fmt(lunes, { day: 'numeric', month: 'short' }) + ' al ' + fmt(viernes, { day: 'numeric', month: 'short', year: 'numeric' });
            el.anterior.disabled = lunes <= lunesMinimo;

            el.dias.textContent = '';
            Object.keys(porDia).forEach(function (f) {
                const d = new Date(f + 'T00:00:00');
                const libres = porDia[f].length;
                const b = nodo('button', 'hc-dia' + (f === diaActivo ? ' is-activo' : ''));
                b.type = 'button';
                b.setAttribute('role', 'tab');
                b.setAttribute('aria-selected', f === diaActivo ? 'true' : 'false');
                b.disabled = libres === 0;
                b.appendChild(nodo('span', 'hc-dia__semana', fmt(d, { weekday: 'short' }).replace('.', '')));
                b.appendChild(nodo('span', 'hc-dia__numero', String(d.getDate())));
                b.appendChild(nodo('span', 'hc-dia__mes', fmt(d, { month: 'short' }).replace('.', '')));
                b.appendChild(nodo('span', 'hc-dia__libres', libres ? (libres === 1 ? '1 horario' : libres + ' horarios') : 'Sin lugar'));
                b.addEventListener('click', function () { diaActivo = f; pintarSemana(); pintarHoras(); });
                el.dias.appendChild(b);
            });
        }

        function pintarHoras() {
            if (!diaActivo) {
                mensaje('bi-calendar-x', 'No hay horarios libres esta semana.', { texto: 'Ver la semana siguiente', fn: () => irA(masDias(lunes, 7)) });
                return;
            }
            el.horas.textContent = '';
            const horas = porDia[diaActivo];
            [['Mañana', horas.filter(h => h < '13:00')], ['Tarde', horas.filter(h => h >= '13:00')]].forEach(function ([titulo, lista]) {
                if (!lista.length) return;
                const g = nodo('div', 'hc-grupo');
                g.appendChild(nodo('div', 'hc-grupo__titulo', titulo));
                const cont = nodo('div', 'hc-grupo__horas');
                lista.forEach(function (h) {
                    const b = nodo('button', 'hc-hora', h);
                    b.type = 'button';
                    const es = elegido && elegido.fecha === diaActivo && elegido.hora === h;
                    if (es) b.classList.add('is-elegida');
                    b.setAttribute('aria-pressed', es ? 'true' : 'false');
                    b.addEventListener('click', () => elegir(diaActivo, h));
                    cont.appendChild(b);
                });
                g.appendChild(cont);
                el.horas.appendChild(g);
            });
        }

        function elegir(fecha, hora) {
            elegido = { fecha, hora };
            el.fecha.value = fecha;
            el.hora.value = hora;
            el.elegido.textContent = '';
            el.elegido.appendChild(document.createTextNode('Nuevo horario: '));
            el.elegido.appendChild(nodo('strong', null, largo(fecha) + ' · ' + hora + ' h'
                + (sinCambios() ? '' : ' · ' + tipoElegido() + ' en ' + sedeElegida())));
            el.guardar.disabled = false;
            pintarHoras();
        }

        async function irA(inicio, buscarHaciaAdelante) {
            const mia = ++peticion;
            lunes = inicio;
            diaActivo = null;
            el.dias.textContent = '';
            el.semana.textContent = 'Buscando horarios…';
            mensaje('bi-hourglass-split', 'Buscando horarios disponibles…');
            try {
                let intentos = buscarHaciaAdelante ? SEMANAS_A_BUSCAR : 1;
                let dias = await traerSemana(lunes);
                while (--intentos > 0 && !Object.values(dias).some(h => h.length)) {
                    lunes = masDias(lunes, 7);
                    dias = await traerSemana(lunes);
                    if (mia !== peticion) return;
                }
                if (mia !== peticion) return;
                porDia = dias;
                diaActivo = Object.keys(porDia).find(f => porDia[f].length) || null;
                pintarSemana();
                pintarHoras();
            } catch (e) {
                if (mia !== peticion) return;
                el.semana.textContent = '';
                mensaje('bi-wifi-off', 'No se pudieron cargar los horarios.', { texto: 'Intentar de nuevo', fn: () => irA(lunes) });
            }
        }

        el.anterior.addEventListener('click', () => irA(masDias(lunes, -7)));
        if (el.sedeNueva) {
            el.sedeNueva.addEventListener('change', cambiarDestino);
            el.tipoNuevo.addEventListener('change', cambiarDestino);
        }
        el.siguiente.addEventListener('click', () => irA(masDias(lunes, 7)));

        modal.addEventListener('show.bs.modal', function (e) {
            const b = e.relatedTarget.dataset;
            cita = { sede: b.sede, tipo: b.tipo, excepcion: b.excepcion, fecha: b.fecha, hora: b.hora };
            original = { sede: b.sedeCatalogo || b.sede, tipo: b.tramite || b.tipo, sedeGuardada: b.sede, tipoGuardado: b.tipo,
                         excepcion: b.excepcion, fecha: b.fecha, hora: b.hora };
            if (el.sedeNueva) {
                el.sedeNueva.value = original.sede;
                el.tipoNuevo.value = original.tipo;
                el.nota.hidden = true;
            }
            elegido = null;
            el.fecha.value = el.hora.value = '';
            el.guardar.disabled = true;
            el.elegido.textContent = 'Toca un horario para elegirlo.';
            contexto();
            el.actual.textContent = '';
            const avisoConfirmada = modal.querySelector('[data-aviso-confirmada]');
            if (avisoConfirmada) avisoConfirmada.hidden = b.confirmada !== 'Si';
            // Ratificación ligada: la casilla sólo aparece si sigue abierta (y empieza sin marcar).
            const ratif = modal.querySelector('[data-ratif]');
            if (ratif) {
                const hay = !!b.ratifNue, abierta = b.ratifAbierta === 'Si';
                ratif.hidden = !hay;
                ratif.querySelector('[data-ratif-opcion]').hidden = !abierta;
                ratif.querySelector('[data-ratif-cerrada]').hidden = !hay || abierta;
                ratif.querySelector('#rgActRatif').checked = false;
                ratif.querySelector('#rgActRatif').disabled = !abierta;
                ratif.querySelector('[data-ratif-nue]').textContent = b.ratifNue || '';
                ratif.querySelector('[data-ratif-nue2]').textContent = b.ratifNue || '';
                ratif.querySelector('[data-ratif-cuando]').textContent = b.ratifCuando || '';
                ratif.querySelector('[data-ratif-estatus]').textContent = b.ratifEstatus || '';
            }
            el.actual.appendChild(document.createTextNode('Cita actual: '));
            el.actual.appendChild(nodo('strong', null, largo(cita.fecha) + ' · ' + cita.hora + ' h'));

            // Sábado o domingo: la primera semana útil es la siguiente.
            const hoy = new Date();
            lunesMinimo = lunesDe(hoy.getDay() === 0 || hoy.getDay() === 6 ? masDias(hoy, 2) : hoy);
            irA(lunesMinimo, true);
        });
    })();
</script>
@endpush

@push('scripts')
<script>
    // Filtros: al cambiar un selector se aplica solo; la página regresa a la 1.
    (function () {
        const form = document.getElementById('rgFiltros');
        if (!form) return;
        form.querySelectorAll('select[data-auto]').forEach(s => s.addEventListener('change', () => form.requestSubmit ? form.requestSubmit() : form.submit()));
        // Un rango al revés se acomoda solo.
        form.addEventListener('submit', function () {
            const d = form.elements.desde, h = form.elements.hasta;
            if (d.value && h.value && h.value < d.value) { const t = d.value; d.value = h.value; h.value = t; }
            // Vacíos fuera de la dirección.
            form.querySelectorAll('input, select').forEach(el => { if (el.name && el.value === '') el.disabled = true; });
        });
    })();
</script>
@endpush
