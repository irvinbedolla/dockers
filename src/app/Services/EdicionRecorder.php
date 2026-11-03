<?php

namespace App\Services;

use App\Models\HistorialEdicion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Ejecuta las altas, cambios y bajas de una edición dejando en
 * historial_ediciones / historial_edicion_detalles solo lo que realmente
 * cambió en la base de datos.
 *
 * Debe usarse dentro de la misma transacción que la edición. El encabezado
 * se crea hasta el primer cambio real: un guardado que no modifica nada no
 * deja registro.
 *
 *   $edicion = EdicionRecorder::iniciar($solicitud, $audienciaId, 'Audiencia');
 *   $edicion->actualizar($solicitante, [...]);
 *   $edicion->crear(new SeerMotivo([...]));
 *   $edicion->borrar(SeerCitados::where(...));
 */
class EdicionRecorder
{
    private ?HistorialEdicion $edicion = null;

    private function __construct(
        private array $encabezado,
    ) {}

    public static function iniciar(Model $solicitud, $audienciaId = null, ?string $origen = null): self
    {
        $user = auth()->user();

        return new self([
            'solicitud_id' => $solicitud->getKey(),
            'audiencia_id' => $audienciaId ?: null,
            'NUE'          => $solicitud->getAttribute('NUE'),
            'delegacion'   => $solicitud->getAttribute('delegacion'),
            'origen'       => $origen,
            'user_id'      => $user->id ?? null,
            'user_nombre'  => $user->name ?? null,
            'user_rol'     => $user ? $user->getRoleNames()->first() : null,
            'ip'           => request()->ip(),
        ]);
    }

    /**
     * Guarda los cambios en el modelo y registra solo las columnas cuyo valor
     * en BD es distinto antes y después (un "1500" que se reenvía como
     * "1500.00" no es un cambio). Con $forzar se ignora $fillable, igual que
     * un update() directo sobre la consulta.
     */
    public function actualizar(Model $modelo, array $cambios, bool $forzar = false): void
    {
        $antes = $modelo->getRawOriginal();

        $forzar ? $modelo->forceFill($cambios) : $modelo->fill($cambios);
        $cambiado = $modelo->getDirty();

        if (empty($cambiado)) {
            return;
        }

        $modelo->save();

        $despues = $modelo->fresh()->getRawOriginal();
        $datosAntes = $datosDespues = [];
        foreach (array_keys($cambiado) as $columna) {
            $a = $antes[$columna] ?? null;
            $d = $despues[$columna] ?? null;
            if (!$this->mismoValor($a, $d)) {
                $datosAntes[$columna]   = $a;
                $datosDespues[$columna] = $d;
            }
        }

        if (!empty($datosDespues)) {
            $this->registrar($modelo, 'updated', $datosAntes, $datosDespues);
        }
    }

    /**
     * Guarda un modelo nuevo y registra el renglón completo.
     */
    public function crear(Model $modelo): void
    {
        $modelo->save();
        $this->registrar($modelo, 'created', null, $modelo->fresh()->getRawOriginal());
    }

    /**
     * Borra los registros de la consulta guardando cada renglón completo.
     * Se borra por llave primaria para eliminar exactamente lo registrado.
     */
    public function borrar(Builder $query): int
    {
        $registros = $query->get();

        if ($registros->isEmpty()) {
            return 0;
        }

        foreach ($registros as $registro) {
            $this->registrar($registro, 'deleted', $registro->getRawOriginal());
        }

        return $registros->first()->newQuery()->whereKey($registros->modelKeys())->delete();
    }

    /**
     * Registra un cambio que no se refleja en las columnas (por ejemplo un archivo que se reemplaza sin cambiar el nombre)
     */
    public function anotar(Model $modelo, array $antes, array $despues): void
    {
        $this->registrar($modelo, 'updated', $antes, $despues);
    }

    private function mismoValor($a, $b): bool
    {
        if ($a === $b) {
            return true;
        }
        if (is_numeric($a) && is_numeric($b)) {
            return $a == $b;
        }

        return false;
    }

    private function registrar(Model $modelo, string $accion, ?array $antes, ?array $despues = null): void
    {
        $this->edicion ??= HistorialEdicion::create($this->encabezado);

        $this->edicion->detalles()->create([
            'tabla'         => $modelo->getTable(),
            'registro_id'   => $modelo->getKey(),
            'accion'        => $accion,
            'datos_antes'   => $antes,
            'datos_despues' => $despues,
        ]);
    }
}
