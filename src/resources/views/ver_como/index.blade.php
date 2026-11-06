@extends('layouts.app')
@section('title', 'Ver como')

{{--
    Administración → "Ver como" (VerComoController, App\Support\VerComo).
    Sólo Super Usuario. Elige una persona o un rol con su sede y navega el
    sistema como lo ve, en sólo lectura.
--}}

@section('page_css')
    <style>
        .vc-intro { color: #5B6B6C; font-size: 14px; max-width: 900px; }
        .vc-actual {
            display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;
            padding: 12px 16px; border-radius: 10px; background: #FBF7EA; border: 1px solid #EBDDB0; margin-bottom: 16px;
        }
        .vc-grid { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 16px; align-items: start; }
        @media (max-width: 1100px) { .vc-grid { grid-template-columns: 1fr; } }
        .vc-tarjeta { background: #fff; border: 1px solid #E3E8E8; border-radius: 12px; padding: 16px; }
        .vc-tarjeta h5 { font-size: 16px; font-weight: 700; color: #2E3C3D; margin: 0 0 4px; }
        .vc-tarjeta > p { font-size: 13px; color: #6C7A7B; margin: 0 0 12px; }
        .vc-filtros { display: grid; grid-template-columns: minmax(0, 2fr) repeat(2, minmax(0, 1fr)) auto; gap: 8px; align-items: end; margin-bottom: 10px; }
        @media (max-width: 760px) { .vc-filtros { grid-template-columns: 1fr 1fr; } }
        .vc-filtros label, .vc-rol label.form-label { font-size: 12px; font-weight: 700; color: #5B6B6C; margin-bottom: 3px; }
        .vc-tabla td { font-size: 13.5px; vertical-align: middle; }
        .vc-rolchip { display: inline-block; padding: 1px 8px; margin: 1px 2px 1px 0; border-radius: 999px; background: #EEF2F2; color: #496163; font-size: 11.5px; font-weight: 700; }
        .vc-inactivo { color: #A3162E; font-size: 11.5px; font-weight: 700; }
        .vc-roles { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 2px 10px; max-height: 330px; overflow-y: auto; padding: 8px 10px; border: 1px solid #E3E8E8; border-radius: 8px; }
        .vc-roles .form-check-label { font-size: 13.5px; }
        .vc-nota { font-size: 12.5px; color: #6C7A7B; margin: 10px 0 0; }
    </style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h3 class="page__heading"><i class="bi bi-eye me-2" aria-hidden="true"></i>Ver como</h3>
    </div>
    <div class="section-body">
        @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $color)
            @if (session($clave))
                <div class="alert alert-{{ $color }} alert-dismissible fade show" role="alert">
                    {{ session($clave) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <p class="vc-intro">
            Navega el sistema como lo ve otra persona: el menú, los permisos, la sede y las pantallas, tal cual.
            Es de <b>sólo lectura</b>: mientras estés en "Ver como" no se guarda nada. Para salir usa el aviso de abajo de la pantalla.
        </p>

        @if ($actual)
            <div class="vc-actual">
                <span><i class="bi bi-eye-fill me-1" aria-hidden="true"></i> Estás viendo como <b>{{ $actual }}</b>.</span>
                <span class="d-flex gap-2">
                    <a href="{{ route('inicio') }}" class="btn btn-sm btn-outline-secondary">Ir al inicio</a>
                    <form method="POST" action="{{ route('ver_como.salir') }}" class="m-0">@csrf
                        <button class="btn btn-sm btn-dark"><i class="bi bi-x-circle me-1"></i>Salir de "Ver como"</button>
                    </form>
                </span>
            </div>
        @endif

        <div class="vc-grid">
            {{-- Una persona en específico --}}
            <div class="vc-tarjeta">
                <h5><i class="bi bi-person me-1" aria-hidden="true"></i> Una persona</h5>
                <p>Sus roles, permisos, sede, módulos y registros, tal como los ve hoy.</p>

                <form method="GET" class="vc-filtros">
                    <div>
                        <label for="vcQ">Nombre o correo</label>
                        <input type="search" id="vcQ" name="q" value="{{ $f['q'] }}" class="form-control form-control-sm" placeholder="Buscar…">
                    </div>
                    <div>
                        <label for="vcRolF">Rol</label>
                        <select id="vcRolF" name="rol" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @foreach ($roles as $r)<option value="{{ $r }}" @selected($f['rol'] === $r)>{{ $r }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="vcSedeF">Sede</label>
                        <select id="vcSedeF" name="sede" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach ($sedes as $s)<option value="{{ $s }}" @selected($f['sede'] === $s)>{{ $s }}</option>@endforeach
                        </select>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="vcInactivos" name="inactivos" value="1" @checked($f['inactivos']) onchange="this.form.submit()">
                            <label class="form-check-label small" for="vcInactivos">Inactivos</label>
                        </div>
                        <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Buscar</button>
                    </div>
                </form>

                @if ($usuarios->isEmpty())
                    <p class="text-muted mb-0">No hay personas con esos filtros.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm vc-tabla mb-2">
                            <thead><tr><th>Persona</th><th>Roles</th><th>Sede</th><th></th></tr></thead>
                            <tbody>
                            @foreach ($usuarios as $u)
                                <tr>
                                    <td>
                                        <b>{{ \Illuminate\Support\Str::title(mb_strtolower($u->name)) }}</b>
                                        @if ($u->estatus !== 'Activo') <span class="vc-inactivo">· Inactivo</span> @endif
                                        <div class="small text-muted">{{ $u->email }}</div>
                                    </td>
                                    <td>@forelse ($u->roles as $r)<span class="vc-rolchip">{{ $r->name }}</span>@empty<span class="text-muted small">Sin rol</span>@endforelse</td>
                                    <td>{{ $u->delegacion ?: '—' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('ver_como.iniciar') }}" class="m-0">@csrf
                                            <input type="hidden" name="tipo" value="usuario">
                                            <input type="hidden" name="user_id" value="{{ $u->id }}">
                                            <button class="btn btn-sm btn-outline-primary text-nowrap"><i class="bi bi-eye me-1"></i>Ver como</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $usuarios->onEachSide(1)->links('pagination::bootstrap-5') }}
                @endif
            </div>

            {{-- Un rol con su sede --}}
            <div class="vc-tarjeta vc-rol">
                <h5><i class="bi bi-people me-1" aria-hidden="true"></i> Un rol</h5>
                <p>Un usuario de prueba con sólo esos roles y esa sede. Para un auxiliar, marca "Auxiliar" y su especialidad (p. ej. "Auxiliar Ratificaciones").</p>

                <form method="POST" action="{{ route('ver_como.iniciar') }}">
                    @csrf
                    <input type="hidden" name="tipo" value="rol">
                    <label class="form-label">Roles</label>
                    <div class="vc-roles">
                        @foreach ($roles as $i => $r)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r }}" id="vcRol{{ $i }}" @checked(in_array($r, (array) old('roles', []), true))>
                                <label class="form-check-label" for="vcRol{{ $i }}">{{ $r }}</label>
                            </div>
                        @endforeach
                    </div>
                    <label class="form-label mt-3" for="vcSede">Sede</label>
                    <select id="vcSede" name="sede" class="form-select" required>
                        <option value="">Elige la sede…</option>
                        @foreach ($sedes as $s)<option value="{{ $s }}" @selected(old('sede') === $s)>{{ $s }}</option>@endforeach
                    </select>
                    <button class="btn btn-primary w-100 mt-3"><i class="bi bi-eye me-1"></i>Ver como este rol</button>
                    <p class="vc-nota">
                        <i class="bi bi-info-circle" aria-hidden="true"></i> Lo que depende de la persona (su módulo en "Mi módulo", "mis" ratificaciones o solicitudes)
                        sale vacío en un rol de prueba; para verlo, usa una persona en específico.
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
