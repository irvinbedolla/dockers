<?php

namespace App\Services;

use App\Models\Retroceso;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Ejecuta los borrados y cambios de un retroceso dejando una foto de cada
 * registro en retroceso_detalles.
 *
 * Debe usarse dentro del mismo DB::transaction que el retroceso: si algo
 * falla, no queda ni el cambio ni su historial.
 *
 *   $retroceso = RetrocesoRecorder::iniciar('ratificacion', $turno);
 *   $retroceso->borrar(Pagos::where(...));
 *   $retroceso->actualizar($turno, ['estatus' => 'Confirmado']);
 *   $retroceso->terminar();
 */
class RetrocesoRecorder
{
    private function __construct(
        private Retroceso $retroceso,
        private Model $entidad,
    ) {}

    /**
     * Crea el encabezado del retroceso. Llamar ANTES de modificar la entidad
     * para que estatus_previo sea el real.
     */
    public static function iniciar(string $tipo, Model $entidad, ?string $motivo = null): self
    {
        $retroceso = Retroceso::create([
            'tipo'           => $tipo,
            'entidad_tipo'   => $entidad->getTable(),
            'entidad_id'     => $entidad->getKey(),
            'NUE'            => $entidad->getAttribute('NUE'),
            'delegacion'     => $entidad->getAttribute('delegacion'),
            'estatus_previo' => $entidad->getRawOriginal('estatus'),
            'motivo'         => $motivo,
            'user_id'        => auth()->id(),
            'user_nombre'    => auth()->user()->name ?? null,
            'ip'             => request()->ip(),
        ]);

        return new self($retroceso, $entidad);
    }

    /**
     * Borra los registros de la consulta guardando cada renglón completo.
     * Se borra por llave primaria para eliminar exactamente lo que se fotografió.
     */
    public function borrar(Builder $query): int
    {
        $registros = $query->get();

        if ($registros->isEmpty()) {
            return 0;
        }

        foreach ($registros as $registro) {
            $this->registrar($registro, 'deleted', $registro->getAttributes());
        }

        return $registros->first()->newQuery()->whereKey($registros->modelKeys())->delete();
    }

    /**
     * Borra un modelo ya cargado guardando su renglón completo.
     */
    public function eliminar(Model $modelo): void
    {
        $this->registrar($modelo, 'deleted', $modelo->getAttributes());
        $modelo->delete();
    }

    /**
     * Actualiza el modelo (mismas reglas que ->update()) guardando solo las
     * columnas que realmente cambian.
     */
    public function actualizar(Model $modelo, array $cambios): void
    {
        $modelo->fill($cambios);
        $cambiado = $modelo->getDirty();

        if (!empty($cambiado)) {
            $this->registrar(
                $modelo,
                'updated',
                array_intersect_key($modelo->getRawOriginal(), $cambiado),
                $cambiado
            );
        }

        $modelo->save();
    }

    /**
     * Cierra el retroceso guardando el estatus final de la entidad.
     */
    public function terminar(): Retroceso
    {
        $this->retroceso->update(['estatus_nuevo' => $this->entidad->getAttribute('estatus')]);

        return $this->retroceso;
    }

    public function id(): int
    {
        return $this->retroceso->id;
    }

    private function registrar(Model $modelo, string $accion, array $antes, ?array $despues = null): void
    {
        $this->retroceso->detalles()->create([
            'tabla'         => $modelo->getTable(),
            'registro_id'   => $modelo->getKey(),
            'accion'        => $accion,
            'datos_antes'   => $antes,
            'datos_despues' => $despues,
        ]);
    }
}
