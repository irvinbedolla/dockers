@extends('layouts.app')
@section('title', 'Horarios de citas')

{{--
    Administración → Horarios de citas. Una tarjeta por trámite de la sede
    elegida; cada una es su propio formulario. La vista previa de horarios se
    recalcula en el navegador con la misma regla que App\Support\HorarioCitas.
--}}

@php
    use App\Support\HorarioCitas;

    $hm = fn ($t) => $t ? substr($t, 0, 5) : '';
    $capacidadDia = function ($c, array $horarios) {
        $total = 0;
        foreach ($horarios as $h) { $total += HorarioCitas::capacidad($c, $h); }
        return $c->limite_diario ? min($total, $c->limite_diario) : $total;
    };
    $iconos = ['Solicitud' => 'bi-file-earmark-text', 'Ratificación' => 'bi-pen', 'Excepción' => 'bi-shield-check'];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .hc-sedes { display: flex; flex-wrap: wrap; gap: 6px; }
        .hc-sede {
            padding: 6px 14px; border-radius: 999px; border: 1px solid var(--tn-borde);
            background: #fff; color: var(--tn-suave); font-size: 13px; font-weight: 600; text-decoration: none;
        }
        .hc-sede:hover { color: var(--tn-verde); border-color: var(--tn-verde-claro); }
        .hc-sede.is-activa { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .hc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 14px; align-items: start; margin-top: 14px; }
        .hc-tarjeta { padding: 0; overflow: hidden; }
        .hc-tarjeta.is-inactivo .hc-cuerpo, .hc-tarjeta.is-inactivo .hc-previa { opacity: .6; }
        .hc-cabeza { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 16px 18px 10px; }
        .hc-cabeza h5 { margin: 0; font-size: 16px; font-weight: 700; color: var(--tn-tinta); display: flex; gap: 8px; align-items: center; }
        .hc-cabeza h5 i { color: var(--tn-dorado); }

        .hc-previa { padding: 0 18px 14px; border-bottom: 1px solid var(--tn-borde); }
        .hc-chips { display: flex; flex-wrap: wrap; gap: 4px; min-height: 26px; }
        .hc-chip {
            padding: 2px 8px; border-radius: 6px; background: #EEF2F2; color: var(--tn-verde);
            font-size: 12.5px; font-weight: 700; font-variant-numeric: tabular-nums;
        }
        .hc-chip--uno { background: #FBF4E1; color: #8A6A12; }
        .hc-chip--vacio { background: #F6E4EA; color: #8C1D40; }
        .hc-dato { margin: 8px 0 0; font-size: 12.5px; color: var(--tn-suave); }
        .hc-dato strong { color: var(--tn-tinta); }

        .hc-cuerpo { padding: 14px 18px 4px; }
        .hc-campos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 12px; }
        .hc-campo--ancho { grid-column: 1 / -1; }
        .hc-campo label { display: block; font-size: 12px; font-weight: 700; color: var(--tn-suave); margin-bottom: 3px; }
        .hc-campo .form-text { font-size: 11.5px; margin-top: 2px; color: var(--tn-tenue); }
        .hc-grupo { display: flex; gap: 6px; }
        .hc-pie { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 12px 18px 16px; }
        .hc-editor { font-size: 11.5px; color: var(--tn-tenue); }

        .hc-resumen { margin-top: 22px; }
        .hc-resumen h5 { font-size: 15px; font-weight: 700; color: var(--tn-tinta); }
        .hc-resumen table { font-size: 13px; }
        .hc-resumen td, .hc-resumen th { vertical-align: middle; }
        .hc-resumen .is-actual { background: #F5F7F7; }
        .hc-nota { font-size: 12.5px; color: var(--tn-tenue); }

        @media (max-width: 420px) {
            .hc-grid { grid-template-columns: 1fr; }
            .hc-campos { grid-template-columns: 1fr; }
        }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('configuracion') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Administración
                </a>
                <h3 class="page__heading mb-0">Horarios de citas</h3>
            </div>
        </div>

        <div class="section-body">
            @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $clave => $color)
                @if (session($clave))
                    <div class="alert alert-{{ $color }} alert-dismissible fade show" role="alert">
                        {{ session($clave) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            @endforeach
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="tn-tarjeta d-flex flex-wrap justify-content-between align-items-center gap-2">
                <nav class="hc-sedes" aria-label="Sede">
                    @foreach ($sedes as $s)
                        <a class="hc-sede {{ $s === $sede ? 'is-activa' : '' }}" href="{{ route('horarios.index', ['sede' => $s]) }}">{{ $s }}</a>
                    @endforeach
                </nav>
                <span class="hc-nota">Los cambios aplican a las citas nuevas, en línea y en ventanilla. Las ya agendadas no se mueven.</span>
            </div>

            <div class="hc-grid">
                @foreach ($configs as $grupo => $c)
                    @php
                        $esEste   = old('tramite') === $grupo && old('delegacion') === $sede;
                        $v        = fn ($campo, $valor) => $esEste ? old($campo, $valor) : $valor;
                        $horarios = HorarioCitas::horarios($c);
                        $unLugar  = implode(', ', $c->horas_un_lugar ?? []);
                        $activo   = $esEste ? (bool) old('activo') : $c->activo;
                    @endphp
                    <form method="POST" action="{{ route('horarios.actualizar') }}"
                          class="tn-tarjeta hc-tarjeta {{ $activo ? '' : 'is-inactivo' }}" data-horario novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="delegacion" value="{{ $sede }}">
                        <input type="hidden" name="tramite" value="{{ $grupo }}">

                        <div class="hc-cabeza">
                            <h5><i class="bi {{ $iconos[$grupo] }}" aria-hidden="true"></i> {{ HorarioCitas::GRUPOS[$grupo] }}</h5>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="activo" value="1"
                                       id="activo-{{ $loop->index }}" {{ $activo ? 'checked' : '' }} data-campo="activo">
                                <label class="form-check-label small" for="activo-{{ $loop->index }}">Recibe citas</label>
                            </div>
                        </div>

                        <div class="hc-previa">
                            <div class="hc-chips" data-previa aria-live="polite">
                                @forelse ($horarios as $h)
                                    <span class="hc-chip {{ HorarioCitas::capacidad($c, $h) === 1 && $c->citas_por_horario > 1 ? 'hc-chip--uno' : '' }}">{{ substr($h, 0, 5) }}</span>
                                @empty
                                    <span class="hc-chip hc-chip--vacio">Sin horarios</span>
                                @endforelse
                            </div>
                            <p class="hc-dato" data-resumen>
                                <strong>{{ count($horarios) }}</strong> horarios al día ·
                                caben hasta <strong>{{ $capacidadDia($c, $horarios) }}</strong> citas
                            </p>
                        </div>

                        <div class="hc-cuerpo">
                            <div class="hc-campos">
                                <div class="hc-campo">
                                    <label for="ini-{{ $loop->index }}">Primer horario</label>
                                    <input type="time" class="form-control" id="ini-{{ $loop->index }}" name="hora_inicio"
                                           value="{{ $v('hora_inicio', $hm($c->hora_inicio)) }}" required data-campo="inicio">
                                </div>
                                <div class="hc-campo">
                                    <label for="fin-{{ $loop->index }}">Cierre</label>
                                    <input type="time" class="form-control" id="fin-{{ $loop->index }}" name="hora_fin"
                                           value="{{ $v('hora_fin', $hm($c->hora_fin)) }}" required data-campo="fin">
                                    <div class="form-text">La última cita termina a esta hora o antes.</div>
                                </div>
                                <div class="hc-campo">
                                    <label for="int-{{ $loop->index }}">Cada cuántos minutos</label>
                                    <input type="number" class="form-control" id="int-{{ $loop->index }}" name="intervalo" min="10" max="240"
                                           value="{{ $v('intervalo', $c->intervalo) }}" required data-campo="intervalo">
                                </div>
                                <div class="hc-campo">
                                    <label for="dur-{{ $loop->index }}">Duración de la cita (min)</label>
                                    <input type="number" class="form-control" id="dur-{{ $loop->index }}" name="duracion" min="5" max="240"
                                           value="{{ $v('duracion', $c->duracion) }}" required data-campo="duracion">
                                </div>
                                <div class="hc-campo">
                                    <label for="cup-{{ $loop->index }}">Citas por horario</label>
                                    <input type="number" class="form-control" id="cup-{{ $loop->index }}" name="citas_por_horario" min="1" max="20"
                                           value="{{ $v('citas_por_horario', $c->citas_por_horario) }}" required data-campo="cupo">
                                    <div class="form-text">Módulos que atienden a la vez.</div>
                                </div>
                                @if ($grupo !== 'Excepción')
                                    <div class="hc-campo">
                                        <label for="lim-{{ $loop->index }}">Límite al día (en línea)</label>
                                        <input type="number" class="form-control" id="lim-{{ $loop->index }}" name="limite_diario" min="1" max="500"
                                               value="{{ $v('limite_diario', $c->limite_diario) }}" placeholder="Sin límite" data-campo="limite">
                                        <div class="form-text">Ventanilla no tiene límite.</div>
                                    </div>
                                @endif
                                <div class="hc-campo hc-campo--ancho">
                                    <label for="uno-{{ $loop->index }}">Horarios con cupo de 1 cita</label>
                                    <input type="text" class="form-control" id="uno-{{ $loop->index }}" name="horas_un_lugar"
                                           value="{{ $v('horas_un_lugar', $unLugar) }}" placeholder="Ninguno · p. ej. 13:30, 14:20" data-campo="uno">
                                    <div class="form-text">Para horas de poco personal (comidas). Se marcan en amarillo arriba.</div>
                                </div>
                                <div class="hc-campo hc-campo--ancho">
                                    <label for="com-{{ $loop->index }}">Comida (opcional)</label>
                                    <div class="hc-grupo">
                                        <input type="time" class="form-control" id="com-{{ $loop->index }}" name="comida_inicio"
                                               value="{{ $v('comida_inicio', $hm($c->comida_inicio)) }}" data-campo="comida" aria-label="Inicio de la comida">
                                        <div class="input-group" style="max-width: 150px;">
                                            <input type="number" class="form-control" name="comida_minutos" min="0" max="180"
                                                   value="{{ $v('comida_minutos', $c->comida_minutos ?? 30) }}" data-campo="comidaMin" aria-label="Minutos de comida">
                                            <span class="input-group-text">min</span>
                                        </div>
                                    </div>
                                    <div class="form-text">Ninguna cita se encima con la comida; la siguiente empieza al terminar.</div>
                                </div>
                            </div>
                            @if ($grupo === 'Excepción')
                                <p class="hc-nota mt-2 mb-0">El calendario de excepción es uno solo para todas las sedes: el cupo se cuenta entre todas.</p>
                            @endif
                        </div>

                        <div class="hc-pie">
                            <span class="hc-editor">
                                @if ($c->exists && $c->updated_by)
                                    Último cambio: {{ optional($c->editor)->name ?? 'usuario '.$c->updated_by }}, {{ $c->updated_at?->locale('es')->isoFormat('D MMM YYYY, HH:mm') }}
                                @elseif (! $c->exists)
                                    Valores de fábrica (aún no guardados)
                                @endif
                            </span>
                            <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-check2"></i> Guardar</button>
                        </div>
                    </form>
                @endforeach
            </div>

            <div class="tn-tarjeta hc-resumen">
                <h5>Todas las sedes</h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Sede</th>
                                @foreach (HorarioCitas::GRUPOS as $etiqueta)<th>{{ $etiqueta }}</th>@endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resumen as $s => $grupos)
                                <tr class="{{ $s === $sede ? 'is-actual' : '' }}">
                                    <th scope="row"><a href="{{ route('horarios.index', ['sede' => $s]) }}" class="text-decoration-none">{{ $s }}</a></th>
                                    @foreach ($grupos as $g)
                                        @php $c = $g['config']; @endphp
                                        <td>
                                            @if (! $c->activo)
                                                <span class="text-muted">No recibe citas</span>
                                            @else
                                                {{ $hm($c->hora_inicio) }}–{{ $hm($c->hora_fin) }} · cada {{ $c->intervalo }} min ·
                                                {{ $c->citas_por_horario }} por horario ·
                                                {{ $c->limite_diario ? 'máx. '.$c->limite_diario.' al día' : 'sin límite' }}
                                                <span class="text-muted">({{ count($g['horarios']) }} horarios)</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        // Vista previa: misma regla que App\Support\HorarioCitas::horarios().
        (function () {
            function minutos(hhmm) {
                if (!/^\d{1,2}:\d{2}/.test(hhmm || '')) { return null; }
                var p = hhmm.split(':');
                return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
            }
            function texto(m) {
                return String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
            }
            function horarios(c) {
                var salida = [];
                if (!c.activo || !(c.intervalo >= 5) || c.inicio === null || c.fin === null) { return salida; }
                var t = c.inicio, dur = Math.max(1, c.duracion || 1);
                var comFin = c.comida !== null ? c.comida + Math.max(0, c.comidaMin || 0) : null;
                while (t + dur <= c.fin && salida.length < 100) {
                    if (c.comida !== null && t < comFin && t + dur > c.comida) { t = comFin; continue; }
                    salida.push(texto(t));
                    t += c.intervalo;
                }
                return salida;
            }

            document.querySelectorAll('[data-horario]').forEach(function (form) {
                var campo = function (n) { return form.querySelector('[data-campo="' + n + '"]'); };
                var previa = form.querySelector('[data-previa]');
                var resumen = form.querySelector('[data-resumen]');

                function pintar() {
                    var num = function (n) { var el = campo(n); return el && el.value !== '' ? parseInt(el.value, 10) : null; };
                    var c = {
                        activo: campo('activo').checked,
                        inicio: minutos(campo('inicio').value), fin: minutos(campo('fin').value),
                        intervalo: num('intervalo'), duracion: num('duracion'),
                        comida: minutos(campo('comida').value), comidaMin: num('comidaMin'),
                    };
                    var cupo = Math.max(1, num('cupo') || 1);
                    var limite = num('limite');
                    var unLugar = (campo('uno').value || '').split(/[\s,;]+/).filter(Boolean).map(function (h) {
                        var m = minutos(h); return m === null ? h : texto(m);
                    });
                    var lista = horarios(c);
                    var total = 0;

                    previa.innerHTML = '';
                    lista.forEach(function (h) {
                        var uno = cupo > 1 && unLugar.indexOf(h) !== -1;
                        total += uno ? 1 : cupo;
                        var chip = document.createElement('span');
                        chip.className = 'hc-chip' + (uno ? ' hc-chip--uno' : '');
                        chip.textContent = h;
                        previa.appendChild(chip);
                    });
                    if (!lista.length) {
                        previa.innerHTML = '<span class="hc-chip hc-chip--vacio">Sin horarios</span>';
                    }
                    if (limite) { total = Math.min(total, limite); }
                    resumen.innerHTML = '<strong>' + lista.length + '</strong> horarios al día · caben hasta <strong>' + total + '</strong> citas'
                        + (c.duracion && c.intervalo && c.duracion > c.intervalo ? ' · <span style="color:#8C1D40;">la cita dura más que el intervalo</span>' : '');
                    form.classList.toggle('is-inactivo', !c.activo);
                }

                form.addEventListener('input', pintar);
                form.addEventListener('change', pintar);
            });
        })();
    </script>
@endsection
