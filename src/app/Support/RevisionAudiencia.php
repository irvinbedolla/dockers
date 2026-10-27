<?php

namespace App\Support;

use App\Models\Poder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * "Revisar audiencia": el resumen del caso (encabezado fijo) y la lista
 * "¿Está lista la audiencia?" con semáforo, armados con lo que ya trae la
 * pantalla (solicitud, solicitantes, citados y la audiencia que se revisa).
 *
 * Cada punto: ['nivel' => 'ok'|'aviso'|'falta'|'info', 'parte' => quién,
 * 'texto' => qué pasa, 'pestana' => id de la pestaña donde se corrige].
 *   falta  impide el acta o casi seguro detiene la audiencia (sin tipo de
 *          identificación, poder vencido, no se logró notificar);
 *   aviso  hay que revisarlo (notificación pendiente, poder sin validar…);
 *   info   dato útil para la sala (traductor, discapacidad, la entrega el
 *          solicitante, observaciones de recepción).
 * Sólo lee; no cambia nada.
 */
class RevisionAudiencia
{
    /** Estatus de notificación que cuentan como hecha. */
    private const NOTIFICADO = ['Finalizado exitosamente', 'Exitosa por Instructivo', 'Notificada en Audiencia', 'Recibe pero no firma', 'Notificada'];

    /** No se logró. */
    private const NO_NOTIFICADO = ['No exitosa se constituye', 'No exitosa no se constituye', 'No notificada'];

    /** Pestañas de la pantalla (los id de los div .tabcontent). */
    public const PESTANAS = [
        'detalles'    => 'Detalles',
        'solicitante' => 'Solicitante',
        'documentos'  => 'Citado(s)',
        'citados'     => 'Documentos',
    ];

    /** Estatus de la solicitud (seer_general) con los que el caso ya terminó. */
    public const CONCLUIDO = ['Conciliacion', 'No conciliacion', 'Archivada', 'Concluida', 'Incumplimiento', 'Incompetencia', 'Desistimiento', 'Reinstalacion'];

    /** Pueden corregir un caso ya concluido desde esta pantalla. */
    public const CORRIGEN_CONCLUIDOS = ['Super Usuario', 'Enlace'];

    /**
     * ¿Se puede guardar, agregar o borrar citados? Sí mientras el caso no haya
     * concluido o la audiencia que se revisa siga pendiente; ya concluido,
     * sólo quien corrige (CORRIGEN_CONCLUIDOS). Antes la condición siempre
     * era verdadera (comparaba con || y no con &&).
     */
    public static function edicionAbierta(object $general, ?object $audiencia, ?User $usuario): bool
    {
        if (! in_array($general->estatus, self::CONCLUIDO, true)) {
            return true;
        }
        if ($audiencia && $audiencia->estatus === 'Pendiente') {
            return true;
        }

        return $usuario !== null && $usuario->hasAnyRole(self::CORRIGEN_CONCLUIDOS);
    }

    public static function armar(object $general, Collection $solicitantes, Collection $citados, ?object $audiencia): array
    {
        $puntos = [];
        $fechaAudiencia = $audiencia?->fecha ? Carbon::parse($audiencia->fecha)->startOfDay() : now()->startOfDay();

        // Solicitante(s)
        foreach ($solicitantes as $s) {
            $nombre = self::nombre($s->nombre ?? '');
            if (trim((string) ($s->identificacion ?? '')) === '') {
                $puntos[] = self::punto('falta', $nombre, 'Falta el tipo de identificación del solicitante (sin él no se puede generar el acta).', 'solicitante');
            } elseif (trim((string) ($s->documentoIdentificacion ?? '')) === '') {
                $puntos[] = self::punto('aviso', $nombre, 'No hay identificación del solicitante cargada.', 'citados');
            } else {
                $puntos[] = self::punto('ok', $nombre, 'Identificación del solicitante registrada ('.$s->identificacion.').', 'citados');
            }
            if (self::si($s->traductor ?? null)) {
                $puntos[] = self::punto('info', $nombre, 'Requiere traductor'.(trim((string) ($s->lenguaje ?? '')) !== '' ? ': '.$s->lenguaje : '').'.', 'solicitante');
            }
            if (self::si($s->discapacidad ?? null)) {
                $puntos[] = self::punto('info', $nombre, 'Tiene discapacidad'.(trim((string) ($s->tipo_discapacidad ?? '')) !== '' ? ': '.$s->tipo_discapacidad : '').'.', 'solicitante');
            }
        }

        // Representante del solicitante (cuando solicita el patrón).
        if ((int) ($general->tipo_solicitud ?? 0) === 2) {
            $poder = $audiencia?->poder_id ? Poder::find($audiencia->poder_id) : null;
            if ($poder) {
                $puntos = array_merge($puntos, self::poder($poder, 'Representante del solicitante', $fechaAudiencia, 'solicitante'));
            } else {
                $puntos[] = self::punto('aviso', 'Representante del solicitante', 'La audiencia no tiene un poder registrado para el solicitante.', 'solicitante');
            }
        }

        // Citados
        $poderes = Poder::whereIn('idAbogado', $citados->pluck('id_abogado')->filter()->unique()->all() ?: [0])->get()->keyBy('idAbogado');
        foreach ($citados as $c) {
            $nombre = self::nombre(trim(($c->nombre ?? '').' '.($c->primer_apellido ?? '').' '.($c->segundo_apellido ?? '')));
            $multa  = ($c->tipo_notificacion ?? null) === 'Multa';
            $quien  = $multa ? $nombre.' · multa' : $nombre;

            if (empty($c->id) || ! is_numeric($c->id)) {
                $puntos[] = self::punto('aviso', $quien, 'Citado agregado en esta edición: guarda para generar su citatorio.', 'documentos');
            } else {
                $puntos[] = self::notificacion($c, $quien);
            }
            if (($c->resulte_responsable ?? null) === 'Si') {
                $puntos[] = self::punto('info', $quien, 'Citado como "quien resulte responsable".', 'documentos');
            }
            if (self::si($c->traductor ?? null)) {
                $puntos[] = self::punto('info', $quien, 'Requiere traductor'.(trim((string) ($c->lenguaje ?? '')) !== '' ? ': '.$c->lenguaje : '').'.', 'documentos');
            }

            if ((int) ($general->tipo_solicitud ?? 0) === 1 && ! $multa) {
                $poder = $c->id_abogado ? $poderes->get($c->id_abogado) : null;
                if ($poder) {
                    $puntos = array_merge($puntos, self::poder($poder, $quien, $fechaAudiencia, 'citados'));
                } else {
                    $puntos[] = self::punto('info', $quien, 'Sin representante registrado: si comparece con uno, registra su poder.', 'citados');
                }
            }
        }

        if ($citados->isEmpty()) {
            $puntos[] = self::punto('falta', 'Citados', 'La solicitud no tiene citados: agrega al menos uno para poder guardar.', 'documentos');
        }

        $llegadas = self::llegadas($audiencia);

        $cuenta = ['falta' => 0, 'aviso' => 0, 'ok' => 0, 'info' => 0];
        foreach ($puntos as $p) {
            $cuenta[$p['nivel']]++;
        }

        // Primero lo que falta, luego avisos, lo que está bien e información.
        $orden = ['falta' => 0, 'aviso' => 1, 'ok' => 2, 'info' => 3];
        usort($puntos, fn ($a, $b) => $orden[$a['nivel']] <=> $orden[$b['nivel']]);

        return [
            'resumen'  => self::resumen($general, $audiencia),
            'puntos'   => $puntos,
            'cuenta'   => $cuenta,
            'lista'    => $cuenta['falta'] === 0 && $cuenta['aviso'] === 0,
            'llegadas' => $llegadas,
        ];
    }

    // ------------------------------------------------------------------

    /**
     * Tarjeta de cada citado (pestaña Citado(s)): etiquetas, cómo va su
     * notificación con el documento que la ampara, su citatorio, su multa y
     * su representante. Indexado por posición, en el mismo orden en que la
     * vista recorre $citados ($loop->index).
     */
    public static function partes(object $general, Collection $citados, Collection $notificaciones): array
    {
        $citados = $citados->values();
        $poderes = Poder::whereIn('idAbogado', $citados->pluck('id_abogado')->filter()->unique()->all() ?: [0])->get()->keyBy('idAbogado');
        $tipo1 = (int) ($general->tipo_solicitud ?? 0) === 1;
        $tarjetas = [];

        foreach ($citados as $i => $c) {
            $nuevo   = empty($c->id) || ! is_numeric($c->id);
            $multa   = ($c->tipo_notificacion ?? null) === 'Multa';
            $resulte = ($c->resulte_responsable ?? 'No') === 'Si';
            $medio   = (string) ($c->notificacion ?? '');

            $etiquetas = [];
            if ($multa) {
                $etiquetas[] = ['Multa', 'multa'];
            } elseif ($medio === 'Trabajador') {
                $etiquetas[] = ['Citado por el solicitante', 'solicitante'];
            } elseif ($medio === 'Exhorto') {
                $etiquetas[] = ['Por exhorto', 'centro'];
            } else {
                $etiquetas[] = ['Citado por el Centro', 'centro'];
            }
            if ($resulte) {
                $etiquetas[] = ['Quien resulte responsable', 'resulte'];
            }
            if ($nuevo) {
                $etiquetas[] = ['Nuevo: falta guardar', 'nuevo'];
            }

            $poder = $tipo1 && ! $multa && $c->id_abogado ? $poderes->get($c->id_abogado) : null;
            $representante = null;
            if ($poder) {
                $vencido = $poder->fechaVigencia && Carbon::parse($poder->fechaVigencia)->lt(today());
                $rep = trim(preg_replace('/\s+/', ' ', ($poder->nombre_representante ?? '').' '.($poder->primer_apellido_representante ?? '').' '.($poder->segundo_apellido_representante ?? '')));
                $representante = [
                    'nombre' => $rep !== '' ? Str::title(mb_strtolower($rep)) : 'Sin nombre',
                    'folio'  => $poder->idAbogado,
                    'texto'  => $vencido ? 'Poder vencido' : ($poder->estatus === 'Validado' ? 'Poder validado' : 'Poder sin validar'),
                    'nivel'  => $vencido ? 'falta' : ($poder->estatus === 'Validado' ? 'ok' : 'aviso'),
                ];
            }

            $tarjetas[$i] = [
                'nombre'        => self::nombre(trim(($c->nombre ?? '').' '.($c->primer_apellido ?? '').' '.($c->segundo_apellido ?? ''))),
                'etiquetas'     => $etiquetas,
                'notificacion'  => $nuevo ? ['texto' => 'Sin citatorio todavía', 'nivel' => 'aviso', 'url' => null] : self::estadoNotificacion($c, $notificaciones->get($c->id)),
                'citatorio'     => $nuevo ? null : self::ruta('pdfCitatorioAudiencia', ['id' => $c->id]),
                'multa'         => $multa && ! $resulte && ! $nuevo ? self::ruta('PDFmulta', ['id' => $c->id, 'id_solicitud' => $general->id]) : null,
                'comparece'     => $tipo1 ? ! empty($c->id_abogado) : ($c->comparecencia ?? null) === 'Si',
                'representante' => $representante,
            ];
        }

        return $tarjetas;
    }

    /** Cómo va la notificación de un citado y el PDF que la ampara (lo mismo que la pestaña Documentos). */
    private static function estadoNotificacion(object $c, ?object $n): array
    {
        if (! $n) {
            return ['texto' => 'Sin notificar', 'nivel' => 'aviso', 'url' => null];
        }
        $p = ['id' => $c->id, 'id_solicitud' => $c->id_solicitud];
        if (($n->notificacion ?? null) === 'Trabajador') {
            return ['texto' => 'Lo entrega el solicitante', 'nivel' => 'info', 'url' => null];
        }

        return match ((string) ($n->estatus ?? '')) {
            'Finalizado exitosamente'     => ['texto' => 'Notificado exitosamente', 'nivel' => 'ok', 'url' => self::ruta('PDFRazonNoticacion', $p)],
            'Recibe pero no firma'        => ['texto' => 'Recibe pero no firma', 'nivel' => 'ok', 'url' => self::ruta('PDFRazonNoticacion', $p)],
            'Exitosa por Instructivo'     => ['texto' => 'Exitosa por instructivo', 'nivel' => 'ok', 'url' => self::ruta('PDFInstructivo', $p)],
            'Notificada en Audiencia'     => ['texto' => 'Notificada en audiencia', 'nivel' => 'ok', 'url' => null],
            'Notificada'                  => ['texto' => 'Notificada', 'nivel' => 'ok', 'url' => null],
            'No notificada'               => ['texto' => 'No notificado', 'nivel' => 'falta', 'url' => self::ruta('PDFInstructivo', $p)],
            'No exitosa se constituye'    => ['texto' => 'No exitosa: se constituye', 'nivel' => 'falta', 'url' => self::ruta('VerPDFNoExitConstituye', $p)],
            'No exitosa no se constituye' => ['texto' => 'No exitosa: no se constituye', 'nivel' => 'falta', 'url' => self::ruta('PDFnotificadoNoexitosaNS', $p)],
            'Exhorto'                     => ['texto' => 'Por exhorto', 'nivel' => 'aviso', 'url' => null],
            default                       => ['texto' => 'Sin notificar', 'nivel' => 'aviso', 'url' => null],
        };
    }

    private static function ruta(string $nombre, array $parametros): ?string
    {
        return Route::has($nombre) ? route($nombre, $parametros) : null;
    }

    private static function resumen(object $general, ?object $audiencia): array
    {
        $conciliador = $audiencia?->id_conciliador ? User::find($audiencia->id_conciliador, ['id', 'name']) : null;

        return [
            'nue'         => $general->NUE ?: 'Sin expediente',
            'tipo'        => (int) ($general->tipo_solicitud ?? 0) === 2 ? 'Solicita el patrón' : 'Solicita el trabajador',
            'estatusCaso' => $general->estatus ?: 'Pendiente',
            'sede'        => $general->delegacion ?? null,
            'audiencia'   => $audiencia ? [
                'folio'       => $audiencia->folio_audiencia ?: ('#'.$audiencia->id),
                'numero'      => $audiencia->numero_audiencia,
                'fecha'       => $audiencia->fecha ? Str::ucfirst(Carbon::parse($audiencia->fecha)->locale('es')->isoFormat('ddd D [de] MMM YYYY')) : '—',
                'hora'        => $audiencia->hora ? Carbon::parse($audiencia->hora)->format('H:i') : '—',
                'sala'        => $audiencia->sala ?: 'Sin sala',
                'estatus'     => $audiencia->estatus ?: 'Pendiente',
                'esHoy'       => $audiencia->fecha && Carbon::parse($audiencia->fecha)->isToday(),
                'conciliador' => $conciliador ? Str::title(mb_strtolower($conciliador->name)) : 'Sin conciliador',
            ] : null,
        ];
    }

    private static function notificacion(object $c, string $quien): array
    {
        $estatus = (string) ($c->estatus ?? '');
        $medio   = (string) ($c->notificacion ?? '');

        if ($medio === 'Trabajador') {
            return self::punto('info', $quien, 'El citatorio lo entrega el solicitante: pide el acuse en la audiencia.', 'citados');
        }
        if ($medio === 'Exhorto' || $estatus === 'Exhorto') {
            return self::punto('aviso', $quien, 'Notificación por exhorto: confirma que ya regresó.', 'citados');
        }
        if (in_array($estatus, self::NOTIFICADO, true)) {
            return self::punto('ok', $quien, 'Notificado ('.mb_strtolower($estatus).').', 'citados');
        }
        if (in_array($estatus, self::NO_NOTIFICADO, true)) {
            return self::punto('falta', $quien, 'No se logró notificar ('.mb_strtolower($estatus).').', 'citados');
        }

        return self::punto('aviso', $quien, 'Notificación pendiente'.($estatus !== '' ? ' ('.mb_strtolower($estatus).')' : '').'.', 'citados');
    }

    /** Estatus, vigencia e identificación de un poder. */
    private static function poder(Poder $poder, string $quien, Carbon $fechaAudiencia, string $pestana): array
    {
        $rep = trim(preg_replace('/\s+/', ' ', ($poder->nombre_representante ?? '').' '.($poder->primer_apellido_representante ?? '').' '.($poder->segundo_apellido_representante ?? '')));
        $rep = $rep !== '' ? Str::title(mb_strtolower($rep)) : 'representante sin nombre';
        $etq = 'Representante '.$rep.' (folio '.$poder->idAbogado.')';
        $puntos = [];

        if ($poder->fechaVigencia && Carbon::parse($poder->fechaVigencia)->lt($fechaAudiencia)) {
            $puntos[] = self::punto('falta', $quien, $etq.': el poder venció el '.Carbon::parse($poder->fechaVigencia)->format('d/m/Y').'.', $pestana);
        } elseif ($poder->estatus !== 'Validado') {
            $puntos[] = self::punto('aviso', $quien, $etq.': el poder está '.mb_strtolower($poder->estatus ?: 'pendiente').' de validar.', $pestana);
        } else {
            $puntos[] = self::punto('ok', $quien, $etq.': poder validado'.($poder->fechaVigencia ? ', vigente al '.Carbon::parse($poder->fechaVigencia)->format('d/m/Y') : ', sin fecha de vigencia').'.', $pestana);
        }
        if (trim((string) $poder->tipo_identificacion) === '') {
            $puntos[] = self::punto('falta', $quien, $etq.': falta el tipo de identificación (sin él no se puede generar el acta).', $pestana);
        }

        return $puntos;
    }

    /** Quién ya llegó a esta audiencia y lo que anotó recepción (Llegadas hoy). */
    private static function llegadas(?object $audiencia): ?array
    {
        if (! $audiencia || ! $audiencia->fecha || ! Carbon::parse($audiencia->fecha)->isToday() || ! LlegadasHoy::migrado()) {
            return null;
        }

        $llegaron = DB::table(LlegadasHoy::TABLA)
            ->where('tipo', 'audiencia')->where('registro_id', $audiencia->id)
            ->orderBy('llegada_at')
            ->get(['nombre', 'parte', 'llegada_at'])
            ->map(fn ($l) => [
                'nombre' => self::nombre((string) $l->nombre),
                'parte'  => Str::ucfirst((string) $l->parte),
                'hora'   => $l->llegada_at ? Carbon::parse($l->llegada_at)->format('H:i') : '',
            ])->all();

        $obs = [];
        if (LlegadasHoy::conObservaciones()) {
            $o = DB::table(LlegadasHoy::TABLA_OBS)->where('tipo', 'audiencia')->where('registro_id', $audiencia->id)->first();
            if ($o) {
                foreach (LlegadasHoy::OBSERVACIONES as $campo => $texto) {
                    if (! empty($o->{$campo})) {
                        $obs[] = $texto;
                    }
                }
                if (trim((string) $o->texto) !== '') {
                    $obs[] = trim((string) $o->texto);
                }
            }
        }

        return ['llegaron' => $llegaron, 'observaciones' => $obs];
    }

    private static function punto(string $nivel, string $parte, string $texto, string $pestana): array
    {
        return ['nivel' => $nivel, 'parte' => $parte, 'texto' => $texto, 'pestana' => $pestana];
    }

    private static function nombre(string $n): string
    {
        $n = trim(preg_replace('/\s+/', ' ', $n));

        return $n !== '' ? Str::title(mb_strtolower($n)) : 'Sin nombre';
    }

    private static function si($v): bool
    {
        return in_array(strtolower(trim((string) $v)), ['si', 'sí', '1', 'true'], true);
    }
}
