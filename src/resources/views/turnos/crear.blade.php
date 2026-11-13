@extends('layouts.app')
@section('title', 'Nuevo turno')

{{--
    Turno de ventanilla: formulario corto. Recepción sólo captura el trámite,
    el municipio (de él sale la sede), si quien solicita es trabajador o
    empleador, su nombre completo, si se canaliza a casos de excepción y el
    horario. Los demás datos (CURP, domicilio, contacto, persona trabajadora)
    los captura el auxiliar en Mi módulo al atenderlo.

    Guarda en RecepcionController@turnos_guardar (CitaPatronal::camposVentanilla).
    El selector de día y hora es el mismo de /generar-cita (_cita_horario_scripts):
    los horarios dependen de la sede, el trámite y el caso de excepción.
--}}

@php
    $etiquetasTramite = ['Solicitud' => 'Solicitud de conciliación', 'Ratificación' => 'Ratificación de convenio'];
    // Cada recepción ve sólo los trámites que puede agendar; si es uno, ya va elegido.
    $tramitesForm = array_values(array_intersect(array_keys($etiquetasTramite), \App\Support\Recepcion::tramitesQuePuedeCrear(auth()->user())));
    $tipoInicial = old('tipo', request()->query('tipo'));
    if (count($tramitesForm) === 1) { $tipoInicial = $tramitesForm[0]; }
    $excepcionInicial = old('excepcion') === 'Si';
@endphp

@section('page_css')
    @include('turnos._cita_estilos')
    <style>
        .nt-tarjeta { max-width: 900px; }
        .nt-tarjeta .card-body { padding: 24px; }
        @media (max-width: 576px) { .nt-tarjeta .card-body { padding: 16px; } }

        .nt-excepcion { display: flex; align-items: flex-start; gap: 14px; padding: 14px 16px; border: 1px solid #E3E8E8; border-radius: 12px; background: #F7F9F9; transition: background .15s, border-color .15s; }
        .nt-excepcion .form-switch { padding-left: 0; margin: 0; }
        .nt-excepcion .form-check-input { width: 3em; height: 1.5em; margin: 2px 0 0; cursor: pointer; float: none; }
        .nt-excepcion .form-check-input:checked { background-color: #7A1F3D; border-color: #7A1F3D; }
        .nt-excepcion label { cursor: pointer; font-weight: 600; color: #2E3C3D; margin: 0; }
        .nt-excepcion small { display: block; color: #5E6E6F; font-weight: 400; margin-top: 2px; }
        .nt-excepcion.is-activa { background: #F4E6EC; border-color: #D9B3C3; }
    </style>
@endsection

@section('content')
    <section class="section">
        <div class="section-header mb-3">
            <div>
                {{-- Conciliación, Género e Igualdad no entra a la lista de turnos: vuelve a sus casos. --}}
                @php($soloExcepcion = \App\Support\CasosExcepcion::soloRol(auth()->user()))
                <a href="{{ $soloExcepcion ? route('excepcion') : route('turnos') }}" class="text-decoration-none small" style="color: #829A9C;">
                    <i class="bi bi-arrow-left"></i> {{ $soloExcepcion ? 'Casos de excepción' : 'Turnos' }}
                </a>
                <h3 class="page__heading mb-0">Nuevo turno</h3>
            </div>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> <strong>Turno registrado.</strong> {{ session('success') }}
                    @if (session('cita_id') && Route::has('citas.documento'))
                        <a class="btn btn-sm btn-success ms-2" href="{{ route('citas.documento', session('cita_id')) }}" target="_blank" rel="noopener">
                            <i class="bi bi-printer me-1"></i> Imprimir acuse
                        </a>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0 nt-tarjeta">
                <div class="card-header card-header-guinda py-3">
                    <h4 class="m-0 fs-5 "><i class="bi bi-calendar-plus me-2"></i>Agendar turno en ventanilla</h4>
                </div>
                <div class="card-body">
                    <form class="needs-validation gc-form" novalidate id="form_roles" method="POST" action="{{ route('turnos_guardar_nuevo') }}">
                        @csrf
                        <input type="hidden" name="excepcion" id="excepcion" value="{{ $excepcionInicial ? 'Si' : 'No' }}">
                        <input type="hidden" name="delegacion" id="delegacion" value="">
                        <input type="hidden" name="fecha_turno" id="fecha_turno" value="">
                        <input type="hidden" name="hora_turno" id="hora_turno" value="">

                        <p class="gc-intro">
                            Lo marcado con <span class="gc-req">*</span> es obligatorio. Los demás datos de la persona
                            los captura el auxiliar en <strong>Mi módulo</strong> al atenderla.
                        </p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="tipo">Tipo de trámite <span class="gc-req">*</span></label>
                                <select id="tipo" name="tipo" class="form-select" onchange="blockCalendar();" required>
                                    <option value="">Elige una opción</option>
                                    @foreach ($tramitesForm as $tramite)
                                        <option value="{{ $tramite }}" @selected($tipoInicial === $tramite)>{{ $etiquetasTramite[$tramite] }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Elige el tipo de trámite.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="municipio_citado">Municipio <span class="gc-req">*</span></label>
                                <select id="municipio_citado" name="municipio_solicitante" class="form-select" required>
                                    <option value="">Elige el municipio</option>
                                    @foreach ($municipios as $mun)
                                        <option value="{{ $mun['id'] }}" data-delegacion-id="{{ $mun['delegacion_id'] }}" @selected((string) old('municipio_solicitante') === (string) $mun['id'])>{{ $mun['nombre'] }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">De la fuente de empleo (o de la persona a citar). Define la sede.</div>
                                <div class="invalid-feedback">Elige el municipio.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="puesto">El solicitante es <span class="gc-req">*</span></label>
                                <select id="puesto" name="calidad" class="form-select" required>
                                    <option value="">Elige una opción</option>
                                    <option value="trabajador" @selected(old('calidad') === 'trabajador')>Trabajador</option>
                                    <option value="empleador" @selected(old('calidad') === 'empleador')>Empleador</option>
                                </select>
                                <div class="invalid-feedback">Elige una opción.</div>
                            </div>

                            <div class="col-md-6">
                                <div class="gc-oficina" id="gcOficina">
                                    <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                                    <div>
                                        <span class="gc-oficina__etiqueta">Sede</span>
                                        <span id="modulo_delegacion" class="gc-oficina__nombre">Elige el municipio</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="gcNombre">Nombre completo del solicitante <span class="gc-req">*</span></label>
                                <input type="text" id="gcNombre" name="nombre" class="form-control gc-mayus" maxlength="100" autocomplete="off"
                                       placeholder="Nombre(s) y apellidos" value="{{ old('nombre') }}" oninput="this.value = this.value.toUpperCase();" required>
                                <div class="invalid-feedback">Escribe el nombre completo.</div>
                            </div>

                            <div class="col-12">
                                <div class="nt-excepcion {{ $excepcionInicial ? 'is-activa' : '' }}" id="ntExcepcion">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="ntExcepcionSwitch" @checked($excepcionInicial)>
                                    </div>
                                    <label for="ntExcepcionSwitch">
                                        Canalizar a casos de excepción
                                        <small id="ntExcepcionAyuda">{{ $excepcionInicial
                                            ? 'Lo atenderá personal de casos de excepción. Los horarios son los de ese módulo.'
                                            : 'Actívalo sólo si el caso es de excepción (art. 685 Ter LFT): discriminación, acoso, beneficiarios, seguridad social, libertad sindical, etc.' }}</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <span class="form-label d-block">Día y hora <span class="gc-req">*</span></span>
                                <div class="gc-horario">
                                    <button type="button" id="botonCalendar" class="btn btn-guinda gc-horario__boton" data-bs-toggle="modal" data-bs-target="#calendarModal" disabled>
                                        <i class="bi bi-calendar-week me-2" aria-hidden="true"></i><span id="gcTextoCalendario">Ver horarios disponibles</span>
                                    </button>
                                    <div class="form-text" id="gcAyudaCalendario">Primero elige el trámite y el municipio.</div>
                                    <div id="resumenTurno" class="gc-turno" style="display:none;" aria-live="polite"></div>
                                    <div class="gc-error" id="gcErrorHorario" hidden>
                                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i> Elige el día y la hora del turno.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="gc-enviar">
                            <button id="btnGuardar" type="submit" class="btn btn-oro btn-lg gc-enviar__boton">
                                <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Agendar turno
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

{{-- Los modales van fuera de .main-content (ver nota en _cita_modales): el
     stack "modals" se pinta antes de los scripts, que buscan estos elementos. --}}
@push('modals')
    @include('turnos._horario_modal')
@endpush

@section('scripts')
    @include('turnos._cita_horario_scripts', ['modo' => 'ventanilla'])
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';
        var tipo       = document.getElementById('tipo');
        var municipio  = document.getElementById('municipio_citado');
        var delegacion = document.getElementById('delegacion');
        var oficina    = document.getElementById('gcOficina');
        var oficinaNom = document.getElementById('modulo_delegacion');
        var ayudaCal   = document.getElementById('gcAyudaCalendario');
        var sw         = document.getElementById('ntExcepcionSwitch');
        var excepcion  = document.getElementById('excepcion');
        var caja       = document.getElementById('ntExcepcion');
        var ayudaExc   = document.getElementById('ntExcepcionAyuda');
        var resumen    = document.getElementById('resumenTurno');

        var AYUDA_EXC = {
            si: 'Lo atenderá personal de casos de excepción. Los horarios son los de ese módulo.',
            no: 'Actívalo sólo si el caso es de excepción (art. 685 Ter LFT): discriminación, acoso, beneficiarios, seguridad social, libertad sindical, etc.'
        };

        function pintar() {
            var lista = !!delegacion.value && !!municipio.value;
            oficina.classList.toggle('is-lista', lista);
            if (!lista) oficinaNom.textContent = 'Elige el municipio';
            ayudaCal.textContent = (tipo.value && delegacion.value)
                ? 'Los horarios dependen de la sede, el trámite' + (excepcion.value === 'Si' ? ' y de casos de excepción' : '') + '. Toca uno y luego "Confirmar".'
                : 'Primero elige el trámite y el municipio.';
        }

        // Interruptor de excepción: cambia qué horarios hay, así que el elegido se descarta.
        sw.addEventListener('change', function () {
            excepcion.value = sw.checked ? 'Si' : 'No';
            caja.classList.toggle('is-activa', sw.checked);
            ayudaExc.textContent = sw.checked ? AYUDA_EXC.si : AYUDA_EXC.no;
            blockCalendar();
            pintar();
        });

        tipo.addEventListener('change', pintar);
        municipio.addEventListener('change', function () {
            // Si regresan a "Elige el municipio", la sede anterior no se queda.
            if (!municipio.value) { delegacion.value = ''; blockCalendar(); }
            setTimeout(pintar, 0);
        });

        // Restablece la clase del resumen al confirmar un horario nuevo.
        var confirmar = document.getElementById('confirmarTurno');
        if (confirmar) confirmar.addEventListener('click', function () { resumen.className = 'gc-turno'; }, true);

        // Si el servidor regresó el formulario (horario ocupado, etc.), la sede
        // sale otra vez del municipio; el día y la hora hay que volver a elegirlos.
        if (municipio.value) { municipio.dispatchEvent(new Event('change')); }
        blockCalendar();
        pintar();
        @if (session()->hasOldInput())
            resumen.className = 'gc-aviso-cambio';
            resumen.textContent = 'Se conservaron los datos capturados. Vuelve a elegir día y hora.';
            resumen.style.display = 'block';
        @endif
    });
    </script>
@endsection
