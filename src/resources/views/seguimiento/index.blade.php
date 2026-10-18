@extends('layouts.app')
@section('title', 'Seguimiento del caso')

{{--
    Seguimiento del caso: la historia de una persona con fecha y día, desde
    su cita hasta en qué quedó (App\Support\SeguimientoCaso). Sólo lectura.
--}}

@php
    $dia = fn ($c) => \Illuminate\Support\Str::ucfirst($c->locale('es')->isoFormat('ddd D MMM YYYY'));
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .sg-buscar { display: flex; gap: 10px; flex-wrap: wrap; }
        .sg-buscar .form-control { flex: 1 1 320px; min-height: 46px; border-radius: 10px; border: 1.5px solid #D5DDDD; font-size: 15px; padding-left: 14px; }
        .sg-buscar .form-control:focus { border-color: var(--tn-verde); box-shadow: 0 0 0 4px rgba(73, 97, 99, .14); }
        .sg-buscar .btn { min-height: 46px; border-radius: 10px; padding: 0 22px; }
        .sg-ayuda { font-size: 12.5px; color: var(--tn-tenue); margin: 8px 0 0; }

        .sg-resultados { display: flex; flex-direction: column; gap: 8px; }
        .sg-resultado { display: block; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-tinta); text-decoration: none; }
        .sg-resultado:hover { border-color: var(--tn-verde-claro); color: var(--tn-tinta); }
        .sg-resultado.is-activo { border-color: var(--tn-verde); box-shadow: inset 4px 0 0 var(--tn-verde); }
        .sg-resultado__nombre { font-weight: 700; font-size: 14px; }
        .sg-resultado__detalle { font-size: 12px; color: var(--tn-suave); }
        .sg-resultado__estatus { font-size: 11.5px; font-weight: 700; color: var(--tn-verde); }

        .sg-cabeza { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
        .sg-persona { font-size: 22px; font-weight: 800; color: var(--tn-tinta); margin: 0; }
        .sg-datos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .sg-dato { padding: 3px 10px; border-radius: 999px; background: var(--tn-fondo); font-size: 12px; color: var(--tn-suave); }
        .sg-dato b { color: var(--tn-tinta); }
        .sg-estado { padding: 6px 14px; border-radius: 999px; font-weight: 800; font-size: 13px; background: #FBF4E1; color: #7A5D10; }
        .sg-estado.is-resuelto { background: #E7ECEC; color: #354647; }
        .sg-estado.is-favorable { background: #E5F3EA; color: #1E7B3C; }
        .sg-dias { font-size: 12.5px; color: var(--tn-tenue); text-align: right; margin-top: 6px; }

        .sg-etapas { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 6px; margin: 18px 0 6px; }
        .sg-etapa { position: relative; text-align: center; font-size: 11.5px; font-weight: 700; color: var(--tn-tenue); }
        .sg-etapa::before { content: ""; display: block; height: 6px; border-radius: 99px; background: #E3E8E8; margin-bottom: 6px; }
        .sg-etapa.is-hecha { color: var(--tn-verde); }
        .sg-etapa.is-hecha::before { background: var(--tn-verde); }

        .sg-linea { list-style: none; margin: 18px 0 0; padding: 0; position: relative; }
        .sg-linea::before { content: ""; position: absolute; left: 131px; top: 6px; bottom: 6px; width: 2px; background: #E3E8E8; }
        .sg-evento { display: grid; grid-template-columns: 112px 40px 1fr; gap: 0 8px; align-items: start; padding: 8px 0; }
        .sg-evento__fecha { text-align: right; font-size: 12px; color: var(--tn-suave); line-height: 1.3; padding-top: 4px; }
        .sg-evento__fecha b { display: block; color: var(--tn-tinta); font-size: 12.5px; }
        .sg-evento__punto { position: relative; z-index: 1; width: 30px; height: 30px; margin: 0 auto; border-radius: 50%; display: grid; place-items: center; background: #EEF2F2; color: var(--tn-verde); border: 2px solid #fff; box-shadow: 0 0 0 1px #E3E8E8; font-size: 14px; }
        .sg-evento--ok .sg-evento__punto { background: #E5F3EA; color: #1E7B3C; }
        .sg-evento--alerta .sg-evento__punto { background: #FDECEA; color: #B42318; }
        .sg-evento__titulo { font-weight: 700; color: var(--tn-tinta); font-size: 14px; padding-top: 4px; }
        .sg-evento__detalle { font-size: 12.5px; color: var(--tn-suave); overflow-wrap: anywhere; }
        .sg-evento__etapa { display: inline-block; margin-left: 6px; padding: 0 7px; border-radius: 99px; background: var(--tn-fondo); color: var(--tn-tenue); font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; vertical-align: 2px; }
        .sg-evento--futuro { opacity: .62; }
        .sg-evento--futuro .sg-evento__punto { background: #fff; border-style: dashed; }
        .sg-hueco { display: grid; grid-template-columns: 112px 40px 1fr; gap: 0 8px; }
        .sg-hueco span { grid-column: 3; font-size: 11.5px; font-weight: 700; color: #A9862B; }
        .sg-hoy { display: grid; grid-template-columns: 112px 40px 1fr; gap: 0 8px; margin: 4px 0; }
        .sg-hoy span { grid-column: 2 / 4; font-size: 11px; font-weight: 800; color: var(--tn-dorado); text-transform: uppercase; letter-spacing: .08em; border-top: 2px dashed #E6C766; padding-top: 4px; }
        .sg-nota { font-size: 12px; color: var(--tn-tenue); margin-top: 14px; }

        @media (max-width: 576px) {
            .sg-linea::before { left: 15px; }
            .sg-evento, .sg-hueco, .sg-hoy { grid-template-columns: 32px 1fr; }
            .sg-evento__fecha { grid-column: 2; grid-row: 1; text-align: left; padding: 0; }
            .sg-evento__fecha b { display: inline; margin-right: 4px; }
            .sg-evento__punto { grid-column: 1; grid-row: 1 / span 3; width: 28px; height: 28px; }
            .sg-evento__titulo, .sg-evento__detalle { grid-column: 2; }
            .sg-hueco span, .sg-hoy span { grid-column: 2; }
            .sg-etapas { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media print {
            .sg-no-imprimir, .navbar, .main-sidebar, footer { display: none !important; }
            .sg-evento--futuro { opacity: 1; }
        }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <a href="{{ route('solicitudes_index') }}" class="text-decoration-none small sg-no-imprimir" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Solicitudes
                </a>
                <h3 class="page__heading mb-0">Seguimiento del caso</h3>
            </div>
            @if ($caso)
                <button type="button" class="btn btn-outline-secondary btn-sm sg-no-imprimir" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
            @endif
        </div>

        <div class="section-body">
            <form method="GET" action="{{ route('seguimiento') }}" class="tn-tarjeta mb-3 sg-no-imprimir" role="search">
                <div class="sg-buscar">
                    <input type="search" name="q" class="form-control" value="{{ $buscar }}" maxlength="80" autofocus
                           placeholder="Folio de cita (MOR-S26-0142), NUE, CURP o nombre" aria-label="Buscar caso">
                    <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-search me-1"></i> Buscar</button>
                </div>
                <p class="sg-ayuda">Desde que agendó su cita o levantó su turno, hasta en qué quedó su solicitud y el pago.</p>
            </form>

            @if ($buscar !== '' && $resultados->isEmpty())
                <div class="tn-tarjeta text-center" style="color: var(--tn-tenue);">No se encontró ningún caso con "{{ $buscar }}" en tus sedes.</div>
            @endif
            @if ($noEncontrado)
                <div class="alert alert-warning">Ese caso no existe o no es de tus sedes.</div>
            @endif

            <div class="row g-3">
                @if ($resultados->count() > 1)
                    <div class="{{ $caso ? 'col-lg-4' : 'col-12' }} sg-no-imprimir">
                        <p class="tn-titulo-seccion">{{ $resultados->count() }} {{ $resultados->count() === 1 ? 'caso' : 'casos' }}</p>
                        <div class="sg-resultados">
                            @foreach ($resultados as $r)
                                <a class="sg-resultado {{ $r['clave'] === $clave ? 'is-activo' : '' }}" href="{{ route('seguimiento', ['q' => $buscar, 'caso' => $r['clave']]) }}">
                                    <div class="sg-resultado__nombre">{{ $r['titulo'] }}</div>
                                    <div class="sg-resultado__detalle">{{ $r['detalle'] }} · {{ $r['fecha'] }}</div>
                                    <div class="sg-resultado__estatus">{{ $r['estatus'] }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($caso)
                    <div class="{{ $resultados->count() > 1 ? 'col-lg-8' : 'col-12' }}">
                        <div class="tn-tarjeta">
                            <div class="sg-cabeza">
                                <div>
                                    <h4 class="sg-persona">{{ $caso['persona'] }}</h4>
                                    <div class="sg-datos">
                                        @if ($caso['nue'])<span class="sg-dato">NUE <b>{{ $caso['nue'] }}</b></span>@endif
                                        @if ($caso['folio'])<span class="sg-dato">Cita <b>{{ $caso['folio'] }}</b></span>@endif
                                        @if ($caso['curp'])<span class="sg-dato">CURP <b>{{ $caso['curp'] }}</b></span>@endif
                                        <span class="sg-dato"><i class="bi bi-geo-alt"></i> {{ $caso['sede'] }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="sg-estado {{ $caso['favorable'] ? 'is-favorable' : ($caso['resuelto'] ? 'is-resuelto' : '') }}">{{ $caso['estado'] }}</span>
                                    @if ($caso['dias'] !== null)
                                        <div class="sg-dias">{{ $caso['resuelto'] ? 'Se resolvió en' : 'Lleva' }} <b>{{ $caso['dias'] }}</b> {{ $caso['dias'] === 1 ? 'día' : 'días' }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="sg-etapas" aria-label="Etapas del caso">
                                @foreach ($caso['etapas'] as $e)
                                    <span class="sg-etapa {{ $e['hecha'] ? 'is-hecha' : '' }}">{{ $e['nombre'] }}</span>
                                @endforeach
                            </div>

                            @if (! $caso['eventos'])
                                <p class="sg-nota">Todavía no hay movimientos con fecha.</p>
                            @else
                                <ol class="sg-linea">
                                    @php $marcoHoy = false; @endphp
                                    @foreach ($caso['eventos'] as $e)
                                        @if ($e['futuro'] && ! $marcoHoy)
                                            @php $marcoHoy = true; @endphp
                                            <li class="sg-hoy" aria-hidden="true"><span>Hoy · lo que sigue</span></li>
                                        @endif
                                        @if ($e['dias'])
                                            <li class="sg-hueco" aria-hidden="true"><span>+{{ $e['dias'] }} {{ $e['dias'] === 1 ? 'día' : 'días' }}</span></li>
                                        @endif
                                        <li class="sg-evento sg-evento--{{ $e['tono'] }} {{ $e['futuro'] ? 'sg-evento--futuro' : '' }}">
                                            <div class="sg-evento__fecha">
                                                <b>{{ $dia($e['cuando']) }}</b>
                                                @unless ($e['soloFecha']){{ $e['cuando']->format('H:i') }}@endunless
                                            </div>
                                            <span class="sg-evento__punto"><i class="bi bi-{{ $e['icono'] }}" aria-hidden="true"></i></span>
                                            <div>
                                                <div class="sg-evento__titulo">
                                                    {{ $e['titulo'] }}
                                                    <span class="sg-evento__etapa">{{ \App\Support\SeguimientoCaso::ETAPAS[$e['etapa']] }}</span>
                                                    @if ($e['futuro'])<span class="sg-evento__etapa" style="background:#FBF4E1;color:#7A5D10;">Programado</span>@endif
                                                </div>
                                                @if ($e['detalle'])<div class="sg-evento__detalle">{{ $e['detalle'] }}</div>@endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif

                            @if (! $caso['folio'])
                                <p class="sg-nota"><i class="bi bi-info-circle"></i> Esta solicitud no está ligada a una cita: se levantó sin cita o antes de que el sistema las ligara.</p>
                            @elseif ($caso['vinculo'] === 'historico')
                                <p class="sg-nota"><i class="bi bi-info-circle"></i> La cita se ligó después por la CURP: es la más cercana antes de la solicitud.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
