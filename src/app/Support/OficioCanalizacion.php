<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Oficio de canalización institucional para atención integral (casos de
 * excepción, art. 685 Ter LFT): uno por cada institución a la que se
 * canaliza el caso, con el formato 685 Ter como anexo.
 *
 * Número CCL/UJDCGI/001/2026: consecutivo por año, de todo el departamento.
 * Se aparta al guardar el oficio por primera vez y se puede corregir a mano.
 * Titular y cargo de la institución se escriben en cada oficio (no se tienen
 * los datos); se propone el último que se usó para esa institución.
 */
class OficioCanalizacion
{
    public const PREFIJO = 'CCL/UJDCGI';

    public static function migrado(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasTable('excepcion_oficios');
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

    /** Oficios ya guardados del caso, por institución. */
    public static function delCaso(int $casoId): array
    {
        return self::migrado()
            ? DB::table('excepcion_oficios')->where('caso_id', $casoId)->get()->keyBy('institucion')->all()
            : [];
    }

    /** Lo que se propone para un oficio nuevo. */
    public static function propuesta(string $institucion, ?string $sede): array
    {
        $ultimo = DB::table('excepcion_oficios')->where('institucion', $institucion)->orderByDesc('id')->first(['destinatario', 'cargo']);
        $anio = (int) now()->format('Y');

        return [
            'numero'       => self::numero(self::siguiente($anio), $anio),
            'fecha'        => now()->toDateString(),
            'lugar'        => ($sede ?: 'Morelia').', Michoacán',
            'destinatario' => $ultimo->destinatario ?? null,
            'cargo'        => $ultimo->cargo ?? null,
        ];
    }

    /** Guarda (o corrige) el oficio de una institución del caso; devuelve su id. */
    public static function guardar(object $caso, string $institucion, array $datos, User $usuario): int
    {
        return DB::transaction(function () use ($caso, $institucion, $datos, $usuario) {
            $existe = DB::table('excepcion_oficios')->where('caso_id', $caso->id)->where('institucion', $institucion)->lockForUpdate()->first();
            $anio = (int) substr($datos['fecha'] ?? now()->toDateString(), 0, 4);

            $numero = trim((string) ($datos['numero'] ?? ''));
            $consecutivo = $numero !== '' ? self::consecutivoDe($numero, $anio) : null;

            if (! $existe) {
                // Bloquea el año para que dos oficios no tomen el mismo número.
                DB::table('excepcion_oficios')->where('anio', $anio)->lockForUpdate()->get(['id']);
                $consecutivo ??= self::siguiente($anio);
                $numero = $numero !== '' ? $numero : self::numero($consecutivo, $anio);
            } else {
                $consecutivo ??= (int) $existe->consecutivo;
                $numero = $numero !== '' ? $numero : $existe->numero;
            }

            $fila = [
                'numero'       => $numero,
                'anio'         => $anio,
                'consecutivo'  => $consecutivo,
                'fecha'        => $datos['fecha'] ?? now()->toDateString(),
                'lugar'        => trim((string) ($datos['lugar'] ?? '')) !== '' ? trim($datos['lugar'])
                    : ($existe->lugar ?? (($caso->delegacion ?? null) ?: 'Morelia').', Michoacán'),
                'destinatario' => $datos['destinatario'] ?? null,
                'cargo'        => $datos['cargo'] ?? null,
                'user_id'      => $usuario->id,
                'updated_at'   => now(),
            ];

            if ($existe) {
                DB::table('excepcion_oficios')->where('id', $existe->id)->update($fila);

                return (int) $existe->id;
            }

            return (int) DB::table('excepcion_oficios')->insertGetId($fila + [
                'caso_id' => $caso->id, 'institucion' => $institucion, 'created_at' => now(),
            ]);
        });
    }
}
