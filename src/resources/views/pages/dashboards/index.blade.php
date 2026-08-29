@extends('layouts.app')
@section('title', 'Agenda')

{{--
    Esta pantalla era un layout completo aparte: repetía navbar, sidebar y footer,
    y cargaba su propio Bootstrap 4.1.1 mientras el resto del sistema va en 5.3.
    Ahora extiende layouts.app, así que la barra superior y el sidebar salen de
    layouts/header.blade.php y layouts/sidebar.blade.php, una sola vez.

    El calendario en sí vive en resources/views/agenda/, porque el Inicio lo
    muestra también y no tiene caso mantener dos copias.
--}}

@section('page_css')
    @include('agenda._estilos')
@endsection

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Agenda</h3>
        </div>

        @include('agenda._calendario')
    </section>

    @push('body_end')
        <div id="menu_carga" style="display: none;">
            <div>.</div>
            <div class="loader"></div>
        </div>
    @endpush
@endsection

@section('scripts')
    @include('agenda._scripts')
@endsection
