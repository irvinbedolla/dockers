<?php

use App\Support\Recepcion;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea los tres roles de recepción: Morelia 01, Morelia 02 y Regional.
 *
 * Es una migración y no una entrada más en RoleSeeder porque ese seeder ya
 * corrió en producción por su propia migración (run_role_seeder) y no vuelve
 * a correr: lo que se le agregue ahora no llegaría al servidor.
 *
 * Los nombres y los permisos vienen de App\Support\Recepcion, el mismo lugar
 * del que los leen las rutas y el menú.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Recepcion::ROLES as $nombre) {
            $rol = Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);

            // syncPermissions y no givePermissionTo: si alguien le agregó algo
            // a mano, correr esto lo regresa a lo definido. Si un permiso no
            // existe, Spatie truena aquí en vez de crear uno mal escrito.
            $rol->syncPermissions(Recepcion::PERMISOS);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Borrar un rol con gente asignada le quita el acceso en silencio: la
        // llave foránea de model_has_roles borra en cascada. Se prefiere que
        // el rollback se detenga y lo diga.
        foreach (Recepcion::ROLES as $nombre) {
            $rol = Role::where('name', $nombre)->where('guard_name', 'web')->first();

            if ($rol && $rol->users()->exists()) {
                throw new RuntimeException(
                    "No se borra el rol {$nombre}: todavía tiene cuentas asignadas. ".
                    'Reasígnalas antes de revertir esta migración.'
                );
            }
        }

        Role::whereIn('name', Recepcion::ROLES)->where('guard_name', 'web')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
