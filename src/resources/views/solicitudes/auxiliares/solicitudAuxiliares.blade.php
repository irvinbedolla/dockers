@extends('layouts.app')
@section('title', 'Nueva solicitud')

{{--
    Nueva solicitud (auxiliar): ¿quién la presenta? Si viene de una cita
    (App\Support\PrellenadoCita) se resalta la opción de la cita: "Soy
    patronal individual" si la agendó el empleador, con o sin folio de
    representante (App\Support\CitaPatronal); si no, "Soy trabajador(a)".
--}}

@php
    $prellenado = \App\Support\PrellenadoCita::paraVista();
    $citaPatronal = $prellenado && ($prellenado['tipo'] ?? 'trabajador') === 'patronal';
@endphp

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
                    'texto' => $citaPatronal
                        ? (! empty($prellenado['datos']['folio'])
                            ? 'Cita patronal con folio de representante <b>'.e($prellenado['datos']['folio']).'</b>: elige <b>Soy patronal individual</b> y en las siguientes pantallas verás el folio y la persona a citar ya puestos para revisarlos.'
                            : 'La agendó el empleador <b>sin folio</b> de representante patronal: elige <b>Soy patronal individual</b>, captura su folio (o regístralo) y en la pantalla del citado verás los datos de la persona trabajadora ya puestos.')
                        : 'Viene de su cita: elige <b>Soy trabajador(a)</b> y en las siguientes pantallas verás los datos ya puestos para revisarlos.',
                ])
            @endif

            <div class="sa-tipos">
                <a href="{{ route('solicitud.industriaAuxiliar', ['tipo_solicitud' => 1, 'draft_id' => $draftId ?? null]) }}"
                   class="sa-tipo {{ $prellenado && ! $citaPatronal ? 'is-sugerido' : '' }}">
                    @if ($prellenado && ! $citaPatronal)<span class="sa-tipo__marca"><i class="bi bi-magic"></i> Su cita</span>@endif
                    <span class="sa-tipo__icono"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <span>
                        <span class="sa-tipo__titulo">Soy trabajador(a)</span>
                        <span class="sa-tipo__texto">La persona trabajadora cita a su patrón(a) o empresa.</span>
                    </span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
                <a href="{{ route('solicitud.industriaAuxiliarP', ['tipo_solicitud' => 2, 'draft_id' => $draftId ?? null]) }}"
                   class="sa-tipo {{ $citaPatronal ? 'is-sugerido' : '' }}">
                    @if ($citaPatronal)<span class="sa-tipo__marca"><i class="bi bi-magic"></i> Su cita</span>@endif
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
