{{--
    /atencion-tramite-conciliacion (AtencionTramiteController): primero se
    escribe el folio de la solicitud; si la audiencia ya tiene resolución se
    muestran las preguntas (App\Support\AtencionTramite); si no, cuándo es.
--}}
@extends('atencion.layout')
@section('titulo', 'Encuesta de atención del trámite')

@php
    use App\Support\AtencionTramite as AT;
    $abierto = $estado === 'abierto';
@endphp

@section('estilos')
    <style>
        .at-folio { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
        .at-folio .form-control { font-size: 18px; letter-spacing: .5px; text-transform: uppercase; max-width: 340px; }
        .at-datos { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 8px 18px; }
        .at-datos dt { font-size: 12px; color: var(--suave); font-weight: 700; margin: 0; }
        .at-datos dd { margin: 0; font-weight: 600; }
        .at-pregunta { border: 1px solid var(--borde); border-radius: 14px; padding: 16px 18px; margin: 0 0 14px; background: #fff; }
        .at-pregunta.is-invalida { border-color: #E2A190; box-shadow: 0 0 0 3px rgba(226, 161, 144, .2); }
        .at-pregunta legend { float: left; width: 100%; font-size: 16px; font-weight: 700; line-height: 1.35; margin: 0 0 12px; padding: 0; display: flex; gap: 10px; }
        .at-pregunta legend + * { clear: both; }
        .at-num { flex: 0 0 auto; width: 28px; height: 28px; border-radius: 50%; background: var(--verde); color: #fff; display: grid; place-items: center; font-size: 14px; }
        .at-opciones { display: flex; flex-wrap: wrap; gap: 8px; }
        .at-opcion { position: relative; }
        .at-opcion input { position: absolute; opacity: 0; inset: 0; pointer-events: none; }
        .at-opcion label { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1.5px solid var(--borde); border-radius: 12px; cursor: pointer; font-weight: 600; background: #fff; transition: background .12s, border-color .12s; }
        .at-opcion label::before { content: ''; width: 16px; height: 16px; border-radius: 50%; border: 2px solid var(--verde-claro); flex: 0 0 auto; }
        .at-opcion input:checked + label { border-color: var(--verde); background: #EEF4F4; color: var(--verde-osc); }
        .at-opcion input:checked + label::before { border-color: var(--verde); background: radial-gradient(var(--verde) 45%, transparent 50%); }
        .at-opcion input:focus-visible + label { outline: 3px solid rgba(73, 97, 99, .35); outline-offset: 2px; }
        .at-opcion input:disabled + label { opacity: .5; cursor: not-allowed; }
        @media (max-width: 576px) { .at-opciones { flex-direction: column; } .at-opcion label { width: 100%; } }
        .at-comentario { margin-top: 12px; }
        .at-comentario textarea { font-size: 14.5px; }
        .at-comentario label { font-size: 13px; color: var(--suave); font-weight: 600; margin-bottom: 4px; }
        .at-error { color: #9A3A22; font-size: 13.5px; margin-top: 8px; font-weight: 600; }
        .at-parte label { padding: 14px 18px; font-size: 15.5px; }
        .at-hecho { font-size: 12px; font-weight: 700; color: #1E7B3C; margin-left: 4px; }
        .at-ejemplo { border: 1px solid var(--borde); border-radius: 12px; background: #fff; margin: 0 0 16px; }
        .at-ejemplo > summary { cursor: pointer; padding: 10px 14px; font-weight: 700; color: var(--verde); list-style: none; }
        .at-ejemplo > summary::-webkit-details-marker { display: none; }
        .at-ejemplo > summary::after { content: ' ▾'; }
        .at-ejemplo[open] > summary::after { content: ' ▴'; }
        .at-ejemplo__cuerpo { padding: 0 14px 14px; font-size: 14.5px; }
        .at-doc { position: relative; max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #D9D9D9; border-radius: 4px; box-shadow: 0 6px 18px rgba(0,0,0,.08); font-family: Arial, Helvetica, sans-serif; color: #222; overflow: hidden; }
        .at-doc__cinta { display: flex; justify-content: space-between; align-items: center; background: #5E5E5E; color: #fff; padding: 6px 12px; }
        .at-doc__logo { font-size: 15px; letter-spacing: .5px; } .at-doc__logo b { font-weight: 800; }
        .at-doc__logo--der { font-size: 11px; font-weight: 700; opacity: .9; }
        .at-doc__arriba { display: flex; justify-content: flex-end; align-items: center; gap: 10px; padding: 14px 16px 6px; }
        .at-doc__datos { display: grid; grid-template-columns: auto auto; gap: 2px 14px; margin: 0; padding: 8px 14px; border: 3px solid #2FD39A; border-radius: 40px; font-size: 12.5px; }
        .at-doc__datos dt { font-weight: 700; margin: 0; } .at-doc__datos dd { margin: 0; }
        .at-doc__folio { font-weight: 800; background: #FFF3B0; padding: 0 4px; border-radius: 3px; }
        .at-doc__marca { font-size: 13px; font-weight: 800; color: #1E7B5A; white-space: nowrap; }
        .at-doc__titulo { text-align: center; font-size: 11.5px; font-weight: 700; padding: 6px 12px 10px; }
        .at-doc__texto { padding: 0 16px 14px; font-size: 11.5px; line-height: 1.5; }
        .at-doc__texto p { margin: 0 0 2px; }
        .at-doc__parrafo { margin-top: 8px !important; color: #444; }
        .at-doc__fecha { font-weight: 700; border-bottom: 2px solid #E85A9B; }
        .at-doc__tapa { display: inline-block; width: 150px; height: 9px; background: #E3E3E3; border-radius: 2px; vertical-align: middle; }
        .at-doc__tapa--corta { width: 110px; }
        @media (max-width: 576px) { .at-doc__arriba { flex-direction: column; align-items: flex-end; } .at-doc__datos { font-size: 11.5px; } }
        .at-privacidad { background: var(--fondo); border: 1px solid var(--borde); border-radius: 12px; padding: 12px 14px; font-size: 14px; }
        .at-privacidad .form-check-input { width: 1.2em; height: 1.2em; }
    </style>
@endsection

@section('contenido')
<div class="at-tarjeta">
    <div class="at-cabeza">
        <h1><i class="bi bi-chat-square-heart me-2" aria-hidden="true"></i>Encuesta de atención del trámite del procedimiento de conciliación laboral</h1>
        <p>Cuéntanos cómo te atendimos. Tu opinión es importante para mejorar el servicio del Centro. Son 7 preguntas y te toma unos 3 minutos.</p>
    </div>
    <div class="at-cuerpo">
        @if ($noDisponible)
            <div class="at-aviso at-aviso--alerta">El formulario todavía no está disponible. Intenta más tarde.</div>
        @else
            @if (session('error'))
                <div class="at-aviso at-aviso--error mb-3"><i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}</div>
            @endif

            {{-- 1. Folio --}}
            <form method="GET" action="{{ route('atencion.formulario') }}" class="mb-3">
                <label for="atFolio" class="form-label fw-bold">Folio de tu solicitud <span class="fw-normal" style="color: var(--suave);">(Número de identificación único)</span></label>
                <div class="at-folio">
                    <input type="text" id="atFolio" name="folio" class="form-control" value="{{ $folio }}" placeholder="Ej. MOR/SOL/2026/04512" autocomplete="off" required>
                    <button type="submit" class="btn btn-verde"><i class="bi bi-search me-1"></i> {{ $general ? 'Buscar otro folio' : 'Continuar' }}</button>
                </div>
                <div class="form-text">Lo encuentras en tu citatorio.</div>
            </form>
            @include('atencion._ejemplo_folio', ['abierto' => ! $general])

            @if ($noEncontrado)
                <div class="at-aviso at-aviso--error"><i class="bi bi-search me-1"></i> No encontramos el folio <b>{{ $folio }}</b>. Revisa que esté bien escrito, por ejemplo <b>MOR/SOL/2026/04512</b>.</div>
            @elseif ($general)
                <div class="at-aviso at-aviso--info mb-3">
                    <dl class="at-datos mb-0">
                        <div><dt>Folio</dt><dd>{{ $resumen['folio'] }}</dd></div>
                        <div><dt>Sede</dt><dd>{{ $resumen['sede'] }}</dd></div>
                        <div><dt>Fecha de la solicitud</dt><dd>{{ $resumen['fecha'] }}</dd></div>
                        @if ($resumen['audiencia'])
                            <div>
                                <dt>{{ $resumen['audiencia']['resolucion'] ? 'Audiencia' : 'Tu audiencia' }}</dt>
                                <dd>{{ \Illuminate\Support\Str::ucfirst($resumen['audiencia']['fecha'] ?? 'Sin fecha') }}{{ $resumen['audiencia']['hora'] ? ' · '.$resumen['audiencia']['hora'].' h' : '' }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if ($estado === 'sin_resolucion')
                    @php $aud = $resumen['audiencia']; @endphp
                    <div class="at-aviso at-aviso--alerta">
                        <i class="bi bi-hourglass-split me-1"></i>
                        @if ($aud && $aud['fecha'] && ! $aud['pasada'])
                            Tu audiencia de conciliación es el <b>{{ $aud['fecha'] }}{{ $aud['hora'] ? ' a las '.$aud['hora'].' h' : '' }}</b>{{ $aud['sala'] ? ', en '.$aud['sala'] : '' }} ({{ $aud['sede'] }}).
                            Podrás contestar este formulario cuando tu audiencia tenga resolución; también te enviaremos la liga por correo.
                        @elseif ($aud && $aud['fecha'])
                            Tu audiencia de conciliación fue el <b>{{ $aud['fecha'] }}</b> y todavía no se registra su resolución. Podrás contestar en cuanto la tenga; también te enviaremos la liga por correo.
                        @else
                            Todavía no hay fecha para tu audiencia de conciliación. Podrás contestar este formulario cuando tu audiencia tenga resolución; también te enviaremos la liga por correo.
                        @endif
                    </div>
                @elseif ($estado === 'contestado')
                    <div class="at-aviso at-aviso--ok"><i class="bi bi-check2-circle me-1"></i> Este formulario ya lo contestaron la parte trabajadora y la parte empleadora. ¡Gracias por tu opinión!</div>
                @else
                    {{-- 2. Preguntas --}}
                    @if ($errors->any())
                        <div class="at-aviso at-aviso--error mb-3" role="alert">
                            <b>Revisa lo siguiente:</b>
                            <ul class="mb-0 mt-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('atencion.guardar') }}" id="atForm" novalidate>
                        @csrf
                        <input type="hidden" name="folio" value="{{ $resumen['folio'] }}">

                        <fieldset class="at-pregunta at-parte {{ $errors->has('parte') ? 'is-invalida' : '' }}">
                            <legend><span class="at-num"><i class="bi bi-person"></i></span> ¿Qué parte eres en este trámite?</legend>
                            <div class="at-opciones">
                                @foreach (AT::PARTES as $clave => $texto)
                                    @php $hecho = in_array($clave, $contestadas, true); @endphp
                                    <div class="at-opcion">
                                        <input type="radio" name="parte" id="parte_{{ $clave }}" value="{{ $clave }}" required
                                               @checked(old('parte', $parte) === $clave) @disabled($hecho)>
                                        <label for="parte_{{ $clave }}">{{ $texto }} @if ($hecho)<span class="at-hecho">· ya contestó</span>@endif</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('parte')<div class="at-error">{{ $message }}</div>@enderror
                        </fieldset>

                        @foreach (AT::PREGUNTAS as $n => $p)
                            <fieldset class="at-pregunta {{ $errors->has('p'.$n) || $errors->has('c'.$n) ? 'is-invalida' : '' }}" id="pregunta{{ $n }}"
                                      @isset($p['solo_si']) data-solo-si="p{{ $p['solo_si'][0] }}={{ $p['solo_si'][1] }}" @endisset>
                                <legend><span class="at-num">{{ $n }}</span> <span>{{ $p['texto'] }}</span></legend>
                                <div class="at-opciones">
                                    @foreach ($p['opciones'] as $valor => $texto)
                                        <div class="at-opcion">
                                            <input type="radio" name="p{{ $n }}" id="p{{ $n }}_{{ $valor }}" value="{{ $valor }}" required @checked(old('p'.$n) === $valor)>
                                            <label for="p{{ $n }}_{{ $valor }}">{{ $texto }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('p'.$n)<div class="at-error">{{ $message }}</div>@enderror
                                <div class="at-comentario">
                                    <label for="c{{ $n }}">
                                        @isset($p['obligatorio_si'])
                                            Comentarios <span data-ra-oblig="{{ $n }}">(si contestas «{{ $p['opciones'][$p['obligatorio_si']] }}», cuéntanos qué pasó)</span>
                                        @else
                                            Comentarios (opcional)
                                        @endisset
                                    </label>
                                    <textarea class="form-control" id="c{{ $n }}" name="c{{ $n }}" rows="2" maxlength="1000">{{ old('c'.$n) }}</textarea>
                                    @error('c'.$n)<div class="at-error">{{ $message }}</div>@enderror
                                </div>
                            </fieldset>
                        @endforeach

                        <div class="at-privacidad mb-3 {{ $errors->has('acepta_privacidad') ? 'border-danger' : '' }}">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" id="atPrivacidad" name="acepta_privacidad" value="1" required @checked(old('acepta_privacidad'))>
                                <label class="form-check-label" for="atPrivacidad">
                                    He leído el <a href="#" data-bs-toggle="modal" data-bs-target="#atAvisoPrivacidad">Aviso de privacidad</a> y acepto que mis respuestas se guarden junto con el folio de mi solicitud.
                                </label>
                            </div>
                            @error('acepta_privacidad')<div class="at-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-verde btn-lg px-5" id="atEnviar"><i class="bi bi-send me-1"></i> Enviar mis respuestas</button>
                            <p class="form-text mt-2 mb-0">Cada parte puede contestar una sola vez. El trámite es gratuito.</p>
                        </div>
                    </form>
                @endif
            @endif
        @endif
    </div>
</div>

<div class="modal fade" id="atAvisoPrivacidad" tabindex="-1" aria-labelledby="atAvisoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="atAvisoTitulo"><i class="bi bi-shield-lock me-2"></i>Aviso de privacidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" style="font-size:14.5px">
                <p>Los datos personales recabados por el Centro de Conciliación Laboral del Estado de Michoacán de Ocampo serán tratados conforme a la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados y demás normativa aplicable.</p>
                <p>Las respuestas de este formulario se guardan junto con el folio de tu solicitud y se usan para <b>evaluar y mejorar la atención</b> que brinda el personal del Centro (Recepción, Auxiliares y Conciliadores) y para generar información estadística, en la que no es posible identificar a las personas. También se guarda de forma automática la dirección IP y el tipo de navegador desde el que contestas, sólo para la seguridad del sistema.</p>
                <p>Si en tus respuestas reportas una situación que requiere atención (por ejemplo, presión para firmar un convenio), el Centro podrá revisarla para darle seguimiento.</p>
                <p class="mb-0">No se realizarán transferencias de datos personales, salvo requerimiento fundado y motivado de autoridad competente. Puedes ejercer tus derechos de acceso, rectificación, cancelación y oposición (derechos ARCO) ante la Unidad de Transparencia del Centro.</p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-verde" data-bs-dismiss="modal">Entendido</button></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        (function () {
            var form = document.getElementById('atForm');
            if (!form) { return; }

            // La pregunta 7 sólo aplica si hubo convenio (pregunta 6).
            function condicionales() {
                form.querySelectorAll('[data-solo-si]').forEach(function (fs) {
                    var par = fs.getAttribute('data-solo-si').split('=');
                    var elegido = form.querySelector('input[name="' + par[0] + '"]:checked');
                    var aplica = !!elegido && elegido.value === par[1];
                    fs.hidden = !aplica;
                    fs.querySelectorAll('input, textarea').forEach(function (el) { el.disabled = !aplica; });
                });
                // Comentario obligatorio de la 7 si contestan "Sí".
                var p7 = form.querySelector('input[name="p7"]:checked');
                var c7 = document.getElementById('c7');
                if (c7) { c7.required = !!p7 && p7.value === 'si' && !c7.disabled; }
            }
            form.addEventListener('change', condicionales);
            condicionales();

            form.addEventListener('submit', function (e) {
                condicionales();
                form.querySelectorAll('.at-pregunta').forEach(function (fs) { fs.classList.remove('is-invalida'); });
                if (!form.checkValidity()) {
                    e.preventDefault();
                    var primero = form.querySelector(':invalid');
                    form.querySelectorAll(':invalid').forEach(function (el) {
                        var fs = el.closest('.at-pregunta');
                        if (fs) { fs.classList.add('is-invalida'); }
                    });
                    if (primero) {
                        (primero.closest('.at-pregunta') || primero).scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }
                var b = document.getElementById('atEnviar');
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando…';
            });
        })();
    </script>
@endsection
