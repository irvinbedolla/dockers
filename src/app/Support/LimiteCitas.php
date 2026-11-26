<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Tope de citas en línea por conexión (IP), correo, teléfono y nombre
 * completo, para que una sola oficina no acapare los turnos. Se fija en
 * Administración → Módulos (tabla limites_citas, una fila); 0 = sin límite.
 * Cada criterio tiene su propio máximo y su propio periodo. Cada intento que
 * se detiene queda en citas_bloqueadas y se ve en /estadisticas/turnos.
 *
 * Periodos:
 *   - Móviles (dia, semana, quincena, mes): cuentan las citas registradas
 *     desde las 00:00 de hoy o en los últimos 7, 15 o 30 días.
 *   - A partir de que se registra (reg7, reg15, reg30): el plazo de N días
 *     empieza con la primera cita de ese dato (un correo, un teléfono…) que
 *     nunca se había usado o cuyo plazo anterior ya terminó; dentro del plazo
 *     cuentan todas sus citas y, al vencer, la siguiente abre un plazo nuevo.
 *
 * Cuentan las citas en línea registradas (created_at), sin importar para qué
 * día son ni si después se cancelaron; las de prueba no cuentan.
 *
 * Listas de correos (sirven también para ventanilla):
 *   - Lista blanca: esos correos no tienen ningún tope.
 *   - Lista negra: con esos correos no se registra la cita, ni en línea ni
 *     en ventanilla.
 *   Se escribe el correo completo o "@dominio.com" para todo un dominio.
 *
 * Las IP exentas (por ejemplo, la de un módulo del propio Centro donde se
 * ayuda a la gente a agendar) tampoco tienen tope.
 */
class LimiteCitas
{
    public const PERIODOS = [
        'dia'      => 'por día (desde las 00:00)',
        'semana'   => 'en los últimos 7 días',
        'quincena' => 'en los últimos 15 días',
        'mes'      => 'en los últimos 30 días',
        'reg7'     => 'en 7 días a partir de que se registra',
        'reg15'    => 'en 15 días a partir de que se registra',
        'reg30'    => 'en 30 días a partir de que se registra',
    ];

    /** Para el <select>: móviles y a partir del registro. */
    public const GRUPOS = [
        'Periodo móvil'               => ['dia', 'semana', 'quincena', 'mes'],
        'A partir de que se registra' => ['reg7', 'reg15', 'reg30'],
    ];

    /** Días de cada periodo (dia se cuenta desde las 00:00). */
    public const DIAS = ['dia' => 1, 'semana' => 7, 'quincena' => 15, 'mes' => 30, 'reg7' => 7, 'reg15' => 15, 'reg30' => 30];

    /** Criterios con tope propio: columna del máximo, del periodo y etiqueta. */
    public const CRITERIOS = [
        'ip'       => ['por_ip', 'periodo_ip', 'Conexión (IP)'],
        'correo'   => ['por_correo', 'periodo_correo', 'Correo'],
        'telefono' => ['por_telefono', 'periodo_telefono', 'Teléfono'],
        'nombre'   => ['por_nombre', 'periodo_nombre', 'Nombre completo'],
    ];

    /** Dónde aplica cada tope. */
    public const CANALES = [
        'linea'      => 'En línea',
        'ventanilla' => 'En ventanilla',
        'ambos'      => 'En las dos',
    ];

    /** Datos que no se pueden revisar en ventanilla (la IP es la de la oficina). */
    public const SOLO_LINEA = ['ip'];

    public const MOTIVOS = [
        'ip'       => 'Conexión (IP)',
        'correo'   => 'Correo',
        'telefono' => 'Teléfono',
        'nombre'   => 'Nombre',
        'negra'    => 'Lista negra',
    ];

    /** Lo más alto que se deja poner; más que esto es lo mismo que sin límite. */
    public const MAXIMO = 500;

    /** Cuántos días atrás se revisa para armar los plazos "a partir de que se registra". */
    private const HISTORIA_DIAS = 365;

    /** ¿Ya corrió la migración (recepcion.ip, limites_citas, citas_bloqueadas)? */
    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= Schema::hasColumn('recepcion', 'ip')
            && Schema::hasTable('limites_citas')
            && Schema::hasTable('citas_bloqueadas');
    }

    /** ¿Ya corrió la migración de un periodo por criterio, nombre y listas? */
    public static function porCriterio(): bool
    {
        static $listo = null;

        return $listo ??= self::migrado() && Schema::hasColumn('limites_citas', 'periodo_ip');
    }

    /** ¿Ya corrió la migración de sedes y canal (en línea / ventanilla) por criterio? */
    public static function porAlcance(): bool
    {
        static $listo = null;

        return $listo ??= self::porCriterio() && Schema::hasColumn('limites_citas', 'canal_ip');
    }

    /** ¿Ya corrió la migración de los trámites a los que aplica cada tope? */
    public static function porTramite(): bool
    {
        static $listo = null;

        return $listo ??= self::porAlcance() && Schema::hasColumn('limites_citas', 'tramites_ip');
    }

    /** Los trámites que se agendan (App\Support\AsignadorModulos::TRAMITES). */
    public static function tramites(): array
    {
        return AsignadorModulos::TRAMITES;
    }

    /** Lista guardada de trámites → nombres válidos; [] = todos. */
    public static function listaTramites($valor): array
    {
        $escritos = is_array($valor) ? $valor : preg_split('/\R+/', (string) $valor, -1, PREG_SPLIT_NO_EMPTY);
        $claves = array_map([self::class, 'sedeClave'], $escritos);
        $elegidos = array_values(array_filter(self::tramites(), fn ($t) => in_array(self::sedeClave($t), $claves, true)));

        return count($elegidos) === count(self::tramites()) ? [] : $elegidos;
    }

    /** Las sedes (nombre => clave), las de App\Support\FolioCita. */
    public static function sedes(): array
    {
        return array_keys(FolioCita::SEDES);
    }

    /** Sede sin acentos ni mayúsculas, para comparar (en recepcion hay "Zitácuaro" y "Zitacuaro"). */
    public static function sedeClave(?string $sede): string
    {
        return mb_strtolower(Str::ascii(trim((string) $sede)));
    }

    /** Lista guardada de sedes → nombres válidos; [] = todas. */
    public static function listaSedes($valor): array
    {
        $escritas = is_array($valor) ? $valor : preg_split('/\R+/', (string) $valor, -1, PREG_SPLIT_NO_EMPTY);
        $claves = array_map([self::class, 'sedeClave'], $escritas);
        $elegidas = array_values(array_filter(self::sedes(), fn ($s) => in_array(self::sedeClave($s), $claves, true)));

        return count($elegidas) === count(self::sedes()) ? [] : $elegidas;
    }

    /** El tope vigente (sin migración: sin límite, salvo la regla por nombre). */
    public static function config(): object
    {
        // Mientras no corra la migración nueva sigue la regla que estaba en
        // el código: 3 citas por nombre en 7 días.
        $base = (object) [
            'sedes_ip' => [], 'sedes_correo' => [], 'sedes_telefono' => [], 'sedes_nombre' => [],
            'tramites_ip' => [], 'tramites_correo' => [], 'tramites_telefono' => [], 'tramites_nombre' => [],
            'canal_ip' => 'linea', 'canal_correo' => 'linea', 'canal_telefono' => 'linea', 'canal_nombre' => 'linea',
            'por_ip' => 0, 'por_correo' => 0, 'por_telefono' => 0, 'por_nombre' => 3,
            'periodo' => 'dia', 'periodo_ip' => 'dia', 'periodo_correo' => 'dia', 'periodo_telefono' => 'dia', 'periodo_nombre' => 'semana',
            'exentas' => '', 'lista_blanca' => '', 'lista_negra' => '',
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
        foreach (self::CRITERIOS as [$col, $colPeriodo]) {
            $fila->{$col} = (int) ($fila->{$col} ?? $base->{$col});
            // Sin la migración nueva: el periodo único de antes (y 7 días para el nombre).
            $p = self::porCriterio() ? ($fila->{$colPeriodo} ?? null) : ($col === 'por_nombre' ? 'semana' : $fila->periodo);
            $fila->{$colPeriodo} = array_key_exists((string) $p, self::PERIODOS) ? $p : 'dia';
        }
        foreach (array_keys(self::CRITERIOS) as $criterio) {
            // Sin la migración de alcance: todas las sedes, sólo en línea.
            $fila->{'sedes_'.$criterio} = self::listaSedes($fila->{'sedes_'.$criterio} ?? '');
            // Sin la migración de trámites: todos.
            $fila->{'tramites_'.$criterio} = self::listaTramites($fila->{'tramites_'.$criterio} ?? '');
            $canal = $fila->{'canal_'.$criterio} ?? 'linea';
            $fila->{'canal_'.$criterio} = in_array($criterio, self::SOLO_LINEA, true) || ! isset(self::CANALES[$canal]) ? 'linea' : $canal;
        }
        $fila->exentas = (string) $fila->exentas;
        $fila->lista_blanca = (string) ($fila->lista_blanca ?? '');
        $fila->lista_negra = (string) ($fila->lista_negra ?? '');

        return $fila;
    }

    /** ¿Hay algún tope o lista negra activos? */
    public static function activo(?object $c = null): bool
    {
        $c ??= self::config();

        return $c->por_ip > 0 || $c->por_correo > 0 || $c->por_telefono > 0 || $c->por_nombre > 0
            || self::listaCorreos($c->lista_negra) !== [];
    }

    /** ["3 citas por conexión (IP) por día (desde las 00:00)", …] para los resúmenes. */
    public static function resumen(?object $c = null): array
    {
        $c ??= self::config();
        $que = ['ip' => 'conexión (IP)', 'correo' => 'correo', 'telefono' => 'teléfono', 'nombre' => 'nombre'];
        $reglas = [];
        foreach (self::CRITERIOS as $criterio => [$col, $colPeriodo]) {
            $n = (int) $c->{$col};
            if ($n > 0) {
                $donde = array_filter([
                    $c->{'sedes_'.$criterio} ? implode(', ', $c->{'sedes_'.$criterio}) : null,
                    $c->{'tramites_'.$criterio} ? implode(', ', $c->{'tramites_'.$criterio}) : null,
                    $c->{'canal_'.$criterio} !== 'linea' ? mb_strtolower(self::CANALES[$c->{'canal_'.$criterio}]) : null,
                ]);
                $reglas[] = $n.' '.($n === 1 ? 'cita' : 'citas').' por '.$que[$criterio].' '.self::PERIODOS[$c->{$colPeriodo}]
                    .($donde ? ' ('.implode(' · ', $donde).')' : '');
            }
        }

        return $reglas;
    }

    /** Guarda el tope (Administración → Módulos). */
    public static function guardar(array $datos, ?object $usuario): void
    {
        $valores = [
            'exentas'                => implode("\n", self::listaExentas((string) ($datos['exentas'] ?? ''))) ?: null,
            'lista_blanca'           => implode("\n", self::listaCorreos((string) ($datos['lista_blanca'] ?? ''))) ?: null,
            'lista_negra'            => implode("\n", self::listaCorreos((string) ($datos['lista_negra'] ?? ''))) ?: null,
            'actualizado_por'        => $usuario?->id,
            'actualizado_por_nombre' => $usuario ? mb_substr((string) $usuario->name, 0, 120) : null,
            'updated_at'             => now(),
        ];
        foreach (self::CRITERIOS as [$col, $colPeriodo]) {
            $valores[$col] = (int) ($datos[$col] ?? 0);
            $valores[$colPeriodo] = $datos[$colPeriodo] ?? 'dia';
        }
        if (self::porAlcance()) {
            foreach (array_keys(self::CRITERIOS) as $criterio) {
                $valores['sedes_'.$criterio] = implode("\n", self::listaSedes($datos['sedes_'.$criterio] ?? [])) ?: null;
                if (self::porTramite()) {
                    $valores['tramites_'.$criterio] = implode("\n", self::listaTramites($datos['tramites_'.$criterio] ?? [])) ?: null;
                }
                $canal = $datos['canal_'.$criterio] ?? 'linea';
                $valores['canal_'.$criterio] = in_array($criterio, self::SOLO_LINEA, true) || ! isset(self::CANALES[$canal]) ? 'linea' : $canal;
            }
        }
        // La columna vieja se queda con el de la conexión (por compatibilidad).
        $valores['periodo'] = $valores['periodo_ip'];

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

    /** Lo escrito en una lista de correos, renglón por renglón o separado por comas. */
    public static function partesLista(string $texto): array
    {
        return preg_split('/[\s,;]+/', mb_strtolower(trim($texto)), -1, PREG_SPLIT_NO_EMPTY);
    }

    /** Correos o dominios (@dominio.com) válidos de una lista; los inválidos se descartan. */
    public static function listaCorreos(string $texto): array
    {
        return array_values(array_unique(array_filter(self::partesLista($texto), [self::class, 'correoValidoLista'])));
    }

    public static function correoValidoLista(string $v): bool
    {
        if (str_starts_with($v, '@')) {
            return (bool) preg_match('/^@[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*\.[a-z]{2,}$/', $v);
        }

        return filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** ¿El correo está en la lista (completo o por su dominio)? */
    public static function correoEnLista(?string $correo, string $texto): bool
    {
        $correo = self::correo($correo);
        if (! $correo || trim($texto) === '') {
            return false;
        }
        $lista = self::listaCorreos($texto);
        $arroba = strrchr($correo, '@');

        return in_array($correo, $lista, true) || ($arroba !== false && in_array($arroba, $lista, true));
    }

    /** ¿Correo en la lista negra? (citas en línea y turnos de ventanilla) */
    public static function enListaNegra(?string $correo, ?object $c = null): bool
    {
        if (! self::porCriterio()) {
            return false;
        }
        $c ??= self::config();

        return self::correoEnLista($correo, $c->lista_negra);
    }

    /** ¿Correo en la lista blanca? (sin ningún tope) */
    public static function enListaBlanca(?string $correo, ?object $c = null): bool
    {
        if (! self::porCriterio()) {
            return false;
        }
        $c ??= self::config();

        return self::correoEnLista($correo, $c->lista_blanca);
    }

    /** Desde cuándo se cuentan las citas de un periodo móvil. */
    public static function desde(string $periodo): Carbon
    {
        return $periodo === 'dia' || ! isset(self::DIAS[$periodo])
            ? now()->startOfDay()
            : now()->subDays(self::DIAS[$periodo]);
    }

    public static function esDesdeRegistro(string $periodo): bool
    {
        return str_starts_with($periodo, 'reg');
    }

    /**
     * Cuántas citas lleva el dato en su periodo y, si ya llegó al máximo,
     * cuándo podrá agendar otra: ['llevaba', 'inicio' (?Carbon), 'libre' (?Carbon)].
     *
     * @param  Closure():\Illuminate\Database\Query\Builder  $consulta  citas en línea de ese dato
     */
    public static function cuenta(Closure $consulta, string $periodo, int $limite): array
    {
        $dias = self::DIAS[$periodo] ?? 1;

        if (! self::esDesdeRegistro($periodo)) {
            $desde = self::desde($periodo);
            $llevaba = $consulta()->where('created_at', '>=', $desde)->count();
            $libre = null;
            if ($limite > 0 && $llevaba >= $limite) {
                if ($periodo === 'dia') {
                    $libre = now()->addDay()->startOfDay();
                } else {
                    // Cuando la más vieja que sobra salga de la ventana.
                    $sale = $consulta()->where('created_at', '>=', $desde)->orderBy('created_at')
                        ->offset($llevaba - $limite)->limit(1)->value('created_at');
                    $libre = $sale ? Carbon::parse($sale)->addDays($dias) : null;
                }
            }

            return ['llevaba' => $llevaba, 'inicio' => $desde, 'libre' => $libre];
        }

        // A partir de que se registra: se arman los plazos con su historia.
        $fechas = $consulta()->where('created_at', '>=', now()->subDays(self::HISTORIA_DIAS))
            ->orderBy('created_at')->pluck('created_at');
        $inicio = null;
        $llevaba = 0;
        foreach ($fechas as $f) {
            $f = Carbon::parse($f);
            if ($inicio === null || $f->gte($inicio->copy()->addDays($dias))) {
                $inicio = $f;
                $llevaba = 0;
            }
            $llevaba++;
        }
        if ($inicio === null || now()->gte($inicio->copy()->addDays($dias))) {
            // Nunca se ha usado o su plazo ya terminó: la siguiente abre uno nuevo.
            return ['llevaba' => 0, 'inicio' => null, 'libre' => null];
        }

        return ['llevaba' => $llevaba, 'inicio' => $inicio, 'libre' => $inicio->copy()->addDays($dias)];
    }

    /**
     * ¿Esta cita pasa el tope? null si sí; si no, por qué:
     * ['motivo', 'limite', 'llevaba', 'periodo', 'mensaje'].
     *
     * $canal: 'linea' (/generar-cita) o 'ventanilla' (/turnos/nuevo). Cada
     * tope aplica sólo en su canal, sus sedes y sus trámites ($tipo: el de
     * la cita), y cuenta sólo las citas de ese canal (en las dos: en línea y
     * ventanilla), esas sedes y esos trámites.
     */
    public static function revisar(?string $ip, ?string $correo, ?string $telefono, ?string $nombre = null, ?string $sede = null, string $canal = 'linea', ?string $tipo = null): ?array
    {
        $c = self::config();
        $ventanilla = $canal === 'ventanilla';

        // Lista negra: no se registra, sin importar los topes.
        if (self::enListaNegra($correo, $c)) {
            return [
                'motivo'  => 'negra',
                'limite'  => 0,
                'llevaba' => 0,
                'periodo' => '',
                'mensaje' => $ventanilla
                    ? 'No se puede registrar el turno: el correo '.self::correo($correo).' está en la lista negra (Administración → Módulos → Límite de citas).'
                    : self::MENSAJE_NEGRA,
            ];
        }
        // Lista blanca y conexiones exentas: sin tope.
        if (self::enListaBlanca($correo, $c) || ! self::activo($c)) {
            return null;
        }
        if (! $ventanilla && $ip && self::migrado() && $c->exentas !== '' && IpUtils::checkIp($ip, self::listaExentas($c->exentas))) {
            return null;
        }

        // Citas que cuentan para un criterio: de su canal, sus sedes y sus trámites.
        $base = function (string $criterio) use ($c) {
            $origenes = ['linea' => ['linea'], 'ventanilla' => ['ventanilla'], 'ambos' => ['linea', 'ventanilla']][$c->{'canal_'.$criterio}];
            $q = DB::table('recepcion')
                ->whereIn('origen', $origenes)
                ->where(fn ($w) => $w->where('es_prueba', 0)->orWhereNull('es_prueba'));
            if ($sedes = $c->{'sedes_'.$criterio}) {
                // Con y sin acentos, como puede venir guardada.
                $q->whereIn('delegacion', array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes)))));
            }
            if ($tramites = $c->{'tramites_'.$criterio}) {
                $q->whereIn('tipo', array_values(array_unique(array_merge($tramites, array_map(fn ($t) => Str::ascii($t), $tramites)))));
            }

            return $q;
        };

        $correoN = self::correo($correo);
        $telN = self::telefono($telefono);
        $nombreN = self::nombre($nombre);
        $consultas = [
            // Sin la migración no hay columna ip; el nombre sí se revisa.
            'ip'       => $ip && self::migrado() ? fn () => $base('ip')->where('ip', $ip) : null,
            'correo'   => $correoN ? fn () => $base('correo')->whereRaw('LOWER(TRIM(correo)) = ?', [$correoN]) : null,
            'telefono' => $telN ? fn () => $base('telefono')->where('telefono', $telN) : null,
            // Los espacios dobles se juntan (el formulario en línea no los quita).
            'nombre'   => $nombreN ? fn () => $base('nombre')->whereRaw("UPPER(TRIM(REPLACE(REPLACE(REPLACE(solicitante, '  ', ' '), '  ', ' '), '  ', ' '))) = ?", [$nombreN]) : null,
        ];

        foreach (self::CRITERIOS as $motivo => [$col, $colPeriodo]) {
            $limite = (int) $c->{$col};
            $consulta = $consultas[$motivo];
            if ($limite <= 0 || $consulta === null) {
                continue;
            }
            // ¿Aplica en este canal, en esta sede y a este trámite?
            $tramites = $c->{'tramites_'.$motivo};
            if ($tramites && ! in_array(self::sedeClave($tipo), array_map([self::class, 'sedeClave'], $tramites), true)) {
                continue;
            }
            if (! in_array($c->{'canal_'.$motivo}, [$canal, 'ambos'], true)) {
                continue;
            }
            $sedes = $c->{'sedes_'.$motivo};
            if ($sedes && ! in_array(self::sedeClave($sede), array_map([self::class, 'sedeClave'], $sedes), true)) {
                continue;
            }
            $periodo = $c->{$colPeriodo};
            $cuenta = self::cuenta($consulta, $periodo, $limite);
            if ($cuenta['llevaba'] >= $limite) {
                return [
                    'motivo'  => $motivo,
                    'limite'  => $limite,
                    'llevaba' => $cuenta['llevaba'],
                    'periodo' => $periodo,
                    'mensaje' => self::mensaje($motivo, $limite, $periodo, $cuenta['inicio'], $cuenta['libre'], $c->{'canal_'.$motivo}, $ventanilla),
                ];
            }
        }

        return null;
    }

    public const MENSAJE_NEGRA = 'No se pudo registrar la cita con este correo electrónico. '
        .'Si necesitas una cita, acude directamente a la sede del Centro o comunícate por teléfono; el trámite es gratuito.';

    /**
     * Lo que se ve cuando el tope detiene la cita: a la persona en línea o
     * al auxiliar en ventanilla. $canalTope: de qué citas es el máximo.
     */
    public static function mensaje(string $motivo, int $limite, string $periodo, ?Carbon $inicio = null, ?Carbon $libre = null, string $canalTope = 'linea', bool $ventanilla = false): string
    {
        $desde = [
            'ip'       => 'desde esta conexión a internet',
            'correo'   => 'con este correo electrónico',
            'telefono' => 'con este número de teléfono',
            'nombre'   => 'a este nombre',
        ][$motivo] ?? '';
        $citas = $limite === 1 ? 'una cita' : $limite.' citas';
        $de = ['linea' => ' en línea', 'ventanilla' => ' en ventanilla', 'ambos' => ''][$canalTope] ?? '';
        $inicioTexto = $ventanilla ? 'No se puede registrar el turno:' : 'No se pudo registrar la cita:';

        if (self::esDesdeRegistro($periodo)) {
            $texto = "{$inicioTexto} ya se registró el máximo de {$citas}{$de} {$desde} en ".self::DIAS[$periodo].' días'
                .($inicio ? ', contados desde la primera ('.$inicio->format('d/m/Y').')' : '').'.';
        } else {
            $cuando = [
                'dia' => 'hoy', 'semana' => 'en los últimos 7 días', 'quincena' => 'en los últimos 15 días', 'mes' => 'en los últimos 30 días',
            ][$periodo] ?? 'hoy';
            $texto = "{$inicioTexto} {$cuando} ya se registró el máximo de {$citas}{$de} {$desde}.";
        }
        if ($libre) {
            $quien = $ventanilla ? 'Podrá agendar otra' : 'Podrás agendar otra';
            $texto .= $periodo === 'dia'
                ? " {$quien} a partir de mañana."
                : " {$quien} a partir del ".$libre->format('d/m/Y').' a las '.$libre->format('H:i').'.';
        }

        return $ventanilla
            ? $texto.' (Límite de Administración → Módulos.)'
            : $texto.' Si necesitas otra cita antes, acude directamente a la sede del Centro o comunícate por teléfono; el trámite es gratuito.';
    }

    /** Deja constancia del intento detenido (para /estadisticas/turnos). */
    public static function registrarBloqueo(array $bloqueo, ?string $ip, ?string $agente, array $datos): void
    {
        if (! self::migrado()) {
            return;
        }
        $nombre = $datos['nombre'] ?? ($datos['solicitante'] ?? null);
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
                'nombre'      => is_string($nombre) && trim($nombre) !== '' ? mb_substr(trim($nombre), 0, 150) : null,
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

    /**
     * Nombre completo en mayúsculas y con un solo espacio. Sólo cuenta si
     * trae nombre y al menos un apellido (dos palabras o más).
     */
    public static function nombre(?string $nombre): ?string
    {
        $n = mb_strtoupper(trim((string) preg_replace('/\s+/u', ' ', (string) $nombre)));

        return $n !== '' && count(explode(' ', $n)) >= 2 ? $n : null;
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
