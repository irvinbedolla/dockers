<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

/**
 * "Ver como" (sólo Super Usuario): navegar el sistema como lo ve otra persona,
 * sin crear un rol de prueba para cada caso. Dos formas:
 *
 *   usuario  una persona en específico: sus roles, permisos, sede, módulos y
 *            registros, tal cual.
 *   rol      uno o varios roles con una sede (p. ej. Auxiliar + Auxiliar
 *            Ratificaciones en Uruapan): un usuario de prueba que no existe
 *            (id 0), con sólo esos roles y sus permisos. Lo que depende de la
 *            persona (Mi módulo, "mis" ratificaciones) sale vacío.
 *
 * Lo guarda la sesión (SESION) y lo aplica el middleware VerComoMiddleware en
 * cada petición: la sesión sigue siendo del Super Usuario, sólo cambia el
 * usuario que ve el sistema. Es de sólo lectura: se bloquea todo lo que no sea
 * GET y lo que se escriba en la base durante la petición se deshace.
 */
class VerComo
{
    public const SESION = 'ver_como';

    /** Quién puede usarlo (siempre se pregunta por el usuario real). */
    public const ROL = 'Super Usuario';

    public static function puede(?User $real): bool
    {
        return $real !== null && $real->hasRole(self::ROL);
    }

    /** Lo que está viendo ahora, o null: ['tipo', 'user_id' | 'roles' + 'sede', 'desde']. */
    public static function datos(): ?array
    {
        $d = session(self::SESION);

        return is_array($d) && in_array($d['tipo'] ?? null, ['usuario', 'rol'], true) ? $d : null;
    }

    /** ¿Se está viendo como otra persona en esta petición? */
    public static function activo(): bool
    {
        return app()->bound('ver_como.real');
    }

    /** El Super Usuario que realmente está en la sesión (null si no hay "Ver como"). */
    public static function real(): ?User
    {
        return app()->bound('ver_como.real') ? app('ver_como.real') : null;
    }

    public static function iniciarUsuario(User $real, User $objetivo): void
    {
        session([self::SESION => ['tipo' => 'usuario', 'user_id' => $objetivo->id, 'desde' => now()->toDateTimeString()]]);
        Log::info('ver_como.inicio', ['real' => $real->id, 'tipo' => 'usuario', 'usuario' => $objetivo->id]);
    }

    public static function iniciarRol(User $real, array $roles, string $sede): void
    {
        session([self::SESION => ['tipo' => 'rol', 'roles' => array_values($roles), 'sede' => $sede, 'desde' => now()->toDateTimeString()]]);
        Log::info('ver_como.inicio', ['real' => $real->id, 'tipo' => 'rol', 'roles' => $roles, 'sede' => $sede]);
    }

    public static function salir(?User $real): void
    {
        if (session()->has(self::SESION)) {
            Log::info('ver_como.fin', ['real' => $real?->id, 'era' => session(self::SESION)]);
        }
        session()->forget(self::SESION);
    }

    /**
     * El usuario con que se ve el sistema, o null si ya no se puede armar (el
     * usuario se borró o los roles ya no existen).
     */
    public static function usuario(array $d, User $real): ?User
    {
        if ($d['tipo'] === 'usuario') {
            return User::with(['roles', 'permissions'])->find($d['user_id'] ?? 0);
        }

        $roles = Role::where('guard_name', 'web')->whereIn('name', (array) ($d['roles'] ?? []))->get();
        if ($roles->isEmpty()) {
            return null;
        }

        // Usuario de prueba: no existe en la base (id 0), así nada de lo que es
        // del Super Usuario (sus módulos, sus registros) se cuela en la vista.
        $u = new User();
        $u->forceFill([
            'id'         => 0,
            'name'       => 'Vista de prueba',
            'email'      => null,
            'delegacion' => (string) ($d['sede'] ?? ''),
            'sexo'       => null,
            'estatus'    => 'Activo',
        ]);
        $u->exists = true;
        $u->setRelation('roles', $roles);
        $u->setRelation('permissions', collect());
        // En el encabezado sale como "Delegado de Morelia".
        $u->name = self::frase($d, $u);

        return $u;
    }

    /**
     * El usuario con que corre la petición. En modo rol se crea de verdad un
     * usuario de prueba (con sus roles en model_has_roles) DENTRO de la
     * transacción que VerComoMiddleware deshace al terminar: así todo el código
     * que hace User::find(auth()->id()) o pregunta los roles a la base lo
     * encuentra, y al final no queda nada. Sólo se llama con esa transacción abierta.
     */
    public static function usuarioParaPeticion(array $d, User $real): ?User
    {
        if ($d['tipo'] === 'usuario') {
            return self::usuario($d, $real);
        }

        $memoria = self::usuario($d, $real);
        if ($memoria === null || \Illuminate\Support\Facades\DB::transactionLevel() === 0) {
            return null;
        }

        $id = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
            'name'       => $memoria->name,
            'email'      => 'ver-como-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(16)).'@prueba.invalid',
            'password'   => \Illuminate\Support\Str::random(60),
            'type'       => $real->type ?: 'Ambos',
            'delegacion' => $memoria->delegacion,
            'estatus'    => 'Activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tabla = config('permission.table_names.model_has_roles', 'model_has_roles');
        $morph = (new User())->getMorphClass();
        foreach ($memoria->roles as $rol) {
            \Illuminate\Support\Facades\DB::table($tabla)->insert(['role_id' => $rol->id, 'model_type' => $morph, 'model_id' => $id]);
        }

        return User::with(['roles', 'permissions'])->find($id);
    }

    /** Cómo se nombra cada rol en el aviso (con su forma femenina si aplica). */
    private const NOMBRES_ROL = [
        'Conciliador'             => ['H' => 'Conciliador', 'M' => 'Conciliadora'],
        'Notificador'             => ['H' => 'Notificador', 'M' => 'Notificadora'],
        'Delegado'                => ['H' => 'Delegado', 'M' => 'Delegada'],
        'Directivo'               => ['H' => 'Directivo', 'M' => 'Directiva'],
        'Administrador'           => ['H' => 'Administrador', 'M' => 'Administradora'],
        'Orientador'              => ['H' => 'Orientador', 'M' => 'Orientadora'],
        'Enlace'                  => ['H' => 'Enlace Administrativo'],
        'Estadistica'             => ['H' => 'Enlace Técnico'],
        'Turnos'                  => ['H' => 'Recepción'],
        'Auxiliar Solicitudes'    => ['H' => 'Auxiliar de Solicitudes'],
        'Auxiliar Ratificaciones' => ['H' => 'Auxiliar de Ratificaciones'],
        'Auxiliar Cumplimientos'  => ['H' => 'Auxiliar de Cumplimientos'],
        'Auxiliar Poderes'        => ['H' => 'Auxiliar de Poderes'],
    ];

    /**
     * Cómo se dice a quién se está viendo: "Delegado de Morelia" (rol) o
     * "Jimena Ruiz, Conciliadora de Morelia" (persona).
     */
    public static function frase(?array $d, ?User $visto): string
    {
        if (! $d || ! $visto) {
            return '';
        }
        $roles = self::rolesLegibles($visto->getRoleNames()->all(), $d['tipo'] === 'usuario' ? $visto->sexo : null);
        $sede  = trim((string) $visto->delegacion);
        $cargo = trim(($roles ?: 'Sin rol').($sede !== '' ? ' de '.$sede : ''));

        return $d['tipo'] === 'usuario'
            ? \Illuminate\Support\Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $visto->name)))).', '.$cargo
            : $cargo;
    }

    /** Lo mismo que frase(); se deja con este nombre para las vistas que ya lo usan. */
    public static function etiqueta(?array $d, ?User $visto): string
    {
        return self::frase($d, $visto);
    }

    /**
     * ["Auxiliar", "Auxiliar Ratificaciones"] → "Auxiliar de Ratificaciones";
     * ["Conciliador"] con sexo M → "Conciliadora". Varios: "A y B".
     */
    public static function rolesLegibles(array $roles, ?string $sexo = null): string
    {
        // "Auxiliar" sobra si viene con su especialidad.
        if (in_array('Auxiliar', $roles, true) && count(array_filter($roles, fn ($r) => str_starts_with($r, 'Auxiliar '))) > 0) {
            $roles = array_values(array_diff($roles, ['Auxiliar']));
        }
        $nombres = array_map(function ($r) use ($sexo) {
            $formas = self::NOMBRES_ROL[$r] ?? null;

            return $formas ? ($formas[$sexo === 'M' ? 'M' : 'H'] ?? $formas['H']) : $r;
        }, $roles);

        if (count($nombres) <= 1) {
            return (string) ($nombres[0] ?? '');
        }
        $ultimo = array_pop($nombres);

        return implode(', ', $nombres).' y '.$ultimo;
    }
}
