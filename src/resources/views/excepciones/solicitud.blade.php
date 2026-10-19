@extends('layouts.app')
@section('title', 'Solicitud '.($general->NUE ?: ''))
{{--
    Solicitud con caso de excepción en modo consulta
    (CasosExcepcionController::verSolicitud). No tiene ningún formulario: el
    Departamento de Conciliación, Género e Igualdad la ve, no la edita. El
    contenido es solicitudes/_vista (el mismo del Seguimiento del caso).
--}}

@section('page_css')
    @include('turnos._estilos')
    <style>
        .sx-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; }
        .sx-migas:hover { color: var(--tn-verde); }
        .sx-aviso { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; background: #EEF2F2; color: var(--tn-suave); font-size: 12.5px; font-weight: 700; }
        .sx-caso { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; background: #F6E4EA; color: #8C1D40; font-size: 12.5px; font-weight: 700; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <a class="sx-migas" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('excepcion', ['vista' => 'solicitudes']) }}"><i class="bi bi-arrow-left"></i> Casos de excepción</a>
                <h3 class="page__heading mb-1">Solicitud {{ $general->NUE ?: 'sin expediente' }}</h3>
                <span class="sx-caso"><i class="bi bi-shield-exclamation"></i> Caso de excepción</span>
                <span class="sx-aviso"><i class="bi bi-eye"></i> Sólo consulta</span>
            </div>
        </div>

        <div class="section-body">
            @include('solicitudes._vista')
        </div>
    </section>
@endsection
