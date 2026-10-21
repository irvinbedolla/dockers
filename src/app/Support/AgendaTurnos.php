<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Agenda de turnos para descargar (Excel, PDF o imagen) desde Turnos →
 * Todos los turnos: un día o una semana, una sede o todas, con hora, módulo,
 * quién llega, quién lo atiende, a qué hora llegó y en qué quedó.
 *
 * Sólo Super Usuario y Directivo (lo cuida la ruta). Las citas de prueba
 * no salen salvo que se pidan.
 */
class AgendaTurnos
{
    /** Descargan la agenda de todas las sedes. */
    public const ROLES = ['Super Usuario', 'Directivo'];

    /**
     * Super Usuario y Directivo siempre; el Delegado sólo si tiene el permiso
     * turnos_ver (se le da en Roles o en su usuario), y nada más de su región.
     */
    public static function puedeDescargar(?User $usuario): bool
    {
        return $usuario !== null && (
            $usuario->hasAnyRole(self::ROLES)
            || ($usuario->hasRole('Delegado') && $usuario->can('turnos_ver'))
        );
    }

    public const PERIODOS = [
        'dia'    => 'Un día',
        'semana' => 'La semana (lunes a domingo)',
    ];

    public const EXCEPCION = [
        'incluir' => 'Incluir casos de excepción',
        'excluir' => 'Sin casos de excepción',
        'solo'    => 'Sólo casos de excepción',
    ];

    public const FORMATOS = ['xlsx', 'pdf', 'png'];

    public const ESTATUS = [
        'pendiente'  => 'Pendiente',
        'confirmada' => 'Llegó',
        'atendido'   => 'Atendido',
        'expirada'   => 'No llegó',
    ];

    /** Color de fondo y de letra de cada estatus (Excel, PDF e imagen). */
    public const COLORES = [
        'pendiente'  => ['#FBF4E1', '#7A5D10'],
        'confirmada' => ['#E3EEF9', '#1F4E79'],
        'atendido'   => ['#E5F3EA', '#1E7B3C'],
        'expirada'   => ['#F1F1F1', '#6B6B6B'],
    ];

    public const COLOR_EXCEPCION = '#F6E4EA';

    /**
     * Todo lo que necesitan las tres salidas.
     *
     * @param array{fecha: string, periodo: string, sede: ?string, excepcion: string, pruebas?: bool} $f
     */
    public static function armar(array $f, User $usuario): array
    {
        $dia = Carbon::parse($f['fecha']);
        [$desde, $hasta] = $f['periodo'] === 'semana'
            ? [$dia->copy()->startOfWeek(Carbon::MONDAY), $dia->copy()->endOfWeek(Carbon::SUNDAY)]
            : [$dia->copy(), $dia->copy()];

        $visibles = Recepcion::sedesVisibles($usuario);
        $sedes    = ! empty($f['sede']) ? [$f['sede']] : $visibles;

        $filas = self::filas($desde->toDateString(), $hasta->toDateString(), $sedes, $f['excepcion'], ! empty($f['pruebas']));

        // Agrupado por sede (en el orden oficial) y dentro por día.
        $porSede = [];
        foreach ($sedes as $sede) {
            $deLaSede = $filas->where('sede_clave', Str::lower(Str::ascii($sede)));
            if ($deLaSede->isNotEmpty() || count($sedes) === 1) {
                $porSede[$sede] = $deLaSede->groupBy('fecha')->all();
            }
        }

        $periodo = $f['periodo'] === 'semana'
            ? 'Semana del '.self::fechaLarga($desde, false).' al '.self::fechaLarga($hasta)
            : Str::ucfirst($desde->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));

        return [
            'titulo'     => 'Agenda de turnos',
            'periodo'    => $periodo,
            'sedesTexto' => ! empty($f['sede']) ? $f['sede'] : (count($visibles) >= count(Recepcion::TODAS_LAS_SEDES) ? 'Todas las sedes' : implode(' y ', $visibles)),
            'excepcion'  => self::EXCEPCION[$f['excepcion']],
            'pruebas'    => ! empty($f['pruebas']),
            'desde'      => $desde,
            'hasta'      => $hasta,
            'filas'      => $filas,
            'porSede'    => $porSede,
            'resumen'    => self::resumen($filas, array_keys($porSede)),
            'porDia'     => self::porDia($filas, $desde, $hasta),
            'generado'   => now(),
            'por'        => $usuario->name,
            'archivo'    => 'agenda_turnos_'.$desde->format('Y-m-d').($f['periodo'] === 'semana' ? '_al_'.$hasta->format('Y-m-d') : '')
                            .(! empty($f['sede']) ? '_'.Str::slug($f['sede'], '_') : ''),
        ];
    }

    /** Un renglón por turno, ya con los textos listos para imprimir. */
    public static function filas(string $desde, string $hasta, array $sedes, string $excepcion, bool $pruebas = false): Collection
    {
        if ($sedes === []) {
            return collect();
        }

        $nombres = array_values(array_unique(array_merge($sedes, array_map(fn ($s) => Str::ascii($s), $sedes))));

        $columnas = [
            'r.id', 'r.consecutivo', 'r.codigo', 'r.fecha', 'r.hora', 'r.hora_fin', 'r.tipo', 'r.estatus',
            'r.exepcion', 'r.tipo_caso', 'r.motivo_excepcion', 'r.solicitante', 'r.delegacion', 'r.origen',
            'r.lugar_auxiliar', 'm.nombre as modulo', 'u.name as asignado',
        ];
        if (FlujoCita::migrado()) {
            array_push($columnas, 'r.llamado_at', 'r.atencion_inicio_at');
        }
        if (FlujoCita::conFinAtencion()) {
            $columnas[] = 'r.atencion_fin_at';
        }
        if (FlujoCita::conSeguimiento()) {
            $columnas[] = 'r.confirmada_at';
        }
        if (FlujoCita::conResponsables()) {
            array_push($columnas, 'r.creado_por_nombre', 'r.atendido_por_nombre');
        }
        if (FlujoCita::conTardias()) {
            $columnas[] = 'r.llego_tarde';
        }

        return DB::table('recepcion as r')
            ->leftJoin('modulos as m', 'm.id', '=', 'r.modulo_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.auxiliar')
            ->whereBetween('r.fecha', [$desde, $hasta])
            ->whereIn('r.delegacion', $nombres)
            ->when($excepcion === 'excluir', fn ($q) => $q->where(fn ($w) => $w->where('r.exepcion', '!=', 'Si')->orWhereNull('r.exepcion')))
            ->when($excepcion === 'solo', fn ($q) => $q->where('r.exepcion', 'Si'))
            ->when(FlujoCita::migrado() && ! $pruebas, fn ($q) => $q->where(fn ($w) => $w->where('r.es_prueba', 0)->orWhereNull('r.es_prueba')))
            ->orderBy('r.fecha')
            ->orderBy('r.hora')
            ->orderByRaw('COALESCE(m.orden, 999)')
            ->orderBy('r.id')
            ->get($columnas)
            ->map(fn ($r) => self::renglon($r));
    }

    /** Totales por sede (y una fila "Total" si hay más de una). */
    public static function resumen(Collection $filas, array $sedes): array
    {
        $contar = function (Collection $c, string $nombre) {
            return [
                'sede'       => $nombre,
                'total'      => $c->count(),
                'pendiente'  => $c->where('estatus_clave', 'pendiente')->count(),
                'confirmada' => $c->where('estatus_clave', 'confirmada')->count(),
                'atendido'   => $c->where('estatus_clave', 'atendido')->count(),
                'expirada'   => $c->where('estatus_clave', 'expirada')->count(),
                'excepcion'  => $c->where('excepcion', true)->count(),
                'linea'      => $c->where('origen', 'En línea')->count(),
                'ventanilla' => $c->where('origen', 'Ventanilla')->count(),
            ];
        };

        $renglones = [];
        foreach ($sedes as $sede) {
            $renglones[] = $contar($filas->where('sede_clave', Str::lower(Str::ascii($sede))), $sede);
        }
        if (count($sedes) > 1) {
            $renglones[] = $contar($filas, 'Total');
        }

        return $renglones;
    }

    /** Cuántos turnos hay cada día del periodo (para el resumen de la semana). */
    public static function porDia(Collection $filas, Carbon $desde, Carbon $hasta): array
    {
        $dias = [];
        for ($d = $desde->copy(); $d->lte($hasta); $d->addDay()) {
            $delDia = $filas->where('fecha', $d->toDateString());
            if ($delDia->isEmpty() && $d->isWeekend()) {
                continue;
            }
            $dias[] = [
                'dia'       => self::dia($d),
                'total'     => $delDia->count(),
                'atendido'  => $delDia->where('estatus_clave', 'atendido')->count(),
                'expirada'  => $delDia->where('estatus_clave', 'expirada')->count(),
                'excepcion' => $delDia->where('excepcion', true)->count(),
            ];
        }

        return $dias;
    }

    /** Estatus para la agenda; distingue las que se atendieron aunque llegaron tarde. */
    private static function etiqueta(string $estatus, bool $tarde): string
    {
        $texto = self::ESTATUS[$estatus] ?? Str::ucfirst($estatus);
        if (! $tarde || $estatus === 'expirada') {
            return $texto;
        }

        return $estatus === 'confirmada' ? 'Llegó tarde' : $texto.' (llegó tarde)';
    }

    public static function dia(Carbon|string $fecha): string
    {
        $c = $fecha instanceof Carbon ? $fecha : Carbon::parse($fecha);

        return Str::ucfirst($c->locale('es')->isoFormat('dddd D [de] MMMM'));
    }

    private static function renglon(object $r): array
    {
        $hora    = fn ($v) => $v ? Carbon::parse($v)->format('H:i') : '';
        $estatus = strtolower((string) $r->estatus);
        $inicio  = $r->atencion_inicio_at ?? null;
        $fin     = $r->atencion_fin_at ?? null;
        $asignado = $r->asignado ? Str::title(mb_strtolower($r->asignado)) : '';
        $atendio  = $r->atendido_por_nombre ?? null;
        if (! $atendio && $estatus === 'atendido') {
            $atendio = $asignado;
        }
        $esExcepcion = $r->exepcion === 'Si';

        return [
            'id'            => $r->id,
            'fecha'         => Carbon::parse($r->fecha)->toDateString(),
            'dia'           => self::dia($r->fecha),
            'hora'          => substr((string) $r->hora, 0, 5),
            'hora_fin'      => $r->hora_fin ? substr((string) $r->hora_fin, 0, 5) : '',
            'sede'          => $r->delegacion,
            'sede_clave'    => Str::lower(Str::ascii((string) $r->delegacion)),
            'modulo'        => $r->modulo ?: ($r->lugar_auxiliar ?: 'Sin módulo'),
            'folio'         => FolioCita::mostrar($r),
            'tipo'          => $r->tipo,
            'persona'       => trim((string) $r->solicitante),
            'origen'        => $r->origen === 'linea' ? 'En línea' : 'Ventanilla',
            'asignado'      => $asignado ?: 'Sin asignar',
            'llego'         => $hora($r->confirmada_at ?? null),
            'llamado'       => $hora($r->llamado_at ?? null),
            'inicio'        => $hora($inicio),
            'fin'           => $hora($fin),
            'minutos'       => $inicio && $fin ? max(0, (int) round(Carbon::parse($inicio)->diffInSeconds(Carbon::parse($fin)) / 60)) : null,
            'atendio'       => $atendio ? Str::title(mb_strtolower($atendio)) : '',
            'creo'          => ($r->creado_por_nombre ?? null) ?: ($r->origen === 'linea' ? 'La persona (en línea)' : ''),
            'estatus_clave' => $estatus,
            // Se atendió aunque llegó tarde: no es lo mismo que "no llegó".
            'estatus'       => self::etiqueta($estatus, ! empty($r->llego_tarde)),
            'excepcion'     => $esExcepcion,
            'motivo'        => $esExcepcion ? (SeguimientoCaso::motivoExcepcion($r) ?? 'Sin motivo capturado') : '',
        ];
    }

    private static function fechaLarga(Carbon $c, bool $conAnio = true): string
    {
        return $c->locale('es')->isoFormat($conAnio ? 'D [de] MMMM [de] YYYY' : 'D [de] MMMM');
    }
}
