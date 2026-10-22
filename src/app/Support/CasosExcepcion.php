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
 * Enlace de Género e Igualdad: distintivo que se da persona por persona
 * desde Usuarios (permiso directo PERMISO_ENLACE, como "Supervisa
 * notificadores" de SíNotifico), para que alguien de otra delegación apoye
 * al departamento. Le da la pestaña y lo mismo que al rol en ella: atender,
 * canalizar y cerrar casos. No ve las estadísticas de excepción (sólo el
 * rol, Super Usuario y Directivo) ni cambia el resto de lo que ve por sus
 * roles (agenda, seguimiento, Inicio).
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

    /** Permiso directo del distintivo "Enlace de Género e Igualdad". */
    public const PERMISO_ENLACE = 'excepcion_enlace';

    public const DISTINTIVO = 'Enlace de Género e Igualdad';

    /**
     * Motivos de "¿Tu caso es por alguno de estos motivos?" (formulario de
     * cita en línea y de ventanilla). Clave => [texto del formulario, grupo,
     * valor de recepcion.tipo_caso, nombre en el reporte].
     *
     * recepcion.tipo_caso sólo guarda el grupo (p. ej. "Discriminación" para
     * embarazo, raza, edad...), por eso el motivo exacto va aparte en
     * recepcion.motivo_excepcion. Es lo que permite el reporte por motivo.
     */
    public const MOTIVOS = [
        'acoso_sexual'          => ['Acoso u hostigamiento sexual', 'Discriminación en el empleo por:', 'Acoso u hostigamiento sexual', 'Acoso / hostigamiento sexual'],
        'condicion_social'      => ['Condición social', 'Discriminación en el empleo por:', 'Discriminación', 'Discriminación por condición social'],
        'embarazo'              => ['Embarazo o maternidad', 'Discriminación en el empleo por:', 'Discriminación', 'Discriminación por maternidad'],
        'raza'                  => ['Raza', 'Discriminación en el empleo por:', 'Discriminación', 'Discriminación por raza'],
        'razones_sexo'          => ['Razones de sexo', 'Discriminación en el empleo por:', 'Discriminación', 'Discriminación por razones de sexo'],
        'religion'              => ['Religión', 'Discriminación en el empleo por:', 'Discriminación', 'Discriminación por religión'],
        'orientacion_sexual'    => ['Orientación sexual', 'Discriminación en el empleo por:', 'Discriminación', 'Discriminación por orientación sexual'],
        'origen_etnico'         => ['Origen étnico', 'Discriminación en el empleo por:', 'Discriminación', 'Discriminación por origen étnico'],
        'acoso_laboral'         => ['Acoso / hostigamiento laboral (violencia laboral)', 'Discriminación en el empleo por:', 'Acoso u hostigamiento laboral', 'Acoso / hostigamiento laboral'],
        'edad'                  => ['Edad', 'Discriminación en el empleo por:', 'Discriminación', 'Discriminación por edad'],
        'designacion'           => ['Designación de beneficiarios por muerte', 'Designación de beneficiarios:', 'Designacion', 'Designación de beneficiarios'],
        'accidentes'            => ['Accidentes de trabajo', 'Prestaciones de seguridad social por:', 'Prestaciones', 'Accidentes de trabajo'],
        'enfermedades'          => ['Enfermedades', 'Prestaciones de seguridad social por:', 'Prestaciones', 'Enfermedades'],
        'guarderias'            => ['Guarderías', 'Prestaciones de seguridad social por:', 'Prestaciones', 'Guarderías'],
        'invalidez'             => ['Invalidez', 'Prestaciones de seguridad social por:', 'Prestaciones', 'Invalidez'],
        'maternidad_ss'         => ['Maternidad', 'Prestaciones de seguridad social por:', 'Prestaciones', 'Prestación de maternidad (seguridad social)'],
        'riesgos'               => ['Riesgos de trabajo', 'Prestaciones de seguridad social por:', 'Prestaciones', 'Riesgos de trabajo'],
        'especie'               => ['Prestaciones en especie', 'Prestaciones de seguridad social por:', 'Prestaciones', 'Prestaciones en especie'],
        'vida'                  => ['Vida', 'Prestaciones de seguridad social por:', 'Prestaciones', 'Seguro de vida'],
        'libertad_asociacion'   => ['Libertad de asociación', 'Derechos fundamentales y libertades laborales:', 'Libertades_publicas', 'Libertad de asociación'],
        'libertad_sindical'     => ['Libertad sindical', 'Derechos fundamentales y libertades laborales:', 'Libertades_publicas', 'Libertad sindical'],
        'negociacion_colectiva' => ['Reconocimiento efectivo de la negociación colectiva', 'Derechos fundamentales y libertades laborales:', 'Libertades_publicas', 'Negociación colectiva'],
        'trabajo_infantil'      => ['Trabajo infantil', 'Derechos fundamentales y libertades laborales:', 'Libertades_publicas', 'Trabajo infantil'],
        'trabajo_forzoso'       => ['Trabajo forzoso u obligatorio', 'Derechos fundamentales y libertades laborales:', 'Libertades_publicas', 'Trabajo forzoso u obligatorio'],
        'contratos_colectivos'  => ['Contratos colectivos', 'Disputa de titularidad o estatutos sindicales:', 'Titularidad', 'Titularidad de contratos colectivos'],
        'contratos_ley'         => ['Contratos ley', 'Disputa de titularidad o estatutos sindicales:', 'Titularidad', 'Titularidad de contratos ley'],
        'estatutos'             => ['Impugnación de los estatutos de los sindicatos o su modificación', 'Disputa de titularidad o estatutos sindicales:', 'Titularidad', 'Impugnación de estatutos sindicales'],
    ];

    /** Motivos de discriminación para "Señale el motivo" del formato 685 Ter. */
    public const MOTIVOS_DISCRIMINACION = ['condicion_social', 'embarazo', 'raza', 'razones_sexo', 'religion', 'orientacion_sexual', 'origen_etnico', 'edad'];

    /** Ya corrió la migración de recepcion.motivo_excepcion. */
    public static function conMotivo(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasColumn('recepcion', 'motivo_excepcion');
    }

    /** La clave si es un motivo del catálogo; si no, null. */
    public static function motivoValido(?string $clave): ?string
    {
        return $clave !== null && isset(self::MOTIVOS[$clave]) ? $clave : null;
    }

    /** Opciones agrupadas para el select del formulario de cita. */
    public static function motivosAgrupados(): array
    {
        $grupos = [];
        foreach (self::MOTIVOS as $clave => [$texto, $grupo, $valor]) {
            $grupos[$grupo][] = ['clave' => $clave, 'texto' => $texto, 'valor' => $valor];
        }

        return $grupos;
    }

    /** Turnos que puede crear en ventanilla (Recepcion::tramitesQuePuedeCrear). */
    public const TRAMITES_TURNO = ['Solicitud', 'Asesoría'];

    /** 1. Tipo de situación (pueden marcarse varias). Clave => texto corto. */
    public const TIPOS = [
        'discriminacion'    => 'Discriminación',
        'acoso_laboral'     => 'Acoso / hostigamiento laboral (violencia laboral)',
        'acoso_sexual'      => 'Acoso u hostigamiento sexual',
        'riesgo_trabajo'    => 'Riesgo o accidente de trabajo',
        'violencia_laboral' => 'Malos tratos o violencia laboral',
    ];

    /** Como lo dice el formato impreso. */
    public const TIPOS_FORMATO = [
        'discriminacion'    => 'He sido objeto de discriminación.',
        'acoso_laboral'     => 'He sido objeto de acoso / hostigamiento laboral (violencia laboral).',
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
        // "6. No somos competentes" en la lista de canalizar: se cierra sin canalizar.
        'incompetente' => 'No somos competentes',
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
        return $usuario !== null && ($usuario->hasAnyRole(self::ATIENDEN) || self::enlace($usuario));
    }

    public static function consulta(?User $usuario): bool
    {
        return $usuario !== null && ($usuario->hasAnyRole(array_merge(self::ATIENDEN, self::CONSULTAN)) || self::enlace($usuario));
    }

    /** Ve las estadísticas de excepción. El Enlace de Género e Igualdad no. */
    public static function estadisticas(?User $usuario): bool
    {
        return $usuario !== null && $usuario->hasAnyRole(['Super Usuario', 'Estadistica', 'Directivo', self::ROL]);
    }

    /** ¿Tiene el distintivo "Enlace de Género e Igualdad"? */
    public static function enlace(?User $usuario): bool
    {
        return $usuario !== null && $usuario->exists && self::hayPermisoEnlace()
            && $usuario->hasDirectPermission(self::PERMISO_ENLACE);
    }

    /**
     * Pone o quita el distintivo (desde Usuarios). Quien ya tiene el rol no lo
     * necesita: se le quita para que no quede un acceso que nadie ve.
     */
    public static function elegirEnlace(User $usuario, bool $enlace): void
    {
        if (! self::hayPermisoEnlace()) {
            return;
        }

        $debe  = $enlace && ! $usuario->hasRole(self::ROL);
        $tiene = $usuario->hasDirectPermission(self::PERMISO_ENLACE);

        if ($debe && ! $tiene) {
            $usuario->givePermissionTo(self::PERMISO_ENLACE);
        } elseif (! $debe && $tiene) {
            $usuario->revokePermissionTo(self::PERMISO_ENLACE);
        }
    }

    /** Antes de correr la migración el permiso no existe: no se truena. */
    private static function hayPermisoEnlace(): bool
    {
        static $hay = null;

        return $hay ??= \Spatie\Permission\Models\Permission::where('name', self::PERMISO_ENLACE)->where('guard_name', 'web')->exists();
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
