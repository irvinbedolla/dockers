@extends('layouts.app')
@section('title', 'Poderes')

{{--
    Inicio de Poderes: a dónde ir. Las cifras salen de PoderController::index.
--}}

@php
    $fmt = fn ($n) => number_format((int) $n);
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
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
        .pd-i-vencido { background: #FDEBDD; color: #B4530F; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <h3 class="page__heading mb-0">Poderes</h3>
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

            <div class="sl-accesos">
                @can('poderes_crear')
                    <a class="sl-acceso sl-acceso--principal" href="{{ route('poder-crear') }}" target="_blank">
                        <span class="sl-acceso__icono"><i class="bi bi-building-add" aria-hidden="true"></i></span>
                        <span>
                            <span class="sl-acceso__titulo">Nuevo poder</span>
                            <span class="sl-acceso__texto">Registrar a la parte patronal y su representante</span>
                        </span>
                    </a>
                @endcan
                <a class="sl-acceso" href="{{ route('poderes.listado', 'vigentes') }}">
                    <span class="sl-acceso__icono"><i class="bi bi-calendar2-check" aria-hidden="true"></i></span>
                    <span>
                        <span class="sl-acceso__titulo">Poderes vigentes</span>
                        <span class="sl-acceso__texto">Con vigencia a hoy o sin fecha de vencimiento</span>
                    </span>
                    <span class="sl-acceso__num">{{ $fmt($cifras['vigentes']) }}</span>
                </a>
                <a class="sl-acceso" href="{{ route('poderes.listado', 'sin-vigencia') }}">
                    <span class="sl-acceso__icono pd-i-vencido"><i class="bi bi-calendar-x" aria-hidden="true"></i></span>
                    <span>
                        <span class="sl-acceso__titulo">Poderes sin vigencia</span>
                        <span class="sl-acceso__texto">Su fecha de vigencia ya pasó</span>
                    </span>
                    <span class="sl-acceso__num">{{ $fmt($cifras['sin-vigencia']) }}</span>
                </a>
            </div>
        </div>
    </section>
@endsection
