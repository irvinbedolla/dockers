@extends('layouts.app')
@section('title', 'Cumplimientos')

@php
    $claseEstatus = fn (?string $e) => $e === 'Pagado' ? 'tn-estatus--confirmada' : 'tn-estatus--pendiente';
    // El apartado, la pestaña, la búsqueda y la fecha se filtran en el servidor
    // (SeerController::audiencias_cumplimiento); cada liga conserva los demás filtros.
    $urlCon = fn (array $cambios) => url()->current().'?'.http_build_query(array_filter(
        array_merge(request()->only(['buscar', 'fecha', 'estatus', 'tipo']), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $apartadosVista = [
        'Pendiente' => ['Pendientes', 'bi-hourglass-split', 'cp-i-pendiente'],
        'Pagado'    => ['Pagados', 'bi-cash-coin', 'cp-i-pagado'],
    ];
    $pestanas = [
        'RAT' => ['Ratificaciones', 'bi-file-earmark-check'],
        'SOL' => ['Solicitudes', 'bi-file-earmark-text'],
    ];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .rt-cifras { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; max-width: 640px; }
        .rt-cifra {
            display: flex; align-items: center; gap: 12px; padding: 14px 16px; text-decoration: none;
            background: #fff; border: 1px solid var(--tn-borde); border-radius: 12px;
            transition: border-color .15s, box-shadow .15s;
        }
        .rt-cifra:hover { border-color: var(--tn-verde-claro); }
        .rt-cifra.is-activa { border-color: var(--tn-verde); box-shadow: 0 0 0 3px rgba(73, 97, 99, .12); }
        .rt-cifra__icono { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; font-size: 19px; flex: 0 0 auto; }
        .rt-cifra__num { display: block; font-size: 22px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .rt-cifra__txt { font-size: 12.5px; color: var(--tn-suave); }
        .cp-i-pendiente { background: #EEF0F3; color: #5F6B7A; }
        .cp-i-pagado { background: #E5F3EA; color: #1E7B3C; }
        @media (max-width: 640px) { .rt-cifras { grid-template-columns: 1fr; } }

        .rt-filtros { display: grid; grid-template-columns: minmax(0, 1fr) 200px auto; gap: 12px; align-items: end; }
        @media (max-width: 768px) { .rt-filtros { grid-template-columns: 1fr; } }
        .rt-filtros label { display: block; font-size: 11.5px; font-weight: 600; color: var(--tn-tenue); margin-bottom: 4px; }
        .rt-buscar { position: relative; }
        .rt-buscar i { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--tn-tenue); }
        .rt-buscar input { padding-right: 36px; }

        /* Pestañas RAT / SOL sobre la tabla. */
        .cp-pestanas { display: flex; gap: 4px; border-bottom: 1px solid var(--tn-borde); margin-bottom: 14px; overflow-x: auto; }
        .cp-pestana {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; margin-bottom: -1px;
            border-bottom: 3px solid transparent; color: var(--tn-suave); font-size: 14px; font-weight: 600;
            text-decoration: none; white-space: nowrap;
        }
        .cp-pestana:hover { color: var(--tn-verde); }
        .cp-pestana.is-activa { color: var(--tn-verde); border-bottom-color: var(--tn-dorado); }
        .cp-pestana__clave { font-size: 11px; font-weight: 700; letter-spacing: .04em; padding: 2px 6px; border-radius: 6px; background: #EEF2F2; color: var(--tn-verde); }
        .cp-pestana__num { font-size: 12px; font-weight: 700; padding: 1px 8px; border-radius: 999px; background: #EEF0F3; color: var(--tn-suave); font-variant-numeric: tabular-nums; }
        .cp-pestana.is-activa .cp-pestana__num { background: var(--tn-verde); color: #fff; }

        .rt-encabezado-dia { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 4px; }
        .rt-encabezado-dia h4 { margin: 0; font-size: 18px; font-weight: 700; color: var(--tn-tinta); }
        .rt-encabezado-dia span { font-size: 13px; color: var(--tn-tenue); }

        /* Encabezado oscuro de Todos los turnos. Con .tn delante para ganarle a
           .table:not(.table-sm) thead th de style.css. */
        .tn-tabla { margin: 0; }
        .tn .table.tn-tabla > :not(caption) > * > th,
        .tn .table.tn-tabla > :not(caption) > * > td { padding: 10px 12px; height: auto; }
        .tn .table.tn-tabla thead th {
            background: #354647; --bs-table-bg: #354647; color: #fff !important;
            font-size: 12px; font-weight: 600; letter-spacing: .03em; white-space: nowrap; border: 0;
        }
        .tn-tabla td { vertical-align: middle; font-size: 13.5px; color: var(--tn-tinta); }

        .rt-nue { font-weight: 700; color: var(--tn-verde); white-space: nowrap; }
        .tn-tabla .btn-sm { white-space: nowrap; font-weight: 600; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header mb-3">
            <h3 class="page__heading mb-0">Cumplimientos</h3>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="rt-cifras">
                @foreach ($apartadosVista as $clave => [$texto, $icono, $tono])
                    <a class="rt-cifra {{ $estatus === $clave ? 'is-activa' : '' }}"
                       href="{{ $urlCon(['estatus' => $clave, 'page' => null]) }}"
                       @if ($estatus === $clave) aria-current="true" @endif>
                        <span class="rt-cifra__icono {{ $tono }}"><i class="bi {{ $icono }}"></i></span>
                        <span><span class="rt-cifra__num">{{ number_format($conteo[$clave]) }}</span><span class="rt-cifra__txt">{{ $texto }}</span></span>
                    </a>
                @endforeach
            </div>

            <div class="tn-tarjeta">
                {{-- GET: la URL guarda la vista y se puede compartir. --}}
                <form action="{{ url()->current() }}" method="GET">
                    <input type="hidden" name="estatus" value="{{ $estatus }}">
                    <input type="hidden" name="tipo" value="{{ $tipo }}">

                    <div class="rt-filtros">
                        <div>
                            <label for="cpBuscar">Buscar</label>
                            <div class="rt-buscar">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <input type="search" id="cpBuscar" name="buscar" class="form-control" placeholder="NUE (ej. MOR/RAT/2026/00576)" value="{{ request('buscar') }}">
                            </div>
                        </div>

                        <div>
                            <label for="cpFecha">Fecha</label>
                            <input type="date" id="cpFecha" name="fecha" class="form-control" value="{{ $fecha }}">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                            @if (request('buscar') || $fecha)
                                <a href="{{ $urlCon(['buscar' => null, 'fecha' => null, 'page' => null]) }}" class="btn btn-outline-secondary" title="Quitar búsqueda y fecha">Limpiar</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="tn-tarjeta mt-3">
                <nav class="cp-pestanas" aria-label="Tipo de expediente">
                    @foreach ($pestanas as $clave => [$texto, $icono])
                        <a class="cp-pestana {{ $tipo === $clave ? 'is-activa' : '' }}"
                           href="{{ $urlCon(['tipo' => $clave, 'page' => null]) }}"
                           @if ($tipo === $clave) aria-current="page" @endif>
                            <span class="cp-pestana__clave">{{ $clave }}</span>
                            {{ $texto }}
                            <span class="cp-pestana__num">{{ number_format($conteoTipo[$clave]) }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="rt-encabezado-dia" aria-live="polite">
                    <h4>{{ $apartadosVista[$estatus][0] }} · {{ $pestanas[$tipo][0] }}</h4>
                    <span>
                        {{ number_format($cumplimientos->total()) }} {{ $cumplimientos->total() === 1 ? 'cumplimiento' : 'cumplimientos' }}@if(request('buscar')) que coinciden con "{{ request('buscar') }}"@endif @if($fecha) del {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}@endif
                    </span>
                </div>

                <div class="table-responsive mt-2">
                    <table class="table table-hover tn-tabla w-100">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Número de Expediente</th>
                                <th>Tipo</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-center">Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cumplimientos as $audiencia)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $audiencia->fecha_formateada }}</td>
                                    <td style="white-space: nowrap;">{{ $audiencia->hora_formateada }}</td>
                                    <td><span class="rt-nue">{{ $audiencia->NUE_FINAL }}</span></td>
                                    <td>{{ $audiencia->tipo_pago }}</td>
                                    <td class="text-center">
                                        <span class="tn-estatus {{ $claseEstatus($audiencia->estatus) }}">{{ $audiencia->estatus }}</span>
                                    </td>
                                    <td class="text-center">
                                        @can('cumplimientos_consultar')
                                            @if ($audiencia->tipo_pago === 'Audiencia')
                                                <a class="btn tn-boton-dorado btn-sm" href="{{ route('pago_cumplimiento', $audiencia->id) }}"><i class="bi bi-receipt me-1"></i> Cumplimiento</a>
                                            @else
                                                <a class="btn tn-boton-dorado btn-sm" href="{{ route('ratificacion_cumplimientos', $audiencia->id_solicitud) }}"><i class="bi bi-receipt me-1"></i> Cumplimiento</a>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4" style="color: var(--tn-tenue);">No hay cumplimientos con estos filtros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($cumplimientos->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $cumplimientos->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('body_end')
<div id="nuevo_usuario" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/usuarios/usuarios.js') }}"></script>
@endsection
