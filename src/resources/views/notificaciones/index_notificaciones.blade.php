@extends('layouts.app')
@section('title', 'Notificaciones')

{{--
    Inicio de Notificaciones. Arriba, a dónde ir; abajo, cómo va la región.
    Las cifras salen de NotificacionesController::inicio.
--}}

@php
    $sedesTexto = implode(' y ', $sedes);
    $fmt = fn ($n) => number_format($n);
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <style>
        .nt-hero { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
        .nt-hero__sedes { font-size: 13px; color: var(--tn-tenue); }

        .nt-accesos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }
        .nt-acceso {
            display: flex;
            gap: 14px;
            align-items: center;
            padding: 18px 20px;
            border-radius: 12px;
            border: 1px solid var(--tn-borde);
            background: #fff;
            color: var(--tn-tinta);
            text-decoration: none;
            transition: border-color .15s, box-shadow .15s, transform .15s;
        }
        .nt-acceso:hover { color: var(--tn-tinta); border-color: var(--tn-verde-claro); box-shadow: 0 6px 18px rgba(46, 60, 61, .08); transform: translateY(-1px); }
        .nt-acceso:focus-visible { outline: 2px solid var(--tn-dorado); outline-offset: 2px; }
        .nt-acceso__icono {
            flex: 0 0 auto;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 22px;
            background: #EEF2F2;
            color: var(--tn-verde);
        }
        .nt-acceso--principal { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .nt-acceso--principal:hover { color: #fff; border-color: var(--tn-verde); }
        .nt-acceso--principal .nt-acceso__icono { background: var(--tn-dorado); color: #fff; }
        .nt-acceso__titulo { display: block; font-size: 15.5px; font-weight: 700; }
        .nt-acceso__texto { display: block; font-size: 12.5px; opacity: .78; }
        .nt-acceso__num { margin-left: auto; font-size: 24px; font-weight: 800; font-variant-numeric: tabular-nums; }

        .nt-cifras {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }
        .nt-cifra {
            display: block;
            padding: 14px 16px;
            border-radius: 10px;
            background: var(--tn-fondo);
            color: var(--tn-tinta);
            text-decoration: none;
        }
        a.nt-cifra:hover { background: #EBEFEF; color: var(--tn-tinta); }
        .nt-cifra__num { display: block; font-size: 28px; font-weight: 800; line-height: 1.1; font-variant-numeric: tabular-nums; }
        .nt-cifra__etiqueta { display: block; margin-top: 4px; font-size: 13px; color: var(--tn-suave); }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <h3 class="page__heading mb-0">Notificaciones</h3>
        </div>

        <div class="section-body">
            <div class="nt-hero">
                <span class="nt-hero__sedes"><i class="bi bi-geo-alt"></i> {{ $sedesTexto }}</span>
            </div>

            <div class="nt-accesos">
                @if ($esNotificador)
                    <a class="nt-acceso nt-acceso--principal" href="{{ route('seer') }}">
                        <span class="nt-acceso__icono"><i class="bi bi-signpost-split" aria-hidden="true"></i></span>
                        <span>
                            <span class="nt-acceso__titulo">Por notificar</span>
                            <span class="nt-acceso__texto">Las que tienes asignadas</span>
                        </span>
                        <span class="nt-acceso__num">{{ $fmt($mias['pendientes']) }}</span>
                    </a>
                    <a class="nt-acceso" href="{{ route('Historial_Notificacador') }}">
                        <span class="nt-acceso__icono"><i class="bi bi-person-check" aria-hidden="true"></i></span>
                        <span>
                            <span class="nt-acceso__titulo">Mis notificaciones</span>
                            <span class="nt-acceso__texto">Las que ya diligenciaste</span>
                        </span>
                    </a>
                @else
                    <a class="nt-acceso nt-acceso--principal" href="{{ route('notificaciones') }}">
                        <span class="nt-acceso__icono"><i class="bi bi-send" aria-hidden="true"></i></span>
                        <span>
                            <span class="nt-acceso__titulo">Por notificar</span>
                            <span class="nt-acceso__texto">Asignar expedientes a notificadores</span>
                        </span>
                        <span class="nt-acceso__num">{{ $fmt($cifras['por_asignar']) }}</span>
                    </a>
                @endif
                {{-- El notificador no ve el historial de todos (SinNotificador); tiene "Mis notificaciones". --}}
                @unless (\App\Support\AgendaNotificador::soloNotificador(auth()->user()))
                    <a class="nt-acceso" href="{{ route('notificaciones_consultar') }}">
                        <span class="nt-acceso__icono"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
                        <span>
                            <span class="nt-acceso__titulo">Historial</span>
                            <span class="nt-acceso__texto">Todas las notificaciones, con filtros</span>
                        </span>
                    </a>
                @endunless
            </div>

            <div class="tn-tarjeta">
                <p class="tn-titulo-seccion">{{ $esNotificador ? 'Tu trabajo' : 'Cómo va la región' }}</p>
                <div class="nt-cifras">
                    @if ($esNotificador)
                        <a class="nt-cifra" href="{{ route('seer') }}">
                            <span class="nt-cifra__num">{{ $fmt($mias['pendientes']) }}</span>
                            <span class="nt-cifra__etiqueta">Pendientes por diligenciar</span>
                        </a>
                        <a class="nt-cifra" href="{{ route('Historial_Notificacador') }}">
                            <span class="nt-cifra__num">{{ $fmt($mias['del_mes']) }}</span>
                            <span class="nt-cifra__etiqueta">Diligenciadas en {{ $mes }}</span>
                        </a>
                    @endif
                    <a class="nt-cifra" href="{{ route('notificaciones') }}">
                        <span class="nt-cifra__num">{{ $fmt($cifras['por_asignar']) }}</span>
                        <span class="nt-cifra__etiqueta">Sin notificador asignado</span>
                    </a>
                    @if (\App\Support\AgendaNotificador::soloNotificador(auth()->user()))
                        <div class="nt-cifra">
                            <span class="nt-cifra__num">{{ $fmt($cifras['en_campo']) }}</span>
                            <span class="nt-cifra__etiqueta">En campo con un notificador</span>
                        </div>
                    @else
                        <a class="nt-cifra" href="{{ route('notificaciones_consultar', ['estatus' => 'Pendiente']) }}">
                            <span class="nt-cifra__num">{{ $fmt($cifras['en_campo']) }}</span>
                            <span class="nt-cifra__etiqueta">En campo con un notificador</span>
                        </a>
                    @endif
                    <div class="nt-cifra">
                        <span class="nt-cifra__num">{{ $fmt($cifras['del_mes']) }}</span>
                        <span class="nt-cifra__etiqueta">Notificadas con éxito en {{ $mes }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
