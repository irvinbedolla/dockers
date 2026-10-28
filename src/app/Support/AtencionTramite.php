<?php

namespace App\Support;

use App\Mail\AtencionTramiteMail;
use App\Models\Poder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Formulario de atención del trámite de conciliación
 * (/atencion-tramite-conciliacion): lo contesta la parte trabajadora o la
 * empleadora con el folio de la solicitud (NUE), una vez por parte.
 *
 *   - Se puede contestar cuando el caso ya concluyó (convenio o constancia) y
 *     sólo dentro de los DIAS días desde que se levantó la solicitud; después
 *     caduca.
 *   - Al concluir el caso se manda por correo el folio y la liga a las partes
 *     que tienen correo (SeerPerGeneral::booted → enviar()). Lo que no se haya
 *     mandado se puede mandar con `php artisan atencion:enviar`.
 *   - Cada respuesta guarda una copia de la solicitud (sede, quién la capturó,
 *     conciliador, resultado…) para las estadísticas (EstadisticasAtencion).
 */
class AtencionTramite
{
    /** Días desde que se levanta la solicitud para contestar. */
    public const DIAS = 60;

    /** Estatus de la solicitud con los que ya se puede contestar (ya hubo audiencia y resultado). */
    public const CONCLUIDOS = ['Conciliacion', 'Reinstalacion', 'No conciliacion', 'Concluida', 'Incumplimiento'];

    /** Estatus que quieren decir que hubo convenio (para comparar con la pregunta 6). */
    public const CON_CONVENIO = ['Conciliacion', 'Reinstalacion', 'Concluida', 'Incumplimiento'];

    public const PARTES = [
        'trabajadora' => 'Parte trabajadora',
        'empleadora'  => 'Parte empleadora',
    ];

    public const ESCALA = ['buena', 'regular', 'mala'];

    /**
     * Las preguntas, en el orden del formulario. 'etapa' es para las
     * estadísticas; 'comentario' => true lo pide obligatorio si se elige
     * la opción indicada en 'obligatorio_si'.
     */
    public const PREGUNTAS = [
        1 => [
            'etapa'    => 'Recepción',
            'texto'    => '¿Cómo consideras que fue la atención brindada por el personal de Recepción?',
            'opciones' => ['buena' => 'Buena', 'regular' => 'Regular', 'mala' => 'Mala'],
        ],
        2 => [
            'etapa'    => 'Captura',
            'texto'    => 'Al momento de capturar tus datos en el sistema, la atención brindada por la persona Auxiliar del Centro fue:',
            'opciones' => ['buena' => 'Buena', 'regular' => 'Regular', 'mala' => 'Mala'],
        ],
        3 => [
            'etapa'    => 'Derechos',
            'texto'    => '¿Se te informó acerca de tus derechos y obligaciones dentro del Procedimiento de Conciliación Laboral?',
            'opciones' => ['si' => 'Sí', 'no' => 'No', 'no_recuerdo' => 'No recuerdo'],
        ],
        4 => [
            'etapa'    => 'Conciliador',
            'texto'    => 'El comportamiento del Conciliador(a) que atendió tu audiencia fue:',
            'opciones' => ['buena' => 'Bueno', 'regular' => 'Regular', 'mala' => 'Malo'],
        ],
        5 => [
            'etapa'    => 'Imparcialidad',
            'texto'    => 'Durante tu audiencia, el Conciliador(a) se mostró:',
            'opciones' => [
                'citada'      => 'A favor de la parte citada',
                'solicitante' => 'A favor de la parte solicitante',
                'equilibrado' => 'Su trato fue equilibrado y justo con ambas partes',
            ],
        ],
        6 => [
            'etapa'    => 'Resultado',
            'texto'    => '¿Cuál fue el resultado de tu audiencia de conciliación?',
            'opciones' => [
                'convenio'        => 'Convenio de Conciliación',
                'no_conciliacion' => 'Constancia de No Conciliación',
                'agotada'         => 'Constancia de Haber Agotado la Etapa de Conciliación Prejudicial',
            ],
        ],
        7 => [
            'etapa'          => 'Presión al firmar',
            'texto'          => 'Al momento de firmar el Convenio de Conciliación, ¿sentiste algún tipo de presión por parte del Conciliador(a)?',
            'opciones'       => ['si' => 'Sí', 'no' => 'No'],
            'solo_si'        => [6, 'convenio'],
            'obligatorio_si' => 'si',
        ],
    ];

    /** ¿Ya corrió la migración? */
    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= Schema::hasTable('atencion_tramite') && Schema::hasTable('atencion_tramite_envios');
    }

    /**
     * El folio como lo escriba la persona: con o sin diagonales, espacios o
     * minúsculas ("mor sol 2026 4512" → "MOR/SOL/2026/04512").
     */
    public static function normalizarFolio(?string $folio): string
    {
        $f = mb_strtoupper(trim((string) $folio));
        if (preg_match('/^([A-Z]{3})\W*([A-Z]{3})\W*(\d{4})\W*(\d{1,6})$/u', preg_replace('/\s+/', ' ', $f), $m)) {
            return $m[1].'/'.$m[2].'/'.$m[3].'/'.str_pad($m[4], 5, '0', STR_PAD_LEFT);
        }

        return preg_replace('/\s+/', '', $f);
    }

    /** La solicitud de ese folio (la más reciente si el folio se repite). */
    public static function buscar(?string $folio): ?object
    {
        $nue = self::normalizarFolio($folio);
        if ($nue === '') {
            return null;
        }

        return DB::table('seer_general')->where('NUE', $nue)->orderByDesc('id')->first();
    }

    /** Hasta cuándo se puede contestar (fin del día). */
    public static function vence(object $general): ?Carbon
    {
        return $general->fecha ? Carbon::parse($general->fecha)->addDays(self::DIAS)->endOfDay() : null;
    }

    /**
     * ¿Se puede contestar? ['estado' => abierto|caducado|sin_concluir|contestado,
     * 'vence' => Carbon, 'contestadas' => [partes]].
     */
    public static function estado(object $general): array
    {
        $vence = self::vence($general);
        $contestadas = self::migrado()
            ? DB::table('atencion_tramite')->where('id_solicitud', $general->id)->pluck('parte')->all()
            : [];

        $estado = match (true) {
            ! $vence || now()->gt($vence)                                  => 'caducado',
            ! in_array($general->estatus, self::CONCLUIDOS, true)          => 'sin_concluir',
            count(array_intersect(array_keys(self::PARTES), $contestadas)) === count(self::PARTES) => 'contestado',
            default                                                        => 'abierto',
        };

        return ['estado' => $estado, 'vence' => $vence, 'contestadas' => $contestadas];
    }

    /** Lo poco que se muestra de la solicitud en la página pública. */
    public static function resumen(object $general): array
    {
        $vence = self::vence($general);

        return [
            'folio'  => $general->NUE,
            'sede'   => $general->delegacion,
            'fecha'  => $general->fecha ? Str::ucfirst(Carbon::parse($general->fecha)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')) : '—',
            'vence'  => $vence ? $vence->copy()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') : '—',
            'tipo'   => (int) $general->tipo_solicitud === 2 ? 'Solicitud de la parte empleadora' : 'Solicitud de la parte trabajadora',
        ];
    }

    /** Reglas de validación del formulario. */
    public static function reglas(Request $request): array
    {
        $reglas = [
            'parte'              => ['required', 'in:'.implode(',', array_keys(self::PARTES))],
            'acepta_privacidad'  => ['accepted'],
        ];
        foreach (self::PREGUNTAS as $n => $p) {
            $aplica = self::aplica($n, $request->all());
            if (! $aplica) {
                // No aplica (p. ej. la 7 sin convenio): lo que llegue se descarta.
                $reglas['p'.$n] = ['exclude'];
                $reglas['c'.$n] = ['exclude'];
                continue;
            }
            $reglas['p'.$n] = ['required', 'in:'.implode(',', array_keys($p['opciones']))];
            $obligatorio = isset($p['obligatorio_si']) && $request->input('p'.$n) === $p['obligatorio_si'];
            $reglas['c'.$n] = [$obligatorio ? 'required' : 'nullable', 'string', 'max:1000'];
        }

        return $reglas;
    }

    /** ¿La pregunta aplica con estas respuestas? (la 7 sólo si en la 6 hubo convenio) */
    public static function aplica(int $n, array $respuestas): bool
    {
        $p = self::PREGUNTAS[$n];

        return ! isset($p['solo_si']) || ($respuestas['p'.$p['solo_si'][0]] ?? null) === $p['solo_si'][1];
    }

    public static function mensajes(): array
    {
        $m = [
            'parte.required'             => 'Indica si eres la parte trabajadora o la parte empleadora.',
            'acepta_privacidad.accepted' => 'Para enviar el formulario, lee y acepta el Aviso de privacidad.',
            'c7.required'                => 'Cuéntanos qué pasó: tu comentario nos ayuda a atenderlo.',
        ];
        foreach (array_keys(self::PREGUNTAS) as $n) {
            $m['p'.$n.'.required'] = 'Contesta la pregunta '.$n.'.';
            $m['c'.$n.'.max'] = 'El comentario de la pregunta '.$n.' es muy largo (máximo 1,000 caracteres).';
        }

        return $m;
    }

    /** Guarda la respuesta con la copia de la solicitud. */
    public static function guardar(object $general, array $datos, Request $request): void
    {
        $valores = [
            'id_solicitud'     => $general->id,
            'folio'            => $general->NUE,
            'parte'            => $datos['parte'],
            'delegacion'       => $general->delegacion,
            'tipo_solicitud'   => (int) $general->tipo_solicitud ?: null,
            'fecha_solicitud'  => $general->fecha,
            'fecha_conclusion' => $general->fecha_terminacion,
            'resultado'        => $general->estatus,
            'capturo_id'       => $general->user_id ?: null,
            'conciliador_id'   => self::conciliador($general),
            'audiencias'       => DB::table('audiencias')->where('id_solicitud', $general->id)->count(),
            'caso_excepcion'   => ($general->caso_excepcion ?? 'No') === 'Si',
            'ip'               => $request->ip(),
            'user_agent'       => mb_substr((string) $request->userAgent(), 0, 255) ?: null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ];
        foreach (array_keys(self::PREGUNTAS) as $n) {
            $aplica = self::aplica($n, $datos);
            $valores['p'.$n] = $aplica ? ($datos['p'.$n] ?? null) : null;
            $c = $aplica ? trim((string) ($datos['c'.$n] ?? '')) : '';
            $valores['c'.$n] = $c !== '' ? $c : null;
        }
        // Lo que pide atención se marca para seguimiento.
        $valores['seguimiento'] = self::esAlerta((object) $valores) ? 'pendiente' : null;

        DB::table('atencion_tramite')->insert($valores);
    }

    /** ¿Esta respuesta pide atención? Presión al firmar, o algo "malo" con comentario. */
    public static function esAlerta(object $r): bool
    {
        if (($r->p7 ?? null) === 'si') {
            return true;
        }
        foreach ([1, 2, 4] as $n) {
            if (($r->{'p'.$n} ?? null) === 'mala' && trim((string) ($r->{'c'.$n} ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /** Conciliador de la última audiencia (o el de la solicitud). */
    public static function conciliador(object $general): ?int
    {
        $id = DB::table('audiencias')->where('id_solicitud', $general->id)
            ->whereNotNull('id_conciliador')->orderByDesc('fecha')->orderByDesc('id')->value('id_conciliador');

        return (int) ($id ?: $general->conciliador_id) ?: null;
    }

    /** Liga del formulario con el folio (y la parte) ya puestos. */
    public static function liga(string $folio, ?string $parte = null): string
    {
        return route('atencion.formulario', array_filter(['folio' => $folio, 'parte' => $parte]));
    }

    // ------------------------------------------------------------------
    // Correo con el folio y la liga
    // ------------------------------------------------------------------

    /**
     * A quién se le manda: la parte solicitante y, si solicita el trabajador,
     * los representantes (poder) de las empresas citadas. [['parte', 'correo', 'nombre']].
     */
    public static function destinatarios(object $general): array
    {
        $tipo2 = (int) $general->tipo_solicitud === 2;
        $lista = [];
        $agregar = function (?string $correo, string $parte, ?string $nombre) use (&$lista) {
            $correo = mb_strtolower(trim((string) $correo));
            if ($correo === '' || ! filter_var($correo, FILTER_VALIDATE_EMAIL) || isset($lista[$correo])) {
                return;
            }
            $lista[$correo] = ['parte' => $parte, 'correo' => $correo, 'nombre' => $nombre ? Str::title(mb_strtolower(trim($nombre))) : null];
        };

        $solicitantes = DB::table('seer_solicitante')->where('id_solicitud', $general->id)->get(['nombre', 'email', 'poder_id']);
        foreach ($solicitantes as $s) {
            $agregar($s->email, $tipo2 ? 'empleadora' : 'trabajadora', $s->nombre);
        }

        // Representantes de la parte empleadora.
        $poderes = $tipo2
            ? array_filter([$general->poder_id ?? null, ...$solicitantes->pluck('poder_id')->all()])
            : DB::table('seer_citados')->where('id_solicitud', $general->id)->whereNotNull('id_abogado')->pluck('id_abogado')->all();
        if ($poderes) {
            foreach (Poder::whereIn('idAbogado', array_unique($poderes))->get() as $p) {
                $nombre = trim(($p->nombre_representante ?? '').' '.($p->primer_apellido_representante ?? '').' '.($p->segundo_apellido_representante ?? ''));
                $agregar($p->correo_representante ?: $p->email_patronal, 'empleadora', $nombre ?: $p->nombres_patronal);
            }
        }

        return array_values($lista);
    }

    /**
     * Manda el folio y la liga a quien falte. Sólo si el caso ya concluyó y
     * todavía no caduca. Devuelve cuántos correos salieron.
     */
    public static function enviar(int $idSolicitud, bool $simular = false): int
    {
        if (! self::migrado()) {
            return 0;
        }
        $general = DB::table('seer_general')->find($idSolicitud);
        if (! $general || ! $general->NUE || self::estado($general)['estado'] !== 'abierto') {
            return 0;
        }

        $enviados = 0;
        $ya = DB::table('atencion_tramite_envios')->where('id_solicitud', $general->id)->whereNotNull('enviado_at')->pluck('correo')->all();
        $contestadas = DB::table('atencion_tramite')->where('id_solicitud', $general->id)->pluck('parte')->all();
        foreach (self::destinatarios($general) as $d) {
            if (in_array($d['correo'], $ya, true) || in_array($d['parte'], $contestadas, true)) {
                continue;
            }
            if ($simular) {
                $enviados++;
                continue;
            }
            $error = null;
            try {
                Mail::to($d['correo'])->send(new AtencionTramiteMail($general, $d));
                $enviados++;
            } catch (\Throwable $e) {
                $error = mb_substr($e->getMessage(), 0, 1000);
                Log::warning('No se pudo enviar el formulario de atención', ['solicitud' => $general->id, 'correo' => $d['correo'], 'error' => $error]);
            }
            DB::table('atencion_tramite_envios')->updateOrInsert(
                ['id_solicitud' => $general->id, 'correo' => $d['correo']],
                ['parte' => $d['parte'], 'enviado_at' => $error ? null : now(), 'error' => $error, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return $enviados;
    }
}
