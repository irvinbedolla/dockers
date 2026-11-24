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
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * "Editar finalización" de una audiencia que terminó en No conciliación:
 * el final se puede cambiar a cualquiera de los que hay al concluir
 * (audiencias/parte3): Hubo convenio, Reinstalación, Reagendar o Archivar.
 *
 *   puede()     quién y cuándo: los mismos que ven el botón "Editar
 *               finalización" (Conciliador y Enlace el día en que terminó;
 *               Super Usuario cuando sea), sólo sobre la última audiencia.
 *   faltantes() lo que falta para ese final; se revisa antes de guardar nada.
 *   aplicar()   hace lo mismo que el final normal sobre la audiencia ya
 *               concluida, quita las multas que salieron de la no
 *               conciliación y deja la incidencia en la bitácora
 *               (historial_administracion, tipo TIPO). Regresa los
 *               documentos de ese final (los del Índice de audiencias).
 */
class CambioFinalizacionAudiencia
{
    public const TIPO = 'cambio_finalizacion_audiencia';

    public const NO_CONCILIACION = 'No conciliacion';

    /** valor del select => texto (el mismo orden y nombres que al concluir la audiencia). */
    public const FINALES = [
        'Conciliacion'    => 'Hubo Convenio',
        'No conciliacion' => 'No Hubo Convenio',
        'Reagendar'       => 'No Hubo Convenio (Se desea reagendar)',
        'Reinstalacion'   => 'Reinstalación',
        'Archivar'        => 'Archivar',
    ];

    public const CONVENIOS = ['Conciliacion', 'Reinstalacion'];

    public static function puede(User $user, SeerPerGeneral $general, ?Audiencias $audiencia): bool
    {
        if (! $audiencia || $audiencia->estatus !== self::NO_CONCILIACION || (int) $audiencia->id_solicitud !== (int) $general->id) {
            return false;
        }
        // Sólo la última audiencia de la solicitud (reagendar crea la siguiente a partir de ella).
        if ((int) Audiencias::where('id_solicitud', $general->id)->max('id') !== (int) $audiencia->id) {
            return false;
        }
        if ($user->hasRole('Super Usuario')) {
            return true;
        }

        return $user->hasAnyRole(['Conciliador', 'Enlace'])
            && $general->fecha_terminacion
            && Carbon::today()->isSameDay(Carbon::parse($general->fecha_terminacion));
    }

    /** El final pedido, si es un cambio válido (null = se queda en No conciliación). */
    public static function destino(array $data): ?string
    {
        $final = $data['conclucion'] ?? null;

        return ! empty($data['cambio_final']) && $final !== self::NO_CONCILIACION && array_key_exists($final, self::FINALES) ? $final : null;
    }

    /** Qué falta para poder cambiar al final elegido. */
    public static function faltantes(string $destino, array $data, int $idSolicitud, Audiencias $audiencia, User $user): array
    {
        $faltan = [];
        if (mb_strlen(trim((string) ($data['motivo_cambio'] ?? ''))) < 10) {
            $faltan[] = 'Escribe el motivo del cambio de finalización (por lo menos 10 caracteres).';
        }

        if (in_array($destino, self::CONVENIOS, true)) {
            // El convenio se termina en la vista previa del acta (rutas de Conciliador y Super Usuario).
            if (! $user->hasAnyRole(['Conciliador', 'Super Usuario'])) {
                $faltan[] = 'El convenio o la reinstalación los registra el conciliador: se terminan en la vista previa del acta de audiencia.';
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
        } elseif ($destino === 'Archivar') {
            if (trim((string) ($data['motivo_archivo'] ?? '')) === '') {
                $faltan[] = 'Escribe el motivo del archivo de la audiencia.';
            }
        } elseif ($destino === 'Reagendar') {
            $fecha = (string) ($data['reagenda_fecha'] ?? '');
            $hora = (string) ($data['reagenda_hora'] ?? '');
            $fechaOk = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $p) && checkdate((int) $p[2], (int) $p[3], (int) $p[1]);
            if (! $fechaOk || $fecha <= Carbon::today()->toDateString()) {
                $faltan[] = 'Elige la fecha de la nueva audiencia (a partir de mañana).';
            }
            if (! preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
                $faltan[] = 'Elige la hora de la nueva audiencia.';
            }
            if ($fechaOk && ! $faltan) {
                $conciliador = (int) ($audiencia->id_conciliador ?: $user->id);
                if (! AgendaAudiencias::puedeEmpalmar($user, $conciliador)
                    && ($choque = AgendaAudiencias::choque($conciliador, $fecha, $hora, $audiencia->id))) {
                    $faltan[] = AgendaAudiencias::mensajeChoque($choque);
                }
            }
        }

        return $faltan;
    }

    /**
     * Aplica el cambio. $reagendar recibe el Request de la reagenda y la hace
     * como al concluir (SeerController::reagendar_audiencia_parte3ConCandado).
     *
     * @return array<int, array{nombre: string, url: string}> Los documentos de ese final.
     */
    public static function aplicar(string $destino, array $data, SeerPerGeneral $general, Audiencias $audiencia, User $user, callable $reagendar): array
    {
        $hacer = function () use ($destino, $data, $general, $audiencia, $user, $reagendar) {
            DB::transaction(function () use ($destino, $data, $general, $audiencia, $user, $reagendar) {
                $manifestacion = SeerPerConciliador::where('id_solicitud', $general->id)
                    ->orderByRaw('CASE WHEN audiencia_id = ? THEN 0 ELSE 1 END', [$audiencia->id])
                    ->orderByDesc('id')
                    ->first();
                $multas = SeerCitados::where('id_solicitud', $general->id)
                    ->where('audiencia_id', $audiencia->id)
                    ->where('tipo_notificacion', 'Multa')
                    ->get();

                $antes = [
                    'final_de_la_audiencia' => self::FINALES[self::NO_CONCILIACION],
                    'estatus_audiencia'     => $audiencia->estatus,
                    'estatus_solicitud'     => $general->estatus,
                    'multas'                => $multas->count() ? $multas->count().' ('.$multas->map(fn ($m) => trim($m->nombre.' '.$m->primer_apellido.' '.$m->segundo_apellido))->implode(', ').')' : null,
                    // Respaldo completo de las multas que se quitan (no se muestra en la bitácora).
                    'multas_respaldo'       => $multas->map->getAttributes()->values()->all(),
                ];
                $despues = ['final_de_la_audiencia' => self::FINALES[$destino]];

                if (in_array($destino, self::CONVENIOS, true)) {
                    $monto = (float) Pagos::where('id_solicitud', $general->id)->whereIn('tipo_pago', ['Audiencia', 'Conciliador'])->sum('monto');
                    $manifestacion?->update([
                        'conclucion'           => $destino,
                        'estatus_conciliacion' => $destino,
                        'tipo'                 => $data['tipo_audiencia'],
                        'tipo_audiencia'       => $data['tipo_audiencia'],
                        'monto'                => $monto,
                    ]);
                    $general->update(['estatus' => $destino, 'tipo' => $data['tipo_audiencia']]);
                    $audiencia->update([
                        'estatus'            => $destino,
                        'pena_convencional'  => $data['pena_convencional'] ?? null,
                        'direccion_convenio' => $data['direccion_convenio'] ?? null,
                    ]);
                    $despues += ['estatus_audiencia' => $destino, 'estatus_solicitud' => $destino, 'monto_del_convenio' => '$'.number_format($monto, 2)];
                } elseif ($destino === 'Archivar') {
                    // Como "Archivar" al concluir (guardar_audiencia_archivo_parte3).
                    $manifestacion?->update(['conclucion' => 'Archivada', 'estatus_conciliacion' => 'Archivada en Audiencia']);
                    $general->update(['estatus' => 'Archivada', 'observaciones' => trim((string) $data['motivo_archivo'])]);
                    $audiencia->update(['estatus' => 'Archivada en Audiencia']);
                    $despues += ['estatus_audiencia' => 'Archivada en Audiencia', 'estatus_solicitud' => 'Archivada'];
                } else { // Reagendar
                    // La reagenda es la misma de siempre: esta audiencia queda "No conciliacion reagendada"
                    // y se crea la nueva en Pendiente con sus citados.
                    $reagendar(Request::create('/', 'POST', [
                        'id'            => $general->id,
                        'audiencia_id'  => $audiencia->id,
                        'fecha'         => $data['reagenda_fecha'],
                        'hora'          => strlen($data['reagenda_hora']) === 5 ? $data['reagenda_hora'].':00' : $data['reagenda_hora'],
                        'primera'       => $data['primera'] ?? null,
                        'justificacion' => $data['justificacion'] ?? null,
                        'segunda'       => $data['segunda'] ?? null,
                    ]));
                    $manifestacion?->update(['conclucion' => 'Reagenda', 'estatus_conciliacion' => 'No conciliacion se reagenda']);
                    // Con audiencia pendiente la solicitud vuelve a Confirmado.
                    $general->update(['estatus' => 'Confirmado']);
                    $nueva = Audiencias::where('id_solicitud', $general->id)->orderByDesc('id')->first();
                    $despues += [
                        'estatus_audiencia' => 'No conciliacion reagendada',
                        'estatus_solicitud' => 'Confirmado',
                        'nueva_audiencia'   => $nueva ? Carbon::parse($nueva->fecha)->format('d/m/Y').' '.substr((string) $data['reagenda_hora'], 0, 5) : null,
                    ];
                }

                // Las multas salen de la no conciliación; con otro final ya no van.
                foreach ($multas as $multa) {
                    $multa->delete();
                }
                if ($multas->count()) {
                    $despues['multas'] = 'Se quitaron';
                }

                HistorialAdministracion::create([
                    'tipo'          => self::TIPO,
                    'tabla'         => 'seer_general',
                    'registro_id'   => $general->id,
                    'NUE'           => $general->NUE,
                    'delegacion'    => $general->delegacion,
                    'datos_antes'   => $antes,
                    'datos_despues' => $despues,
                    'motivo'        => 'Final de la audiencia: No conciliación → '.self::FINALES[$destino].'. '.trim((string) $data['motivo_cambio']),
                    'user_id'       => $user->id,
                    'user_nombre'   => $user->name,
                    'ip'            => request()->ip(),
                ]);
            });
        };

        if ($destino === 'Reagendar') {
            // Con el mismo candado de agenda que la reagenda normal.
            $conciliador = (int) ($audiencia->id_conciliador ?: $user->id);
            $sede = User::whereKey($conciliador)->value('delegacion') ?? $audiencia->delegacion;
            AgendaAudiencias::conBloqueo($sede, $hacer);
        } else {
            $hacer();
        }

        return self::documentos($destino, $general->id, $audiencia->id);
    }

    /** Los documentos de cada final, como en el Índice de audiencias. */
    public static function documentos(string $destino, int $idSolicitud, int $audienciaId): array
    {
        $q = '?audiencia_id='.$audienciaId;
        $acta = ['nombre' => 'Acta de Audiencia', 'url' => route('VerPDFAudiencia', $idSolicitud).$q];

        if ($destino === 'Conciliacion') {
            $ptu = Concepto::where('id_solicitud', $idSolicitud)->where('tipo_pago', 'Audiencia')->where('descripcion', 'PTU')->exists();

            return [$acta, ['nombre' => 'Convenio', 'url' => route($ptu ? 'PDFconvenioPTU_SI_S' : 'PDFconveniosolicitud', $idSolicitud).$q]];
        }
        if ($destino === 'Reinstalacion') {
            return [$acta, ['nombre' => 'Convenio de reinstalación', 'url' => route('PDFconvenioreinstalacion', $idSolicitud).$q]];
        }
        if ($destino === 'Archivar') {
            return [['nombre' => 'Acta de Archivo', 'url' => route('PDFfalltaInteres', $idSolicitud)], $acta];
        }

        return [['nombre' => 'Notificación al solicitante', 'url' => route('PDFnotificacion_solicitante', $idSolicitud)]];
    }
}
