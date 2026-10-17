<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Casos de excepción del artículo 685 Ter de la LFT (discriminación, acoso u
 * hostigamiento sexual, riesgos de trabajo, violencia laboral...).
 *
 * El Departamento de Conciliación, Género e Igualdad (rol ROL) ve todos los
 * casos de todas las sedes:
 *   - citas y turnos marcados como excepción (recepcion.exepcion = 'Si');
 *   - solicitudes marcadas como excepción (seer_general.caso_excepcion = 'Si'),
 *     sólo en modo vista.
 *
 * Sólo ese rol (y Super Usuario) atiende: llena el formato de atención y, si
 * la persona no sigue con la conciliación, la canaliza.
 *
 * Ojo: el nombre del rol lleva coma, así que no puede ir en un middleware
 * 'role:...' (Laravel parte los parámetros por coma). Para eso está el
 * middleware App\Http\Middleware\CasosExcepcionAcceso.
 */
class CasosExcepcion
{
    public const ROL = 'Conciliación, Genero e Igualdad';

    /** Atienden: llenan el formato y canalizan. */
    public const ATIENDEN = ['Super Usuario', self::ROL];

    /** Consultan la bandeja (sin atender). */
    public const CONSULTAN = ['Directivo', 'Delegado', 'Excepcion'];

    public const SEDES = ['Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo'];

    /** Turnos que puede crear en ventanilla (Recepcion::tramitesQuePuedeCrear). */
    public const TRAMITES_TURNO = ['Solicitud', 'Asesoría'];

    /** 1. Tipo de situación (pueden marcarse varias). Clave => texto corto. */
    public const TIPOS = [
        'discriminacion'    => 'Discriminación',
        'acoso_sexual'      => 'Acoso u hostigamiento sexual',
        'riesgo_trabajo'    => 'Riesgo o accidente de trabajo',
        'violencia_laboral' => 'Malos tratos o violencia laboral',
    ];

    /** Como lo dice el formato impreso. */
    public const TIPOS_FORMATO = [
        'discriminacion'    => 'He sido objeto de discriminación.',
        'acoso_sexual'      => 'He recibido acoso sexual por parte de un superior jerárquico o de un compañero de trabajo.',
        'riesgo_trabajo'    => 'He sufrido un riesgo o accidente de trabajo.',
        'violencia_laboral' => 'He sido objeto de malos tratos o violencia laboral.',
    ];

    /** 2. Frecuencia (los valores son los que ya guardaba el formulario anterior). */
    public const FRECUENCIAS = [
        'Una vez'      => 'Una vez.',
        'Varias veces' => 'Varias veces.',
        'Continua'     => 'De manera continua, hasta la fecha actual.',
    ];

    /** 3. Cambios en su situación laboral (mismos valores que antes). */
    public const SITUACIONES = [
        'Sigue igual'                => 'Sigue igual',
        'Tension estres incomodidad' => 'Tensión, estrés e incomodidad en el área de trabajo',
        'Cambio area'                => 'Le cambiaron de área',
        'otro'                       => 'Otro',
    ];

    /** A dónde se canaliza si la persona no sigue con la conciliación. */
    public const INSTITUCIONES = [
        'procuraduria' => 'Procuraduría de la Defensa del Trabajo',
        'ceav'         => 'Comisión de Atención a Víctimas',
        'coepredv'     => 'Consejo para Prevenir y Erradicar la Discriminación y la Violencia',
        'seimujer'     => 'SEIMUJER',
        'cedh'         => 'Comisión Estatal de los Derechos Humanos',
    ];

    public const RESULTADOS = [
        'continua' => 'Continúa con la conciliación',
        'canaliza' => 'Canalizada',
    ];

    public const GRUPOS_VULNERABLES = [
        'Menores de edad', 'Adultos mayores', 'Discapacidad', 'Población indígena', 'Personas Migrantes', 'LGBTTTIQ', 'No aplica',
    ];

    public static function es(?User $usuario): bool
    {
        return $usuario !== null && $usuario->hasRole(self::ROL);
    }

    /** Sólo tiene este rol: su agenda se reduce a las citas en línea de excepción. */
    public static function soloRol(?User $usuario): bool
    {
        return self::es($usuario) && $usuario->getRoleNames()->diff([self::ROL])->isEmpty();
    }

    public static function atiende(?User $usuario): bool
    {
        return $usuario !== null && $usuario->hasAnyRole(self::ATIENDEN);
    }

    public static function consulta(?User $usuario): bool
    {
        return $usuario !== null && $usuario->hasAnyRole(array_merge(self::ATIENDEN, self::CONSULTAN));
    }

    /**
     * En la agenda, las citas en línea que ve este usuario se limitan a las de
     * excepción (de todas las sedes). Si además es supervisor o recepción,
     * manda esa otra regla.
     */
    public static function agendaSoloExcepcion(?User $usuario): bool
    {
        return self::es($usuario)
            && ! $usuario->hasAnyRole(Recepcion::SUPERVISAN)
            && ! Recepcion::es($usuario);
    }

    /** Citas y turnos de excepción ("r"), sin las de prueba del flujo nuevo. */
    public static function citas(): Builder
    {
        return DB::table('recepcion as r')
            ->where('r.exepcion', 'Si')
            ->when(FlujoCita::migrado(), fn ($q) => $q->where('r.es_prueba', 0));
    }

    /**
     * Por atender: citas de excepción de los últimos 30 días (hasta hoy) que
     * todavía no tienen formato de atención, salvo las que no llegaron.
     */
    public static function porAtender(): Builder
    {
        return self::citas()
            ->whereBetween('r.fecha', [now()->subDays(30)->toDateString(), now()->toDateString()])
            ->where('r.estatus', '!=', 'expirada')
            ->whereNotExists(fn ($s) => $s->select(DB::raw(1))->from('seer_casos_excepcion as x')->whereColumn('x.id_turno', 'r.id')
                ->when(self::conFormato(), fn ($q) => $q->whereNotNull('x.terminado_at')));
    }

    /** Solicitudes de excepción ("g"). */
    public static function solicitudes(): Builder
    {
        return DB::table('seer_general as g')->where('g.caso_excepcion', 'Si');
    }

    /** ¿La solicitud es de excepción (marcada o nacida de una cita de excepción)? */
    public static function solicitudEsCaso(object $general): bool
    {
        if (($general->caso_excepcion ?? null) === 'Si') {
            return true;
        }

        return ! empty($general->recepcion_id)
            && DB::table('recepcion')->where('id', $general->recepcion_id)->where('exepcion', 'Si')->exists();
    }

    /** Ya existe la tabla del formato con las columnas nuevas (fase 2). */
    public static function conFormato(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasColumn('seer_casos_excepcion', 'resultado');
    }

    /**
     * Tarjetas del Inicio del rol: casos de hoy, por atender, citas en línea
     * y solicitudes del mes.
     */
    public static function inicio(): array
    {
        $hoy = now()->toDateString();
        $deHoy = self::citas()->where('r.fecha', $hoy);

        $porSede = (clone $deHoy)->selectRaw('r.delegacion, COUNT(*) AS total')->groupBy('r.delegacion')->pluck('total', 'delegacion');

        $porAtender = self::porAtender();

        $enLinea = (clone $deHoy)->where('r.origen', 'linea');

        $mes = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];

        return [
            'hoy' => [
                'total'    => (int) $porSede->sum(),
                'porSede'  => collect(self::SEDES)->map(fn ($s) => ['sede' => $s, 'total' => (int) ($porSede[$s] ?? 0)])
                    ->filter(fn ($s) => $s['total'] > 0)->values()->all(),
                'url'      => route('excepcion'),
            ],
            'porAtender' => [
                'total'     => (clone $porAtender)->count(),
                'porLlegar' => (clone $porAtender)->where('r.estatus', 'pendiente')->count(),
                'enSala'    => (clone $porAtender)->where('r.estatus', 'confirmada')->count(),
                'atendidas' => (clone $porAtender)->where('r.estatus', 'atendido')->count(),
                'url'       => route('excepcion', ['vista' => 'pendientes']),
            ],
            'enLinea' => [
                'total'    => (clone $enLinea)->count(),
                'proximas' => self::citas()->where('r.origen', 'linea')
                    ->whereBetween('r.fecha', [now()->addDay()->toDateString(), now()->addDays(7)->toDateString()])->count(),
                'url'      => route('agenda'),
            ],
            'solicitudes' => [
                'total' => self::solicitudes()->whereBetween('g.fecha', $mes)->count(),
                'url'   => route('excepcion', ['vista' => 'solicitudes']),
            ],
        ];
    }
}
