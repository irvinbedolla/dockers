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
 *   paraVista() Lo usan las pantallas de la captura; sólo ponen valores y
 *               los resaltan, el auxiliar revisa y corrige:
 *                 - solicitudAuxiliares: resalta "Soy trabajador(a)" o, si la
 *                   cita es patronal (CitaPatronal), "Soy patronal individual";
 *                 - datos generales (_inicio_formulario): municipio de la
 *                   fuente de empleo, delegación y objeto sugerido ('inicio');
 *                 - solicitanteAux (_prellenado): datos de la persona ('datos');
 *                 - patronal: solicitanteAuxP (folio de representante, 'datos')
 *                   y citadosAuxP (persona trabajadora a citar, 'citado').
 *               Con paraVista('trabajador') / paraVista('patronal') cada
 *               pantalla sólo toma el prellenado de su tipo.
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
        // La agendó el empleador (con o sin folio): "Soy patronal individual",
        // con la persona trabajadora como citada; el folio, si lo dio.
        $patronal = CitaPatronal::esEmpleador($cita);

        session([self::SESION => [
            'cita_id' => $cita->id,
            'folio'   => $cita->folio_cita,
            'tipo'    => $patronal ? 'patronal' : 'trabajador',
            'persona' => mb_strtoupper(trim((string) $cita->solicitante)),
            'en'      => now()->timestamp,
            'datos'   => $patronal ? self::datosPatronal($cita) : self::datos($cita),
            'citado'  => $patronal ? self::citadoPatronal($cita) : [],
            'inicio'  => $patronal
                // Los objetos sugeridos son del catálogo del trabajador: en patronal no se sugieren.
                ? ['municipio' => $cita->municipio ? (int) $cita->municipio : null, 'motivos' => []]
                : self::inicio($cita),
        ]]);
    }

    /**
     * El prellenado vigente; con $tipo ('trabajador' | 'patronal'), sólo si
     * es de ese tipo (la captura del trabajador no toma el de una cita
     * patronal y al revés). Los anteriores a este cambio son de trabajador.
     */
    public static function paraVista(?string $tipo = null): ?array
    {
        $p = session(self::SESION);

        if (! is_array($p) || (now()->timestamp - ($p['en'] ?? 0)) > self::VIGENCIA_MINUTOS * 60) {
            session()->forget(self::SESION);

            return null;
        }

        if ($tipo !== null && ($p['tipo'] ?? 'trabajador') !== $tipo) {
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

        // El prellenado sólo liga una solicitud de su mismo tipo (tipo_solicitud 2 = patronal individual).
        $tipo = isset($solicitud->tipo_solicitud)
            ? ((string) $solicitud->tipo_solicitud === '2' ? 'patronal' : 'trabajador')
            : null;
        $p = $enLinea ? null : self::paraVista($tipo);
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
                'motivo_termino'     => MotivoTermino::migrado() && ! $cita->motivo_termino ? 'solicitud' : null,
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
            ->whereNotIn('estatus', ['expirada', 'cancelada'])
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

    /** Campo de "Datos del Solicitante Patronal" (solicitanteAuxP) => valor. */
    private static function datosPatronal(Recepcion $c): array
    {
        return array_filter([
            'folio'     => $c->poder_folio ? (string) $c->poder_folio : null,
            'excepcion' => $c->exepcion === 'Si' ? 'Si' : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /** Campo de "Datos Personales del Citado" (citadosAuxP) => valor. */
    private static function citadoPatronal(Recepcion $c): array
    {
        $citado = CitaPatronal::citado($c);
        $dom = is_array($citado['domicilio'] ?? null) ? $citado['domicilio'] : [];
        $curp = Curp::normalizar($citado['curp'] ?? null);
        $curpValida = $curp !== '' && Curp::valida($curp);

        return array_filter([
            'nombre'               => $citado['nombre'] ?? null,
            'primer_apellido'      => $citado['primer_apellido'] ?? null,
            'segundo_apellido'     => $citado['segundo_apellido'] ?? null,
            // La edad y la CURP capturadas al agendar son de la persona trabajadora.
            'curp'                 => $curpValida ? $curp : null,
            'fecha_nacimiento'     => $curpValida ? Curp::fecha($curp) : null,
            'edad'                 => $c->edad,
            'vialidad'             => ! empty($dom['calle']) ? 'CALLE' : null,
            'calle'                => $dom['calle'] ?? null,
            'exterior'             => $dom['num_ext'] ?? null,
            'interior'             => $dom['num_int'] ?? null,
            'colonia'              => $dom['colonia'] ?? null,
            'cp'                   => $dom['cp'] ?? null,
            'descripcionSolicitud' => trim((string) ($c->conflicto ?: $c->observaciones)) ?: null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Formulario de ratificación de Mi módulo (citas.blade.php) para una cita
     * sin turno todavía: lo que se capturó al agendar. El domicilio de ese
     * formulario es el de la fuente de empleo, así que sólo va su municipio.
     */
    public static function ratificacion(Recepcion $c): array
    {
        $trabajador = CitaPatronal::citado($c);
        // Formulario corto de ventanilla: si agendó el propio trabajador sólo
        // está su nombre completo; se desglosa (aproximado) para prellenar.
        if (! $trabajador && ! CitaPatronal::esEmpleador($c)) {
            $trabajador = CitaPatronal::desglosarNombre($c->solicitante);
        }
        // Si agendó el trabajador, la CURP de la cita es la suya; si fue el
        // empleador, la del trabajador va en citado.
        $curp = Curp::normalizar(CitaPatronal::esEmpleador($c) ? ($trabajador['curp'] ?? null) : $c->curp);

        return array_filter([
            'folio'              => $c->poder_folio ?? null,
            'primero_trabajador' => $trabajador['primer_apellido'] ?? null,
            'segundo_trabajador' => $trabajador['segundo_apellido'] ?? null,
            'trabajador'         => $trabajador['nombre'] ?? null,
            'nacionalidad'       => strlen($curp) === 18 ? (substr($curp, 11, 2) === 'NE' ? 'EXTRANJERA' : 'MEXICANA') : null,
            'trabajador_edad'    => $c->edad,
            'trabajador_sexo'    => in_array($c->sexo, ['H', 'M'], true) ? $c->sexo : null,
            'trabajador_curp'    => strlen($curp) === 18 ? $curp : null,
            // Los municipios del formulario de cita son de Michoacán.
            'estado_rat'         => $c->municipio ? 16 : null,
            'municipio_rat'      => $c->municipio ?: null,
            'sede'               => $c->delegacion,
            'fecha'              => $c->fecha ? $c->fecha->format('Y-m-d') : null,
            'hora'               => $c->hora ? $c->hora->format('H:i') : null,
        ], fn ($v) => $v !== null && $v !== '');
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
