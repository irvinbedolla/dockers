<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Todo conciliador activo necesita su renglón en permisos_conciliador: sin él,
 * SeerController::ObtenerAudiencia nunca le asigna audiencias (es lo que le
 * pasaba a Luis Rico al cambiarlo a Conciliador).
 *
 * A quien no lo tenga se le crea con lo mismo que tienen los demás:
 * presencial, de lunes a viernes de 8:00 a 18:00. Se ajusta después en
 * Usuarios → editar. Desde ahora Usuarios lo crea solo al dar el rol.
 *
 * Re-ejecutable: no toca a quien ya tiene agenda.
 */
return new class extends Migration
{
    public function up(): void
    {
        $sinAgenda = DB::table('users as u')
            ->join('model_has_roles as mr', 'mr.model_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'mr.role_id')
            ->where('r.name', 'Conciliador')
            ->where('mr.model_type', 'App\\Models\\User')
            ->where('u.estatus', 'Activo')
            ->whereNotExists(fn ($q) => $q->selectRaw(1)
                ->from('permisos_conciliador as p')
                ->whereColumn('p.id_conciliador', 'u.id'))
            ->distinct()
            ->pluck('u.id');

        $ahora = now();
        foreach ($sinAgenda as $id) {
            $fila = ['id_conciliador' => $id, 'tipo' => 'Precencial', 'created_at' => $ahora, 'updated_at' => $ahora];
            foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as $dia) {
                $fila[$dia]            = 'Si';
                $fila[$dia.'_inicio']  = '08:00:00';
                $fila[$dia.'_final']   = '18:00:00';
            }
            DB::table('permisos_conciliador')->insert($fila);
        }
    }

    public function down(): void
    {
        // No se deshace: borrar la agenda dejaría otra vez sin audiencias a
        // quien ya las está recibiendo.
    }
};
