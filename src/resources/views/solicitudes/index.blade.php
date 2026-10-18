@extends('layouts.app')
@section('title', 'Solicitudes')

{{--
    Inicio de Solicitudes. Arriba, a dónde ir; abajo, cómo va la sede.
    Las cifras salen de SeerController::solicitudes.
--}}

@php
    $fmt = fn ($n) => number_format((int) $n);
    $prellenado = \App\Support\PrellenadoCita::paraVista();
    $conModulo = \App\Http\Controllers\MiModuloController::puedeVer(auth()->user());
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .sl-hero { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
        .sl-hero__sede { font-size: 13px; color: var(--tn-tenue); }

        .sl-accesos { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .sl-acceso {
            display: flex; gap: 14px; align-items: center; padding: 18px 20px; border-radius: 12px;
            border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-tinta); text-decoration: none;
            transition: border-color .15s, box-shadow .15s, transform .15s;
        }
        .sl-acceso:hover { color: var(--tn-tinta); border-color: var(--tn-verde-claro); box-shadow: 0 6px 18px rgba(46, 60, 61, .08); transform: translateY(-1px); }
        .sl-acceso:focus-visible { outline: 2px solid var(--tn-dorado); outline-offset: 2px; }
        .sl-acceso__icono { flex: 0 0 auto; width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; font-size: 22px; background: #EEF2F2; color: var(--tn-verde); }
        .sl-acceso--principal { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .sl-acceso--principal:hover { color: #fff; border-color: var(--tn-verde); }
        .sl-acceso--principal .sl-acceso__icono { background: var(--tn-dorado); color: #fff; }
        .sl-acceso__titulo { display: block; font-size: 15.5px; font-weight: 700; }
        .sl-acceso__texto { display: block; font-size: 12.5px; opacity: .78; }
        .sl-acceso__num { margin-left: auto; font-size: 24px; font-weight: 800; font-variant-numeric: tabular-nums; }

        .sl-continuar {
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px 14px; padding: 12px 16px; margin-bottom: 16px;
            border-radius: 12px; background: #FBF4E1; border: 1px solid #F0DFA8; color: #6B5210; font-size: 13.5px;
        }
        .sl-continuar .btn { margin-left: auto; }

        .sl-cifras { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; }
        .sl-cifra { display: block; padding: 14px 16px; border-radius: 10px; background: var(--tn-fondo); color: var(--tn-tinta); text-decoration: none; }
        a.sl-cifra:hover { background: #EBEFEF; color: var(--tn-tinta); }
        .sl-cifra__num { display: block; font-size: 28px; font-weight: 800; line-height: 1.1; font-variant-numeric: tabular-nums; }
        .sl-cifra__etiqueta { display: block; margin-top: 4px; font-size: 13px; color: var(--tn-suave); }
        .sl-cifra__extra { display: block; margin-top: 2px; font-size: 12px; color: var(--tn-tenue); }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <h3 class="page__heading mb-0">Solicitudes</h3>
        </div>

        <div class="section-body">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="sl-hero">
                <span class="sl-hero__sede"><i class="bi bi-geo-alt"></i> {{ $sedeTexto }}</span>
            </div>

            @if ($prellenado)
                @can('solicitudes_crear')
                    <div class="sl-continuar" role="status">
                        <i class="bi bi-magic" aria-hidden="true"></i>
                        <span>Tienes lista la solicitud de la cita <b>{{ $prellenado['folio'] }}</b>@if (! empty($prellenado['persona'])) · {{ $prellenado['persona'] }}@endif con sus datos prellenados.</span>
                        <a href="{{ route('solicitud') }}" class="btn btn-sm tn-boton-dorado">Continuar</a>
                    </div>
                @endcan
            @endif

            <div class="sl-accesos">
                @can('solicitudes_crear')
                    <a class="sl-acceso sl-acceso--principal" href="{{ route('solicitud') }}">
                        <span class="sl-acceso__icono"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i></span>
                        <span>
                            <span class="sl-acceso__titulo">Nueva solicitud</span>
                            <span class="sl-acceso__texto">Capturar una solicitud de conciliación</span>
                        </span>
                    </a>
                @endcan
                @can('solicitudes_pendientes_validar')
                    <a class="sl-acceso" href="{{ route('solicitudes_pendientes') }}">
                        <span class="sl-acceso__icono"><i class="bi bi-clipboard-check" aria-hidden="true"></i></span>
                        <span>
                            <span class="sl-acceso__titulo">Pendientes por validar</span>
                            <span class="sl-acceso__texto">De la última semana</span>
                        </span>
                        <span class="sl-acceso__num">{{ $fmt($cifras['pendientes']) }}</span>
                    </a>
                @endcan
                <a class="sl-acceso" href="{{ route('todas_solicitudes') }}">
                    <span class="sl-acceso__icono"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
                    <span>
                        <span class="sl-acceso__titulo">Todas las solicitudes</span>
                        <span class="sl-acceso__texto">Consulta y seguimiento</span>
                    </span>
                </a>
                @if ($conModulo)
                    <a class="sl-acceso" href="{{ route('mi_modulo') }}">
                        <span class="sl-acceso__icono"><i class="bi bi-person-workspace" aria-hidden="true"></i></span>
                        <span>
                            <span class="sl-acceso__titulo">Mi módulo</span>
                            <span class="sl-acceso__texto">Quién sigue y tus citas de hoy</span>
                        </span>
                    </a>
                @endif
            </div>

            <div class="tn-tarjeta">
                <p class="tn-titulo-seccion">Cómo va {{ $sedeTexto === 'Todas las sedes' ? 'el estado' : 'la sede' }}</p>
                <div class="sl-cifras">
                    <div class="sl-cifra">
                        <span class="sl-cifra__num">{{ $fmt($cifras['hoy']) }}</span>
                        <span class="sl-cifra__etiqueta">Solicitudes registradas hoy</span>
                    </div>
                    <div class="sl-cifra">
                        <span class="sl-cifra__num">{{ $fmt($cifras['mes']) }}</span>
                        <span class="sl-cifra__etiqueta">Registradas en {{ $mes }}</span>
                    </div>
                    @can('solicitudes_crear')
                        <div class="sl-cifra">
                            <span class="sl-cifra__num">{{ $fmt($cifras['mias_hoy']) }}</span>
                            <span class="sl-cifra__etiqueta">Capturadas por ti hoy</span>
                        </div>
                    @endcan
                    @if ($cifras['pendientes'] !== null)
                        <a class="sl-cifra" href="{{ route('solicitudes_pendientes') }}">
                            <span class="sl-cifra__num">{{ $fmt($cifras['pendientes']) }}</span>
                            <span class="sl-cifra__etiqueta">Pendientes por validar</span>
                        </a>
                    @endif
                    <div class="sl-cifra">
                        <span class="sl-cifra__num">{{ $fmt($cifras['citas']->total ?? 0) }}</span>
                        <span class="sl-cifra__etiqueta">Citas de solicitud por atender hoy</span>
                        <span class="sl-cifra__extra">{{ $fmt($cifras['citas']->en_sala ?? 0) }} ya en sala</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
