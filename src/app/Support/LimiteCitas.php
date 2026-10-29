<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Tope de citas en línea por conexión (IP), correo y teléfono, para que una
 * sola oficina no acapare los turnos. Se fija en Administración → Módulos
 * (tabla limites_citas, una fila); 0 = sin límite. Cada intento que se
 * detiene queda en citas_bloqueadas y se ve en /estadisticas/turnos.
 *
 * Cuentan las citas en línea registradas (created_at) dentro del periodo,
 * sin importar para qué día son ni si después se cancelaron; las de prueba
 * no cuentan. Las IP exentas (por ejemplo, la de un módulo del propio Centro
 * donde se ayuda a la gente a agendar) no tienen tope.
 */
class LimiteCitas
{
    public const PERIODOS = [
        'dia'    => 'por día (desde las 00:00)',
        'semana' => 'en los últimos 7 días',
        'mes'    => 'en los últimos 30 días',
    ];

    public const MOTIVOS = [
        'ip'       => 'Conexión (IP)',
        'correo'   => 'Correo',
        'telefono' => 'Teléfono',
    ];

    /** Lo más alto que se deja poner; más que esto es lo mismo que sin límite. */
    public const MAXIMO = 500;

    /** ¿Ya corrió la migración (recepcion.ip, limites_citas, citas_bloqueadas)? */
    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= Schema::hasColumn('recepcion', 'ip')
            && Schema::hasTable('limites_citas')
            && Schema::hasTable('citas_bloqueadas');
    }

    /** El tope vigente (sin migración: todo en 0, es decir, sin límite). */
    public static function config(): object
    {
        $base = (object) [
            'por_ip' => 0, 'por_correo' => 0, 'por_telefono' => 0, 'periodo' => 'dia', 'exentas' => '',
            'actualizado_por_nombre' => null, 'updated_at' => null,
        ];
        if (! self::migrado()) {
            return $base;
        }
        $fila = DB::table('limites_citas')->orderBy('id')->first();
        if (! $fila) {
            return $base;
        }
        $fila->periodo = array_key_exists($fila->periodo, self::PERIODOS) ? $fila->periodo : 'dia';
        $fila->exentas = (string) $fila->exentas;

        return $fila;
    }

    /** ¿Hay algún tope activo? */
    public static function activo(?object $c = null): bool
    {
        $c ??= self::config();

        return $c->por_ip > 0 || $c->por_correo > 0 || $c->por_telefono > 0;
    }

    /** Guarda el tope (Administración → Módulos). */
    public static function guardar(array $datos, ?object $usuario): void
    {
        $valores = [
            'por_ip'                 => (int) $datos['por_ip'],
            'por_correo'             => (int) ($datos['por_correo'] ?? 0),
            'por_telefono'           => (int) ($datos['por_telefono'] ?? 0),
            'periodo'                => $datos['periodo'],
            'exentas'                => implode("\n", self::listaExentas((string) ($datos['exentas'] ?? ''))) ?: null,
            'actualizado_por'        => $usuario?->id,
            'actualizado_por_nombre' => $usuario ? mb_substr((string) $usuario->name, 0, 120) : null,
            'updated_at'             => now(),
        ];

        $id = DB::table('limites_citas')->orderBy('id')->value('id');
        if ($id) {
            DB::table('limites_citas')->where('id', $id)->update($valores);
        } else {
            DB::table('limites_citas')->insert($valores + ['created_at' => now()]);
        }
    }

    /** IPs o rangos (CIDR) exentos, uno por renglón o separados por comas; los inválidos se descartan. */
    public static function listaExentas(string $texto): array
    {
        $lista = [];
        foreach (preg_split('/[\s,;]+/', $texto) as $v) {
            $v = trim($v);
            if ($v !== '' && self::ipValida($v)) {
                $lista[] = $v;
            }
        }

        return array_values(array_unique($lista));
    }

    public static function ipValida(string $v): bool
    {
        [$ip, $mascara] = array_pad(explode('/', $v, 2), 2, null);
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        if ($mascara === null) {
            return true;
        }
        $max = str_contains($ip, ':') ? 128 : 32;

        return ctype_digit($mascara) && (int) $mascara >= 0 && (int) $mascara <= $max;
    }

    /** Desde cuándo se cuentan las citas del periodo. */
    public static function desde(string $periodo): Carbon
    {
        return match ($periodo) {
            'semana' => now()->subDays(7),
            'mes'    => now()->subDays(30),
            default  => now()->startOfDay(),
        };
    }

    /**
     * ¿Esta cita en línea pasa el tope? null si sí; si no, por qué:
     * ['motivo', 'limite', 'llevaba', 'periodo', 'mensaje'].
     */
    public static function revisar(?string $ip, ?string $correo, ?string $telefono): ?array
    {
        if (! self::migrado()) {
            return null;
        }
        $c = self::config();
        if (! self::activo($c)) {
            return null;
        }
        if ($ip && $c->exentas !== '' && IpUtils::checkIp($ip, self::listaExentas($c->exentas))) {
            return null;
        }

        $desde = self::desde($c->periodo);
        $base = fn () => DB::table('recepcion')
            ->where('origen', 'linea')
            ->where('created_at', '>=', $desde)
            ->where(fn ($w) => $w->where('es_prueba', 0)->orWhereNull('es_prueba'));

        $revisiones = [
            'ip'       => [$c->por_ip, $ip ? fn () => $base()->where('ip', $ip)->count() : null],
            'correo'   => [$c->por_correo, self::correo($correo) ? fn () => $base()->whereRaw('LOWER(TRIM(correo)) = ?', [self::correo($correo)])->count() : null],
            'telefono' => [$c->por_telefono, self::telefono($telefono) ? fn () => $base()->where('telefono', self::telefono($telefono))->count() : null],
        ];

        foreach ($revisiones as $motivo => [$limite, $contar]) {
            if ($limite <= 0 || $contar === null) {
                continue;
            }
            $llevaba = $contar();
            if ($llevaba >= $limite) {
                return [
                    'motivo'  => $motivo,
                    'limite'  => (int) $limite,
                    'llevaba' => $llevaba,
                    'periodo' => $c->periodo,
                    'mensaje' => self::mensaje($motivo, (int) $limite, $c->periodo),
                ];
            }
        }

        return null;
    }

    /** Lo que ve la persona cuando el tope la detiene. */
    public static function mensaje(string $motivo, int $limite, string $periodo): string
    {
        $desde = [
            'ip'       => 'desde esta conexión a internet',
            'correo'   => 'con este correo electrónico',
            'telefono' => 'con este número de teléfono',
        ][$motivo] ?? '';
        $cuando = ['dia' => 'hoy', 'semana' => 'en los últimos 7 días', 'mes' => 'en los últimos 30 días'][$periodo] ?? 'hoy';
        $citas = $limite === 1 ? 'una cita' : $limite.' citas';

        return "No se pudo registrar la cita: {$cuando} ya se registró el máximo de {$citas} en línea {$desde}. "
            .'Si necesitas otra cita, acude directamente a la sede del Centro o comunícate por teléfono; el trámite es gratuito.';
    }

    /** Deja constancia del intento detenido (para /estadisticas/turnos). */
    public static function registrarBloqueo(array $bloqueo, ?string $ip, ?string $agente, array $datos): void
    {
        try {
            DB::table('citas_bloqueadas')->insert([
                'motivo'      => $bloqueo['motivo'],
                'limite'      => $bloqueo['limite'],
                'llevaba'     => min($bloqueo['llevaba'], 65535),
                'periodo'     => $bloqueo['periodo'],
                'ip'          => $ip,
                'user_agent'  => self::agente($agente),
                'correo'      => self::correo($datos['email'] ?? null) ? mb_substr(self::correo($datos['email']), 0, 150) : null,
                'telefono'    => self::telefono($datos['telefono'] ?? null) ? mb_substr(self::telefono($datos['telefono']), 0, 20) : null,
                'curp'        => isset($datos['curp']) && is_string($datos['curp']) ? mb_substr(mb_strtoupper(trim($datos['curp'])), 0, 18) : null,
                'nombre'      => isset($datos['nombre']) && is_string($datos['nombre']) ? mb_substr(trim($datos['nombre']), 0, 150) : null,
                'delegacion'  => isset($datos['delegacion']) && is_string($datos['delegacion']) ? mb_substr($datos['delegacion'], 0, 40) : null,
                'tipo'        => isset($datos['tipo']) && is_string($datos['tipo']) ? mb_substr($datos['tipo'], 0, 30) : null,
                'fecha_turno' => isset($datos['fecha_turno']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $datos['fecha_turno']) ? $datos['fecha_turno'] : null,
                'hora_turno'  => isset($datos['hora_turno']) && is_string($datos['hora_turno']) ? substr($datos['hora_turno'], 0, 5) : null,
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Que no guardar la constancia no tumbe la respuesta a la persona.
            report($e);
        }
    }

    /** Navegador recortado a lo que cabe en la columna. */
    public static function agente(?string $agente): ?string
    {
        $agente = trim((string) $agente);

        return $agente !== '' ? mb_substr($agente, 0, 255) : null;
    }

    public static function correo(?string $correo): ?string
    {
        $correo = mb_strtolower(trim((string) $correo));

        return $correo !== '' ? $correo : null;
    }

    public static function telefono(?string $telefono): ?string
    {
        $tel = preg_replace('/\D+/', '', (string) $telefono);

        return $tel !== '' ? $tel : null;
    }

    /** Para Administración → Módulos: intentos detenidos hoy y la IP con más citas en línea hoy. */
    public static function hoy(): array
    {
        if (! self::migrado()) {
            return ['bloqueos' => 0, 'top' => null, 'citas' => 0];
        }
        $inicio = now()->startOfDay();
        $base = DB::table('recepcion')->where('origen', 'linea')->where('created_at', '>=', $inicio)
            ->where(fn ($w) => $w->where('es_prueba', 0)->orWhereNull('es_prueba'));

        $top = (clone $base)->whereNotNull('ip')
            ->select('ip', DB::raw('COUNT(*) as citas'))
            ->groupBy('ip')->orderByDesc('citas')->first();

        return [
            'bloqueos' => DB::table('citas_bloqueadas')->where('created_at', '>=', $inicio)->count(),
            'top'      => $top && $top->citas > 1 ? $top : null,
            'citas'    => (clone $base)->count(),
        ];
    }

    /** Navegador y sistema en pocas palabras (para las tablas). */
    public static function navegador(?string $agente): string
    {
        $a = (string) $agente;
        if ($a === '') {
            return '—';
        }
        $nav = match (true) {
            str_contains($a, 'Edg/')                                  => 'Edge',
            str_contains($a, 'OPR/') || str_contains($a, 'Opera')     => 'Opera',
            str_contains($a, 'SamsungBrowser')                        => 'Samsung Internet',
            str_contains($a, 'Firefox/')                              => 'Firefox',
            str_contains($a, 'Chrome/') || str_contains($a, 'CriOS/') => 'Chrome',
            str_contains($a, 'Safari/')                               => 'Safari',
            default                                                   => Str::limit($a, 30, '…'),
        };
        $so = match (true) {
            str_contains($a, 'Android')                                     => 'Android',
            str_contains($a, 'iPhone') || str_contains($a, 'iPad')          => 'iOS',
            str_contains($a, 'Windows')                                     => 'Windows',
            str_contains($a, 'Mac OS X') || str_contains($a, 'Macintosh')   => 'Mac',
            str_contains($a, 'Linux')                                       => 'Linux',
            default                                                         => null,
        };

        return $so ? $nav.' · '.$so : $nav;
    }
}
