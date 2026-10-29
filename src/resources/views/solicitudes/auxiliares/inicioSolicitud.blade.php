@extends('layouts.app')
@section('title', 'Solicitud')

{{-- Datos generales de la solicitud de la persona trabajadora (con prellenado de la cita). --}}

@section('page_css')
    @include('solicitudes.auxiliares._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-body sa">
            @include('solicitudes.auxiliares._inicio_formulario', [
                'accion'     => route('agregaSolicitanteA'),
                'prellenado' => \App\Support\PrellenadoCita::paraVista(),
            ])
        </div>
    </section>
@endsection
