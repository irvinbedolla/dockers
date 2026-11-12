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

        // Representante del solicitante (cuando solicita el patrón): el poder de
        // la audiencia o, si no tiene, el del solicitante.
        if ((int) ($general->tipo_solicitud ?? 0) === 2) {
            $pid = self::poderSolicitante($general, $solicitantes->first(), $audiencia);
            $poder = $pid ? Poder::find($pid) : null;
            if ($poder) {
                $puntos = array_merge($puntos, self::poder($poder, 'Representante del solicitante', $fechaAudiencia, 'citados'));
            } else {
                $puntos[] = self::punto('aviso', 'Representante del solicitante', 'La audiencia no tiene un poder registrado para el solicitante.', 'citados');
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

    // ------------------------------------------------------------------

    /**
     * Poder que lleva al solicitante: el de la audiencia que se revisa (cada
     * audiencia puede venir con otro representante) o, si no tiene, el que
     * quedó en el solicitante.
     */
    public static function poderSolicitante(object $general, ?object $solicitante, ?object $audiencia): ?int
    {
        if ((int) ($general->tipo_solicitud ?? 0) === 2 && ! empty($audiencia?->poder_id)) {
            return (int) $audiencia->poder_id;
        }

        return ! empty($solicitante?->poder_id) ? (int) $solicitante->poder_id : null;
    }

    /**
     * Representantes del caso para la pestaña Documentos: uno por poder (si el
     * mismo poder lleva a varias partes, se juntan), con sus datos y los PDF
     * que tiene cargados. Solicitante: su poder (ver poderSolicitante);
     * citados: el representante registrado (sólo cuando solicita el
     * trabajador, igual que la lista de revisión).
     */
    public static function representantes(object $general, Collection $solicitantes, Collection $citados, ?object $audiencia): array
    {
        $partes = [];
        foreach ($solicitantes as $s) {
            if ($pid = self::poderSolicitante($general, $s, $audiencia)) {
                $partes[$pid][] = 'Solicitante: '.self::nombre((string) ($s->nombre ?? ''));
            }
        }
        if ((int) ($general->tipo_solicitud ?? 0) === 1) {
            foreach ($citados as $c) {
                if (($c->tipo_notificacion ?? null) !== 'Multa' && ! empty($c->id_abogado) && is_numeric($c->id_abogado)) {
                    $partes[(int) $c->id_abogado][] = 'Citado: '.self::nombre(trim(($c->nombre ?? '').' '.($c->primer_apellido ?? '').' '.($c->segundo_apellido ?? '')));
                }
            }
        }
        if (! $partes) {
            return [];
        }

        $poderes = Poder::whereIn('idAbogado', array_keys($partes))->get()->keyBy('idAbogado');
        $hoy = today();
        $lista = [];
        foreach ($partes as $pid => $quienes) {
            $p = $poderes->get($pid);
            if (! $p) {
                $lista[] = ['folio' => $pid, 'partes' => array_values(array_unique($quienes)), 'falta' => true];
                continue;
            }

            $moral = ($p->tipo ?? '') === 'Moral';
            $conRep = ($p->reprecentante ?? 'Si') !== 'No';
            $otorga = $moral
                ? trim((string) $p->nombres_patronal)
                : trim(preg_replace('/\s+/', ' ', ($p->nombres_patronal ?? '').' '.($p->primer_apellido_patronal ?? '').' '.($p->segundo_apellido_patronal ?? '')));
            $rep = trim(preg_replace('/\s+/', ' ', ($p->nombre_representante ?? '').' '.($p->primer_apellido_representante ?? '').' '.($p->segundo_apellido_representante ?? '')));
            $vencido = $p->fechaVigencia && Carbon::parse($p->fechaVigencia)->lt($hoy);

            $docs = [
                [$moral ? 'Acta constitutiva' : 'Identificación del empleador', $p->ineDocumento],
            ];
            if ($conRep) {
                $docs[] = ['Identificación del representante', $p->representacionDocumento];
                $docs[] = ['Documento que acredita la personería', $p->cedulaDocumento];
            }
            $docs[] = ['Anexo', $p->anexo_documeto === 'Sin anexo' ? null : $p->anexo_documeto];

            $lista[] = [
                'folio'          => $p->idAbogado,
                'partes'         => array_values(array_unique($quienes)),
                'falta'          => false,
                'tipo'           => $moral ? 'Persona moral' : 'Persona física',
                'otorga'         => $otorga !== '' ? $otorga : 'Sin nombre',
                'conRep'         => $conRep,
                'representante'  => $conRep ? ($rep !== '' ? Str::title(mb_strtolower($rep)) : 'Sin nombre') : null,
                'curp'           => $conRep ? ($p->curp_representante ?: null) : ($p->curp_patronal ?: null),
                'correo'         => $conRep ? ($p->correo_representante ?: null) : ($p->email_patronal ?: null),
                'telefono'       => $conRep ? ($p->numero_representante ?: null) : ($p->telefono_patronal ?: null),
                'identificacion' => trim(($p->tipo_identificacion ?? '').' '.($p->num_identificacion ?? '')) ?: null,
                'documento'      => $conRep ? ($p->tipo_documento_representante ?: null) : null,
                'expedicion'     => $p->fechaRegistro ? Carbon::parse($p->fechaRegistro)->format('d/m/Y') : null,
                'vigencia'       => $p->fechaVigencia ? Carbon::parse($p->fechaVigencia)->format('d/m/Y') : 'Sin fecha',
                'descripcion'    => trim((string) $p->descipcion_poder) ?: null,
                'estado'         => $vencido
                    ? ['texto' => 'Poder vencido', 'nivel' => 'falta']
                    : (($p->estatus ?? '') === 'Validado' ? ['texto' => 'Poder validado', 'nivel' => 'ok'] : ['texto' => 'Poder sin validar', 'nivel' => 'aviso']),
                'sinIdentificacion' => trim((string) $p->tipo_identificacion) === '',
                'docs'           => array_map(fn ($d) => [
                    'nombre' => $d[0],
                    'url'    => self::documentoPoder($p->idAbogado, $d[1]),
                ], $docs),
            ];
        }

        return $lista;
    }

    /** Liga firmada a un PDF del poder (la misma que usa Poderes); null si no hay archivo. */
    private static function documentoPoder($idAbogado, $archivo): ?string
    {
        $archivo = trim((string) $archivo);
        if ($archivo === '' || ! function_exists('signedDocRoute') || ! Route::has('documentos.ver')) {
            return null;
        }

        return signedDocRoute('documentos.ver', ['tipo' => 'poder', 'id' => $idAbogado, 'archivo' => $archivo]);
    }

    /** Campo del formulario (sol[i][campo]) => columna de seer_solicitante. */
    private const CAMPOS_SOLICITANTE = [
        'curp_solicitante'             => 'curp',
        'nombre_solicitante'           => 'nombre',
        'sexo_solicitante'             => 'sexo',
        'nacionalidad_solicitante'     => 'nacionalidad',
        'email_solicitante'            => 'email',
        'fecha_nacimiento_solicitante' => 'fecha_nacimiento',
        'edad_solicitante'             => 'edad',
        'telefono1_solicitante'        => 'telefono1',
        'telefono2_solicitante'        => 'telefono2',
        'rfc_solicitante'              => 'rfc',
        'traductor_solicitante'        => 'traductor',
        'lenguaje_solicitante'         => 'lenguaje',
        'discapacidad_solicitante'     => 'discapacidad',
        'disc_solicitante'             => 'tipo_discapacidad',
        'tipo_vialidad'                => 'tipo_vialidad',
        'calle_solicitante'            => 'calle',
        'num_ext_solicitante'          => 'num_ext',
        'num_int_solicitante'          => 'num_int',
        'codigo_postal_solicitante'    => 'codigo_postal',
        'referencia_solicitante'       => 'referencia',
        'colonia_solicitante'          => 'colonia',
        'calle2_solicitante'           => 'calle2',
        'calle3_solicitante'           => 'calle3',
        'municipio_solicitante'        => 'municipio_domicilio',
        'estado_solicitante'           => 'estado_domicilio',
        'nss'                          => 'nss',
        'puesto'                       => 'puesto',
        'pago'                         => 'pago',
        'periodo_pago'                 => 'periodo_pago',
        'fecha_ingreso'                => 'fecha_ingreso',
        'fecha_salida'                 => 'fecha_salida',
        'jornada'                      => 'jornada',
        'horas_semana'                 => 'horas_semana',
        'descripcionSolicitud'         => 'descripcionSolicitud',
        'labora'                       => 'labora',
    ];

    /** Como antes: sólo se guardan si traen valor (no se borran desde aquí). */
    private const SOLO_CON_VALOR = ['telefono2_solicitante', 'nss', 'rfc_solicitante'];

    /**
     * Lo que se actualiza de un solicitante con lo que mandó Revisar audiencia
     * (sol[i][...]). Sólo los campos que vinieron en el formulario: si una
     * sección no los trae, no se tocan (antes se ponían en null).
     */
    public static function datosSolicitante(array $d): array
    {
        $datos = [];
        foreach (self::CAMPOS_SOLICITANTE as $campo => $columna) {
            if (! array_key_exists($campo, $d) || is_array($d[$campo])) {
                continue;
            }
            $valor = $d[$campo] === '' ? null : $d[$campo];
            if ($valor === null && in_array($campo, self::SOLO_CON_VALOR, true)) {
                continue;
            }
            $datos[$columna] = $valor;
        }

        return $datos;
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
