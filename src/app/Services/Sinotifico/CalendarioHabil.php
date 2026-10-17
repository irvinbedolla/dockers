<?php

namespace App\Services\Sinotifico;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Días hábiles de una sede: lunes a viernes, sin los días que el Centro marca
 * como inhábiles en `dias_inhabiles` (los generales de la sede: sin usuario,
 * descripción "Inhabil" y tipo "Todos"; los bloqueos por conciliador no
 * cuentan para el plazo de notificación).
 */
class CalendarioHabil
{
    /** @var array<string, true> fechas Y-m-d inhábiles */
    private array $inhabiles = [];

    public function __construct(
        private readonly string $sede,
        CarbonImmutable $desde,
        CarbonImmutable $hasta,
    ) {
        $rangos = DB::table('dias_inhabiles')
            ->where('centro', $sede)
            ->whereNull('user_id')
            ->where('descripcion', 'Inhabil')
            ->where('tipo', 'Todos')
            ->where('fecha_inicio', '<=', $hasta->toDateString())
            ->where('fecha_final', '>=', $desde->toDateString())
            ->get(['fecha_inicio', 'fecha_final']);

        foreach ($rangos as $rango) {
            $dia = CarbonImmutable::parse($rango->fecha_inicio)->startOfDay();
            $fin = CarbonImmutable::parse($rango->fecha_final)->startOfDay();
            for (; $dia->lte($fin); $dia = $dia->addDay()) {
                $this->inhabiles[$dia->toDateString()] = true;
            }
        }
    }

    public function esHabil(CarbonImmutable $dia): bool
    {
        return ! $dia->isWeekend() && ! isset($this->inhabiles[$dia->toDateString()]);
    }

    /** El día hábil que queda `$dias` días hábiles antes de `$fecha`. */
    public function restarHabiles(CarbonImmutable $fecha, int $dias): CarbonImmutable
    {
        $dia = $fecha->startOfDay();
        while ($dias > 0) {
            $dia = $dia->subDay();
            if ($this->esHabil($dia)) {
                $dias--;
            }
        }

        return $dia;
    }

    /**
     * Días hábiles de `$desde` (exclusivo) a `$hasta` (inclusivo).
     * Negativo si `$hasta` ya pasó. 0 si son el mismo día.
     */
    public function habilesEntre(CarbonImmutable $desde, CarbonImmutable $hasta): int
    {
        $desde = $desde->startOfDay();
        $hasta = $hasta->startOfDay();

        if ($desde->eq($hasta)) {
            return 0;
        }

        $signo = $hasta->gt($desde) ? 1 : -1;
        [$a, $b] = $signo > 0 ? [$desde, $hasta] : [$hasta, $desde];

        $cuenta = 0;
        for ($dia = $a->addDay(); $dia->lte($b); $dia = $dia->addDay()) {
            if ($this->esHabil($dia)) {
                $cuenta++;
            }
        }

        return $signo * $cuenta;
    }

    public function sede(): string
    {
        return $this->sede;
    }
}
