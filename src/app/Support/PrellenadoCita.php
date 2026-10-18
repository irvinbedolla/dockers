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

    public static function ligar(object $solicitud): void
    {
        $p = self::paraVista();
        if (! $p || ! FlujoCita::migrado()) {
            return;
        }

        DB::table('seer_general')->where('id', $solicitud->id)->update(['recepcion_id' => $p['cita_id']]);
        self::descartar();

        // La solicitud ya quedó: la cita termina aquí aunque no piquen "Terminar".
        $cita = Recepcion::find($p['cita_id']);
        if ($cita && in_array($cita->estatus, ['pendiente', 'confirmada'], true)) {
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
