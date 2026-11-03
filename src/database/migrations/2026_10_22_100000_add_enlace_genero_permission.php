<?php

use App\Support\CasosExcepcion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Distintivo "Enlace de Género e Igualdad" (CasosExcepcion::PERMISO_ENLACE).
 *
 * No se da a ningún rol: se pone y se quita persona por persona desde
 * Usuarios, como "Supervisa notificadores" de SíNotifico.
 *
 * Re-ejecutable.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate(CasosExcepcion::PERMISO_ENLACE, 'web');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $id = Permission::where('name', CasosExcepcion::PERMISO_ENLACE)->where('guard_name', 'web')->value('id');
        if ($id) {
            DB::table('model_has_permissions')->where('permission_id', $id)->delete();
            DB::table('role_has_permissions')->where('permission_id', $id)->delete();
            Permission::where('id', $id)->delete();
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
