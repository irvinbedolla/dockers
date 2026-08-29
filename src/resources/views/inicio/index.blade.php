@extends('layouts.app')
@section('title', 'Inicio')

{{--
    Pantalla de Inicio. Nace deslindada del resto a propósito: es donde más
    adelante van a vivir los datos y las notificaciones de cada rol, así que
    tiene su propio controlador y su propia vista.

    Por ahora sólo saluda y muestra la agenda, incluida desde el mismo parcial
    que usa /agenda para no mantener dos calendarios.
--}}

@section('page_css')
    @include('agenda._estilos')

    <style>
        .inicio-saludo {
            background: #fff;
            border: 1px solid #E3E8E8;
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 18px;
        }

        .inicio-saludo h2 {
            font-size: 20px;
            font-weight: 600;
            color: #496163;
            margin: 0 0 4px;
        }

        .inicio-saludo p {
            margin: 0;
            font-size: 13.5px;
            color: #7B8A8B;
        }
    </style>
@endsection

@section('content')
    @php
        // users.name guarda el nombre completo en un solo campo y en mayúsculas,
        // a veces con espacios de más: se normaliza para el saludo.
        $nombre = \Illuminate\Support\Str::title(preg_replace('/\s+/', ' ', trim($usuario->name)));
    @endphp

    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Sistema integral para la Conciliación</h3>
        </div>

        <div class="section-body">
            <div class="inicio-saludo">
                <h2>Hola, {{ $nombre }}</h2>
                <p>¡Bienvenid@!</p>
            </div>
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
