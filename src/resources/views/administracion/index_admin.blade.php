@extends('layouts.app')
@php
    $fechaActual = date('Y-m-d');

    // El menú se declara aquí para no repetir el mismo bloque de marcado cinco
    // veces: cada entrada lleva su ruta, su icono y una línea que explica a
    // dónde lleva, y el foreach de abajo las pinta todas igual.
    $accesos = [
        [
            'ruta'   => 'configuracion_usuarios',
            'icono'  => 'bi-people',
            'titulo' => 'Usuarios',
            'texto'  => 'Altas, bajas y roles del personal.',
            'solo_super' => false,
        ],
        [
            'ruta'   => 'configuracion_sedes',
            'icono'  => 'bi-calendar-x',
            'titulo' => 'Días inhábiles',
            'texto'  => 'Bloqueos y horarios por sede.',
            'solo_super' => true,
        ],
        [
            'ruta'   => 'index_retroceso',
            'icono'  => 'bi-arrow-counterclockwise',
            'titulo' => 'Retrocesos',
            'texto'  => 'Regresar un expediente de etapa.',
            'solo_super' => true,
        ],
        [
            'ruta'   => 'configuracion_borrar_cumpli',
            'icono'  => 'bi-trash3',
            'titulo' => 'Borrar cumplimientos',
            'texto'  => 'Eliminar cumplimientos capturados.',
            'solo_super' => true,
        ],
        [
            'ruta'   => 'cambio_fecha_audiencia',
            'icono'  => 'bi-calendar-event',
            'titulo' => 'Cambiar fecha de audiencia',
            'texto'  => 'Reprogramar una audiencia agendada.',
            'solo_super' => true,
        ],
    ];

    $esSuperUsuario = ($userRole[0] ?? null) === 'Super Usuario';
@endphp

@section('page_css')
    <style>
        .adm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 14px;
        }

        /* La tarjeta completa es el enlace: el área clicable es todo el bloque,
           no sólo el texto. */
        .adm-item {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 16px;
            border: 1px solid #E3E8E8;
            border-radius: 12px;
            background: #fff;
            color: #496163;
            text-decoration: none;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .adm-item:hover,
        .adm-item:focus-visible {
            border-color: #496163;
            box-shadow: 0 6px 18px rgba(73, 97, 99, .16);
            transform: translateY(-2px);
            color: #496163;
            text-decoration: none;
        }

        .adm-item:focus-visible { outline: 2px solid #496163; outline-offset: 2px; }
        .adm-item:active { transform: translateY(0); box-shadow: 0 2px 8px rgba(73, 97, 99, .14); }

        .adm-icono {
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #EDF1F1;
            color: #496163;
            font-size: 19px;
            transition: background-color .18s ease, color .18s ease;
        }

        .adm-item:hover .adm-icono,
        .adm-item:focus-visible .adm-icono {
            background: #496163;
            color: #fff;
        }

        .adm-titulo {
            font-size: 14.5px;
            font-weight: 600;
            line-height: 1.25;
            margin-bottom: 3px;
        }

        .adm-texto {
            font-size: 12.5px;
            line-height: 1.35;
            color: #7B8A8B;
            margin: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .adm-item, .adm-icono { transition: none; }
            .adm-item:hover, .adm-item:focus-visible { transform: none; }
        }
    </style>
@endsection

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Administración</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>¡Contraseña Actualizada!</strong>
                                    {{ session()->get('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                                </div>
                            @endif

                            {{-- Validación de campos: se avisa si alguno quedó vacío. --}}
                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                                </div>
                            @endif

                            <div class="adm-grid">
                                @foreach ($accesos as $acceso)
                                    @continue($acceso['solo_super'] && !$esSuperUsuario)
                                    <a href="{{ route($acceso['ruta']) }}" class="adm-item">
                                        <span class="adm-icono" aria-hidden="true"><i class="bi {{ $acceso['icono'] }}"></i></span>
                                        <span>
                                            <span class="adm-titulo d-block">{{ $acceso['titulo'] }}</span>
                                            <span class="adm-texto d-block">{{ $acceso['texto'] }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('body_end')
<div id="nuevo_poder" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush
