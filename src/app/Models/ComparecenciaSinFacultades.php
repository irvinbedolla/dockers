<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Formato "Comparecencia sin Acreditación de Facultades" capturado por el
 * conciliador: uno por citado de la solicitud, editable las veces que haga falta.
 */
class ComparecenciaSinFacultades extends Model
{
    protected $table = 'comparecencias_sin_facultades';

    protected $fillable = [
        'solicitud_id', 'citado_id', 'audiencia_id', 'fecha_acta', 'hora_acta', 'compareciente',
        'tipo_identificacion', 'num_identificacion', 'representa', 'documento', 'documento_descripcion',
        'fecha_notificacion', 'hora_notificacion', 'user_id',
    ];

    protected $casts = [
        'fecha_acta'         => 'date',
        'fecha_notificacion' => 'date',
    ];

    public const DOCUMENTOS = [
        'ninguno'      => 'No exhibe documento alguno',
        'insuficiente' => 'Exhibe documento insuficiente',
    ];

    public const IDENTIFICACIONES = [
        'Credencial de elector', 'Pasaporte', 'Cédula profesional', 'Licencia de conducir', 'Credencial de inapam',
        'Cartilla militar', 'Documento migratorio', 'Constancia de identidad', 'Otro',
    ];

    /**
     * Para el formulario de la audiencia: por cada citado de la solicitud, lo
     * guardado o, si todavía no hay nada, lo que se puede proponer.
     */
    public static function paraCitados(int $solicitudId): array
    {
        $guardados = static::where('solicitud_id', $solicitudId)->get()->keyBy('citado_id');
        $citados = DB::table('seer_citados')->where('id_solicitud', $solicitudId)
            ->get(['id', 'nombre', 'primer_apellido', 'segundo_apellido', 'fecha']);

        $formatos = [];
        foreach ($citados as $c) {
            $g = $guardados->get($c->id);
            $notificado = $c->fecha ? Carbon::parse($c->fecha) : null;
            $formatos[$c->id] = $g ? [
                'guardado'              => true,
                'actualizado'           => $g->updated_at?->format('d/m/Y H:i'),
                'fecha_acta'            => $g->fecha_acta?->format('Y-m-d'),
                'hora_acta'             => substr((string) $g->hora_acta, 0, 5),
                'compareciente'         => $g->compareciente,
                'tipo_identificacion'   => $g->tipo_identificacion,
                'num_identificacion'    => $g->num_identificacion,
                'representa'            => $g->representa,
                'documento'             => $g->documento,
                'documento_descripcion' => $g->documento_descripcion,
                'fecha_notificacion'    => $g->fecha_notificacion?->format('Y-m-d'),
                'hora_notificacion'     => $g->hora_notificacion ? substr((string) $g->hora_notificacion, 0, 5) : '',
            ] : [
                'guardado'              => false,
                'actualizado'           => null,
                'fecha_acta'            => now()->toDateString(),
                'hora_acta'             => now()->format('H:i'),
                'compareciente'         => '',
                'tipo_identificacion'   => 'Credencial de elector',
                'num_identificacion'    => '',
                'representa'            => trim(implode(' ', array_filter([$c->nombre, $c->primer_apellido, $c->segundo_apellido]))),
                'documento'             => 'ninguno',
                'documento_descripcion' => '',
                'fecha_notificacion'    => $notificado?->toDateString(),
                'hora_notificacion'     => $notificado && $notificado->format('H:i') !== '00:00' ? $notificado->format('H:i') : '',
            ];
        }

        return $formatos;
    }
}
