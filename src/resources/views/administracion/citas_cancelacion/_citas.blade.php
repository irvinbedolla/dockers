{{--
    Pestaña Citas de Cancelación/Reagenda (CancelacionCitasController::index).
    Espera $f, $citas (paginadas), $resumen, $historial, $hoy, $rango y $ruta.
--}}
@php
    use App\Http\Controllers\CancelacionCitasController;
    use App\Support\CitasCancelacion as CC;
    use Illuminate\Support\Carbon;

    $kpis = ['' => ['Total', $resumen['total']]];
    foreach (CC::ESTATUS as $clave => $texto) {
        $kpis[$clave] = [$clave === 'pendiente' ? 'Pendientes' : $texto.'s', $resumen[$clave]];
    }
@endphp

<div class="rg-kpis">
    @foreach ($kpis as $clave => [$texto, $n])
        <a href="{{ $ruta(['estatus' => $clave ?: null, 'page' => null]) }}"
           class="rg-kpi rg-kpi--{{ $clave ?: 'total' }} {{ ($f['estatus'] ?? '') === $clave ? 'is-activo' : '' }}"
           @if (($f['estatus'] ?? '') === $clave) aria-current="true" @endif>
            <b>{{ number_format($n) }}</b><span>{{ $texto }}</span>
        </a>
    @endforeach
    <div class="rg-kpi"><b>{{ number_format($resumen['enLinea']) }}</b><span>Agendadas en línea</span></div>
</div>

<div class="rg-titulo-fila">
    <p class="rg-titulo">
        @if ($f['buscando'])
            Resultados para “{{ $f['buscar'] }}”
        @else
            {{ $rango }}
        @endif
        <small>· {{ number_format($citas->total()) }} {{ $citas->total() === 1 ? 'cita' : 'citas' }}{{ $f['estatus'] ? ' '.mb_strtolower(CC::ESTATUS[$f['estatus']]).($citas->total() === 1 ? '' : 's') : '' }}</small>
    </p>
    @if ($f['unDia'] && ! $f['buscando'])
        @php
            $antes   = Carbon::parse($f['desde'])->subDay()->toDateString();
            $despues = Carbon::parse($f['desde'])->addDay()->toDateString();
        @endphp
        <div class="rg-dia">
            <a href="{{ $ruta(['desde' => $antes, 'hasta' => $antes, 'page' => null]) }}" class="btn btn-outline-secondary btn-sm" aria-label="Día anterior"><i class="bi bi-chevron-left"></i></a>
            <a href="{{ $ruta(['desde' => $despues, 'hasta' => $despues, 'page' => null]) }}" class="btn btn-outline-secondary btn-sm" aria-label="Día siguiente"><i class="bi bi-chevron-right"></i></a>
        </div>
    @endif
</div>

@if ($citas->isEmpty())
    <p class="text-muted mb-0">{{ $f['buscando'] ? 'No se encontraron citas.' : 'No hay citas con estos filtros.' }}</p>
@else
    <div class="table-responsive">
        <table class="table table-sm align-middle rg-tabla">
            <thead><tr><th>Folio</th><th>Solicitante</th><th>Sede</th><th>Trámite</th><th>Origen</th><th>Fecha</th><th>Hora</th><th>Estatus</th><th></th></tr></thead>
            <tbody>
            @foreach ($citas as $cita)
                @php $h = $historial[$cita->id] ?? null; @endphp
                <tr>
                    <td class="text-nowrap"><b>{{ $cita->folio_cita }}</b></td>
                    <td>
                        {{ $cita->solicitante }}
                        @if ($cita->correo || $cita->telefono)
                            <span class="rg-contacto">{{ implode(' · ', array_filter([$cita->correo, $cita->telefono])) }}</span>
                        @endif
                    </td>
                    <td>{{ CC::sede($cita->delegacion) }}</td>
                    <td>{{ CC::tramite($cita->tipo) }}</td>
                    <td>
                        @if (isset(CC::ORIGENES[$cita->origen]))
                            <span class="rg-chip rg-chip--{{ $cita->origen }}">{{ CC::ORIGENES[$cita->origen] }}</span>
                        @endif
                    </td>
                    <td class="text-nowrap">{{ $cita->fecha->format('d/m/Y') }}</td>
                    <td>{{ substr($cita->getRawOriginal('hora'), 0, 5) }}</td>
                    <td>
                        <span class="rg-estatus rg-estatus--{{ $cita->estatus }}"
                              @if ($h && $h['cancelada']) title="Cancelada el {{ $h['cancelada']['cuando'] }} por {{ $h['cancelada']['quien'] }}: {{ $h['cancelada']['motivo'] }}" @endif>{{ $cita->estatus }}</span>
                        @if ($h && $h['reagendas'])
                            <span class="rg-chip rg-chip--reagenda" title="Último: {{ $h['ultimo'] }}">Reagendada{{ $h['reagendas'] > 1 ? ' '.$h['reagendas'].'×' : '' }}</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        @if (CancelacionCitasController::movible($cita, $hoy))
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#modalReagendar"
                                    data-folio="{{ $cita->folio_cita }}"
                                    data-accion="{{ route('citas_cancelacion.reagendar', $cita->id) }}"
                                    data-sede="{{ $cita->delegacion }}"
                                    data-tipo="{{ $cita->tipo }}"
                                    data-excepcion="{{ $cita->exepcion === 'Si' ? 'Si' : 'No' }}"
                                    data-fecha="{{ $cita->fecha->format('Y-m-d') }}"
                                    data-hora="{{ substr($cita->getRawOriginal('hora'), 0, 5) }}"
                                    data-confirmada="{{ $cita->estatus === 'confirmada' ? 'Si' : 'No' }}"
                                    data-sede-catalogo="{{ CC::sede($cita->delegacion) }}"
                                    data-tramite="{{ CC::tramite($cita->tipo) }}">{{ CancelacionCitasController::puedeCambiarTodo(auth()->user()) ? 'Cambiar' : 'Reagendar' }}</button>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalCancelar"
                                    data-folio="{{ $cita->folio_cita }}"
                                    data-accion="{{ route('citas_cancelacion.cancelar', $cita->id) }}">Cancelar</button>
                        @elseif (CancelacionCitasController::enAtencion($cita))
                            <span class="rg-sin-accion">En atención</span>
                        @elseif (in_array($cita->estatus, ['pendiente', 'confirmada'], true))
                            <span class="rg-sin-accion">Ya pasó</span>
                        @else
                            <span class="rg-sin-accion">Sólo pendientes o confirmadas</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="rg-pie-tabla">
        <span class="rg-sin-accion">Mostrando {{ $citas->firstItem() }}–{{ $citas->lastItem() }} de {{ number_format($citas->total()) }}</span>
        {{ $citas->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
@endif
