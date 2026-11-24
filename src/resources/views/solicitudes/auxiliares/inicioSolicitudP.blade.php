@extends('layouts.app')
@section('title', 'Solicitud')

{{-- Datos generales de la solicitud patronal individual (con prellenado si la cita es patronal: App\Support\CitaPatronal). --}}

@section('page_css')
    @include('solicitudes.auxiliares._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-body sa">
            @include('solicitudes.auxiliares._inicio_formulario', [
                'accion'     => route('agregaSolicitanteAP'),
                'prellenado' => \App\Support\PrellenadoCita::paraVista('patronal'),
            ])
        </div>
    </section>
@endsection
