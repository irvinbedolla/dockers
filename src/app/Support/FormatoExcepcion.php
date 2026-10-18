<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Formato de atención para casos previstos en el art. 685 Ter LFT y
 * situaciones de violencia laboral (Departamento de Conciliación, Género e
 * Igualdad).
 *
 * Un caso nace de una cita/turno (tipo "cita", recepcion.id) o de una
 * solicitud (tipo "solicitud", seer_general.id). El formato se guarda en
 * seer_casos_excepcion (un renglón por caso) y se puede ir llenando por
 * partes: mientras no se termine es borrador (terminado_at null). El PDF se
 * puede descargar en cualquier momento; lo que falte sale en blanco para
 * llenarse a mano.
 *
 * Al abrirlo se prellena con lo que el sistema ya sabe (cita, solicitud,
 * solicitante y citado); lo que se guardó manda sobre lo prellenado.
 */
class FormatoExcepcion
{
    public const TIPOS_ORIGEN = ['cita', 'solicitud'];

    /** Campos del formato (los que viajan del formulario a la tabla). */
    public const CAMPOS = [
        'fecha', 'expediente', 'nombre', 'edad', 'genero', 'genero_otro', 'telefono', 'correo',
        'empresa', 'area_adscripcion', 'puesto', 'conoce_jefe', 'jefe_inmediato',
        'tipos', 'motivos', 'riesgo_lugar', 'frecuencia', 'situacion_laboral', 'situacion_otro',
        'descripcion_persona', 'descripcion_conductas', 'observaciones', 'vulnerables',
        'resultado', 'instituciones', 'domicilio_procuraduria',
    ];

    /**
     * Cita, solicitud y formato guardado de un caso. 404 si no es caso de
     * excepción.
     *
     * @return array{tipo: string, id: int, cita: ?object, solicitud: ?object, caso: ?object}
     */
    public static function origen(string $tipo, int $id): array
    {
        abort_unless(in_array($tipo, self::TIPOS_ORIGEN, true), 404);

        $cita = $solicitud = null;

        if ($tipo === 'cita') {
            $cita = DB::table('recepcion')->where('id', $id)->first();
            abort_unless($cita && $cita->exepcion === 'Si', 404);
            $solicitud = DB::table('seer_general')->where('recepcion_id', $cita->id)->orderBy('id')->first();
        } else {
            $solicitud = DB::table('seer_general')->where('id', $id)->first();
            abort_unless($solicitud && CasosExcepcion::solicitudEsCaso($solicitud), 404);
            $cita = $solicitud->recepcion_id ? DB::table('recepcion')->where('id', $solicitud->recepcion_id)->first() : null;
        }

        // El formato puede estar ligado a la cita o a la solicitud del mismo caso.
        $caso = DB::table('seer_casos_excepcion')
            ->where(function ($q) use ($cita, $solicitud) {
                $q->whereRaw('1 = 0');
                if ($cita) {
                    $q->orWhere('id_turno', $cita->id);
                }
                if ($solicitud && CasosExcepcion::conFormato()) {
                    $q->orWhere('id_solicitud', $solicitud->id);
                }
            })
            ->orderByDesc('id')
            ->first();

        return compact('tipo', 'id', 'cita', 'solicitud', 'caso');
    }

    /** Valores del formato: lo guardado encima de lo prellenado. */
    public static function datos(array $origen): array
    {
        $datos = self::prellenado($origen);

        if ($caso = $origen['caso']) {
            foreach (self::CAMPOS as $campo) {
                $valor = $caso->{$campo} ?? null;
                if (in_array($campo, ['tipos', 'instituciones'], true)) {
                    $valor = $valor ? (json_decode($valor, true) ?: []) : null;
                }
                if ($campo === 'conoce_jefe' && $valor !== null) {
                    $valor = (string) (int) $valor;
                }
                if ($valor !== null && $valor !== '' && $valor !== []) {
                    $datos[$campo] = $valor;
                }
            }
            // Formato del sistema anterior: sólo traía tipo_caso.
            if (empty($caso->tipos ?? null) && ! empty($caso->tipo_caso)) {
                $datos['tipos'] = array_values(array_unique(array_merge($datos['tipos'] ?? [], self::tiposDe($caso->tipo_caso))));
            }
        }

        $datos['tipos'] ??= [];
        $datos['instituciones'] ??= [];

        return $datos;
    }

    /** Lo que el sistema ya sabe del caso. */
    public static function prellenado(array $origen): array
    {
        $cita = $origen['cita'];
        $sol  = $origen['solicitud'];

        $solicitante = $sol ? DB::table('seer_solicitante')->where('id_solicitud', $sol->id)->orderBy('id')->first() : null;
        $citado = $sol ? DB::table('seer_citados')->where('id_solicitud', $sol->id)->orderBy('id')->first() : null;

        $sexo = $solicitante->sexo ?? $cita->sexo ?? null;
        $genero = match ($sexo) {
            'M', 'Mujer', 'Femenino'   => 'F',
            'H', 'Hombre', 'Masculino' => 'M',
            null, ''                   => null,
            default                    => 'Otro',
        };

        $tipoCaso = $cita->tipo_caso ?? null;

        return array_filter([
            'fecha'                 => now()->toDateString(),
            'expediente'            => ($sol->NUE ?? null) ?: ($cita ? FolioCita::mostrar($cita) : null),
            'nombre'                => self::texto($solicitante->nombre ?? $cita->solicitante ?? null),
            'edad'                  => $solicitante->edad ?? $cita->edad ?? null,
            'genero'                => $genero,
            'genero_otro'           => $genero === 'Otro' ? ($sexo === 'NB' ? 'No binario' : $sexo) : null,
            'telefono'              => $solicitante->telefono1 ?? $cita->telefono ?? null,
            'correo'                => $solicitante->email ?? $cita->correo ?? null,
            'empresa'               => $citado ? trim($citado->nombre.' '.$citado->primer_apellido.' '.$citado->segundo_apellido) : null,
            'puesto'                => $solicitante->puesto ?? null,
            'tipos'                 => self::tiposDe($tipoCaso),
            'motivos'               => null,
            'descripcion_conductas' => self::texto(($solicitante->descripcionSolicitud ?? null) ?: ($cita->conflicto ?? null)),
            'vulnerables'           => in_array($cita->vulnerables ?? null, CasosExcepcion::GRUPOS_VULNERABLES, true) ? $cita->vulnerables : null,
            'domicilio_procuraduria'=> self::domicilioSugerido(self::sede($origen)),
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /** Sede del caso: la de la solicitud o la de la cita. */
    public static function sede(array $origen): ?string
    {
        return $origen['solicitud']->delegacion ?? $origen['cita']->delegacion ?? null;
    }

    /** El último domicilio de la Procuraduría que se usó en la sede. */
    public static function domicilioSugerido(?string $sede): ?string
    {
        if (! $sede || ! CasosExcepcion::conFormato()) {
            return null;
        }

        return DB::table('seer_casos_excepcion')
            ->where('delegacion', $sede)
            ->whereNotNull('domicilio_procuraduria')
            ->orderByDesc('id')
            ->value('domicilio_procuraduria');
    }

    /** Del tipo de caso de la cita (texto libre de antes) a las claves del formato. */
    public static function tiposDe(?string $tipoCaso): array
    {
        $t = mb_strtolower(trim((string) $tipoCaso));

        return match (true) {
            $t === ''                                                           => [],
            str_contains($t, 'acoso') || str_contains($t, 'hostigamiento')     => ['acoso_sexual'],
            str_contains($t, 'riesgo') || str_contains($t, 'accidente') || str_contains($t, 'enfermedad') => ['riesgo_trabajo'],
            str_contains($t, 'malos tratos') || str_contains($t, 'violencia')  => ['violencia_laboral'],
            str_contains($t, 'discrimina') || in_array($t, ['condición social', 'embarazo', 'raza', 'razones de sexo', 'religión', 'orientación sexual', 'origen étnico'], true) => ['discriminacion'],
            default                                                             => [],
        };
    }

    /**
     * Guarda el formato. $terminar cierra la atención (terminado_at); si no,
     * queda como borrador.
     */
    public static function guardar(array $origen, array $datos, bool $terminar, User $usuario): int
    {
        $cita = $origen['cita'];
        $sol  = $origen['solicitud'];

        $tipos = array_values(array_intersect(array_keys(CasosExcepcion::TIPOS), Arr::wrap($datos['tipos'] ?? [])));
        // También se canaliza "para atención integral" cuando la persona sí
        // continúa con la conciliación (el oficio lo contempla).
        $instituciones = array_values(array_intersect(array_keys(CasosExcepcion::INSTITUCIONES), Arr::wrap($datos['instituciones'] ?? [])));

        $conoce = isset($datos['conoce_jefe']) && $datos['conoce_jefe'] !== '' ? (string) (int) $datos['conoce_jefe'] : null;

        $fila = [
            'id_turno'               => $cita->id ?? null,
            'id_solicitud'           => $sol->id ?? null,
            'delegacion'             => self::sede($origen),
            'fecha'                  => $datos['fecha'] ?? now()->toDateString(),
            'expediente'             => $datos['expediente'] ?? null,
            'nombre'                 => $datos['nombre'] ?? null,
            'edad'                   => $datos['edad'] ?? null,
            'genero'                 => $datos['genero'] ?? null,
            'genero_otro'            => ($datos['genero'] ?? null) === 'Otro' ? ($datos['genero_otro'] ?? null) : null,
            'telefono'               => $datos['telefono'] ?? null,
            'correo'                 => $datos['correo'] ?? null,
            'empresa'                => $datos['empresa'] ?? null,
            'area_adscripcion'       => $datos['area_adscripcion'] ?? null,
            'puesto'                 => $datos['puesto'] ?? null,
            'conoce_jefe'            => $conoce === null ? null : (int) $conoce,
            'jefe_inmediato'         => $conoce === '1' ? ($datos['jefe_inmediato'] ?? null) : null,
            'tipos'                  => json_encode($tipos),
            // Para lo que todavía lee tipo_caso (reportes anteriores): el primero.
            'tipo_caso'              => $tipos ? CasosExcepcion::TIPOS[$tipos[0]] : null,
            'motivos'                => in_array('discriminacion', $tipos, true) ? ($datos['motivos'] ?? null) : null,
            'riesgo_lugar'           => in_array('riesgo_trabajo', $tipos, true) ? ($datos['riesgo_lugar'] ?? null) : null,
            'frecuencia'             => $datos['frecuencia'] ?? null,
            'situacion_laboral'      => $datos['situacion_laboral'] ?? null,
            'situacion_otro'         => ($datos['situacion_laboral'] ?? null) === 'otro' ? ($datos['situacion_otro'] ?? null) : null,
            'descripcion_persona'    => $datos['descripcion_persona'] ?? null,
            'descripcion_conductas'  => $datos['descripcion_conductas'] ?? null,
            'observaciones'          => $datos['observaciones'] ?? null,
            'vulnerables'            => $datos['vulnerables'] ?? null,
            'resultado'              => $datos['resultado'] ?? null,
            'instituciones'          => json_encode($instituciones),
            // Para lo que todavía lee "dependencia".
            'dependencia'            => $instituciones ? implode(', ', array_map(fn ($k) => CasosExcepcion::INSTITUCIONES[$k], $instituciones)) : null,
            'domicilio_procuraduria' => in_array('procuraduria', $instituciones, true) ? ($datos['domicilio_procuraduria'] ?? null) : null,
            'id_user'                => $usuario->id,
            'updated_at'             => now(),
        ];

        if ($terminar) {
            $fila['terminado_at'] = $origen['caso']->terminado_at ?? now();
            $fila['hora'] = now()->format('H:i');
        }

        if ($origen['caso']) {
            DB::table('seer_casos_excepcion')->where('id', $origen['caso']->id)->update($fila);
            $id = (int) $origen['caso']->id;
        } else {
            $id = (int) DB::table('seer_casos_excepcion')->insertGetId($fila + ['created_at' => now(), 'hora' => now()->format('H:i')]);
        }

        // No continúa: si ya hay solicitud y así se pidió, se cierra como "Canalizada".
        if ($terminar && ($datos['resultado'] ?? null) === 'canaliza' && $sol && ($datos['cerrar_solicitud'] ?? null) === '1') {
            self::cerrarSolicitud($sol, $instituciones, $usuario);
        }

        return $id;
    }

    /** Estatus de solicitud que todavía se pueden cerrar como canalizada. */
    public const SOLICITUD_ABIERTA = ['Pendiente', 'Aceptado', 'Confirmado', 'Reagendada', 'Prevencion'];

    public static function puedeCerrar(?object $solicitud): bool
    {
        return $solicitud !== null
            && OficioCanalizacion::migrado()
            && in_array($solicitud->estatus, self::SOLICITUD_ABIERTA, true);
    }

    /**
     * La persona no sigue con la conciliación (art. 685 Ter): la solicitud y
     * sus audiencias pendientes quedan "Canalizada" (no es desistimiento) y
     * se deja constancia en las observaciones. La audiencia libera su horario
     * (AgendaAudiencias::LIBERAN).
     */
    public static function cerrarSolicitud(object $solicitud, array $instituciones, User $usuario): void
    {
        if (! self::puedeCerrar($solicitud)) {
            return;
        }

        $a = $instituciones ? implode(', ', array_map(fn ($k) => CasosExcepcion::INSTITUCIONES[$k], $instituciones)) : 'otra institución';
        $nota = 'Canalizada por caso de excepción (art. 685 Ter LFT) a: '.$a.'. '.now()->format('d/m/Y H:i').', '.trim((string) $usuario->name).'.';

        DB::transaction(function () use ($solicitud, $nota) {
            DB::table('seer_general')->where('id', $solicitud->id)->update([
                'estatus'           => 'Canalizada',
                'fecha_terminacion' => now()->toDateString(),
                'observaciones'     => trim(trim((string) $solicitud->observaciones)."\n".$nota),
                'updated_at'        => now(),
            ]);

            DB::table('audiencias')->where('id_solicitud', $solicitud->id)->where('estatus', 'Pendiente')
                ->update(['estatus' => 'Canalizada', 'updated_at' => now()]);
        });
    }

    private static function texto(?string $t): ?string
    {
        $t = trim(preg_replace('/\s+/', ' ', (string) $t));

        return $t === '' ? null : $t;
    }
}
