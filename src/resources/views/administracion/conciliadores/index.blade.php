@extends('layouts.app')
@section('title', 'Conciliadores')

{{--
    Administración → Conciliadores (ConciliadoresController@index, sólo Super
    Usuario). La semana de cada conciliador: en qué sede atiende cada día,
    presencial (relleno) o virtual (contorno punteado), y su horario. Sale de
    permisos_conciliador (App\Support\SedesConciliador).
--}}

@php
    use App\Support\SedesConciliador as SC;
    use App\Models\PermisosConciliador;
@endphp

@section('page_css')
    @include('administracion.conciliadores._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('configuracion') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Administración
                </a>
                <h3 class="page__heading mb-0">Conciliadores</h3>
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

            <div class="cn-cifras mb-3">
                <div class="cn-cifra"><b>{{ $cifras['total'] }}</b><span>conciliadores {{ empty($filtros['inactivos']) ? 'activos' : '' }}</span></div>
                <div class="cn-cifra cn-cifra--multi"><b>{{ $cifras['multisede'] }}</b><span><i class="bi bi-diagram-3-fill" style="color:#A9842A;"></i> con agenda múltiple</span></div>
                <div class="cn-cifra"><b>{{ $cifras['fuera'] }}</b><span>atienden fuera de su región</span></div>
                <div class="cn-cifra {{ $cifras['sinAgenda'] ? 'cn-cifra--alerta' : '' }}"><b>{{ $cifras['sinAgenda'] }}</b><span>sin agenda: no reciben audiencias</span></div>
            </div>

            <div class="tn-tarjeta mb-3">
                <form method="GET" action="{{ route('conciliadores.index') }}" class="cn-filtros">
                    <nav class="cn-sedes" aria-label="Sede">
                        <a class="cn-sede-filtro {{ empty($filtros['sede']) ? 'is-activa' : '' }}"
                           href="{{ route('conciliadores.index', array_filter(['buscar' => $filtros['buscar'] ?? null, 'inactivos' => $filtros['inactivos'] ?? null])) }}">Todas</a>
                        @foreach (SC::SEDES as $s)
                            <a class="cn-sede-filtro {{ ($filtros['sede'] ?? '') === $s ? 'is-activa' : '' }}"
                               href="{{ route('conciliadores.index', array_filter(['sede' => $s, 'buscar' => $filtros['buscar'] ?? null, 'inactivos' => $filtros['inactivos'] ?? null])) }}">{{ $s }}</a>
                        @endforeach
                    </nav>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        @if (! empty($filtros['sede']))<input type="hidden" name="sede" value="{{ $filtros['sede'] }}">@endif
                        <input type="search" name="buscar" class="form-control form-control-sm" style="width: 200px;" placeholder="Buscar por nombre" value="{{ $filtros['buscar'] ?? '' }}">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="cnInactivos" name="inactivos" value="1" @checked(! empty($filtros['inactivos'])) onchange="this.form.submit()">
                            <label class="form-check-label small" for="cnInactivos">Incluir inactivos</label>
                        </div>
                        <button class="btn btn-sm tn-boton-dorado" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="tn-tarjeta">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div class="cn-leyenda">
                        @foreach (SC::COLORES as $s => $c)
                            <span><span class="cn-punto" style="background: {{ $c }}"></span>{{ $s }}</span>
                        @endforeach
                        <span class="ms-2"><span class="cn-bloque d-inline-block" style="--c:#496163;">Presencial</span></span>
                        <span><span class="cn-bloque cn-bloque--virtual d-inline-block" style="--c:#496163;">Virtual</span></span>
                    </div>
                    <span class="cn-sub">Los documentos de cada audiencia salen de la sede donde se celebra.</span>
                </div>

                <div class="table-responsive">
                    <table class="cn-tabla">
                        <thead>
                            <tr>
                                <th>Conciliador</th>
                                @foreach (PermisosConciliador::DIAS as $dia => $corto)
                                    <th class="cn-dia">{{ $corto }}</th>
                                @endforeach
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($filas as $fila)
                                @php $u = $fila->usuario; @endphp
                                <tr>
                                    <td>
                                        <div class="cn-nombre">{{ $u->name }}</div>
                                        <div class="cn-sub">Adscrito a {{ $u->delegacion ?: 'sin sede' }}@if ($u->estatus !== 'Activo') · <strong>{{ $u->estatus }}</strong>@endif</div>
                                        <div class="cn-insignias">
                                            @if ($fila->multisede)<span class="cn-insignia cn-insignia--multi" title="{{ SC::resumen($u) }}"><i class="bi bi-diagram-3-fill"></i> Agenda múltiple</span>@endif
                                            @if ($fila->fuera)<span class="cn-insignia cn-insignia--fuera">Fuera de su región</span>@endif
                                            @if ($fila->sinAgenda)<span class="cn-insignia cn-insignia--sin">Sin agenda</span>@endif
                                            <span class="cn-insignia cn-insignia--sede">{{ $fila->porSede ? 'Por sede' : 'La de siempre' }}</span>
                                        </div>
                                    </td>
                                    @foreach (PermisosConciliador::DIAS as $dia => $corto)
                                        <td class="cn-dia">
                                            @forelse ($fila->semana[$dia] as $b)
                                                <span class="cn-bloque {{ $b['modalidad'] === SC::VIRTUAL ? 'cn-bloque--virtual' : '' }}"
                                                      style="--c: {{ SC::COLORES[$b['sede']] ?? '#496163' }};"
                                                      title="{{ $b['sede'] }} · {{ SC::MODALIDADES[$b['modalidad']] }} · {{ $b['inicio'] }}–{{ $b['fin'] }}">
                                                    {{ $b['sede'] }} <small>{{ $b['inicio'] }}–{{ $b['fin'] }}</small>
                                                </span>
                                            @empty
                                                <span class="cn-vacio">—</span>
                                            @endforelse
                                        </td>
                                    @endforeach
                                    <td class="text-end">
                                        <a href="{{ route('conciliadores.editar', $u->id) }}" class="btn btn-sm btn-outline-secondary" title="Editar sedes y horario">
                                            <i class="bi bi-pencil-square"></i><span class="d-none d-xl-inline"> Editar</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center cn-sub py-4">No hay conciliadores con ese filtro.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($historial->isNotEmpty())
                <div class="tn-tarjeta mt-3">
                    <h5 class="mb-2" style="font-size: 15px; font-weight: 700; color: var(--tn-tinta);">Últimos cambios de agenda</h5>
                    @include('administracion.conciliadores._historial', ['historial' => $historial, 'nombres' => $nombres])
                </div>
            @endif
        </div>
    </section>
@endsection
