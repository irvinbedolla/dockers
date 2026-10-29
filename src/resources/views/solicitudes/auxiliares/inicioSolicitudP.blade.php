@extends('layouts.app')
@section('title', 'Solicitud')

{{-- Datos generales de la solicitud patronal individual (sin prellenado: la cita es de la persona trabajadora). --}}

@section('page_css')
    @include('solicitudes.auxiliares._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-body sa">
            @include('solicitudes.auxiliares._inicio_formulario', [
                'accion'     => route('agregaSolicitanteAP'),
                'prellenado' => null,
            ])
        </div>
    </section>
@endsection
