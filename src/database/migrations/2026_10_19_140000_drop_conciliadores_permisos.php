<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Quita los permisos de la pantalla /conciliador/index, que se eliminó: la
 * agenda del conciliador (tipo, días y horario) se edita en Usuarios.
 * Sólo los tenía Super Usuario.
 */
return new class extends Migration
{
    private const PERMISOS = [
        'conciliadores_permisos',
        'conciliadores_permisos_consultar',
        'conciliadores_permisos_editar',
        'conciliadores_permisos_crear',
    ];

    public function up(): void
    {
        Permission::where('guard_name', 'web')->whereIn('name', self::PERMISOS)->get()->each->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach (self::PERMISOS as $nombre) {
            Permission::findOrCreate($nombre, 'web');
        }

        Role::where('name', 'Super Usuario')->where('guard_name', 'web')->first()?->givePermissionTo(self::PERMISOS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
