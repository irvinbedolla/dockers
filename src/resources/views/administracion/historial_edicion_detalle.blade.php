@extends('layouts.app')
@section('title', 'Detalle de edición')
@section('content')
    @php
        $campo = fn ($nombre) => ucfirst(str_replace('_', ' ', $nombre));
        $valor = function ($v) {
            if ($v === null || $v === '') {
                return '—';
            }
            return is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
        };
        // Creados y eliminados se pintan igual; solo cambia de dónde sale el renglón.
        $grupos = [
            ['registros' => $creados,    'origen' => 'datos_despues', 'texto' => 'agregado(s)',  'color' => 'text-success', 'icono' => 'bi-plus-circle'],
            ['registros' => $eliminados, 'origen' => 'datos_antes',   'texto' => 'eliminado(s)', 'color' => 'text-danger',  'icono' => 'bi-trash'],
        ];
    @endphp
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Edición #{{ $edicion->id }} · {{ $edicion->NUE ?? 'Sin NUE' }}</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-12 col-md-6">
                                    <p class="mb-1"><strong>Editado por:</strong> {{ $edicion->user_nombre ?? '—' }} ({{ $edicion->user_rol ?? 'sin rol' }})</p>
                                    <p class="mb-1"><strong>Fecha:</strong> {{ $edicion->created_at->format('d/m/Y H:i:s') }}</p>
                                    <p class="mb-1"><strong>IP:</strong> {{ $edicion->ip ?? '—' }}</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <p class="mb-1"><strong>Delegación:</strong> {{ $edicion->delegacion ?? '—' }}</p>
                                    <p class="mb-1"><strong>Editado desde:</strong> {{ $edicion->origen ?? '—' }}</p>
                                    <p class="mb-1"><strong>Solicitud:</strong> #{{ $edicion->solicitud_id }}@if ($edicion->audiencia_id) · <strong>Audiencia:</strong> #{{ $edicion->audiencia_id }}@endif</p>
                                </div>
                            </div>

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
                                            @foreach ($detalle->datos_despues ?? [] as $columna => $despues)
                                                <tr>
                                                    <th>{{ $campo($columna) }}</th>
                                                    <td class="text-danger">{{ $valor($detalle->datos_antes[$columna] ?? null) }}</td>
                                                    <td class="text-success">{{ $valor($despues) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            {{-- Registros agregados y eliminados, agrupados por tabla --}}
                            @foreach ($grupos as $grupo)
                                @foreach ($grupo['registros'] as $tabla => $registros)
                                    @php
                                        $resumen = collect($columnas[$tabla]
                                                ?? array_slice(array_keys($registros->first()->{$grupo['origen']} ?? []), 0, 5))
                                            ->reject(fn ($c) => in_array($c, ['id', 'created_at', 'updated_at'], true))
                                            ->values();
                                    @endphp
                                    <h5 class="mt-4 {{ $grupo['color'] }}">
                                        <i class="bi {{ $grupo['icono'] }}"></i>
                                        {{ $tablas[$tabla] ?? $tabla }} {{ $grupo['texto'] }} ({{ $registros->count() }})
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
                                                    @php $datos = $detalle->{$grupo['origen']} ?? []; @endphp
                                                    <tr>
                                                        <td>{{ $detalle->registro_id }}</td>
                                                        @foreach ($resumen as $columna)
                                                            <td>{{ $valor($datos[$columna] ?? null) }}</td>
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
                                                                @foreach ($datos as $columna => $dato)
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
                            @endforeach

                            <br>
                            <a href="{{ url()->previous() === url()->current() ? route('historial_ediciones') : url()->previous() }}" class="btn btn-warning">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
