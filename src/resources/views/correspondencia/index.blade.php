@extends('layouts.app')
@section('title', 'Correspondencia')

{{--
    Correspondencia de la recepción de Morelia. Hoy sólo aparta el lugar en el
    menú: la ruta, el permiso y la pantalla ya existen, así que cuando llegue
    el módulo basta con cambiar esta vista y su controlador.
--}}

@section('page_css')
    <style>
        .proximamente {
            max-width: 560px;
            margin: 40px auto;
            background: #fff;
            border: 1px solid #E3E8E8;
            border-radius: 12px;
            padding: 40px 32px;
            text-align: center;
        }

        .proximamente__icono {
            width: 72px;
            height: 72px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #EEF2F2;
            color: #496163;
            font-size: 32px;
        }

        .proximamente__etiqueta {
            display: inline-block;
            margin-bottom: 12px;
            padding: 3px 12px;
            border-radius: 999px;
            background: #CEA845;
            color: #fff;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .proximamente__titulo {
            margin: 0 0 8px;
            font-size: 26px;
            font-weight: 700;
            color: #2E3C3D;
        }

        .proximamente__texto {
            margin: 0;
            font-size: 14.5px;
            line-height: 1.55;
            color: #5E6E6F;
        }
    </style>
@endsection

@section('content')
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="page__heading mb-0">Correspondencia</h3>
        </div>

        <div class="section-body">
            <div class="proximamente">
                <div class="proximamente__icono" aria-hidden="true">
                    <i class="bi bi-envelope-paper"></i>
                </div>
                <span class="proximamente__etiqueta">Próximamente</span>
                <h4 class="proximamente__titulo">Estamos preparando este apartado</h4>
                <p class="proximamente__texto">
                    Aquí podrás registrar y dar seguimiento a la correspondencia
                    que llega a la recepción. Muy pronto estará disponible.
                </p>
            </div>
        </div>
    </section>
@endsection
