<?php

namespace App\Support;

use App\Models\Poder;
use Illuminate\Http\Request;

/**
 * Cita de solicitud patronal individual.
 *
 * En el formulario de cita (público y ventanilla), con "Solicitud de
 * conciliación" + "Acudes en calidad de: Empleador" + "Sí, ya tengo folio",
 * la persona escribe su folio de representante patronal (abogados.idAbogado)
 * y los datos de la persona trabajadora a la que va a citar, igual que en
 * "Soy patronal individual". Sus datos de solicitante salen del folio, así
 * que no se piden CURP ni domicilio (FlujoCita::camposDeCita).
 *
 * En Mi módulo, "Preparar solicitud" (PrellenadoCita) lleva al auxiliar por
 * "Soy patronal individual" con el folio y la persona citada ya puestos.
 */
class CitaPatronal
{
    /** ¿Ya corrió la migración de recepcion.poder_folio / citado? */
    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= \Illuminate\Support\Facades\Schema::hasColumn('recepcion', 'poder_folio');
    }

    /**
     * ¿El formulario de cita llegó como solicitud patronal (con folio)? No
     * depende de la migración: una cita patronal nunca pide CURP ni domicilio
     * (FlujoCita::camposDeCita); sin la migración sólo no se guardan el folio
     * ni la persona citada (ver camposDeCita).
     */
    public static function aplica(Request $request): bool
    {
        return $request->input('tipo') === 'Solicitud'
            && $request->input('calidad') === 'empleador'
            && $request->filled('poder_folio');
    }

    /** ¿La cita es de solicitud patronal? */
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
     * Folio y persona citada del formulario de cita, validados, listos para
     * Recepcion::create. La CURP es la de quien presentará la solicitud (la
     * misma que pone solicitud_parte2AuxP), para que también se ligue por CURP.
     *
     * @return array{0: array, 1: ?string} [columnas, error]
     */
    public static function camposDeCita(Request $request): array
    {
        $folio = (int) preg_replace('/\D/', '', (string) $request->input('poder_folio'));
        $poder = $folio > 0 ? Poder::find($folio) : null;
        if (! $poder) {
            return [[], 'El folio de representante patronal no existe. Revísalo o regístrate en "Registro de representante patronal".'];
        }
        [$status, $mensaje] = self::estado($poder);
        if ($status !== 'elegible') {
            return [[], $mensaje];
        }

        $limpio = fn (string $campo) => mb_substr(mb_strtoupper(trim((string) $request->input($campo))), 0, 60);
        $citado = [
            'nombre'           => $limpio('citado_nombre'),
            'primer_apellido'  => $limpio('citado_primer_apellido'),
            'segundo_apellido' => $limpio('citado_segundo_apellido'),
        ];
        if ($citado['nombre'] === '' || $citado['primer_apellido'] === '') {
            return [[], 'Escribe el nombre y el primer apellido de la persona trabajadora a la que vas a citar.'];
        }

        $curp = Curp::normalizar($poder->reprecentante === 'Si' ? $poder->curp_representante : $poder->curp_patronal);
        $curp = strlen($curp) === 18 ? $curp : null;

        // Sin la migración de recepcion.poder_folio / citado la cita se agenda
        // igual (sólo con la CURP del folio); en Mi módulo no habrá prellenado patronal.
        if (! self::migrado()) {
            \Illuminate\Support\Facades\Log::warning('Cita patronal sin la migración add_cita_patronal_to_recepcion: no se guardan folio ni persona citada.', ['folio' => $poder->idAbogado]);

            return [array_filter(['curp' => $curp]), null];
        }

        return [array_filter([
            'poder_folio' => $poder->idAbogado,
            'citado'      => json_encode(array_filter($citado, fn ($v) => $v !== ''), JSON_UNESCAPED_UNICODE),
            'curp'        => $curp,
        ], fn ($v) => $v !== null), null];
    }

    /** Persona trabajadora a citar guardada en la cita. */
    public static function citado(object $cita): array
    {
        return json_decode((string) ($cita->citado ?? ''), true) ?: [];
    }

    /** "NOMBRE APELLIDO APELLIDO" de la persona citada (para Mi módulo). */
    public static function nombreCitado(object $cita): ?string
    {
        $c = self::citado($cita);
        $nombre = trim(implode(' ', array_filter([$c['nombre'] ?? null, $c['primer_apellido'] ?? null, $c['segundo_apellido'] ?? null])));

        return $nombre !== '' ? $nombre : null;
    }
}
