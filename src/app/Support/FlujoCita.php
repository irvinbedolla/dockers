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

    /** Minutos antes de la hora de la cita en que se avisa al auxiliar (fase 3). */
    public static function minutosAviso(): int
    {
        return max(1, min(60, (int) Ajuste::valor('flujo_cita.aviso_minutos', 10)));
    }
}
