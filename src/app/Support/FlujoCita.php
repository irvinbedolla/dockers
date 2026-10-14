<?php

namespace App\Support;

use App\Models\Ajuste;
use App\Models\User;

/**
 * Flujo nuevo "cita en línea → solicitud": prellenado, aviso al auxiliar,
 * "Mi módulo" y llamado en pantallas. Todo cuelga de este interruptor
 * global (Administración → Flujo de citas):
 *
 *   apagado  Como siempre. Nada nuevo se ve ni se ejecuta.
 *   pruebas  Sólo las citas de prueba y los auxiliares piloto usan el flujo
 *            nuevo; todo lo demás sigue tradicional.
 *   activo   El banderazo: todas las citas en línea, en todo el estado.
 *
 * Una cita es de prueba si se marcó así en /turnos/nuevo (sólo Super
 * Usuario) o si se agendó con uno de los correos de prueba.
 */
class FlujoCita
{
    public const APAGADO = 'apagado';
    public const PRUEBAS = 'pruebas';
    public const ACTIVO  = 'activo';

    public const ESTADOS = [
        self::APAGADO => 'Apagado',
        self::PRUEBAS => 'Pruebas',
        self::ACTIVO  => 'Activo',
    ];

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

    public static function estado(): string
    {
        $estado = (string) Ajuste::valor('flujo_cita.estado', self::APAGADO);

        return array_key_exists($estado, self::ESTADOS) ? $estado : self::APAGADO;
    }

    public static function apagado(): bool
    {
        return self::estado() === self::APAGADO;
    }

    /** ¿El flujo nuevo aplica a esta cita? */
    public static function aplicaA(object $cita): bool
    {
        return match (self::estado()) {
            self::ACTIVO  => true,
            self::PRUEBAS => (bool) data_get($cita, 'es_prueba'),
            default       => false,
        };
    }

    /** ¿Esta persona ve "Mi módulo" y lo demás del flujo nuevo? */
    public static function habilitadoPara(?User $usuario): bool
    {
        if ($usuario === null) {
            return false;
        }

        return match (self::estado()) {
            self::ACTIVO  => true,
            self::PRUEBAS => $usuario->hasRole('Super Usuario') || in_array((int) $usuario->id, self::pilotos(), true),
            default       => false,
        };
    }

    /** @return string[] correos en minúsculas */
    public static function correosPrueba(): array
    {
        $texto = (string) Ajuste::valor('flujo_cita.correos_prueba', '');

        return array_values(array_unique(array_filter(array_map(
            fn ($c) => mb_strtolower(trim($c)),
            preg_split('/[\s,;]+/', $texto) ?: []
        ))));
    }

    public static function esCorreoPrueba(?string $correo): bool
    {
        $correo = mb_strtolower(trim((string) $correo));

        return $correo !== '' && in_array($correo, self::correosPrueba(), true);
    }

    /** @return int[] ids de los auxiliares piloto */
    public static function pilotos(): array
    {
        $ids = json_decode((string) Ajuste::valor('flujo_cita.pilotos', '[]'), true);

        return array_values(array_map('intval', is_array($ids) ? $ids : []));
    }

    /**
     * ¿El formulario de cita pide CURP y domicilio (para el prellenado)?
     *   activo  → siempre.
     *   pruebas → en /generar-cita sólo con ?pruebas=1; en ventanilla sólo
     *             a un Super Usuario. La operación real no cambia.
     */
    public static function camposExtra(bool $ventanilla): bool
    {
        if (! self::migrado()) {
            return false;
        }

        return match (self::estado()) {
            self::ACTIVO  => true,
            self::PRUEBAS => $ventanilla
                ? (auth()->check() && auth()->user()->hasRole('Super Usuario'))
                : request()->boolean('pruebas'),
            default       => false,
        };
    }

    /**
     * CURP y domicilio del formulario de cita, validados, listos para
     * Recepcion::create. Con el flujo activo son obligatorios; en pruebas,
     * sólo si el formulario los traía (camposExtra).
     *
     * @return array{0: array, 1: ?string} [columnas, error]
     */
    public static function camposDeCita(\Illuminate\Http\Request $request): array
    {
        if (! self::migrado()) {
            return [[], null];
        }

        // Con el banderazo (activo) son obligatorios aunque el formulario no
        // los traiga (p. ej. una página abierta desde antes de activarlo: se
        // regresa con el aviso y ya muestra los campos). En pruebas, sólo si
        // el formulario los pidió.
        if (! $request->has('curp') && self::estado() !== self::ACTIVO) {
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

    /** Minutos antes de la hora de la cita en que se avisa al auxiliar (fase 3). */
    public static function minutosAviso(): int
    {
        return max(1, min(60, (int) Ajuste::valor('flujo_cita.aviso_minutos', 10)));
    }
}
