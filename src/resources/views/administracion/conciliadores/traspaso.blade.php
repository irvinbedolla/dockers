@extends('layouts.app')
@section('title', 'Traspasar audiencias')

{{--
    Administración → Conciliadores → Traspasar audiencias (sólo Super Usuario).
    ConciliadoresController@traspaso / @aplicarTraspaso, App\Support\TraspasoAudiencias.
    1) Sede, a quién se pasan y desde (y hasta) cuándo.
    2) Qué conciliadores tienen audiencias ahí en ese periodo y cuáles se pasan.
    3) Cuántas le caben al destino y cuántas chocan con su agenda, y por qué.
    4) Motivo y traspasar.
--}}

@php
    use App\Support\SedesConciliador as SC;
    use App\Support\TraspasoAudiencias as TA;
    $f = $filtros;
@endphp

@section('page_css')
    @include('administracion.conciliadores._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <a href="{{ route('conciliadores.index') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                <i class="bi bi-arrow-left"></i> Conciliadores
            </a>
            <h3 class="page__heading mb-0">Traspasar audiencias</h3>
            <div class="cn-sub">Pasa a un conciliador las audiencias ya agendadas de una sede, a partir de una fecha. Fecha y hora no cambian.</div>
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
                            <span class="tr-choca">No tiene {{ $f['sede'] }} en su agenda a partir del {{ \Illuminate\Support\Carbon::parse($f['desde'])->locale('es')->isoFormat('D [de] MMMM') }}.</span>
                        @endif
                        <a href="{{ route('conciliadores.editar', $destino->id) }}" class="ms-auto small" target="_blank"><i class="bi bi-pencil-square"></i> Editar su agenda</a>
                    </div>
                    @unless ($agenda)
                        <p class="cn-sub mt-2 mb-0">Sin esa sede todas chocan. Dale {{ $f['sede'] }} (virtual) con "Desde" {{ \Illuminate\Support\Carbon::parse($f['desde'])->format('d/m/Y') }} en su agenda y vuelve a revisar.</p>
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
                            <ul class="cn-sub mb-3">
                                @foreach ($resumen['porMotivo'] as $m => $n)
                                    <li><b>{{ $n }}</b> · {{ TA::MOTIVOS[$m] }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($revision->isNotEmpty())
                            <div class="table-responsive" style="max-height: 460px; overflow-y: auto;">
                                <table class="cn-tabla tr-tabla">
                                    <thead>
                                        <tr><th>Fecha</th><th>Hora</th><th>Expediente</th><th>Conciliador actual</th><th>Resultado</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($revision as $a)
                                            <tr class="{{ $a->pasa ? '' : 'tr-fila--choca' }}">
                                                <td>{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::parse($a->fecha)->locale('es')->isoFormat('ddd D MMM YYYY')) }}</td>
                                                <td><b>{{ $a->hora }}</b></td>
                                                <td>{{ $a->nue ?: $a->folio }}</td>
                                                <td>{{ $a->conciliador }}</td>
                                                <td>
                                                    @if ($a->pasa)
                                                        <span class="tr-pasa"><i class="bi bi-check-circle-fill"></i> Le cabe</span>
                                                    @else
                                                        <span class="tr-choca"><i class="bi bi-exclamation-triangle-fill"></i> Choca
                                                            @foreach ($a->motivos as $texto)<span>{{ $texto }}</span>@endforeach
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- 4. Aplicar --}}
                    @if ($resumen['total'])
                        <div class="tn-tarjeta mb-3">
                            <form method="POST" action="{{ route('conciliadores.traspaso.aplicar') }}" id="trAplicar"
                                  data-pasan="{{ $resumen['pasan'] }}" data-total="{{ $resumen['total'] }}" data-destino="{{ $destino->name }}">
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
                                        @if ($resumen['chocan'])
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="trChoques" name="incluir_choques">
                                                <label class="form-check-label small" for="trChoques">
                                                    Pasar también las {{ $resumen['chocan'] }} que chocan (se le encimarían o quedarían fuera de su horario).
                                                    Sin marcar, se quedan con su conciliador.
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="trBoton" @disabled(! $resumen['pasan'] && ! $resumen['chocan'])>
                                        <i class="bi bi-arrow-left-right"></i> <span>Traspasar {{ $resumen['pasan'] }} {{ $resumen['pasan'] === 1 ? 'audiencia' : 'audiencias' }}</span>
                                    </button>
                                </div>
                                <p class="cn-sub mt-2 mb-0">
                                    <i class="bi bi-info-circle"></i> La fecha y la hora no cambian. Si ya se notificó a las partes con la sala o el nombre del conciliador anterior,
                                    avísales del cambio. Queda registrado en la bitácora.
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
<script>
(function () {
    var form = document.getElementById('trAplicar');
    if (!form) { return; }
    var choques = document.getElementById('trChoques');
    var boton = document.getElementById('trBoton');
    function cuantas() { return (choques && choques.checked) ? +form.dataset.total : +form.dataset.pasan; }
    function pinta() {
        var n = cuantas();
        boton.querySelector('span').textContent = 'Traspasar ' + n + (n === 1 ? ' audiencia' : ' audiencias');
        boton.disabled = n === 0;
    }
    if (choques) { choques.addEventListener('change', pinta); }
    pinta();

    var confirmado = false;
    form.addEventListener('submit', function (e) {
        if (confirmado || !form.checkValidity()) { return; }
        e.preventDefault();
        var n = cuantas();
        var texto = 'Se pasarán ' + n + (n === 1 ? ' audiencia' : ' audiencias') + ' a ' + form.dataset.destino + '. Fecha y hora no cambian.';
        var seguir = function () { confirmado = true; boton.disabled = true; form.submit(); };
        if (typeof Swal === 'undefined') { if (window.confirm(texto)) { seguir(); } return; }
        Swal.fire({
            icon: 'question', title: '¿Traspasar audiencias?', text: texto,
            showCancelButton: true, confirmButtonText: 'Sí, traspasar', cancelButtonText: 'Cancelar',
            reverseButtons: true, confirmButtonColor: '#496163'
        }).then(function (r) { if (r.isConfirmed) { seguir(); } });
    });
})();
</script>
@endpush
