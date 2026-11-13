<?php

namespace App\Support;

use App\Models\Poder;
use Illuminate\Http\Request;

/**
 * Quién agenda la cita y, si es la parte patronal, con qué folio.
 *
 * En el formulario de cita (público y ventanilla), con "Solicitud de
 * conciliación" o "Ratificación de convenio" se pregunta "Acudes en calidad
 * de" (trabajador | empleador). Si es el empleador, se pregunta si ya tiene
 * folio de representante patronal (abogados.idAbogado):
 *
 *   - Sin folio: el paso 2 son los datos de la persona trabajadora (nombre
 *     desglosado, edad, sexo, CURP y domicilio). El nombre completo, correo y
 *     teléfono de ese paso son de quien agenda: ahí le llega el acuse.
 *   - Con folio: lo mismo, más el folio validado. Los datos de la parte
 *     patronal salen del folio.
 *
 * En una ratificación también se pide el nombre desglosado cuando agenda el
 * trabajador (el formulario de ratificación lo usa así).
 *
 * Dónde queda cada cosa en recepcion:
 *   calidad      'trabajador' | 'empleador'
 *   poder_folio  el folio, si lo dio
 *   citado       JSON de la persona trabajadora: nombre, primer_apellido,
 *                segundo_apellido y, si agendó el empleador, curp y domicilio
 *   curp/domicilio  los de quien agenda si es trabajador; si es el empleador
 *                con folio, la CURP del folio (para ligar la solicitud por CURP)
 *
 * En Mi módulo, PrellenadoCita usa esto para abrir "Soy patronal individual"
 * (solicitud) o el formulario de ratificación con la persona ya puesta.
 */
class CitaPatronal
{
    public const TRAMITES = ['Solicitud', 'Ratificación'];

    /** ¿Ya corrió la migración de recepcion.poder_folio / citado / calidad? */
    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= \Illuminate\Support\Facades\Schema::hasColumn('recepcion', 'calidad');
    }

    /** ¿La cita la agendó el empleador (con o sin folio)? */
    public static function esEmpleador(object $cita): bool
    {
        return self::migrado() && (($cita->calidad ?? null) === 'empleador' || ! empty($cita->poder_folio));
    }

    /** ¿La cita trae folio de representante patronal? */
    public static function esPatronal(object $cita): bool
    {
        return self::migrado() && ! empty($cita->poder_folio);
    }

    /**
     * Mismo criterio que TurnosController::validarFolio y la pantalla
     * "Datos del Solicitante Patronal": sólo un folio elegible deja continuar.
     *
     * @return array{0: string, 1: string} [status, mensaje]
     */
    public static function estado(Poder $poder): array
    {
        if (! is_null($poder->fechaVigencia) && $poder->fechaVigencia < now()->format('Y-m-d')) {
            return ['sin_vigencia', 'El folio de representante patronal ya no está vigente.'];
        }
        if ($poder->estatus !== 'Validado') {
            return ['requiere_validacion', 'El folio de representante patronal todavía requiere validación.'];
        }

        return ['elegible', 'Elegible'];
    }

    /**
     * El folio escrito, si existe y es elegible.
     *
     * @return array{0: ?Poder, 1: ?string} [poder, error]
     */
    public static function poderElegible($folio): array
    {
        $folio = (int) preg_replace('/\D/', '', (string) $folio);
        $poder = $folio > 0 ? Poder::find($folio) : null;
        if (! $poder) {
            return [null, 'El folio de representante patronal no existe. Revísalo o regístrate en "Registro de representante patronal".'];
        }
        [$status, $mensaje] = self::estado($poder);

        return $status === 'elegible' ? [$poder, null] : [null, $mensaje];
    }

    /**
     * Columnas de recepcion que salen del formulario de cita, validadas:
     * solicitante, curp, domicilio, calidad, poder_folio y citado.
     *
     * @return array{0: array, 1: ?string} [columnas, error]
     */
    public static function camposDelFormulario(Request $request): array
    {
        $tipo      = (string) $request->input('tipo');
        $calidad   = in_array($tipo, self::TRAMITES, true) && in_array($request->input('calidad'), ['trabajador', 'empleador'], true)
            ? $request->input('calidad')
            : null;
        $empleador = $calidad === 'empleador';
        $conFolio  = $empleador && $request->input('tiene_folio') === 'si';
        // Nombre desglosado de la persona trabajadora: lo pide el formulario de
        // ratificación y "Datos Personales del Citado" de la solicitud patronal.
        $desglosado = $empleador || $tipo === 'Ratificación';

        if (in_array($tipo, self::TRAMITES, true) && ! $calidad) {
            return [[], 'Indica si acudes como trabajador o como empleador.'];
        }

        // CURP y domicilio (de quien agenda o, si es el empleador, del trabajador).
        [$flujo, $error] = FlujoCita::camposDeCita($request);
        if ($error) {
            return [[], $error];
        }

        $limpio = fn (string $campo) => mb_substr(mb_strtoupper(trim((string) $request->input($campo))), 0, 60);
        $trabajador = [];
        if ($desglosado) {
            $trabajador = array_filter([
                'nombre'           => $limpio('citado_nombre'),
                'primer_apellido'  => $limpio('citado_primer_apellido'),
                'segundo_apellido' => $limpio('citado_segundo_apellido'),
            ], fn ($v) => $v !== '');
            if (empty($trabajador['nombre']) || empty($trabajador['primer_apellido'])) {
                return [[], 'Escribe el nombre y el primer apellido de la persona trabajadora.'];
            }
        }

        // Quien agenda: el nombre completo; en una ratificación del propio
        // trabajador, su nombre desglosado.
        $solicitante = $empleador || ! $desglosado
            ? mb_strtoupper(trim((string) $request->input('nombre')))
            : implode(' ', $trabajador);
        if ($solicitante === '') {
            return [[], $empleador ? 'Escribe el nombre de quien agenda la cita.' : 'Escribe tu nombre completo.'];
        }

        $poder = null;
        if ($conFolio) {
            [$poder, $error] = self::poderElegible($request->input('poder_folio'));
            if ($error) {
                return [[], $error];
            }
        }

        $campos = ['solicitante' => $solicitante];

        if (! self::migrado()) {
            if ($calidad) {
                \Illuminate\Support\Facades\Log::warning('Cita sin la migración add_cita_patronal_to_recepcion: no se guardan calidad, folio ni persona trabajadora.', ['folio' => $poder?->idAbogado]);
            }

            return [$campos + $flujo, null];
        }

        if ($empleador) {
            // La CURP y el domicilio capturados son de la persona trabajadora.
            $trabajador += array_filter([
                'curp'      => $flujo['curp'] ?? null,
                'domicilio' => isset($flujo['domicilio']) ? json_decode($flujo['domicilio'], true) : null,
            ]);
            $curpFolio = $poder ? Curp::normalizar($poder->reprecentante === 'Si' ? $poder->curp_representante : $poder->curp_patronal) : '';
            $campos += [
                'curp'      => strlen($curpFolio) === 18 ? $curpFolio : null,
                'domicilio' => null,
            ];
        } else {
            $campos += $flujo;
        }

        return [$campos + array_filter([
            'calidad'     => $calidad,
            'poder_folio' => $poder?->idAbogado,
            'citado'      => $trabajador ? json_encode($trabajador, JSON_UNESCAPED_UNICODE) : null,
        ], fn ($v) => $v !== null), null];
    }

    /**
     * Formulario corto de ventanilla (/turnos/nuevo): sólo trámite, municipio,
     * calidad (trabajador | empleador), nombre completo de quien solicita,
     * caso de excepción y horario. Ya no se piden CURP, domicilio, folio ni
     * la persona trabajadora: el auxiliar los captura en Mi módulo.
     *
     * @return array{0: array, 1: ?string} [columnas, error]
     */
    public static function camposVentanilla(Request $request): array
    {
        $calidad = $request->input('calidad');
        if (! in_array($calidad, ['trabajador', 'empleador'], true)) {
            return [[], 'Indica si quien solicita es trabajador o empleador.'];
        }

        $nombre = mb_substr(preg_replace('/\s+/u', ' ', mb_strtoupper(trim((string) $request->input('nombre')))), 0, 100);
        if ($nombre === '') {
            return [[], 'Escribe el nombre completo de quien solicita.'];
        }

        return [['solicitante' => $nombre] + (self::migrado() ? ['calidad' => $calidad] : []), null];
    }

    /**
     * "NOMBRE(S) APELLIDO1 APELLIDO2" → nombre, primer y segundo apellido.
     * Aproximado (con 3 o más palabras, las dos últimas son los apellidos);
     * se usa sólo para prellenar y el auxiliar lo revisa.
     *
     * @return array{nombre?: string, primer_apellido?: string, segundo_apellido?: string}
     */
    public static function desglosarNombre(?string $completo): array
    {
        $partes = preg_split('/\s+/u', mb_strtoupper(trim((string) $completo)), -1, PREG_SPLIT_NO_EMPTY);

        return match (true) {
            count($partes) === 0 => [],
            count($partes) === 1 => ['nombre' => $partes[0]],
            count($partes) === 2 => ['nombre' => $partes[0], 'primer_apellido' => $partes[1]],
            default => [
                'nombre'           => implode(' ', array_slice($partes, 0, -2)),
                'primer_apellido'  => $partes[count($partes) - 2],
                'segundo_apellido' => $partes[count($partes) - 1],
            ],
        };
    }

    /** Persona trabajadora guardada en la cita. */
    public static function citado(object $cita): array
    {
        return json_decode((string) ($cita->citado ?? ''), true) ?: [];
    }

    /** "NOMBRE APELLIDO APELLIDO" de la persona trabajadora (para Mi módulo). */
    public static function nombreCitado(object $cita): ?string
    {
        $c = self::citado($cita);
        $nombre = trim(implode(' ', array_filter([$c['nombre'] ?? null, $c['primer_apellido'] ?? null, $c['segundo_apellido'] ?? null])));

        return $nombre !== '' ? $nombre : null;
    }
}
