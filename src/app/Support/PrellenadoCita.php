<?php

namespace App\Support;

use App\Models\Recepcion;
use Illuminate\Support\Facades\DB;

/**
 * Fase 2 del flujo cita → solicitud: la solicitud del auxiliar arranca con lo
 * que la persona ya escribió al agendar.
 *
 *   preparar()  Desde "Mi módulo" ("Preparar solicitud"): guarda en la sesión
 *               del auxiliar los datos de la cita.
 *   paraVista() Lo usan las pantallas de la captura del trabajador; sólo
 *               ponen valores y los resaltan, el auxiliar revisa y corrige:
 *                 - solicitudAuxiliares: resalta "Soy trabajador(a)";
 *                 - datos generales (_inicio_formulario): municipio de la
 *                   fuente de empleo, delegación y objeto sugerido ('inicio');
 *                 - solicitanteAux (_prellenado): datos de la persona ('datos').
 *   ligar()     Al guardar la solicitud (guardar_solicitudAux) la liga con la
 *               cita (seer_general.recepcion_id), da la cita por atendida
 *               (fase 4: se quita de la pantalla) y limpia la sesión.
 *
 * No se crea nada en la base hasta que el auxiliar termina la solicitud como
 * siempre, así que no se gastan NUE ni se mueven estadísticas.
 */
class PrellenadoCita
{
    private const SESION = 'flujo_cita.prellenado';

    /** Un prellenado olvidado deja de aplicar después de esto. */
    private const VIGENCIA_MINUTOS = 180;

    public static function preparar(Recepcion $cita): void
    {
        session([self::SESION => [
            'cita_id' => $cita->id,
            'folio'   => $cita->folio_cita,
            'persona' => mb_strtoupper(trim((string) $cita->solicitante)),
            'en'      => now()->timestamp,
            'datos'   => self::datos($cita),
            'inicio'  => self::inicio($cita),
        ]]);
    }

    public static function paraVista(): ?array
    {
        $p = session(self::SESION);

        if (! is_array($p) || (now()->timestamp - ($p['en'] ?? 0)) > self::VIGENCIA_MINUTOS * 60) {
            session()->forget(self::SESION);

            return null;
        }

        return $p;
    }

    public static function descartar(): void
    {
        session()->forget(self::SESION);
    }

    /** Solicitud en línea: días antes y después de hoy en que se busca su cita. */
    public const VENTANA_EN_LINEA = 30;

    /**
     * Al guardar la solicitud la liga con su cita:
     *   1. la del prellenado, si el auxiliar la capturó desde la cita;
     *   2. si no, por la CURP del solicitante, una cita que aún no tenga
     *      solicitud: la de HOY si la captura el Centro, o la más cercana
     *      a hoy (± VENTANA_EN_LINEA días) si la persona la llenó en línea
     *      ($enLinea), porque puede llenarla antes o después de su cita.
     * Si la cita es de hoy, queda atendida y sale de la pantalla y de "quién
     * sigue". Una cita futura no se toca: todavía tiene que venir.
     */
    public static function ligar(object $solicitud, ?string $curp = null, bool $enLinea = false): void
    {
        if (! FlujoCita::migrado()) {
            return;
        }

        $p = $enLinea ? null : self::paraVista();
        if ($p) {
            $citaId  = (int) $p['cita_id'];
            $vinculo = 'prellenado';
            self::descartar();
        } elseif ($porCurp = $enLinea ? self::citaCercanaPorCurp($curp) : self::citaDeHoyPorCurp($curp)) {
            $citaId  = (int) $porCurp->id;
            $vinculo = 'curp';
        } else {
            return;
        }

        DB::table('seer_general')->where('id', $solicitud->id)->update(
            ['recepcion_id' => $citaId] + (FlujoCita::conSeguimiento() ? ['recepcion_vinculo' => $vinculo] : [])
        );

        // La solicitud ya quedó: la cita termina aquí aunque no piquen "Terminar".
        $cita = Recepcion::find($citaId);
        if ($cita && $cita->fecha && $cita->fecha->isToday() && in_array($cita->estatus, ['pendiente', 'confirmada'], true)) {
            $ahora = now();
            $cita->forceFill(array_filter([
                'estatus'            => 'atendido',
                'llamado_at'         => $cita->llamado_at ? null : $ahora,
                'atencion_inicio_at' => $cita->atencion_inicio_at ? null : $ahora,
                'atencion_fin_at'    => FlujoCita::conFinAtencion() ? $ahora : null,
            ]))->save();
            PantallaEspera::olvidar((string) $cita->delegacion);
        }
    }

    /** Cita de hoy con esa CURP que todavía no tiene solicitud (la que ya llegó, primero). */
    public static function citaDeHoyPorCurp(?string $curp): ?Recepcion
    {
        $curp = Curp::normalizar($curp);
        if (strlen($curp) !== 18) {
            return null;
        }

        $yo = auth()->id();

        return Recepcion::where('fecha', now()->toDateString())
            ->where('curp', $curp)
            ->whereIn('estatus', ['confirmada', 'pendiente', 'atendido'])
            ->whereNotExists(fn ($q) => $q->selectRaw(1)->from('seer_general as g')->whereColumn('g.recepcion_id', 'recepcion.id'))
            ->orderByRaw("CASE estatus WHEN 'confirmada' THEN 0 WHEN 'atendido' THEN 1 ELSE 2 END")
            ->when($yo, fn ($q) => $q->orderByRaw('auxiliar = ? DESC', [$yo]))
            ->orderBy('hora')
            ->first();
    }

    /** Solicitud en línea: la cita con esa CURP sin solicitud más cercana a hoy (± VENTANA_EN_LINEA días). */
    public static function citaCercanaPorCurp(?string $curp): ?Recepcion
    {
        $curp = Curp::normalizar($curp);
        if (strlen($curp) !== 18) {
            return null;
        }

        $hoy = now()->toDateString();

        return Recepcion::whereBetween('fecha', [now()->subDays(self::VENTANA_EN_LINEA)->toDateString(), now()->addDays(self::VENTANA_EN_LINEA)->toDateString()])
            ->where('curp', $curp)
            ->where('tipo', 'Solicitud')
            ->where('estatus', '!=', 'expirada')
            ->whereNotExists(fn ($q) => $q->selectRaw(1)->from('seer_general as g')->whereColumn('g.recepcion_id', 'recepcion.id'))
            ->orderByRaw('ABS(DATEDIFF(fecha, ?))', [$hoy])
            ->first();
    }

    /** Campo del formulario del trabajador (solicitanteAux) => valor. */
    private static function datos(Recepcion $c): array
    {
        $dom = json_decode((string) $c->domicilio, true) ?: [];
        $curp = Curp::normalizar($c->curp);
        $curpValida = $curp !== '' && Curp::valida($curp);

        $campos = [
            'nombre'               => mb_strtoupper(trim((string) $c->solicitante)),
            'curp'                 => $curpValida ? $curp : null,
            'fecha_nacimiento'     => $curpValida ? Curp::fecha($curp) : null,
            'edad'                 => $c->edad,
            'genero'               => in_array($c->sexo, ['H', 'M'], true) ? $c->sexo : null,
            'nacionalidad'         => $curpValida && substr($curp, 11, 2) !== 'NE' ? 'Mexicana' : null,
            'correo'               => $c->correo,
            'telefono1'            => preg_replace('/\D/', '', (string) $c->telefono) ?: null,
            // El municipio de la cita es el de la FUENTE DE EMPLEO (así lo pregunta el
            // formulario de cita), no el del domicilio: va en datos generales (inicio()).
            'vialidad'             => ! empty($dom['calle']) ? 'CALLE' : null,
            'vialidad_calle'       => $dom['calle'] ?? null,
            'numExt'               => $dom['num_ext'] ?? null,
            'numInt'               => $dom['num_int'] ?? null,
            'colonia_solicitante'  => $dom['colonia'] ?? null,
            'cp'                   => $dom['cp'] ?? null,
            'descripcionSolicitud' => trim((string) ($c->conflicto ?: $c->observaciones)) ?: null,
            // Si la cita venía como caso de excepción, la solicitud también.
            'excepcion'            => $c->exepcion === 'Si' ? 'Si' : null,
        ];

        return array_filter($campos, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Pantalla "Datos generales de la solicitud": municipio de la fuente de
     * empleo (la delegación sale sola de él) y los objetos sugeridos.
     *
     * @return array{municipio: ?int, motivos: int[]}
     */
    public static function inicio(Recepcion $c): array
    {
        return [
            'municipio' => $c->municipio ? (int) $c->municipio : null,
            'motivos'   => self::objetosSugeridos((string) ($c->conflicto ?: $c->observaciones)),
        ];
    }

    /**
     * Objetos de la solicitud (catalogo_motivos, trabajador) que sugiere lo
     * que la persona escribió al agendar. Es sólo una sugerencia: sale
     * resaltada para que el auxiliar la confirme o la quite.
     *
     * @return int[] ids de catalogo_motivos
     */
    public static function objetosSugeridos(string $texto): array
    {
        $t = mb_strtoupper(\Illuminate\Support\Str::ascii($texto));
        if (trim($t) === '') {
            return [];
        }

        $despido  = (bool) preg_match('/DESPID|DESPIDIERON|ME CORRI|ME CORRE|CORRIERON|ME SACARON|ME DIERON DE BAJA|DE BAJA SIN/', $t);
        $renuncia = (bool) preg_match('/RENUNCI/', $t);
        $pago     = (bool) preg_match('/FINIQUITO|LIQUIDACI|AGUINALDO|VACACION|PRIMA |HORAS? EXTRA|SALARIO|SUELDO|PAGA|PAGO|UTILIDADES|REPARTO|INFONAVIT|SEGURO SOCIAL|IMSS/', $t);
        $reconoce = (bool) preg_match('/RECONO[CZ]/', $t);

        $nombres = array_filter([
            $despido ? 'Despido' : null,
            $renuncia ? 'Terminación voluntaria de la relación de trabajo' : null,
            // El despido ya reclama lo que se le debe; el pago va solo si no hubo despido.
            ($pago && ! $despido) ? 'Pago de prestaciones' : null,
            $reconoce ? 'Reconocimiento de Derechos' : null,
        ]);
        if (! $nombres) {
            return [];
        }

        $ids = DB::table('catalogo_motivos')->where('tipo_solicitud', '1')->whereIn('motivo', $nombres)->pluck('id', 'motivo');

        return array_values(array_map('intval', array_filter(array_map(fn ($n) => $ids[$n] ?? null, $nombres))));
    }
}
