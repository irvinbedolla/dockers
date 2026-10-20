<?php

use App\Support\Sinotifico\Acceso;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permisos de SíNotifico (ver App\Support\Sinotifico\Acceso).
 *
 * Los Enlaces no reciben nada aquí: la supervisión de su sede se da persona
 * por persona desde Usuarios.
 *
 * Re-ejecutable: no duplica permisos ni quita los que ya tenga cada rol.
 */
return new class extends Migration
{
    private const POR_ROL = [
        'Notificador' => [Acceso::NOTIFICAR],
        'Delegado' => [Acceso::SUPERVISAR_SEDE],
        'Directivo' => [Acceso::SUPERVISAR_TODAS],
        'Super Usuario' => [Acceso::SUPERVISAR_TODAS],
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Acceso::PERMISOS as $nombre) {
            Permission::findOrCreate($nombre, Acceso::GUARD);
        }

        foreach (self::POR_ROL as $rol => $permisos) {
            Role::findOrCreate($rol, Acceso::GUARD)->givePermissionTo($permisos);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $ids = Permission::whereIn('name', Acceso::PERMISOS)->where('guard_name', Acceso::GUARD)->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        Permission::whereIn('id', $ids)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
