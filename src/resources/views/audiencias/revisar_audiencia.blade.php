@extends('layouts.app')
@section('title', 'Revisar Audiencia')

@section('page_css')
    <style>
        /* Revisar audiencia (Fase 1): encabezado fijo, lista de revisión y pestañas. */
        .ra-hueco { height: 0; }
        .ra-reserva { height: 0; }
        .ra-barra {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px 16px;
            background: #fff; border: 1px solid #E3E8E8; border-left: 4px solid #CEA845; border-radius: 12px;
            padding: 12px 16px; margin-bottom: 14px; z-index: 900;
        }
        .ra-barra.is-fija { position: fixed; box-shadow: 0 6px 18px rgba(46, 60, 61, .14); border-radius: 0 0 12px 12px; }
        .ra-barra__caso { min-width: 0; }
        .ra-barra__titulo { display: flex; align-items: center; flex-wrap: wrap; gap: 6px 8px; }
        .ra-barra__titulo b { font-size: 18px; color: #2E3C3D; }
        .ra-barra__datos { display: flex; flex-wrap: wrap; gap: 4px 16px; margin-top: 4px; font-size: 13px; color: #5E6E6F; }
        .ra-barra__datos i { color: #829A9C; margin-right: 3px; }
        .ra-chip { display: inline-block; padding: 2px 10px; border-radius: 999px; background: #EEF2F2; color: #496163; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .ra-chip--audiencia { background: #E3EEF9; color: #1F4E79; }
        .ra-chip--cerrado { background: #F1F1F1; color: #6B6B6B; }
        .ra-acciones { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .ra-acciones .btn { border-radius: 10px; font-weight: 700; }
        .ra-nota-acciones { width: 100%; text-align: right; font-size: 12px; color: #8A9899; margin-top: -8px; }
        .ra-barra.is-fija .ra-barra__datos { display: none; }

        .ra-lista { background: #fff; border: 1px solid #E3E8E8; border-radius: 12px; margin-bottom: 16px; }
        .ra-lista > summary { list-style: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; padding: 12px 16px; }
        .ra-lista > summary::-webkit-details-marker { display: none; }
        .ra-lista__titulo { font-size: 15px; font-weight: 800; color: #2E3C3D; }
        .ra-lista.is-lista .ra-lista__titulo { color: #1E7B3C; }
        .ra-lista__cuentas { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .ra-lista__ver { font-size: 12px; color: #496163; font-weight: 700; margin-left: 4px; }
        .ra-lista[open] .ra-lista__ver::after { content: ' ▲'; }
        .ra-lista:not([open]) .ra-lista__ver::after { content: ' ▼'; }
        .ra-cuenta { padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .ra-cuenta--falta { background: #FBEDEA; color: #9A3A22; }
        .ra-cuenta--aviso { background: #FBF4E1; color: #7A5D10; }
        .ra-cuenta--ok { background: #E9F5EE; color: #1E7B3C; }
        .ra-puntos { list-style: none; margin: 0; padding: 0 16px 8px; border-top: 1px solid #EEF2F2; }
        .ra-punto { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px solid #F3F5F5; font-size: 13.5px; color: #3E4C4D; }
        .ra-punto:last-child { border-bottom: 0; }
        .ra-punto > i { font-size: 16px; margin-top: 1px; }
        .ra-punto--falta > i { color: #C0392B; }
        .ra-punto--aviso > i { color: #D9A93A; }
        .ra-punto--ok > i { color: #1E7B3C; }
        .ra-punto--info > i { color: #5B7DB1; }
        .ra-punto__texto { flex: 1 1 auto; min-width: 0; }
        .ra-punto__texto b { color: #2E3C3D; margin-right: 4px; }
        .ra-ir { font-size: 12px; font-weight: 700; color: #496163; white-space: nowrap; text-decoration: none; }
        .ra-ir:hover { color: #2E3C3D; text-decoration: underline; }
        .ra-llegadas { display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; padding: 10px 16px 14px; border-top: 1px solid #EEF2F2; font-size: 13px; }
        .ra-llegadas__titulo { width: 100%; font-weight: 800; color: #2E3C3D; }
        .ra-llegadas__vacio { color: #8A9899; }
        .ra-llego { padding: 3px 10px; border-radius: 999px; background: #E9F5EE; color: #1E7B3C; font-weight: 700; }
        .ra-llego small { color: #4F7F5F; font-weight: 400; }
        .ra-obs { padding: 3px 10px; border-radius: 999px; background: #FBEDEA; color: #8C1D40; font-weight: 700; }

        /* Pestañas: ahora se marca la que está abierta. */
        .tab { display: flex; flex-wrap: wrap; gap: 6px; border: 0; background: transparent; overflow: visible; }
        /* style.css le da a todo .btn fondo blanco al pasar el mouse (y con
           !important borde transparente): la pestaña activa, con texto blanco,
           se "borraba". Por eso estos selectores llevan html body y !important. */
        html body .tab a.btn.ra-tab { border-radius: 10px 10px 0 0; border: 1px solid #D5DDDD !important; border-bottom: 0 !important; background: #F1F4F4 !important; color: #496163 !important; font-weight: 700; padding: 8px 16px; box-shadow: none; transition: background-color .15s, color .15s; }
        html body .tab a.btn.ra-tab:not(.active):hover { background: #E3EAEA !important; border-color: #D5DDDD !important; color: #2E3C3D !important; }
        html body .tab a.btn.ra-tab.active,
        html body .tab a.btn.ra-tab.active:hover,
        html body .tab a.btn.ra-tab.active:focus { background: #496163 !important; border-color: #496163 !important; color: #fff !important; cursor: default; }
        .tabcontent { display: none; padding: 12px 14px; border: 1px solid #D5DDDD; border-radius: 0 12px 12px 12px; scroll-margin-top: 170px; }

        .was-validated .form-control:invalid ~ .invalid-feedback,
        .was-validated select.form-control:invalid ~ .invalid-feedback,
        .was-validated .form-select:invalid ~ .invalid-feedback {
            display: block;
        }

        @media (max-width: 768px) {
            .ra-barra.is-fija .ra-barra__titulo .ra-chip { display: none; }
            .ra-acciones .btn { padding: 6px 10px; font-size: 13px; }
        }
        /* Fase 2: secciones en modo lectura (Editar para cambiar) y una tarjeta por citado. */
        .ra-seccion__cab { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px 12px; padding: 4px 0 10px; margin-bottom: 10px; border-bottom: 1px solid #EEF2F2; }
        .ra-seccion__titulo { margin: 0; font-size: 16px; font-weight: 800; color: #2E3C3D; }
        .ra-seccion__nota { display: block; font-size: 12px; color: #8A9899; }
        .ra-seccion__herr { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .ra-seccion__cambios { font-size: 12px; font-weight: 700; color: #B4532A; }
        .ra-seccion__falta { font-size: 12px; font-weight: 700; color: #9A3A22; background: #FBEDE8; border-radius: 999px; padding: 2px 10px; }
        .btn.ra-editar { font-weight: 700; border-radius: 8px; }
        .ra-parte.is-editando { border-color: #CEA845; box-shadow: 0 0 0 3px rgba(206, 168, 69, .15); }

        .ra-lectura input:not([type=hidden]):not([type=file]):not([type=checkbox]):not([type=radio]),
        .ra-lectura select,
        .ra-lectura textarea {
            border-color: transparent !important; background: transparent !important; box-shadow: none !important;
            padding-left: 0 !important; color: #2E3C3D !important; font-weight: 600; pointer-events: none;
            -webkit-appearance: none; -moz-appearance: none; appearance: none; resize: none; text-overflow: ellipsis;
        }
        .ra-lectura input[type=file], .ra-lectura .ra-solo-edicion, .ra-lectura .eliminar, .ra-lectura .invalid-feedback { display: none !important; }
        .ra-lectura label { font-size: 12px; color: #8A9899; font-weight: 700; margin-bottom: 0; }
        .ra-lectura label span[style*="red"] { display: none; }
        .ra-lectura .form-group { margin-bottom: 6px; }

        /* Se puede editar dando clic en el dato: al pasar el mouse el dato se
           sombrea y sale un lápiz; el clic abre la sección en ese campo. */
        .ra-lectura.ra-editable .form-group { position: relative; cursor: pointer; border-radius: 8px; transition: background-color .12s, box-shadow .12s; }
        .ra-lectura.ra-editable .form-group:hover { background-color: #F2F6F6; box-shadow: 0 0 0 6px #F2F6F6; }
        .ra-lectura.ra-editable .form-group:hover::after {
            content: "\F4CB"; font-family: bootstrap-icons; position: absolute; top: 2px; right: 4px;
            font-size: 12px; color: #496163; pointer-events: none;
        }
        .ra-lectura.ra-editable .form-group:hover label { color: #496163; }

        .ra-parte { border: 1px solid #E3E8E8; border-radius: 12px; padding: 12px 16px; margin: 6px 0 16px; background: #fff; }
        .ra-parte__cab { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; padding-bottom: 10px; margin-bottom: 6px; border-bottom: 1px solid #EEF2F2; }
        .ra-parte__quien { display: flex; gap: 10px; align-items: flex-start; min-width: 0; }
        .ra-parte__num { width: 28px; height: 28px; border-radius: 50%; background: #354647; color: #fff; font-weight: 800; display: grid; place-items: center; flex: 0 0 auto; }
        .ra-parte__nombre { font-size: 16px; font-weight: 800; color: #2E3C3D; }
        .ra-parte__chips { display: flex; flex-wrap: wrap; gap: 4px 6px; margin-top: 4px; }
        .ra-et { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: #EEF2F2; color: #496163; }
        .ra-et--multa, .ra-et--falta { background: #FBEDEA; color: #9A3A22; }
        .ra-et--aviso, .ra-et--nuevo { background: #FBF4E1; color: #7A5D10; }
        .ra-et--ok { background: #E9F5EE; color: #1E7B3C; }
        .ra-et--info, .ra-et--solicitante { background: #E3EEF9; color: #1F4E79; }
        .ra-et--resulte { background: #F1EFF8; color: #4B3F7A; }
        .ra-seccion h4.text-center { text-align: left !important; font-size: 12.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #829A9C; margin: 12px 0 4px; }
        .ra-subtitulo { font-size: 12.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #829A9C; margin: 8px 0 6px; }
        .ra-tabla-docs td, .ra-tabla-docs th { vertical-align: middle; }
        /* Fase 3: varios solicitantes y representantes con su poder (pestaña Documentos). */
        .ra-sol__titulo { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 800; color: #2E3C3D; margin: 14px 0 6px; padding-top: 10px; border-top: 1px solid #EEF2F2; }
        .ra-rep { border: 1px solid #E3E8E8; border-radius: 12px; padding: 12px 16px; margin: 6px 0 12px; background: #fff; }
        .ra-rep__cab { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px 12px; padding-bottom: 8px; border-bottom: 1px solid #EEF2F2; }
        .ra-rep__nombre { font-size: 15px; font-weight: 800; color: #2E3C3D; }
        .ra-rep__partes { font-size: 12.5px; color: #5E6E6F; }
        .ra-rep__datos { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 8px 16px; margin: 10px 0; }
        .ra-rep__datos dt { font-size: 12px; color: #8A9899; font-weight: 700; margin: 0; }
        .ra-rep__datos dd { margin: 0; color: #2E3C3D; font-weight: 600; word-break: break-word; }
        .ra-rep__ancho { grid-column: 1 / -1; }
        .ra-rep__docs { display: flex; flex-wrap: wrap; gap: 6px; }
        .ra-rep-resumen { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; background: #F7F9F9; border: 1px solid #E3E8E8; border-radius: 10px; padding: 10px 14px; margin: 8px 0 12px; font-size: 13.5px; }
        /* Botones con los colores institucionales: verde obscuro #496163 (hover
           #3E5254, el de style.css) y verde claro #829A9C. Rojo apagado sólo
           para borrar. Con html body y !important para ganarle a style.css. */
        html body .btn.btn-primary,
        html body .btn.btn-success { background-color: #496163 !important; border-color: #496163 !important; color: #fff !important; box-shadow: 0 2px 6px rgba(73, 97, 99, .28); }
        html body .btn.btn-primary:not(.btn-social):hover, html body .btn.btn-primary:not(.btn-social):focus, html body .btn.btn-primary:not(.btn-social):active,
        html body .btn.btn-success:not(.btn-social):hover, html body .btn.btn-success:not(.btn-social):focus, html body .btn.btn-success:not(.btn-social):active { background-color: #3E5254 !important; border-color: #3E5254 !important; color: #fff !important; }
        html body .btn.btn-info { background-color: #829A9C !important; border-color: #829A9C !important; color: #fff !important; box-shadow: 0 2px 6px rgba(130, 154, 156, .3); }
        html body .btn.btn-info:not(.btn-social):hover, html body .btn.btn-info:not(.btn-social):focus, html body .btn.btn-info:not(.btn-social):active { background-color: #496163 !important; border-color: #496163 !important; color: #fff !important; }
        html body .btn.btn-secondary { background-color: #E3EAEA !important; border-color: #E3EAEA !important; color: #496163 !important; box-shadow: none; }
        html body .btn.btn-secondary:not(.btn-social):not(:disabled):hover { background-color: #D2DCDC !important; border-color: #D2DCDC !important; color: #354647 !important; }
        html body .btn.btn-outline-secondary,
        html body .btn.ra-editar { background-color: #829A9C !important; border: 1px solid #829A9C !important; color: #fff !important; box-shadow: 0 2px 6px rgba(130, 154, 156, .3); }
        html body .btn.btn-outline-secondary:not(.btn-social):hover, html body .btn.btn-outline-secondary:not(.btn-social):focus,
        html body .btn.ra-editar:not(.btn-social):hover, html body .btn.ra-editar:not(.btn-social):focus,
        html body .ra-seccion.ra-lectura.ra-editable:hover > .ra-seccion__cab .btn.ra-editar,
        html body .ra-seccion.ra-lectura.ra-editable:hover > .ra-parte__cab .btn.ra-editar,
        html body .ra-seccion.is-editando .btn.ra-editar { background-color: #496163 !important; border-color: #496163 !important; color: #fff !important; }
        html body .btn.btn-danger,
        html body .btn.btn-outline-danger { background-color: #fff !important; border: 1px solid #C9A49A !important; color: #9A3A22 !important; box-shadow: none; }
        html body .btn.btn-danger:not(.btn-social):hover, html body .btn.btn-danger:not(.btn-social):focus,
        html body .btn.btn-outline-danger:not(.btn-social):hover, html body .btn.btn-outline-danger:not(.btn-social):focus { background-color: #9A3A22 !important; border-color: #9A3A22 !important; color: #fff !important; }
        /* "Guardar edición" mientras no hay cambios: gris, no verde. */
        html body .btn.btn-primary.ra-guardar--espera,
        html body .btn.btn-primary.ra-guardar--espera:disabled { background-color: #E3EAEA !important; border-color: #E3EAEA !important; color: #8A9899 !important; box-shadow: none; opacity: 1; }
        .ra-guardar-aviso { font-size: 12px; color: #8A9899; }
        /* Documentos: se comprueba que exista el archivo (script de data-doc). */
        a.ra-doc-revisando { opacity: .6; }
        .ra-doc-falta { display: inline-flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; font-size: 12.5px; font-weight: 700; color: #9A3A22; background: #FBEDE8; border-radius: 8px; padding: 4px 4px 4px 10px; }
        .ra-doc-falta .btn { font-size: 12px; padding: 2px 8px; }
        input.ra-doc-subir { border-color: #CEA845 !important; box-shadow: 0 0 0 3px rgba(206, 168, 69, .2) !important; }
        .ra-guardar-aviso b { color: #496163; }
        html body .btn.btn-light.disabled { background-color: #F4F6F6 !important; border-color: #F4F6F6 !important; color: #8A9899 !important; box-shadow: none; }
        /* Confirmación de borrar citado */
        .ra-confirmar__datos { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 16px; background: #F7F9F9; border: 1px solid #E3E8E8; border-radius: 10px; padding: 10px 14px; margin: 0 0 12px; }
        .ra-confirmar__datos dt { font-size: 12px; color: #8A9899; font-weight: 700; margin: 0; }
        .ra-confirmar__datos dd { margin: 0; color: #2E3C3D; font-weight: 600; word-break: break-word; }
        .ra-confirmar__nota { font-size: 13px; color: #5E6E6F; }
        .ra-confirmar__aviso { font-size: 13px; color: #9A3A22; background: #FBEDEA; border-radius: 8px; padding: 8px 10px; }
        @media (max-width: 480px) { .ra-confirmar__datos { grid-template-columns: 1fr; } }
        .ra-varios { background: #FBF4E1; color: #7A5D10; border-radius: 10px; padding: 8px 12px; font-size: 13px; margin-bottom: 10px; }
    </style>
@endsection

@section('content')
    <section class="section">
        <div class="section-header">
            @if ($isAudiencia == 'Si')
            <h3 class="page__heading">Revisar Audiencia</h3>
            @else
            <h3 class="page__heading">Revisar Solicitud</h3>
            @endif
        </div>
        <div class="section-body">
            <?php $fecha_actual->format('d/m/Y') ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Detalles de la solicitud</h3>
                            @if(session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>¡Registro correcto!</strong>
                                    {{ session()->get('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <!--Se realiza la validación de campos para ver si dejó alguno vacío-->
                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            <!--<span class="badge badge-danger">{{ $error }}</span>-->
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form class="needs-validation" novalidate method="POST" action="{{route('audiencia_confirmar')}}" enctype='multipart/form-data'>
                                    @csrf
                                    <input type="hidden" name="id" value="{{$id}}">
                                    <input type="hidden" name="isAudiencia" value="1">
                                    <input type="hidden" name="esAudiencia" value="{{ $isAudiencia }}">
                                    <input type="hidden" name="audiencia_id" value="{{ request()->query('audiencia_id') }}">

                                    @include('audiencias._revisar_encabezado')
                                    @include('audiencias._revisar_lista')

                                    {{-- Las acciones ya no son pestaña: están en el encabezado. Los id de "Citado(s)" y "Documentos" están cruzados desde antes; se dejan así. --}}
                                    <div class="tab">
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'detalles')">Detalles</a>
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'solicitante')">{{ $general->tipo_solicitud == 2 ? 'Solicitante (patrón)' : 'Solicitante' }}</a>
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'documentos')">Citado(s)</a>
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'citados')">Documentos</a>
                                    </div>

                                    <div id="detalles" class="tabcontent ra-seccion">
                                        @include('audiencias._revisar_seccion_cab', ['titulo' => 'Datos de la solicitud'])
                                        <div id="tabla_detalles" class="row">
                                            <div class="col-xs-12 col-sm-6 col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Fecha de Registro</label>
                                                    <input type="date" class="form-control" value="<?=$general["fecha"];?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Fecha de Confirmación</label>
                                                    <input type="date" class="form-control" value="{{ $general['fecha_confirmacion'] ?? '' }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label for="password">Número de Expediente</label>
                                                    <input type="text" name="actividad_economica" class="form-control" value="{{ $general['NUE'] ?? '' }}" readonly>
                                                </div>
                                            </div>

                                            
                                            <div class="col-xs-12 col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label for="password">Actividad económica<span style="color:red;"> (*)</span></label>
                                                    <input type="text" name="actividad_economica" class="form-control" value="<?=$general["actividad"];?>" required>
                                                    <div class="invalid-feedback">
                                                        La actividad económica es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <label for="name">Rama industrial del negocio<span style="color:red;"> (*)</span></label>
                                                    <select class="form-control" name="ramaIndustrial" required>
                                                        <option value="">Seleccione</option>
                                                        @foreach($ramas as $rama)
                                                            <option value="{{$rama['id']}}" {{ $rama["id"] == $general["id_rama"] ? "selected" : '' }} >{{$rama['rama_industrial']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El rama industrial es obligatoria.
                                                    </div>
                                                </div>
                                            </div>
                                        

                                            <div class="col-xs-12 col-sm-12 col-md-12"><br>
                                                <table  class="table table-striped mt-1" style="margin: 0 center; text-align:center;">
                                                    <thead style="background-color: #D2D3D5;">
                                                        <th style="color: black;">Motivo capturado</th>
                                                        <th style="color: black;">Acción</th>
                                                    </thead>
                                                    <tbody>
                                                         @foreach($motivos as $motivo)
                                                            <tr>
                                                                <td>
                                                                    <option value="{{$motivo['id']}}">{{$motivo['motivo']}}</option>
                                                                </td>  
                                                                <td>
                                                                    @can('audiencias_borrar_motivo')
                                                                        <a href="{{ route('eliminar_motivo', ['id' => $id, 'id_motivo' => $motivo->id] ) }}" class="eliminar btn btn-danger btn-sm">Eliminar</a>
                                                                    @endcan
                                                                </td>   
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                       
                                            <div class="col-xs-6 col-sm-6 col-md-6 ra-solo-edicion">
                                                <div class="form-group">
                                                    <label for="name">Agregar Otro Motivo a la Solicitud</label>
                                                    @can('audiencias_agregar_motivo')
                                                        <select  class="form-control" id="motivo_solicitud">
                                                            <option value="">Seleccione</option>
                                                            @foreach($mostrarMotivos as $motivo)
                                                                <option value="{{$motivo['id']}}">{{$motivo['motivo']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El objeto de solicitud es obligatoria.
                                                        </div>
                                                    @endcan
                                                </div>
                                            </div>

                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-12 ra-solo-edicion"><br>
                                                <table id="tabla" name="motivo_solicitud[]" class="table table-striped mt-1" style="margin: 0 center; text-align:center;">
                                                    <thead style="background-color: #D2D3D5;">
                                                        <th style="color: black;">Objeto de la Solicitud</th>
                                                        <th style="color: black;">Acción</th>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                            <div id="div1"  class="col-xs-12 col-sm-12 col-md-12"><br>
                                                <table id="tabla" class="table table-striped mt-1" style="margin: 0 center; text-align:center;">
                                                    <thead style="background-color: #D2D3D5;">
                                                        <th style="color: black;">Audiencia</th>
                                                        <th style="color: black;">Fecha</th>
                                                        <th style="color: black;">Hora</th>

                                                    </thead>
                                                    <tbody>
                                                        @forelse($historial_audiencias as $audiencia)
                                                            <tr>
                                                                <td>{{ $audiencia->folio_audiencia }}</td>                      
                                                                <td>{{ \Carbon\Carbon::parse($audiencia->fecha)->format('d/m/y') }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($audiencia->hora)->format('H:i') }} hrs.</td>
  
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3">No hay audiencias registradas para esta solicitud.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>

                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="solicitante" class="tabcontent ra-seccion">
                                        @include('audiencias._revisar_seccion_cab', ['titulo' => 'Solicitante'])
                                        <div id="tabla_solicitante" class="row">
                                            @foreach($solicitantes as $solicitante)
                                                @include('audiencias._revisar_solicitante', ['i' => $loop->index, 'sfx' => $loop->index ? '_'.$loop->index : ''])
                                            @endforeach
                                        </div>
                                    </div>
                                    <div id="documentos" class="tabcontent">
                                        <div id="tabla_documentos" class="row">
                                            
                                            @if($edicionAbierta && $general->tipo_solicitud == 1)
                                                @can('audiencias_borrar_citados')
                                                    <div class="col-xs-12 col-sm-12 col-md-12"><br>
                                                        <a type="button" class="btn btn-primary open-modal mb-3" data-bs-toggle="modal" 
                                                        data-bs-target="#exampleModal1" data-id="{{ $id }}">Agregar Citado</a>
                                                        <a type="button" class="btn btn-outline-danger open-modal mb-3" data-bs-toggle="modal" 
                                                        data-bs-target="#exampleModal2" data-id="{{ $id }}">Borrar Citado</a>
                                                    </div>
                                                @endcan
                                            @endif<br>

                                            @foreach($citados as $citado)
                                                @php
                                                    $esResulte = isset($citado['resulte_responsable']) ? $citado['resulte_responsable'] : (isset($citado->resulte_responsable) ? $citado->resulte_responsable : 'No');
                                                @endphp
                                                {{-- Tarjeta del citado: datos, notificación, representante y documentos juntos. --}}
                                                <div class="col-12">
                                                <div class="ra-parte ra-seccion">
                                                @include('audiencias._revisar_citado_cab', ['tj' => $tarjetas[$loop->index] ?? null, 'n' => $loop->iteration])
                                                <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group mt-1">
                                                        <h4 class="text-center">Datos del citado</h4>
                                                    </div>
                                                </div>
                                                
                                                <br>
                                                <div class="col-xs-12 col-sm-6 col-md-4" id="nombre_wrap_{{$loop->index}}">
                                                    <div class="form-group">
                                                        <label for="password">Nombre<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="nombre_citado[]" value="{{ $citado['nombre'] ?? '' }}" required>
                                                        <input type="hidden" name="resulte_responsable[]" value="{{ $esResulte }}">
                                                        <div class="invalid-feedback">
                                                            El campo nombre es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4" id="primer_wrap_{{$loop->index}}" style="{{ $esResulte == 'Si' ? 'display:none;' : '' }}">
                                                    <div class="form-group">
                                                       <label for="password">Primer apellido<span style="color:red;"> (*)</span></label>
                                                       <input type="text" class="form-control" name="primer_apellido[]" value="{{ $citado['primer_apellido'] ?? '' }}" @if($esResulte == 'Si') @else required @endif>
                                                       <div class="invalid-feedback">
                                                            El campo primer apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4" id="segundo_wrap_{{$loop->index}}" style="{{ $esResulte == 'Si' ? 'display:none;' : '' }}">
                                                    <div class="form-group">
                                                       <label for="password">Segundo apellido</label>
                                                       <input type="text" class="form-control" name="segundo_apellido[]" value="{{ $citado['segundo_apellido'] ?? '' }}">
                                                       <div class="invalid-feedback">
                                                            El campo segundo apellido es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de persona<span style="color:red;"> (*)</span></label>
                                                        <select name="tipo_persona_citado[]" class="form-control" resulte_flag="{{ $esResulte }}" required>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="Fisica" {{ $citado['tipo_persona'] == 'Fisica' ? "selected" : '' }}>Física</option>
                                                            <option value="Moral"  {{ $citado['tipo_persona'] == 'Moral' ? "selected" : '' }}>Moral</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(!empty($citado['curp']))
                                                    <div class="col-xs-12 col-sm-6 col-md-4" id="campo_curp">
                                                        <div class="form-group">
                                                           <label for="password">CURP</label>
                                                           <input type="text" class="form-control" name="curp_citado[]" value="{{ $citado['curp'] ?? '' }}" maxlength="18">   
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">RFC</label>
                                                        <input type="text" class="form-control" name="rfc_citado[]" value="{{ $citado['rfc'] ?? '' }}">   
                                                    </div>
                                                </div>
                                                @php
                                                    $tradVal = 'No';
                                                    if (isset($citado['traductor'])) {
                                                        $tradVal = (string) $citado['traductor'];
                                                    } elseif (isset($citado->traductor)) {
                                                        $tradVal = (string) $citado->traductor;
                                                    }
                                                    if ($tradVal === '1' || $tradVal === '1' || $tradVal === 'true' || $tradVal === 'True') {
                                                        $tradVal = 'Si';
                                                    }
                                                    if ($tradVal === '0' || $tradVal === 'false' || $tradVal === 'False') {
                                                        $tradVal = 'No';
                                                    }
                                                    $lenguajeVal = '';
                                                    if (isset($citado['lenguaje'])) {
                                                        $lenguajeVal = $citado['lenguaje'];
                                                    } elseif (isset($citado->lenguaje)) {
                                                        $lenguajeVal = $citado->lenguaje;
                                                    }
                                                @endphp
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">¿Requiere traductor?<span style="color:red;"> (*)</span></label>
                                                        <select name="traductor[]" id="traductor_{{$loop->index}}" class="form-control" required>
                                                            <option value="No" {{ $tradVal !== 'Si' ? 'selected' : '' }}>No</option>
                                                            <option value="Si" {{ $tradVal === 'Si' ? 'selected' : '' }}>Si</option>
                                                        </select>
                                                        <div class="invalid-feedback">Este campo es obligatorio.</div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" id="lenguaje_wrap_{{$loop->index}}" style="{{ $tradVal === 'Si' ? '' : 'display:none;' }}">
                                                    <div class="form-group">
                                                        <label for="name">¿Qué tipo de lenguaje requiere?<span style="color:red;"> (*)</span></label>
                                                        <input type="text" name="lenguaje[]" id="lenguaje_{{$loop->index}}" class="form-control" value="{{ $lenguajeVal ?? '' }}" oninput="this.value = this.value.toUpperCase()">
                                                        <div class="invalid-feedback">El lenguaje es obligatorio cuando requiere traductor.</div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Dirección del citado</h4>
                                                    </div>
                                                </div><br>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa<span style="color:red;"> (*)</span></label>
                                                        <select class="form-control" name="estado_citado[]">
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}" {{ $citado['estado_citado'] == $est['id'] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El Estado es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Municipio<span style="color:red;"> (*)</span></label>
                                                        <select class="form-control" name="municipio_citado[]">
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}" {{ $citado['municipio_citado'] == $mun['id'] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El Municipio es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de vialidad<span style="color:red;"> (*)</span></label>
                                                        <select name="vialidad_citado[]" class="form-control" required>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="CALLE"          {{ $citado['tipo_vialidad'] == 'CALLE' ? "selected" : '' }}   >Calle</option>
                                                            <option value="AVENIDA"        {{ $citado['tipo_vialidad'] == 'AVENIDA' ? "selected" : '' }} >Avenida</option>
                                                            <option value="CALZADA"        {{ $citado['tipo_vialidad'] == 'CALZADA' ? "selected" : '' }} >Calzada</option>
                                                            <option value="BOULEVARD"      {{ $citado['tipo_vialidad'] == 'BOULEVARD' ? "selected" : '' }} >Boulevard</option>
                                                            <option value="AMPLIACIÓN"     {{ $citado['tipo_vialidad'] == 'AMPLIACIÓN' ? "selected" : '' }} >Ampliación</option>
                                                            <option value="ANDADOR"        {{ $citado['tipo_vialidad'] == 'ANDADOR' ? "selected" : '' }} >Andador</option>
                                                            <option value="AUTOPISTA"      {{ $citado['tipo_vialidad'] == 'AUTOPISTA' ? "selected" : '' }} >Autopista</option>
                                                            <option value="CALLEJÓN"       {{ $citado['tipo_vialidad'] == 'CALLEJÓN' ? "selected" : '' }}>Callejón</option>
                                                            <option value="CARRETERA"      {{ $citado['tipo_vialidad'] == 'CARRETERA' ? "selected" : '' }}   >Carretera</option>
                                                            <option value="CERRADA"        {{ $citado['tipo_vialidad'] == 'CERRADA' ? "selected" : '' }} >Cerrada</option>
                                                            <option value="CIRCUITO"       {{ $citado['tipo_vialidad'] == 'CIRCUITO' ? "selected" : '' }} >Circuito</option>
                                                            <option value="CIRCUNVALACIÓN" {{ $citado['tipo_vialidad'] == 'CIRCUNVALACIÓN' ? "selected" : '' }} >Circunvalación</option>
                                                            <option value="CONTINUACIÓN"   {{ $citado['tipo_vialidad'] == 'CONTINUACIÓN' ? "selected" : '' }} >Continuación</option>
                                                            <option value="CORREDOR"       {{ $citado['tipo_vialidad'] == 'CORREDOR' ? "selected" : '' }} >Corredor</option>
                                                            <option value="DIAGONAL"       {{ $citado['tipo_vialidad'] == 'DIAGONAL' ? "selected" : '' }} >Diagonal</option>
                                                            <option value="EJE VIAL"       {{ $citado['tipo_vialidad'] == 'EJE VIAL' ? "selected" : '' }}>Eje vial</option>
                                                            <option value="PERIFÉRICO"     {{ $citado['tipo_vialidad'] == 'PERIFÉRICO' ? "selected" : '' }}   >Periférico</option>
                                                            <option value="PRIVADA"     {{ $citado['tipo_vialidad'] == 'PRIVADA' ? "selected" : '' }}   >Privada</option>
                                                            <option value="PROLONGACIÓN"   {{ $citado['tipo_vialidad'] == 'PROLONGACIÓN' ? "selected" : '' }} >Prolongación</option>
                                                            <option value="RETORNO"        {{ $citado['tipo_vialidad'] == 'RETORNO' ? "selected" : '' }} >Retorno</option>
                                                            <option value="VIADUCTO"       {{ $citado['tipo_vialidad'] == 'VIADUCTO' ? "selected" : '' }} >Viaducto</option>
                                                            <option value="PASEO"       {{ $citado['tipo_vialidad'] == 'PASEO' ? "selected" : '' }} >Paseo</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo vialidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Nombre de la vialidad<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="calle_citado[]" value="{{ $citado['calle'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo nombre de la vialidad es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>                                                
                                                
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">N° Ext.<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="n_ext_citado[]" value="{{ $citado['n_ext'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo Núm. Ext. es obligatorio.
                                                        </div>     
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">N° Int.</label>
                                                        <input type="text" class="form-control" name="n_int_citado[]" value="{{ $citado['n_int'] ?? '' }}">   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Colonia<span style="color:red;"> (*)</span></label>
                                                        <input type="hidden" name="id_citado[]" value="{{ $citado->id ?? ($citado['id'] ?? '') }}">
                                                        <input type="text" class="form-control" name="colonia_citado[]" value="{{ $citado['colonia'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo colonia es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Código postal<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="cp_citado[]" value="{{ $citado['cp'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo código postal es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Referencia<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="referencia_citado[]" value="{{ $citado['referencia'] ?? '' }}" required>
                                                        <div class="invalid-feedback">
                                                            El campo referencia es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Entre Calle</label>
                                                        <input type="text" class="form-control" name="calle1_citado[]" value="{{ $citado['calle1'] ?? '' }}">   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Y calle</label>
                                                        <input type="text" class="form-control" name="calle2_citado[]" value="{{ $citado['calle2'] ?? '' }}">   
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-2">
                                                    <div class="form-group">
                                                        <label for="name">¿Quién entregará las notificaciones?<span style="color:red;"> (*)</span></label>
                                                        <select name="notificacion[]" class="form-control" required>
                                                            <option value="Trabajador"  {{ $citado['notificacion'] == 'Trabajador' ? "selected" : '' }}>Solicitante</option>
                                                            <option value="Centro"      {{ $citado['notificacion'] == 'Centro' ? "selected" : '' }}>Centro de conciliación Laboral</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                @php
                                                    $img1 = $citado->imagen_domicilio1 ?? null;
                                                    $img2 = $citado->imagen_domicilio2 ?? null;
                                                    $solicitudId = $idSolicitud ?? $citado->solicitud_id ?? $id; 

                                                    $urlImg1 = ($img1 && $img1 !== 'Sin documento')
                                                        ? signedDocRoute('documentos.ver', [
                                                            'tipo'    => 'solicitud', 
                                                            'id'      => $solicitudId, 
                                                            'archivo' => trim($img1)
                                                        ])
                                                        : null;

                                                    $urlImg2 = ($img2 && $img2 !== 'Sin documento')
                                                        ? signedDocRoute('documentos.ver', [
                                                            'tipo'    => 'solicitud', 
                                                            'id'      => $solicitudId, 
                                                            'archivo' => trim($img2)
                                                        ])
                                                        : null;
                                                @endphp
                                                <div class="col-xs-12 col-sm-12 col-md-5" data-doc-grupo>
                                                    <label for="password">Referencia Imagen 1<span style="color:red;"> (*)</span></label><br>
                                                    @if ($urlImg1)
                                                        <a href="{{ $urlImg1 }}" target="_blank" class="btn btn-outline-info" data-doc>Ver Imagen Existente</a>
                                                    @else
                                                        <span class="text-muted">No se subió imagen</span>
                                                    @endif
                                                    <input type="file" name="foto1[]" accept="image/*" class="form-control">
                                                    <input type="hidden" name="imagen_domicilio1[]" value="{{ $citado->imagen_domicilio1 }}">
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-5" data-doc-grupo>
                                                    <label for="password">Referencia Imagen 2</label><br>
                                                    @if ($urlImg2)
                                                        <a target='_blank' href="{{ $urlImg2 }}" class="btn btn-outline-info" data-doc>Ver Imagen Existente</a>
                                                    @else
                                                        <span class="text-muted">No se subió imagen</span>
                                                    @endif
                                                    <input type="file" name="foto2[]" accept="image/*" class="form-control">
                                                    <input type="hidden" name="imagen_domicilio2[]" value="{{ $citado->imagen_domicilio2 }}">
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12"><br></div>
                                                

                                                </div>{{-- .row --}}
                                                </div>{{-- .ra-parte --}}
                                                </div>{{-- .col-12 --}}
                                            @endforeach

                                            @if ($general->tipo_solicitud == 2 && $solicitantes->isNotEmpty())
                                                <div class="col-12">
                                                <div class="ra-parte ra-seccion">
                                                    @include('audiencias._revisar_seccion_cab', ['titulo' => 'Relación laboral', 'nota' => 'Datos del trabajador citado con el patrón que solicita; se capturan una sola vez.'])
                                                    <div class="row">
                                                        <input type="hidden" name="sol[0][id]" value="{{ $solicitantes->first()->id }}">
                                                        @include('audiencias._revisar_laboral', ['solicitante' => $solicitantes->first(), 'i' => 0, 'sfx' => '_lab'])
                                                    </div>
                                                </div>
                                                </div>
                                            @endif
                                            
                                        </div>
                                    </div>
                                    <div id="citados" class="tabcontent ra-seccion">
                                        @include('audiencias._revisar_seccion_cab', ['titulo' => 'Documentos', 'nota' => 'Para reemplazar la identificación del solicitante, da clic en Editar.'])
                                        <div id="tabla_citatorio" class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <h4 class="text-center">Documentos Solicitante</h4>
                                                </div>
                                            </div><br>
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <label for="password">Identificación Oficial</label><br>
                                                <a class="btn btn-info" target="_blank" href="{{ signedDocRoute('documento_identificacion_solicitante_ver', $id) }}" data-doc @if ($general->tipo_solicitud != 2) data-doc-subir="indetificacion" @endif>Visualizar</a>
                                            </div>
                                            @if($general->tipo_solicitud != 2)
                                            <div class="col-xs-12 col-sm-12 col-md-6">
                                                <label for="password">Reemplazar Identificación Oficial</label><br>
                                                <input type="file" name="indetificacion" accept=".pdf" class="form-control">
                                            </div>
                                            @endif
                                            @include('audiencias._revisar_docs_citados')
                                            @include('audiencias._revisar_docs_representantes')
                                            {{--<div class="col-xs-12 col-sm-12 col-md-6">
                                                <label for="password">Identificación Oficial</label><br>
                                                <a target='_blank' href="../storage/app/documentosSolicitud/{{$solicitante->documentoCurp}}">PDF</a><br>
                                                <input type="file" name="documentoIdentificacion" accept=".pdf" class="form-control">
                                            </div>--}}
                                           {{-- <div class="col-xs-12 col-sm-12 col-md-6"><br>
                                                <div class="form-group">
                                                    <h4 class="text-center">Citatorios</h4>
                                                </div>
                                            </div><br>
                                            <div class="col-xs-12 col-sm-12 col-md-6"><br>
                                                <div class="form-group">
                                                    <h4 class="text-center">Notificaciones</h4>
                                                </div>        
                                            </div><br>
                                            @foreach($citados as $citado)
                                                @php
                                                    $notificado = $notificaciones->get($citado->id);
                                                @endphp

                                                <div class="row">
                                                    <div class="col-xs-12 col-sm-3 col-md-3">
                                                        <div class="form-group">
                                                            <label>Nombre Citado</label><br>
                                                            <span>{{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-3 col-md-3">
                                                        <div class="form-group">
                                                            <label>Citatorio</label><br>
                                                            <a class="btn btn-success"
                                                            href="{{ route('pdfCitatorioAudiencia', $citado->id) }}"
                                                            target="_blank">
                                                            Visualizar
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-3 col-md-3">
                                                        <div class="form-group">
                                                            <label>Nombre Citado</label><br>
                                                        <span>{{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <label>Estado de la Notificación</label><br>
                                                        {{-- Notifica el trabajador --}}
                                                        {{--@if($notificado->notificacion === "Trabajador")
                                                            <span style="color:blue">Notificación Entregada por el Solicitante</span>

                                                        {{-- No exitosa se constituye --}}
                                                        {{--@elseif($notificado->estatus === "No exitosa se constituye")
                                                            <span style="color:blue">Se Constituye</span>

                                                        {{-- Finalisada exitosamente --}}
                                                        {{--@elseif($notificado->estatus === "Finalizado exitosamente")
                                                            <span style="color:blue">Notificado Exitosamente</span>

                                                        {{-- No notificada por instructivo--}}
                                                        {{--@elseif($notificado->estatus === "No notificada")
                                                            <span style="color:blue">No Notificado</span>

                                                        {{-- No exitosa, no se cosntituye --}}
                                                        {{--@elseif($notificado->estatus === "No exitosa no se constituye")
                                                            <span style="color:blue">No se Constituye</span>

                                                        @else
                                                            <span style="color:blue">Sin Notificar</span>
                                                        @endif
                                                    </div>

                                                    <div class="col-sm-1">
                                                        <label>Notificación</label><br>
                                                        @if($notificado->notificacion === "Trabajador")
                                                            <span class="btn btn-secondary disabled" aria-disabled="true">Visualizar</span>

                                                        {{-- No exitosa se constituye --}}
                                                        {{--@elseif($notificado->estatus === "No exitosa se constituye")
                                                            <a class="btn btn-success" href="{{ route('PDFNoExitosa', ['id' => $citado->id, 'id_solicitud' => $citado->id_solicitud]) }}" target="_blank">Visualizar</a>

                                                        {{-- Finalisada exitosamente --}}
                                                        {{--@elseif($notificado->estatus === "Finalizado exitosamente")
                                                            <a class="btn btn-success" href="{{ route('PDFRazonNoticacion', ['id' => $citado->id, 'id_solicitud' => $citado->id_solicitud]) }}" target="_blank">Visualizar</a>

                                                        {{-- No notificada por instructivo--}}
                                                        {{--@elseif($notificado->estatus === "No notificada")
                                                            <a class="btn btn-success" href="{{ route('PDFInstructivo', ['id' => $citado->id, 'id_solicitud' => $citado->id_solicitud]) }}" target="_blank">Visualizar</a>

                                                        {{-- No exitosa, no se cosntituye --}}
                                                        {{--@elseif($notificado->estatus === "No exitosa no se constituye")
                                                            <a class="btn btn-success" href="{{ route('PDFNoExitosaInt', ['id' => $citado->id, 'id_solicitud' => $citado->id_solicitud]) }}"target="_blank">Visualizar</a>
                                                        
                                                        @else
                                                            <span class="btn btn-secondary disabled" aria-disabled="true">Visualizar</span>
                                                        @endif
                                                    </div>
                                                  
                                                </div>
                                            @endforeach--}}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h4 class="text-center"><strong>Acuses</strong></h4>
                                                    <hr>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Acuse de Confirmación</strong>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <a class="btn btn-info"
                                                            href="{{ route('PDFacuseConfirmada', $general['id']) }}"
                                                            target="_blank">Visualizar</a>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Acuse de Solicitud</strong>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <a class="btn btn-info"
                                                            href="{{ route('PDFacuse_solicitud', $general['id']) }}"
                                                            target="_blank">Visualizar</a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <h4 class="text-center"><strong>Notificación al Solicitante</strong></h4>
                                                    <hr>

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label><strong>Nombre Solicitante</strong></label><br>
                                                            <span>{{ $solicitante->nombre }}</span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <a class="btn btn-info"
                                                            href="{{ route('PDFnotificacion_solicitante', $general['id']) }}"
                                                            target="_blank">Visualizar</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <h4 class="text-center"><strong>Actas de Audiencia</strong></h4>
                                                    <hr>
                                                    <table class="table table-borderless" style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
                                                        <tbody>
                                                            @forelse($historial_audiencias as $audiencia)
                                                                <tr style="border: none;">
                                                                    <td style="padding: 5px 0; border: none;">
                                                                        <strong>Folio:</strong> {{ $audiencia->folio_audiencia }}
                                                                    </td>
                                                                    <td style="padding: 5px 0; border: none;">
                                                                        <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($audiencia->fecha)->translatedFormat('d/m/Y') }}
                                                                    </td>
                                                                    <td style="padding: 5px 0; border: none;">
                                                                        <strong>Hora:</strong> {{ \Carbon\Carbon::parse($audiencia->hora)->format('H:i') }} HRS.
                                                                    </td>
                                                                    <td style="padding: 5px 0; border: none;">
                                                                        <strong>Estatus:</strong> {{ $audiencia->estatus ?? 'Pendiente' }}
                                                                    </td>
                                                                    <td style="padding: 5px 0; border: none; text-align: right;">
                                                                        @if($audiencia->estatus == 'Pendiente' || $audiencia->estatus == 'Archivada' || $audiencia->estatus == 'Incompetencia' || $audiencia->estatus == 'Reagendada' || $audiencia->estatus == 'Desistimiento')
                                                                        @else
                                                                            <a class="btn btn-info btn-xs" href="{{ route('VerPDFAudiencia', $audiencia->id_solicitud) . '?audiencia_id=' . $audiencia->id }}" target="_blank">
                                                                                Visualizar
                                                                            </a>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" style="border: none; padding: 10px 0;">
                                                                        No hay audiencias registradas para esta solicitud.
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <h4 class="text-center"><strong>Multas</strong></h4>
                                                    <hr>
                                                    <div class="form-group">
                                
                                                            @foreach($citados as $citado) 
                                                                @if($citado->tipo_notificacion === 'Multa' && $citado->resulte_responsable === 'No')
                                                                    <div class="row" style="margin-bottom: 5px; border-bottom: 1px dotted #ccc; padding: 5px 0;">
                                                                        <div class="col-sm-5">
                                                                            <label>Nombre Citado</label><br>
                                                                            <strong>{{ $citado->nombre }} {{ $citado->primer_apellido }}</strong>
                                                                        </div>
                                                                        
                                                                        <div class="col-sm-4">
                                                                            <label>Multa</label><br>
                                                                            <a class="btn btn-success btn-xs" 
                                                                            href="{{ route('PDFmulta', ['id' => $citado->id, 'id_solicitud' => $general->id]) }}" 
                                                                            target="_blank">
                                                                            Visualizar
                                                                            </a>
                                                                        </div>
                                                                    </div>

                                                                @endif
                                                            @endforeach
                                                        
                                                    </div>
                                                </div>
                                            </div>

                                            {{--<div class="row mt-4">
                                                <div class="col-md-6">
                                                    <h4 class="text-center"><strong>Actas de Audiencia</strong></h4>
                                                    <hr>
                                                    @forelse($historial_audiencias as $audiencia)
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>Folio: {{ $audiencia->folio_audiencia }}</strong>
                                                            </div>
                                                            <div>
                                                                <strong>Estatus: {{ $audiencia->estatus_conciliacion }}  @if($audiencia->estatus_conciliacion === 'Pendiente')<span>Pendiente</span></strong>
                                                            </div>
                                                        {{--<a class="btn btn-info"
                                                            href="{{ route('VerPDFAudiencia', $audiencia->id_solicitud) . '?audiencia_id=' . $audiencia->id }}"
                                                            target="_blank">Visualizar</a>--}}
                                                           {{-- @if(!($audiencia->estatus_conciliacion === 'Pendiente' && $datosIncompletos))
                                                                <a class="btn btn-info"
                                                                href="{{ route('VerPDFAudiencia', $audiencia->id_solicitud) . '?audiencia_id=' . $audiencia->id }}"
                                                                target="_blank">
                                                                    Visualizar
                                                                </a>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <p>No hay audiencias registradas para esta solicitud.</p>
                                                    @endforelse
                                                </div>
                                            </div>--}}
                                            
                                           {{-- @foreach($citados as $citado)
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="password">Nombre Citado</label><br>
                                                        <span>{{ $citado->nombre }} {{ $citado->primer_apellido }} {{ $citado->segundo_apellido }}</span>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="password">Citatorio</label><br>
                                                        <a class="btn btn-success" href="{{ route('pdfCitatorioAudiencia', $citado->id) }}"  target="_blank">Visualizar</a>
                                                    </div>
                                                </div>
                                            @endforeach--}}
                                            
                                            <!--div class="col-xs-12 col-sm-12 col-md-12">
                                                @if($edicionAbierta)
                                                    <button type="submit" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Guardar</button>
                                                @endif
                                                <a href="{{ route('todas_audiencias') }}" class="btn btn-primary" style="background-color:#CEA845; border-color:#CEA845;">Regresar</a>
                                            </!--div-->
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>

        //No toquen esto, funciona :)
        
        // Manejador para mostrar feedback de validación en formularios con pestañas.
        document.addEventListener('DOMContentLoaded', function () {
            var forms = document.querySelectorAll('form.needs-validation');
            if (!forms || forms.length === 0) return;

            forms.forEach(function(form) {
                // Capturar el evento invalid en fase de captura para abrir la pestaña
                // que contiene el control inválido antes de que el navegador intente enfocarlo.
                form.addEventListener('invalid', function (e) {
                    try { e.preventDefault(); e.stopPropagation(); } catch (err) {}
                    var invalidEl = e.target;
                    var tabContainer = invalidEl.closest('.tabcontent');
                    if (tabContainer && tabContainer.id) {
                        var selector = '.tab a[onclick*="' + tabContainer.id + '"]';
                        var tabBtn = document.querySelector(selector);
                        if (tabBtn) {
                            try { tabBtn.click(); } catch (err) { /* ignore */ }
                        } else if (typeof openCity === 'function') {
                            try { openCity(new Event('click'), tabContainer.id); } catch (err) { /* ignore */ }
                        }
                    }
                    setTimeout(function () { try { invalidEl.focus(); } catch (err) { /* ignore */ } }, 80);
                }, true);

                form.addEventListener('submit', function (e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        form.classList.add('was-validated');

                        // Mostrar SweetAlert (fallback a alert si no está disponible)
                        if (typeof swal === 'function') {
                            swal("Error", "Por favor corrija los campos obligatorios.", "error");
                        } else {
                            try { alert('Por favor corrija los campos obligatorios.'); } catch (err) { /* ignore */ }
                        }

                        var firstInvalid = form.querySelector(':invalid');
                        if (firstInvalid) {
                            var tabContainer = firstInvalid.closest('.tabcontent');
                            if (tabContainer && tabContainer.id) {
                                var selector = '.tab a[onclick*="' + tabContainer.id + '"]';
                                var tabBtn = document.querySelector(selector);
                                if (tabBtn) {
                                    try { tabBtn.click(); } catch (err) { /* ignore */ }
                                } else if (typeof openCity === 'function') {
                                    try { openCity(new Event('click'), tabContainer.id); } catch (err) { /* ignore */ }
                                }
                            }
                            setTimeout(function () { try { firstInvalid.focus(); } catch (err) { /* ignore */ } }, 80);
                        }
                    }
                }, false);
            });
        });
        </script>

    </section>
@endsection


@push('modals')
<!-- Modal -->
<div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate' method='POST' action="{{route('agregar_citado_edicion')}}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Citado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">                                    
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <label for="name">¿Agregar "Quien resulte responsable"? <span style="color:red;">(*)</span></label>
                                <select name="responsable" id="responsable" class="form-control" required>
                                    <option value="">SELECCIONE</option>
                                    <option value="Si">Si</option>
                                    <option value="No">No</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo es obligatorio.
                                </div>
                            </div>
                        </div>
                        @php
                            $hasCitados = isset($citados) && count($citados) > 0;
                            $defaultNotificacion = '';
                            if ($hasCitados) {
                                $hasCentro = $citados->where('notificacion', 'Centro')->count() > 0;
                                $defaultNotificacion = $hasCentro ? 'Centro' : 'Trabajador';
                            }
                        @endphp
                        @if($hasCitados)
                            <input type="hidden" name="notificacion" value="{{ $defaultNotificacion }}">
                        @else
                            <div class="col-xs-12 col-sm-12 col-md-6">
                              <div class="form-group">
                                  <label for="name">¿Quién entregará las notificaciones? <span style="color:red;">(*)</span></label>
                                  <select name="notificacion" class="form-control" required>
                                      <option value="">SELECCIONE</option>
                                      <option value="Trabajador">Solicitante</option>
                                      <option value="Centro">Centro de conciliación Laboral</option>
                                  </select>
                                  <div class="invalid-feedback">
                                      El campo es obligatorio.
                                  </div>
                              </div>
                            </div>
                        @endif
                          <div class="row">
                              <div class="col-xs-12 col-sm-12 col-md-2">
                                  <div class="form-group">
                                      <label for="name">Tipo de persona <span style="color:red;">(*)</span></label>
                                      <select name="tipo" id="tipo" class="form-control" required>
                                          <option value="">Seleccione</option>
                                          <option value="Fisica">Física</option>
                                          <option value="Moral">Moral</option>
                                      </select>
                                      <div class="invalid-feedback">
                                          El tipo de persona es obligatorio.
                                      </div>
                                  </div>
                              </div>

                              <div class="col-xs-12 col-sm-12 col-md-4" id="campo_curp">
                                  <div class="form-group">
                                      <label for="name">CURP (Opcional)</label>
                                      <input type="text" name="curp" id="curp_input" oninput="validarInput(this)" class="form-control"> 
                                      <pre id="resultado"></pre>
                                      <div class="invalid-feedback">
                                          El nombre es obligatorio.
                                      </div>
                                  </div>
                              </div>
                              <div class="col-xs-12 col-sm-12 col-md-4" id="tipoPersona_razon" style="display:none;">
                                  <div class="form-group">
                                      <label for="name">Razón social <span style="color:red;">(*)</span></label>
                                      <input type="text" name="razon" id="razon" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                      <div class="invalid-feedback">
                                          La razón social es obligatorio.
                                      </div>
                                  </div>
                              </div>
                              <div class="col-xs-12 col-sm-12 col-md-3">
                                  <div class="form-group">
                                      <label for="name">RFC (Opcional)</label>
                                      <input type="text" name="rfc" class="form-control" minlength="13" maxlength="13" oninput="this.value = this.value.toUpperCase()"> 
                                      <div class="invalid-feedback">
                                           El campo RFC es obligatorio.
                                      </div>
                                  </div>
                              </div>
    
                              <div class="col-xs-12 col-sm-12 col-md-3">
                                  <div class="form-group">
                                      <label for="name">¿Requiere algún traductor? <span style="color:red;">(*)</span></label>
                                        <select name="traductor" id="traductor_modal" class="form-control" required>
                                       <option value="">SELECCIONE</option>
                                       <option value="Si">Si</option>
                                       <option value="No">No</option>
                                    </select>
                                </div>
                            </div>
                                <div class="col-xs-12 col-sm-12 col-md-3" id="lenguaje_modal_wrap" style="display:none;">
                                <div class="form-group">
                                    <label for="name">¿Qué tipo de lenguaje require? <span style="color:red;">(*)</span></label>
                                        <input type="text" name="lenguaje" id="lenguaje_modal" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                        La nacionalidad es obligatoria.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12" id="tipoPersona_nombre" style="display:none;">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Nombre(s) <span style="color:red;">(*)</span></label>
                                            <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                            <input type="text" name="primer_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Segundo apellido</label>
                                            <input type="text" name="segundo_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12" style="background-color:#D2D3D5; color:black; width:100%; height:30px;">
                            <div class="form-group">
                                <h4 class="text-center">Dirección de la fuente de empleo</h4>
                            </div>
                        </div>   
                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                            <div class="form-group">
                                <label for="name">Tipo de vialidad <span style="color:red;">(*)</span></label>
                                <select name="vialidad" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                                    <option value="">SELECCIONE</option>
                                    <option value="AMPLIACIÓN">Ampliación</option>
                                    <option value="ANDADOR">Andador</option>
                                    <option value="AUTOPISTA">Autopista</option>
                                    <option value="AVENIDA">Avenida</option>
                                    <option value="BOULEVARD">Boulevard</option>
                                    <option value="CALLE">Calle</option>
                                    <option value="CALLEJÓN">Callejón</option>
                                    <option value="CALZADA">Calzada</option>
                                    <option value="CARRETERA">Carretera</option>
                                    <option value="CERRADA">Cerrada</option>
                                    <option value="CIRCUITO">Circuito</option>
                                    <option value="CIRCUNVALACIÓN">Circunvalación</option>
                                    <option value="CONTINUACIÓN">Continuación</option>
                                    <option value="CORREDOR">Corredor</option>
                                    <option value="DIAGONAL">Diagonal</option>
                                    <option value="EJE VIAL">Eje vial</option>
                                    <option value="PERIFÉRICO">Periférico</option>
                                    <option value="PRIVADA">Privada</option>
                                    <option value="PROLONGACIÓN">Prolongación</option>
                                    <option value="RETORNO">Retorno</option>
                                    <option value="VIADUCTO">Viaducto</option>
                                    <option value="PASEO">Paseo</option>
                                </select>
                                <div class="invalid-feedback">
                                    El campo vialidad es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                            <div class="form-group">
                                <label for="name">Nombre de la vialidad <span style="color:red;">(*)</span></label>
                                <input type="text" name="calle" class="form-control" required oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo calle es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                            <div class="form-group">
                                <label for="name">Colonia <span style="color:red;">(*)</span></label>
                                <input type="text" name="colonia" class="form-control" required oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo colonia es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-3"><br>
                            <div class="form-group">
                                <label for="name">Entre calle (Opcional)</label>
                                <input type="text" name="calle1" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo calle es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="name">y calle (Opcional)</label>
                                <input type="text" name="calle2" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo calle es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-2">
                            <div class="form-group">
                                <label for="name">Núm. Ext. <span style="color:red;">(*)</span></label>
                                <input type="text" name="exterior" class="form-control" required oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo núm. ext. es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-2">
                            <div class="form-group">
                                <label for="name">Núm. Int. (Opcional)</label>
                                <input type="text" name="interior" class="form-control" oninput="this.value = this.value.toUpperCase()"> 
                                <div class="invalid-feedback">
                                    El campo núm. int. es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-2">
                            <div class="form-group">
                                <label for="name">Código postal <span style="color:red;">(*)</span></label>
                                <input type="text" name="cp" class="form-control" minlength="5" maxlength="5" required> 
                                <div class="invalid-feedback">
                                    El campo Código Postal es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="name">Estado <span style="color:red;">(*)</span></label>
                                <select id="estado_citado" class="form-control" name="estado_citado" required oninput="this.value = this.value.toUpperCase()">
                                    <option value="">Seleccione</option>
                                    @foreach($estados as $es)
                                        <option value="{{$es['id']}}">{{$es['nombre']}}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    El campo Estado es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <div class="form-group">
                                <label for="name">Municipio o Alcaldía <span style="color:red;">(*)</span></label>
                                <select id="municipio_citado" class="form-control" name="municipio_citado" required oninput="this.value = this.value.toUpperCase()">
                                    <option value="">SELECCIONE</option>
                                    @foreach($municipios as $mun)
                                        <option value="{{$mun['id']}}">{{$mun['nombre']}}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    El campo municipio o alcaldía es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-9">
                            <div class="form-group">
                            <label for="floatingTextarea">Referencias del domicilio <span style="color:red;">(*)</span></label>
                                <textarea class="form-control" placeholder="Ingresa alguna referencia de como llegar" name="referencia" required oninput="this.value = this.value.toUpperCase()"></textarea>
                                <div class="invalid-feedback">
                                    El campo referencias es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6">
                            <div class="form-group">
                                <label for="name">Referencia 1 <span style="color:red;">(*)</span></label>
                                <input type="file" class="form-control" name="foto1" accept="image/*" required>
                                <div class="invalid-feedback">
                                    El campo imagen 1 es obligatorio.
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6">
                            <div class="form-group">
                                <label for="name">Referencia 2 (Opcional)</label>
                                <input type="file" class="form-control" name="foto2" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- Modal -->
    <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        @csrf
        <input type="hidden" name="id" value="{{$id}}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Borrar citado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped mt-2">
                            <thead style="background-color: #354647;">
                                <th style="color: #fff;">Nombre</th>
                                <th style="color: #fff;">Tipo de Notificación</th>
                                <th style="color: #fff;">Dirección</th>
                                <th style="color: #fff;">Acciones</th>
                            </thead>
                            <tbody>
                                @foreach($citados as $citado)
                                <tr>
                                    <td>{{$citado->nombre}}</td>
                                    @if($citado->tipo_notificacion == 'Multa')
                                    <td>{{$citado->tipo_notificacion}}</td>
                                    @else
                                    <td>{{$citado->notificacion}}</td>
                                    @endif
                                    <td>{{ $citado->colonia }} {{ $citado->calle }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('borrar_citado_edicion') }} ">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="id" value="{{$id}}">
                                            <input type="hidden" name="borrar" value="{{$citado->id}}">
                                            <button type="button" class="btn btn-danger ra-borrar-citado"
                                                data-nombre="{{ trim(preg_replace('/\s+/', ' ', $citado->nombre.' '.$citado->primer_apellido.' '.$citado->segundo_apellido)) }}"
                                                data-notificacion="{{ $citado->tipo_notificacion == 'Multa' ? 'Multa' : ['Centro' => 'La hace el Centro', 'Trabajador' => 'La entrega el solicitante', 'Exhorto' => 'Por exhorto'][$citado->notificacion] ?? ($citado->notificacion ?: '—') }}"
                                                data-direccion="{{ trim($citado->calle.' '.$citado->n_ext.', '.$citado->colonia, ' ,') }}"><i class="bi bi-trash"></i> Eliminar</button>
                                        </form>
                                    </td>   
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

<div id="menu_carga" style ="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

{{-- Confirmación antes de borrar un citado (botón Eliminar de "Borrar citado"). --}}
<div class="modal fade" id="raConfirmarBorrar" tabindex="-1" aria-labelledby="raConfirmarBorrarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="raConfirmarBorrarTitulo"><i class="bi bi-exclamation-triangle" style="color:#9A3A22"></i> ¿Borrar este citado?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Vas a quitar de la solicitud a <b data-ra-nombre></b>.</p>
                <dl class="ra-confirmar__datos">
                    <div><dt>Notificación</dt><dd data-ra-notificacion></dd></div>
                    <div><dt>Dirección</dt><dd data-ra-direccion></dd></div>
                </dl>
                <p class="ra-confirmar__nota mb-0"><i class="bi bi-info-circle"></i> Se borra definitivamente al dar <b>Guardar edición</b>; si no guardas, el citado se queda.</p>
                <p class="ra-confirmar__aviso mb-0 mt-2" data-ra-cambios hidden><i class="bi bi-exclamation-circle"></i> Tienes cambios sin guardar en el formulario: la página se recarga al borrar y esos cambios se perderán. Si los necesitas, cancela y guarda primero.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" data-ra-confirmar><i class="bi bi-trash"></i> Sí, borrar</button>
            </div>
        </div>
    </div>
</div>
@endpush

@section('scripts')
    <script>
        // Documentos que ya no existen en el servidor de archivos: cada enlace
        // con data-doc se comprueba con una petición HEAD (no descarga nada;
        // SeerController::verPDF y documento_identificacion_solicitante_ver
        // responden 200 o 404). Si falta, el botón cambia por "No se encontró
        // el archivo" y, donde se puede, "Subir de nuevo".
        // Los enlaces van firmados por 60 minutos: si la página lleva más
        // tiempo abierta, al dar clic se avisa que hay que recargar.
        document.addEventListener('DOMContentLoaded', function () {
            var cargada = Date.now(), VIGENCIA = 55 * 60 * 1000;
            var enlaces = Array.prototype.slice.call(document.querySelectorAll('a[data-doc]'));
            if (!enlaces.length) { return; }

            function subirDe(a) {
                var nombre = a.getAttribute('data-doc-subir');
                if (nombre) { return document.querySelector('input[type=file][name="' + nombre + '"]'); }
                var grupo = a.closest('[data-doc-grupo]');
                return grupo ? grupo.querySelector('input[type=file]') : null;
            }

            function falta(a) {
                var aviso = document.createElement('span');
                aviso.className = 'ra-doc-falta';
                aviso.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> No se encontró el archivo';
                aviso.title = 'El archivo está registrado pero ya no está en el servidor de archivos.';
                var input = subirDe(a);
                if (input) {
                    var boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'btn btn-sm btn-outline-secondary ra-doc-subir-btn';
                    boton.innerHTML = '<i class="bi bi-upload"></i> Subir de nuevo';
                    boton.addEventListener('click', function () {
                        if (window.raSubirDeNuevo) { window.raSubirDeNuevo(input); } else { input.click(); }
                    });
                    aviso.appendChild(boton);
                }
                a.replaceWith(aviso);
            }

            // De 4 en 4 para no saturar el servidor de archivos.
            var cola = enlaces.slice();
            function siguiente() {
                var a = cola.shift();
                if (!a) { return; }
                a.classList.add('ra-doc-revisando');
                fetch(a.href, { method: 'HEAD', credentials: 'same-origin' })
                    .then(function (r) {
                        a.classList.remove('ra-doc-revisando');
                        if (r.status === 404) { falta(a); }
                    })
                    .catch(function () { a.classList.remove('ra-doc-revisando'); })
                    .then(siguiente);
            }
            for (var i = 0; i < 4; i++) { siguiente(); }

            document.addEventListener('click', function (e) {
                var a = e.target.closest && e.target.closest('a[data-doc]');
                if (!a || Date.now() - cargada < VIGENCIA) { return; }
                e.preventDefault();
                var recargar = function () { window.raSaltarAvisoSalida = true; location.reload(); };
                if (typeof Swal === 'undefined') {
                    if (confirm('Los enlaces a los documentos caducaron (la página lleva más de una hora abierta). ¿Recargar la página?')) { recargar(); }
                    return;
                }
                Swal.fire({
                    icon: 'info', title: 'Hay que recargar la página',
                    text: 'Los enlaces a los documentos duran una hora y esta página lleva más tiempo abierta. Si tienes cambios sin guardar, guárdalos primero.',
                    showCancelButton: true, confirmButtonText: 'Recargar', cancelButtonText: 'Cancelar',
                    reverseButtons: true, confirmButtonColor: '#496163'
                }).then(function (r) { if (r.isConfirmed) { recargar(); } });
            });
        });
    </script>
    {{-- SweetAlert2 (como en el resto de Administración): confirmación al salir sin guardar. --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('assets/js/estadistica/estadistica.js') }}"></script>
    <script>
            $(function(){
                $('#motivo_solicitud').on('change', validarcheckfolio);
            })


            let motivosSeleccionados = [];

            function validarcheckfolio(){
                var opcionSeleccionada = $(this).val();
                var opcionTexto = $("#motivo_solicitud option:selected").text();

                // Verifica si ya fue agregado ese motivo
                if (motivosSeleccionados.includes(opcionSeleccionada)) {
                    alert('Este motivo ya ha sido seleccionado.');
                    $(this).val('');
                    return;
                }

                motivosSeleccionados.push(opcionSeleccionada);

                $('#tabla tbody').append(
                    '<tr data-id="' + opcionSeleccionada + '">' +
                        '<td>' + opcionTexto + '</td>' +
                        '<td><button type="button" class="eliminar btn btn-danger btn-sm">Eliminar</button></td>' +
                    '</tr>'
                );

                $('#div1').append(
                    '<input type="hidden" name="motivo_solicitud[]" value="' + opcionSeleccionada + '" id="input-motivo-' + opcionSeleccionada + '">'
                );

                try { updateMotivosState(); } catch (err) { /* ignore */ }

                // Reinicia el select
                $(this).val('');
            }

            //Tipo de persona
            document.addEventListener('DOMContentLoaded', function () {
                const selectTipo = document.getElementById('tipo');
                const nombreDiv = document.getElementById('tipoPersona_nombre');
                const razonDiv = document.getElementById('tipoPersona_razon');
                const curpDiv = document.getElementById('campo_curp');

                function actualizarTipoPersona() {
                    // Evita errores si los contenedores no existen en esta vista
                    if (!selectTipo || !nombreDiv || !razonDiv || !curpDiv) return;
                    const valor = selectTipo.value;

                    nombreDiv.style.display = 'none';
                    razonDiv.style.display = 'none';
                    curpDiv.style.display = 'none';

                    if (valor === 'Fisica') {
                        nombreDiv.style.display = 'block';
                        curpDiv.style.display = 'block';
                    } else if (valor === 'Moral') {
                        razonDiv.style.display = 'block';
                    }
                }

                if (selectTipo) {
                    selectTipo.addEventListener('change', actualizarTipoPersona);
                    // Ejecutar al cargar por si ya tiene valor
                    actualizarTipoPersona();
                }

                // Traductor, discapacidad y fecha de salida de cada solicitante. Los id
                // llevan sufijo: '' el primero, _1, _2… los demás y _lab la relación
                // laboral cuando solicita el patrón.
                function alternar(sel, div, input, valor) {
                    if (!sel || !div) return;
                    var aplicar = function () {
                        var ver = sel.value === valor;
                        div.hidden = !ver;
                        if (input) input.required = ver;
                    };
                    sel.addEventListener('change', aplicar);
                    aplicar();
                }
                document.querySelectorAll('[id^="needsLanguageSolicitante"]').forEach(function (sel) {
                    var sfx = sel.id.slice('needsLanguageSolicitante'.length);
                    alternar(sel, document.getElementById('languageRequired' + sfx), document.getElementById('languageValueSolicitante' + sfx), 'Si');
                });
                document.querySelectorAll('[id^="hasDisabilitySolicitante"]').forEach(function (sel) {
                    var sfx = sel.id.slice('hasDisabilitySolicitante'.length);
                    alternar(sel, document.getElementById('disabilityRequired' + sfx), document.getElementById('disabilityValueSolicitante' + sfx), 'Si');
                });
                document.querySelectorAll('[id^="laboraActualmenteValue"]').forEach(function (sel) {
                    var sfx = sel.id.slice('laboraActualmenteValue'.length);
                    alternar(sel, document.getElementById('fechaSalida' + sfx), null, 'No');
                });

                // Traductor y lenguaje en modal Agregar Citado (sincroniza visibilidad y required)
                var tradModal = document.getElementById('traductor_modal');
                var langWrapModal = document.getElementById('lenguaje_modal_wrap');
                var langInputModal = document.getElementById('lenguaje_modal');
                function syncModalLenguaje(){
                    if (!tradModal || !langWrapModal || !langInputModal) return;
                    if (tradModal.value === 'Si') {
                        langWrapModal.style.display = '';
                        langInputModal.required = true;
                    } else {
                        langWrapModal.style.display = 'none';
                        langInputModal.required = false;
                        try { langInputModal.value = ''; } catch (err) {}
                    }
                }
                if (tradModal) {
                    syncModalLenguaje();
                    tradModal.addEventListener('change', syncModalLenguaje);
                }

                // Traductor por citado 
                function syncLenguajeRequired(index) {
                    var sel = document.getElementById('traductor_' + index);
                    var wrap = document.getElementById('lenguaje_wrap_' + index);
                    var input = document.getElementById('lenguaje_' + index);
                    if (!sel || !wrap || !input) return;
                    if (sel.value === 'Si') {
                        wrap.style.display = '';
                        input.required = true;
                    } else {
                        wrap.style.display = 'none';
                        input.required = false;
                        try { input.value = ''; } catch (err) {}
                    }
                }

                var traductores = document.querySelectorAll('[id^="traductor_"]');
                traductores.forEach(function(sel){
                    var idx = sel.id.replace('traductor_', '');
                    syncLenguajeRequired(idx);
                    sel.addEventListener('change', function(){ syncLenguajeRequired(idx); });
                });


                const forms = document.querySelectorAll('form.needs-validation');
                    if (forms && forms.length) {
                    // Función para validar teléfonos en tiempo real
                    function updateTelefonoState(form){
                        try{
                            var tel1 = form.querySelector('input[name$="[telefono1_solicitante]"]');
                            var tel2 = form.querySelector('input[name$="[telefono2_solicitante]"]');
                            var validTel1 = true;
                            var validTel2 = true;

                            if(tel1){
                                var d1 = (tel1.value || '').replace(/\D/g, '');
                                if(d1.length !== 10){
                                    tel1.classList.add('is-invalid');
                                    tel1.classList.remove('is-valid');
                                    try { tel1.setCustomValidity('El teléfono celular debe tener 10 dígitos'); } catch(e){}
                                    validTel1 = false;
                                } else {
                                    tel1.classList.remove('is-invalid');
                                    tel1.classList.add('is-valid');
                                    try { tel1.setCustomValidity(''); } catch(e){}
                                    validTel1 = true;
                                }
                            }

                            if(tel2){
                                var d2 = (tel2.value || '').replace(/\D/g, '');
                                if(tel2.value && d2.length !== 10){
                                    tel2.classList.add('is-invalid');
                                    tel2.classList.remove('is-valid');
                                    try { tel2.setCustomValidity('El teléfono fijo debe tener 10 dígitos'); } catch(e){}
                                    validTel2 = false;
                                } else {
                                    tel2.classList.remove('is-invalid');
                                    if(d2.length === 10) tel2.classList.add('is-valid'); else tel2.classList.remove('is-valid');
                                    try { tel2.setCustomValidity(''); } catch(e){}
                                    validTel2 = true;
                                }
                            }

                            var confirmArea = document.getElementById('tabla_confirmar');
                            if(confirmArea){
                                var existing = document.getElementById('telefono-warning');
                                if(!(validTel1 && validTel2)){
                                    if(!existing){
                                        var div = document.createElement('div');
                                        div.id = 'telefono-warning';
                                        div.className = 'text-muted mt-2';
                                        div.innerText = 'Por favor ingrese teléfonos válidos (10 dígitos) antes de guardar.';
                                        confirmArea.appendChild(div);
                                    }
                                } else { if(existing) existing.remove(); }
                            }
                        }catch(err){ console.warn('updateTelefonoState', err); }
                    }

                    forms.forEach(function(form) {
                        // Validación en tiempo real: escuchar inputs de teléfono
                        var tel1input = form.querySelector('input[name$="[telefono1_solicitante]"]');
                        var tel2input = form.querySelector('input[name$="[telefono2_solicitante]"]');
                        if(tel1input){ tel1input.addEventListener('input', function(){ updateTelefonoState(form); }); tel1input.addEventListener('blur', function(){ updateTelefonoState(form); }); }
                        if(tel2input){ tel2input.addEventListener('input', function(){ updateTelefonoState(form); }); tel2input.addEventListener('blur', function(){ updateTelefonoState(form); }); }

                        form.addEventListener('submit', function(e) {
                            var valid = true;
                            var tel1 = form.querySelector('input[name$="[telefono1_solicitante]"]');
                            var tel2 = form.querySelector('input[name$="[telefono2_solicitante]"]');
                            if (tel1 && tel1.value.replace(/\D/g, '').length !== 10) {
                                if (typeof swal === 'function') { swal("Error", "El teléfono celular debe tener exactamente 10 dígitos.", "error"); } else { try { alert('El teléfono celular debe tener exactamente 10 dígitos.'); } catch(e){} }
                                try { tel1.focus(); } catch(e){}
                                valid = false;
                            }
                            if (tel2 && tel2.value && tel2.value.replace(/\D/g, '').length !== 10) {
                                if (typeof swal === 'function') { swal("Error", "El teléfono fijo debe tener exactamente 10 dígitos.", "error"); } else { try { alert('El teléfono fijo debe tener exactamente 10 dígitos.'); } catch(e){} }
                                try { tel2.focus(); } catch(e){}
                                valid = false;
                            }

                            // Asegurar que el estado más reciente se aplique
                            try { updateTelefonoState(form); } catch(e){}

                            if (!valid) {
                                e.preventDefault();
                                e.stopPropagation();
                                form.classList.add('was-validated');
                            }
                        });
                    });
                }
            });

            // Eliminar fila e input hidden
            $(document).on('click', '.eliminar', function() {
                var fila = $(this).closest('tr');
                var idMotivo = fila.attr('data-id');

                // Elimina input y fila
                $('#input-motivo-' + idMotivo).remove();
                fila.remove();

                // Actualiza la lista de los motivos seleccionados
                motivosSeleccionados = motivosSeleccionados.filter(id => id !== idMotivo);
                
                try { updateMotivosState(); } catch (err) { /* ignore */ }
            });
        
            $('#tabla_detalles').show();
            $('#tabla_solicitante').show();
            $('#tabla_citados').show();
            $('#tabla_documentos').show();
            
            // Verifica si hay al menos un motivo y habilita/deshabilita los botones de confirmación en esta vista
            function updateMotivosState(){
                try{
                    var hidden = document.querySelectorAll('input[name="motivo_solicitud[]"]');

                    var existingRows = 0;
                    var tables = document.querySelectorAll('table');
                    tables.forEach(function(t){
                        try{
                            var th = t.querySelector('thead th');
                            if(!th) return;
                            var txt = th.textContent || th.innerText || '';
                            if(txt.trim().toLowerCase().indexOf('motivo capturado') !== -1){
                                var tb = t.tBodies[0];
                                if(tb) existingRows = tb.rows.length;
                            }
                        }catch(e){}
                    });

                    var totalMotivos = (hidden ? hidden.length : 0) + (existingRows ? existingRows : 0);
                    var hasMotivo = totalMotivos > 0;

                    var confirmArea = document.getElementById('tabla_confirmar');
                    if(!confirmArea) return;
                    var buttons = confirmArea.querySelectorAll('button[type="submit"], button[name="toquen"]');
                    buttons.forEach(function(btn){
                        if(btn.hasAttribute('data-ignore-motivo')) return;
                        btn.disabled = !hasMotivo;
                    });
                    var existing = document.getElementById('motivo-warning');
                    if(!hasMotivo){
                        if(!existing){
                            var div = document.createElement('div');
                            div.id = 'motivo-warning';
                            div.className = 'text-muted mt-2';
                            div.innerText = 'Debe agregar al menos un motivo para poder guardar cambios.';
                            confirmArea.appendChild(div);
                        }
                    } else { if(existing) existing.remove(); }
                }catch(err){ console.warn('updateMotivosState', err); }
            }
            try { document.addEventListener('DOMContentLoaded', updateMotivosState); } catch(err){}
    </script>
    <script>
        function syncTipoCitado(index){
            try {
                var sel = document.querySelectorAll('select[name="tipo_persona_citado[]"]')[index];
                if(!sel) return;

                var nombreInput = document.querySelectorAll('input[name="nombre_citado[]"]')[index];
                var primerInput = document.querySelectorAll('input[name="primer_apellido[]"]')[index];
                var segundoInput = document.querySelectorAll('input[name="segundo_apellido[]"]')[index];

                var nombreWrap = nombreInput ? nombreInput.closest('.form-group').parentElement : null;
                var primerWrap = primerInput ? primerInput.closest('.form-group').parentElement : null;
                var segundoWrap = segundoInput ? segundoInput.closest('.form-group').parentElement : null;

                if (sel.value === 'Moral') {
                    // Tipo de persona moral solo puede visualizar el nombre
                    if (nombreWrap) nombreWrap.style.display = '';
                    if (primerWrap) primerWrap.style.display = 'none';
                    if (segundoWrap) segundoWrap.style.display = 'none';
                    if (nombreInput) nombreInput.required = true;
                    if (primerInput) primerInput.required = false;
                    if (segundoInput) segundoInput.required = false;
                } else {
                    // Tipo de persona física visualiza los 3
                    if (nombreWrap) nombreWrap.style.display = '';
                    if (primerWrap) primerWrap.style.display = '';
                    if (segundoWrap) segundoWrap.style.display = '';
                    if (nombreInput) nombreInput.required = true;
                    if (primerInput) primerInput.required = true;
                    if (segundoInput) segundoInput.required = false;
                }
                // Si este citado es el 'quien resulte'
                try {
                    var resulteFlag = sel.getAttribute('resulte_flag');
                    if (resulteFlag === 'Si') {
                        if (primerWrap) primerWrap.style.display = 'none';
                        if (segundoWrap) segundoWrap.style.display = 'none';
                        if (primerInput) primerInput.required = false;
                        if (segundoInput) segundoInput.required = false;
                    }
                } catch (err) {}
            } catch (err) {
                console.warn('syncTipoCitado error', err);
            }
        }

        document.addEventListener('DOMContentLoaded', function(){
            var selects = document.querySelectorAll('select[name="tipo_persona_citado[]"]');
            selects.forEach(function(sel, idx){
                syncTipoCitado(idx);
                sel.addEventListener('change', function(){ syncTipoCitado(idx); });
            });
        });
         // Función para convertir todo el texto a mayúsculas
        function convertirAMayusculas() {
            const elementos = document.querySelectorAll('input[type="text"], textarea');

            elementos.forEach(elemento => {
                elemento.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
                if (elemento.value) {
                    elemento.value = elemento.value.toUpperCase();
                }
            });
        }
        document.addEventListener('DOMContentLoaded', (event) => {
            convertirAMayusculas();
            (function () {
                'use strict'
                var forms = document.querySelectorAll('.needs-validation')
                Array.prototype.slice.call(forms)
                    .forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            if (!form.checkValidity()) {
                                event.preventDefault()
                                event.stopPropagation()
                            }
                            form.classList.add('was-validated')
                        }, false)
                    })
            })()
        });
        // FIN Función para convertir todo el texto a mayúsculas
    </script>
    <script>
        // Revisar audiencia (Fase 1): pestaña abierta al entrar, enlaces de la
        // lista a su pestaña y encabezado fijo al bajar.
        document.addEventListener('DOMContentLoaded', function () {
            function abrir(id) {
                var boton = document.querySelector('.tab a[onclick*="\'' + id + '\'"]');
                if (boton) { boton.click(); }
            }
            // Antes no se abría ninguna pestaña: la página se veía vacía.
            abrir('detalles');

            document.querySelectorAll('.ra-ir').forEach(function (enlace) {
                enlace.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = enlace.getAttribute('data-pestana');
                    abrir(id);
                    var destino = document.getElementById(id);
                    if (destino) { destino.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
                });
            });

            // El layout tiene overflow-x: hidden, así que position: sticky no
            // funciona: al pasar el encabezado se fija con JS.
            // .ra-hueco es una marca de 0 px justo arriba del encabezado que nunca
            // cambia de alto (antes se le daba el alto del encabezado y se
            // observaba a sí misma: al fijarse dejaba de estar "fuera", se
            // soltaba, volvía a quedar fuera... y la página brincaba y se trababa).
            // .ra-reserva, justo debajo, ocupa el lugar del encabezado fijo con
            // su alto y su margen, así el contenido no se mueve.
            var barra = document.getElementById('raBarra');
            var marca = document.querySelector('.ra-hueco');
            if (!barra || !marca) { return; }
            var reserva = document.createElement('div');
            reserva.className = 'ra-reserva';
            barra.parentNode.insertBefore(reserva, barra.nextSibling);
            var nav = document.querySelector('.main-navbar');
            var arriba = function () { return nav ? Math.max(0, nav.getBoundingClientRect().bottom) : 0; };
            var fija = false, pendiente = false;

            function acomodar() {
                var r = reserva.getBoundingClientRect();
                barra.style.top = arriba() + 'px';
                barra.style.left = r.left + 'px';
                barra.style.width = r.width + 'px';
            }

            function soltar() {
                fija = false;
                barra.classList.remove('is-fija');
                barra.style.top = barra.style.left = barra.style.width = '';
                reserva.style.height = '0px';
            }

            function revisar() {
                pendiente = false;
                var debe = marca.getBoundingClientRect().top < arriba();
                if (debe && !fija) {
                    var margen = parseFloat(getComputedStyle(barra).marginBottom) || 0;
                    reserva.style.height = (barra.offsetHeight + margen) + 'px';
                    fija = true;
                    barra.classList.add('is-fija');
                    acomodar();
                } else if (!debe && fija) {
                    soltar();
                } else if (fija) {
                    acomodar();
                }
            }

            function pedir() {
                if (!pendiente) { pendiente = true; window.requestAnimationFrame(revisar); }
            }

            // capture: también llega si el que se desplaza es un contenedor del layout.
            document.addEventListener('scroll', pedir, { passive: true, capture: true });
            // Al cambiar el ancho, el encabezado puede ocupar otro alto: se suelta y se vuelve a medir.
            window.addEventListener('resize', function () { if (fija) { soltar(); } pedir(); });
            revisar();
        });
    </script>
    <script>
        // Borrar citado: el botón Eliminar pide confirmación en su propio modal.
        // Se cierra la lista, se confirma y, si se cancela, la lista vuelve a abrirse.
        document.addEventListener('DOMContentLoaded', function () {
            var lista = document.getElementById('exampleModal2');
            var confirmar = document.getElementById('raConfirmarBorrar');
            if (!lista || !confirmar || typeof bootstrap === 'undefined') { return; }
            var modalConfirmar = bootstrap.Modal.getOrCreateInstance(confirmar);
            var boton = confirmar.querySelector('[data-ra-confirmar]');
            var formulario = null, regresarALista = false;

            document.querySelectorAll('.ra-borrar-citado').forEach(function (b) {
                b.addEventListener('click', function () {
                    formulario = b.closest('form');
                    confirmar.querySelector('[data-ra-nombre]').textContent = b.getAttribute('data-nombre') || 'este citado';
                    confirmar.querySelector('[data-ra-notificacion]').textContent = b.getAttribute('data-notificacion') || '—';
                    confirmar.querySelector('[data-ra-direccion]').textContent = b.getAttribute('data-direccion') || '—';
                    confirmar.querySelector('[data-ra-cambios]').hidden = !document.querySelector('.ra-seccion__cambios:not([hidden])');
                    regresarALista = true;
                    var abierta = bootstrap.Modal.getInstance(lista);
                    if (abierta && lista.classList.contains('show')) {
                        lista.addEventListener('hidden.bs.modal', function abrir() {
                            lista.removeEventListener('hidden.bs.modal', abrir);
                            modalConfirmar.show();
                        });
                        abierta.hide();
                    } else {
                        modalConfirmar.show();
                    }
                });
            });

            boton.addEventListener('click', function () {
                if (!formulario) { return; }
                regresarALista = false;
                boton.disabled = true;
                boton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Borrando…';
                window.raSaltarAvisoSalida = true;
                var carga = document.getElementById('menu_carga');
                if (carga) { carga.style.display = ''; }
                formulario.submit();
            });

            confirmar.addEventListener('hidden.bs.modal', function () {
                if (regresarALista) { bootstrap.Modal.getOrCreateInstance(lista).show(); }
                regresarALista = false;
            });
        });
    </script>
    <script>
        // Revisar audiencia (Fase 2): cada sección se ve en modo lectura y se
        // abre con "Editar". Los campos siguen en el formulario (readonly), así
        // que se guardan igual que antes; al enviar se abren todas un momento
        // para que la validación revise los campos obligatorios.
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('form.needs-validation');
            var secciones = Array.prototype.slice.call(document.querySelectorAll('.ra-seccion'));
            if (!form || !secciones.length) { return; }
            var puedeEditar = {{ $puedeEditar ? 'true' : 'false' }};
            var conErrores = {{ $errors->any() ? 'true' : 'false' }};
            var CAMPOS = 'input:not([type=hidden]):not([type=file]):not([type=checkbox]):not([type=radio]):not([type=button]):not([type=submit]), select, textarea';
            var cambios = false, enviando = false;

            function lectura(sec, si) {
                sec.classList.toggle('ra-lectura', si);
                sec.classList.toggle('is-editando', !si);
                sec.querySelectorAll(CAMPOS).forEach(function (el) {
                    if (si) {
                        if (!el.hasAttribute('data-ra-orig')) {
                            el.setAttribute('data-ra-orig', JSON.stringify({ ro: el.readOnly, ti: el.getAttribute('tabindex'), ph: el.getAttribute('placeholder') }));
                        }
                        if (el.tagName !== 'SELECT') { el.readOnly = true; }
                        el.setAttribute('tabindex', '-1');
                        if (el.tagName !== 'SELECT' && !el.value) { el.setAttribute('placeholder', '—'); }
                    } else if (el.hasAttribute('data-ra-orig')) {
                        var o = JSON.parse(el.getAttribute('data-ra-orig'));
                        if (el.tagName !== 'SELECT') { el.readOnly = o.ro; }
                        if (o.ti === null) { el.removeAttribute('tabindex'); } else { el.setAttribute('tabindex', o.ti); }
                        if (o.ph === null) { el.removeAttribute('placeholder'); } else { el.setAttribute('placeholder', o.ph); }
                    }
                });
                var txt = sec.querySelector('.ra-editar span');
                if (txt) { txt.textContent = si ? 'Editar datos' : 'Listo'; }
            }

            // Oculto dentro de la sección (no cuenta que la pestaña esté cerrada).
            function oculto(el, sec) {
                for (var n = el; n && n !== sec; n = n.parentElement) {
                    if (n.hidden || getComputedStyle(n).display === 'none') { return true; }
                }
                return false;
            }

            function faltaAlgo(sec) {
                return Array.prototype.some.call(sec.querySelectorAll(CAMPOS), function (el) {
                    return el.required && !el.disabled && !el.value && !oculto(el, sec);
                });
            }

            secciones.forEach(function (sec) {
                // Se abre sola si viene de un error o le falta un dato obligatorio.
                var falta = puedeEditar && faltaAlgo(sec);
                lectura(sec, !(puedeEditar && (conErrores || falta)));
                if (falta) {
                    var herr = sec.querySelector('.ra-seccion__herr');
                    if (herr) {
                        var aviso = document.createElement('span');
                        aviso.className = 'ra-seccion__falta';
                        aviso.textContent = 'Falta un dato obligatorio';
                        herr.insertBefore(aviso, herr.firstChild);
                    }
                }
                var boton = sec.querySelector('.ra-editar');
                if (boton) {
                    boton.addEventListener('click', function () {
                        var abrir = sec.classList.contains('ra-lectura');
                        lectura(sec, !abrir);
                        if (abrir) {
                            var primero = sec.querySelector(CAMPOS);
                            if (primero) { try { primero.focus({ preventScroll: true }); } catch (e) {} }
                        }
                    });
                }
                var marcar = function (e) {
                    if (!e.target.matches || !e.target.matches(CAMPOS + ', input[type=file]')) { return; }
                    cambios = true;
                    sec.querySelectorAll('.ra-seccion__cambios').forEach(function (m) { m.hidden = false; });
                };
                sec.addEventListener('input', marcar);
                sec.addEventListener('change', marcar);
            });

            // Clic directo en un dato (modo lectura): abre su sección y deja el
            // cursor en ese campo. Los campos en lectura no reciben el clic
            // (pointer-events: none), así que se toma el .form-group que lo envuelve.
            if (puedeEditar) {
                secciones.forEach(function (sec) {
                    if (sec.querySelector('.ra-editar')) { sec.classList.add('ra-editable'); }
                });
                form.addEventListener('click', function (e) {
                    var grupo = e.target.closest && e.target.closest('.form-group');
                    var sec = grupo && grupo.closest('.ra-seccion');
                    if (!sec || !sec.classList.contains('ra-lectura') || !sec.classList.contains('ra-editable')) { return; }
                    if (e.target.closest('a, button, .btn, [data-bs-toggle], input[type=file]')) { return; }
                    var campo = Array.prototype.find.call(grupo.querySelectorAll(CAMPOS), function (el) { return !oculto(el, grupo); });
                    lectura(sec, false);
                    if (campo) {
                        try { campo.focus({ preventScroll: true }); } catch (err) {}
                        if (campo.tagName === 'SELECT' && typeof campo.showPicker === 'function') {
                            try { campo.showPicker(); } catch (err) {}
                        }
                    }
                });
            }

            // Al enviar: se abren todas para que la validación vea los obligatorios;
            // si algo falta, se queda abierta sólo la sección con el problema.
            form.addEventListener('submit', function () {
                var antes = secciones.map(function (sec) { return sec.classList.contains('ra-lectura'); });
                secciones.forEach(function (sec) { lectura(sec, false); });
                enviando = true;
                setTimeout(function () {
                    if (form.checkValidity()) { return; }
                    enviando = false;
                    secciones.forEach(function (sec, i) {
                        if (antes[i] && !sec.querySelector(':invalid')) { lectura(sec, true); }
                    });
                }, 0);
            }, true);

            // Si un campo inválido está en una sección en lectura, se abre.
            form.addEventListener('invalid', function (e) {
                var sec = e.target.closest && e.target.closest('.ra-seccion');
                if (sec && sec.classList.contains('ra-lectura')) { lectura(sec, false); }
            }, true);

            // Recargar o cerrar la pestaña: el navegador sólo permite su aviso
            // propio (no se puede cambiar por uno de la página).
            window.addEventListener('beforeunload', function (e) {
                if (hayCambios() && !enviando && !window.raSaltarAvisoSalida) { e.preventDefault(); e.returnValue = ''; }
            });

            // "Subir de nuevo" (archivo que no existe): abre la sección y el selector de archivo.
            window.raSubirDeNuevo = function (input) {
                var sec = input.closest('.ra-seccion');
                if (sec && sec.classList.contains('ra-lectura')) { lectura(sec, false); }
                input.classList.add('ra-doc-subir');
                input.scrollIntoView({ block: 'center', behavior: 'smooth' });
                input.click();
            };

            // Salir por un enlace (Regresar, menú lateral, etc.) o con el botón
            // Atrás del navegador: confirmación con SweetAlert en vez del aviso
            // nativo. Cuenta como cambio lo que marcan las secciones y lo que
            // enciende "Guardar edición" (motivos, archivos).
            function hayCambios() { return cambios || !!window.raCambioDetectado; }

            function preguntarSalida() {
                if (typeof Swal === 'undefined') {
                    return Promise.resolve(window.confirm('Tienes cambios sin guardar. ¿Salir de todos modos?'));
                }
                return Swal.fire({
                    icon: 'warning',
                    title: '¿Salir sin guardar?',
                    html: 'Hiciste cambios que <b>todavía no se guardan</b>. Si sales ahora, se pierden.<br>Para conservarlos, quédate y da clic en <b>Guardar edición</b>.',
                    showCancelButton: true,
                    confirmButtonText: 'Seguir editando',
                    cancelButtonText: 'Salir sin guardar',
                    reverseButtons: true,
                    focusConfirm: true,
                    confirmButtonColor: '#496163',
                    cancelButtonColor: '#9A3A22',
                }).then(function (r) { return r.dismiss === Swal.DismissReason.cancel; });
            }

            function salir(destino) {
                window.raSaltarAvisoSalida = true;
                destino();
            }

            document.addEventListener('click', function (e) {
                var a = e.target.closest && e.target.closest('a[href]');
                if (!a || !hayCambios() || enviando || window.raSaltarAvisoSalida) { return; }
                var href = a.getAttribute('href') || '';
                if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) { return; }
                if (a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-bs-toggle')
                    || href === '' || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) { return; }
                e.preventDefault();
                preguntarSalida().then(function (si) {
                    if (si) { salir(function () { window.location.href = a.href; }); }
                });
            }, true);

            // Botón Atrás: se agrega una entrada al historial en el primer
            // cambio; al regresar se pregunta y, si se queda, se vuelve a poner.
            var trampa = false;
            function ponerTrampa() {
                if (trampa || !window.history || !history.pushState) { return; }
                trampa = true;
                history.pushState({ raCambios: true }, '');
            }
            form.addEventListener('input', function (e) { if (e.isTrusted) { ponerTrampa(); } }, true);
            form.addEventListener('change', function (e) { if (e.isTrusted) { ponerTrampa(); } }, true);
            window.addEventListener('popstate', function () {
                if (!trampa) { return; }
                if (!hayCambios() || enviando || window.raSaltarAvisoSalida) { trampa = false; history.back(); return; }
                preguntarSalida().then(function (si) {
                    if (si) {
                        salir(function () { trampa = false; history.back(); });
                    } else {
                        history.pushState({ raCambios: true }, '');
                    }
                });
            });
        });
    </script>
    <script>
        // "Guardar edición" apagado (gris) hasta que la persona cambie algo:
        // escribir, elegir una opción, subir un archivo, agregar o quitar un
        // motivo. Sólo cuentan acciones de la persona (isTrusted): los
        // municipios y demás campos que se llenan solos al cargar no lo
        // activan. Si la página regresó con errores, ya trae cambios: activo.
        // "Guardar e iniciar audiencia" no se toca: se puede iniciar sin editar.
        document.addEventListener('DOMContentLoaded', function () {
            var boton = document.querySelector('[data-ra-guardar]');
            var form = boton && boton.form;
            if (!form || {{ $errors->any() ? 'true' : 'false' }}) { return; }
            var aviso = document.querySelector('[data-ra-guardar-aviso]');

            function esperar(si) {
                boton.disabled = si;
                boton.classList.toggle('ra-guardar--espera', si);
                boton.title = si ? 'Aún no has cambiado nada. Da clic en Editar en la sección que quieras corregir.' : '';
                if (aviso) { aviso.hidden = !si; }
            }
            function activar() { window.raCambioDetectado = true; if (boton.disabled) { esperar(false); } }

            function cambio(e) {
                var t = e.target;
                if (!e.isTrusted || !t.matches || !t.matches('input, select, textarea') || t.type === 'hidden') { return; }
                activar();
            }
            form.addEventListener('input', cambio, true);
            form.addEventListener('change', cambio, true);
            // Quitar un motivo (botón Eliminar de la tabla de motivos).
            form.addEventListener('click', function (e) {
                if (e.isTrusted && e.target.closest && e.target.closest('.eliminar')) { activar(); }
            }, true);

            // Enter en un campo sin cambios no debe mandar el siguiente botón
            // activo ("Guardar e iniciar audiencia") en lugar de Guardar edición.
            form.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && boton.disabled && e.target.matches && e.target.matches('input')) { e.preventDefault(); }
            }, true);

            // Para otros scripts que cambien datos del formulario por su cuenta.
            window.raMarcarCambio = activar;
            esperar(true);
        });
    </script>

    {{-- "Describe brevemente el motivo de tu solicitud" crece hasta verse completo. --}}
    @include('partials.general._textarea_autoalto')
@endsection

