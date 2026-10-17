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

                        {{-- 1. Tipo de situación --}}
                        <div class="tn-tarjeta fx-seccion">
                            <h4><span class="fx-num">1</span> Tipo de situación que enfrenta</h4>
                            <div class="fx-opciones @error('tipos') border border-danger rounded p-2 @enderror">
                                @foreach ($CX::TIPOS_FORMATO as $clave => $texto)
                                    <label class="fx-opcion"><input type="checkbox" name="tipos[]" value="{{ $clave }}" data-tipo="{{ $clave }}" @checked(in_array($clave, $arr('tipos'), true))> {{ $texto }}</label>
                                    @if ($clave === 'discriminacion')
                                        <div class="fx-extra" data-de="discriminacion"><input type="text" name="motivos" class="form-control" placeholder="Señale el motivo" value="{{ $v('motivos') }}"></div>
                                    @elseif ($clave === 'riesgo_trabajo')
                                        <div class="fx-extra fx-opciones fx-opciones--fila" data-de="riesgo_trabajo">
                                            <label class="fx-opcion"><input type="radio" name="riesgo_lugar" value="dentro" @checked($v('riesgo_lugar') === 'dentro')> Dentro del centro de trabajo</label>
                                            <label class="fx-opcion"><input type="radio" name="riesgo_lugar" value="fuera" @checked($v('riesgo_lugar') === 'fuera')> Fuera del centro de trabajo</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- 2 y 3 --}}
                        <div class="tn-tarjeta fx-seccion">
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
                        <div class="tn-tarjeta fx-seccion">
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
                        <div class="tn-tarjeta fx-seccion">
                            <h4><i class="bi bi-signpost-split"></i> ¿La persona desea continuar con el procedimiento de conciliación?</h4>
                            <p class="fx-nota">Por estar en un supuesto del art. 685 Ter LFT, la persona no está obligada a agotar la instancia conciliatoria. Si decide no continuar, se le brinda asesoría y se canaliza.</p>
                            <div class="fx-decision @error('resultado') border border-danger rounded p-2 @enderror">
                                <label class="fx-opcion"><input type="radio" name="resultado" value="continua" @checked($v('resultado') === 'continua')>
                                    <span><b>Sí, continúa</b><small>Sigue con la conciliación en el Centro.</small></span></label>
                                <label class="fx-opcion"><input type="radio" name="resultado" value="canaliza" @checked($v('resultado') === 'canaliza')>
                                    <span><b>No, se canaliza</b><small>Se envía a la institución que corresponda.</small></span></label>
                            </div>

                            <div id="fxCanaliza" class="mt-3" @if ($v('resultado') !== 'canaliza') hidden @endif>
                                <span class="fx-etq">Canalizar a</span>
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
                                </div>
                            </div>
                        </div>

                        <div class="fx-barra">
                            <span class="fx-barra__nota"><i class="bi bi-info-circle"></i> Puedes guardar a medias y terminar después.</span>
                            <button type="submit" name="accion" value="borrador" class="btn btn-outline-secondary" @disabled(! $migrado)><i class="bi bi-save"></i> Guardar borrador</button>
                            <button type="submit" name="accion" value="pdf" formtarget="_blank" class="btn btn-outline-secondary" @disabled(! $migrado)><i class="bi bi-printer"></i> Guardar y descargar PDF</button>
                            <button type="submit" name="accion" value="terminar" class="btn btn-primary" @disabled(! $migrado)><i class="bi bi-check2-circle"></i> {{ $terminado ? 'Guardar cambios' : 'Terminar atención' }}</button>
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
            mostrar('riesgo_trabajo', marcado('[data-tipo="riesgo_trabajo"]'));
            mostrar('situacion_otro', marcado('input[name="situacion_laboral"][value="otro"]'));
            mostrar('procuraduria', marcado('[data-institucion="procuraduria"]'));
            document.getElementById('fxCanaliza').hidden = !marcado('input[name="resultado"][value="canaliza"]');
            document.getElementById('fxGeneroOtro').hidden = !marcado('input[name="genero"][value="Otro"]');
            document.getElementById('fxJefe').hidden = !marcado('input[name="conoce_jefe"][value="1"]');
        }
        form.addEventListener('change', refrescar);
        refrescar();
    })();
    </script>
@endsection
