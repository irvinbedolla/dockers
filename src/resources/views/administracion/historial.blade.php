@extends('layouts.app')
@section('title', $config['titulo'])
@section('content')
    @php
        // Fecha y hora "d/m/Y H:i" a partir de las columnas fecha/hora del registro.
        $fechaHora = function ($datos) {
            if (empty($datos['fecha'])) {
                return '—';
            }
            $fecha = \Carbon\Carbon::parse($datos['fecha'])->format('d/m/Y');
            $hora  = !empty($datos['hora']) ? \Carbon\Carbon::parse($datos['hora'])->format('H:i') : '';
            return trim($fecha . ' ' . $hora);
        };
        $esCambioFecha = in_array($tipo, ['cambio_fecha_audiencia', 'cambio_fecha_cumplimiento'], true);
    @endphp
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">{{ $config['titulo'] }}</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            @if ($errors->any())
                                <div class="alert alert-dark alert-dismissible fade show" role="alert">
                                    <strong>¡Revise los campos!</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            <form method="GET" action="{{ route('administracion_historial', $tipo) }}">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label for="nue">NUE</label>
                                        <input type="text" class="form-control" name="nue" id="nue"
                                               placeholder="Ej. MOR/SOL/2026/00576" value="{{ request('nue') }}">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label for="usuario">Usuario</label>
                                        <select class="form-control" name="usuario" id="usuario">
                                            <option value="">Todos</option>
                                            @foreach ($usuarios as $usuario)
                                                <option value="{{ $usuario->user_id }}" @selected((string) request('usuario') === (string) $usuario->user_id)>{{ $usuario->user_nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label for="desde">Desde</label>
                                        <input type="date" class="form-control" name="desde" id="desde" value="{{ request('desde') }}">
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <label for="hasta">Hasta</label>
                                        <input type="date" class="form-control" name="hasta" id="hasta" value="{{ request('hasta') }}">
                                    </div>
                                    <div class="col-12 col-md-2 d-flex gap-1">
                                        <button type="submit" class="btn btn-primary" title="Buscar"><i class="bi bi-search"></i></button>
                                        <a href="{{ route('administracion_historial', $tipo) }}" class="btn btn-light" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                                    </div>
                                </div>
                            </form>
                            <br>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ $tipo === 'borrar_cumplimiento' ? 'Borrado el' : 'Cambiado el' }}</th>
                                            <th>NUE</th>
                                            <th>Delegación</th>
                                            @if ($tipo === 'borrar_cumplimiento')
                                                <th>Tipo</th>
                                                <th>Fecha del pago</th>
                                                <th>Monto</th>
                                                <th>Descripción</th>
                                                <th>Estatus</th>
                                            @elseif ($tipo === 'cambio_fecha_audiencia')
                                                <th>Audiencia</th>
                                            @else
                                                <th>Descripción</th>
                                            @endif
                                            @if ($esCambioFecha)
                                                <th>Fecha anterior</th>
                                                <th>Fecha nueva</th>
                                            @endif
                                            <th>Motivo</th>
                                            <th>Usuario</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($registros as $registro)
                                            @php
                                                $antes   = $registro->datos_antes ?? [];
                                                $despues = array_merge($antes, $registro->datos_despues ?? []);
                                            @endphp
                                            <tr>
                                                <td>{{ $registro->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $registro->NUE ?? '—' }}</td>
                                                <td>{{ $registro->delegacion ?? '—' }}</td>
                                                @if ($tipo === 'borrar_cumplimiento')
                                                    <td>{{ $antes['tipo_pago'] ?? '—' }}</td>
                                                    <td>{{ $fechaHora($antes) }}</td>
                                                    <td>{{ isset($antes['monto']) ? '$' . number_format((float) $antes['monto'], 2) : '—' }}</td>
                                                    <td>{{ $antes['descripcion'] ?? '—' }}</td>
                                                    <td>{{ $antes['estatus'] ?? '—' }}</td>
                                                @elseif ($tipo === 'cambio_fecha_audiencia')
                                                    <td>
                                                        {{ $antes['numero_audiencia'] ?? '—' }}
                                                        @if (!empty($antes['folio_audiencia']))
                                                            <br><small class="text-muted">{{ $antes['folio_audiencia'] }}</small>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td>{{ $antes['descripcion'] ?? '—' }}</td>
                                                @endif
                                                @if ($esCambioFecha)
                                                    <td class="text-danger">{{ $fechaHora($antes) }}</td>
                                                    <td class="text-success">{{ $fechaHora($despues) }}</td>
                                                @endif
                                                <td style="max-width: 280px; white-space: pre-line;">{{ $registro->motivo ?? 'No capturado' }}</td>
                                                <td>{{ $registro->user_nombre ?? '—' }}</td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-light"
                                                            data-bs-toggle="collapse" data-bs-target="#registro-{{ $registro->id }}">
                                                        <i class="bi bi-list-ul"></i> Ver todo
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr class="collapse" id="registro-{{ $registro->id }}">
                                                <td colspan="20">
                                                    <table class="table table-sm mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th class="w-25">Campo</th>
                                                                <th>Antes</th>
                                                                <th>Después</th>
                                                            </tr>
                                                        </thead>
                                                        @foreach ($antes as $columna => $valor)
                                                            @php $cambio = array_key_exists($columna, $registro->datos_despues ?? []); @endphp
                                                            <tr>
                                                                <th>{{ ucfirst(str_replace('_', ' ', $columna)) }}</th>
                                                                <td @class(['text-danger' => $cambio])>{{ $valor ?? '—' }}</td>
                                                                <td @class(['text-success' => $cambio])>{{ $cambio ? ($registro->datos_despues[$columna] ?? '—') : '' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="20" class="text-center text-muted">No hay registros con esos filtros.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $registros->links('pagination::bootstrap-4') }}

                            <br>
                            <a href="{{ route($config['regresar']) }}" class="btn btn-warning">Regresar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
