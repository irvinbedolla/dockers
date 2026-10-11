<?php

namespace App\Support;

use App\Models\Recepcion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Datos ya listos para los correos de citas (agendada y reasignada): folio,
 * fecha en palabras, dirección legible, mapa, Google Calendar y qué llevar
 * según el trámite. Así las plantillas sólo pintan.
 */
class CitaCorreo
{
    /** Domicilio de cada oficina (el mismo que usan el acuse y la confirmación). */
    public const DIRECCIONES = [
        'Morelia'         => 'BLVD. GARCÍA DE LEÓN NO. 1575, COL. CHAPULTEPEC ORIENTE, C.P. 58260, MORELIA, MICHOACÁN',
        'Zitácuaro'       => '5 DE MAYO NTE. 3, CENTRO, C.P. 61500, ZITÁCUARO, MICHOACÁN.',
        'Zamora'          => 'JUSTO SIERRA NO. 290, COL. JARDINES DE CATEDRAL, C.P. 59670, ZAMORA DE HIDALGO, MICHOACÁN.',
        'Sahuayo'         => 'AV. UNIVERSIDAD SUR NO. 3000, SEGUNDO PISO, EDIFICIO CENTRAL, COL. LOMAS DE UNIVERSIDAD, C.P. 59103, SAHUAYO DE MORELOS, MICHOACÁN.',
        'Uruapan'         => 'NUEVO PARICUTÍN NO. 308, COL. SAN RAFAEL, C.P. 60136, URUAPAN, MICHOACÁN.',
        'Lázaro Cárdenas' => 'PARACHO NO. 26, COL. 600 CASAS, C.P. 60950, LÁZARO CÁRDENAS, MICHOACÁN.',
    ];

    private const TRAMITES = [
        'Solicitud'    => 'Solicitud de conciliación',
        'Ratificación' => 'Ratificación de convenio',
        'Asesoría'     => 'Asesoría',
        'Cumplimiento' => 'Cumplimiento',
    ];

    private const QUE_LLEVAR = [
        'Solicitud' => [
            '<strong>Identificación oficial vigente</strong> (INE, pasaporte o cédula profesional).',
            'Tu <strong>CURP</strong> y tu domicilio.',
            'Nombre de la persona, empresa o sindicato al que se va a citar.',
            'Su <strong>domicilio completo</strong> para poder notificarle.',
            'El motivo de tu cita: qué pasó y qué solicitas.',
        ],
        'Ratificación' => [
            '<strong>Identificación oficial vigente</strong> (INE, pasaporte o cédula profesional).',
            'Tu <strong>CURP</strong>.',
            'El <strong>convenio o documento del acuerdo</strong> que van a ratificar, si ya lo tienen.',
            'Nombre y domicilio de la empresa o persona con quien hiciste el acuerdo.',
        ],
        'Asesoría' => [
            '<strong>Identificación oficial vigente</strong> (INE, pasaporte o cédula profesional).',
            'Los documentos que tengas de tu trabajo: contrato, recibos de nómina, constancias o mensajes.',
            'Fechas importantes anotadas: cuándo entraste, cuándo saliste y qué pasó.',
        ],
    ];

    public static function datos(Recepcion $cita): array
    {
        $folio     = $cita->folio_cita;
        $tipo      = (string) $cita->tipo;
        $fecha     = Carbon::parse($cita->fecha);
        $hora      = $cita->hora instanceof \DateTimeInterface ? $cita->hora->format('H:i') : substr((string) $cita->hora, 0, 5);
        $direccion = self::DIRECCIONES[$cita->delegacion] ?? '';
        $legible   = $direccion
            ? rtrim(strtr(Str::title(mb_strtolower($direccion)), [' De ' => ' de ', ' Del ' => ' del ', ' La ' => ' la ', 'C.p.' => 'C.P.']), '.')
            : '';
        $modulo    = str_replace('Modulo', 'Módulo', (string) $cita->lugar_auxiliar);
        $inicio    = Carbon::parse($fecha->format('Y-m-d').' '.$hora, 'America/Mexico_City');

        return [
            'nombre'      => Str::title(mb_strtolower(trim((string) $cita->solicitante))),
            'folio'       => $folio,
            'tipo'        => self::TRAMITES[$tipo] ?? $tipo,
            'tipoCorto'   => mb_strtolower(self::TRAMITES[$tipo] ?? $tipo),
            'fechaLarga'  => Str::ucfirst($fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
            'hora'        => $hora,
            'sede'        => $cita->delegacion,
            'modulo'      => $modulo,
            'direccion'   => $legible,
            'mapa'        => $direccion ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode('Centro de Conciliación Laboral '.$direccion) : null,
            'calendario'  => 'https://calendar.google.com/calendar/render?'.http_build_query([
                'action'   => 'TEMPLATE',
                'text'     => 'Cita en el Centro de Conciliación Laboral (folio '.$folio.')',
                'dates'    => $inicio->format('Ymd\THis').'/'.$inicio->copy()->addMinutes(40)->format('Ymd\THis'),
                'ctz'      => 'America/Mexico_City',
                'location' => $legible,
                'details'  => $modulo.'. Lleva tu identificación oficial y tu acuse con código QR. Tolerancia: 5 minutos.',
            ], '', '&', PHP_QUERY_RFC3986),
            'queLlevar'   => self::QUE_LLEVAR[$tipo] ?? self::QUE_LLEVAR['Solicitud'],
            'excepcion'   => $cita->exepcion === 'Si',
            // El mismo enlace que lleva el QR del acuse (lo lee el escáner de recepción).
            'qrUrl'       => $cita->id ? route('citas.confirmar', $cita->id) : null,
        ];
    }
}
