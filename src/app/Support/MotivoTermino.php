<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Por qué terminó la atención de una cita de Solicitud o Asesoría: se pide al
 * dar "Terminar" en Mi módulo (MiModuloController::accion) y se guarda en
 * recepcion.motivo_termino (+ motivo_termino_texto: el comentario, que es
 * obligatorio con "Otra").
 *
 * Cuando la solicitud se guarda desde el prellenado, la cita se cierra sola
 * con 'solicitud' (PrellenadoCita::ligar).
 */
class MotivoTermino
{
    public const OPCIONES = [
        'solicitud'   => 'Se levantó la solicitud',
        'asesoria'    => 'Sólo requería asesoría u orientación',
        'documentos'  => 'Le faltaron documentos o datos',
        'competencia' => 'No es competencia del Centro',
        'duplicada'   => 'Ya tenía una solicitud',
        'desistio'    => 'Decidió no continuar',
        'otra'        => 'Otra',
    ];

    /** Tipos de cita en los que se pide (las ratificaciones se confirman al terminar). */
    public const TIPOS = ['Solicitud', 'Asesoría', 'Asesoria'];

    /** ¿Ya corrió la migración de recepcion.motivo_termino? */
    public static function migrado(): bool
    {
        static $listo = null;

        return $listo ??= Schema::hasColumn('recepcion', 'motivo_termino');
    }

    public static function aplicaTipo(?string $tipo): bool
    {
        return self::migrado() && in_array((string) $tipo, self::TIPOS, true);
    }

    public static function reglas(): array
    {
        return [
            'motivo_termino'       => ['required', Rule::in(array_keys(self::OPCIONES))],
            'motivo_termino_texto' => ['nullable', 'string', 'max:255', 'required_if:motivo_termino,otra'],
        ];
    }

    public static function mensajes(): array
    {
        return [
            'motivo_termino.required'          => 'Elige por qué terminó la atención.',
            'motivo_termino.in'                => 'Elige una de las razones de la lista.',
            'motivo_termino_texto.required_if' => 'Escribe cuál fue la razón.',
            'motivo_termino_texto.max'         => 'El comentario puede tener hasta 255 caracteres.',
        ];
    }

    /** "Le faltaron documentos o datos · no traía INE", "Otra: …", o null si no tiene. */
    public static function etiqueta(?string $clave, ?string $texto = null): ?string
    {
        if (! $clave) {
            return null;
        }
        $texto = trim((string) $texto);
        $base  = self::OPCIONES[$clave] ?? $clave;

        if ($clave === 'otra' && $texto !== '') {
            return 'Otra: '.$texto;
        }

        return $texto !== '' ? $base.' · '.$texto : $base;
    }
}
