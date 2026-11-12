@extends('layouts.app')
@section('title', 'Traspasar audiencias')

{{--
    Administración → Conciliadores → Traspasar audiencias (sólo Super Usuario).
    ConciliadoresController@traspaso / @aplicarTraspaso, App\Support\TraspasoAudiencias.
    1) Sede, a quién se pasan y desde (y hasta) cuándo.
    2) Qué conciliadores tienen audiencias ahí en ese periodo y cuáles se pasan.
    3) Cuántas le caben al destino y, de las que chocan, qué se hace con cada
       una: se queda, pasa de todos modos (empalme) o va a otro conciliador
       de la sede libre a esa hora. Más de 2 a la misma hora: hay que confirmarlo.
    4) Motivo y traspasar. Después, "Resumen del traspaso" para bajarlo como imagen.
--}}

@php
    use App\Support\SedesConciliador as SC;
    use App\Support\TraspasoAudiencias as TA;
    use Illuminate\Support\Carbon;
    $f = $filtros;
    $dia = fn ($fecha) => \Illuminate\Support\Str::ucfirst(Carbon::parse($fecha)->locale('es')->isoFormat('ddd D MMM YYYY'));
@endphp

@section('page_css')
    @include('administracion.conciliadores._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <div>
                <a href="{{ route('conciliadores.index') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Conciliadores
                </a>
                <h3 class="page__heading mb-0">Traspasar audiencias</h3>
                <div class="cn-sub">Pasa a un conciliador las audiencias ya agendadas de una sede, a partir de una fecha. Fecha y hora no cambian.</div>
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
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- Resumen del traspaso recién hecho: se baja como imagen para compartirlo. --}}
            @if ($resultado)
                <div class="tn-tarjeta mb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <h5 class="mb-0" style="font-size: 15px; font-weight: 700; color: var(--tn-tinta);">Resumen del traspaso</h5>
                        <button type="button" class="btn btn-sm tn-boton-dorado" id="trBajarResumen"
                                data-archivo="traspaso_{{ \Illuminate\Support\Str::slug($resultado['sede'], '_') }}_{{ now()->format('Y-m-d_Hi') }}.png">
                            <i class="bi bi-image"></i> Descargar imagen
                        </button>
                    </div>
                    <div class="tr-resumen-marco">
                        <div class="tr-resumen" id="trResumen">
                            <div class="tr-resumen__cabeza">
                                <div>
                                    <div class="tr-resumen__titulo">Traspaso de audiencias · {{ $resultado['sede'] }}</div>
                                    <div class="tr-resumen__sub">A {{ $resultado['destino'] }} · {{ $resultado['periodo'] }}</div>
                                </div>
                                <div class="tr-resumen__ccl">Centro de Conciliación Laboral<br>del Estado de Michoacán</div>
                            </div>
                            <div class="tr-resumen__barra"><span></span></div>
                            @php
                                $porEstado = collect($resultado['filas'])->countBy('estado');
                            @endphp
                            <div class="tr-resumen__cifras">
                                <div><b>{{ count($resultado['filas']) }}</b> {{ count($resultado['filas']) === 1 ? 'revisada' : 'revisadas' }}</div>
                                <div class="ok"><b>{{ ($porEstado['pasa'] ?? 0) + ($porEstado['empalme'] ?? 0) }}</b> a {{ $resultado['destino'] }}</div>
                                @if ($porEstado['otro'] ?? 0)<div class="otro"><b>{{ $porEstado['otro'] }}</b> a otro conciliador</div>@endif
                                @if ($porEstado['queda'] ?? 0)<div class="queda"><b>{{ $porEstado['queda'] }}</b> {{ $porEstado['queda'] === 1 ? 'se queda' : 'se quedan' }} con su conciliador</div>@endif
                                @if ($porEstado['empalme'] ?? 0)<div class="empalme"><b>{{ $porEstado['empalme'] }}</b> {{ $porEstado['empalme'] === 1 ? 'empalmada' : 'empalmadas' }}</div>@endif
                            </div>
                            <table class="tr-resumen__tabla">
                                <thead><tr><th>Fecha</th><th>Hora</th><th>Expediente</th><th>Antes</th><th>Ahora</th><th>Nota</th></tr></thead>
                                <tbody>
                                    @foreach ($resultado['filas'] as $r)
                                        <tr class="tr-resumen__{{ $r['estado'] }}">
                                            <td>{{ $dia($r['fecha']) }}</td>
                                            <td><b>{{ $r['hora'] }}</b></td>
                                            <td>{{ $r['nue'] }}</td>
                                            <td>{{ $r['antes'] }}</td>
                                            <td><b>{{ $r['ahora'] }}</b></td>
                                            <td>{{ $r['nota'] ?: 'Pasa' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="tr-resumen__pie">
                                Motivo: {{ $resultado['motivo'] }}<br>
                                Hecho el {{ $resultado['cuando'] }} por {{ $resultado['por'] }}. La fecha y la hora de las audiencias no cambiaron.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 1. Qué se traspasa --}}
            <div class="tn-tarjeta mb-3">
                <form method="GET" action="{{ route('conciliadores.traspaso') }}" class="tr-form">
                    <div>
                        <label for="trSede">Audiencias de la sede</label>
                        <select class="form-select form-select-sm" id="trSede" name="sede" required>
                            <option value="">Elige…</option>
                            @foreach (SC::SEDES as $s)
                                <option value="{{ $s }}" @selected(($f['sede'] ?? '') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="trDestino">Pasan a</label>
                        <select class="form-select form-select-sm" id="trDestino" name="destino" required>
                            <option value="">Elige al conciliador…</option>
                            @foreach ($conciliadores->groupBy('delegacion') as $delegacion => $grupo)
                                <optgroup label="{{ $delegacion ?: 'Sin sede' }}">
                                    @foreach ($grupo as $c)
                                        <option value="{{ $c->id }}" @selected((int) ($f['destino'] ?? 0) === $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="trDesde">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="trDesde" name="desde" value="{{ $f['desde'] ?? '' }}" required>
                    </div>
                    <div>
                        <label for="trHasta">Hasta <small class="fw-normal">(opcional)</small></label>
                        <input type="date" class="form-control form-control-sm" id="trHasta" name="hasta" value="{{ $f['hasta'] ?? '' }}">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-sm tn-boton-dorado w-100"><i class="bi bi-search"></i> Revisar</button>
                    </div>
                </form>
                <p class="cn-sub mt-2 mb-0">
                    Esto mueve lo <b>ya agendado</b>. Para que las audiencias <b>nuevas</b> de esa sede también le caigan desde esa fecha,
                    dale la sede en su agenda (Conciliadores → Editar) con la vigencia "Desde".
                </p>
            </div>

            @if ($listo)
                {{-- Agenda del destino en esa sede --}}
                <div class="tn-tarjeta mb-3">
                    <div class="tr-agenda">
                        <b>{{ $destino->name }} en {{ $f['sede'] }}:</b>
                        @if ($agenda)
                            <span class="cn-bloque {{ $agenda['modalidad'] === SC::VIRTUAL ? 'cn-bloque--virtual' : '' }} d-inline-block" style="--c: {{ SC::COLORES[$f['sede']] ?? '#496163' }};">
                                {{ SC::MODALIDADES[$agenda['modalidad']] ?? $agenda['modalidad'] }} · {{ SC::diasTexto($agenda['dias']) }} {{ $agenda['inicio'] }}–{{ $agenda['fin'] }}
                            </span>
                            @if ($agenda['vigencia'])<span class="cn-sub"><i class="bi bi-calendar-range"></i> {{ $agenda['vigencia'] }}</span>@endif
                            @if (! empty($agenda['exclusiva']))<span class="cn-sub" style="color: #8C1D40; font-weight: 700;"><i class="bi bi-lock-fill"></i> Exclusiva: las nuevas de {{ $f['sede'] }} sólo le caen a él/ella</span>@endif
                        @else
                            <span class="tr-choca">No tiene {{ $f['sede'] }} en su agenda a partir del {{ Carbon::parse($f['desde'])->locale('es')->isoFormat('D [de] MMMM') }}.</span>
                        @endif
                        <a href="{{ route('conciliadores.editar', $destino->id) }}" class="ms-auto small" target="_blank"><i class="bi bi-pencil-square"></i> Editar su agenda</a>
                    </div>
                    @unless ($agenda)
                        <p class="cn-sub mt-2 mb-0">Sin esa sede todas chocan. Dale {{ $f['sede'] }} (virtual) con "Desde" {{ Carbon::parse($f['desde'])->format('d/m/Y') }} en su agenda y vuelve a revisar.</p>
                    @endunless
                </div>

                {{-- 2. De quién --}}
                <div class="tn-tarjeta mb-3">
                    <h5 class="mb-2" style="font-size: 15px; font-weight: 700; color: var(--tn-tinta);">
                        Conciliadores con audiencias en {{ $f['sede'] }} {{ mb_strtolower(SC::vigenciaTexto($f['desde'], $f['hasta'] ?? null)) }}
                    </h5>
                    @if ($origenes->isEmpty())
                        <p class="cn-sub mb-0">No hay audiencias pendientes de {{ $f['sede'] }} en ese periodo (sin contar las que ya son de {{ $destino->name }}).</p>
                    @else
                        <form method="GET" action="{{ route('conciliadores.traspaso') }}" id="trOrigenes">
                            @foreach (['sede', 'destino', 'desde', 'hasta'] as $c)
                                @if (! empty($f[$c]))<input type="hidden" name="{{ $c }}" value="{{ $f[$c] }}">@endif
                            @endforeach
                            <input type="hidden" name="elegir" value="1">
                            <div class="table-responsive">
                                <table class="cn-tabla tr-tabla">
                                    <thead>
                                        <tr><th style="width: 32px;"></th><th>Conciliador</th><th class="text-center">Audiencias</th><th class="text-center">Le caben</th><th class="text-center">Chocan</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($origenes as $o)
                                            @php $r = $porOrigen[$o->id]; @endphp
                                            <tr>
                                                <td><input class="form-check-input" type="checkbox" name="origenes[]" value="{{ $o->id }}" id="trO{{ $o->id }}" @checked(in_array($o->id, $elegidos, true)) onchange="this.form.submit()"></td>
                                                <td><label for="trO{{ $o->id }}" class="mb-0 cn-nombre" style="cursor: pointer;">{{ $o->nombre }}</label></td>
                                                <td class="text-center"><b>{{ $o->total }}</b></td>
                                                <td class="text-center tr-pasa">{{ $r['pasan'] }}</td>
                                                <td class="text-center {{ $r['chocan'] ? 'tr-choca' : 'cn-sub' }}">{{ $r['chocan'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="cn-sub mt-1 mb-0">"Le caben" y "Chocan" cuentan sólo las de cada conciliador; juntando varios pueden chocar entre sí (abajo se ve el total real).</p>
                        </form>
                    @endif
                </div>

                @if ($origenes->isNotEmpty())
                    {{-- 3. Revisión --}}
                    <div class="tn-tarjeta mb-3">
                        <div class="tr-cifras">
                            <div class="tr-cifra"><b>{{ $resumen['total'] }}</b> audiencias elegidas</div>
                            <div class="tr-cifra tr-cifra--pasa"><b>{{ $resumen['pasan'] }}</b> le caben</div>
                            <div class="tr-cifra {{ $resumen['chocan'] ? 'tr-cifra--choca' : '' }}"><b>{{ $resumen['chocan'] }}</b> chocan con su agenda</div>
                        </div>
                        @if ($resumen['porMotivo'])
                            <ul class="cn-sub mb-2">
                                @foreach ($resumen['porMotivo'] as $m => $n)
                                    <li><b>{{ $n }}</b> · {{ TA::MOTIVOS[$m] }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($saturados)
                            <div class="tr-alerta tr-alerta--roja">
                                <b><i class="bi bi-exclamation-octagon-fill"></i> Revisa estos horarios antes de aceptar:</b>
                                hay {{ TA::EMPALME_MAXIMO + 1 }} o más audiencias a la misma hora. Si todas pasan a {{ $destino->name }}, se le empalmarían; dos a la misma hora se aceptan, tres o más no sin confirmarlo.
                                <ul class="mb-0 mt-1">
                                    @foreach ($saturados as $s)
                                        <li><b>{{ $dia($s['fecha']) }} {{ $s['hora'] }}</b> · {{ $s['total'] }} audiencias{{ $s['ya'] ? " ({$s['ya']} ya suya)" : '' }}: {{ implode(', ', $s['expedientes']) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($resumen['chocan'])
                            <div class="tr-masivo">
                                <span class="cn-sub">Las que chocan:</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-todas="dejar">Que se queden con su conciliador</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-todas="destino">Pasarlas a {{ $destino->name }} (empalmes)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-todas="libre">A otro conciliador libre de {{ $f['sede'] }}</button>
                            </div>
                        @endif

                        @if ($revision->isNotEmpty())
                            <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                                <table class="cn-tabla tr-tabla" id="trTabla">
                                    <thead>
                                        <tr><th>Fecha</th><th>Hora</th><th>Expediente</th><th>Conciliador actual</th><th>Resultado</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($revision as $a)
                                            <tr class="{{ $a->pasa ? '' : 'tr-fila--choca' }}" data-slot="{{ $a->slot }}" data-pasa="{{ $a->pasa ? 1 : 0 }}"
                                                data-nue="{{ $a->nue }}" data-existentes='@json($a->existentes)' data-dia="{{ $dia($a->fecha) }}">
                                                <td>{{ $dia($a->fecha) }}</td>
                                                <td><b>{{ $a->hora }}</b></td>
                                                <td>{{ $a->nue }}</td>
                                                <td>{{ $a->conciliador }}</td>
                                                <td>
                                                    @if ($a->pasa)
                                                        <span class="tr-pasa"><i class="bi bi-check-circle-fill"></i> Le cabe</span>
                                                    @else
                                                        <span class="tr-choca"><i class="bi bi-exclamation-triangle-fill"></i> Choca
                                                            @foreach ($a->motivos as $texto)<span>{{ $texto }}</span>@endforeach
                                                        </span>
                                                        @php $ops = $opciones[$a->id] ?? []; @endphp
                                                        <select class="form-select form-select-sm tr-decision mt-1" name="decisiones[{{ $a->id }}]" form="trAplicar" data-slot="{{ $a->slot }}">
                                                            <option value="dejar" @selected(old("decisiones.{$a->id}", 'dejar') === 'dejar')>Se queda con {{ $a->conciliador }}</option>
                                                            <option value="destino" @selected(old("decisiones.{$a->id}") === 'destino')>Pasar a {{ $destino->name }} de todos modos</option>
                                                            @if ($ops)
                                                                <optgroup label="Otro conciliador de {{ $f['sede'] }}">
                                                                    @foreach ($ops as $o)
                                                                        <option value="{{ $o['id'] }}" @disabled(! $o['libre']) @selected((string) old("decisiones.{$a->id}") === (string) $o['id'])>
                                                                            {{ $o['nombre'] }} — {{ $o['libre'] ? 'libre a esa hora' : $o['motivo'] }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endif
                                                        </select>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        {{-- Cómo quedaría la agenda del destino con lo elegido (se arma en el navegador). --}}
                        <div class="tr-alerta mt-3" id="trEmpalmes" hidden></div>
                    </div>

                    {{-- 4. Aplicar --}}
                    @if ($resumen['total'])
                        <div class="tn-tarjeta mb-3">
                            <form method="POST" action="{{ route('conciliadores.traspaso.aplicar') }}" id="trAplicar" data-destino="{{ $destino->name }}">
                                @csrf
                                @foreach (['sede', 'destino', 'desde', 'hasta'] as $c)
                                    @if (! empty($f[$c]))<input type="hidden" name="{{ $c }}" value="{{ $f[$c] }}">@endif
                                @endforeach
                                @foreach ($elegidos as $id)<input type="hidden" name="origenes[]" value="{{ $id }}">@endforeach

                                <div class="tr-aplicar">
                                    <div>
                                        <label for="trMotivo" class="small fw-bold" style="color: var(--tn-suave);">Motivo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="trMotivo" name="motivo" maxlength="255" required value="{{ old('motivo') }}"
                                               placeholder="Ej. Oficio de jurídico: desde el 9 de diciembre {{ $destino->name }} lleva {{ $f['sede'] }} en virtual">
                                        <div class="cn-sub mt-2" id="trPlan"></div>
                                        <div class="form-check mt-2" id="trConfirmarCaja" hidden>
                                            <input class="form-check-input" type="checkbox" value="1" id="trConfirmar" name="confirmar_empalmes">
                                            <label class="form-check-label small fw-bold" for="trConfirmar" style="color: #9A3A22;">
                                                Revisé los horarios con {{ TA::EMPALME_MAXIMO + 1 }} o más audiencias a la misma hora y aun así quiero traspasarlas.
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="trBoton">
                                        <i class="bi bi-arrow-left-right"></i> <span>Traspasar</span>
                                    </button>
                                </div>
                                <p class="cn-sub mt-2 mb-0">
                                    <i class="bi bi-info-circle"></i> La fecha y la hora no cambian. Si ya se notificó a las partes con la sala o el nombre del conciliador anterior,
                                    avísales del cambio. Queda registrado en la bitácora y al terminar puedes bajar el resumen como imagen.
                                </p>
                            </form>
                        </div>
                    @endif
                @endif
            @endif

            @if ($historial->isNotEmpty())
                <div class="tn-tarjeta mt-3">
                    <h5 class="mb-2" style="font-size: 15px; font-weight: 700; color: var(--tn-tinta);">Últimos traspasos</h5>
                    @include('administracion.conciliadores._historial', ['historial' => $historial, 'nombres' => $nombres])
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
(function () {
    var MAX = {{ TA::EMPALME_MAXIMO }};
    var form = document.getElementById('trAplicar');
    var tabla = document.getElementById('trTabla');
    var filas = tabla ? Array.prototype.slice.call(tabla.querySelectorAll('tbody tr')) : [];
    var selects = filas.map(function (tr) { return tr.querySelector('select.tr-decision'); }).filter(Boolean);
    var panel = document.getElementById('trEmpalmes');
    var plan = document.getElementById('trPlan');
    var caja = document.getElementById('trConfirmarCaja');
    var confirmar = document.getElementById('trConfirmar');
    var boton = document.getElementById('trBoton');
    var destino = form ? form.dataset.destino : '';
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

    function calcular() {
        // Un conciliador "libre" no se puede elegir dos veces a la misma hora.
        selects.forEach(function (s) {
            Array.prototype.forEach.call(s.options, function (o) {
                if (!/^\d+$/.test(o.value)) { return; }
                if (o.dataset.ocupado === undefined) { o.dataset.ocupado = o.disabled ? '1' : '0'; }
                var tomado = selects.some(function (x) { return x !== s && x.dataset.slot === s.dataset.slot && x.value === o.value; });
                o.disabled = o.dataset.ocupado === '1' || tomado;
            });
        });

        var porSlot = {}, aDestino = 0, aOtro = 0, quedan = 0;
        filas.forEach(function (tr) {
            var slot = tr.dataset.slot;
            if (!porSlot[slot]) { porSlot[slot] = { dia: tr.dataset.dia, hora: slot.split('|')[1], exp: JSON.parse(tr.dataset.existentes || '[]'), ya: JSON.parse(tr.dataset.existentes || '[]').length }; }
            var s = tr.querySelector('select.tr-decision');
            var d = tr.dataset.pasa === '1' ? 'destino' : (s ? s.value : 'dejar');
            if (d === 'destino') { porSlot[slot].exp.push(tr.dataset.nue); aDestino++; }
            else if (/^\d+$/.test(d)) { aOtro++; } else { quedan++; }
        });

        var dobles = [], triples = [];
        Object.keys(porSlot).forEach(function (k) {
            var p = porSlot[k];
            if (p.exp.length > MAX) { triples.push(p); } else if (p.exp.length === MAX) { dobles.push(p); }
        });

        if (panel) {
            var html = '';
            if (triples.length) {
                html += '<div class="tr-alerta tr-alerta--roja"><b><i class="bi bi-exclamation-octagon-fill"></i> ' + esc(destino) + ' quedaría con ' + (MAX + 1) + ' o más audiencias a la misma hora:</b><ul class="mb-0 mt-1">'
                    + triples.map(function (p) { return '<li><b>' + esc(p.dia + ' ' + p.hora) + '</b> · ' + p.exp.length + ': ' + esc(p.exp.join(', ')) + (p.ya ? ' (' + p.ya + ' ya suya)' : '') + '</li>'; }).join('')
                    + '</ul><div class="mt-1">Asigna alguna a otro conciliador libre o confirma abajo.</div></div>';
            }
            if (dobles.length) {
                html += '<div class="tr-alerta tr-alerta--ambar mt-2"><b><i class="bi bi-intersect"></i> Se le empalman 2 a la misma hora (se acepta):</b><ul class="mb-0 mt-1">'
                    + dobles.map(function (p) { return '<li><b>' + esc(p.dia + ' ' + p.hora) + '</b>: ' + esc(p.exp.join(' y ')) + (p.ya ? ' (una ya era suya)' : '') + '</li>'; }).join('')
                    + '</ul></div>';
            }
            panel.innerHTML = html;
            panel.hidden = !html;
            panel.classList.toggle('tr-alerta', false);
        }

        if (plan) {
            plan.innerHTML = '<b>Así queda:</b> ' + aDestino + ' a ' + esc(destino)
                + (aOtro ? ' · ' + aOtro + ' a otro conciliador' : '')
                + (quedan ? ' · ' + quedan + (quedan === 1 ? ' se queda' : ' se quedan') + ' con su conciliador' : '')
                + (dobles.length ? ' · ' + dobles.length + ' horario' + (dobles.length === 1 ? '' : 's') + ' con 2 empalmadas' : '');
        }
        if (caja) {
            caja.hidden = !triples.length;
            if (!triples.length && confirmar) { confirmar.checked = false; }
        }
        var n = aDestino + aOtro;
        if (boton) {
            boton.querySelector('span').textContent = 'Traspasar ' + n + (n === 1 ? ' audiencia' : ' audiencias');
            boton.disabled = n === 0 || (triples.length > 0 && !(confirmar && confirmar.checked));
        }
        return { n: n, aDestino: aDestino, aOtro: aOtro, quedan: quedan, dobles: dobles, triples: triples };
    }

    selects.forEach(function (s) { s.addEventListener('change', calcular); });
    if (confirmar) { confirmar.addEventListener('change', calcular); }
    document.querySelectorAll('[data-todas]').forEach(function (b) {
        b.addEventListener('click', function () {
            var modo = b.dataset.todas;
            selects.forEach(function (s) { s.value = modo === 'libre' ? 'dejar' : modo; });
            calcular();
            if (modo === 'libre') {
                // Uno por uno, el primer conciliador libre que no esté ya tomado a esa hora.
                selects.forEach(function (s) {
                    var libre = Array.prototype.find.call(s.options, function (o) { return /^\d+$/.test(o.value) && !o.disabled; });
                    if (libre) { s.value = libre.value; calcular(); }
                });
            }
        });
    });
    if (form) { calcular(); }

    var confirmado = false;
    if (form) {
        form.addEventListener('submit', function (e) {
            if (confirmado || !form.checkValidity()) { return; }
            e.preventDefault();
            var r = calcular();
            if (boton.disabled) { return; }
            var texto = r.aDestino + ' pasan a ' + destino + (r.aOtro ? ', ' + r.aOtro + ' a otro conciliador' : '')
                + (r.quedan ? ' y ' + r.quedan + (r.quedan === 1 ? ' se queda' : ' se quedan') + ' con su conciliador' : '') + '. Fecha y hora no cambian.'
                + (r.triples.length ? ' Hay ' + r.triples.length + ' horario(s) con ' + (MAX + 1) + ' o más audiencias a la misma hora.' : '');
            var seguir = function () { confirmado = true; boton.disabled = true; form.submit(); };
            if (typeof Swal === 'undefined') { if (window.confirm(texto)) { seguir(); } return; }
            Swal.fire({
                icon: r.triples.length ? 'warning' : 'question', title: '¿Traspasar ' + r.n + (r.n === 1 ? ' audiencia?' : ' audiencias?'), text: texto,
                showCancelButton: true, confirmButtonText: 'Sí, traspasar', cancelButtonText: 'Cancelar',
                reverseButtons: true, confirmButtonColor: '#496163'
            }).then(function (x) { if (x.isConfirmed) { seguir(); } });
        });
    }

    // Resumen del traspaso → imagen PNG.
    var bajar = document.getElementById('trBajarResumen');
    if (bajar) {
        bajar.addEventListener('click', function () {
            var hoja = document.getElementById('trResumen');
            if (typeof html2canvas === 'undefined') { alert('No se pudo cargar la herramienta de imagen. Revisa tu conexión e inténtalo de nuevo.'); return; }
            bajar.disabled = true;
            html2canvas(hoja, { scale: 2, backgroundColor: '#ffffff' }).then(function (lienzo) {
                var a = document.createElement('a');
                a.download = bajar.dataset.archivo;
                a.href = lienzo.toDataURL('image/png');
                document.body.appendChild(a); a.click(); a.remove();
            }).finally(function () { bajar.disabled = false; });
        });
    }
})();
</script>
@endpush
