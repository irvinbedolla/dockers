<?php

namespace App\Support;

use App\Models\User;

/**
 * Flujo "cita → solicitud": prellenado con revisión, aviso al auxiliar de
 * quién sigue, "Mi módulo" y llamado en pantallas.
 *
 * Ya está activo para siempre en todas las sedes, para las citas en línea y
 * las de ventanilla (se quitó el interruptor de Administración → Flujo de
 * citas). Lo único que queda de la etapa de pruebas son las citas de prueba
 * (recepcion.es_prueba): las crea `php artisan citas:demo-flujo`, no salen en
 * la pantalla de espera ni en los conteos y se borran con
 * `php artisan citas:limpiar-pruebas`.
 */
class FlujoCita
{
    /** Minutos antes de que le toque a la siguiente persona en que se avisa al auxiliar. */
    public const AVISO_MINUTOS = 10;

    /** ¿Ya corrió la migración de la fase 0? (se pregunta una vez por petición) */
    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= \Illuminate\Support\Facades\Schema::hasColumn('recepcion', 'es_prueba');
    }

    /** Fase 4: ¿ya corrió la migración de recepcion.atencion_fin_at? */
    public static function conFinAtencion(): bool
    {
        static $listo = null;

        return $listo ??= self::migrado() && \Illuminate\Support\Facades\Schema::hasColumn('recepcion', 'atencion_fin_at');
    }

    /** ¿El flujo aplica a esta cita? Siempre, una vez corrida la migración. */
    public static function aplicaA(object $cita): bool
    {
        return self::migrado();
    }

    /** ¿Esta persona ve "Mi módulo" y lo demás del flujo? */
    public static function habilitadoPara(?User $usuario): bool
    {
        return $usuario !== null && self::migrado();
    }

    /** ¿El formulario de cita pide CURP y domicilio (para el prellenado)? */
    public static function camposExtra(bool $ventanilla = false): bool
    {
        return self::migrado();
    }

    /**
     * CURP y domicilio del formulario de cita, validados, listos para
     * Recepcion::create. Son obligatorios.
     *
     * @return array{0: array, 1: ?string} [columnas, error]
     */
    public static function camposDeCita(\Illuminate\Http\Request $request): array
    {
        if (! self::migrado()) {
            return [[], null];
        }

        $curp = Curp::normalizar($request->input('curp'));
        if ($curp === '') {
            return [[], 'Escribe la CURP y el domicilio: ahora son obligatorios para agendar.'];
        }
        if ($error = Curp::error($curp)) {
            return [[], $error];
        }

        $domicilio = [
            'calle'    => trim((string) $request->input('domicilio_calle')),
            'num_ext'  => trim((string) $request->input('domicilio_numero')),
            'num_int'  => trim((string) $request->input('domicilio_interior')),
            'colonia'  => trim((string) $request->input('domicilio_colonia')),
            'cp'       => preg_replace('/\D/', '', (string) $request->input('domicilio_cp')),
        ];
        if ($domicilio['calle'] === '' || $domicilio['num_ext'] === '' || $domicilio['colonia'] === '') {
            return [[], 'Escribe la calle, el número y la colonia del domicilio.'];
        }
        if (strlen($domicilio['cp']) !== 5) {
            return [[], 'El código postal debe tener 5 dígitos.'];
        }

        $domicilio = array_map(fn ($v) => mb_substr(mb_strtoupper($v), 0, 60), $domicilio);

        return [[
            'curp'      => $curp,
            'domicilio' => json_encode($domicilio, JSON_UNESCAPED_UNICODE),
        ], null];
    }

    /** Minutos antes de que siga la próxima persona en que se avisa al auxiliar. */
    public static function minutosAviso(): int
    {
        return self::AVISO_MINUTOS;
    }
}
