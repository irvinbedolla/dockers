<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de poderes (App\Support\HistorialPoderes). Cada foto de
 * historial_abogados guarda:
 *   accion          alta, edicion, validacion (Pendiente → Validado),
 *                   representante (folio nuevo al agregar un representante)
 *                   o baja (lo que había antes de borrarlo);
 *   origen_abogado  el folio del que salió un folio de representante;
 *   previo_id       la foto anterior del mismo folio, para decir qué cambió.
 *
 * Las fotos que ya existían se llenan aquí (unas 10 mil: tarda segundos).
 * Se puede correr más de una vez: sólo llena las que siguen sin accion.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('historial_abogados')) {
            return;
        }

        Schema::table('historial_abogados', function (Blueprint $t) {
            if (! Schema::hasColumn('historial_abogados', 'accion')) {
                $t->string('accion', 20)->nullable()->after('id_user');
            }
            if (! Schema::hasColumn('historial_abogados', 'origen_abogado')) {
                $t->unsignedBigInteger('origen_abogado')->nullable()->after('accion');
            }
            if (! Schema::hasColumn('historial_abogados', 'previo_id')) {
                $t->unsignedBigInteger('previo_id')->nullable()->after('origen_abogado');
            }
        });

        $indices = collect(Schema::getIndexes('historial_abogados'))->pluck('name');
        Schema::table('historial_abogados', function (Blueprint $t) use ($indices) {
            if (! $indices->contains('historial_abogados_abogado_id_index')) {
                $t->index(['id_abogado', 'id'], 'historial_abogados_abogado_id_index');
            }
            if (! $indices->contains('historial_abogados_accion_fecha_index')) {
                $t->index(['accion', 'created_at'], 'historial_abogados_accion_fecha_index');
            }
            if (! $indices->contains('historial_abogados_origen_index')) {
                $t->index('origen_abogado', 'historial_abogados_origen_index');
            }
        });

        // Llenar las fotos que ya existían, folio por folio y en orden.
        $folio = null;
        $previa = null;
        $cambios = [];
        DB::table('historial_abogados')
            ->select('id', 'id_abogado', 'estatus', 'accion')
            ->orderBy('id_abogado')->orderBy('id')
            ->lazy(2000)
            ->each(function ($foto) use (&$folio, &$previa, &$cambios) {
                if ($foto->id_abogado !== $folio) {
                    $folio = $foto->id_abogado;
                    $previa = null;
                }
                if ($foto->accion === null) {
                    $cambios[] = [
                        'id'        => $foto->id,
                        'previo_id' => $previa?->id,
                        'accion'    => ! $previa ? 'alta' : ($previa->estatus === 'Pendiente' && $foto->estatus === 'Validado' ? 'validacion' : 'edicion'),
                    ];
                }
                $previa = $foto;
            });

        foreach (array_chunk($cambios, 500) as $lote) {
            DB::transaction(function () use ($lote) {
                foreach ($lote as $c) {
                    DB::table('historial_abogados')->where('id', $c['id'])->update(['previo_id' => $c['previo_id'], 'accion' => $c['accion']]);
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('historial_abogados')) {
            return;
        }
        $indices = collect(Schema::getIndexes('historial_abogados'))->pluck('name');
        Schema::table('historial_abogados', function (Blueprint $t) use ($indices) {
            foreach (['historial_abogados_abogado_id_index', 'historial_abogados_accion_fecha_index', 'historial_abogados_origen_index'] as $i) {
                if ($indices->contains($i)) {
                    $t->dropIndex($i);
                }
            }
        });
        Schema::table('historial_abogados', function (Blueprint $t) {
            foreach (['previo_id', 'origen_abogado', 'accion'] as $c) {
                if (Schema::hasColumn('historial_abogados', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
