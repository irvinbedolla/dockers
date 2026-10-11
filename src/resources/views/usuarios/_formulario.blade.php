{{--
    Campos del alta y de la edición de usuarios (crear.blade.php y
    editar.blade.php). Espera: $user (nuevo o existente), $roles, $rolActual,
    $sedes, $sistemas y $soyYo. La CURP vive en users.profile_photo_path.
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
        <div class="col-md-6">
            <label for="roles">Rol <span class="req">*</span></label>
            <select name="roles" id="roles" class="form-select {{ $campo('roles') }}" required>
                <option value="">Elige un rol</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol }}" @selected(old('roles', $rolActual) === $rol)>{{ $rol }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">@error('roles'){{ $message }}@else Elige el rol.@enderror</div>
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
