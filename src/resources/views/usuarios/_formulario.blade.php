{{--
    Campos del alta y de la edición de usuarios (crear.blade.php y
    editar.blade.php). Espera: $user (nuevo o existente), $roles, $rolesActuales,
    $agenda (PermisosConciliador::resumen), $supervisaSinotifico, $sedes,
    $sistemas y $soyYo.
    La CURP vive en users.profile_photo_path.
--}}
@php
    $esNuevo = ! $user->exists;
    $campo = fn (string $n) => $errors->has($n) ? 'is-invalid' : '';
    $regexCurp = '/^[A-Z]{4}\d{6}[HMX][A-Z]{5}[A-Z0-9]\d$/';
    $curpGuardada = strtoupper(trim((string) $user->profile_photo_path));
    $curpMostrada = preg_match($regexCurp, $curpGuardada) ? $curpGuardada : '';
    $curpRelleno = ! $esNuevo && $curpGuardada !== '' && $curpMostrada === '';
    $activo = old('activo', $esNuevo || $user->estatus === 'Activo' ? '1' : '0') === '1';
@endphp

{{-- 1. Persona --}}
<div class="tn-tarjeta uf-seccion">
    <h5 class="uf-seccion__titulo"><span class="uf-seccion__num">1</span> Datos de la persona</h5>
    <p class="uf-seccion__ayuda">Como aparecerá en el sistema y en los documentos.</p>
    <div class="row g-3">
        <div class="col-md-12">
            <label for="name">Nombre completo <span class="req">*</span></label>
            <input type="text" class="form-control {{ $campo('name') }}" id="name" name="name"
                   value="{{ old('name', $user->name) }}" required minlength="5" maxlength="191" autocomplete="off"
                   placeholder="Nombre(s) y apellidos">
            <div class="invalid-feedback">@error('name'){{ $message }}@else Escribe el nombre completo.@enderror</div>
        </div>
        <div class="col-md-6">
            <label for="email">Correo <span class="req">*</span></label>
            <input type="email" class="form-control {{ $campo('email') }}" id="email" name="email"
                   value="{{ old('email', $user->email) }}" required maxlength="60" autocomplete="off" placeholder="nombre@cclmichoacan.gob.mx">
            <div class="invalid-feedback">@error('email'){{ $message }}@else Escribe un correo válido.@enderror</div>
            <div class="form-text">Con este correo inicia sesión.</div>
        </div>
        <div class="col-md-6">
            <label for="curp">CURP</label>
            <input type="hidden" name="curp_mostrada" value="{{ $curpMostrada }}">
            <input type="text" class="form-control text-uppercase {{ $campo('curp') }}" id="curp" name="curp"
                   value="{{ old('curp', $curpMostrada) }}" maxlength="18" pattern="[A-Za-z]{4}[0-9]{6}[HMXhmx][A-Za-z]{5}[A-Za-z0-9][0-9]"
                   autocomplete="off" placeholder="Opcional">
            <div class="invalid-feedback">@error('curp'){{ $message }}@else La CURP debe tener 18 caracteres válidos.@enderror</div>
            @if ($curpRelleno)
                <div class="form-text">Tiene guardado un dato que no es CURP; si escribes una, lo reemplaza.</div>
            @endif
        </div>
        <div class="col-md-6">
            <label for="sexo">Sexo</label>
            <select name="sexo" id="sexo" class="form-select {{ $campo('sexo') }}">
                <option value="">Sin especificar</option>
                <option value="H" @selected(old('sexo', $user->sexo) === 'H')>Hombre</option>
                <option value="M" @selected(old('sexo', $user->sexo) === 'M')>Mujer</option>
                <option value="NC" @selected(old('sexo', $user->sexo) === 'NC')>Prefiere no decirlo</option>
            </select>
            <div class="form-text">Para el saludo del Inicio: "Buenas tardes, Conciliadora".</div>
        </div>
    </div>
    <div class="row">
        @include('usuarios._campo_foto')
    </div>
</div>

{{-- 2. Acceso --}}
<div class="tn-tarjeta uf-seccion">
    <h5 class="uf-seccion__titulo"><span class="uf-seccion__num">2</span> Acceso</h5>
    <p class="uf-seccion__ayuda">El rol decide qué menús y permisos tiene; la sede, qué información ve.</p>
    <div class="row g-3">
        <div class="col-12">
            <label>Roles <span class="req">*</span></label>
            @php
                $marcados = (array) old('roles', $rolesActuales);
                $esp = \App\Support\Especialidades::class;
                $familiaAux = array_merge([$esp::BASE], $esp::ROLES);
                $otros = array_values(array_diff($roles, $familiaAux));
            @endphp
            <div class="uf-roles {{ $errors->has('roles') || $errors->has('roles.*') ? 'is-invalid' : '' }}" id="ufRoles">
                @if (in_array($esp::BASE, $roles, true))
                    <fieldset class="uf-roles__grupo">
                        <legend>Auxiliar de Conciliador</legend>
                        <p class="uf-roles__ayuda">Marca lo que atiende. Cada especialidad incluye el rol Auxiliar.</p>
                        @foreach ($esp::ROLES as $rol)
                            @if (in_array($rol, $roles, true))
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $rol }}" id="rol-{{ \Illuminate\Support\Str::slug($rol) }}"
                                           data-especialidad @checked(in_array($rol, $marcados, true))>
                                    <label class="form-check-label" for="rol-{{ \Illuminate\Support\Str::slug($rol) }}">{{ $esp::ETIQUETAS[$rol] }}</label>
                                </div>
                            @endif
                        @endforeach
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $esp::BASE }}" id="rol-auxiliar"
                                   data-auxiliar-base @checked(in_array($esp::BASE, $marcados, true))>
                            <label class="form-check-label" for="rol-auxiliar">Auxiliar (base)</label>
                        </div>
                        <div class="uf-roles__aviso" id="ufAvisoAuxiliar" hidden>Sin especialidad sólo podrá consultar: no verá Solicitudes, Ratificaciones ni Cumplimientos.</div>
                    </fieldset>
                @endif
                <fieldset class="uf-roles__grupo">
                    <legend>Otros roles</legend>
                    <div class="uf-roles__lista">
                        @foreach ($otros as $rol)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $rol }}" id="rol-{{ \Illuminate\Support\Str::slug($rol) }}"
                                       @checked(in_array($rol, $marcados, true))>
                                <label class="form-check-label" for="rol-{{ \Illuminate\Support\Str::slug($rol) }}">{{ $rol }}</label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
            </div>
            <div class="invalid-feedback d-block" @if (! $errors->has('roles') && ! $errors->has('roles.*')) hidden @endif id="ufRolesError">
                {{ $errors->first('roles') ?: $errors->first('roles.*') ?: 'Elige al menos un rol.' }}
            </div>
            <div class="form-text">Puede tener varios: por ejemplo, Recepción General y Auxiliar de Solicitudes en una sede chica.</div>
        </div>
        {{-- SíNotifico: los Enlaces asignan notificaciones, pero sólo los
             elegidos aquí supervisan a los notificadores de su sede en la app. --}}
        @php
            $supervisaMarcado = old('sinotifico_supervisa', ($supervisaSinotifico ?? false) ? '1' : '0') === '1';
        @endphp
        <div class="col-12" id="ufSinotifico" @if (! in_array('Enlace', $marcados, true)) hidden @endif>
            <div class="uf-sinotifico">
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="sinotifico_supervisa" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="sinotifico_supervisa" name="sinotifico_supervisa" value="1"
                           @checked($supervisaMarcado)>
                    <label class="form-check-label" for="sinotifico_supervisa">Supervisa notificadores en SíNotifico</label>
                </div>
                <div class="form-text mt-1">
                    Ve en la app cómo van los notificadores de su sede: asignadas, pendientes y fuera de plazo.
                    Delegados, Directivos y Super Usuarios ya lo tienen por su rol.
                </div>
            </div>
        </div>
        {{-- Enlace de Género e Igualdad: distintivo para apoyar al departamento
             desde otra delegación (CasosExcepcion::elegirEnlace). Quien ya tiene
             el rol no lo necesita. --}}
        @php
            $enlaceMarcado = old('excepcion_enlace', ($enlaceExcepcion ?? false) ? '1' : '0') === '1';
        @endphp
        <div class="col-12" id="ufEnlaceExcepcion" @if (in_array(\App\Support\CasosExcepcion::ROL, $marcados, true)) hidden @endif>
            <div class="uf-sinotifico uf-enlace">
                <div class="form-check form-switch mb-0">
                    <input type="hidden" name="excepcion_enlace" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="excepcion_enlace" name="excepcion_enlace" value="1"
                           @checked($enlaceMarcado)>
                    <label class="form-check-label" for="excepcion_enlace">{{ \App\Support\CasosExcepcion::DISTINTIVO }}</label>
                </div>
                <div class="form-text mt-1">
                    Apoya desde su delegación al Departamento de Conciliación, Género e Igualdad: le aparece "Casos de excepción"
                    y puede atender, canalizar y cerrar casos, igual que el departamento (sin sus estadísticas). No cambia lo demás de sus roles.
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <label for="delegacion">Sede <span class="req">*</span></label>
            <select name="delegacion" id="delegacion" class="form-select {{ $campo('delegacion') }}" required>
                <option value="">Elige una sede</option>
                @foreach ($sedes as $sede)
                    <option value="{{ $sede }}" @selected(old('delegacion', $user->delegacion) === $sede)>{{ $sede }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">@error('delegacion'){{ $message }}@else Elige la sede.@enderror</div>
        </div>
        <div class="col-md-6">
            <label for="type">Sistema <span class="req">*</span></label>
            <select name="type" id="type" class="form-select {{ $campo('type') }}" required>
                <option value="">Elige el sistema</option>
                @foreach ($sistemas as $valor => $etiqueta)
                    <option value="{{ $valor }}" @selected(old('type', $user->type ?? 'Si concilio') === $valor)>{{ $etiqueta }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">@error('type'){{ $message }}@else Elige el sistema.@enderror</div>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <div>
                <div class="form-check form-switch mb-1">
                    <input type="hidden" name="activo" value="{{ $soyYo ? '1' : '0' }}">
                    <input class="form-check-input {{ $campo('activo') }}" type="checkbox" role="switch" id="activo" name="activo" value="1"
                           @checked($activo) @disabled($soyYo)>
                    <label class="form-check-label" for="activo" style="font-weight: 600;">Cuenta activa (puede iniciar sesión)</label>
                </div>
                @if ($soyYo)
                    <div class="form-text mt-0">No puedes desactivar tu propia cuenta.</div>
                @endif
                @error('activo')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Agenda del conciliador: sólo se ve con el rol Conciliador marcado. Sin
     ella el reparto automático nunca le asigna audiencias. --}}
@php
    $agendaTipo   = old('conciliador_tipo', $agenda['tipo']);
    $agendaDias   = (array) old('conciliador_dias', $agenda['dias']);
    $agendaInicio = old('conciliador_inicio', $agenda['inicio']);
    $agendaFin    = old('conciliador_fin', $agenda['fin']);
    $subsedes     = ['Morelia' => 'Zitácuaro', 'Uruapan' => 'Lázaro Cárdenas', 'Zamora' => 'Sahuayo'];
@endphp
<div class="tn-tarjeta uf-seccion" id="ufAgenda" @if (! in_array('Conciliador', (array) old('roles', $rolesActuales), true)) hidden @endif>
    <h5 class="uf-seccion__titulo"><span class="uf-seccion__num"><i class="bi bi-calendar-week"></i></span> Audiencias del conciliador</h5>
    <p class="uf-seccion__ayuda">
        Con esto el sistema le reparte audiencias en automático al confirmar solicitudes.
        @if (! $agenda['existe'] && ! $esNuevo)
            <strong style="color:#8A6A12;">Todavía no tiene agenda: hoy no recibe audiencias.</strong>
        @endif
    </p>
    <div class="row g-3">
        <div class="col-12">
            <label>Qué audiencias recibe <span class="req">*</span></label>
            <div class="uf-opciones">
                @foreach (\App\Models\PermisosConciliador::TIPOS as $valor => $etiqueta)
                    <label class="uf-opcion">
                        <input class="form-check-input" type="radio" name="conciliador_tipo" value="{{ $valor }}" @checked($agendaTipo === $valor)>
                        <span>
                            <span class="uf-opcion__titulo">{{ $etiqueta }}</span>
                            <span class="uf-opcion__texto" data-texto-tipo="{{ $valor }}">
                                @switch($valor)
                                    @case('Precencial') De su sede, en sala. @break
                                    @case('Virtual') De la subsede (<span data-subsede>Zitácuaro, Lázaro Cárdenas o Sahuayo</span>). @break
                                    @default De su sede y de la subsede. También ve los cumplimientos de la subsede.
                                @endswitch
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('conciliador_tipo')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-7">
            <label>Días <span class="req">*</span></label>
            <div class="uf-dias">
                @foreach (\App\Models\PermisosConciliador::DIAS as $dia => $corto)
                    <input type="checkbox" class="btn-check" name="conciliador_dias[]" value="{{ $dia }}" id="dia-{{ $dia }}" autocomplete="off" @checked(in_array($dia, $agendaDias, true))>
                    <label class="btn uf-dia" for="dia-{{ $dia }}">{{ $corto }}</label>
                @endforeach
            </div>
            @error('conciliador_dias')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-6 col-md-auto">
            <label for="conciliador_inicio">Desde <span class="req">*</span></label>
            <input type="time" class="form-control {{ $campo('conciliador_inicio') }}" id="conciliador_inicio" name="conciliador_inicio" value="{{ $agendaInicio }}" step="60">
            <div class="invalid-feedback">{{ $errors->first('conciliador_inicio') }}</div>
        </div>
        <div class="col-6 col-md-auto">
            <label for="conciliador_fin">Hasta <span class="req">*</span></label>
            <input type="time" class="form-control {{ $campo('conciliador_fin') }}" id="conciliador_fin" name="conciliador_fin" value="{{ $agendaFin }}" step="60">
            <div class="invalid-feedback">{{ $errors->first('conciliador_fin') }}</div>
        </div>
        <div class="col-12">
            <div class="form-text mt-0">Las vacaciones y permisos de un día se bloquean en Administración → bloqueo de conciliadores; aquí va su horario normal.</div>
        </div>
    </div>
</div>

{{-- 3. Contraseña --}}
<div class="tn-tarjeta uf-seccion">
    <h5 class="uf-seccion__titulo"><span class="uf-seccion__num">3</span> Contraseña</h5>
    <p class="uf-seccion__ayuda">
        @if ($esNuevo)
            Mínimo 8 caracteres. Compártela con la persona por un medio seguro; podrá cambiarla después.
        @else
            Déjala vacía para conservar la actual. Si escribes una nueva (mínimo 8 caracteres), se cierra su "recordarme" en otros equipos.
        @endif
    </p>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="password">{{ $esNuevo ? 'Contraseña' : 'Nueva contraseña' }} @if ($esNuevo)<span class="req">*</span>@endif</label>
            <div class="input-group has-validation">
                <input type="password" class="form-control {{ $campo('password') }}" id="password" name="password"
                       @if ($esNuevo) required @endif minlength="8" maxlength="72" autocomplete="new-password">
                <button class="btn btn-outline-secondary" type="button" id="verPassword" aria-label="Mostrar contraseña" title="Mostrar">
                    <i class="bi bi-eye"></i>
                </button>
                <div class="invalid-feedback">@error('password'){{ $message }}@else Mínimo 8 caracteres.@enderror</div>
            </div>
        </div>
        <div class="col-md-6">
            <label for="confirm-password">Confirmar contraseña @if ($esNuevo)<span class="req">*</span>@endif</label>
            <input type="password" class="form-control" id="confirm-password" name="confirm-password"
                   @if ($esNuevo) required @endif minlength="8" maxlength="72" autocomplete="new-password">
            <div class="invalid-feedback">Las contraseñas no coinciden.</div>
        </div>
        <div class="col-12">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="generarPassword">
                <i class="bi bi-magic me-1"></i> Generar una contraseña segura
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var pass = document.getElementById('password');
        var conf = document.getElementById('confirm-password');
        var curp = document.getElementById('curp');
        if (!pass || !conf) { return; }

        // Confirmación: el navegador la marca inválida mientras no coincida.
        function revisarConfirmacion() {
            conf.setCustomValidity(conf.value !== pass.value ? 'No coinciden' : '');
        }
        pass.addEventListener('input', revisarConfirmacion);
        conf.addEventListener('input', revisarConfirmacion);

        curp.addEventListener('input', function () { curp.value = curp.value.toUpperCase().replace(/\s/g, ''); });

        // Roles: una especialidad marca el Auxiliar base; quitar el base
        // quita las especialidades. Al menos un rol para poder guardar.
        var cajaRoles = document.getElementById('ufRoles');
        var base = cajaRoles.querySelector('[data-auxiliar-base]');
        var especialidades = Array.prototype.slice.call(cajaRoles.querySelectorAll('[data-especialidad]'));
        var avisoAux = document.getElementById('ufAvisoAuxiliar');
        var errorRoles = document.getElementById('ufRolesError');
        function revisarRoles() {
            var alguna = especialidades.some(function (c) { return c.checked; });
            if (base) { avisoAux.hidden = !(base.checked && !alguna); }
            var hay = cajaRoles.querySelectorAll('input[name="roles[]"]:checked').length > 0;
            cajaRoles.classList.toggle('is-invalid', !hay && cajaRoles.dataset.tocado === '1');
            errorRoles.hidden = hay || cajaRoles.dataset.tocado !== '1';
            return hay;
        }
        especialidades.forEach(function (c) {
            c.addEventListener('change', function () { if (c.checked && base) { base.checked = true; } cajaRoles.dataset.tocado = '1'; revisarRoles(); });
        });
        if (base) {
            base.addEventListener('change', function () {
                if (!base.checked) { especialidades.forEach(function (c) { c.checked = false; }); }
                cajaRoles.dataset.tocado = '1'; revisarRoles();
            });
        }
        cajaRoles.addEventListener('change', function () { cajaRoles.dataset.tocado = '1'; revisarRoles(); });
        var formulario = cajaRoles.closest('form');
        formulario.addEventListener('submit', function (e) {
            cajaRoles.dataset.tocado = '1';
            if (!revisarRoles()) { e.preventDefault(); e.stopImmediatePropagation(); cajaRoles.scrollIntoView({ block: 'center' }); }
        }, true);
        revisarRoles();

        // Agenda del conciliador: aparece al marcar el rol y nombra la
        // subsede según la sede elegida.
        var agenda = document.getElementById('ufAgenda');
        var rolConciliador = cajaRoles.querySelector('input[name="roles[]"][value="Conciliador"]');
        var sede = document.getElementById('delegacion');
        var SUBSEDES = @json($subsedes);
        function revisarAgenda() {
            if (!agenda) { return; }
            agenda.hidden = !(rolConciliador && rolConciliador.checked);
            var sub = SUBSEDES[sede.value];
            agenda.querySelectorAll('[data-subsede]').forEach(function (s) {
                s.textContent = sub || 'Zitácuaro, Lázaro Cárdenas o Sahuayo';
            });
            // Las subsedes no tienen subsede propia: su conciliador vive en la sede principal.
        }
        if (rolConciliador) { rolConciliador.addEventListener('change', revisarAgenda); }

        // SíNotifico: la opción de supervisar sólo aplica con el rol Enlace.
        var bloqueSinotifico = document.getElementById('ufSinotifico');
        var rolEnlace = cajaRoles.querySelector('input[name="roles[]"][value="Enlace"]');
        if (bloqueSinotifico && rolEnlace) {
            rolEnlace.addEventListener('change', function () { bloqueSinotifico.hidden = !rolEnlace.checked; });
        }
        // Enlace de Género e Igualdad: sobra si ya tiene el rol del departamento.
        var bloqueEnlace = document.getElementById('ufEnlaceExcepcion');
        var rolGenero = cajaRoles.querySelector('input[name="roles[]"][value="' + @json(\App\Support\CasosExcepcion::ROL) + '"]');
        if (bloqueEnlace && rolGenero) {
            rolGenero.addEventListener('change', function () { bloqueEnlace.hidden = rolGenero.checked; });
        }
        if (sede) { sede.addEventListener('change', revisarAgenda); }
        revisarAgenda();

        document.getElementById('verPassword').addEventListener('click', function () {
            var ver = pass.type === 'password';
            pass.type = conf.type = ver ? 'text' : 'password';
            this.innerHTML = ver ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            this.setAttribute('aria-label', ver ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });

        // Sin letras que se confunden (l, 1, O, 0) para dictarla sin errores.
        document.getElementById('generarPassword').addEventListener('click', function () {
            pass.value = conf.value = window.siconcilioClave ? window.siconcilioClave() : '';
            pass.type = conf.type = 'text';
            document.getElementById('verPassword').innerHTML = '<i class="bi bi-eye-slash"></i>';
            revisarConfirmacion();
            pass.focus(); pass.select();
        });
    })();
</script>
@endpush
