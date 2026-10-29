@extends('layouts.app')
@section('title', 'Nueva solicitud')

{{--
    Nueva solicitud (auxiliar): ¿quién la presenta? Si viene de una cita
    (App\Support\PrellenadoCita) se resalta "Soy trabajador(a)": las citas de
    solicitud las agenda la persona trabajadora.
--}}

@php $prellenado = \App\Support\PrellenadoCita::paraVista(); @endphp

@section('page_css')
    @include('solicitudes.auxiliares._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-body sa">
            <div class="sa-cabeza">
                <a href="{{ route('solicitudes_index') }}" class="sa-cabeza__regreso"><i class="bi bi-arrow-left"></i> Solicitudes</a>
                <h3 class="page__heading">Nueva solicitud</h3>
                <p class="sa-cabeza__sub">¿Quién presenta la solicitud?</p>
            </div>

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            @if ($prellenado)
                @include('solicitudes.auxiliares._prellenado_banner', [
                    'texto' => 'Viene de su cita: elige <b>Soy trabajador(a)</b> y en las siguientes pantallas verás los datos ya puestos para revisarlos.',
                ])
            @endif

            <div class="sa-tipos">
                <a href="{{ route('solicitud.industriaAuxiliar', ['tipo_solicitud' => 1, 'draft_id' => $draftId ?? null]) }}"
                   class="sa-tipo {{ $prellenado ? 'is-sugerido' : '' }}">
                    @if ($prellenado)<span class="sa-tipo__marca"><i class="bi bi-magic"></i> Su cita</span>@endif
                    <span class="sa-tipo__icono"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <span>
                        <span class="sa-tipo__titulo">Soy trabajador(a)</span>
                        <span class="sa-tipo__texto">La persona trabajadora cita a su patrón(a) o empresa.</span>
                    </span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
                <a href="{{ route('solicitud.industriaAuxiliarP', ['tipo_solicitud' => 2, 'draft_id' => $draftId ?? null]) }}" class="sa-tipo">
                    <span class="sa-tipo__icono"><i class="bi bi-building" aria-hidden="true"></i></span>
                    <span>
                        <span class="sa-tipo__titulo">Soy patronal individual</span>
                        <span class="sa-tipo__texto">El patrón(a) o la empresa cita a una persona trabajadora.</span>
                    </span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>
@endsection
