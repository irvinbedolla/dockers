<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Historial del inventario (Administración → Inventario): un renglón por alta,
 * corrección, verificación o bandera de revisión de un dispositivo.
 * datos_antes / datos_despues guardan sólo los campos que cambiaron (en el
 * alta, datos_antes va vacío). Ver App\Models\InventarioHistorial.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventario_historial')) {
            return;
        }

        Schema::create('inventario_historial', function (Blueprint $table) {
            $table->id();
            // Sin borrado en cascada: un dispositivo con historial no se puede borrar por accidente.
            $table->foreignId('dispositivo_id')->constrained('inventario_dispositivos');
            $table->enum('movimiento', ['alta', 'edicion', 'verificacion', 'revision']);
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();
            $table->text('nota')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_nombre')->nullable();          // se conserva aunque el usuario se elimine
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['dispositivo_id', 'created_at']);
            $table->index(['movimiento', 'created_at']);
        });

        // Al principio el inventario escribía en historial_administracion
        // (tipo inventario_*): esos movimientos se pasan aquí.
        if (! Schema::hasTable('historial_administracion')) {
            return;
        }
        DB::transaction(function () {
            $viejos = DB::table('historial_administracion')
                ->where('tabla', 'inventario_dispositivos')
                ->where('tipo', 'like', 'inventario\_%')
                ->whereIn('registro_id', DB::table('inventario_dispositivos')->select('id'))
                ->orderBy('id')
                ->get();

            foreach ($viejos as $v) {
                DB::table('inventario_historial')->insert([
                    'dispositivo_id' => $v->registro_id,
                    'movimiento'     => substr($v->tipo, strlen('inventario_')),
                    'datos_antes'    => $v->datos_antes,
                    'datos_despues'  => $v->datos_despues,
                    'nota'           => $v->motivo,
                    'user_id'        => $v->user_id,
                    'user_nombre'    => $v->user_nombre,
                    'ip'             => $v->ip,
                    'created_at'     => $v->created_at,
                    'updated_at'     => $v->updated_at,
                ]);
            }

            DB::table('historial_administracion')->whereIn('id', $viejos->pluck('id'))->delete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_historial');
    }
};
