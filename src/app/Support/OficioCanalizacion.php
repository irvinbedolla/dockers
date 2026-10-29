<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Canalización de un caso de excepción (art. 685 Ter LFT) a una institución.
 * Un renglón de excepcion_oficios por caso e institución, con dos documentos:
 *
 *   - Formato de aceptación de canalización: lo firma la persona usuaria
 *     (se marca "aceptación firmada");
 *   - Oficio de canalización institucional para atención integral, con el
 *     formato 685 Ter como anexo.
 *
 * Número CCL/UJDCGI/001/2026: consecutivo por año, de todo el departamento.
 * Se aparta al generar el oficio (imprimir sólo la aceptación no lo gasta) y
 * se puede corregir a mano. Titular y cargo de la institución se escriben en
 * cada oficio; se propone el último que se usó.
 */
class OficioCanalizacion
{
    public const PREFIJO = 'CCL/UJDCGI';

    public const CARGO_ATIENDE = 'Departamento de Conciliación, Género e Igualdad';

    public static function migrado(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasTable('excepcion_oficios');
    }

    /** Ya corrió la migración de la aceptación de canalización. */
    public static function conAceptacion(): bool
    {
        static $hay = null;

        return $hay ??= self::migrado() && Schema::hasColumn('excepcion_oficios', 'aceptacion_firmada');
    }

    public static function numero(int $consecutivo, int $anio): string
    {
        return sprintf('%s/%03d/%d', self::PREFIJO, $consecutivo, $anio);
    }

    /** Si el número escrito tiene la forma CCL/UJDCGI/045/2026, su consecutivo. */
    public static function consecutivoDe(string $numero, int $anio): ?int
    {
        return preg_match('#^'.preg_quote(self::PREFIJO, '#').'/(\d{1,6})/'.$anio.'$#', trim($numero), $m) ? (int) $m[1] : null;
    }

    public static function siguiente(int $anio): int
    {
        return (int) DB::table('excepcion_oficios')->where('anio', $anio)->max('consecutivo') + 1;
    }

    /** Lo guardado del caso, por institución. */
    public static function delCaso(int $casoId): array
    {
        return self::migrado()
            ? DB::table('excepcion_oficios')->where('caso_id', $casoId)->get()->keyBy('institucion')->all()
            : [];
    }

    /**
     * Valores para la tarjeta de una institución: lo guardado encima de lo
     * propuesto. 'numeroPropuesto' es el que tocaría si se genera hoy.
     */
    public static function tarjeta(?object $guardado, string $institucion, ?string $sede, array $tipos, ?User $usuario): array
    {
        $ultimo = DB::table('excepcion_oficios')->where('institucion', $institucion)
            ->when($guardado, fn ($q) => $q->where('id', '!=', $guardado->id))
            ->orderByDesc('id')->first();
        $anio = (int) now()->format('Y');

        $propuesta = [
            'numero'             => null,
            'fecha'              => now()->toDateString(),
            'lugar'              => ($sede ?: 'Morelia').', Michoacán',
            'destinatario'       => $ultimo->destinatario ?? null,
            'cargo'              => $ultimo->cargo ?? null,
            'area_servicio'      => $ultimo->area_servicio ?? null,
            'motivo'             => implode(', ', array_map(fn ($k) => CasosExcepcion::TIPOS[$k] ?? $k, $tipos)) ?: null,
            'cargo_atiende'      => self::CARGO_ATIENDE,
            'aceptacion_firmada' => false,
        ];

        $valores = $propuesta;
        foreach ((array) $guardado as $campo => $valor) {
            if (array_key_exists($campo, $propuesta) && $valor !== null && $valor !== '') {
                $valores[$campo] = $valor;
            }
        }
        $valores['aceptacion_firmada'] = (bool) ($guardado->aceptacion_firmada ?? false);

        return $valores + [
            'guardado'        => (bool) $guardado,
            'conOficio'       => ! empty($guardado->numero),
            'numeroPropuesto' => self::numero(self::siguiente($anio), $anio),
        ];
    }

    /**
     * Guarda los datos de la canalización. Con $conOficio aparta el número
     * de oficio si todavía no tiene (el escrito a mano o el siguiente).
     */
    public static function guardar(object $caso, string $institucion, array $datos, User $usuario, bool $conOficio): object
    {
        DB::transaction(function () use ($caso, $institucion, $datos, $usuario, $conOficio) {
            $existe = DB::table('excepcion_oficios')->where('caso_id', $caso->id)->where('institucion', $institucion)->lockForUpdate()->first();
            $fecha = $datos['fecha'] ?? ($existe->fecha ?? now()->toDateString());
            $texto = fn (string $campo) => trim((string) ($datos[$campo] ?? '')) !== '' ? trim($datos[$campo]) : null;

            $fila = [
                'fecha'         => $fecha,
                'lugar'         => $texto('lugar') ?? ($existe->lugar ?? (($caso->delegacion ?? null) ?: 'Morelia').', Michoacán'),
                'destinatario'  => $texto('destinatario'),
                'cargo'         => $texto('cargo'),
                'user_id'       => $usuario->id,
                'updated_at'    => now(),
            ];

            if (self::conAceptacion()) {
                $firmada = ($datos['aceptacion_firmada'] ?? '0') === '1';
                $fila += [
                    'area_servicio'         => $texto('area_servicio'),
                    'motivo'                => $texto('motivo'),
                    'cargo_atiende'         => $texto('cargo_atiende'),
                    'aceptacion_firmada'    => $firmada,
                    'aceptacion_firmada_at' => $firmada ? ($existe->aceptacion_firmada_at ?? now()) : null,
                ];
            }

            // Número de oficio: se aparta al generar el oficio; si ya tiene, se puede corregir.
            $numero = $texto('numero');
            $anio = (int) substr($fecha, 0, 4);
            if (! empty($existe->numero)) {
                $fila['numero'] = $numero ?? $existe->numero;
                $fila['anio'] = $anio;
                $fila['consecutivo'] = ($numero ? self::consecutivoDe($numero, $anio) : null) ?? $existe->consecutivo;
            } elseif ($conOficio || ! self::conAceptacion()) {
                // Bloquea el año para que dos oficios no tomen el mismo número.
                DB::table('excepcion_oficios')->where('anio', $anio)->lockForUpdate()->get(['id']);
                $consecutivo = ($numero ? self::consecutivoDe($numero, $anio) : null) ?? self::siguiente($anio);
                $fila['numero'] = $numero ?? self::numero($consecutivo, $anio);
                $fila['anio'] = $anio;
                $fila['consecutivo'] = $consecutivo;
            }

            if ($existe) {
                DB::table('excepcion_oficios')->where('id', $existe->id)->update($fila);
            } else {
                DB::table('excepcion_oficios')->insert($fila + [
                    'caso_id' => $caso->id, 'institucion' => $institucion, 'created_at' => now(),
                ]);
            }
        });

        return DB::table('excepcion_oficios')->where('caso_id', $caso->id)->where('institucion', $institucion)->first();
    }
}
