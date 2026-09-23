@extends('layouts.app')
@section('title', 'Detalle de retroceso')
@section('content')
    @php
        $campo = fn ($nombre) => ucfirst(str_replace('_', ' ', $nombre));
        $valor = function ($v) {
            if ($v === null || $v === '') {
                return '—';
            }
            return is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
        };
    @endphp
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">
                Retroceso #{{ $retroceso->id }} · {{ $tipos[$retroceso->tipo] ?? $retroceso->tipo }} · {{ $retroceso->NUE ?? 'Sin NUE' }}
            </h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-12 col-md-6">
                                    <p class="mb-1"><strong>Aplicado por:</strong> {{ $retroceso->user_nombre ?? '—' }}</p>
                                    <p class="mb-1"><strong>Fecha:</strong> {{ $retroceso->created_at->format('d/m/Y H:i:s') }}</p>
                                    <p class="mb-1"><strong>IP:</strong> {{ $retroceso->ip ?? '—' }}</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <p class="mb-1"><strong>Delegación:</strong> {{ $retroceso->delegacion ?? '—' }}</p>
                                    <p class="mb-1">
                                        <strong>Estatus:</strong>
                                        {{ $retroceso->estatus_previo ?? '—' }}
                                        <i class="bi bi-arrow-right"></i>
                                        {{ $retroceso->estatus_nuevo ?? '—' }}
                                    </p>
                                    <p class="mb-1"><strong>Motivo:</strong> {{ $retroceso->motivo ?? 'No capturado' }}</p>
                                </div>
                            </div>

                            @if ($eliminados->isEmpty() && $modificados->isEmpty())
                                <div class="alert alert-info">Este retroceso no borró ni modificó registros.</div>
                            @endif

                            {{-- Registros eliminados, agrupados por tabla --}}
                            @foreach ($eliminados as $tabla => $registros)
                                @php
                                    $resumen = collect($columnas[$tabla]
                                            ?? array_slice(array_keys($registros->first()->datos_antes ?? []), 0, 5))
                                        ->reject(fn ($c) => in_array($c, ['id', 'created_at', 'updated_at'], true))
                                        ->values();
                                @endphp
                                <h5 class="mt-4 text-danger">
                                    <i class="bi bi-trash"></i>
                                    {{ $tablas[$tabla] ?? $tabla }} eliminados ({{ $registros->count() }})
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                @foreach ($resumen as $columna)
                                                    <th>{{ $campo($columna) }}</th>
                                                @endforeach
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($registros as $detalle)
                                                <tr>
                                                    <td>{{ $detalle->registro_id }}</td>
                                                    @foreach ($resumen as $columna)
                                                        <td>{{ $valor($detalle->datos_antes[$columna] ?? null) }}</td>
                                                    @endforeach
                                                    <td class="text-end">
                                                        <button type="button" class="btn btn-sm btn-light"
                                                                data-bs-toggle="collapse" data-bs-target="#registro-{{ $detalle->id }}">
                                                            <i class="bi bi-list-ul"></i> Ver todo
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr class="collapse" id="registro-{{ $detalle->id }}">
                                                    <td colspan="{{ $resumen->count() + 2 }}">
                                                        <table class="table table-sm mb-0">
                                                            @foreach ($detalle->datos_antes ?? [] as $columna => $dato)
                                                                <tr>
                                                                    <th class="w-25">{{ $campo($columna) }}</th>
                                                                    <td>{{ $valor($dato) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            {{-- Registros modificados: antes / después --}}
                            @foreach ($modificados as $detalle)
                                <h5 class="mt-4 text-primary">
                                    <i class="bi bi-pencil-square"></i>
                                    {{ $tablas[$detalle->tabla] ?? $detalle->tabla }} #{{ $detalle->registro_id }} modificado
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="w-25">Campo</th>
                                                <th>Antes</th>
                                                <th>Después</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($detalle->datos_antes ?? [] as $columna => $antes)
                                                <tr>
                                                    <th>{{ $campo($columna) }}</th>
                                                    <td class="text-danger">{{ $valor($antes) }}</td>
                                                    <td class="text-success">{{ $valor($detalle->datos_despues[$columna] ?? null) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            <br>
                            <a href="{{ url()->previous() === url()->current() ? route('retrocesos_historial') : url()->previous() }}" class="btn btn-warning">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
