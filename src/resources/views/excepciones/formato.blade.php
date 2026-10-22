@extends('layouts.app')
@section('title', 'Formato 685 Ter')
{{--
    Formato de atención para casos previstos en el art. 685 Ter LFT
    (CasosExcepcionController::formato, App\Support\FormatoExcepcion).
    Se puede guardar como borrador y seguir después; el PDF se descarga en
    cualquier momento y lo que falte sale en blanco para llenarlo a mano.
--}}
@php
    $CX = \App\Support\CasosExcepcion::class;
    $v   = fn (string $campo, $omision = null) => old($campo, $datos[$campo] ?? $omision);
    $arr = fn (string $campo) => (array) old($campo, $datos[$campo] ?? []);
    $cita = $origen['cita'];
    $sol  = $origen['solicitud'];
    $caso = $origen['caso'];
    $rutaGuardar = route('excepciones.formato.guardar', ['tipo' => $origen['tipo'], 'id' => $origen['id']]);
    $estatusCita = ['pendiente' => 'Por llegar', 'confirmada' => 'En sala', 'atendido' => 'Atendida', 'expirada' => 'No llegó'];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .fx-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; }
        .fx-migas:hover { color: var(--tn-verde); }
        .fx-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 700; }
        .fx-badge--borrador { background: #FBF4E1; color: #7A5D10; }
        .fx-badge--nuevo { background: #EEF2F2; color: var(--tn-suave); }
        .fx-badge--continua { background: #E5F3EA; color: #1E7B3C; }
        .fx-badge--canaliza { background: #F6E4EA; color: #8C1D40; }
        .fx-badge--incompetente { background: #ECEAF4; color: #4B3F7A; }
        .fx-opcion--incompetente { border-style: dashed; }
        .fx-badge--no_excepcion { background: #EEF0F3; color: #5F6B7A; }
        .fx-opcion--noexc { border-style: dashed; }
        .fx-noexc { border: 1px solid #D5DDDD; }
        .fx-hora-ayuda { font-size: 12px; color: var(--tn-tenue); margin-top: 4px; }

        .fx-rejilla { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 16px; align-items: start; }
        @media (max-width: 1100px) { .fx-rejilla { grid-template-columns: 1fr; } .fx-lado { order: -1; } }

        .fx-seccion { margin-bottom: 16px; }
        .fx-seccion > h4 { font-size: 15px; font-weight: 800; color: var(--tn-tinta); margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
        .fx-seccion > h4 .fx-num { display: inline-grid; place-items: center; width: 24px; height: 24px; border-radius: 8px; background: #E7EFEF; color: var(--tn-verde); font-size: 12.5px; }
        .fx-campos { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 12px 14px; }
        .fx-c12 { grid-column: span 12; } .fx-c8 { grid-column: span 8; } .fx-c6 { grid-column: span 6; } .fx-c4 { grid-column: span 4; } .fx-c3 { grid-column: span 3; }
        @media (max-width: 760px) { .fx-c8, .fx-c6, .fx-c4, .fx-c3 { grid-column: span 12; } }
        .fx-campos label, .fx-etq { display: block; font-size: 12px; font-weight: 700; color: var(--tn-suave); margin-bottom: 5px; }
        .fx-ayuda { font-size: 11.5px; color: var(--tn-tenue); margin-top: 4px; }

        .fx-opciones { display: flex; flex-direction: column; gap: 8px; }
        .fx-opciones--fila { flex-direction: row; flex-wrap: wrap; }
        .fx-opcion { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border: 1px solid var(--tn-borde); border-radius: 10px; background: #fff; cursor: pointer; font-size: 14px; color: var(--tn-tinta); margin: 0; }
        .fx-opcion:hover { border-color: var(--tn-verde-claro); }
        .fx-campos label.fx-opcion { display: flex; font-size: 14px; font-weight: 400; color: var(--tn-tinta); margin: 0; }
        .fx-opcion input { margin-top: 3px; accent-color: #496163; flex: none; }
        .fx-opcion:has(input:checked) { border-color: var(--tn-verde); background: #F3F7F7; box-shadow: inset 0 0 0 1px var(--tn-verde); }
        .fx-extra { margin: 2px 0 4px 34px; }
        .fx-extra[hidden] { display: none; }

        .fx-decision { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 760px) { .fx-decision { grid-template-columns: 1fr; } }
        .fx-decision .fx-opcion { padding: 14px; }
        .fx-decision b { display: block; font-size: 15px; }
        .fx-decision small { display: block; color: var(--tn-tenue); font-size: 12.5px; margin-top: 2px; }
        .fx-nota { font-size: 12.5px; color: var(--tn-suave); background: var(--tn-fondo); border-radius: 10px; padding: 10px 12px; margin-bottom: 14px; }

        .fx-lado .tn-tarjeta { position: sticky; top: 80px; }
        .fx-lado dl { margin: 0; display: grid; gap: 10px; }
        .fx-lado dt { font-size: 11px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; color: var(--tn-tenue); }
        .fx-lado dd { margin: 1px 0 0; font-size: 13.5px; color: var(--tn-tinta); }

        .fx-barra {
            position: sticky; bottom: 0; z-index: 5; display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px;
            padding: 12px 16px; margin-top: 4px; background: rgba(255, 255, 255, .96); border: 1px solid var(--tn-borde); border-radius: 12px;
            box-shadow: 0 -6px 18px rgba(46, 60, 61, .06);
        }
        .fx-barra .fx-barra__nota { margin-right: auto; align-self: center; font-size: 12.5px; color: var(--tn-tenue); }

        .fx-oficios { display: grid; gap: 12px; }
        .fx-oficio { border: 1px solid var(--tn-borde); border-radius: 12px; padding: 12px 14px; background: #fff; }
        .fx-oficio__cabeza { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
        .fx-oficio__cabeza b { font-size: 14px; color: var(--tn-tinta); }
        .fx-paso { display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 800; color: var(--tn-tinta); margin: 4px 0 10px; }
        .fx-paso small { font-weight: 500; color: var(--tn-tenue); font-size: 12px; }
        .fx-paso .fx-num, .fx-oficio .fx-num { display: inline-grid; place-items: center; width: 22px; height: 22px; border-radius: 7px; background: #E7EFEF; color: var(--tn-verde); font-size: 12px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <a class="fx-migas" href="{{ route('excepcion') }}"><i class="bi bi-arrow-left"></i> Casos de excepción</a>
                <h3 class="page__heading mb-1">Formato de atención · art. 685 Ter</h3>
                @if ($terminado)
                    <span class="fx-badge fx-badge--{{ $caso->resultado ?? 'continua' }}"><i class="bi bi-check2-circle"></i> {{ $CX::RESULTADOS[$caso->resultado ?? ''] ?? 'Atendido' }}</span>
                @elseif ($caso)
                    <span class="fx-badge fx-badge--borrador"><i class="bi bi-pencil"></i> Borrador</span>
                @else
                    <span class="fx-badge fx-badge--nuevo"><i class="bi bi-magic"></i> Prellenado con lo que ya hay en el sistema</span>
                @endif
            </div>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('excepciones.formato.pdf', ['tipo' => $origen['tipo'], 'id' => $origen['id']]) }}" target="_blank" rel="noopener">
                <i class="bi bi-file-earmark-pdf"></i> PDF de lo guardado
            </a>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if (! $migrado)
                <div class="alert alert-warning">Falta correr la migración del formato (<code>php artisan migrate</code>). Mientras tanto se puede ver y descargar, pero no guardar.</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <b>Para terminar falta:</b>
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ $rutaGuardar }}" id="fxForm" novalidate>
                @csrf
                <div class="fx-rejilla">
                    <div>
                        {{-- Encabezado --}}
                        <div class="tn-tarjeta fx-seccion">
                            <h4><i class="bi bi-calendar3"></i> Fecha y folio</h4>
                            <div class="fx-campos">
                                <div class="fx-c4"><label for="fxFecha">Fecha</label><input type="date" id="fxFecha" name="fecha" class="form-control" value="{{ $v('fecha') }}"></div>
                                <div class="fx-c8"><label for="fxFolio">Folio</label><input type="text" id="fxFolio" name="expediente" class="form-control" value="{{ $v('expediente') }}" maxlength="60">
                                    <div class="fx-ayuda">Por omisión, el expediente o el folio de la cita.</div></div>
                            </div>
                        </div>

                        {{-- Persona --}}
                        <div class="tn-tarjeta fx-seccion">
                            <h4><i class="bi bi-person"></i> Datos de la persona solicitante</h4>
                            <div class="fx-campos">
                                <div class="fx-c8"><label for="fxNombre">Nombre completo</label><input type="text" id="fxNombre" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ $v('nombre') }}"></div>
                                <div class="fx-c4"><label for="fxEdad">Edad</label><input type="number" id="fxEdad" name="edad" class="form-control" min="0" max="120" value="{{ $v('edad') }}"></div>
                                <div class="fx-c12">
                                    <span class="fx-etq">Género</span>
                                    <div class="fx-opciones fx-opciones--fila">
                                        @foreach (['M' => 'M', 'F' => 'F', 'Otro' => 'Otro'] as $valor => $etiqueta)
                                            <label class="fx-opcion"><input type="radio" name="genero" value="{{ $valor }}" @checked($v('genero') === $valor)> {{ $etiqueta }}</label>
                                        @endforeach
                                        <input type="text" name="genero_otro" id="fxGeneroOtro" class="form-control" style="max-width: 260px" placeholder="¿Cuál?" value="{{ $v('genero_otro') }}">
                                    </div>
                                </div>
                                <div class="fx-c6"><label for="fxTel">Teléfono de contacto</label><input type="tel" id="fxTel" name="telefono" class="form-control" value="{{ $v('telefono') }}"></div>
                                <div class="fx-c6"><label for="fxCorreo">Correo electrónico</label><input type="email" id="fxCorreo" name="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ $v('correo') }}"></div>
                                <div class="fx-c6">
                                    <label for="fxVulnerable">Grupo vulnerable</label>
                                    <select id="fxVulnerable" name="vulnerables" class="form-select">
                                        <option value="">Sin indicar</option>
                                        @foreach ($CX::GRUPOS_VULNERABLES as $g)
                                            <option value="{{ $g }}" @selected($v('vulnerables') === $g)>{{ $g === 'LGBTTTIQ' ? 'LGBTTTIQ+' : $g }}</option>
                                        @endforeach
                                    </select>
                                    <div class="fx-ayuda">Sólo para estadística; no sale en el formato impreso.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Fuente de empleo --}}
                        <div class="tn-tarjeta fx-seccion">
                            <h4><i class="bi bi-building"></i> Datos de la fuente de empleo</h4>
                            <div class="fx-campos">
                                <div class="fx-c12"><label for="fxEmpresa">Nombre de la empresa o persona empleadora</label><input type="text" id="fxEmpresa" name="empresa" class="form-control" value="{{ $v('empresa') }}"></div>
                                <div class="fx-c6"><label for="fxArea">Área de adscripción</label><input type="text" id="fxArea" name="area_adscripcion" class="form-control" value="{{ $v('area_adscripcion') }}"></div>
                                <div class="fx-c6"><label for="fxPuesto">Puesto</label><input type="text" id="fxPuesto" name="puesto" class="form-control" value="{{ $v('puesto') }}"></div>
                                <div class="fx-c12">
                                    <span class="fx-etq">¿Conoce el nombre de su jefe inmediato?</span>
                                    <div class="fx-opciones fx-opciones--fila">
                                        <label class="fx-opcion"><input type="radio" name="conoce_jefe" value="1" @checked((string) $v('conoce_jefe') === '1')> Sí</label>
                                        <label class="fx-opcion"><input type="radio" name="conoce_jefe" value="0" @checked((string) $v('conoce_jefe') === '0')> No</label>
                                        <input type="text" name="jefe_inmediato" id="fxJefe" class="form-control" style="max-width: 380px" placeholder="Nombre del jefe inmediato" value="{{ $v('jefe_inmediato') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        @php
                            // "No es caso de excepción": no sigue el formulario (FormatoExcepcion::reclasificar).
                            $noExc = old('no_excepcion') !== null ? old('no_excepcion') === '1' : ($datos['resultado'] ?? null) === 'no_excepcion';
                            $sigue = old('siguiente', $siguientes === ['terminar'] ? 'terminar' : null);
                        @endphp
                        {{-- 1. Tipo de situación --}}
                        <div class="tn-tarjeta fx-seccion">
                            <h4><span class="fx-num">1</span> Tipo de situación que enfrenta</h4>
                            <div class="fx-opciones @error('tipos') border border-danger rounded p-2 @enderror">
                                @foreach ($CX::TIPOS_FORMATO as $clave => $texto)
                                    <label class="fx-opcion"><input type="checkbox" name="tipos[]" value="{{ $clave }}" data-tipo="{{ $clave }}" @checked(in_array($clave, $arr('tipos'), true))> {{ $texto }}</label>
                                    @if ($clave === 'discriminacion')
                                        <div class="fx-extra fx-campos" data-de="discriminacion">
                                            <div class="fx-c6">
                                                <select name="motivo_discriminacion" id="fxMotivoDisc" class="form-select" aria-label="Señale el motivo">
                                                    <option value="">Señale el motivo…</option>
                                                    @foreach ($CX::MOTIVOS_DISCRIMINACION as $clave)
                                                        <option value="{{ $clave }}" @selected($v('motivo_discriminacion') === $clave)>{{ $CX::MOTIVOS[$clave][0] }}</option>
                                                    @endforeach
                                                    <option value="otro" @selected($v('motivo_discriminacion') === 'otro')>Otro</option>
                                                </select>
                                            </div>
                                            <div class="fx-c6" data-de="motivo_otro"><input type="text" name="motivos" class="form-control" placeholder="¿Cuál?" value="{{ $v('motivo_discriminacion') === 'otro' ? $v('motivos') : '' }}"></div>
                                        </div>
                                    @elseif ($clave === 'riesgo_trabajo')
                                        <div class="fx-extra fx-opciones fx-opciones--fila" data-de="riesgo_trabajo">
                                            <label class="fx-opcion"><input type="radio" name="riesgo_lugar" value="dentro" @checked($v('riesgo_lugar') === 'dentro')> Dentro del centro de trabajo</label>
                                            <label class="fx-opcion"><input type="radio" name="riesgo_lugar" value="fuera" @checked($v('riesgo_lugar') === 'fuera')> Fuera del centro de trabajo</label>
                                        </div>
                                    @endif
                                @endforeach
                                <input type="hidden" name="no_excepcion" value="0">
                                <label class="fx-opcion fx-opcion--noexc"><input type="checkbox" name="no_excepcion" value="1" id="fxNoExc" @checked($noExc)> No es caso de excepción</label>
                            </div>
                        </div>

                        {{-- No es caso de excepción: qué sigue --}}
                        <div class="tn-tarjeta fx-seccion fx-noexc" id="fxNoExcPanel" @unless ($noExc) hidden @endunless>
                            <h4><i class="bi bi-arrow-return-right"></i> No es caso de excepción: ¿qué sigue?</h4>
                            <p class="fx-nota">Ya no se llena el resto del formato. Al terminar se cierra el caso y la cita deja de estar marcada como excepción.</p>
                            <div class="fx-opciones @error('siguiente') border border-danger rounded p-2 @enderror">
                                @if (in_array('hoy', $siguientes, true))
                                    <label class="fx-opcion"><input type="radio" name="siguiente" value="hoy" @checked($sigue === 'hoy')>
                                        <span><b>Pasar a la fila de hoy</b><small>Se le asigna un módulo de {{ $cita->tipo ?? 'su trámite' }} en {{ $cita->delegacion ?? $sede }} y entra a la fila de hoy, ya en sala.</small></span></label>
                                    <label class="fx-opcion"><input type="radio" name="siguiente" value="otra_fecha" @checked($sigue === 'otra_fecha')>
                                        <span><b>Agendar en otra fecha</b><small>Cita de {{ $cita->tipo ?? 'su trámite' }} con el horario normal de la sede; conserva su folio.</small></span></label>
                                    <div class="fx-extra fx-campos" data-de="otra_fecha" id="fxOtraFecha"
                                         data-url="{{ url('api/obtenerTurnosDisponibles') }}" data-sede="{{ $cita->delegacion ?? '' }}" data-tipo="{{ $cita->tipo ?? '' }}">
                                        <div class="fx-c6"><label for="fxFechaNueva">Fecha</label>
                                            <input type="date" id="fxFechaNueva" name="fecha_nueva" class="form-control @error('fecha_nueva') is-invalid @enderror" min="{{ now()->toDateString() }}" value="{{ old('fecha_nueva') }}"></div>
                                        <div class="fx-c6"><label for="fxHoraNueva">Horario</label>
                                            <select id="fxHoraNueva" name="hora_nueva" class="form-select @error('hora_nueva') is-invalid @enderror" data-anterior="{{ old('hora_nueva') }}">
                                                <option value="">Elige primero la fecha</option>
                                            </select>
                                            <div class="fx-hora-ayuda" id="fxHoraAyuda"></div></div>
                                    </div>
                                @endif
                                <label class="fx-opcion"><input type="radio" name="siguiente" value="terminar" @checked($sigue === 'terminar')>
                                    <span><b>Sólo terminar la atención</b><small>{{ $sol ? 'Ya tiene solicitud: sigue su trámite normal y deja de estar marcada como excepción.' : 'No se agenda nada; si la cita seguía en espera, queda como atendida.' }}</small></span></label>
                            </div>
                        </div>

                        {{-- 2 y 3 --}}
                        <div class="tn-tarjeta fx-seccion fx-solo-exc">
                            <div class="fx-campos">
                                <div class="fx-c6">
                                    <h4 style="font-size:15px;font-weight:800;margin:0 0 12px"><span class="fx-num" style="display:inline-grid;place-items:center;width:24px;height:24px;border-radius:8px;background:#E7EFEF;color:#496163;font-size:12.5px">2</span> Frecuencia de los hechos</h4>
                                    <div class="fx-opciones @error('frecuencia') border border-danger rounded p-2 @enderror">
                                        @foreach ($CX::FRECUENCIAS as $valor => $texto)
                                            <label class="fx-opcion"><input type="radio" name="frecuencia" value="{{ $valor }}" @checked($v('frecuencia') === $valor)> {{ $texto }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="fx-c6">
                                    <h4 style="font-size:15px;font-weight:800;margin:0 0 12px"><span class="fx-num" style="display:inline-grid;place-items:center;width:24px;height:24px;border-radius:8px;background:#E7EFEF;color:#496163;font-size:12.5px">3</span> Cambios en su situación laboral</h4>
                                    <div class="fx-opciones">
                                        @foreach ($CX::SITUACIONES as $valor => $texto)
                                            <label class="fx-opcion"><input type="radio" name="situacion_laboral" value="{{ $valor }}" data-situacion @checked($v('situacion_laboral') === $valor)> {{ $texto }}</label>
                                        @endforeach
                                        <div class="fx-extra" data-de="situacion_otro" style="margin-left:0"><input type="text" name="situacion_otro" class="form-control" placeholder="¿Cuál?" value="{{ $v('situacion_otro') }}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4, 5 y 6 --}}
                        <div class="tn-tarjeta fx-seccion fx-solo-exc">
                            <div class="fx-campos">
                                <div class="fx-c12">
                                    <label for="fxComunico"><span class="fx-num">4.</span> ¿La persona afectada comunicó los hechos a alguien más de su área de trabajo? Describa a quién o a quiénes</label>
                                    <textarea id="fxComunico" name="descripcion_persona" class="form-control" rows="2">{{ $v('descripcion_persona') }}</textarea>
                                </div>
                                <div class="fx-c12">
                                    <label for="fxConductas"><span class="fx-num">5.</span> Descripción de las conductas manifestadas</label>
                                    <textarea id="fxConductas" name="descripcion_conductas" class="form-control" rows="5">{{ $v('descripcion_conductas') }}</textarea>
                                </div>
                                <div class="fx-c12">
                                    <label for="fxObs"><span class="fx-num">6.</span> Observaciones y comentarios</label>
                                    <textarea id="fxObs" name="observaciones" class="form-control" rows="3">{{ $v('observaciones') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Decisión --}}
                        <div class="tn-tarjeta fx-seccion fx-solo-exc">
                            <h4><i class="bi bi-signpost-split"></i> ¿La persona desea continuar con el procedimiento de conciliación?</h4>
                            <p class="fx-nota">Por estar en un supuesto del art. 685 Ter LFT, la persona no está obligada a agotar la instancia conciliatoria. Si decide no continuar, se le brinda asesoría y se canaliza.</p>
                            <div class="fx-decision @error('resultado') border border-danger rounded p-2 @enderror">
                                <label class="fx-opcion"><input type="radio" name="resultado" value="continua" @checked($v('resultado') === 'continua')>
                                    <span><b>Sí, continúa</b><small>Sigue con la conciliación en el Centro.</small></span></label>
                                <label class="fx-opcion"><input type="radio" name="resultado" value="canaliza" @checked(in_array($v('resultado'), ['canaliza', 'incompetente'], true))>
                                    <span><b>No, se canaliza</b><small>Se envía a la institución que corresponda.</small></span></label>
                            </div>

                            @php
                                // "6. No somos competentes": se guarda como resultado 'incompetente'.
                                $noCompetente = old('no_competente') !== null ? old('no_competente') === '1' : ($datos['resultado'] ?? null) === 'incompetente';
                            @endphp
                            <div id="fxCanaliza" class="mt-3" @if (! $v('resultado')) hidden @endif>
                                <span class="fx-etq" id="fxCanalizaEtq" data-canaliza="Canalizar a (al menos una) o marcar que no somos competentes" data-continua="¿Además se canaliza para atención integral? (opcional)">{{ $v('resultado') === 'continua' ? '¿Además se canaliza para atención integral? (opcional)' : 'Canalizar a (al menos una) o marcar que no somos competentes' }}</span>
                                <div class="fx-opciones @error('instituciones') border border-danger rounded p-2 @enderror">
                                    @foreach ($CX::INSTITUCIONES as $clave => $nombre)
                                        <label class="fx-opcion"><input type="checkbox" name="instituciones[]" value="{{ $clave }}" data-institucion="{{ $clave }}" @checked(in_array($clave, $arr('instituciones'), true))> {{ $loop->iteration }}. {{ $nombre }}</label>
                                        @if ($clave === 'procuraduria')
                                            <div class="fx-extra" data-de="procuraduria">
                                                <label for="fxDomicilio" class="fx-etq">Domicilio de la Procuraduría ("ubicado en…")</label>
                                                <input type="text" id="fxDomicilio" name="domicilio_procuraduria" class="form-control" value="{{ $v('domicilio_procuraduria') }}">
                                                <div class="fx-ayuda">Se propone el último que se usó en {{ $sede ?: 'esta sede' }}.</div>
                                            </div>
                                        @endif
                                    @endforeach
                                    <div id="fxNoCompetente" @if ($v('resultado') === 'continua') hidden @endif>
                                        <input type="hidden" name="no_competente" value="0">
                                        <label class="fx-opcion fx-opcion--incompetente"><input type="checkbox" name="no_competente" value="1" id="fxNoCompetenteCaja" @checked($noCompetente)>
                                            {{ count($CX::INSTITUCIONES) + 1 }}. No somos competentes</label>
                                    </div>
                                </div>
                            </div>

                            @if ($puedeCerrar)
                                <div id="fxCerrar" class="mt-3" @if (! in_array($v('resultado'), ['canaliza', 'incompetente'], true)) hidden @endif>
                                    <input type="hidden" name="cerrar_solicitud" value="0">
                                    <label class="fx-opcion">
                                        <input type="checkbox" name="cerrar_solicitud" value="1" @checked(old('cerrar_solicitud', '1') === '1')>
                                        <span><b>Cerrar la solicitud {{ $sol->NUE ?: 'sin expediente' }} como «<span id="fxCerrarComo">{{ $noCompetente ? 'Incompetencia' : 'Canalizada' }}</span>»</b>
                                            <small>Termina el trámite de conciliación; sus audiencias pendientes quedan así y liberan el horario. Se aplica al terminar la atención.</small></span>
                                    </label>
                                </div>
                            @elseif ($sol && in_array($sol->estatus, ['Canalizada', 'Incompetencia'], true))
                                <p class="fx-nota mt-3 mb-0"><i class="bi bi-check2-circle"></i> La solicitud {{ $sol->NUE }} ya está cerrada como «{{ $sol->estatus }}».</p>
                            @endif
                        </div>

                        <div class="fx-barra">
                            <span class="fx-barra__nota"><i class="bi bi-info-circle"></i> Puedes guardar a medias y terminar después.</span>
                            <button type="submit" name="accion" value="borrador" class="btn btn-outline-secondary" @disabled(! $migrado)><i class="bi bi-save"></i> Guardar borrador</button>
                            <button type="submit" name="accion" value="pdf" formtarget="_blank" class="btn btn-outline-secondary" @disabled(! $migrado)><i class="bi bi-printer"></i> Guardar y descargar PDF</button>
                            <button type="submit" name="accion" value="terminar" class="btn btn-primary" @disabled(! $migrado || ($terminado && ($caso->resultado ?? null) === 'no_excepcion'))><i class="bi bi-check2-circle"></i> {{ $terminado ? 'Guardar cambios' : 'Terminar atención' }}</button>
                        </div>
                    </div>

                    {{-- Contexto del caso --}}
                    <aside class="fx-lado">
                        <div class="tn-tarjeta">
                            <h4 style="font-size:14px;font-weight:800;margin:0 0 12px;color:var(--tn-tinta)"><i class="bi bi-info-square"></i> Del sistema</h4>
                            <dl>
                                @if ($cita)
                                    <div><dt>Cita</dt><dd>{{ \App\Support\FolioCita::mostrar($cita) }} · {{ $cita->origen === 'linea' ? 'en línea' : 'ventanilla' }}</dd></div>
                                    <div><dt>Fecha</dt><dd>{{ \Illuminate\Support\Carbon::parse($cita->fecha)->locale('es')->isoFormat('ddd D MMM YYYY') }}, {{ substr((string) $cita->hora, 0, 5) }} h</dd></div>
                                    <div><dt>Trámite</dt><dd>{{ $cita->tipo }}</dd></div>
                                    <div><dt>Estatus de la cita</dt><dd>{{ $estatusCita[$cita->estatus] ?? $cita->estatus }}</dd></div>
                                    <div><dt>Caso declarado</dt><dd>{{ $cita->tipo_caso ?: 'Sin especificar' }}</dd></div>
                                @endif
                                <div><dt>Sede</dt><dd>{{ $sede ?: '—' }}</dd></div>
                                @if ($sol)
                                    <div><dt>Solicitud</dt><dd><a href="{{ route('excepciones.solicitud', $sol->id) }}" target="_blank" rel="noopener">{{ $sol->NUE ?: 'Sin expediente' }} <i class="bi bi-box-arrow-up-right" style="font-size:11px"></i></a></dd></div>
                                @endif
                                @if ($caso && ($caso->updated_at ?? null))
                                    <div><dt>Última vez guardado</dt><dd>{{ \Illuminate\Support\Carbon::parse($caso->updated_at)->locale('es')->isoFormat('D MMM YYYY, HH:mm') }}</dd></div>
                                @endif
                            </dl>
                        </div>
                    </aside>
                </div>
            </form>

            {{-- Canalización: por institución marcada, aceptación (la firma la persona) y oficio. --}}
            @if ($caso && ! empty($datos['instituciones']))
                @php $conAceptacion = \App\Support\OficioCanalizacion::conAceptacion(); @endphp
                <div class="tn-tarjeta fx-seccion mt-3" id="oficios">
                    <h4><i class="bi bi-envelope-paper"></i> Canalización: aceptación y oficios</h4>
                    @if (! \App\Support\OficioCanalizacion::migrado())
                        <div class="alert alert-warning mb-0">Falta correr la migración de canalización (<code>php artisan migrate</code>).</div>
                    @else
                        <p class="fx-nota">Por cada institución: primero la persona firma la <b>aceptación de canalización</b>; después se genera el <b>oficio</b>, con el formato 685 Ter como anexo. Si cambias las instituciones de arriba, guarda el formato para que se actualice esta lista.</p>
                        @unless ($conAceptacion)
                            <div class="alert alert-warning">Falta correr la migración de la aceptación (<code>php artisan migrate</code>); por ahora sólo está el oficio.</div>
                        @endunless
                        <div class="fx-oficios">
                            @foreach ($oficios as $inst => $o)
                                <form class="fx-oficio" method="POST" action="{{ route('excepciones.oficio.guardar', ['tipo' => $origen['tipo'], 'id' => $origen['id'], 'institucion' => $inst]) }}">
                                    @csrf
                                    <div class="fx-oficio__cabeza">
                                        <b>{{ $CX::INSTITUCIONES[$inst] ?? $inst }}</b>
                                        <span class="d-flex flex-wrap gap-1">
                                            @if ($conAceptacion)
                                                @if ($o['aceptacion_firmada'])
                                                    <span class="fx-badge fx-badge--continua"><i class="bi bi-pen"></i> Aceptación firmada</span>
                                                @else
                                                    <span class="fx-badge fx-badge--borrador"><i class="bi bi-exclamation-circle"></i> Falta firma de aceptación</span>
                                                @endif
                                            @endif
                                            @if ($o['conOficio'])
                                                <span class="fx-badge fx-badge--continua"><i class="bi bi-check2"></i> {{ $o['numero'] }}</span>
                                            @else
                                                <span class="fx-badge fx-badge--nuevo">Oficio sin generar</span>
                                            @endif
                                        </span>
                                    </div>

                                    @if ($conAceptacion)
                                        <div class="fx-paso"><span class="fx-num">1</span> Aceptación de canalización <small>la firma la persona usuaria</small></div>
                                        <div class="fx-campos">
                                            <div class="fx-c6"><label>Área o servicio al que se canaliza</label><input type="text" name="area_servicio" class="form-control" value="{{ $o['area_servicio'] }}" placeholder="Ej. asesoría jurídica, atención psicológica"></div>
                                            <div class="fx-c6"><label>Cargo de quien brinda la atención</label><input type="text" name="cargo_atiende" class="form-control" value="{{ $o['cargo_atiende'] }}"></div>
                                            <div class="fx-c12"><label>Motivo general de la canalización</label><input type="text" name="motivo" class="form-control" value="{{ $o['motivo'] }}" maxlength="500"></div>
                                            <div class="fx-c12 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <input type="hidden" name="aceptacion_firmada" value="0">
                                                <label class="fx-opcion mb-0" style="flex: 1 1 320px"><input type="checkbox" name="aceptacion_firmada" value="1" data-firmada @checked($o['aceptacion_firmada'])> <span><b>Aceptación firmada</b> <small class="d-block" style="color:var(--tn-tenue)">La persona ya firmó el formato de aceptación.</small></span></label>
                                                <button type="submit" name="accion" value="aceptacion" formtarget="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Imprimir aceptación</button>
                                            </div>
                                        </div>
                                        <div class="fx-paso mt-3"><span class="fx-num">2</span> Oficio a la institución <small>con el formato 685 Ter como anexo</small></div>
                                    @endif

                                    <div class="fx-campos">
                                        <div class="fx-c4"><label>Número de oficio</label><input type="text" name="numero" class="form-control" value="{{ $o['numero'] }}" placeholder="{{ $o['numeroPropuesto'] }}" maxlength="40">
                                            @unless ($o['conOficio'])<div class="fx-ayuda">Se aparta al generar el oficio.</div>@endunless</div>
                                        <div class="fx-c4"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ \Illuminate\Support\Carbon::parse($o['fecha'])->toDateString() }}" required></div>
                                        <div class="fx-c4"><label>Lugar</label><input type="text" name="lugar" class="form-control" value="{{ $o['lugar'] }}" maxlength="80"></div>
                                        <div class="fx-c6"><label>Titular de la institución</label><input type="text" name="destinatario" class="form-control" value="{{ $o['destinatario'] }}" placeholder="Nombre de la persona titular"></div>
                                        <div class="fx-c6"><label>Cargo</label><input type="text" name="cargo" class="form-control" value="{{ $o['cargo'] }}" placeholder="Cargo"></div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 justify-content-end mt-2">
                                        <button type="submit" name="accion" value="guardar" class="btn btn-outline-secondary btn-sm"><i class="bi bi-save"></i> Guardar</button>
                                        @if ($o['conOficio'])
                                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('excepciones.oficio.pdf', ['tipo' => $origen['tipo'], 'id' => $origen['id'], 'institucion' => $inst]) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i> Ver oficio</a>
                                        @endif
                                        <button type="submit" name="accion" value="oficio" formtarget="_blank" class="btn btn-primary btn-sm" @if ($conAceptacion) data-oficio @endif>
                                            <i class="bi bi-printer"></i> {{ $o['conOficio'] ? 'Guardar y ver oficio' : 'Generar oficio' }}
                                        </button>
                                    </div>
                                </form>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    <script>
    (function () {
        var form = document.getElementById('fxForm');
        function marcado(sel) { var el = form.querySelector(sel); return !!(el && el.checked); }
        function mostrar(clave, si) { form.querySelectorAll('[data-de="' + clave + '"]').forEach(function (el) { el.hidden = !si; }); }
        function refrescar() {
            mostrar('discriminacion', marcado('[data-tipo="discriminacion"]'));
            var motivoDisc = document.getElementById('fxMotivoDisc');
            mostrar('motivo_otro', !!motivoDisc && motivoDisc.value === 'otro');
            mostrar('riesgo_trabajo', marcado('[data-tipo="riesgo_trabajo"]'));
            mostrar('situacion_otro', marcado('input[name="situacion_laboral"][value="otro"]'));
            // "No es caso de excepción": se apaga el resto del formato y se pregunta qué sigue.
            var noExc = marcado('#fxNoExc');
            form.querySelectorAll('input[name="tipos[]"]').forEach(function (c) {
                if (noExc) { c.checked = false; }
                c.disabled = noExc;
                c.closest('.fx-opcion').style.opacity = noExc ? '.5' : '';
            });
            if (noExc) { mostrar('discriminacion', false); mostrar('riesgo_trabajo', false); }
            form.querySelectorAll('.fx-solo-exc').forEach(function (s) { s.hidden = noExc; });
            document.getElementById('fxNoExcPanel').hidden = !noExc;
            mostrar('otra_fecha', marcado('input[name="siguiente"][value="otra_fecha"]'));

            var canaliza = marcado('input[name="resultado"][value="canaliza"]');
            var continua = marcado('input[name="resultado"][value="continua"]');
            // "No somos competentes" sólo con "No, se canaliza", y excluye a las instituciones.
            var noComp = document.getElementById('fxNoCompetenteCaja');
            document.getElementById('fxNoCompetente').hidden = !canaliza;
            if (noComp && !canaliza) { noComp.checked = false; }
            var sinInstitucion = !!(noComp && noComp.checked);
            form.querySelectorAll('input[name="instituciones[]"]').forEach(function (c) {
                if (sinInstitucion) { c.checked = false; }
                c.disabled = sinInstitucion;
                c.closest('.fx-opcion').style.opacity = sinInstitucion ? '.5' : '';
            });
            mostrar('procuraduria', marcado('[data-institucion="procuraduria"]'));
            document.getElementById('fxCanaliza').hidden = !(canaliza || continua);
            var etq = document.getElementById('fxCanalizaEtq');
            etq.textContent = continua ? etq.dataset.continua : etq.dataset.canaliza;
            var cerrar = document.getElementById('fxCerrar');
            if (cerrar) { cerrar.hidden = !canaliza; }
            var como = document.getElementById('fxCerrarComo');
            if (como) { como.textContent = sinInstitucion ? 'Incompetencia' : 'Canalizada'; }
            document.getElementById('fxGeneroOtro').hidden = !marcado('input[name="genero"][value="Otro"]');
            document.getElementById('fxJefe').hidden = !marcado('input[name="conoce_jefe"][value="1"]');
        }
        form.addEventListener('change', refrescar);
        refrescar();

        // Otra fecha: horarios libres de ese día (mismo calendario que la ventanilla).
        var caja = document.getElementById('fxOtraFecha');
        if (caja) {
            var fecha = document.getElementById('fxFechaNueva');
            var hora = document.getElementById('fxHoraNueva');
            var ayuda = document.getElementById('fxHoraAyuda');
            var pedido = 0;
            function horarios() {
                var este = ++pedido;
                hora.innerHTML = '<option value="">' + (fecha.value ? 'Buscando horarios…' : 'Elige primero la fecha') + '</option>';
                ayuda.textContent = '';
                if (!fecha.value) { return; }
                var q = new URLSearchParams({ sede: caja.dataset.sede, tipo: caja.dataset.tipo, excepcion: 'No', start: fecha.value, end: fecha.value });
                fetch(caja.dataset.url + '?' + q.toString(), { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
                    .then(function (eventos) {
                        if (este !== pedido) { return; }
                        var libres = (eventos || []).filter(function (e) {
                            var est = e.extendedProps && e.extendedProps.estado;
                            return (est === 'disponible' || est === 'turnos') && e.start.slice(0, 10) === fecha.value;
                        });
                        if (!libres.length) {
                            hora.innerHTML = '<option value="">Sin horarios libres ese día</option>';
                            ayuda.textContent = 'Prueba con otra fecha (los fines de semana y días inhábiles no tienen horario).';
                            return;
                        }
                        var antes = hora.dataset.anterior || '';
                        hora.innerHTML = '<option value="">Elige un horario</option>' + libres.map(function (e) {
                            var h = e.start.slice(11, 16);
                            return '<option value="' + h + '"' + (antes.slice(0, 5) === h ? ' selected' : '') + '>' + h + (e.extendedProps.estado === 'turnos' ? ' · con cupo' : '') + '</option>';
                        }).join('');
                        ayuda.textContent = libres.length + (libres.length === 1 ? ' horario libre' : ' horarios libres');
                    })
                    .catch(function () { if (este === pedido) { hora.innerHTML = '<option value="">No se pudieron cargar los horarios</option>'; } });
            }
            fecha.addEventListener('change', horarios);
            if (fecha.value) { horarios(); }
        }

        // Generar el oficio sin la aceptación firmada: se avisa, no se impide.
        document.querySelectorAll('.fx-oficio [data-oficio]').forEach(function (boton) {
            boton.addEventListener('click', function (e) {
                var firmada = boton.form.querySelector('[data-firmada]');
                if (firmada && !firmada.checked && !window.confirm('La persona todavía no firma la aceptación de canalización. ¿Generar el oficio de todos modos?')) {
                    e.preventDefault();
                }
            });
        });
    })();
    </script>
@endsection
