<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * /estadisticas/turnos → "¿Quién agenda las citas en línea?": desde qué
 * conexiones (IP), correos y teléfonos se registran, para ver si una sola
 * oficina (por ejemplo, un despacho) acapara los turnos.
 *
 * Con los mismos filtros del panel (fechas de la cita, sede y trámite); sólo
 * citas en línea y sin las de prueba. La IP se guarda desde que se instaló
 * este cambio (LimiteCitas): las citas anteriores salen como "sin IP".
 *
 * Ojo al leerlo: varias personas pueden compartir una IP (la red de una
 * empresa, una escuela o un mismo proveedor de celular), así que la IP es una
 * pista que se confirma con el correo, el teléfono y cuántas personas
 * distintas (CURP) hay detrás.
 */
class AgendadoresCitas
{
    /** Desde cuántas citas una IP, correo o teléfono cuenta como "frecuente". */
    public const FRECUENTE = [2, 3, 5, 10];

    public const FRECUENTE_DEF = 3;

    /** Cuántos renglones por tabla (pantalla y PDF) y cuántas citas en el detalle de cada IP. */
    public const TOP = 30;

    public const TOP_PDF = 10;

    public const DETALLE = 60;

    /** Dominios de correo de uso general (los demás suelen ser de empresas o despachos). */
    private const DOMINIOS_PUBLICOS = [
        'gmail.com', 'hotmail.com', 'hotmail.es', 'outlook.com', 'outlook.es', 'live.com', 'live.com.mx',
        'yahoo.com', 'yahoo.com.mx', 'yahoo.es', 'icloud.com', 'me.com', 'msn.com', 'aol.com', 'prodigy.net.mx', 'protonmail.com', 'proton.me',
    ];

    public static function frecuente($valor): int
    {
        $v = (int) $valor;

        return in_array($v, self::FRECUENTE, true) ? $v : self::FRECUENTE_DEF;
    }

    /** @param array{desde: string, hasta: string, sede: string, tipo: string, origen: string, frecuente?: int} $f */
    public static function armar(array $f, bool $todo = false): array
    {
        // $todo: para el Excel, sin recortar las tablas y con todas las citas.
        $top = $todo ? PHP_INT_MAX : self::TOP;
        $min = self::frecuente($f['frecuente'] ?? self::FRECUENTE_DEF);
        $base = [
            'migrado'   => LimiteCitas::migrado(),
            'frecuente' => $min,
            'soloLinea' => $f['origen'] === 'ventanilla',
            'limite'    => LimiteCitas::config(),
        ];
        if (! $base['migrado']) {
            return $base + ['citas' => 0];
        }

        $sedes = $f['sede'] !== '' ? array_values(array_unique([$f['sede'], Str::ascii($f['sede'])])) : null;
        $filas = DB::table('recepcion')
            ->where('origen', 'linea')
            ->whereBetween('fecha', [$f['desde'], $f['hasta']])
            ->when($sedes, fn ($q) => $q->whereIn('delegacion', $sedes))
            ->when($f['tipo'] !== '', fn ($q) => $q->where('tipo', $f['tipo']))
            ->where(fn ($w) => $w->where('es_prueba', 0)->orWhereNull('es_prueba'))
            ->orderBy('created_at')
            ->get(['id', 'codigo', 'consecutivo', 'fecha', 'hora', 'delegacion', 'tipo', 'estatus', 'exepcion',
                'solicitante', 'curp', 'correo', 'telefono', 'ip', 'user_agent', 'created_at']);

        $grupos = ['ip' => [], 'correo' => [], 'telefono' => [], 'dominio' => []];
        $personas = [];
        $todas = [];
        $porHora = array_fill(0, 24, 0);
        $porDiaSemana = array_fill(1, 7, 0);
        $conIp = 0;

        foreach ($filas as $r) {
            $persona = self::persona($r);
            $personas[$persona] = true;
            $registro = $r->created_at ? Carbon::parse($r->created_at) : null;
            if ($registro) {
                $porHora[(int) $registro->format('G')]++;
                $porDiaSemana[(int) $registro->format('N')]++;
            }

            $correo = LimiteCitas::correo($r->correo);
            $tel = LimiteCitas::telefono($r->telefono);
            $ip = trim((string) $r->ip);
            if ($ip !== '') {
                $conIp++;
                self::sumar($grupos['ip'][$ip], $r, $persona, $correo, $tel, $ip, $registro);
            }
            if ($correo) {
                self::sumar($grupos['correo'][$correo], $r, $persona, $correo, $tel, $ip, $registro);
                $dominio = Str::after($correo, '@');
                if ($dominio !== $correo && ! in_array($dominio, self::DOMINIOS_PUBLICOS, true)) {
                    self::sumar($grupos['dominio'][$dominio], $r, $persona, $correo, $tel, $ip, $registro);
                }
            }
            if ($tel) {
                self::sumar($grupos['telefono'][$tel], $r, $persona, $correo, $tel, $ip, $registro);
            }
            if ($todo) {
                $todas[] = self::renglon($r, $correo, $tel, $ip, $registro);
            }
        }

        $total = $filas->count();
        $bloqueos = self::bloqueos($f, $todo ? 5000 : 50);

        $tabla = function (array $g, int $desde, array $bloq = []) use ($total) {
            $lista = [];
            foreach ($g as $clave => $a) {
                if ($a['citas'] < $desde) {
                    continue;
                }
                $lista[] = self::cerrar((string) $clave, $a, $total, $bloq[$clave] ?? 0);
            }
            usort($lista, fn ($x, $y) => [$y['citas'], $y['personas']] <=> [$x['citas'], $x['personas']]);

            return $lista;
        };

        $porIp = $tabla($grupos['ip'], 2, $bloqueos['porIp']);
        $frecuentes = array_values(array_filter($porIp, fn ($x) => $x['citas'] >= $min));
        $citasFrecuentes = array_sum(array_column($frecuentes, 'citas'));
        $top10 = array_sum(array_column(array_slice($porIp, 0, 10), 'citas'));
        $porCorreo = $tabla($grupos['correo'], 2, $bloqueos['porCorreo']);
        $porTelefono = $tabla($grupos['telefono'], 2, $bloqueos['porTelefono']);

        $pct = fn ($v, $de) => $de > 0 ? round($v * 100 / $de, 1) : null;

        return $base + [
            'citas'            => $total,
            'personas'         => count($personas),
            'conIp'            => $conIp,
            'sinIp'            => $total - $conIp,
            'ips'              => count($grupos['ip']),
            'ipsFrecuentes'    => count($frecuentes),
            'citasFrecuentes'  => $citasFrecuentes,
            'pctFrecuentes'    => $pct($citasFrecuentes, $conIp),
            'pctTop10'         => $pct($top10, $conIp),
            'topIp'            => $porIp[0] ?? null,
            'correosFrecuentes'   => count(array_filter($porCorreo, fn ($x) => $x['citas'] >= $min)),
            'telefonosFrecuentes' => count(array_filter($porTelefono, fn ($x) => $x['citas'] >= $min)),
            'porIp'            => array_slice($porIp, 0, $top),
            'masIps'           => max(0, count($porIp) - $top),
            'porCorreo'        => array_slice($porCorreo, 0, $top),
            'masCorreos'       => max(0, count($porCorreo) - $top),
            'porTelefono'      => array_slice($porTelefono, 0, $top),
            'masTelefonos'     => max(0, count($porTelefono) - $top),
            'todas'            => $todas,
            'porDominio'       => array_slice($tabla($grupos['dominio'], 1), 0, 20),
            'porHora'          => $porHora,
            'porDiaSemana'     => array_values($porDiaSemana),
            'bloqueos'         => $bloqueos,
        ];
    }

    /** CURP si la trae; si no, el nombre normalizado (para contar personas distintas). */
    private static function persona(object $r): string
    {
        $curp = strtoupper(trim((string) $r->curp));
        if (strlen($curp) >= 10) {
            return 'c:'.$curp;
        }

        return 'n:'.Str::lower(Str::ascii(preg_replace('/\s+/', ' ', trim((string) $r->solicitante))));
    }

    private static function sumar(?array &$a, object $r, string $persona, ?string $correo, ?string $tel, string $ip, ?Carbon $registro): void
    {
        $a ??= [
            'citas' => 0, 'personas' => [], 'correos' => [], 'telefonos' => [], 'ips' => [], 'sedes' => [], 'tramites' => [],
            'atendidas' => 0, 'noAcudieron' => 0, 'excepcion' => 0, 'primera' => null, 'ultima' => null,
            'porHora' => [], 'navegadores' => [], 'detalle' => [],
        ];
        $a['citas']++;
        $a['personas'][$persona] = true;
        if ($correo) {
            $a['correos'][$correo] = true;
        }
        if ($tel) {
            $a['telefonos'][$tel] = true;
        }
        if ($ip !== '') {
            $a['ips'][$ip] = true;
        }
        $sede = trim((string) $r->delegacion) ?: 'Sin sede';
        $a['sedes'][$sede] = ($a['sedes'][$sede] ?? 0) + 1;
        $tramite = $r->tipo ?: 'Sin trámite';
        $a['tramites'][$tramite] = ($a['tramites'][$tramite] ?? 0) + 1;
        $estatus = strtolower((string) $r->estatus);
        $a['atendidas'] += $estatus === 'atendido' ? 1 : 0;
        $a['noAcudieron'] += $estatus === 'expirada' ? 1 : 0;
        $a['excepcion'] += $r->exepcion === 'Si' ? 1 : 0;
        if ($registro) {
            $a['primera'] = $a['primera'] && $a['primera']->lt($registro) ? $a['primera'] : $registro;
            $a['ultima'] = $a['ultima'] && $a['ultima']->gt($registro) ? $a['ultima'] : $registro;
            $hora = $registro->format('Y-m-d H');
            $a['porHora'][$hora] = ($a['porHora'][$hora] ?? 0) + 1;
        }
        $nav = LimiteCitas::navegador($r->user_agent);
        $a['navegadores'][$nav] = ($a['navegadores'][$nav] ?? 0) + 1;
        if (count($a['detalle']) < self::DETALLE) {
            $a['detalle'][] = self::renglon($r, $correo, $tel, $ip, $registro);
        }
    }

    /** Una cita para las listas de detalle. */
    private static function renglon(object $r, ?string $correo, ?string $tel, string $ip, ?Carbon $registro): array
    {
        return [
            'folio'     => FolioCita::mostrar($r),
            'cita'      => Carbon::parse($r->fecha)->format('d/m/Y').' '.substr((string) $r->hora, 0, 5),
            'sede'      => trim((string) $r->delegacion) ?: 'Sin sede',
            'tramite'   => $r->tipo ?: 'Sin trámite',
            'nombre'    => Str::title(mb_strtolower(trim((string) $r->solicitante))) ?: '—',
            'curp'      => strtoupper(trim((string) $r->curp)) ?: '—',
            'correo'    => $correo ?? '—',
            'telefono'  => $tel ?? '—',
            'ip'        => $ip !== '' ? $ip : '—',
            'registro'  => $registro ? $registro->format('d/m/Y H:i') : '—',
            'estatus'   => $r->estatus ? Str::ucfirst((string) $r->estatus) : 'Pendiente',
            'navegador' => LimiteCitas::navegador($r->user_agent),
            'excepcion' => $r->exepcion === 'Si',
        ];
    }

    private static function cerrar(string $clave, array $a, int $total, int $bloqueos): array
    {
        arsort($a['sedes']);
        arsort($a['tramites']);
        arsort($a['navegadores']);
        $pasadas = $a['atendidas'] + $a['noAcudieron'];

        return [
            'clave'         => $clave,
            'citas'         => $a['citas'],
            'pct'           => $total > 0 ? round($a['citas'] * 100 / $total, 1) : null,
            'personas'      => count($a['personas']),
            'correos'       => count($a['correos']),
            'telefonos'     => count($a['telefonos']),
            'ips'           => count($a['ips']),
            'sedes'         => $a['sedes'],
            'sedesTexto'    => implode(', ', array_map(fn ($s, $n) => $s.' ('.$n.')', array_keys($a['sedes']), $a['sedes'])),
            'tramitesTexto' => implode(', ', array_map(fn ($s, $n) => $s.' ('.$n.')', array_keys($a['tramites']), $a['tramites'])),
            'atendidas'     => $a['atendidas'],
            'noAcudieron'   => $a['noAcudieron'],
            'pctNoAcudieron'=> $pasadas > 0 ? round($a['noAcudieron'] * 100 / $pasadas, 1) : null,
            'excepcion'     => $a['excepcion'],
            'primera'       => $a['primera']?->format('d/m/Y H:i'),
            'ultima'        => $a['ultima']?->format('d/m/Y H:i'),
            'maxHora'       => $a['porHora'] ? max($a['porHora']) : 0,
            'navegador'     => array_key_first($a['navegadores']) ?? '—',
            'navegadores'   => count($a['navegadores']),
            'bloqueos'      => $bloqueos,
            'detalle'       => $a['detalle'],
        ];
    }

    /** Intentos que el tope detuvo, para citas en el periodo (por la fecha que pedían). */
    private static function bloqueos(array $f, int $cuantos): array
    {
        $sedes = $f['sede'] !== '' ? array_values(array_unique([$f['sede'], Str::ascii($f['sede'])])) : null;
        $q = DB::table('citas_bloqueadas')
            ->whereBetween('fecha_turno', [$f['desde'], $f['hasta']])
            ->when($sedes, fn ($w) => $w->whereIn('delegacion', $sedes))
            ->when($f['tipo'] !== '', fn ($w) => $w->where('tipo', $f['tipo']));

        $porMotivo = (clone $q)->select('motivo', DB::raw('COUNT(*) as n'))->groupBy('motivo')->pluck('n', 'motivo')->all();
        $contar = fn (string $col) => (clone $q)->whereNotNull($col)->select($col, DB::raw('COUNT(*) as n'))->groupBy($col)->pluck('n', $col)->all();
        $lista = (clone $q)->orderByDesc('created_at')->limit($cuantos)->get()->map(fn ($b) => [
            'cuando'    => $b->created_at ? Carbon::parse($b->created_at)->format('d/m/Y H:i') : '—',
            'motivo'    => LimiteCitas::MOTIVOS[$b->motivo] ?? $b->motivo,
            'regla'     => 'Tenía '.$b->llevaba.' · máximo '.$b->limite.' '.(LimiteCitas::PERIODOS[$b->periodo] ?? ''),
            'ip'        => $b->ip ?: '—',
            'correo'    => $b->correo ?: '—',
            'telefono'  => $b->telefono ?: '—',
            'nombre'    => $b->nombre ? Str::title(mb_strtolower($b->nombre)) : '—',
            'curp'      => $b->curp ?: '—',
            'sede'      => $b->delegacion ?: '—',
            'tramite'   => $b->tipo ?: '—',
            'pedia'     => $b->fecha_turno ? Carbon::parse($b->fecha_turno)->format('d/m/Y').($b->hora_turno ? ' '.$b->hora_turno : '') : '—',
            'navegador' => LimiteCitas::navegador($b->user_agent),
        ])->all();

        return [
            'total'     => array_sum($porMotivo),
            'porMotivo' => $porMotivo,
            'porIp'       => $contar('ip'),
            'porCorreo'   => $contar('correo'),
            'porTelefono' => $contar('telefono'),
            'lista'       => $lista,
        ];
    }
}
