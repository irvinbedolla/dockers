@extends('layouts.app')
@section('title', 'Módulos y citas')

{{--
    Administración → Módulos. Arriba se elige sede, día y origen; abajo, una
    tarjeta por módulo con quién lo atiende, qué recibe y sus citas del día.
    Editar un módulo cambia la asignación automática de aquí en adelante.
--}}

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $fechaLarga = Str::ucfirst(Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    $anterior   = Carbon::parse($fecha)->subDay()->toDateString();
    $siguiente  = Carbon::parse($fecha)->addDay()->toDateString();
    $url = fn (array $cambios) => route('modulos.index', array_filter(
        array_merge(['sede' => $sede, 'fecha' => $fecha, 'origen' => $origen], $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $iniciales = function (?string $nombre) {
        $partes = preg_split('/\s+/', trim((string) $nombre));
        return mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1).mb_substr($partes[1] ?? '', 0, 1));
    };
    $nombreBonito = fn (?string $n) => $n ? Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim($n)))) : null;
    $sexos = ['H' => 'Hombre', 'M' => 'Mujer', 'NB' => 'No binario', 'LGBTTTIQ' => 'LGBTTTIQ'];
    $horasComida = [];
    for ($t = strtotime('08:30'); $t <= strtotime('17:30'); $t += 1800) { $horasComida[] = date('H:i', $t); }
    $sinModulo = $porModulo->get('sin', collect());
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .md-barra {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
            align-items: center;
            justify-content: space-between;
        }
        .md-sedes { display: flex; flex-wrap: wrap; gap: 6px; }
        .md-sede {
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid var(--tn-borde);
            background: #fff;
            color: var(--tn-suave);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }
        .md-sede:hover { color: var(--tn-verde); border-color: var(--tn-verde-claro); }
        .md-sede.is-activa { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .md-dia { display: flex; align-items: center; gap: 6px; }
        .md-dia .form-control { width: 160px; }

        .md-resumen { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 10px; margin: 18px 0 12px; }
        .md-resumen h4 { margin: 0; font-size: 18px; font-weight: 700; color: var(--tn-tinta); }
        .md-filtros { display: flex; flex-wrap: wrap; gap: 6px; }

        .md-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 14px;
            align-items: start;
        }

        .md-modulo { padding: 0; overflow: hidden; }
        .md-modulo.is-inactivo { opacity: .65; }
        .md-cabeza { padding: 16px 18px 12px; border-bottom: 1px solid var(--tn-borde); }
        .md-cabeza__fila { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
        .md-nombre { margin: 0; font-size: 16px; font-weight: 700; color: var(--tn-tinta); }
        .md-num {
            min-width: 26px;
            padding: 1px 8px;
            border-radius: 999px;
            background: var(--tn-fondo);
            color: var(--tn-verde);
            font-size: 12.5px;
            font-weight: 700;
            text-align: center;
        }

        .md-persona { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        .md-avatar {
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #E7ECEC;
            color: var(--tn-verde);
            font-size: 12px;
            font-weight: 700;
        }
        .md-avatar--vacio { background: #FDEBDD; color: #B4530F; }
        .md-persona__nombre { font-size: 13.5px; font-weight: 600; color: var(--tn-tinta); line-height: 1.2; }
        .md-persona__nota { font-size: 12px; color: var(--tn-tenue); }

        .md-chips { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 10px; }
        .md-chip {
            padding: 1px 8px;
            border-radius: 6px;
            background: #EEF2F2;
            color: var(--tn-verde);
            font-size: 11.5px;
            font-weight: 700;
        }
        .md-chip--respaldo { background: #fff; border: 1px dashed var(--tn-verde-claro); color: var(--tn-suave); font-weight: 600; }
        .md-chip--comida { background: #FBF4E1; color: #8A6A12; }
        .md-chip--inactivo { background: #F6E4EA; color: #8C1D40; }
        .md-chip--excepcion { background: #EDE7F6; color: #5B3F99; }
        .md-modulo.is-excepcion { border-top: 3px solid #7B5EA7; }
        .md-clase { display: flex; flex-wrap: wrap; gap: 6px 16px; }

        /* Switch "Recibe citas" de cada módulo y qué se pausa. */
        .md-linea {
            display: flex; align-items: center; flex-wrap: wrap; gap: 4px 10px;
            margin: 12px 0 0; padding: 7px 10px; border-radius: 8px;
            background: #EEF6F0; border: 1px solid #CFE6D6;
        }
        .md-linea .form-check { display: flex; align-items: center; gap: 8px; min-height: 0; padding-left: 0; }
        .md-linea .form-check-input { margin: 0; float: none; width: 2.3em; height: 1.2em; cursor: pointer; }
        .md-linea .form-check-input:checked { background-color: #2E8B57; border-color: #2E8B57; }
        .md-linea .form-check-label { font-size: 13px; font-weight: 700; color: var(--tn-tinta); cursor: pointer; }
        .md-linea__nota { font-size: 12px; color: var(--tn-tenue); }
        .md-linea.is-pausa { background: #F6E4EA; border-color: #EBC3D0; }
        .md-linea.is-pausa .md-linea__nota { color: #8C1D40; font-weight: 600; }
        .md-linea.is-pausa-todo { background: #F9D6DD; border-color: #E09AAD; }
        .md-linea + .md-linea { margin-top: 6px; }
        .md-linea.is-programada { background: #FBF4E1; border-color: #EBDDB0; }
        .md-linea.is-programada .md-linea__nota { color: #8A6A12; font-weight: 600; }
        .md-linea.is-heredado { background: #F3F5F5; border-color: #E0E5E5; }
        .md-linea__fecha { width: auto; min-width: 150px; padding: 2px 8px; font-size: 12.5px; }
        .md-linea__acciones { flex-basis: 100%; display: flex; align-items: center; gap: 8px; }
        .md-linea__acciones .btn { font-size: 12px; padding: 2px 10px; }
        /* .d-flex lleva !important: sin esto el aviso no se oculta. */
        #mdAvisoPausa[hidden] { display: none !important; }
        .md-linea__modo { flex-basis: 100%; display: flex; flex-wrap: wrap; align-items: center; gap: 4px 14px; font-size: 12.5px; color: var(--tn-suave); }
        .md-linea__modo b { font-weight: 700; color: var(--tn-tinta); }
        .md-linea__op { display: inline-flex; align-items: center; gap: 5px; cursor: pointer; margin: 0; }
        .md-linea__op input { margin: 0; accent-color: #8C1D40; cursor: pointer; }
        .md-linea.is-error .md-linea__nota { color: #A3162E; font-weight: 600; }
        .md-linea.is-guardando { opacity: .6; }

        .md-citas { list-style: none; margin: 0; padding: 0; }
        .md-cita { border-top: 1px solid var(--tn-borde); }
        .md-cita:first-child { border-top: 0; }
        .md-cita summary {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 10px 18px;
            cursor: pointer;
            list-style: none;
        }
        .md-cita summary::-webkit-details-marker { display: none; }
        .md-cita summary:hover { background: #FAFBFB; }
        .md-cita[open] summary { background: var(--tn-fondo); }
        .md-hora { font-weight: 700; font-size: 14px; color: var(--tn-verde); font-variant-numeric: tabular-nums; }
        .md-quien { min-width: 0; }
        .md-quien__nombre { display: block; font-size: 13.5px; font-weight: 600; color: var(--tn-tinta); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .md-quien__detalle { display: flex; flex-wrap: wrap; gap: 4px 6px; align-items: center; font-size: 12px; color: var(--tn-tenue); }

        .md-datos { padding: 4px 18px 14px; background: var(--tn-fondo); }
        .md-datos dl {
            display: grid;
            grid-template-columns: 110px minmax(0, 1fr);
            gap: 4px 10px;
            margin: 0 0 10px;
            font-size: 12.5px;
        }
        .md-datos dt { color: var(--tn-tenue); font-weight: 600; }
        .md-datos dd { margin: 0; color: var(--tn-tinta); word-break: break-word; }
        .md-acciones { display: flex; flex-wrap: wrap; gap: 6px; }

        .md-vacio { padding: 16px 18px; margin: 0; font-size: 13px; color: var(--tn-tenue); }

        .md-nuevo {
            display: grid;
            place-items: center;
            gap: 6px;
            min-height: 160px;
            border: 2px dashed var(--tn-borde);
            border-radius: 12px;
            background: transparent;
            color: var(--tn-suave);
            font-weight: 600;
        }
        .md-nuevo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .md-nuevo i { font-size: 24px; }

        /* Límite de citas en línea (_limite_citas) */
        .md-limite__cab { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px 16px; }
        .md-limite__titulo { margin: 0 0 4px; font-size: 16px; font-weight: 700; color: var(--tn-tinta); display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
        .md-limite__titulo .bi { color: var(--tn-verde); }
        .md-limite__texto { font-size: 13.5px; color: var(--tn-suave); }
        .md-limite__acciones { display: flex; flex-wrap: wrap; gap: 6px; }
        .md-limite__hoy { display: flex; flex-wrap: wrap; gap: 6px 18px; margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--tn-borde); font-size: 13px; color: var(--tn-suave); }
        .md-limite__hoy b { color: var(--tn-tinta); }
        .md-limite__alerta, .md-limite__alerta b { color: #9A3A22 !important; }
        .md-limite__nota { font-size: 12.5px; color: #7A5D10; background: #FBF4E1; border-radius: 8px; padding: 8px 10px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('configuracion') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Administración
                </a>
                <h3 class="page__heading mb-0">Módulos y citas</h3>
            </div>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @include('administracion.modulos._limite_citas')

            <div class="tn-tarjeta md-barra">
                <nav class="md-sedes" aria-label="Sede">
                    @foreach ($sedes as $s)
                        <a class="md-sede {{ $s === $sede ? 'is-activa' : '' }}" href="{{ $url(['sede' => $s]) }}">{{ $s }}</a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('modulos.index') }}" class="md-dia" id="mdDia">
                    <input type="hidden" name="sede" value="{{ $sede }}">
                    @if ($origen)<input type="hidden" name="origen" value="{{ $origen }}">@endif
                    <a class="btn btn-outline-secondary" href="{{ $url(['fecha' => $anterior]) }}" aria-label="Día anterior"><i class="bi bi-chevron-left"></i></a>
                    <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" aria-label="Día">
                    <a class="btn btn-outline-secondary" href="{{ $url(['fecha' => $siguiente]) }}" aria-label="Día siguiente"><i class="bi bi-chevron-right"></i></a>
                    @if ($fecha !== $hoy)
                        <a class="btn btn-outline-secondary" href="{{ $url(['fecha' => $hoy]) }}">Hoy</a>
                    @endif
                </form>
            </div>

            <div class="md-resumen">
                <h4>{{ $fechaLarga }} · {{ $sede }}</h4>
                <div class="md-filtros">
                    <a class="tn-atajo {{ $origen ? '' : 'is-activo' }}" href="{{ $url(['origen' => null]) }}">Todas · {{ $conteo['total'] }}</a>
                    <a class="tn-atajo {{ $origen === 'linea' ? 'is-activo' : '' }}" href="{{ $url(['origen' => 'linea']) }}">En línea · {{ $conteo['linea'] }}</a>
                    <a class="tn-atajo {{ $origen === 'ventanilla' ? 'is-activo' : '' }}" href="{{ $url(['origen' => 'ventanilla']) }}">Ventanilla · {{ $conteo['ventanilla'] }}</a>
                </div>
            </div>

            @if ($enLineaMigrado)
                <div class="alert alert-warning d-flex gap-2 align-items-start py-2 mb-3" id="mdAvisoPausa" role="status" @if (! $sinEnLinea && ! $sinVentanilla) hidden @endif>
                    <i class="bi bi-pause-circle-fill mt-1" aria-hidden="true"></i>
                    <div>
                        <div data-canal="linea" @if (! $sinEnLinea) hidden @endif>
                            <b>En línea:</b> en {{ $sede }} todos los módulos que atienden <b data-lista>{{ implode(', ', $sinEnLinea) }}</b> están en pausa; el formulario público no ofrece horarios para eso.
                        </div>
                        <div data-canal="ventanilla" @if (! $sinVentanilla) hidden @endif>
                            <b>Ventanilla:</b> en {{ $sede }} todos los módulos que atienden <b data-lista>{{ implode(', ', $sinVentanilla) }}</b> están en pausa total; recepción no puede agendarlo.
                        </div>
                        <small class="d-block">Prende al menos un módulo para volver a ofrecer horarios.</small>
                    </div>
                </div>
            @endif

            <div class="md-grid">
                @foreach ($modulos as $modulo)
                    @php
                        $susCitas = $porModulo->get($modulo->id, collect());
                        $persona  = $modulo->persona;
                        $datosModulo = [
                            'id' => $modulo->id, 'nombre' => $modulo->nombre, 'user_id' => $modulo->user_id,
                            'tramites' => $modulo->tramites ?? [], 'respaldo' => $modulo->respaldo ?? [],
                            'hora_comida' => $modulo->hora_comida ? substr($modulo->hora_comida, 0, 5) : '',
                            'activo' => $modulo->activo, 'orden' => $modulo->orden,
                            'accion' => route('modulos.actualizar', $modulo),
                        ];
                    @endphp
                    <article class="tn-tarjeta md-modulo {{ $modulo->activo ? '' : 'is-inactivo' }} {{ $modulo->esDeExcepcion() ? 'is-excepcion' : '' }}">
                        <header class="md-cabeza">
                            <div class="md-cabeza__fila">
                                <div>
                                    <h5 class="md-nombre">{{ $modulo->nombre }}</h5>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="md-num" title="Citas este día">{{ $susCitas->count() }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar módulo"
                                            data-bs-toggle="modal" data-bs-target="#mdModalModulo"
                                            data-modulo="{{ json_encode($datosModulo, JSON_UNESCAPED_UNICODE) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="md-persona">
                                @if ($persona)
                                    <span class="md-avatar" aria-hidden="true">{{ $iniciales($persona->name) }}</span>
                                    <span>
                                        <span class="md-persona__nombre d-block">{{ $nombreBonito($persona->name) }}</span>
                                        @if ($persona->estatus !== 'Activo')
                                            <span class="md-persona__nota" style="color:#B4530F;">Cuenta inactiva: no recibe citas</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="md-avatar md-avatar--vacio" aria-hidden="true"><i class="bi bi-person-dash"></i></span>
                                    <span class="md-persona__nombre" style="color:#B4530F;">Sin persona asignada</span>
                                @endif
                            </div>

                            <div class="md-chips">
                                @if (! $modulo->activo)<span class="md-chip md-chip--inactivo">Inactivo</span>@endif
                                @foreach ($modulo->tramites ?? [] as $t)
                                    @if ($t === \App\Support\AsignadorModulos::TRAMITE_EXCEPCION)
                                        <span class="md-chip md-chip--excepcion" title="Recibe los casos de excepción de {{ $modulo->delegacion }}"><i class="bi bi-shield-heart"></i> Casos de excepción</span>
                                    @else
                                        <span class="md-chip">{{ $t }}</span>
                                    @endif
                                @endforeach
                                @foreach ($modulo->respaldo ?? [] as $t)<span class="md-chip md-chip--respaldo" title="Sólo si los demás están ocupados">{{ $t }} · respaldo</span>@endforeach
                                @if ($modulo->hora_comida)<span class="md-chip md-chip--comida"><i class="bi bi-cup-hot"></i> {{ substr($modulo->hora_comida, 0, 5) }}</span>@endif
                                @foreach (\App\Http\Controllers\ModulosController::tramitesSinPermiso($modulo) as $t)
                                    <span class="md-chip md-chip--inactivo" title="Su rol no tiene permisos para atender {{ $t }}. Asígnale la especialidad en Usuarios.">
                                        <i class="bi bi-exclamation-triangle"></i> Sin permiso: {{ $t }}
                                    </span>
                                @endforeach
                            </div>

                            @if ($enLineaMigrado)
                                @php $pausa = $modulo->pausa(); @endphp
                                {{-- Apagado: desde ese momento no se le asignan citas nuevas en línea (o tampoco de ventanilla). --}}
                                <form method="POST" action="{{ route('modulos.en_linea', $modulo) }}"
                                      class="md-linea js-pausa {{ $pausa ? 'is-pausa' : '' }} {{ $pausa === 'todas' ? 'is-pausa-todo' : '' }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="fecha_vista" value="{{ $fecha }}">
                                    <input type="hidden" name="origen_vista" value="{{ $origen }}">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" data-switch
                                               id="mdRecibe{{ $modulo->id }}" @checked(! $pausa)
                                               title="Apágalo para que ya no le lleguen citas nuevas">
                                        <label class="form-check-label" for="mdRecibe{{ $modulo->id }}">Recibe citas</label>
                                    </div>
                                    <span class="md-linea__nota" aria-live="polite">{{ $modulo->notaPausa() }}</span>
                                    <div class="md-linea__modo" role="radiogroup" aria-label="Qué se pausa" @if (! $pausa) hidden @endif>
                                        <b>Pausar:</b>
                                        <label class="md-linea__op"><input type="radio" name="modo{{ $modulo->id }}" value="linea" @checked($pausa !== 'todas')> Sólo citas en línea</label>
                                        @if ($ventanillaMigrada)
                                            <label class="md-linea__op"><input type="radio" name="modo{{ $modulo->id }}" value="todas" @checked($pausa === 'todas')> Todas, también ventanilla</label>
                                        @endif
                                    </div>
                                    <noscript>
                                        <select name="pausa" class="form-select form-select-sm w-auto">
                                            <option value="ninguna" @selected(! $pausa)>Recibe todas</option>
                                            <option value="linea" @selected($pausa === 'linea')>Pausar sólo en línea</option>
                                            @if ($ventanillaMigrada)<option value="todas" @selected($pausa === 'todas')>Pausar todas</option>@endif
                                        </select>
                                        <button class="btn btn-sm btn-link p-0">Guardar</button>
                                    </noscript>
                                </form>
                            @endif

                            @if ($reasignacionMigrada)
                                @php
                                    $bloqueo = $modulo->bloqueoReasignacion();
                                    $bloqueoDesde = $modulo->no_reasignar_desde?->toDateString();
                                    $porPausa = $modulo->pausa() === 'todas';
                                @endphp
                                {{-- Aparte de la pausa: desde ahora o una fecha ya no se le pueden mover citas (Reasignar, reagenda). --}}
                                <form method="POST" action="{{ route('modulos.reasignacion', $modulo) }}"
                                      data-desde="{{ $bloqueoDesde }}" data-bloqueo="{{ $bloqueo }}"
                                      class="md-linea js-reasig {{ $porPausa ? 'is-heredado' : ($bloqueo === 'vigente' ? 'is-pausa' : ($bloqueo === 'programado' ? 'is-programada' : '')) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="fecha_vista" value="{{ $fecha }}">
                                    <input type="hidden" name="origen_vista" value="{{ $origen }}">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" data-switch
                                               id="mdReasig{{ $modulo->id }}" @checked(! $bloqueo)
                                               title="Apágalo para que ya no se le puedan mover citas desde una fecha">
                                        <label class="form-check-label" for="mdReasig{{ $modulo->id }}">Acepta reasignaciones</label>
                                    </div>
                                    <span class="md-linea__nota" aria-live="polite">{{ $modulo->notaReasignacion() }}</span>
                                    <div class="md-linea__modo" data-cuando @if (! $bloqueo) hidden @endif>
                                        <b>Desde:</b>
                                        <label class="md-linea__op"><input type="radio" name="cuando{{ $modulo->id }}" value="ahora" @checked($bloqueo !== 'programado')> Ahora</label>
                                        <label class="md-linea__op"><input type="radio" name="cuando{{ $modulo->id }}" value="fecha" @checked($bloqueo === 'programado')> Una fecha</label>
                                        <input type="date" class="form-control form-control-sm md-linea__fecha" data-fecha min="{{ $hoy }}"
                                               value="{{ $bloqueo === 'programado' ? $bloqueoDesde : '' }}" aria-label="Fecha desde la que ya no se le reasigna"
                                               @if ($bloqueo !== 'programado') hidden @endif>
                                    </div>
                                    <div class="md-linea__acciones" data-acciones hidden>
                                        <button type="button" class="btn btn-sm btn-dark" data-aplicar>Bloquear reasignaciones</button>
                                        <button type="button" class="btn btn-sm btn-link p-0" data-cancelar>Cancelar</button>
                                    </div>
                                    <noscript>
                                        <select name="bloquear" class="form-select form-select-sm w-auto">
                                            <option value="0" @selected(! $bloqueo)>Acepta reasignaciones</option>
                                            <option value="1" @selected($bloqueo)>No acepta desde…</option>
                                        </select>
                                        <input type="date" name="desde" min="{{ $hoy }}" value="{{ $bloqueo === 'programado' ? $bloqueoDesde : '' }}" class="form-control form-control-sm w-auto">
                                        <button class="btn btn-sm btn-link p-0">Guardar</button>
                                    </noscript>
                                </form>
                            @endif
                        </header>

                        @if ($susCitas->isEmpty())
                            <p class="md-vacio">Sin citas este día.</p>
                        @else
                            <ul class="md-citas">
                                @foreach ($susCitas as $cita)
                                    @include('administracion.modulos._cita', ['cita' => $cita])
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach

                @if ($sinModulo->isNotEmpty())
                    <article class="tn-tarjeta md-modulo">
                        <header class="md-cabeza">
                            <div class="md-cabeza__fila">
                                <h5 class="md-nombre">Sin módulo del catálogo</h5>
                                <span class="md-num">{{ $sinModulo->count() }}</span>
                            </div>
                            <p class="md-persona__nota mt-2 mb-0">Casos de excepción, turnos viejos de ventanilla o módulos que ya no existen.</p>
                        </header>
                        <ul class="md-citas">
                            @foreach ($sinModulo as $cita)
                                @include('administracion.modulos._cita', ['cita' => $cita, 'mostrarLugar' => true])
                            @endforeach
                        </ul>
                    </article>
                @endif

                <button type="button" class="md-nuevo" data-bs-toggle="modal" data-bs-target="#mdModalModulo" data-modulo="">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    Nuevo módulo en {{ $sede }}
                </button>
            </div>
        </div>
    </section>

    {{-- Crear / editar módulo --}}
    <div class="modal fade" id="mdModalModulo" tabindex="-1" aria-labelledby="mdModalModuloTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <form class="modal-content" method="POST" id="mdFormModulo" action="{{ route('modulos.crear') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="mdMetodo">
                <input type="hidden" name="delegacion" value="{{ $sede }}">
                <input type="hidden" name="fecha_vista" value="{{ $fecha }}">
                <input type="hidden" name="origen_vista" value="{{ $origen }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdModalModuloTitulo">Nuevo módulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label" for="mdNombre">Nombre</label>
                            <input type="text" class="form-control" id="mdNombre" name="nombre" maxlength="60" required placeholder="Modulo 6">
                        </div>
                        <div class="col-4">
                            <label class="form-label" for="mdOrden">Orden</label>
                            <input type="number" class="form-control" id="mdOrden" name="orden" min="0" max="999">
                        </div>
                        <div class="col-12">
                            <span class="form-label d-block">Tipo de módulo</span>
                            <div class="md-clase">
                                <label class="form-check m-0"><input class="form-check-input" type="radio" name="clase" value="normal" id="mdClaseNormal" checked> Normal (citas y turnos)</label>
                                <label class="form-check m-0"><input class="form-check-input" type="radio" name="clase" value="excepcion" id="mdClaseExc"> <i class="bi bi-shield-heart" style="color:#7B5EA7"></i> Casos de excepción</label>
                            </div>
                            <div class="form-text" id="mdClaseAyuda" hidden>Recibe las citas de casos de excepción de {{ $sede }}, en línea y de ventanilla. Sólo lo puede atender quien tiene el rol de Conciliación, Género e Igualdad o el distintivo de Enlace de Género e Igualdad (Usuarios).</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="mdPersona">Quién lo atiende</label>
                            <select class="form-select" id="mdPersona" name="user_id">
                                <option value="">Sin persona (no recibe citas)</option>
                                @foreach ($personal as $delegacion => $gente)
                                    <optgroup label="{{ $delegacion ?: 'Sin sede' }}">
                                        @foreach ($gente as $p)
                                            <option value="{{ $p->id }}">{{ $nombreBonito($p->name) }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <select class="form-select" id="mdPersonaExc" name="user_id" disabled hidden>
                                <option value="">Sin persona (no recibe citas)</option>
                                @forelse ($personalExcepcion as $delegacion => $gente)
                                    <optgroup label="{{ $delegacion ?: 'Sin sede' }}">
                                        @foreach ($gente as $p)
                                            <option value="{{ $p->id }}">{{ $nombreBonito($p->name) }}</option>
                                        @endforeach
                                    </optgroup>
                                @empty
                                    <option value="" disabled>Nadie tiene el rol ni el distintivo todavía (Usuarios)</option>
                                @endforelse
                            </select>
                            <div class="form-text">Al cambiar a la persona, sus citas pendientes de hoy en adelante pasan a quien elijas.</div>
                        </div>
                        <div class="col-12 col-sm-6" data-solo-normal>
                            <span class="form-label d-block">Recibe</span>
                            @foreach ($tramites as $t)
                                <div class="form-check">
                                    <input class="form-check-input md-tramite" type="checkbox" name="tramites[]" value="{{ $t }}" id="mdT{{ $loop->index }}">
                                    <label class="form-check-label" for="mdT{{ $loop->index }}">{{ $t }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-12 col-sm-6" data-solo-normal>
                            <span class="form-label d-block">De respaldo <i class="bi bi-info-circle text-muted" title="Sólo cuando ningún módulo que lo recibe de primera mano está libre"></i></span>
                            @foreach ($tramites as $t)
                                <div class="form-check">
                                    <input class="form-check-input md-respaldo" type="checkbox" name="respaldo[]" value="{{ $t }}" id="mdR{{ $loop->index }}">
                                    <label class="form-check-label" for="mdR{{ $loop->index }}">{{ $t }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="mdComida">Hora de comida</label>
                            <select class="form-select" id="mdComida" name="hora_comida">
                                <option value="">Ninguna</option>
                                @foreach ($horasComida as $h)<option value="{{ $h }}">{{ $h }} – {{ date('H:i', strtotime($h) + 1800) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="activo" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="mdActivo" name="activo" value="1" checked>
                                <label class="form-check-label" for="mdActivo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn tn-boton-dorado">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reasignar cita --}}
    @include('turnos._modal_reasignar', [
        'opciones'      => $opciones,
        'urlReasignar'  => route('modulos.reasignar', ['id' => '__ID__']),
        'camposOcultos' => ['fecha_vista' => $fecha, 'origen_vista' => $origen],
    ])
@endsection

@section('scripts')
    <script>
        (function () {
            // Elegir otro día recarga.
            var dia = document.querySelector('#mdDia input[name="fecha"]');
            if (dia) dia.addEventListener('change', function () { this.form.submit(); });

            // --- Módulo: el mismo formulario crea y edita.
            var modalModulo = document.getElementById('mdModalModulo');
            var form = document.getElementById('mdFormModulo');
            var urlCrear = form.getAttribute('action');

            function ponerClase(esExc) {
                var normal = document.getElementById('mdPersona'), exc = document.getElementById('mdPersonaExc');
                normal.hidden = normal.disabled = esExc;
                exc.hidden = exc.disabled = !esExc;
                document.getElementById('mdClaseAyuda').hidden = !esExc;
                form.querySelectorAll('[data-solo-normal]').forEach(function (d) { d.hidden = esExc; });
                form.querySelectorAll('.md-tramite').forEach(function (c) { c.disabled = esExc; });
            }
            form.querySelectorAll('input[name="clase"]').forEach(function (r) {
                r.addEventListener('change', function () { ponerClase(r.value === 'excepcion' && r.checked); });
            });

            modalModulo.addEventListener('show.bs.modal', function (e) {
                var crudo = e.relatedTarget ? e.relatedTarget.getAttribute('data-modulo') : '';
                var m = crudo ? JSON.parse(crudo) : null;

                form.reset();
                document.getElementById('mdModalModuloTitulo').textContent = m ? 'Editar ' + m.nombre : 'Nuevo módulo';
                form.setAttribute('action', m ? m.accion : urlCrear);
                document.getElementById('mdMetodo').value = m ? 'PUT' : 'POST';
                document.getElementById('mdNombre').value = m ? m.nombre : '';
                document.getElementById('mdOrden').value = m ? m.orden : '';
                // Normal o de casos de excepción: cambia qué se pide y a quién se puede elegir.
                var esExc = !!(m && m.tramites.indexOf('Excepción') !== -1);
                document.getElementById(esExc ? 'mdClaseExc' : 'mdClaseNormal').checked = true;
                ponerClase(esExc);
                document.getElementById(esExc ? 'mdPersonaExc' : 'mdPersona').value = m && m.user_id ? String(m.user_id) : '';
                document.getElementById('mdComida').value = m ? m.hora_comida : '';
                document.getElementById('mdActivo').checked = m ? !!m.activo : true;
                form.querySelectorAll('.md-tramite').forEach(function (c) { c.checked = !!(m && m.tramites.indexOf(c.value) !== -1); });
                form.querySelectorAll('.md-respaldo').forEach(function (c) { c.checked = !!(m && m.respaldo.indexOf(c.value) !== -1); });
            });

            // --- Switch "Recibe citas" y qué se pausa: se guarda al momento, sin recargar.
            var avisoPausa = document.getElementById('mdAvisoPausa');
            function pintarAviso(canal, lista) {
                if (!avisoPausa) return;
                var linea = avisoPausa.querySelector('[data-canal="' + canal + '"]');
                linea.hidden = !lista.length;
                linea.querySelector('[data-lista]').textContent = lista.join(', ');
                avisoPausa.hidden = Array.prototype.every.call(avisoPausa.querySelectorAll('[data-canal]'), function (d) { return d.hidden; });
            }
            document.querySelectorAll('.js-pausa').forEach(function (f) {
                var sw = f.querySelector('[data-switch]');
                var nota = f.querySelector('.md-linea__nota');
                var modo = f.querySelector('.md-linea__modo');
                var radios = f.querySelectorAll('.md-linea__modo input[type="radio"]');
                var actual = sw.checked ? 'ninguna' : ((f.querySelector('.md-linea__modo input:checked') || {}).value || 'linea');
                function pintar(p) {
                    sw.checked = p === 'ninguna';
                    modo.hidden = p === 'ninguna';
                    f.classList.toggle('is-pausa', p !== 'ninguna');
                    f.classList.toggle('is-pausa-todo', p === 'todas');
                    radios.forEach(function (r) { if (p !== 'ninguna') r.checked = r.value === p; });
                }
                function guardar(pausa) {
                    sw.disabled = true;
                    radios.forEach(function (r) { r.disabled = true; });
                    f.classList.add('is-guardando');
                    f.classList.remove('is-error');
                    fetch(f.getAttribute('action'), {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': f.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({ pausa: pausa })
                    })
                        .then(function (r) {
                            return r.json().catch(function () { return {}; }).then(function (j) {
                                if (!r.ok) throw new Error(j.message || 'No se pudo guardar; intenta de nuevo.');
                                return j;
                            });
                        })
                        .then(function (j) {
                            actual = j.pausa;
                            pintar(actual);
                            nota.textContent = j.nota;
                            // La pausa total también bloquea reasignaciones: se avisa al switch de abajo.
                            var hermano = f.parentNode.querySelector('.js-reasig');
                            if (hermano && j.notaReasignacion) {
                                hermano.dispatchEvent(new CustomEvent('pausa', { detail: { todas: j.pausa === 'todas', nota: j.notaReasignacion } }));
                            }
                            pintarAviso('linea', j.sinEnLinea || []);
                            pintarAviso('ventanilla', j.sinVentanilla || []);
                        })
                        .catch(function (e) {
                            pintar(actual);
                            f.classList.add('is-error');
                            nota.textContent = e.message;
                        })
                        .finally(function () {
                            sw.disabled = false;
                            radios.forEach(function (r) { r.disabled = false; });
                            f.classList.remove('is-guardando');
                        });
                }
                // Al apagar se pausa lo que esté marcado abajo (de inicio, sólo en línea).
                sw.addEventListener('change', function () {
                    var marcado = f.querySelector('.md-linea__modo input:checked');
                    guardar(sw.checked ? 'ninguna' : (marcado ? marcado.value : 'linea'));
                });
                radios.forEach(function (r) {
                    r.addEventListener('change', function () { if (r.checked && !sw.checked) guardar(r.value); });
                });
            });

            // --- Switch "Acepta reasignaciones" (aparte de la pausa): desde ahora o una fecha.
            // Volver a aceptar se guarda al momento; bloquear, con el botón.
            document.querySelectorAll('.js-reasig').forEach(function (f) {
                var sw = f.querySelector('[data-switch]');
                var nota = f.querySelector('.md-linea__nota');
                var panel = f.querySelector('[data-cuando]');
                var cuandos = f.querySelectorAll('input[name^="cuando"]');
                var fecha = f.querySelector('[data-fecha]');
                var acciones = f.querySelector('[data-acciones]');
                var aplicar = f.querySelector('[data-aplicar]');
                var actual = { bloqueo: f.dataset.bloqueo || '', desde: f.dataset.desde || '' };
                var porFecha = function () { var r = Array.prototype.find.call(cuandos, function (x) { return x.checked; }); return !!r && r.value === 'fecha'; };

                function pintar(e) {
                    var activo = !!e.bloqueo;
                    sw.checked = !activo;
                    panel.hidden = !activo;
                    if (!f.classList.contains('is-heredado')) {
                        f.classList.toggle('is-pausa', e.bloqueo === 'vigente');
                        f.classList.toggle('is-programada', e.bloqueo === 'programado');
                    }
                    cuandos.forEach(function (r) { r.checked = r.value === (e.bloqueo === 'programado' ? 'fecha' : 'ahora'); });
                    fecha.value = e.bloqueo === 'programado' ? e.desde : '';
                    fecha.hidden = !porFecha();
                    acciones.hidden = true;
                }
                function revisar() {
                    fecha.hidden = !porFecha();
                    var desde = porFecha() ? fecha.value : '';
                    var mismo = actual.bloqueo && ((actual.bloqueo === 'programado') === porFecha()) && (!porFecha() || desde === actual.desde);
                    acciones.hidden = !!mismo;
                    aplicar.textContent = actual.bloqueo ? 'Guardar cambios' : 'Bloquear reasignaciones';
                }
                function bloquear(si) {
                    f.querySelectorAll('input:not([type="hidden"]), button').forEach(function (x) { x.disabled = si; });
                    f.classList.toggle('is-guardando', si);
                }
                function guardar(datos) {
                    bloquear(true);
                    f.classList.remove('is-error');
                    fetch(f.getAttribute('action'), {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': f.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify(datos)
                    })
                        .then(function (r) {
                            return r.json().catch(function () { return {}; }).then(function (j) {
                                if (!r.ok) throw new Error(j.message || 'No se pudo guardar; intenta de nuevo.');
                                return j;
                            });
                        })
                        .then(function (j) {
                            actual = { bloqueo: j.bloqueo || '', desde: j.desde || '' };
                            pintar(actual);
                            nota.textContent = j.nota;
                        })
                        .catch(function (e) {
                            pintar(actual);
                            f.classList.add('is-error');
                            nota.textContent = e.message;
                        })
                        .finally(function () { bloquear(false); });
                }

                sw.addEventListener('change', function () {
                    if (sw.checked) {
                        if (actual.bloqueo) { guardar({ bloquear: false }); } else { pintar(actual); }
                        return;
                    }
                    panel.hidden = false;
                    revisar();
                });
                cuandos.forEach(function (r) { r.addEventListener('change', function () { if (!sw.checked) revisar(); }); });
                fecha.addEventListener('change', function () { if (!sw.checked) revisar(); });
                aplicar.addEventListener('click', function () {
                    if (porFecha() && !fecha.value) {
                        f.classList.add('is-error');
                        nota.textContent = 'Elige en el calendario la fecha desde la que ya no se le reasignan citas.';
                        fecha.focus();
                        return;
                    }
                    guardar({ bloquear: true, desde: porFecha() ? fecha.value : null });
                });
                f.querySelector('[data-cancelar]').addEventListener('click', function () { f.classList.remove('is-error'); pintar(actual); });
                f.addEventListener('pausa', function (e) {
                    f.classList.toggle('is-heredado', e.detail.todas);
                    if (e.detail.todas) { f.classList.remove('is-pausa', 'is-programada'); }
                    pintar(actual);
                    nota.textContent = e.detail.nota;
                });
            });
        })();
    </script>
@endsection
