<?php

namespace App\Support;

use App\Models\Audiencias;
use App\Models\Concepto;
use App\Models\HistorialAdministracion;
use App\Models\Pagos;
use App\Models\SeerCitados;
use App\Models\SeerPerConciliador;
use App\Models\SeerPerGeneral;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * "Editar finalización" de una audiencia que terminó en No conciliación:
 * el conciliador puede cambiar el final a "Hubo convenio".
 *
 *   puede()     quién y cuándo (los mismos que ven el botón "Editar
 *               finalización": Conciliador y Enlace el día en que terminó;
 *               Super Usuario cuando sea).
 *   faltantes() lo que falta para que haya convenio (se revisa antes de
 *               guardar nada).
 *   aplicar()   la audiencia y la solicitud pasan a Conciliación, la
 *               manifestación queda como convenio, se quitan las multas que
 *               salieron de la no conciliación y se deja la incidencia en la
 *               bitácora (historial_administracion, tipo TIPO). Regresa los
 *               documentos del convenio (los mismos de Índice de audiencias).
 */
class CambioFinalizacionAudiencia
{
    public const TIPO = 'cambio_finalizacion_audiencia';

    public const CONVENIO = 'Conciliacion';

    public const NO_CONCILIACION = 'No conciliacion';

    public static function puede(User $user, SeerPerGeneral $general, ?Audiencias $audiencia): bool
    {
        if (! $audiencia || $audiencia->estatus !== self::NO_CONCILIACION || (int) $audiencia->id_solicitud !== (int) $general->id) {
            return false;
        }
        if ($user->hasRole('Super Usuario')) {
            return true;
        }

        return $user->hasAnyRole(['Conciliador', 'Enlace'])
            && $general->fecha_terminacion
            && Carbon::today()->isSameDay(Carbon::parse($general->fecha_terminacion));
    }

    /** Qué falta para poder registrar el convenio. */
    public static function faltantes(array $data, int $idSolicitud): array
    {
        $faltan = [];
        if (mb_strlen(trim((string) ($data['motivo_cambio'] ?? ''))) < 10) {
            $faltan[] = 'Escribe el motivo del cambio de finalización (por lo menos 10 caracteres).';
        }
        $hayConceptos = ! empty($data['tipo_pago'])
            || Concepto::where('id_solicitud', $idSolicitud)->where('tipo_pago', 'Audiencia')->exists();
        if (! $hayConceptos) {
            $faltan[] = 'Para registrar el convenio agrega por lo menos un concepto de pago.';
        }
        $hayPagos = ! empty($data['dias_pagos'])
            || Pagos::where('id_solicitud', $idSolicitud)->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])->exists();
        if (! $hayPagos) {
            $faltan[] = 'Para registrar el convenio agrega por lo menos un cumplimiento (fecha de pago).';
        }
        if (! in_array($data['tipo_audiencia'] ?? null, ['Presencial', 'Virtual'], true)) {
            $faltan[] = 'Elige el tipo de audiencia (presencial o virtual).';
        }
        if (trim((string) ($data['direccion_convenio'] ?? '')) === '') {
            $faltan[] = 'Escribe el domicilio laboral que aparecerá en el convenio.';
        }

        return $faltan;
    }

    /** @return array<int, array{nombre: string, url: string}> Los documentos del convenio. */
    public static function aplicar(array $data, SeerPerGeneral $general, Audiencias $audiencia, User $user): array
    {
        DB::transaction(function () use ($data, $general, $audiencia, $user) {
            $manifestacion = SeerPerConciliador::where('id_solicitud', $general->id)
                ->orderByRaw('CASE WHEN audiencia_id = ? THEN 0 ELSE 1 END', [$audiencia->id])
                ->orderByDesc('id')
                ->first();
            $multas = SeerCitados::where('id_solicitud', $general->id)
                ->where('audiencia_id', $audiencia->id)
                ->where('tipo_notificacion', 'Multa')
                ->get();

            $antes = [
                'final_de_la_audiencia' => $manifestacion->conclucion ?? null,
                'estatus_audiencia'     => $audiencia->estatus,
                'estatus_solicitud'     => $general->estatus,
                'multas'                => $multas->count() ? $multas->count().' ('.$multas->map(fn ($m) => trim($m->nombre.' '.$m->primer_apellido.' '.$m->segundo_apellido))->implode(', ').')' : null,
            ];

            $monto = (float) Pagos::where('id_solicitud', $general->id)->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])->sum('monto');
            if ($manifestacion) {
                $manifestacion->update([
                    'conclucion'           => self::CONVENIO,
                    'estatus_conciliacion' => self::CONVENIO,
                    'tipo'                 => $data['tipo_audiencia'],
                    'tipo_audiencia'       => $data['tipo_audiencia'],
                    'monto'                => $monto,
                ]);
            }
            $general->update([
                'estatus' => self::CONVENIO,
                'tipo'    => $data['tipo_audiencia'],
            ]);
            $audiencia->update([
                'estatus'            => self::CONVENIO,
                'pena_convencional'  => $data['pena_convencional'] ?? null,
                'direccion_convenio' => $data['direccion_convenio'] ?? null,
            ]);
            // Las multas salen de la no conciliación; con convenio ya no van (igual que al terminar la audiencia con convenio).
            foreach ($multas as $multa) {
                $multa->delete();
            }

            HistorialAdministracion::create([
                'tipo'          => self::TIPO,
                'tabla'         => 'seer_general',
                'registro_id'   => $general->id,
                'NUE'           => $general->NUE,
                'delegacion'    => $general->delegacion,
                'datos_antes'   => $antes,
                'datos_despues' => [
                    'final_de_la_audiencia' => self::CONVENIO,
                    'estatus_audiencia'     => self::CONVENIO,
                    'estatus_solicitud'     => self::CONVENIO,
                    'multas'                => $multas->count() ? 'Se quitaron' : null,
                    'monto_del_convenio'    => '$'.number_format($monto, 2),
                ],
                'motivo'        => 'Final de la audiencia: No conciliación → Hubo convenio. '.trim((string) $data['motivo_cambio']),
                'user_id'       => $user->id,
                'user_nombre'   => $user->name,
                'ip'            => request()->ip(),
            ]);
        });

        return self::documentos($general->id, $audiencia->id);
    }

    /** Acta de audiencia y convenio, como en el Índice de audiencias. */
    public static function documentos(int $idSolicitud, int $audienciaId): array
    {
        $ptu = Concepto::where('id_solicitud', $idSolicitud)->where('tipo_pago', 'Audiencia')->where('descripcion', 'PTU')->exists();
        $q = '?audiencia_id='.$audienciaId;

        return [
            ['nombre' => 'Acta de Audiencia', 'url' => route('VerPDFAudiencia', $idSolicitud).$q],
            ['nombre' => 'Convenio', 'url' => route($ptu ? 'PDFconvenioPTU_SI_S' : 'PDFconveniosolicitud', $idSolicitud).$q],
        ];
    }
}
