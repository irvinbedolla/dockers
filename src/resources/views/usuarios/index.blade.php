@extends('layouts.app')
@section('title', 'Usuarios')

{{--
    Usuarios del personal (sin solicitantes). El filtro y el orden se hacen en
    el navegador: son ~150 renglones. El interruptor de estado manda el PATCH
    a usuarios_estatus (AdministracionController::cambiarEstatus), que ya
    impide desactivarse a uno mismo y revisa el permiso usuarios_editar.
--}}

@php
    use App\Support\FechaLegible;
    use Illuminate\Support\Str;

    $bonito = fn (?string $n) => $n ? Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim($n)))) : '';
    $iniciales = function (?string $n) {
        $p = preg_split('/\s+/', trim((string) $n));
        return mb_strtoupper(mb_substr($p[0] ?? '', 0, 1).mb_substr($p[1] ?? '', 0, 1));
    };
    $ahora = now();
    $puedeEditar = auth()->user()->can('usuarios_editar');
    $puedeEliminar = auth()->user()->can('usuarios_eliminar');
    // Sólo un Super Usuario modifica a otro Super Usuario (UsuarioController::autorizar).
    $soySuper = auth()->user()->hasRole('Super Usuario');
    $puedeTocar = fn ($u) => $soySuper || ! $u->roles->contains('name', 'Super Usuario');
    $modalPassword = session('modal_password');
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .us-cifras { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
        .us-cifra {
            display: flex; align-items: center; gap: 12px; padding: 14px 16px; text-align: left;
            background: #fff; border: 1px solid var(--tn-borde); border-radius: 12px; cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }
        .us-cifra:hover { border-color: var(--tn-verde-claro); }
        .us-cifra.is-activa { border-color: var(--tn-verde); box-shadow: 0 0 0 3px rgba(73, 97, 99, .12); }
        .us-cifra__icono { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; font-size: 19px; flex: 0 0 auto; }
        .us-cifra__num { display: block; font-size: 22px; font-weight: 800; color: var(--tn-tinta); line-height: 1.1; font-variant-numeric: tabular-nums; }
        .us-cifra__txt { font-size: 12.5px; color: var(--tn-suave); }
        .us-i-activo { background: #E5F3EA; color: #1E7B3C; }
        .us-i-inactivo { background: #F6E4EA; color: #8C1D40; }
        .us-i-sin { background: #FBF4E1; color: #8A6A12; }

        .us-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .us-buscar { position: relative; flex: 1 1 260px; }
        .us-buscar i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--tn-tenue); }
        .us-buscar input { padding-left: 36px; }
        .us-filtros .form-select { flex: 0 1 190px; width: auto; }
        .us-segmento { display: inline-flex; border: 1px solid var(--tn-borde); border-radius: 8px; overflow: hidden; }
        .us-segmento button { border: 0; background: #fff; padding: 6px 12px; font-size: 13px; font-weight: 600; color: var(--tn-suave); }
        .us-segmento button + button { border-left: 1px solid var(--tn-borde); }
        .us-segmento button.is-activo { background: var(--tn-verde); color: #fff; }
        .us-conteo { font-size: 12.5px; color: var(--tn-tenue); margin: 12px 2px 8px; }

        .us-tabla { width: 100%; border-collapse: separate; border-spacing: 0; }
        .us-tabla th {
            font-size: 11.5px; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue);
            font-weight: 700; padding: 10px 14px; border-bottom: 1px solid var(--tn-borde); background: #FAFBFB; white-space: nowrap;
        }
        .us-tabla td { padding: 12px 14px; border-bottom: 1px solid var(--tn-borde); vertical-align: middle; font-size: 13.5px; }
        .us-tabla tbody tr:hover { background: #FAFBFB; }
        .us-tabla tr.is-inactivo .us-persona, .us-tabla tr.is-inactivo .us-roles, .us-tabla tr.is-inactivo .us-sede { opacity: .55; }

        .us-persona { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .us-avatar { width: 38px; height: 38px; border-radius: 50%; flex: 0 0 auto; object-fit: cover; border: 1px solid var(--tn-borde); }
        .us-avatar--letras { display: grid; place-items: center; background: #E7ECEC; color: var(--tn-verde); font-size: 13px; font-weight: 700; }
        .us-nombre { display: block; font-weight: 700; color: var(--tn-tinta); line-height: 1.25; }
        .us-correo { display: block; font-size: 12.5px; color: var(--tn-tenue); overflow-wrap: anywhere; }
        .us-tu { font-size: 11px; font-weight: 700; color: var(--tn-dorado); margin-left: 4px; }

        .us-roles { display: flex; flex-wrap: wrap; gap: 4px; }
        .us-rol { padding: 2px 8px; border-radius: 6px; background: #EEF2F2; color: var(--tn-verde); font-size: 12px; font-weight: 700; white-space: nowrap; }
        .us-rol--sinotifico { background: #FBF3DC; color: #8A6A12; }

        .us-acceso { display: flex; gap: 8px; align-items: flex-start; }
        .us-punto { width: 8px; height: 8px; border-radius: 50%; margin-top: 6px; flex: 0 0 auto; }
        .us-punto--reciente { background: #2E9E57; }
        .us-punto--medio { background: #D6A21E; }
        .us-punto--viejo { background: #A9B4B5; }
        .us-punto--nunca { background: #C2577A; }
        .us-acceso__texto { line-height: 1.3; }
        .us-acceso__hace { display: block; font-size: 12px; color: var(--tn-tenue); }

        .us-estado { display: flex; align-items: center; gap: 8px; margin: 0; }
        .us-estado .form-check-input { width: 2.4em; height: 1.25em; cursor: pointer; margin: 0; }
        .us-estado .form-check-input:checked { background-color: #2E9E57; border-color: #2E9E57; }
        .us-estado .form-check-input:disabled { cursor: not-allowed; }
        .us-estado__txt { font-size: 12.5px; font-weight: 700; min-width: 56px; }
        .us-estado__txt--activo { color: #1E7B3C; }
        .us-estado__txt--inactivo { color: #8C1D40; }

        .us-acciones { display: flex; gap: 6px; justify-content: flex-end; }
        .us-acciones .btn { width: 34px; height: 34px; padding: 0; display: grid; place-items: center; }
        .us-vacio { text-align: center; padding: 40px 16px; color: var(--tn-tenue); }
        .us-vacio i { font-size: 30px; display: block; margin-bottom: 6px; }

        @media (max-width: 860px) {
            .us-cifras { grid-template-columns: 1fr; }
            .us-tabla thead { display: none; }
            .us-tabla, .us-tabla tbody, .us-tabla tr, .us-tabla td { display: block; width: 100%; }
            .us-tabla tr {
                display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 8px 12px;
                padding: 14px; border-bottom: 1px solid var(--tn-borde);
            }
            .us-tabla td { padding: 0; border: 0; }
            .us-tabla td.us-c-persona { grid-column: 1 / -1; }
            .us-tabla td.us-c-roles, .us-tabla td.us-c-acceso { grid-column: 1 / -1; }
            .us-tabla td.us-c-sede::before { content: 'Sede: '; color: var(--tn-tenue); }
            .us-tabla td.us-c-acceso::before { content: 'Último acceso'; display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); font-weight: 700; margin-bottom: 2px; }
            .us-acciones { justify-content: flex-start; }
        }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="page__heading mb-0">Usuarios</h3>
                <span class="small" style="color: var(--tn-tenue);">Personal con acceso a SICONCILIO</span>
            </div>
            @can('usuarios_crear')
                <a class="btn tn-boton-dorado" href="{{ route('usuarios.create') }}" onclick="crear_usuario();">
                    <i class="bi bi-person-plus me-1"></i> Nuevo usuario
                </a>
            @endcan
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
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="us-cifras">
                <button type="button" class="us-cifra" data-atajo="Activo">
                    <span class="us-cifra__icono us-i-activo"><i class="bi bi-person-check"></i></span>
                    <span><span class="us-cifra__num">{{ $conteo['activos'] }}</span><span class="us-cifra__txt">Activos</span></span>
                </button>
                <button type="button" class="us-cifra" data-atajo="Inactivo">
                    <span class="us-cifra__icono us-i-inactivo"><i class="bi bi-person-slash"></i></span>
                    <span><span class="us-cifra__num">{{ $conteo['inactivos'] }}</span><span class="us-cifra__txt">Inactivos</span></span>
                </button>
                <button type="button" class="us-cifra" data-atajo="sin">
                    <span class="us-cifra__icono us-i-sin"><i class="bi bi-clock-history"></i></span>
                    <span><span class="us-cifra__num">{{ $conteo['sin_entrar'] }}</span><span class="us-cifra__txt">Activos sin entrar en 30 días</span></span>
                </button>
            </div>

            <div class="tn-tarjeta">
                <div class="us-filtros">
                    <label class="us-buscar mb-0">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" class="form-control" id="usBuscar" placeholder="Buscar por nombre o correo" aria-label="Buscar por nombre o correo">
                    </label>
                    <select class="form-select" id="usRol" aria-label="Rol">
                        <option value="">Todos los roles</option>
                        @foreach ($roles as $rol)<option value="{{ $rol }}">{{ $rol }}</option>@endforeach
                    </select>
                    <select class="form-select" id="usSede" aria-label="Sede">
                        <option value="">Todas las sedes</option>
                        @foreach ($sedes as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                    </select>
                    <div class="us-segmento" role="group" aria-label="Estado">
                        <button type="button" data-estado="" class="is-activo">Todos</button>
                        <button type="button" data-estado="Activo">Activos</button>
                        <button type="button" data-estado="Inactivo">Inactivos</button>
                    </div>
                    <select class="form-select" id="usOrden" aria-label="Ordenar">
                        <option value="nombre">Orden: nombre</option>
                        <option value="reciente">Entró más reciente</option>
                        <option value="antiguo">Más tiempo sin entrar</option>
                    </select>
                </div>

                <p class="us-conteo" id="usConteo" aria-live="polite"></p>

                <div class="table-responsive-md">
                    <table class="us-tabla" id="usTabla">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Sede</th>
                                <th>Último acceso</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $u)
                                @php
                                    $activo  = $u->estatus === 'Activo';
                                    $acceso  = $u->last_login_at;
                                    $dias    = $acceso ? (int) floor(abs($acceso->diffInDays($ahora))) : null;
                                    $punto   = $acceso === null ? 'nunca' : ($dias <= 7 ? 'reciente' : ($dias <= 30 ? 'medio' : 'viejo'));
                                    $soyYo   = (int) $u->id === (int) auth()->id();
                                    $roles   = $u->roles->pluck('name');
                                @endphp
                                <tr class="{{ $activo ? '' : 'is-inactivo' }}"
                                    data-texto="{{ mb_strtolower($u->name.' '.$u->email) }}"
                                    data-roles="|{{ $roles->implode('|') }}|"
                                    data-sede="{{ $u->delegacion }}"
                                    data-estatus="{{ $u->estatus }}"
                                    data-nombre="{{ mb_strtolower($u->name) }}"
                                    data-acceso="{{ $acceso ? $acceso->timestamp : 0 }}"
                                    data-sin="{{ $activo && ($acceso === null || $dias > 30) ? 1 : 0 }}">
                                    <td class="us-c-persona">
                                        <div class="us-persona">
                                            @if ($u->tieneFotoDePerfil())
                                                <img class="us-avatar" src="{{ $u->avatar_url }}" alt="" width="38" height="38" loading="lazy">
                                            @else
                                                <span class="us-avatar us-avatar--letras" aria-hidden="true">{{ $iniciales($u->name) }}</span>
                                            @endif
                                            <span style="min-width: 0;">
                                                <span class="us-nombre">{{ $bonito($u->name) }}@if ($soyYo)<span class="us-tu">(tú)</span>@endif</span>
                                                <span class="us-correo">{{ $u->email }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="us-c-roles">
                                        <div class="us-roles">
                                            @foreach ($roles as $rol)<span class="us-rol">{{ $rol }}</span>@endforeach
                                            @if ($u->permissions->contains('name', \App\Support\Sinotifico\Acceso::SUPERVISAR_SEDE))
                                                <span class="us-rol us-rol--sinotifico" title="Supervisa a los notificadores de su sede en SíNotifico">SíNotifico · Supervisa</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="us-c-sede"><span class="us-sede">{{ $u->delegacion }}</span></td>
                                    <td class="us-c-acceso">
                                        <div class="us-acceso" @if ($acceso) title="{{ $acceso->format('d/m/Y H:i') }}{{ $u->last_login_ip ? ' · IP '.$u->last_login_ip : '' }}" @endif>
                                            <span class="us-punto us-punto--{{ $punto }}" aria-hidden="true"></span>
                                            <span class="us-acceso__texto">
                                                {{ FechaLegible::conHora($acceso, 'Nunca ha entrado') }}
                                                @if ($acceso && ! $acceso->isToday() && ! $acceso->isYesterday())
                                                    <span class="us-acceso__hace">{{ $acceso->locale('es')->diffForHumans() }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td class="us-c-estado">
                                        @if ($puedeEditar && $puedeTocar($u))
                                            <form method="POST" action="{{ route('usuarios_estatus', $u->id) }}" class="us-estado" data-estado-form>
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="estatus" value="{{ $activo ? 'Inactivo' : 'Activo' }}">
                                                <input class="form-check-input" type="checkbox" role="switch" {{ $activo ? 'checked' : '' }}
                                                       {{ $soyYo ? 'disabled' : '' }}
                                                       aria-label="{{ $activo ? 'Desactivar' : 'Activar' }} a {{ $bonito($u->name) }}"
                                                       title="{{ $soyYo ? 'No puedes desactivar tu propia cuenta' : ($activo ? 'Desactivar cuenta' : 'Activar cuenta') }}">
                                                <span class="us-estado__txt us-estado__txt--{{ $activo ? 'activo' : 'inactivo' }}">{{ $activo ? 'Activo' : 'Inactivo' }}</span>
                                            </form>
                                        @else
                                            <span class="us-estado__txt us-estado__txt--{{ $activo ? 'activo' : 'inactivo' }}">{{ $activo ? 'Activo' : 'Inactivo' }}</span>
                                        @endif
                                    </td>
                                    <td class="us-c-acciones">
                                        <div class="us-acciones">
                                            @if ($puedeEditar && $puedeTocar($u))
                                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('usuarios.edit', $u->id) }}" onclick="editar_usuario();" title="Editar">
                                                    <i class="bi bi-pencil"></i><span class="visually-hidden">Editar</span>
                                                </a>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" title="Cambiar contraseña"
                                                        data-bs-toggle="modal" data-bs-target="#usModalPassword"
                                                        data-accion="{{ route('usuarios.password', $u->id) }}" data-nombre="{{ $bonito($u->name) }}" data-correo="{{ $u->email }}">
                                                    <i class="bi bi-key"></i><span class="visually-hidden">Cambiar contraseña</span>
                                                </button>
                                            @endif
                                            @if ($puedeEliminar && ! $soyYo && $puedeTocar($u))
                                                <button type="button" class="btn btn-outline-danger btn-sm" title="Eliminar"
                                                        data-bs-toggle="modal" data-bs-target="#usModalEliminar"
                                                        data-accion="{{ route('usuarios.destroy', $u->id) }}" data-nombre="{{ $bonito($u->name) }}">
                                                    <i class="bi bi-trash"></i><span class="visually-hidden">Eliminar</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="us-vacio" id="usVacio" hidden>
                        <i class="bi bi-person-x" aria-hidden="true"></i>
                        Ningún usuario coincide con los filtros.
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('body_end')
<div id="nuevo_usuario" style="display: none;">
    <div>.</div>
    <div class="loader"></div>
</div>

@if ($puedeEditar)
<div class="modal fade" id="usModalPassword" tabindex="-1" aria-labelledby="usModalPasswordTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="usFormPassword" novalidate
              action="{{ $modalPassword ? route('usuarios.password', $modalPassword['id']) : '' }}" autocomplete="off">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="usModalPasswordTitulo"><i class="bi bi-key me-1" style="color:#CEA845;"></i> Cambiar contraseña</h5>
                    <div class="small text-muted" id="usPasswordPara">{{ $modalPassword['nombre'] ?? '' }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @if ($errors->password->any())
                    <div class="alert alert-danger py-2 small">{{ $errors->password->first() }}</div>
                @endif
                <label for="usNuevaPassword" class="form-label small fw-bold">Nueva contraseña</label>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" id="usNuevaPassword" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" id="usVerPassword" aria-label="Mostrar contraseña" title="Mostrar"><i class="bi bi-eye"></i></button>
                </div>
                <label for="usConfirmaPassword" class="form-label small fw-bold">Confirmar contraseña</label>
                <input type="password" class="form-control" id="usConfirmaPassword" name="password_confirmation" required minlength="8" maxlength="72" autocomplete="new-password">
                <div class="small mt-1" id="usPasswordAviso" style="min-height: 18px;"></div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="usGenerarPassword"><i class="bi bi-magic me-1"></i> Generar una segura</button>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none" id="usCopiarPassword" hidden><i class="bi bi-clipboard me-1"></i> Copiar</button>
                </div>
                <p class="small text-muted mt-3 mb-0">Mínimo 8 caracteres. Al guardarla se cierra su "recordarme" en otros equipos. Compártela con la persona por un medio seguro.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn tn-boton-dorado" id="usGuardarPassword" disabled><i class="bi bi-check2 me-1"></i> Guardar contraseña</button>
            </div>
        </form>
    </div>
</div>
@endif

@if ($puedeEliminar)
<div class="modal fade" id="usModalEliminar" tabindex="-1" aria-labelledby="usModalEliminarTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="usFormEliminar">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title" id="usModalEliminarTitulo">¿Eliminar usuario?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Vas a eliminar a <strong id="usNombreEliminar"></strong>. No se puede deshacer.</p>
                <p class="mb-0 small text-muted">Si sólo quieres quitarle el acceso, mejor desactívalo con el interruptor: conserva su historial y se puede reactivar.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i> Eliminar</button>
            </div>
        </form>
    </div>
</div>
@endif
@endpush

@section('scripts')
    <script src="{{ asset('assets/js/usuarios/usuarios.js') }}?v=2"></script>
    <script>
        (function () {
            var tabla = document.getElementById('usTabla');
            var cuerpo = tabla.tBodies[0];
            var filas = Array.prototype.slice.call(cuerpo.rows);
            var total = filas.length;
            var estado = { texto: '', rol: '', sede: '', estatus: '', sin: false, orden: 'nombre' };

            var sinAcentos = function (t) { return (t || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); };
            filas.forEach(function (f) { f.dataset.busca = sinAcentos(f.dataset.texto); });

            function aplicar() {
                var q = sinAcentos(estado.texto.trim());
                var visibles = 0;
                filas.forEach(function (f) {
                    var ok = (!q || f.dataset.busca.indexOf(q) !== -1)
                        && (!estado.rol || f.dataset.roles.indexOf('|' + estado.rol + '|') !== -1)
                        && (!estado.sede || f.dataset.sede === estado.sede)
                        && (!estado.estatus || f.dataset.estatus === estado.estatus)
                        && (!estado.sin || f.dataset.sin === '1');
                    f.hidden = !ok;
                    if (ok) { visibles++; }
                });

                var orden = filas.slice().sort(function (a, b) {
                    if (estado.orden === 'reciente') { return b.dataset.acceso - a.dataset.acceso; }
                    if (estado.orden === 'antiguo') { return a.dataset.acceso - b.dataset.acceso; }
                    if (a.dataset.estatus !== b.dataset.estatus) { return a.dataset.estatus === 'Activo' ? -1 : 1; }
                    return a.dataset.nombre.localeCompare(b.dataset.nombre, 'es');
                });
                orden.forEach(function (f) { cuerpo.appendChild(f); });

                document.getElementById('usConteo').textContent = visibles === total
                    ? total + ' usuarios'
                    : 'Mostrando ' + visibles + ' de ' + total + ' usuarios';
                document.getElementById('usVacio').hidden = visibles !== 0;

                document.querySelectorAll('.us-segmento button').forEach(function (b) {
                    b.classList.toggle('is-activo', b.dataset.estado === estado.estatus && !estado.sin);
                });
                document.querySelectorAll('.us-cifra').forEach(function (c) {
                    var a = c.dataset.atajo;
                    c.classList.toggle('is-activa', a === 'sin' ? estado.sin : (!estado.sin && a === estado.estatus));
                });
            }

            document.getElementById('usBuscar').addEventListener('input', function () { estado.texto = this.value; aplicar(); });
            document.getElementById('usRol').addEventListener('change', function () { estado.rol = this.value; aplicar(); });
            document.getElementById('usSede').addEventListener('change', function () { estado.sede = this.value; aplicar(); });
            document.getElementById('usOrden').addEventListener('change', function () { estado.orden = this.value; aplicar(); });
            document.querySelectorAll('.us-segmento button').forEach(function (b) {
                b.addEventListener('click', function () { estado.estatus = b.dataset.estado; estado.sin = false; aplicar(); });
            });
            document.querySelectorAll('.us-cifra').forEach(function (c) {
                c.addEventListener('click', function () {
                    var a = c.dataset.atajo;
                    if (a === 'sin') {
                        estado.sin = !estado.sin;
                        estado.estatus = estado.sin ? 'Activo' : '';
                        if (estado.sin) { estado.orden = 'antiguo'; document.getElementById('usOrden').value = 'antiguo'; }
                    } else {
                        estado.sin = false;
                        estado.estatus = estado.estatus === a ? '' : a;
                    }
                    aplicar();
                });
            });

            // El interruptor guarda al momento; se bloquea mientras se envía.
            document.querySelectorAll('[data-estado-form]').forEach(function (form) {
                var sw = form.querySelector('.form-check-input');
                sw.addEventListener('change', function () {
                    sw.disabled = true;
                    form.submit();
                });
            });

            // Cambio rápido de contraseña.
            var modalPass = document.getElementById('usModalPassword');
            if (modalPass) {
                var formPass = document.getElementById('usFormPassword');
                var nueva = document.getElementById('usNuevaPassword');
                var confirma = document.getElementById('usConfirmaPassword');
                var aviso = document.getElementById('usPasswordAviso');
                var guardar = document.getElementById('usGuardarPassword');
                var copiar = document.getElementById('usCopiarPassword');
                var ver = document.getElementById('usVerPassword');

                function mostrar(si) {
                    nueva.type = confirma.type = si ? 'text' : 'password';
                    ver.innerHTML = si ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                }
                function revisar() {
                    var ok = nueva.value.length >= 8 && nueva.value === confirma.value;
                    if (!nueva.value) { aviso.textContent = ''; }
                    else if (nueva.value.length < 8) { aviso.textContent = 'Faltan ' + (8 - nueva.value.length) + ' caracteres.'; aviso.style.color = '#8C1D40'; }
                    else if (confirma.value && confirma.value !== nueva.value) { aviso.textContent = 'Las contraseñas no coinciden.'; aviso.style.color = '#8C1D40'; }
                    else if (ok) { aviso.textContent = 'Lista para guardar.'; aviso.style.color = '#1E7B3C'; }
                    else { aviso.textContent = ''; }
                    guardar.disabled = !ok;
                    copiar.hidden = !nueva.value;
                }
                nueva.addEventListener('input', revisar);
                confirma.addEventListener('input', revisar);
                ver.addEventListener('click', function () { mostrar(nueva.type === 'password'); });
                document.getElementById('usGenerarPassword').addEventListener('click', function () {
                    nueva.value = confirma.value = window.siconcilioClave();
                    mostrar(true);
                    revisar();
                    nueva.select();
                });
                copiar.addEventListener('click', function () {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(nueva.value).then(function () { copiar.innerHTML = '<i class="bi bi-check2 me-1"></i> Copiada'; });
                    } else { nueva.select(); }
                });
                formPass.addEventListener('submit', function () { guardar.disabled = true; });

                modalPass.addEventListener('show.bs.modal', function (e) {
                    var b = e.relatedTarget;
                    if (b) {
                        formPass.action = b.getAttribute('data-accion');
                        document.getElementById('usPasswordPara').textContent = b.getAttribute('data-nombre') + ' · ' + b.getAttribute('data-correo');
                        var alerta = modalPass.querySelector('.alert'); if (alerta) { alerta.remove(); }
                    }
                    nueva.value = confirma.value = '';
                    copiar.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copiar';
                    mostrar(false);
                    revisar();
                });
                modalPass.addEventListener('shown.bs.modal', function () { nueva.focus(); });
                modalPass.addEventListener('hidden.bs.modal', function () { nueva.value = confirma.value = ''; });

                @if ($modalPassword)
                    // Volvió con error de validación: se reabre el modal de esa persona.
                    window.addEventListener('load', function () { bootstrap.Modal.getOrCreateInstance(modalPass).show(); });
                @endif
            }

            var modal = document.getElementById('usModalEliminar');
            if (modal) {
                modal.addEventListener('show.bs.modal', function (e) {
                    var b = e.relatedTarget;
                    document.getElementById('usFormEliminar').action = b.getAttribute('data-accion');
                    document.getElementById('usNombreEliminar').textContent = b.getAttribute('data-nombre');
                });
            }

            aplicar();
        })();
    </script>
@endsection
