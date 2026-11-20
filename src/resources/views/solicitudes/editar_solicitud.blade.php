@extends('layouts.app')
@section('title', 'Revisar Solicitud')

@section('page_css')
<style>
    /* Estilo tomado de audiencias/revisar_audiencia (pestañas, secciones, tarjetas y botones). */
    select[name="vialidad"] option,
    select[name="estado_citado"] option,
    select[name="municipio_citado"] option { text-transform: uppercase; }

    .was-validated .form-control:invalid ~ .invalid-feedback,
    .was-validated select.form-control:invalid ~ .invalid-feedback,
    .was-validated .form-select:invalid ~ .invalid-feedback {
        display: block;
    }

    /* Pestañas: blancas con borde; la abierta en verde obscuro con línea dorada. */
    .tab { display: flex; flex-wrap: wrap; gap: 6px; border: 0; background: transparent; overflow: visible; }
    html body .tab a.btn.ra-tab { display: inline-flex; align-items: center; gap: 6px; border-radius: 10px 10px 0 0; border: 1px solid #C3CFCF !important; border-bottom: 0 !important; background: #fff !important; color: #354647 !important; font-weight: 700; padding: 9px 18px; box-shadow: inset 0 3px 0 transparent; transition: background-color .15s, color .15s, box-shadow .15s; }
    html body .tab a.btn.ra-tab i { color: #829A9C; transition: color .15s; }
    html body .tab a.btn.ra-tab:not(.active):hover { background: #EEF3F3 !important; border-color: #A9BBBB !important; color: #1D2B2C !important; box-shadow: inset 0 3px 0 #829A9C; }
    html body .tab a.btn.ra-tab:not(.active):hover i { color: #496163; }
    html body .tab a.btn.ra-tab.active,
    html body .tab a.btn.ra-tab.active:hover,
    html body .tab a.btn.ra-tab.active:focus { background: #354647 !important; border-color: #354647 !important; color: #fff !important; box-shadow: inset 0 3px 0 #CEA845; cursor: default; }
    html body .tab a.btn.ra-tab.active i { color: #CEA845; }
    .tabcontent { display: none; padding: 12px 14px; border: 1px solid #D5DDDD; border-radius: 0 12px 12px 12px; }

    /* Encabezado fijo con las acciones (Confirmar, Prevención, Guardar, Regresar). */
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
    .ra-barra.is-fija .ra-barra__datos { display: none; }
    .ra-chip { display: inline-block; padding: 2px 10px; border-radius: 999px; background: #EEF2F2; color: #496163; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .ra-chip--audiencia { background: #E3EEF9; color: #1F4E79; }
    .ra-chip--prevencion { background: #FBEDEA; color: #9A3A22; }
    .ra-acciones { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; }
    .ra-acciones .btn { border-radius: 10px; font-weight: 700; }
    /* Avisos que los scripts agregan dentro de las acciones (motivos, teléfonos, imagen 1). */
    .ra-acciones > div, .ra-nota-acciones { flex-basis: 100%; text-align: right; font-size: 12px; color: #8A9899; margin-top: 0 !important; }
    @media (max-width: 768px) {
        .ra-barra.is-fija .ra-barra__titulo .ra-chip { display: none; }
        .ra-acciones .btn { padding: 6px 10px; font-size: 13px; }
    }

    /* Modo lectura: los datos se ven como texto hasta dar clic en "Editar datos" o en el dato. */
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
    html body .ra-seccion input.ra-fijo,
    html body .ra-seccion input.ra-fijo:focus { border-color: transparent !important; background: transparent !important; box-shadow: none !important;
        padding-left: 0 !important; color: #2E3C3D !important; font-weight: 600; pointer-events: none; cursor: default; }
    .ra-fijo__candado { font-size: 11px; color: #A2AFAF; margin-left: 2px; }
    .ra-lectura.ra-editable .form-group { position: relative; cursor: pointer; border-radius: 8px; transition: background-color .12s, box-shadow .12s; }
    .ra-lectura.ra-editable .form-group:hover { background-color: #F2F6F6; box-shadow: 0 0 0 6px #F2F6F6; }
    .ra-lectura.ra-editable .form-group:hover::after {
        content: "\F4CB"; font-family: bootstrap-icons; position: absolute; top: 2px; right: 4px;
        font-size: 12px; color: #496163; pointer-events: none;
    }
    .ra-lectura.ra-editable .form-group:hover label { color: #496163; }
    .ra-lectura.ra-editable .form-group:has(.ra-fijo) { cursor: default; }
    .ra-lectura.ra-editable .form-group:has(.ra-fijo):hover { background-color: transparent; box-shadow: none; }
    .ra-lectura.ra-editable .form-group:has(.ra-fijo):hover::after { content: none; }
    /* Datos deshabilitados (representante): no se abren con clic. */
    .ra-lectura.ra-editable .form-group:has(:disabled):not(:has(:enabled:not([type=hidden]))) { cursor: default; }
    .ra-lectura.ra-editable .form-group:has(:disabled):not(:has(:enabled:not([type=hidden]))):hover { background-color: transparent; box-shadow: none; }
    .ra-lectura.ra-editable .form-group:has(:disabled):not(:has(:enabled:not([type=hidden]))):hover::after { content: none; }

    /* Editar: azul petróleo. Suave en reposo; sólido al pasar por la sección y mientras se edita ("Listo"). */
    html body .btn.ra-editar { background-color: #E3EFF3 !important; border: 1px solid #A9CBD7 !important; color: #1D5F73 !important; font-weight: 700; box-shadow: none; }
    html body .btn.ra-editar:not(.btn-social):hover, html body .btn.ra-editar:not(.btn-social):focus,
    html body .ra-seccion.ra-lectura.ra-editable:hover > .ra-seccion__cab .btn.ra-editar,
    html body .ra-seccion.ra-lectura.ra-editable:hover > .ra-parte__cab .btn.ra-editar { background-color: #1D5F73 !important; border-color: #1D5F73 !important; color: #fff !important; }
    html body .ra-seccion.is-editando > .ra-seccion__cab .btn.ra-editar,
    html body .ra-seccion.is-editando > .ra-parte__cab .btn.ra-editar { background-color: #1D5F73 !important; border-color: #1D5F73 !important; color: #fff !important; box-shadow: 0 2px 6px rgba(29, 95, 115, .3); }
    html body .ra-seccion.is-editando > .ra-seccion__cab .btn.ra-editar:not(.btn-social):hover,
    html body .ra-seccion.is-editando > .ra-parte__cab .btn.ra-editar:not(.btn-social):hover { background-color: #164B5B !important; border-color: #164B5B !important; }
    .tabcontent.ra-seccion.is-editando { border-color: #A9CBD7; }

    /* Encabezado de cada sección y subtítulos. */
    .ra-seccion__cab { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px 12px; padding: 4px 0 10px; margin-bottom: 10px; border-bottom: 1px solid #EEF2F2; }
    .ra-seccion__titulo { margin: 0; font-size: 16px; font-weight: 800; color: #2E3C3D; }
    .ra-seccion__nota { display: block; font-size: 12px; color: #8A9899; }
    .ra-seccion__herr { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .ra-seccion h4.text-center { text-align: left !important; font-size: 12.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #829A9C; margin: 12px 0 4px; }
    .ra-seccion h5.text-center { text-align: left !important; font-size: 13px; font-weight: 800; color: #496163; margin: 8px 0 4px; }

    /* Tarjeta por citado. */
    .ra-parte { border: 1px solid #E3E8E8; border-radius: 12px; padding: 12px 16px; margin: 6px 0 16px; background: #fff; }
    .ra-parte__cab { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; padding-bottom: 10px; margin-bottom: 6px; border-bottom: 1px solid #EEF2F2; }
    .ra-parte__quien { display: flex; gap: 10px; align-items: flex-start; min-width: 0; }
    .ra-parte__num { width: 28px; height: 28px; border-radius: 50%; background: #354647; color: #fff; font-weight: 800; display: grid; place-items: center; flex: 0 0 auto; }
    .ra-parte__nombre { font-size: 16px; font-weight: 800; color: #2E3C3D; }
    .ra-parte__chips { display: flex; flex-wrap: wrap; gap: 4px 6px; margin-top: 4px; }
    .ra-et { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: #EEF2F2; color: #496163; }
    .ra-et--info { background: #E3EEF9; color: #1F4E79; }
    .ra-et--resulte { background: #F1EFF8; color: #4B3F7A; }

    /* Botones. */
    html body .btn { transition: background-color .15s, border-color .15s, color .15s, box-shadow .15s; }

    /* Guardar / Confirmar: dorado. */
    html body .btn.btn-primary { background-color: #CEA845 !important; border: 1px solid #CEA845 !important; color: #fff !important; font-weight: 700; box-shadow: 0 2px 6px rgba(206, 168, 69, .35); }
    html body .btn.btn-primary:not(.btn-social):hover, html body .btn.btn-primary:not(.btn-social):focus { background-color: #B8952F !important; border-color: #B8952F !important; color: #fff !important; }
    html body .btn.btn-primary:not(.btn-social):active { background-color: #A6852A !important; border-color: #A6852A !important; color: #fff !important; }

    /* Agregar: verde suave. */
    html body .btn.ra-btn-agregar { background-color: #E5F3EA !important; border: 1px solid #A8D5B6 !important; color: #1E7B3C !important; font-weight: 700; box-shadow: none; }
    html body .btn.ra-btn-agregar:not(.btn-social):hover, html body .btn.ra-btn-agregar:not(.btn-social):focus { background-color: #1E7B3C !important; border-color: #1E7B3C !important; color: #fff !important; }

    /* Ver documento: contorno verde institucional, se rellena al pasar. */
    html body .btn.btn-info,
    html body .btn.btn-success,
    html body .btn.btn-outline-info,
    html body .btn.btn-outline-secondary { background-color: #fff !important; border: 1px solid #829A9C !important; color: #496163 !important; font-weight: 600; box-shadow: none; }
    html body .btn.btn-info:not(.btn-social):hover, html body .btn.btn-info:not(.btn-social):focus,
    html body .btn.btn-success:not(.btn-social):hover, html body .btn.btn-success:not(.btn-social):focus,
    html body .btn.btn-outline-info:not(.btn-social):hover, html body .btn.btn-outline-info:not(.btn-social):focus,
    html body .btn.btn-outline-secondary:not(.btn-social):hover, html body .btn.btn-outline-secondary:not(.btn-social):focus { background-color: #496163 !important; border-color: #496163 !important; color: #fff !important; }

    /* Regresar / Cerrar: gris neutro. */
    html body .btn.btn-secondary { background-color: #E3EAEA !important; border: 1px solid #E3EAEA !important; color: #496163 !important; font-weight: 600; box-shadow: none; }
    html body .btn.btn-secondary:not(.btn-social):not(:disabled):not(.disabled):hover { background-color: #D2DCDC !important; border-color: #D2DCDC !important; color: #354647 !important; }

    /* Borrar / Prevención: rojo apagado. */
    html body .btn.btn-danger,
    html body .btn.btn-outline-danger { background-color: #fff !important; border: 1px solid #C9A49A !important; color: #9A3A22 !important; box-shadow: none; }
    html body .btn.btn-danger:not(.btn-social):hover, html body .btn.btn-danger:not(.btn-social):focus,
    html body .btn.btn-outline-danger:not(.btn-social):hover, html body .btn.btn-outline-danger:not(.btn-social):focus { background-color: #9A3A22 !important; border-color: #9A3A22 !important; color: #fff !important; }

    /* Sin archivo / deshabilitado: apagado. */
    html body .btn.disabled,
    html body .btn:disabled { background-color: #F4F6F6 !important; border: 1px dashed #D5DDDD !important; color: #8A9899 !important; box-shadow: none; opacity: 1; }
</style>
@endsection

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Revisar Solicitud</h3>
        </div>
        <div class="section-body">
            <?php $fecha_actual = date('d-m-Y');?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="text-center">Detalles de la Solicitud</h3>
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

                                <form id="formSolicitud" class="needs-validation" novalidate method="POST" action="{{route('confirmar_solicitud')}}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$id}}">
                                    <input type="hidden" name="consecutivo" value="{{ $general['consecutivo'] ?? '' }}">

                                    @php
                                        $primerSolicitante = $solicitantes->first();
                                        $nombreSolicitante = $primerSolicitante->nombre ?? '';
                                        $citadosCount = isset($citados)
                                            ? (is_countable($citados) ? count($citados) : (method_exists($citados, 'count') ? $citados->count() : 0))
                                            : 0;
                                        $estatusEditable = $general['estatus'] == 'Prevencion' || $general['estatus'] == 'Pendiente';
                                        if ($general->tipo_generacion == 0) {
                                            $origenSolicitud = 'Solicitud en línea';
                                        } elseif ($general->tipo_generacion == 1000) {
                                            $origenSolicitud = 'Solicitud en tablet';
                                        } else {
                                            $origenSolicitud = 'Personal Centro';
                                        }
                                    @endphp
                                    {{-- Encabezado con las acciones siempre a la vista (antes, pestaña "Acciones"). Se queda fijo arriba al bajar. --}}
                                    <div class="ra-hueco"></div>
                                    <div class="ra-barra" id="raBarra">
                                        <div class="ra-barra__caso">
                                            <div class="ra-barra__titulo">
                                                <b>{{ $general->NUE ?: ($nombreSolicitante !== '' ? $nombreSolicitante : 'Solicitud') }}</b>
                                                <span class="ra-chip">{{ $general->tipo_solicitud == 2 ? 'Solicita el patrón' : 'Solicita el trabajador' }}</span>
                                                <span class="ra-chip {{ $general['estatus'] == 'Prevencion' ? 'ra-chip--prevencion' : 'ra-chip--audiencia' }}">{{ $general['estatus'] }}</span>
                                            </div>
                                            <div class="ra-barra__datos">
                                                @if (filled($general['fecha'] ?? null))
                                                    <span><i class="bi bi-calendar-event"></i> Capturada el {{ \Illuminate\Support\Carbon::parse($general['fecha'])->format('d/m/Y') }}</span>
                                                @endif
                                                @if ($general->NUE && $nombreSolicitante !== '')
                                                    <span><i class="bi bi-person"></i> {{ $nombreSolicitante }}</span>
                                                @endif
                                                <span><i class="bi bi-people"></i> {{ $citadosCount }} {{ $citadosCount == 1 ? 'citado' : 'citados' }}</span>
                                                <span><i class="bi bi-display"></i> {{ $origenSolicitud }}</span>
                                            </div>
                                        </div>
                                        {{-- id="tabla_confirmar": lo usan los scripts de motivos, teléfonos e imagen de referencia. --}}
                                        <div class="ra-acciones" id="tabla_confirmar">
                                            @can('solicitudes_validar')
                                                @if($estatusEditable)
                                                    @if($citadosCount > 0)
                                                        <button id="btnConfirmarSolicitud" type="submit" class="btn btn-primary" name="toquen" value="1"><i class="bi bi-check2-circle"></i> Confirmar solicitud</button>
                                                    @else
                                                        <button type="button" class="btn btn-secondary" disabled title="Agregue al menos un citado para poder guardar."  name="toquen" value="1">Confirmar solicitud</button>
                                                    @endif
                                                    <button type="button" class="btn btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#exampleModal" data-id="{{ $general->id }}"><i class="bi bi-exclamation-triangle"></i> Prevención</button>
                                                @endif
                                            @endcan
                                            @if(!$estatusEditable)
                                                <button type="submit" class="btn btn-primary" name="toquen" value="2"><i class="bi bi-save"></i> Guardar</button>
                                            @endif
                                            <a class="btn btn-secondary" id="raRegresar" href="{{ route('cancelar_edicion', ['redirect_to' => 'solicitudes_pendientes']) }}"><i class="bi bi-arrow-left"></i> Regresar</a>
                                            @can('solicitudes_validar')
                                                @if($estatusEditable)
                                                    @if($citadosCount > 0)
                                                        <div id="msgRefImg1" class="ra-nota-acciones" style="display:none;">Debe subir la <strong>Referencia Imagen 1</strong> de todos los citados para poder confirmar.</div>
                                                    @else
                                                        <div class="ra-nota-acciones">Debe agregar al menos un citado para poder guardar.</div>
                                                    @endif
                                                @endif
                                            @endcan
                                        </div>
                                    </div>

                                    <div class="tab">
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'detalles')"><i class="bi bi-card-text" aria-hidden="true"></i> Detalles</a>
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'solicitante')"><i class="bi bi-person" aria-hidden="true"></i> {{ $general->tipo_solicitud == 2 ? 'Solicitante (patrón)' : 'Solicitante' }}</a>
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'documentos')"><i class="bi bi-people" aria-hidden="true"></i> Citado(s)</a>
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'observaciones')"><i class="bi bi-chat-left-text" aria-hidden="true"></i> Observaciones</a>
                                        <a class="btn ra-tab tablinks" onclick="openCity(event, 'citados')"><i class="bi bi-folder2-open" aria-hidden="true"></i> Documentos</a>
                                    </div>

                                    <div id="detalles" class="tabcontent ra-seccion">
                                        @include('audiencias._revisar_seccion_cab', ['titulo' => 'Datos de la solicitud', 'puedeEditar' => true])
                                        <div id="tabla_detalles" class="row">
                                            <div class="col-xs-12 col-sm-6 col-md-3">
                                                <div class="form-group">
                                                    <label>Fecha de registro <i class="bi bi-lock ra-fijo__candado" title="No se puede editar"></i></label>
                                                    <input type="text" class="form-control ra-fijo" value="{{ filled($general['fecha'] ?? null) ? \Illuminate\Support\Carbon::parse($general['fecha'])->format('d/m/Y') : '—' }}" readonly tabindex="-1" aria-readonly="true">
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-9">
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
                                                                                         <a href="{{ route('eliminar_motivo_solicitud', ['id' => $id, 'id_motivo' => $motivo->id] ) }}" class="eliminar btn btn-danger btn-sm">Eliminar</a>
                                                                                     </td>   
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                       
                                            <div class="col-xs-6 col-sm-6 col-md-6 ra-solo-edicion">
                                                <div class="form-group">
                                                    <label for="name">Agregar otro motivo a la solicitud</label>
                                                    <select  class="form-control" id="motivo_solicitud">
                                                        <option value="">Seleccione</option>
                                                        @foreach($mostrarMotivos as $motivo)
                                                            <option value="{{$motivo['id']}}">{{$motivo['motivo']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        El objeto de solicitud es obligatoria.
                                                    </div>
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
                                        </div>
                                    </div>
                                    <div id="solicitante" class="tabcontent ra-seccion">
                                        @include('audiencias._revisar_seccion_cab', ['titulo' => 'Solicitante', 'puedeEditar' => true])
                                        <div id="tabla_solicitante" class="row">
                                            @foreach($solicitantes as $solicitante)
                                                <div class="col-xs-12 col-sm-6 col-md-12">
                                                    <div class="form-group">
                                                        <label for="password">Nombre<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="nombre_solicitante" value="<?=$solicitante["nombre"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo nombre es obligatorio.
                                                        </div>   
                                                    </div>                                                    
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" hidden>
                                                    <div class="form-group">
                                                        <label for="confirm-password">Tipo de persona<span style="color:red;"> (*)</span></label>
                                                        <select name="tipo_persona_solicitante" class="form-control" required>
                                                            <option value="Fisica" {{ $solicitante["tipo_persona"] == 'Fisica' ? "selected" : '' }}>Física</option>
                                                            <option value="Moral"  {{ $solicitante['tipo_persona'] == 'Moral' ? "selected" : '' }}>Moral</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo tipo de persona es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <label for="confirm-password">CURP<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="curp_solicitante" value="<?=$solicitante["curp"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo curp es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">RFC</label>
                                                        <input type="text" class="form-control" name="rfc_solicitante" value="<?=$solicitante["rfc"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="confirm-password">Sexo<span style="color:red;"> (*)</span></label>
                                                        <select name="sexo_solicitante" class="form-control" required>
                                                            <option value="H" {{ $solicitante["sexo"] == 'H' ? "selected" : '' }}>Hombre</option>
                                                            <option value="M"  {{ $solicitante['sexo'] == 'M' ? "selected" : '' }}>Mujer</option>
                                                            <option value="NB"  {{ $solicitante['sexo'] == 'NB' ? "selected" : '' }}>No Binario</option>
                                                            <option value="LGBTTTIQ"  {{ $solicitante['sexo'] == 'LGBTTTIQ' ? "selected" : '' }}>LGBTTTIQ+</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo curp es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="confirm-password">Nacionalidad<span style="color:red;"> (*)</span></label>
                                                        <select name="nacionalidad_solicitante" class="form-control" required>
                                                            <option value="Mexicana" {{ $solicitante["sexo"] == 'Mexicana' ? "selected" : '' }}>Mexicana</option>
                                                            <option value="Otra"  {{ $solicitante['sexo'] == 'Otra' ? "selected" : '' }}>Otra</option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo nacionalidad es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Email<span style="color:red;"> (*)</span></label>
                                                        <input type="email" class="form-control" name="email_solicitante" value="<?=$solicitante["email"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo email es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Fecha nacimiento<span style="color:red;"> (*)</span></label>
                                                        <input type="date" class="form-control" name="fecha_nacimiento_solicitante" value="<?=$solicitante["fecha_nacimiento"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El fecha nacimiento es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Edad<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="edad_solicitante" value="<?=$solicitante["edad"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo edad es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Teléfono<span style="color:red;"> (*)</span></label>
                                                        <input type="tel" id="telefono1_solicitante" class="form-control" name="telefono1_solicitante" value="<?=$solicitante["telefono1"];?>" required inputmode="numeric" maxlength="14">
                                                        <div class="invalid-feedback">
                                                            El teléfono es obligatorio y debe contener 10 dígitos.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Teléfono (Opcional)</label>
                                                        <input type="tel" id="telefono2_solicitante" class="form-control" name="telefono2_solicitante" value="<?=$solicitante["telefono2"];?>" inputmode="numeric" maxlength="14">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="confirm-password">Requiere traductor<span style="color:red;"> (*)</span></label>
                                                        <select name="traductor_solicitante" id="needsLanguageSolicitante" class="form-control" required>
                                                            <option value="Si" {{ $solicitante["traductor"] == 'Si' ? "selected" : '' }}>SI</option>
                                                            <option value="No"  {{ $solicitante['traductor'] == 'No' ? "selected" : '' }}>NO</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" id="languageRequired" hidden>
                                                    <div class="form-group">
                                                        <label for="password">Lenguaje requerido<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="lenguaje_solicitante" id="languageValueSolicitante" value="<?=$solicitante["lenguaje"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="confirm-password">Tiene discapacidad<span style="color:red;"> (*)</span></label>
                                                        <select name="discapacidad_solicitante" id="hasDisabilitySolicitante" class="form-control" required>
                                                            <option value="Si" {{ $solicitante["discapacidad"] == 'Si' ? "selected" : '' }}>SI</option>
                                                            <option value="No"  {{ $solicitante['discapacidad'] == 'No' ? "selected" : '' }}>NO</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" id="disabilityRequired" hidden>
                                                    <div class="form-group">
                                                        <label for="password">Discapacidad<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="disc_solicitante" id="disabilityValueSolicitante" value="<?=$solicitante["tipo_discapacidad"];?>">   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <h4 class="text-center">Dirección del solicitante</h4>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Entidad Federativa<span style="color:red;"> (*)</span></label>
                                                        <select id="estado_solicitante" class="form-control" name="estado_solicitante" required>
                                                            @foreach($estados as $est)
                                                                <option value="{{$est['id']}}" {{ $solicitante['estado'] == $est['id'] ? "selected" : '' }}>{{$est['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo entidad federativa es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Municipio<span style="color:red;"> (*)</span></label>
                                                        <select class="form-control" name="municipio_solicitante" required>
                                                            @foreach($municipios as $mun)
                                                                <option value="{{$mun['id']}}" {{ $solicitante['municipio_domicilio'] == $mun['id'] ? "selected" : '' }}>{{$mun['nombre']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo municipio es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Tipo de vialidad<span style="color:red;"> (*)</span></label>
                                                        <!--input type="text" class="form-control" name="tipo_vialidad" value="<?=$solicitante["tipo_vialidad"];?>" required-->
                                                        <select name="tipo_vialidad" class="form-control" required>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="CALLE"          {{ $solicitante['tipo_vialidad'] == 'CALLE' ? "selected" : '' }}   >Calle</option>
                                                            <option value="AVENIDA"        {{ $solicitante['tipo_vialidad'] == 'AVENIDA' ? "selected" : '' }} >Avenida</option>
                                                            <option value="CALZADA"        {{ $solicitante['tipo_vialidad'] == 'CALZADA' ? "selected" : '' }} >Calzada</option>
                                                            <option value="BOULEVARD"      {{ $solicitante['tipo_vialidad'] == 'BOULEVARD' ? "selected" : '' }} >Boulevard</option>
                                                            <option value="AMPLIACIÓN"     {{ $solicitante['tipo_vialidad'] == 'AMPLIACIÓN' ? "selected" : '' }} >Ampliación</option>
                                                            <option value="ANDADOR"        {{ $solicitante['tipo_vialidad'] == 'ANDADOR' ? "selected" : '' }} >Andador</option>
                                                            <option value="AUTOPISTA"      {{ $solicitante['tipo_vialidad'] == 'AUTOPISTA' ? "selected" : '' }} >Autopista</option>
                                                            <option value="CALLEJÓN"       {{ $solicitante['tipo_vialidad'] == 'CALLEJÓN' ? "selected" : '' }}>Callejón</option>
                                                            <option value="CARRETERA"      {{ $solicitante['tipo_vialidad'] == 'CARRETERA' ? "selected" : '' }}   >Carretera</option>
                                                            <option value="CERRADA"        {{ $solicitante['tipo_vialidad'] == 'CERRADA' ? "selected" : '' }} >Cerrada</option>
                                                            <option value="CIRCUITO"       {{ $solicitante['tipo_vialidad'] == 'CIRCUITO' ? "selected" : '' }} >Circuito</option>
                                                            <option value="CIRCUNVALACIÓN" {{ $solicitante['tipo_vialidad'] == 'CIRCUNVALACIÓN' ? "selected" : '' }} >Circunvalación</option>
                                                            <option value="CONTINUACIÓN"   {{ $solicitante['tipo_vialidad'] == 'CONTINUACIÓN' ? "selected" : '' }} >Continuación</option>
                                                            <option value="CORREDOR"       {{ $solicitante['tipo_vialidad'] == 'CORREDOR' ? "selected" : '' }} >Corredor</option>
                                                            <option value="DIAGONAL"       {{ $solicitante['tipo_vialidad'] == 'DIAGONAL' ? "selected" : '' }} >Diagonal</option>
                                                            <option value="EJE VIAL"       {{ $solicitante['tipo_vialidad'] == 'EJE VIAL' ? "selected" : '' }}>Eje vial</option>
                                                            <option value="PERIFÉRICO"     {{ $solicitante['tipo_vialidad'] == 'PERIFÉRICO' ? "selected" : '' }}   >Periférico</option>
                                                            <option value="PRIVADA"        {{ $solicitante['tipo_vialidad'] == 'PRIVADA' ? "selected" : '' }} >Privada</option>
                                                            <option value="PROLONGACIÓN"   {{ $solicitante['tipo_vialidad'] == 'PROLONGACIÓN' ? "selected" : '' }} >Prolongación</option>
                                                            <option value="RETORNO"        {{ $solicitante['tipo_vialidad'] == 'RETORNO' ? "selected" : '' }} >Retorno</option>
                                                            <option value="VIADUCTO"       {{ $solicitante['tipo_vialidad'] == 'VIADUCTO' ? "selected" : '' }} >Viaducto</option>
                                                            <option value="PASEO"       {{ $solicitante['tipo_vialidad'] == 'PASEO' ? "selected" : '' }} >Paseo</option>                                                           
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            El campo tipo de vialidad es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Nombre de la vialidad<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="calle_solicitante" value="<?=$solicitante["calle"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo nombre de la vialidad es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Núm. Ext.<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="num_ext_solicitante" value="<?=$solicitante["num_ext"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo Núm. Ext. es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Núm. Int.</label>
                                                        <input type="text" class="form-control" name="num_int_solicitante" value="<?=$solicitante["num_int"];?>">   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Colonia<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="colonia_solicitante" value="<?=$solicitante["colonia"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo colonia es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                    <div class="form-group">
                                                        <label for="password">Código postal<span style="color:red;"> (*)</span></label>
                                                        <input type="text" class="form-control" name="codigo_postal_solicitante" value="<?=$solicitante["codigo_postal"];?>" required>
                                                        <div class="invalid-feedback">
                                                            El campo código postal es obligatorio.
                                                        </div>   
                                                    </div>
                                                </div>

                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Referencia</label>
                                                        <input type="text" class="form-control" name="referencia_solicitante" value="<?=$solicitante["referencia"];?>">   
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Entre calle</label>
                                                        <input type="text" class="form-control" name="calle2_solicitante" value="<?=$solicitante["calle2"];?>">   
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <label for="password">Y calle</label>
                                                        <input type="text" class="form-control" name="calle3_solicitante" value="<?=$solicitante["calle3"];?>">   
                                                    </div>
                                                </div>

                                                @if($general->tipo_solicitud == 2)
                                                    @if(optional($solicitante->poder)->reprecentante == 'Si')
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h4 class="text-center">Datos del representante</h4>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                            <h5 class="text-center" style="color:#CEA845">Información del Representante Legal</h5>
                                                        </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h5 class="text-center">Datos de identificación</h5>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="name">Nombre(s) del representante<span style="color:red;">(*)</span></label>
                                                                <input type="text" name="nombre_representante_pF" value="{{ $solicitante->poder->nombre_representante }}" class="form-control" oninput="this.value = this.value.toUpperCase()" disabled> 
                                                                <div class="invalid-feedback">
                                                                    El nombre es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                                                <input type="text" name="primer_representante_pF" value="{{ $solicitante->poder->primer_apellido_representante }}" class="form-control" oninput="this.value = this.value.toUpperCase()" disabled> 
                                                                <div class="invalid-feedback">
                                                                    El primer apellido es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="name">Segundo apellido <span style="color:red;">(*)</span></label>
                                                                <input type="text" name="segundo_representante_pF" value="{{ $solicitante->poder->segundo_apellido_representante }}" class="form-control" oninput="this.value = this.value.toUpperCase()" disabled> 
                                                                <div class="invalid-feedback">
                                                                    El segundo apellido es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>  
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="">CURP</label>
                                                                <input type="text" class="form-control" name="curp_representante_pF" value="{{$solicitante->poder->curp_representante }}" minlength="18" maxlength="18" oninput="this.value = this.value.toUpperCase()" disabled>
                                                                <div class="invalid-feedback">
                                                                    La CURP es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="name">Sexo <span style="color:red;">(*)</span></label>
                                                                <select name="sexo_representante_pF" id="sexo_representante_pF" class="form-control" disabled>
                                                                    <option value="">Seleccione</option>
                                                                    <option value="Femenino"    {{ $solicitante->poder["sexo_representante"] == "Femenino" ? "selected" : '' }}>Femenino</option>
                                                                    <option value="Masculino"   {{ $solicitante->poder["sexo_representante"] == "Masculino" ? "selected" : '' }}>Masculino</option>
                                                                    <option value="Prefiero no responder">Prefiero no responder</option>
                                                                </select>
                                                                <div class="invalid-feedback">
                                                                    El tipo de persona es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                                <h5 class="text-center">Datos de contacto</h5>
                                                            </div>
                                                        </div> 

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="">Correo electrónico <span style="color:red;">(*)</span></label>
                                                                <input type="email" class="form-control" name="correo_representante_pF" value="{{$solicitante->poder->correo_representante }}" disabled>
                                                                <div class="invalid-feedback">
                                                                    El Correo electrónico es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label for="">Teléfono <span style="color:red;">(*)</span></label>
                                                                <input type="text" class="form-control"  name="telefono_representante_pF" value="{{$solicitante->poder->numero_representante }}" maxlength="10" pattern="[0-9]+" disabled>
                                                                <div class="invalid-feedback">
                                                                    El telefono es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                                <div class="form-group">
                                                                    <label for="name">Identificación Oficial<span style="color:red;">(*)</span></label>
                                                                    <select  name="tipo_identificacion_pFCR" class="form-control" disabled>
                                                                        <option value="">Seleccione el tipo de indentificación</option>
                                                                        <option value="Credencial de elector" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de elector" ? "selected" : '' }} >Credencial de Elector</option>
                                                                        <option value="Pasaporte" {{ $solicitante->poder["tipo_identificacion"] == "Pasaporte" ? "selected" : '' }}>Pasaporte</option>
                                                                        <option value="Cédula profesional" {{ $solicitante->poder["tipo_identificacion"] == "Cédula profesional" ? "selected" : '' }}>Cédula Profesional</option>
                                                                        <option value="Licencia de conducir" {{ $solicitante->poder["tipo_identificacion"] == "Licencia de conducir" ? "selected" : '' }}>Licencia de Conducir</option>
                                                                        <option value="Credencial de inapam" {{ $solicitante->poder["tipo_identificacion"] == "Credencial de inapam" ? "selected" : '' }}>Credencial de INAPAM</option>
                                                                        <option value="Cartilla militar" {{ $solicitante->poder["tipo_identificacion"] == "Cartilla militar" ? "selected" : '' }}>Cartilla Militar</option>
                                                                        <option value="Documento migratorio" {{ $solicitante->poder["tipo_identificacion"] == "Documento migratorio" ? "selected" : '' }}>Documento Migratorio</option>
                                                                        <option value="Constancia de identidad" {{ $solicitante->poder["tipo_identificacion"] == "Constancia de identidad" ? "selected" : '' }}>Constancia de Identidad</option>
                                                                        <option value="Otro" {{ $solicitante->poder["tipo_identificacion"] == "Otro" ? "selected" : '' }}>Otros</option>
                                                                    </select>
                                                                    <div class="invalid-feedback">
                                                                        Este campo identificación es obligatorio.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xs-12 col-sm-12 col-md-6"> 
                                                                <div class="form-group">
                                                                    <label for="name">Núm de identificación <span style="color:red;">(*)</span> <span data-bs-toggle="modal" data-bs-target="#helpModal" style="cursor: pointer;">❓</span></label>
                                                                    <input type="text" name="num_identificacion_pFCR" class="form-control" oninput="this.value = this.value.toUpperCase()"  value="{{$solicitante->poder->num_identificacion }}" disabled> 
                                                                    <div class="invalid-feedback">
                                                                        El campo núm. de identificación es obligatorio.
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h5 class="text-center" style="color:#CEA845">Datos de la documentación que acredite la personeria</h5>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-4">  
                                                            <div class="form-group">
                                                                <label for="name">Tipo de documento <span style="color:red;">(*)</span></label>
                                                                <select name="tipo_documento_pF" id="tipo_documento_pF" class="form-control" disabled>
                                                                    <option value="">Seleccione</option>
                                                                    <option value="Carta Poder" {{ $solicitante->poder["tipo_documento_representante"] == "Carta Poder" ? "selected" : '' }}>Carta Poder</option>
                                                                    <option value="Instrumento Notarial" {{ $solicitante->poder["tipo_documento_representante"] == "Instrumento Notarial" ? "selected" : '' }}>Instrumento Notarial</option>
                                                                </select>
                                                                <div class="invalid-feedback">
                                                                    El campo es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                                            <div class="form-group">
                                                                <label for="">Fecha expedición <span style="color:red;">(*)</span></label>
                                                                <input type="date" class="form-control" name="fecha_expedicion_pF" value="{{$solicitante->poder->fechaRegistro }}" disabled>
                                                                <div class="invalid-feedback">
                                                                    La fecha es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                                            <div class="form-group">
                                                                <label for="">Fecha vigencia</label>
                                                                <input type="date" class="form-control" name="fecha_vigencia_pF" value="{{$solicitante->poder->fechaVigencia }}" disabled>
                                                                <div class="invalid-feedback">
                                                                    La fecha es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>  
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <label for="">Descripción del documento que acredite la personaria</label>
                                                                <textarea class="form-control" aria-describedby="basic-addon1" name="descripcion_pF" disabled>{{$solicitante->poder->descipcion_poder}}</textarea>
                                                                <div class="invalid-feedback">
                                                                    La descripción es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h5 class="text-center" style="color:#CEA845">Documentos del Solicitante</h5>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                @if($solicitante->poder->tipo == 'Fisica')
                                                                    <label>*Identificación del Empleador</label><br>
                                                                @else
                                                                    <label>*Acta Constitutiva</label><br>
                                                                @endif
                                                                @if(!empty($solicitante->poder->ineDocumento))
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->ineDocumento]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @endif
                                                                <div class="invalid-feedback">
                                                                    La Identificación es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Identificación del Representante Legal</label><br>
                                                                @if($solicitante->poder->representacionDocumento != NULL)
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->representacionDocumento]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @endif
                                                                <div class="invalid-feedback">
                                                                    El documento de representación es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Documento que acredite la personería</label><br>
                                                                @if($solicitante->poder->cedulaDocumento != NULL)
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->cedulaDocumento]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>Anexo (Documentos Complementarios)</label><br>
                                                                @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->anexo_documeto]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @else
                                                                    <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">Sin anexo</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h4 class="text-center">Documentos del representante</h4>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Identificación del Empleador</label><br>
                                                                @if(!empty($solicitante->poder->ineDocumento))
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->ineDocumento]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @endif
                                                                <div class="invalid-feedback">
                                                                    La Identificación es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>Anexo (Documentos Complementarios)</label><br>
                                                                @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->anexo_documeto]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @else
                                                                    <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">Sin anexo</a>
                                                                @endif
                                                            </div>
                                                        </div>

                                                    @endif
                                                @endif

                                                @if ($general->tipo_solicitud == 1) 
                                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                            <h4 class="text-center">Datos Laborales</h4>
                                                        </div>
                                                    </div>
                                                        
                                                    <div class="col-xs-12 col-sm-6 col-md-3">
                                                        <div class="form-group">
                                                            <label for="password">Seguro Social</label>
                                                            <input type="text" class="form-control" name="nss" value="<?=$solicitante["nss"];?>">   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-3">
                                                        <div class="form-group">
                                                            <label for="password">Puesto<span style="color:red;"> (*)</span></label>
                                                            <input type="text" class="form-control" name="puesto" value="<?=$solicitante["puesto"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo puesto es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-3">
                                                        <div class="form-group">
                                                            <label for="name">Periodo de pago<span style="color:red;"> (*)</span></label>
                                                            <select name="periodo_pago" class="form-control" required>
                                                                <option value="">SELECCIONE</option>
                                                                <option value="Semanal"      {{ $solicitante['periodo_pago'] == 'Semanal' ? "selected" : '' }}>SEMANAL</option>
                                                                <option value="Quincenal"   {{ $solicitante['periodo_pago'] == 'Quincenal' ? "selected" : '' }}>QUINCENAL</option>
                                                                <option value="Mensual"     {{ $solicitante['periodo_pago'] == 'Mensual' ? "selected" : '' }}>MENSUAL</option>
                                                                <option value="Diario"      {{ $solicitante['periodo_pago'] == 'Diario' ? "selected" : '' }}>DIARIO</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                El campo periodo de pago es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-3">
                                                        <div class="form-group">
                                                            <label for="password">Sueldo<span style="color:red;"> (*)</span></label>
                                                            <input type="text" class="form-control" name="pago" value="<?=$solicitante["pago"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo sueldo es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-2">
                                                        <div class="form-group">
                                                            <label for="password">Fecha de ingreso<span style="color:red;"> (*)</span></label>
                                                            <input type="date" class="form-control" name="fecha_ingreso" value="<?=$solicitante["fecha_ingreso"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo fecha de ingreso es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-2" id= "fechaSalida" hidden>
                                                        <div class="form-group">
                                                            <label for="password">Fecha de salida</label>
                                                            <input type="date" class="form-control" name="fecha_salida" value="<?=$solicitante["fecha_salida"];?>">   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                                        <div class="form-group">
                                                            <label for="name">Horario laboral<span style="color:red;"> (*)</span></label>
                                                            <input type="text" name="jornada" class="form-control" value="<?=$solicitante["jornada"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo horario laboral es obligatoria.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-xs-12 col-sm-6 col-md-2">
                                                        <div class="form-group">
                                                            <label for="password">Horas trabajadas a la semana<span style="color:red;"> (*)</span></label>
                                                            <input type="number" class="form-control" min="0" name="horas_semana" value="<?=$solicitante["horas_semana"];?>" required>
                                                            <div class="invalid-feedback">
                                                                El campo horas trabajadas es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-6 col-md-2">
                                                        <div class="form-group">
                                                            <label for="password">Labora actualmente<span style="color:red;"> (*)</span></label>
                                                            <!--input type="text" class="form-control" name="labora" id="laboraActualmenteValue" value="<?=$solicitante["labora"];?>" required-->
                                                            <select name="labora" id="laboraActualmenteValue" class="form-control" required>
                                                                <option value="Si" {{ $solicitante["labora"] == 'Si' ? "selected" : '' }}>SI</option>
                                                                <option value="No"  {{ $solicitante['labora'] == 'No' ? "selected" : '' }}>NO</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                                El campo labora actualmente es obligatorio.
                                                            </div>   
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                            <label for="name">Describe brevemente el motivo de tu solicitud <span style="color:red;">(*)</span></label>
                                                            <textarea class="form-control js-autoalto" name="descripcionSolicitud" required>{{ $solicitante["descripcionSolicitud"] ?? '' }}</textarea>
                                                            <div class="invalid-feedback">
                                                                El campo descripción del motivo de la solicitud es obligatorio.
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                            <!--div class="col-xs-12 col-sm-12 col-md-2" style="margin-top: 20px;">
                                                <button type="submit" class="btn btn-info btn-block">Actualizar datos del solicitante</button>
                                            </div-->
                                            @endforeach
                                        </div>
                                    </div>
                                    <div id="documentos" class="tabcontent">
                                        <div class="ra-seccion__cab">
                                            <div><h4 class="ra-seccion__titulo">Citado(s)</h4></div>
                                        </div>
                                        <div id="tabla_documentos" class="row">

                                            @if ($general->tipo_solicitud == 1)
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <a type="button" class="btn ra-btn-agregar open-modal mb-3" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal1" data-id="{{ $id }}"><i class="bi bi-person-plus"></i> Agregar Citado</a>
                                                    <a type="button" class="btn btn-outline-danger open-modal mb-3" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal2" data-id="{{ $id }}"><i class="bi bi-person-dash"></i> Borrar Citado</a>
                                                </div>
                                            @endif

                                            @foreach($citados as $citado)
                                                @php
                                                    $esResulte = isset($citado['resulte_responsable']) ? $citado['resulte_responsable'] : (isset($citado->resulte_responsable) ? $citado->resulte_responsable : 'No');
                                                    $nombreCitado = trim(($citado['nombre'] ?? '').' '.($citado['primer_apellido'] ?? '').' '.($citado['segundo_apellido'] ?? ''));
                                                @endphp
                                                {{-- Tarjeta del citado (mismo estilo que Revisar audiencia). --}}
                                                <div class="col-12">
                                                <div class="ra-parte ra-seccion">
                                                <div class="ra-parte__cab">
                                                    <div class="ra-parte__quien">
                                                        <span class="ra-parte__num">{{ $loop->iteration }}</span>
                                                        <div>
                                                            <div class="ra-parte__nombre">{{ $nombreCitado !== '' ? $nombreCitado : 'Citado' }}</div>
                                                            <div class="ra-parte__chips">
                                                                @if (!empty($citado['tipo_persona']))
                                                                    <span class="ra-et">Persona {{ $citado['tipo_persona'] == 'Fisica' ? 'física' : 'moral' }}</span>
                                                                @endif
                                                                @if ($esResulte == 'Si')
                                                                    <span class="ra-et ra-et--resulte">Quien resulte responsable</span>
                                                                @endif
                                                                @if (!empty($citado['notificacion']))
                                                                    <span class="ra-et ra-et--info"><i class="bi bi-envelope"></i> Notifica: {{ $citado['notificacion'] == 'Centro' ? 'Centro de conciliación' : 'Solicitante' }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ra-seccion__herr">
                                                        <span class="ra-seccion__cambios" hidden><i class="bi bi-dot"></i> Cambios sin guardar</span>
                                                        <button type="button" class="btn btn-sm ra-editar"><i class="bi bi-pencil"></i> <span>Editar datos</span></button>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group mt-2">
                                                        <h4 class="text-center">Datos personales del citado</h4>
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

                                                <div class="col-xs-12 col-sm-6 col-md-3">
                                                        <div class="form-group">
                                                            <label for="name">¿Requiere traductor?<span style="color:red;"> (*)</span></label>
                                                            <select name="traductor[]" id="traductor_{{$loop->index}}" class="form-control" required>
                                                                <option value="No" {{ (isset($citado['traductor']) ? ($citado['traductor'] ? '' : 'selected') : (isset($citado->traductor) && !$citado->traductor ? 'selected' : '')) }}>No</option>
                                                                <option value="Si" {{ (isset($citado['traductor']) ? ($citado['traductor'] ? 'selected' : '') : (isset($citado->traductor) && $citado->traductor ? 'selected' : '')) }}>Si</option>
                                                            </select>
                                                            <div class="invalid-feedback">Este campo es obligatorio.</div>
                                                        </div>
                                                </div>
                                                <div class="col-xs-12 col-sm-6 col-md-3" id="lenguaje_wrap_{{$loop->index}}">
                                                    <div class="form-group">
                                                        <label for="name">¿Qué tipo de lenguaje requiere?<span style="color:red;"> (*)</span></label>
                                                        <input type="text" name="lenguaje" id="lenguaje_{{$loop->index}}" class="form-control" value="{{ $citado['lenguaje'] ?? ($citado->lenguaje ?? '') }}" oninput="this.value = this.value.toUpperCase()">
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
                                                            <option value="PRIVADA"       {{ $citado['tipo_vialidad'] == 'PRIVADA' ? "selected" : '' }} >Privada</option>
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
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-5">
                                                    <label for="password">Referencia Imagen 1<span style="color:red;"> (*)</span></label><br>
                                                    @php
                                                        $hasRefImg1 = (!empty($citado->imagen_domicilio1) && $citado->imagen_domicilio1 !== 'Sin documento');
                                                    @endphp
                                                    @if ($hasRefImg1)
                                                        <a target="_blank" class="btn btn-sm btn-outline-secondary mb-1" href="{{ signedDocRoute('documentos.ver_imagen', ['id_solicitud' => $citado->id_solicitud ?? $id, 'filename' => $citado->imagen_domicilio1]) }}"><i class="bi bi-image"></i> Ver imagen</a><br>
                                                    @else
                                                        <span class="text-muted">No se subió imagen</span>
                                                    @endif
                                                    <input type="file" name="foto1[]" accept="image/*" class="form-control" {{ $hasRefImg1 ? '' : 'required' }}>
                                                    <div class="invalid-feedback">Debe subir la Referencia Imagen 1 para poder confirmar.</div>
                                                    <input type="hidden" name="imagen_domicilio1[]" value="{{ $citado->imagen_domicilio1 }}">
                                                    <input type="hidden" class="ref-img1-exists" value="{{ $hasRefImg1 ? '1' : '0' }}">
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-5">
                                                    <label for="password">Referencia Imagen 2</label><br>
                                                    @if (!empty($citado->imagen_domicilio2) && $citado->imagen_domicilio2 !== 'Sin documento')
                                                        <a target='_blank' class="btn btn-sm btn-outline-secondary mb-1" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'solicitud', 'id' => $general->id, 'archivo' => $citado->imagen_domicilio2]) }}"><i class="bi bi-image"></i> Ver imagen</a><br>
                                                    @else
                                                        <span class="text-muted">No se subió imagen</span>
                                                    @endif
                                                    <input type="file" name="foto2[]" accept="image/*" class="form-control">
                                                    <input type="hidden" name="imagen_domicilio2[]" value="{{ $citado->imagen_domicilio2 }}">
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-12"><br></div>

                                                @if ($general->tipo_solicitud == 2)
                                                    @foreach($solicitantes as $solicitante)
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h4 class="text-center">Datos laborales del citado</h4>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-xs-12 col-sm-6 col-md-3">
                                                            <div class="form-group">
                                                                <label for="password">Seguro Social</label>
                                                                <input type="text" class="form-control" name="nss" value="<?=$solicitante["nss"];?>">   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-3">
                                                            <div class="form-group">
                                                                <label for="password">Puesto<span style="color:red;"> (*)</span></label>
                                                                <input type="text" class="form-control" name="puesto" value="<?=$solicitante["puesto"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo puesto es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-3">
                                                            <div class="form-group">
                                                                <label for="name">Periodo de pago<span style="color:red;"> (*)</span></label>
                                                                <select name="periodo_pago" class="form-control" required>
                                                                    <option value="">SELECCIONE</option>
                                                                    <option value="Semanal"      {{ $solicitante['periodo_pago'] == 'Semanal' ? "selected" : '' }}>SEMANAL</option>
                                                                    <option value="Quincenal"   {{ $solicitante['periodo_pago'] == 'Quincenal' ? "selected" : '' }}>QUINCENAL</option>
                                                                    <option value="Mensual"     {{ $solicitante['periodo_pago'] == 'Mensual' ? "selected" : '' }}>MENSUAL</option>
                                                                    <option value="Diario"      {{ $solicitante['periodo_pago'] == 'Diario' ? "selected" : '' }}>DIARIO</option>
                                                                </select>
                                                                <div class="invalid-feedback">
                                                                    El campo periodo de pago es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-3">
                                                            <div class="form-group">
                                                                <label for="password">Sueldo<span style="color:red;"> (*)</span></label>
                                                                <input type="text" class="form-control" name="pago" value="<?=$solicitante["pago"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo sueldo es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-2">
                                                            <div class="form-group">
                                                                <label for="password">Fecha de ingreso<span style="color:red;"> (*)</span></label>
                                                                <input type="date" class="form-control" name="fecha_ingreso" value="<?=$solicitante["fecha_ingreso"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo fecha de ingreso es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-2" id= "fechaSalida" hidden>
                                                            <div class="form-group">
                                                                <label for="password">Fecha de salida</label>
                                                                <input type="date" class="form-control" name="fecha_salida" value="<?=$solicitante["fecha_salida"];?>">   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-4">
                                                            <div class="form-group">
                                                                <label for="name">Horario laboral<span style="color:red;"> (*)</span></label>
                                                                <input type="text" name="jornada" class="form-control" value="<?=$solicitante["jornada"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo horario laboral es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                        <div class="col-xs-12 col-sm-6 col-md-2">
                                                            <div class="form-group">
                                                                <label for="password">Horas trabajadas a la semana<span style="color:red;"> (*)</span></label>
                                                                <input type="number" class="form-control" min="0" name="horas_semana" value="<?=$solicitante["horas_semana"];?>" required>
                                                                <div class="invalid-feedback">
                                                                    El campo horas trabajadas es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-6 col-md-2">
                                                            <div class="form-group">
                                                                <label for="password">Labora actualmente<span style="color:red;"> (*)</span></label>
                                                                <!--input type="text" class="form-control" name="labora" id="laboraActualmenteValue" value="<?=$solicitante["labora"];?>" required-->
                                                                <select name="labora" id="laboraActualmenteValue" class="form-control" required>
                                                                    <option value="Si" {{ $solicitante["labora"] == 'Si' ? "selected" : '' }}>SI</option>
                                                                    <option value="No"  {{ $solicitante['labora'] == 'No' ? "selected" : '' }}>NO</option>
                                                                </select>
                                                                <div class="invalid-feedback">
                                                                    El campo labora actualmente es obligatorio.
                                                                </div>   
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <label for="name">Describe brevemente el motivo de tu solicitud <span style="color:red;">(*)</span></label>
                                                                <textarea class="form-control js-autoalto" name="descripcionSolicitud" required>{{ $solicitante["descripcionSolicitud"] ?? '' }}</textarea>
                                                                <div class="invalid-feedback">
                                                                    El campo descripción del motivo de la solicitud es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                </div>{{-- /row --}}
                                                </div>{{-- /ra-parte --}}
                                                </div>{{-- /col-12 --}}
                                            @endforeach

                                        </div>
                                    </div>

                                    <div id="citados" class="tabcontent ra-seccion">
                                        @include('audiencias._revisar_seccion_cab', ['titulo' => 'Documentos', 'puedeEditar' => true])
                                        <div id="tabla_citados" class="row">
                                                @if($general->tipo_solicitud == 1)
                                                    <div class="col-xs-12 col-sm-12 col-md-6">
                                                        <label for="password">Identificación Oficial <span style="color:red;">(*)</span></label><br>
                                                        <a class="btn btn-info" target="_blank" href="{{ signedDocRoute('documento_identificacion_solicitante_ver', $id) }}">Visualizar</a>
                                                    </div>
    
                                                    <div class="col-xs-12 col-sm-12 col-md-6 ra-solo-edicion">
                                                        <label for="password">Reemplazar Identificación Oficial</label><br>
                                                        <input type="file" name="documentoIdentificacion" accept=".pdf" class="form-control">
                                                    </div>
                                                    <br>
                                                @else
                                                    @if($solicitante->poder->reprecentante == 'Si')
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                @if($solicitante->poder->tipo == 'Fisica')
                                                                    <label>*Identificación del Empleador</label><br>
                                                                @else
                                                                    <label>*Acta Constitutiva</label><br>
                                                                @endif
                                                                @if(!empty($solicitante->poder->ineDocumento))
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->ineDocumento]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @endif
                                                                <div class="invalid-feedback">
                                                                    La Identificación es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Identificación del Representante Legal</label><br>
                                                                @if($solicitante->poder->representacionDocumento != NULL)
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->representacionDocumento]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @endif
                                                                <div class="invalid-feedback">
                                                                    El documento de representación es obligatorio.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Documento que acredite la personería</label><br>
                                                                @if($solicitante->poder->cedulaDocumento != NULL)
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->cedulaDocumento]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>Anexo (Documentos Complementarios)</label><br>
                                                                @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->anexo_documeto]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @else
                                                                    <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">Sin anexo</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-xs-12 col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <h4 class="text-center">Documentos del representante</h4>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>*Identificación del Empleador</label><br>
                                                                @if(!empty($solicitante->poder->ineDocumento))
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->ineDocumento]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @endif
                                                                <div class="invalid-feedback">
                                                                    La Identificación es obligatoria.
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-xs-12 col-sm-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>Anexo (Documentos Complementarios)</label><br>
                                                                @if($solicitante->poder->anexo_documeto != "Sin anexo")
                                                                    <a target="_blank" class="btn btn-sm btn-outline-secondary" href="{{ signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $solicitante->poder->idAbogado, 'archivo' => $solicitante->poder->anexo_documeto]) }}"><i class="bi bi-eye"></i> Existente</a>
                                                                @else
                                                                    <a class="btn btn-secondary disabled" href="#" tabindex="-1" aria-disabled="true">Sin anexo</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif

                                            @foreach ($solicitantes as $solicitante)
                                               <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
                                                    <div class="form-group">
                                                        <label for="name">Tipo de Identificación<span style="color:red;" ></span> (*)</span></label>
                                                        <select name="tipoIdentificacion" class="form-control" required @if ($general->tipo_solicitud == 2 && $solicitante['identificacion'] != NULL) disabled @endif>
                                                            <option value="">SELECCIONE</option>
                                                            <option value="Credencial de elector"          {{ $solicitante['identificacion'] == 'Credencial de elector' ? "selected" : '' }}   >Credencial de elector</option>
                                                            <option value="Pasaporte"        {{ $solicitante['identificacion'] == 'Pasaporte' ? "selected" : '' }} >Pasaporte</option>
                                                            <option value="Cédula profesional"        {{ $solicitante['identificacion'] == 'Cédula profesional' ? "selected" : '' }} >Calzada</option>
                                                            <option value="Licencia de conducir"          {{ $solicitante['identificacion'] == 'Licencia de conducir' ? "selected" : '' }}   >Licencia de conducir</option>
                                                            <option value="Otro"        {{ $solicitante['identificacion'] == 'Otro' ? "selected" : '' }} >Otros</option>
                                                            <option value="Credencial de inapam"        {{ $solicitante['identificacion'] == 'Credencial de inapam' ? "selected" : '' }} >Credencial de inapam</option>
                                                            <option value="Cartilla militar"          {{ $solicitante['identificacion'] == 'Cartilla militar' ? "selected" : '' }}   >Cartilla militar</option>
                                                            <option value="Documento migratorio"        {{ $solicitante['identificacion'] == 'Documento migratorio' ? "selected" : '' }} >Documento migratorio</option>
                                                            <option value="Constancia de identidad"        {{ $solicitante['identificacion'] == 'Constancia de identidad' ? "selected" : '' }} >Constancia de identidad</option>                                                 
                                                        </select>
                                                        @if ($general->tipo_solicitud == 2 && $solicitante['identificacion'] != NULL)
                                                            <input type="hidden" name="tipoIdentificacion" value="{{ $solicitante['identificacion'] }}">
                                                        @endif
                                                        <div class="invalid-feedback">
                                                            El campo Tipo de Identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
                                                    <div class="form-group">
                                                        <label for="name">Número de identificación<span style="color:red;"> (*)</span></label>
                                                        <input type="text" name="numeroIdentificacion" maxlength="20" class="form-control" value="{{ $solicitante['num_identificacion'] ?? '' }}" required @if ($general->tipo_solicitud == 2 && $solicitante['num_identificacion'] != NULL) readonly @endif>
                                                            
                                                        <div class="invalid-feedback">
                                                            El campo Número de Identificación es obligatorio.
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                        </div>
                                    </div>

                                    <div id="observaciones" class="tabcontent ra-seccion">
                                        @include('audiencias._revisar_seccion_cab', ['titulo' => 'Observaciones', 'nota' => 'Sólo lectura', 'puedeEditar' => false])
                                        <div id="tabla_citados" class="row">

                                            <div class="col-xs-12 col-sm-6 col-md-12">
                                                <div class="form-group">
                                                    <label for="email">Observaciones</label>
                                                    <input type="text" class="form-control" name="observaciones" value="<?=$general["observaciones"];?>" readonly>
                                                </div>
                                            </div>
          
                                            <!--div class="col-xs-12 col-sm-12 col-md-12"><br>
                                                <a class="btn btn-primary" href="{{ url()->previous() }}">Regresar</a>
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
    <form class='needs-validation novalidate'  method='POST' action="{{route('agregar_citado_edicion')}}" enctype="multipart/form-data">
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
                                <label for="name">Agregar "Quien resulte responsable" <span style="color:red;">(*)</span></label>
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
                                         El campo conflicto es obligatorio.
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
                                            <input type="text" name="nombre" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Primer apellido <span style="color:red;">(*)</span></label>
                                            <input type="text" name="primer_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
                                            <div class="invalid-feedback">
                                                El nombre es obligatorio.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label for="name">Segundo apellido</label>
                                            <input type="text" name="segundo_apellido" class="form-control" oninput="this.value = this.value.toUpperCase()" > 
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
                    <h5 class="modal-title" id="exampleModalLabel">Motivo de rechazo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped mt-2">
                            <thead style="background-color: #354647;">
                                <th style="color: #fff;">Nombre</th>
                                <th style="color: #fff;">CURP</th>
                                <th style="color: #fff;">Dirección</th>
                                <th style="color: #fff;">Acciones</th>
                            </thead>
                            <tbody>
                                @foreach($citados as $citado)
                                <tr>
                                    <td>{{$citado->nombre}} {{$citado->primer_apellido}} {{$citado->segundo_apellido}}</td>
                                    <td>{{$citado->curp}}</td>
                                    <td>{{$citado->colonia}}." ".{{$citado->calle}}</td>
                                    <td>
                                        <form method="POST" action="{{ route('borrar_citado_edicion') }} ">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <input type="hidden" name="id" value="{{$id}}">
                                            <input type="hidden" name="borrar" value="{{$citado->id}}">
                                            <button class="btn btn-danger" onclick=editar_rol(); type="submit">Eliminar</button>
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

<!-- Modal Rechazo de solicitud-->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form class='needs-validation novalidate'  method='POST' action="{{route('rechazar_solicitud')}}">
        @csrf
        <input type="hidden" id="modal-id" name="id" value="">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Motivo de la prevención</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea name="observaciones" style="width:100%"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/estadistica/estadisticaConfirmar.js') }}"></script>

    <script>
        (function () {
            function syncConfirmButtonByRefImg1() {
                var btn = document.getElementById('btnConfirmarSolicitud');
                if (!btn) return;

                var msg = document.getElementById('msgRefImg1');
                var rows = document.querySelectorAll('.ref-img1-exists');
                var inputs = document.querySelectorAll('input[type="file"][name="foto1[]"]');

                var missing = false;
                for (var i = 0; i < rows.length; i++) {
                    var exists = rows[i].value === '1';
                    if (exists) continue;

                    var fileInput = inputs[i];
                    var hasFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);
                    if (!hasFile) {
                        missing = true;
                        break;
                    }
                }

                btn.disabled = missing;
                if (msg) msg.style.display = missing ? 'block' : 'none';
            }

            document.addEventListener('change', function (e) {
                if (e.target && e.target.matches('input[type="file"][name="foto1[]"]')) {
                    syncConfirmButtonByRefImg1();
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                syncConfirmButtonByRefImg1();
            });

            window.addEventListener('click', function (e) {
                if (e.target && e.target.closest && e.target.closest('.tab')) {
                    setTimeout(syncConfirmButtonByRefImg1, 50);
                }
            });
        })();
    </script>
        <script>
            $(function(){
                $('#motivo_solicitud').on('change', validarcheckfolio);
            })

            let motivosSeleccionados = [];

                //Traductor por citado
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

                //Traductor en modal Agregar Citado
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

                // Actualiza el estado del botón Confirmar/Guardar cuando se agrega un motivo
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

                const needsLanguage = document.getElementById('needsLanguageSolicitante');
                const languageDiv = document.getElementById('languageRequired');
                const languageInput = document.getElementById('languageValueSolicitante');

                const hasDisability = document.getElementById('hasDisabilitySolicitante');
                const disabilityDiv = document.getElementById('disabilityRequired');
                const disabilityInput = document.getElementById('disabilityValueSolicitante');

                const laboraActualmente = document.getElementById('laboraActualmenteValue');
                const fechaSalida = document.getElementById('fechaSalida')

                function updateLanguageVisibility() {
                    if (!languageDiv || !needsLanguage) return;
                    if (needsLanguage.value === 'Si') {
                        languageDiv.hidden = false;
                        if (languageInput) languageInput.required = true;
                    } else {
                        languageDiv.hidden = true;
                        if (languageInput) languageInput.required = false;
                    }
                }

                function updateDisabilityVisibility() {
                    if (!disabilityDiv || !hasDisability) return;
                    if (hasDisability.value === 'Si') {
                        disabilityDiv.hidden = false;
                        if (disabilityInput) disabilityInput.required = true;
                    } else {
                        disabilityDiv.hidden = true;
                        if (disabilityInput) disabilityInput.required = false;
                    }
                }

                function updateFechaSalidaVisibility() {
                    if (!laboraActualmente || !fechaSalida) return;
                    if (laboraActualmente.value === 'Si') {
                        fechaSalida.hidden = true;
                    } else {
                        fechaSalida.hidden = false;
                    }
                }

                if (needsLanguage) needsLanguage.addEventListener('change', updateLanguageVisibility);
                if (hasDisability) hasDisability.addEventListener('change', updateDisabilityVisibility);
                if (laboraActualmente) laboraActualmente.addEventListener('change', updateFechaSalidaVisibility)

                updateLanguageVisibility();
                updateDisabilityVisibility();
                updateFechaSalidaVisibility();


                const forms = document.querySelectorAll('form.needs-validation');
                if (forms && forms.length) {
                    // Función para validar teléfonos en tiempo real
                    function updateTelefonoState(form){
                        try{
                            var tel1 = form.querySelector('input[name="telefono1_solicitante"]');
                            var tel2 = form.querySelector('input[name="telefono2_solicitante"]');
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
                        // Escuchar inputs de teléfono
                        var tel1input = form.querySelector('input[name="telefono1_solicitante"]');
                        var tel2input = form.querySelector('input[name="telefono2_solicitante"]');
                        if(tel1input){ tel1input.addEventListener('input', function(){ updateTelefonoState(form); }); tel1input.addEventListener('blur', function(){ updateTelefonoState(form); }); }
                        if(tel2input){ tel2input.addEventListener('input', function(){ updateTelefonoState(form); }); tel2input.addEventListener('blur', function(){ updateTelefonoState(form); }); }

                        form.addEventListener('submit', function(e) {
                            var valid = true;
                            var tel1 = form.querySelector('input[name="telefono1_solicitante"]');
                            var tel2 = form.querySelector('input[name="telefono2_solicitante"]');
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

                // Actualiza el estado del botón al eliminar un motivo
                try { updateMotivosState(); } catch (err) { /* ignore */ }
            });
        
            $('#tabla_detalles').show();
            //$('#tabla_solicitante').show();
            $('#tabla_citados').show();
            $('#tabla_documentos').show();
            $('#tabla_observaciones').show();
            $('#tabla_confirmar').show();
       
        $('.open-modal').click(function() {
            //console.log("hola");
            const id = $(this).data('id'); // Obtiene el valor de data-id
            //console.log(id);
            document.getElementById('modal-id').value = id;
        });
        
        // Verifica si hay al menos un motivo y habilita/deshabilita los botones de confirmar/guardar
        function updateMotivosState(){
            try{
                // hidden inputs añadidos en esta sesión
                var hidden = document.querySelectorAll('input[name="motivo_solicitud[]"]');

                // buscar tabla con motivos ya capturados en servidor (thead contiene 'Motivo capturado')
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
                // seleccionar botones relevantes dentro del área de confirmación
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
                        div.innerText = 'Debe agregar al menos un motivo para poder guardar/confirmar.';
                        confirmArea.appendChild(div);
                    }
                } else {
                    if(existing) existing.remove();
                }
            }catch(err){ console.warn('updateMotivosState', err); }
        }

        // Ejecutar al cargar la página
        try { document.addEventListener('DOMContentLoaded', updateMotivosState); } catch (err) {}
    </script>
    <script>
        function syncTipoCitado(index){
            try {
                var sel = document.querySelectorAll('select[name="tipo_persona_citado[]"]')[index];
                if(!sel) return;
                var nombreWrap = document.getElementById('nombre_wrap_' + index);
                var primerWrap = document.getElementById('primer_wrap_' + index);
                var nombreInput = document.querySelectorAll('input[name="nombre_citado[]"]')[index];
                var primerInput = document.querySelectorAll('input[name="primer_apellido[]"]')[index];
                var segundoWrap = document.getElementById('segundo_wrap_' + index);
                var segundoInput = document.querySelectorAll('input[name="segundo_apellido[]"]')[index];

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
    </script>
    <script>
        // Igual que Revisar audiencia: pestaña "Detalles" abierta al entrar y
        // encabezado con las acciones fijo arriba al bajar.
        document.addEventListener('DOMContentLoaded', function () {
            var boton = document.querySelector('.tab a[onclick*="\'detalles\'"]');
            if (boton) { boton.click(); }

            // El layout tiene overflow-x: hidden, así que position: sticky no
            // funciona: al pasar el encabezado se fija con JS. .ra-hueco es una
            // marca de 0 px arriba del encabezado; .ra-reserva ocupa su lugar
            // mientras está fijo para que el contenido no brinque.
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
            document.addEventListener('scroll', pedir, { passive: true, capture: true });
            window.addEventListener('resize', function () { if (fija) { soltar(); } pedir(); });
            revisar();
        });
    </script>
    <script>
        // Igual que Revisar audiencia: cada sección se ve en modo lectura y se
        // abre con "Editar datos" o con clic en el dato. Los campos siguen en
        // el formulario (readonly), así que se envían igual que antes; al
        // enviar se abren todas un momento para que la validación revise los
        // campos obligatorios (un campo readonly no se valida).
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('formSolicitud');
            if (!form) { return; }
            var secciones = Array.prototype.slice.call(form.querySelectorAll('.ra-seccion'));
            if (!secciones.length) { return; }
            var conErrores = {{ $errors->any() ? 'true' : 'false' }};
            // Citados agregados/borrados o motivos quitados que siguen pendientes en la sesión.
            var pendientesServidor = {{ (!empty(session('citados_edicion_new')) || !empty(session('citados_edicion_delete')) || !empty(session('motivos_edicion_delete'))) ? 'true' : 'false' }};
            // .ra-fijo: datos que no se editan (fecha de registro).
            var CAMPOS = 'input:not([type=hidden]):not([type=file]):not([type=checkbox]):not([type=radio]):not([type=button]):not([type=submit]):not(.ra-fijo), select, textarea';
            var cambios = false;

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
                var ico = sec.querySelector('.ra-editar i');
                if (ico) { ico.className = si ? 'bi bi-pencil' : 'bi bi-check2'; }
            }

            // Oculto dentro de la sección (no cuenta que la pestaña esté cerrada).
            function oculto(el, sec) {
                for (var n = el; n && n !== sec; n = n.parentElement) {
                    if (n.hidden || getComputedStyle(n).display === 'none') { return true; }
                }
                return false;
            }

            // Falta un obligatorio (incluye la Referencia Imagen 1 sin subir).
            function faltaAlgo(sec) {
                return Array.prototype.some.call(sec.querySelectorAll(CAMPOS + ', input[type=file][required]'), function (el) {
                    var vacio = el.type === 'file' ? !(el.files && el.files.length) : !el.value;
                    return el.required && !el.disabled && vacio && !oculto(el, sec);
                });
            }

            secciones.forEach(function (sec) {
                var editable = !!sec.querySelector('.ra-editar');
                // Se abre sola si viene de un error o le falta un dato obligatorio.
                var falta = editable && faltaAlgo(sec);
                lectura(sec, !(editable && (conErrores || falta)));
                if (falta) {
                    var herr = sec.querySelector('.ra-seccion__herr');
                    if (herr) {
                        var aviso = document.createElement('span');
                        aviso.className = 'ra-seccion__falta';
                        aviso.textContent = 'Falta un dato obligatorio';
                        herr.insertBefore(aviso, herr.firstChild);
                    }
                }
                if (editable) {
                    sec.classList.add('ra-editable');
                    sec.querySelector('.ra-editar').addEventListener('click', function () {
                        var abrir = sec.classList.contains('ra-lectura');
                        lectura(sec, !abrir);
                        if (abrir) {
                            var primero = Array.prototype.find.call(sec.querySelectorAll(CAMPOS), function (el) { return !el.disabled && !oculto(el, sec); });
                            if (primero) { try { primero.focus({ preventScroll: true }); } catch (e) {} }
                        }
                    });
                }
                var marcar = function (e) {
                    if (!e.isTrusted || !e.target.matches || !e.target.matches(CAMPOS + ', input[type=file]')) { return; }
                    if (e.target.id === 'motivo_solicitud') { return; }
                    cambios = true;
                    sec.querySelectorAll('.ra-seccion__cambios').forEach(function (m) { m.hidden = false; });
                };
                sec.addEventListener('input', marcar);
                sec.addEventListener('change', marcar);
            });

            // Clic directo en un dato (modo lectura): abre su sección y deja el
            // cursor en ese campo. Los campos en lectura no reciben el clic
            // (pointer-events: none), así que se toma el .form-group que lo envuelve.
            form.addEventListener('click', function (e) {
                var grupo = e.target.closest && e.target.closest('.form-group');
                var sec = grupo && grupo.closest('.ra-seccion');
                if (!sec || !sec.classList.contains('ra-lectura') || !sec.classList.contains('ra-editable')) { return; }
                if (e.target.closest('a, button, .btn, [data-bs-toggle], input[type=file]')) { return; }
                var campo = Array.prototype.find.call(grupo.querySelectorAll(CAMPOS), function (el) { return !el.disabled && !oculto(el, grupo); });
                if (!campo) { return; } // dato fijo o deshabilitado: no abre la edición
                lectura(sec, false);
                try { campo.focus({ preventScroll: true }); } catch (err) {}
                if (campo.tagName === 'SELECT' && typeof campo.showPicker === 'function') {
                    try { campo.showPicker(); } catch (err) {}
                }
            });

            // Al enviar: se abren todas para que la validación vea los obligatorios;
            // si algo falta, se queda abierta sólo la sección con el problema.
            form.addEventListener('submit', function () {
                var antes = secciones.map(function (sec) { return sec.classList.contains('ra-lectura'); });
                secciones.forEach(function (sec) { lectura(sec, false); });
                setTimeout(function () {
                    if (form.checkValidity()) { return; }
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

            // Regresar: si hay cambios sin guardar, se pregunta antes de salir.
            var regresar = document.getElementById('raRegresar');
            if (regresar) {
                regresar.addEventListener('click', function (e) {
                    var motivosNuevos = form.querySelectorAll('input[type=hidden][name="motivo_solicitud[]"]').length > 0;
                    if (!cambios && !motivosNuevos && !pendientesServidor) { return; }
                    e.preventDefault();
                    var destino = regresar.href;
                    var texto = 'Hiciste cambios que todavía no se guardan. Si sales ahora, se pierden.';
                    if (typeof swal === 'function') {
                        swal({
                            title: '¿Salir sin guardar?',
                            text: texto,
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Seguir editando',
                            cancelButtonText: 'Salir sin guardar',
                            confirmButtonColor: '#496163',
                            closeOnConfirm: true,
                            closeOnCancel: true
                        }, function (seguir) {
                            if (!seguir) { window.location.href = destino; }
                        });
                    } else if (window.confirm(texto + ' ¿Salir de todos modos?')) {
                        window.location.href = destino;
                    }
                });
            }
        });
    </script>

    @include('partials.general._textarea_autoalto')
@endsection


