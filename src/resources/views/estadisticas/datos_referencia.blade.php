@extends('layouts.app')
@section('title', 'Datos de referencia')
{{--
    /estadisticas/datos-referencia (ReporteEstadisticoController::referencia,
    App\Support\DatosReferencia): lo que el Reporte estadístico necesita y no
    sale del sistema. Cada dato se guarda por separado. Estadística lo abre
    desde el menú; Super Usuario y Directivo, desde el Panel estadístico.
--}}
@php
    use App\Support\DatosReferencia as DR;

    // Números con separador de miles. Lo que no es número (p. ej. lo que se
    // escribió mal y regresó con error) se muestra tal cual.
    $mostrar = function ($v, string $tipo) {
        if ($v === null || $v === '') {
            return '';
        }
        $limpio = is_string($v) ? str_replace([',', ' ', '$'], '', $v) : $v;
        if (in_array($tipo, ['entero', 'moneda', 'decimal'], true) && ! is_numeric($limpio)) {
            return $v;
        }
        $v = $limpio;

        return match ($tipo) {
            'entero' => number_format((int) $v),
            'moneda', 'decimal' => number_format((float) $v, 2),
            default => $v,
        };
    };
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .dr-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .dr-migas:hover { color: var(--tn-verde); }
        .dr-indice { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
        .dr-indice a { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; }
        .dr-indice a:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .dr-grupo { margin: 22px 0 10px; display: flex; align-items: center; gap: 10px; }
        .dr-grupo__icono { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; background: #E7ECEC; color: var(--tn-verde); font-size: 16px; }
        .dr-grupo h2 { margin: 0; font-size: 15px; font-weight: 800; color: var(--tn-tinta); }
        .dr-grupo p { margin: 0; font-size: 12.5px; color: var(--tn-tenue); }
        .dr-tarjeta { margin-bottom: 14px; scroll-margin-top: 90px; }
        .dr-tarjeta:target { box-shadow: 0 0 0 2px var(--tn-dorado); }
        .dr-cabeza { display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 6px 12px; margin-bottom: 12px; }
        .dr-titulo { margin: 0; font-size: 14px; font-weight: 800; color: var(--tn-tinta); }
        .dr-ayuda { font-size: 12.5px; color: var(--tn-suave); margin: 2px 0 0; max-width: 760px; }
        .dr-editado { font-size: 11.5px; color: var(--tn-tenue); white-space: nowrap; }
        .dr-campos { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px 14px; }
        .dr-campos--largo { grid-template-columns: 1fr; }
        .dr-campos label { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .02em; color: var(--tn-suave); margin-bottom: 4px; }
        .dr-tabla { width: 100%; border-collapse: separate; border-spacing: 0; }
        .dr-tabla th { font-size: 11.5px; font-weight: 700; color: var(--tn-suave); padding: 0 6px 6px; white-space: nowrap; }
        .dr-tabla td { padding: 3px 4px; vertical-align: middle; }
        .dr-tabla td.dr-fijo { font-weight: 700; color: var(--tn-tinta); padding-left: 6px; white-space: nowrap; }
        .dr-tabla .form-control, .dr-tabla .form-select { min-width: 92px; padding: 4px 8px; font-size: 13px; }
        .dr-tabla .dr-num { text-align: right; font-variant-numeric: tabular-nums; }
        .dr-quitar { border: 0; background: none; color: var(--tn-tenue); padding: 4px 6px; border-radius: 6px; }
        .dr-quitar:hover { color: #A3162E; background: #FBE4E6; }
        .dr-scroll { overflow-x: auto; }
        .dr-pie { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
        .dr-agregar { border: 1px dashed var(--tn-verde-claro); background: #fff; color: var(--tn-verde); border-radius: 8px; font-size: 12.5px; font-weight: 600; padding: 5px 12px; }
        .dr-agregar:hover { background: #F2F6F6; }
        .dr-guardar { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; font-weight: 600; }
        .dr-guardar:hover { background: #3B5052; color: #fff; }
        .dr-cambios { font-size: 12px; color: #8A6A12; font-weight: 600; }
        .dr-oculto { display: none; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                @if (auth()->user()->hasAnyRole(['Super Usuario', 'Directivo', 'Estadistica']))
                    <a class="dr-migas" href="{{ route('estadisticas.index') }}"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
                @endif
                <h3 class="page__heading mb-0">Datos de referencia</h3>
                <span class="small" style="color: var(--tn-tenue);">Cifras del Reporte estadístico que no salen del sistema. Cambian poco; cada bloque se guarda por separado.</span>
            </div>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('estadisticas.reporte') }}"><i class="bi bi-easel2"></i> Ver el reporte</a>
        </div>

        <div class="section-body">
            <nav class="dr-indice">
                @foreach ($grupos as $grupo => $datos)
                    <a href="#grupo-{{ $grupo }}"><i class="bi {{ DR::GRUPOS[$grupo][1] }}"></i> {{ DR::GRUPOS[$grupo][0] }}</a>
                @endforeach
            </nav>

            @foreach ($grupos as $grupo => $datos)
                <div class="dr-grupo" id="grupo-{{ $grupo }}">
                    <span class="dr-grupo__icono"><i class="bi {{ DR::GRUPOS[$grupo][1] }}"></i></span>
                    <div><h2>{{ DR::GRUPOS[$grupo][0] }}</h2><p>{{ DR::GRUPOS[$grupo][2] }}</p></div>
                </div>

                @foreach ($datos as $clave => $d)
                    @php
                        $bolsa = $errors->getBag($clave);
                        // Tras un error se muestra lo que se había escrito en este bloque.
                        $valor = $bolsa->any()
                            ? ($d['tipo'] === 'campos' ? (old('campos') ?? $d['actual']) : array_values(old('filas') ?? $d['actual']))
                            : $d['actual'];
                        $largo = $d['tipo'] === 'campos' && collect($d['campos'])->contains(fn ($c) => $c[1] === 'parrafo');
                    @endphp
                    <form class="tn-tarjeta dr-tarjeta js-dr-form" id="{{ $clave }}" method="POST" action="{{ route('datos_referencia.guardar', $clave) }}" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="dr-cabeza">
                            <div>
                                <h3 class="dr-titulo">{{ $d['titulo'] }}</h3>
                                @if (! empty($d['ayuda']))<p class="dr-ayuda">{{ $d['ayuda'] }}</p>@endif
                            </div>
                            <span class="dr-editado">
                                @if ($d['editado'])
                                    <i class="bi bi-pencil"></i> {{ $d['editor'] ?? 'Alguien' }} · {{ $d['editado']->isoFormat('D MMM YYYY, HH:mm') }}
                                @else
                                    <i class="bi bi-file-earmark-text"></i> Valor del reporte de septiembre 2026
                                @endif
                            </span>
                        </div>

                        @if ($bolsa->any())
                            <div class="alert alert-danger py-2 small">{{ $bolsa->first() }}</div>
                        @endif

                        @if ($d['tipo'] === 'campos')
                            <div class="dr-campos {{ $largo ? 'dr-campos--largo' : '' }}">
                                @foreach ($d['campos'] as $campo => [$etiqueta, $tipo])
                                    @php($v = $valor[$campo] ?? null)
                                    <div>
                                        <label for="{{ $clave }}-{{ $campo }}">{{ $etiqueta }}</label>
                                        @if ($tipo === 'parrafo')
                                            <textarea id="{{ $clave }}-{{ $campo }}" name="campos[{{ $campo }}]" class="form-control js-autoalto" rows="3">{{ $v }}</textarea>
                                        @elseif ($tipo === 'fecha')
                                            <input type="date" id="{{ $clave }}-{{ $campo }}" name="campos[{{ $campo }}]" class="form-control" value="{{ $v }}">
                                        @else
                                            <input type="text" id="{{ $clave }}-{{ $campo }}" name="campos[{{ $campo }}]" class="form-control {{ in_array($tipo, ['entero', 'moneda', 'decimal']) ? 'dr-num' : '' }}"
                                                   value="{{ $mostrar($v, $tipo) }}" @if (in_array($tipo, ['entero', 'moneda', 'decimal'])) inputmode="decimal" @endif>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @php($fijas = ! empty($d['fijas']))
                            <div class="dr-scroll">
                                <table class="dr-tabla">
                                    <thead><tr>
                                        @foreach ($d['columnas'] as [$etiqueta]) <th>{{ $etiqueta }}</th> @endforeach
                                        @unless ($fijas)<th></th>@endunless
                                    </tr></thead>
                                    <tbody class="js-dr-filas">
                                        @foreach ($valor as $i => $fila)
                                            <tr>
                                                @foreach ($d['columnas'] as $col => [$etiqueta, $tipo])
                                                    @php($v = $fila[$col] ?? null)
                                                    @if ($tipo === 'fijo')
                                                        <td class="dr-fijo">{{ $v }}</td>
                                                    @elseif ($tipo === 'sino')
                                                        <td><select name="filas[{{ $i }}][{{ $col }}]" class="form-select" aria-label="{{ $etiqueta }}">
                                                            <option value="0" @selected(! $v)>No</option><option value="1" @selected($v)>Sí</option>
                                                        </select></td>
                                                    @else
                                                        <td><input type="text" name="filas[{{ $i }}][{{ $col }}]" class="form-control {{ in_array($tipo, ['entero', 'moneda', 'decimal']) ? 'dr-num' : '' }}"
                                                                   value="{{ $mostrar($v, $tipo) }}" aria-label="{{ $etiqueta }}" @if (in_array($tipo, ['entero', 'moneda', 'decimal'])) inputmode="decimal" @endif></td>
                                                    @endif
                                                @endforeach
                                                @unless ($fijas)
                                                    <td><button type="button" class="dr-quitar js-dr-quitar" title="Quitar renglón" aria-label="Quitar renglón"><i class="bi bi-x-lg"></i></button></td>
                                                @endunless
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="dr-pie">
                            <div>
                                @if ($d['tipo'] === 'tabla' && empty($d['fijas']))
                                    <button type="button" class="dr-agregar js-dr-agregar"><i class="bi bi-plus-lg"></i> Agregar renglón</button>
                                @endif
                                <span class="dr-cambios dr-oculto js-dr-cambios"><i class="bi bi-exclamation-circle"></i> Cambios sin guardar</span>
                            </div>
                            <button type="submit" class="btn btn-sm dr-guardar js-dr-guardar"><i class="bi bi-check2"></i> Guardar</button>
                        </div>
                    </form>
                @endforeach
            @endforeach
        </div>
    </section>
@endsection

@section('scripts')
    @include('partials.general._textarea_autoalto')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    (function () {
        @if (session('guardado'))
            Swal.fire({ icon: 'success', title: 'Guardado', text: {!! json_encode('Se actualizó «'.session('guardado').'». El reporte ya usa los nuevos datos.') !!}, confirmButtonColor: '#496163', confirmButtonText: 'Listo', timer: 3500 });
        @endif

        var pendientes = new Set();

        document.querySelectorAll('.js-dr-form').forEach(function (form) {
            var aviso = form.querySelector('.js-dr-cambios');
            function marcar() { pendientes.add(form); aviso.classList.remove('dr-oculto'); }
            form.addEventListener('input', marcar);
            form.addEventListener('change', marcar);

            // Renglones: agregar (copia vacía del último) y quitar.
            var cuerpo = form.querySelector('.js-dr-filas');
            var agregar = form.querySelector('.js-dr-agregar');
            if (agregar && cuerpo) {
                agregar.addEventListener('click', function () {
                    var modelo = cuerpo.lastElementChild;
                    if (!modelo) { return; }
                    var nueva = modelo.cloneNode(true);
                    var n = Date.now();
                    nueva.querySelectorAll('input, select').forEach(function (el) {
                        el.name = el.name.replace(/filas\[[^\]]+\]/, 'filas[n' + n + ']');
                        if (el.tagName === 'SELECT') { el.value = '0'; } else { el.value = ''; }
                    });
                    cuerpo.appendChild(nueva);
                    nueva.querySelector('input').focus();
                    marcar();
                });
            }
            form.addEventListener('click', function (e) {
                var b = e.target.closest('.js-dr-quitar');
                if (!b) { return; }
                if (cuerpo.children.length <= 1) {
                    b.closest('tr').querySelectorAll('input').forEach(function (i) { i.value = ''; });
                } else {
                    b.closest('tr').remove();
                }
                marcar();
            });

            // Guardar una sola vez.
            form.addEventListener('submit', function () {
                pendientes.delete(form);
                var btn = form.querySelector('.js-dr-guardar');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando…';
            });
        });

        // Avisar antes de irse con cambios sin guardar (enlaces de la página).
        document.addEventListener('click', function (e) {
            var a = e.target.closest('a[href]');
            if (!a || !pendientes.size || a.getAttribute('href').charAt(0) === '#' || a.target === '_blank') { return; }
            e.preventDefault();
            Swal.fire({
                icon: 'warning', title: 'Tienes cambios sin guardar',
                text: 'Si sales ahora, se pierden los cambios que no guardaste.',
                showCancelButton: true, confirmButtonText: 'Salir sin guardar', cancelButtonText: 'Seguir editando',
                confirmButtonColor: '#496163', reverseButtons: true
            }).then(function (r) { if (r.isConfirmed) { pendientes.clear(); window.location.href = a.href; } });
        });
        window.addEventListener('beforeunload', function (e) {
            if (pendientes.size) { e.preventDefault(); e.returnValue = ''; }
        });
    })();
    </script>
@endsection
