@extends('layouts.app')
@section('title', 'Turnos')

{{--
    Inicio de Turnos. Arriba lo que se hace (nuevo turno, ver los de hoy,
    ver todos), en medio cómo va el día y abajo los folios que la recepción
    consulta para cuadrar con el papel.
--}}

@php
    use Illuminate\Support\Carbon;

    $hoyLargo   = \Illuminate\Support\Str::ucfirst(Carbon::today()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    $totalHoy   = $hoyPorEstatus->sum();
    $hoyFecha   = Carbon::today()->toDateString();
    $sedesTexto = count($sedesVisibles) > 2 ? count($sedesVisibles).' sedes' : implode(' y ', $sedesVisibles);
    $porEstatus = [
        'pendiente'  => 'Pendientes',
        'confirmada' => 'Confirmadas',
        'atendido'   => 'Atendidos',
        'expirada'   => 'Expiradas',
    ];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .tn-hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }
        .tn-hero__fecha {
            font-size: 13px;
            font-weight: 600;
            color: var(--tn-verde-claro);
        }
        .tn-hero__sedes { font-size: 13px; color: var(--tn-tenue); }

        .tn-acciones {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }
        @media (max-width: 900px) { .tn-acciones { grid-template-columns: 1fr; } }

        .tn-accion {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 20px;
            border-radius: 12px;
            border: 1px solid var(--tn-borde);
            background: #fff;
            color: var(--tn-tinta);
            text-decoration: none;
            transition: border-color .15s, box-shadow .15s, transform .15s;
        }
        .tn-accion:hover {
            color: var(--tn-tinta);
            border-color: var(--tn-verde-claro);
            box-shadow: 0 6px 18px rgba(46, 60, 61, .08);
            transform: translateY(-1px);
        }
        .tn-accion:focus-visible { outline: 2px solid var(--tn-dorado); outline-offset: 2px; }
        .tn-accion__icono {
            flex: 0 0 auto;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 22px;
            background: #EEF2F2;
            color: var(--tn-verde);
        }
        .tn-accion--principal { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .tn-accion--principal:hover { color: #fff; border-color: var(--tn-verde); }
        .tn-accion--principal .tn-accion__icono { background: var(--tn-dorado); color: #fff; }
        .tn-accion__titulo { display: block; font-size: 15.5px; font-weight: 700; }
        .tn-accion__texto { display: block; font-size: 12.5px; opacity: .75; }

        .tn-cuerpo {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr);
            gap: 14px;
            align-items: start;
        }
        @media (max-width: 1100px) { .tn-cuerpo { grid-template-columns: 1fr; } }

        .tn-cifras {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        @media (max-width: 640px) { .tn-cifras { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

        .tn-cifra {
            display: block;
            padding: 12px 14px;
            border-radius: 10px;
            background: var(--tn-fondo);
            color: var(--tn-tinta);
            text-decoration: none;
        }
        a.tn-cifra:hover { background: #EBEFEF; color: var(--tn-tinta); }
        .tn-cifra__num {
            display: block;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.1;
            font-variant-numeric: tabular-nums;
        }
        .tn-cifra__etiqueta { display: block; margin-top: 4px; }
        .tn-cifra--total .tn-cifra__num { color: var(--tn-verde); }

        .tn-proximos { list-style: none; margin: 0; padding: 0; }
        .tn-proximo {
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 10px 0;
            border-top: 1px solid var(--tn-borde);
        }
        .tn-proximo:first-child { border-top: 0; padding-top: 0; }
        .tn-proximo__hora { font-weight: 700; font-size: 15px; font-variant-numeric: tabular-nums; color: var(--tn-verde); }
        .tn-proximo__nombre { font-weight: 600; font-size: 13.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .tn-proximo__detalle { font-size: 12px; color: var(--tn-tenue); }
        .tn-nada { margin: 0; font-size: 13.5px; color: var(--tn-tenue); }

        .tn-folios {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .tn-folio-caja {
            padding: 12px 14px;
            border: 1px dashed var(--tn-borde);
            border-radius: 10px;
        }
        .tn-folio-caja__num { display: block; font-size: 22px; font-weight: 700; color: var(--tn-tinta); font-variant-numeric: tabular-nums; }
        .tn-folio-caja__etiqueta { display: block; font-size: 12px; color: var(--tn-tenue); }
        .tn-ver-todo { font-size: 13px; font-weight: 600; color: var(--tn-verde); text-decoration: none; }
        .tn-ver-todo:hover { color: var(--tn-tinta); }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <h3 class="page__heading mb-0">Turnos</h3>
        </div>

        <div class="section-body">
            @can('turnos_revisar')
                <div class="tn-hero">
                    <div>
                        <div class="tn-hero__fecha">{{ $hoyLargo }}</div>
                        @if ($sedesTexto)<div class="tn-hero__sedes"><i class="bi bi-geo-alt"></i> {{ $sedesTexto }}</div>@endif
                    </div>
                </div>

                <div class="tn-acciones">
                    @can('turnos_crear')
                        <a class="tn-accion tn-accion--principal" href="{{ route('nueva_cita') }}" onclick="crear_turnos();">
                            <span class="tn-accion__icono"><i class="bi bi-plus-lg" aria-hidden="true"></i></span>
                            <span>
                                <span class="tn-accion__titulo">Nuevo turno</span>
                                <span class="tn-accion__texto">Registrar a quien llega a ventanilla</span>
                            </span>
                        </a>
                    @endcan
                    <a class="tn-accion" href="{{ route('turnos.todos', ['fecha' => $hoyFecha]) }}">
                        <span class="tn-accion__icono"><i class="bi bi-calendar-day" aria-hidden="true"></i></span>
                        <span>
                            <span class="tn-accion__titulo">Turnos de hoy</span>
                            <span class="tn-accion__texto">{{ $totalHoy }} {{ $totalHoy === 1 ? 'turno' : 'turnos' }} agendados para hoy</span>
                        </span>
                    </a>
                    <a class="tn-accion" href="{{ route('turnos.todos') }}">
                        <span class="tn-accion__icono"><i class="bi bi-calendar3" aria-hidden="true"></i></span>
                        <span>
                            <span class="tn-accion__titulo">Todos los turnos</span>
                            <span class="tn-accion__texto">Consulta cualquier día y filtra</span>
                        </span>
                    </a>
                </div>

                <div class="tn-cuerpo">
                    <div class="tn-tarjeta">
                        <p class="tn-titulo-seccion">Hoy</p>
                        <div class="tn-cifras">
                            <a class="tn-cifra tn-cifra--total" href="{{ route('turnos.todos', ['fecha' => $hoyFecha]) }}">
                                <span class="tn-cifra__num">{{ $totalHoy }}</span>
                                <span class="tn-cifra__etiqueta tn-titulo-seccion" style="margin:4px 0 0;">Total</span>
                            </a>
                            @foreach ($porEstatus as $clave => $etiqueta)
                                <a class="tn-cifra" href="{{ route('turnos.todos', ['fecha' => $hoyFecha, 'estatus' => $clave]) }}">
                                    <span class="tn-cifra__num">{{ $hoyPorEstatus[$clave] ?? 0 }}</span>
                                    <span class="tn-cifra__etiqueta"><span class="tn-estatus tn-estatus--{{ $clave }}">{{ $etiqueta }}</span></span>
                                </a>
                            @endforeach
                            <a class="tn-cifra" href="{{ route('turnos.todos', ['fecha' => $hoyFecha, 'origen' => 'linea']) }}">
                                <span class="tn-cifra__num">{{ $hoyEnLinea }}</span>
                                <span class="tn-cifra__etiqueta"><span class="tn-origen tn-origen--linea">Citas en línea</span></span>
                            </a>
                        </div>
                    </div>

                    <div class="tn-tarjeta">
                        <div class="d-flex justify-content-between align-items-baseline">
                            <p class="tn-titulo-seccion">Próximos pendientes</p>
                            <a class="tn-ver-todo" href="{{ route('turnos.todos', ['fecha' => $hoyFecha, 'estatus' => 'pendiente']) }}">Ver todos <i class="bi bi-arrow-right"></i></a>
                        </div>
                        @if ($proximos->isEmpty())
                            <p class="tn-nada">No hay turnos pendientes por atender en lo que resta del día.</p>
                        @else
                            <ul class="tn-proximos">
                                @foreach ($proximos as $p)
                                    <li class="tn-proximo">
                                        <span class="tn-proximo__hora">{{ substr($p->hora, 0, 5) }}</span>
                                        <span style="min-width:0;">
                                            <span class="tn-proximo__nombre d-block">{{ $p->solicitante }}</span>
                                            <span class="tn-proximo__detalle">
                                                {{ $p->tipo }}@if ($p->lugar_auxiliar) · {{ $p->lugar_auxiliar }}@endif
                                                @if (count($sedesVisibles) > 1) · {{ $p->delegacion }}@endif
                                            </span>
                                        </span>
                                        @if ($p->origen === 'linea')
                                            <span class="tn-origen tn-origen--linea">En línea</span>
                                        @else
                                            <span class="tn-origen tn-origen--ventanilla">Ventanilla</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="tn-tarjeta mt-3">
                    <p class="tn-titulo-seccion">Folios de la sede</p>
                    <div class="tn-folios">
                        <div class="tn-folio-caja">
                            <span class="tn-folio-caja__num">{{ $last_hora_solicitud ?? 0 }}</span>
                            <span class="tn-folio-caja__etiqueta">Último folio de solicitudes del día</span>
                        </div>
                        <div class="tn-folio-caja">
                            <span class="tn-folio-caja__num">{{ $last_hora_ratificacion ?? 0 }}</span>
                            <span class="tn-folio-caja__etiqueta">Último folio de ratificaciones del día</span>
                        </div>
                        <div class="tn-folio-caja">
                            <span class="tn-folio-caja__num">{{ number_format($last_sede_solicitud ?? 0) }}</span>
                            <span class="tn-folio-caja__etiqueta">Último folio de solicitudes en la sede</span>
                        </div>
                        <div class="tn-folio-caja">
                            <span class="tn-folio-caja__num">{{ number_format($last_sede_ratificacion ?? 0) }}</span>
                            <span class="tn-folio-caja__etiqueta">Último folio de ratificaciones en la sede</span>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </section>
@endsection

@push('body_end')
<div id="nuevo_turno" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/turnos/turnos.js') }}"></script>
@endsection
