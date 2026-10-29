@extends('layouts.app')
@section('title', 'Detalle de edición de ratificación')
@section('content')
    @php
        $campo = fn ($nombre) => $campos[$nombre] ?? ucfirst(str_replace('_', ' ', $nombre));
        $valor = function ($columna, $v) use ($conciliadores) {
            if ($v === null || $v === '') {
                return '—';
            }
            if ($columna === 'id_conciliador' && isset($conciliadores[$v])) {
                return $conciliadores[$v];
            }
            return is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
        };
    @endphp
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">
                Edición #{{ $edicion->id }} · {{ $edicion->NUE ?? 'Sin NUE' }}
            </h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row mb-3">
                                <div class="col-12 col-md-6">
                                    <p class="mb-1"><strong>Editado por:</strong> {{ $edicion->user_nombre ?? '—' }}</p>
                                    <p class="mb-1"><strong>Rol:</strong> {{ $edicion->user_rol ?? '—' }}</p>
                                    <p class="mb-1"><strong>Fecha:</strong> {{ $edicion->created_at->format('d/m/Y H:i:s') }}</p>
                                    <p class="mb-1"><strong>IP:</strong> {{ $edicion->ip ?? '—' }}</p>
                                </div>
                                <div class="col-12 col-md-6">
                                    <p class="mb-1"><strong>Delegación:</strong> {{ $edicion->delegacion ?? '—' }}</p>
                                    <p class="mb-1"><strong>Estatus al editar:</strong> {{ $edicion->estatus ?? '—' }}</p>
                                    <p class="mb-1" style="white-space: pre-line;"><strong>Motivo:</strong> {{ $edicion->motivo ?? 'No capturado' }}</p>
                                </div>
                            </div>

                            @if ($edicion->detalles->isEmpty())
                                <div class="alert alert-info">Esta edición no modificó registros.</div>
                            @endif

                            {{-- Cada registro afectado: antes / después --}}
                            @foreach ($edicion->detalles as $detalle)
                                @php
                                    $antes   = $detalle->datos_antes ?? [];
                                    $despues = $detalle->datos_despues ?? [];
                                @endphp
                                <h5 class="mt-4 text-primary">
                                    <i class="bi bi-pencil-square"></i>
                                    {{ $tablas[$detalle->tabla] ?? $detalle->tabla }} #{{ $detalle->registro_id }}
                                    · {{ $acciones[$detalle->accion] ?? $detalle->accion }}
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
                                            @foreach (array_keys($antes + $despues) as $columna)
                                                <tr>
                                                    <th>{{ $campo($columna) }}</th>
                                                    <td class="text-danger">{{ $valor($columna, $antes[$columna] ?? null) }}</td>
                                                    <td class="text-success">{{ $valor($columna, $despues[$columna] ?? null) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            <br>
                            <a href="{{ url()->previous() === url()->current() ? route('historial_edicion_ratificaciones') : url()->previous() }}" class="btn btn-warning">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
