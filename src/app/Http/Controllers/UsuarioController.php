<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\PermisosConciliador;
use App\Models\User;
use App\Support\FotoPerfil;
use App\Support\Sinotifico\Acceso;
use App\Support\TablaPosiciones;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UsuarioController extends Controller
{   
    public function index()
    {
        // Todo el personal (sin solicitantes), activos e inactivos: desde aquí
        // se reactivan. Son ~150, así que el filtro se hace en el navegador.
        $usuarios = User::with('roles:id,name', 'permissions:id,name')
            ->whereHas('roles', fn ($q) => $q->where('name', '!=', 'Solicitante'))
            ->orderByRaw("estatus = 'Activo' DESC")
            ->orderBy('name')
            ->get();

        $hace30 = now()->subDays(30);
        $conteo = [
            'activos'    => $usuarios->where('estatus', 'Activo')->count(),
            'inactivos'  => $usuarios->where('estatus', 'Inactivo')->count(),
            'sin_entrar' => $usuarios->where('estatus', 'Activo')
                ->filter(fn ($u) => ! $u->last_login_at || $u->last_login_at->lt($hace30))->count(),
        ];
        $roles = $usuarios->flatMap(fn ($u) => $u->roles->pluck('name'))->unique()->sort()->values();
        $sedes = $usuarios->pluck('delegacion')->filter()->unique()->sort()->values();

        return view('usuarios.index', compact('usuarios', 'conteo', 'roles', 'sedes'));
    }

    /** Sedes válidas (el enum de users.delegacion). */
    private const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    /** Sistema al que entra la cuenta (users.type). */
    private const SISTEMAS = ['Si concilio' => 'SICONCILIO', 'Seer' => 'SEER', 'Ambos' => 'Ambos'];

    /**
     * Roles que quien está en sesión puede asignar. "Solicitante" es para las
     * cuentas que se crean solas desde el portal, y sólo un Super Usuario
     * puede dar el rol de Super Usuario.
     */
    private function rolesAsignables(): array
    {
        $excluir = ['Solicitante'];
        if (! auth()->user()->hasRole('Super Usuario')) {
            $excluir[] = 'Super Usuario';
        }

        return Role::whereNotIn('name', $excluir)->orderBy('name')->pluck('name')->all();
    }

    public function create()
    {
        return view('usuarios.crear', [
            'user'      => new User(),
            'roles'         => $this->rolesAsignables(),
            'rolesActuales' => [],
            'agenda'        => PermisosConciliador::resumen(null),
            'supervisaSinotifico' => false,
            'soyYo'     => false,
            'sedes'    => self::SEDES,
            'sistemas' => self::SISTEMAS,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'name'  => preg_replace('/\s+/', ' ', trim((string) $request->input('name'))),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
            'curp'  => strtoupper(trim((string) $request->input('curp'))) ?: null,
        ]);

        $datos = $request->validate([
            'name'        => ['required', 'string', 'min:5', 'max:191'],
            'email'       => ['required', 'email', 'max:60', 'unique:users,email'],
            'curp'        => ['nullable', 'regex:/^[A-Z]{4}\d{6}[HMX][A-Z]{5}[A-Z0-9]\d$/', 'unique:users,profile_photo_path'],
            'sexo'        => ['nullable', 'in:H,M,NC'],
            'roles'       => ['required', 'array', 'min:1'],
            'roles.*'     => [Rule::in($this->rolesAsignables())],
            'delegacion'  => ['required', Rule::in(self::SEDES)],
            'type'        => ['required', Rule::in(array_keys(self::SISTEMAS))],
            'password'    => ['required', 'string', 'min:8', 'max:72', 'same:confirm-password'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=200,min_height=200'],
            'sinotifico_supervisa' => ['nullable', 'boolean'],
        ] + $this->reglasAgenda($request), $this->mensajes());

        $foto = null;
        if ($request->hasFile('foto_perfil')) {
            try {
                $foto = FotoPerfil::guardar($request->file('foto_perfil'));
            } catch (\RuntimeException $e) {
                return back()->withInput($request->except('password', 'confirm-password'))
                    ->withErrors(['foto_perfil' => $e->getMessage()]);
            }
        }

        // Sólo los campos del formulario: nada de $request->all().
        $user = DB::transaction(function () use ($datos, $foto, $request) {
            $user = User::create([
                'name'               => $datos['name'],
                'email'              => $datos['email'],
                'password'           => Hash::make($datos['password']),
                'profile_photo_path' => $datos['curp'] ?? null,   // la columna guarda la CURP
                'sexo'               => $datos['sexo'] ?? null,
                'delegacion'         => $datos['delegacion'],
                'type'               => $datos['type'],
                'foto_perfil'        => $foto,
            ]);
            $user->forceFill(['estatus' => $request->boolean('activo', true) ? 'Activo' : 'Inactivo'])->save();
            $user->syncRoles($this->conRolBase($datos['roles']));
            $this->guardarAgenda($user, $datos);
            Acceso::elegirSupervisor($user, $request->boolean('sinotifico_supervisa'));

            return $user;
        });

        // Roles y estatus deciden quién sale en la tabla de posiciones.
        TablaPosiciones::olvidar();

        return redirect()->route('usuarios')
            ->with('success', "Se creó la cuenta de {$user->name} (".implode(', ', $this->conRolBase($datos['roles'])).", {$user->delegacion}).");
    }

    public function show($id)
    {
        //
    }

    /**
     * Sólo un Super Usuario puede tocar la cuenta de otro Super Usuario: si
     * no, un Administrador podría cambiarle la contraseña y entrar como él.
     */
    private function autorizar(User $user): void
    {
        abort_unless(auth()->user()->can('usuarios_editar'), 403);
        abort_if(
            $user->hasRole('Super Usuario') && ! auth()->user()->hasRole('Super Usuario'),
            403,
            'Sólo un Super Usuario puede modificar la cuenta de otro Super Usuario.'
        );
    }

    public function edit($id)
    {
        $user = User::with('roles:id,name')->findOrFail($id);
        $this->autorizar($user);

        $rolesActuales = $user->roles->pluck('name')->all();
        // Si tiene un rol que no aparece en la lista (p. ej. Solicitante), se
        // conserva como opción para no quitárselo sin querer.
        $roles = collect($this->rolesAsignables())->merge($rolesActuales)->unique()->sort()->values()->all();

        return view('usuarios.editar', [
            'user'      => $user,
            'roles'     => $roles,
            'rolesActuales' => $rolesActuales,
            'agenda'        => PermisosConciliador::resumen($user->id),
            'supervisaSinotifico' => Acceso::supervisaPorEleccion($user),
            'sedes'     => self::SEDES,
            'sistemas'  => self::SISTEMAS,
            'soyYo'     => (int) $user->id === (int) auth()->id(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->autorizar($user);
        $soyYo = (int) $user->id === (int) auth()->id();

        $request->merge([
            'name'  => preg_replace('/\s+/', ' ', trim((string) $request->input('name'))),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
            'curp'  => strtoupper(trim((string) $request->input('curp'))) ?: null,
        ]);

        $permitidos = array_merge($this->rolesAsignables(), $user->getRoleNames()->all());

        $datos = $request->validate([
            'name'        => ['required', 'string', 'min:5', 'max:191'],
            'email'       => ['required', 'email', 'max:60', Rule::unique('users', 'email')->ignore($user->id)],
            'curp'        => ['nullable', 'regex:/^[A-Z]{4}\d{6}[HMX][A-Z]{5}[A-Z0-9]\d$/', Rule::unique('users', 'profile_photo_path')->ignore($user->id)],
            'sexo'        => ['nullable', 'in:H,M,NC'],
            'roles'       => ['required', 'array', 'min:1'],
            'roles.*'     => [Rule::in($permitidos)],
            'delegacion'  => ['required', Rule::in(self::SEDES)],
            'type'        => ['required', Rule::in(array_keys(self::SISTEMAS))],
            'password'    => ['nullable', 'string', 'min:8', 'max:72', 'same:confirm-password'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=200,min_height=200'],
            'sinotifico_supervisa' => ['nullable', 'boolean'],
        ] + $this->reglasAgenda($request), $this->mensajes());

        // Uno mismo no se quita el acceso ni el rol de Super Usuario: no habría
        // quién lo deshiciera desde aquí.
        if ($soyYo && ! $request->boolean('activo', true)) {
            return back()->withInput($request->except('password', 'confirm-password'))
                ->withErrors(['activo' => 'No puedes desactivar tu propia cuenta.']);
        }
        if ($soyYo && $user->hasRole('Super Usuario') && ! in_array('Super Usuario', $datos['roles'], true)) {
            return back()->withInput($request->except('password', 'confirm-password'))
                ->withErrors(['roles' => 'No puedes quitarte el rol de Super Usuario a ti mismo.']);
        }

        $input = [
            'name'       => $datos['name'],
            'email'      => $datos['email'],
            'sexo'       => $datos['sexo'] ?? null,
            'delegacion' => $datos['delegacion'],
            'type'       => $datos['type'],
        ];

        // La CURP sólo se toca si cambió respecto a lo que mostraba el
        // formulario: muchas cuentas viejas traen relleno ("tmp…") que no se
        // muestra y que no debe borrarse sólo por guardar otro dato.
        if (($datos['curp'] ?? '') !== (string) $request->input('curp_mostrada', '')) {
            $input['profile_photo_path'] = $datos['curp'] ?? null;
        }

        if (! empty($datos['password'])) {
            $input['password'] = Hash::make($datos['password']);
        }

        if ($request->hasFile('foto_perfil')) {
            try {
                $input['foto_perfil'] = FotoPerfil::guardar($request->file('foto_perfil'), $user->foto_perfil);
            } catch (\RuntimeException $e) {
                return back()->withInput($request->except('password', 'confirm-password'))
                    ->withErrors(['foto_perfil' => $e->getMessage()]);
            }
        } elseif ($request->boolean('quitar_foto')) {
            FotoPerfil::borrar($user->foto_perfil);
            $input['foto_perfil'] = null;
        }

        DB::transaction(function () use ($user, $input, $datos, $request, $soyYo) {
            $user->update($input);
            if (! $soyYo) {
                $user->forceFill(['estatus' => $request->boolean('activo', true) ? 'Activo' : 'Inactivo'])->save();
            }
            if (isset($input['password'])) {
                // Cierra el "recordarme" de otros equipos.
                $user->forceFill(['remember_token' => \Illuminate\Support\Str::random(60)])->save();
            }
            // syncRoles quita sólo los roles de este modelo y limpia la caché
            // de permisos (el DELETE a mano borraba por model_id sin tipo).
            $user->syncRoles($this->conRolBase($datos['roles']));
            $this->guardarAgenda($user, $datos);
            // Supervisión en SíNotifico: sólo para Enlaces elegidos a mano.
            Acceso::elegirSupervisor($user, $request->boolean('sinotifico_supervisa'));
        });

        TablaPosiciones::olvidar();

        return redirect()->route('usuarios')->with('success', "Se guardaron los cambios de {$user->name}.");
    }

    /**
     * Una especialidad de Auxiliar no sirve sola: lleva el rol base
     * "Auxiliar", que es el que el resto del sistema busca por nombre.
     */
    private function conRolBase(array $roles): array
    {
        $roles = array_values(array_unique($roles));
        if (array_intersect($roles, \App\Support\Especialidades::ROLES) && ! in_array(\App\Support\Especialidades::BASE, $roles, true)) {
            $roles[] = \App\Support\Especialidades::BASE;
        }

        return $roles;
    }

    /** Cambio rápido de contraseña desde el listado (modal). */
    public function cambiarPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->autorizar($user);

        $validador = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ], [
            'password.required'  => 'Escribe la nueva contraseña.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'       => 'La contraseña no puede pasar de 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validador->fails()) {
            return redirect()->route('usuarios')
                ->withErrors($validador, 'password')
                ->with('modal_password', ['id' => $user->id, 'nombre' => $user->name]);
        }

        $user->forceFill([
            'password'       => Hash::make($request->input('password')),
            'remember_token' => \Illuminate\Support\Str::random(60),
        ])->save();

        \Illuminate\Support\Facades\Log::info('Contraseña cambiada desde Usuarios', ['usuario' => $user->id, 'por' => auth()->id()]);

        return redirect()->route('usuarios')->with('success', "Se cambió la contraseña de {$user->name}.");
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->can('usuarios_eliminar'), 403);
        $user = User::findOrFail($id);
        abort_if($user->hasRole('Super Usuario') && ! auth()->user()->hasRole('Super Usuario'), 403);

        if ((int) $user->id === (int) auth()->id()) {
            return redirect()->route('usuarios')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $nombre = $user->name;
        FotoPerfil::borrar($user->foto_perfil);
        $user->syncRoles([]);
        $user->delete();
        TablaPosiciones::olvidar();

        return redirect()->route('usuarios')->with('success', "Se eliminó la cuenta de {$nombre}.");
    }

    /**
     * Agenda del conciliador (permisos_conciliador). Sólo es obligatoria si
     * se le marca el rol Conciliador: sin ella el reparto automático de
     * audiencias (SeerController::ObtenerAudiencia) nunca lo toma en cuenta.
     */
    private function reglasAgenda(Request $request): array
    {
        $esConciliador = in_array('Conciliador', (array) $request->input('roles', []), true);

        return [
            'conciliador_tipo'   => [Rule::requiredIf($esConciliador), 'nullable', Rule::in(array_keys(PermisosConciliador::TIPOS))],
            'conciliador_dias'   => [Rule::requiredIf($esConciliador), 'nullable', 'array', 'min:1'],
            'conciliador_dias.*' => [Rule::in(array_keys(PermisosConciliador::DIAS))],
            'conciliador_inicio' => [Rule::requiredIf($esConciliador), 'nullable', 'date_format:H:i'],
            'conciliador_fin'    => [Rule::requiredIf($esConciliador), 'nullable', 'date_format:H:i', 'after:conciliador_inicio'],
        ];
    }

    /**
     * Con el rol Conciliador se crea o actualiza su agenda. Si se le quita el
     * rol, la agenda se conserva (ObtenerAudiencia ya no lo toma porque exige
     * el rol) para que al devolvérselo vuelva con el mismo horario.
     */
    private function guardarAgenda(User $user, array $datos): void
    {
        if (! in_array('Conciliador', $datos['roles'], true)) {
            return;
        }

        $base = PermisosConciliador::PREDETERMINADO;

        PermisosConciliador::guardarPara(
            $user->id,
            $datos['conciliador_tipo'] ?? $base['tipo'],
            array_values($datos['conciliador_dias'] ?? $base['dias']),
            $datos['conciliador_inicio'] ?? $base['inicio'],
            $datos['conciliador_fin'] ?? $base['fin']
        );
    }

    private function mensajes(): array
    {
        return [
            'name.required'       => 'Escribe el nombre completo.',
            'name.min'            => 'Escribe el nombre completo.',
            'email.required'      => 'Escribe el correo.',
            'email.email'         => 'El correo no tiene un formato válido.',
            'email.unique'        => 'Ya existe una cuenta con ese correo.',
            'email.max'           => 'El correo no puede pasar de 60 caracteres.',
            'curp.regex'          => 'La CURP no tiene un formato válido (18 caracteres).',
            'curp.unique'         => 'Ya existe una cuenta con esa CURP.',
            'roles.required'      => 'Elige al menos un rol.',
            'roles.min'           => 'Elige al menos un rol.',
            'roles.*.in'          => 'No puedes asignar ese rol.',
            'delegacion.required' => 'Elige la sede.',
            'type.required'       => 'Elige a qué sistema entra.',
            'password.required'   => 'Escribe una contraseña.',
            'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
            'password.same'       => 'Las contraseñas no coinciden.',
            'foto_perfil.image'      => 'El archivo debe ser una imagen.',
            'foto_perfil.mimes'      => 'La foto debe ser JPG, PNG o WebP.',
            'foto_perfil.max'        => 'La foto no debe pesar más de 8 MB.',
            'foto_perfil.dimensions' => 'La foto debe medir al menos 200x200 píxeles.',
            'foto_perfil.uploaded'   => 'La foto no se pudo subir: excede el límite del servidor.',
            'conciliador_tipo.required'   => 'Elige qué audiencias recibe el conciliador.',
            'conciliador_dias.required'   => 'Marca al menos un día en que recibe audiencias.',
            'conciliador_dias.min'        => 'Marca al menos un día en que recibe audiencias.',
            'conciliador_inicio.required' => 'Indica la hora en que empieza a recibir audiencias.',
            'conciliador_fin.required'    => 'Indica la hora en que deja de recibir audiencias.',
            'conciliador_fin.after'       => 'La hora final debe ser después de la inicial.',
            'conciliador_inicio.date_format' => 'La hora no es válida.',
            'conciliador_fin.date_format'    => 'La hora no es válida.',
        ];
    }
}
