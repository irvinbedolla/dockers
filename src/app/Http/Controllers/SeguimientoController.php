<?php

namespace App\Http\Controllers;

use App\Support\AgendaContexto;
use App\Support\CasosExcepcion;
use App\Support\SeguimientoCaso;
use App\Support\VistaSolicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Seguimiento del caso (App\Support\SeguimientoCaso):
 *
 *   /seguimiento                 todos los casos de sus sedes en una tabla,
 *                                del más reciente al más antiguo, con filtros;
 *   /seguimiento/{s123|c45}      la línea de tiempo de un caso, con sus
 *                                documentos en cada paso;
 *   /seguimiento/solicitud/{id}  la solicitud en modo consulta (verSolicitud: "solicitud"
 *                                choca con Controller::solicitud()) (se abre en un
 *                                modal; sólo HTML, sin layout).
 *
 * Super Usuario y Directivo ven todo; Conciliación, Género e Igualdad,
 * todas las sedes pero sólo casos de excepción; los demás, las sedes de su
 * agenda (SeguimientoCaso::sedes / soloExcepcion).
 */
class SeguimientoController extends Controller
{
    public const ESTADOS = [
        ''          => 'Todos',
        'tramite'   => 'En trámite',
        'resueltos' => 'Resueltos',
        'citas'     => 'Citas sin solicitud',
        'excepcion' => 'Casos de excepción',
    ];

    /** A qué se refiere el filtro de fecha (SeguimientoCaso::tabla). */
    public const FECHAS = [
        'registro'  => 'Registro del caso',
        'cita'      => 'Día de la cita',
        'audiencia' => 'Día de audiencia',
    ];

    public function index(Request $request)
    {
        $usuario  = $request->user();
        $solo     = SeguimientoCaso::soloExcepcion($usuario);
        $visibles = $usuario->hasAnyRole(SeguimientoCaso::VEN_TODO) || CasosExcepcion::es($usuario)
            ? CasosExcepcion::SEDES
            : (AgendaContexto::para($usuario)['sedes'] ?? []);
        $fecha    = (string) $request->query('fecha', '');
        $fecha    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) && checkdate((int) substr($fecha, 5, 2), (int) substr($fecha, 8, 2), (int) substr($fecha, 0, 4)) ? $fecha : '';
        $filtros  = [
            'q'      => trim((string) $request->query('q', '')),
            'sede'   => in_array($request->query('sede'), $visibles, true) ? $request->query('sede') : '',
            'estado' => array_key_exists((string) $request->query('estado', ''), self::ESTADOS) ? (string) $request->query('estado', '') : '',
            'fecha'  => $fecha,
            // Sólo cuenta con fecha; sin ella no ensucia "Limpiar".
            'en'     => $fecha !== '' && array_key_exists((string) $request->query('en'), self::FECHAS) ? (string) $request->query('en') : ($fecha !== '' ? 'registro' : ''),
        ];

        return view('seguimiento.index', [
            'casos'   => SeguimientoCaso::tabla(SeguimientoCaso::sedes($usuario), $filtros, 25, $solo),
            'filtros' => $filtros,
            'sedes'   => $visibles,
            'estados' => $solo ? array_diff_key(self::ESTADOS, ['excepcion' => 1]) : self::ESTADOS,
            'fechas'  => self::FECHAS,
            'soloExcepcion' => $solo,
        ]);
    }

    public function ver(Request $request, string $clave)
    {
        $caso = SeguimientoCaso::linea($clave, SeguimientoCaso::sedes($request->user()), SeguimientoCaso::soloExcepcion($request->user()));
        abort_unless($caso, 404, 'Ese caso no existe o no es de tus sedes.');

        return view('seguimiento.ver', ['caso' => $caso]);
    }

    public function verSolicitud(Request $request, int $id)
    {
        $datos = VistaSolicitud::datos($id);
        $sedes = SeguimientoCaso::sedes($request->user());
        abort_unless($datos && (in_array($datos['general']->delegacion, $sedes, true) || in_array(Str::ascii((string) $datos['general']->delegacion), $sedes, true)), 404);
        abort_if(SeguimientoCaso::soloExcepcion($request->user()) && ! CasosExcepcion::solicitudEsCaso($datos['general']), 404);

        return view('solicitudes._vista', $datos);
    }
}
