<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Divide al Auxiliar de Conciliador en especialidades.
 *
 * Antes: un solo rol "Auxiliar" con 35 permisos (solicitudes, ratificaciones
 * y cumplimientos), así que todos veían y podían hacer todo.
 *
 * Ahora:
 *   - "Auxiliar" se queda (el código lo busca por nombre en muchos lugares),
 *     pero sólo con lo común: poderes, consultar solicitudes, audiencias y
 *     notificaciones.
 *   - Tres roles nuevos con lo propio de cada trámite: Auxiliar Solicitudes,
 *     Auxiliar Ratificaciones y Auxiliar Cumplimientos.
 *
 * Reparto: cada Auxiliar recibe las especialidades de los trámites que
 * atiende (o recibe de respaldo) en el catálogo de módulos. Quien no tiene
 * módulo recibe las tres, para no quitarle nada; se ajusta en Usuarios.
 *
 * Re-ejecutable: no duplica roles ni toca a quien ya tenga una especialidad.
 */
return new class extends Migration
{
    private const GUARD = 'web';

    private const BASE = [
        'audiencia_ver_documento_digital', 'audiencias_consultar', 'audiencias_ver_citatorios',
        'notificaciones_ver_notificacion',
        'solicitudes_ver', 'solicitudes_ver_documentos',
    ];

    private const ESPECIALIDADES = [
        'Auxiliar Poderes' => [
            'poderes_agregar_representante', 'poderes_crear', 'poderes_editar', 'poderes_ver_expediente',
        ]
    ];

    private const POR_TRAMITE = [

        'Poderes'      => 'Auxiliar Poderes'
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $auxiliar = Role::findOrCreate('Auxiliar', self::GUARD);

        foreach (self::ESPECIALIDADES as $nombre => $permisos) {
            $rol = Role::findOrCreate($nombre, self::GUARD);
            $rol->syncPermissions($this->permisos($permisos));
        }

        // Reparto ANTES de recortar al Auxiliar, para que nadie se quede un
        // momento sin permisos.
        $tipo = (new User())->getMorphClass();
        $especialidades = Role::whereIn('name', array_keys(self::ESPECIALIDADES))->pluck('id', 'name');

        $auxiliares = DB::table('model_has_roles')
            ->where('role_id', $auxiliar->id)
            ->where('model_type', $tipo)
            ->pluck('model_id');

        foreach ($auxiliares as $userId) {
            $yaTiene = DB::table('model_has_roles')
                ->where('model_type', $tipo)->where('model_id', $userId)
                ->whereIn('role_id', $especialidades->values())
                ->exists();
            if ($yaTiene) {
                continue;
            }
        }

        $auxiliar->syncPermissions($this->permisos(self::BASE));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $todos = array_merge(self::BASE, ...array_values(self::ESPECIALIDADES));
        // El Auxiliar de antes no tenía reportes_cumplimientos.
        $todos = array_values(array_diff(array_unique($todos), ['reportes_cumplimientos']));
        Role::findOrCreate('Auxiliar', self::GUARD)->syncPermissions($this->permisos($todos));

        Role::whereIn('name', array_keys(self::ESPECIALIDADES))->where('guard_name', self::GUARD)->get()
            ->each(fn (Role $r) => $r->delete());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** Sólo los que existen en esta base (se crean si faltan). */
    private function permisos(array $nombres): array
    {
        return array_map(fn ($n) => Permission::findOrCreate($n, self::GUARD), $nombres);
    }
};
