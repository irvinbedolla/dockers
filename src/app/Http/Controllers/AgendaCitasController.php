<?php

namespace App\Http\Controllers;

use App\Support\Recepcion;
use App\Support\SemaforoAgenda;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Fuente de la agenda con las citas en línea de recepción.
 *
 * Qué es "en línea": un registro de la tabla recepcion que trae correo. El
 * formulario público lo exige y la captura en ventanilla no lo pide; en los
 * datos la separación es limpia -8,473 turnos de ventanilla sin correo y las
 * citas con correo empiezan el 18 de septiembre de 2026, cuando se abrió el
 * formulario-. Si algún día la ventanilla empieza a pedir correo, esta regla
 * deja de servir y hará falta una columna de origen explícita.
 */
class AgendaCitasController extends Controller
{
    private const TODAS_LAS_SEDES = [
        'Morelia', 'Zitácuaro', 'Uruapan', 'Lázaro Cárdenas', 'Zamora', 'Sahuayo',
    ];

    public function __invoke(Request $request)
    {
        $datos = $request->validate([
            'start' => ['required', 'date'],
            'end'   => ['required', 'date'],
            'sede'  => ['nullable', 'string', 'max:30'],
        ]);

        $usuario = $request->user();

        // Super Usuario y Administrador ven todas; la recepción, las suyas.
        // El sede del filtro se cruza contra eso: escribir otra sede en la
        // URL no amplía lo que uno puede ver.
        $permitidas = $usuario->hasAnyRole(Recepcion::SUPERVISAN)
            ? self::TODAS_LAS_SEDES
            : Recepcion::sedes($usuario);

        $sede = $datos['sede'] ?? null;
        $sedes = ($sede && $sede !== 'Todos')
            ? array_values(array_intersect($permitidas, [$sede]))
            : $permitidas;

        if ($sedes === []) {
            return response()->json([]);
        }

        $desde = Carbon::parse($datos['start'])->toDateString();
        $hasta = Carbon::parse($datos['end'])->toDateString();

        $citas = DB::table('recepcion as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.auxiliar')
            ->whereBetween('r.fecha', [$desde, $hasta])
            ->whereIn('r.delegacion', $sedes)
            ->whereNotNull('r.correo')
            ->where('r.correo', '<>', '')
            ->orderBy('r.fecha')
            ->orderBy('r.hora')
            ->select([
                'r.id', 'r.fecha', 'r.hora', 'r.hora_fin', 'r.tipo', 'r.estatus',
                'r.delegacion', 'r.solicitante', 'r.correo', 'r.telefono',
                'r.lugar_auxiliar', 'r.exepcion', 'r.folio',
                'u.name as atiende',
            ])
            ->get();

        return response()->json($citas->map(function ($c) {
            $hora    = substr((string) $c->hora, 0, 5);
            $atiende = $c->atiende ? trim(preg_replace('/\s+/', ' ', $c->atiende)) : null;

            $evento = [
                'id'    => $c->id,
                'title' => $c->solicitante ?: 'Sin nombre',
                'start' => $c->fecha.'T'.$c->hora,
                'extendedProps' => [
                    'hora'        => $hora,
                    'color'       => SemaforoAgenda::cita($c->estatus),
                    'estatus'     => $c->estatus,
                    'tipo'        => $c->tipo,
                    'delegacion'  => $c->delegacion,
                    'solicitante' => $c->solicitante,
                    'atiende'     => $atiende,
                    'modulo'      => $c->lugar_auxiliar,
                    'excepcion'   => $c->exepcion === 'Si',
                    'folio'       => $c->folio,
                    // Contacto: sólo para el detalle al hacer clic, no para
                    // la tarjeta. Recepción lo usa para confirmar la cita.
                    'correo'      => $c->correo,
                    'telefono'    => $c->telefono,
                    // Lo que pinta la tarjeta en el calendario. Las demás
                    // fuentes muestran solicitante, citado y conciliador; una
                    // cita no tiene citado ni conciliador todavía.
                    'lineas'      => [
                        ['Solicitante', $c->solicitante],
                        ['Trámite', $c->tipo],
                        ['Atiende', $atiende],
                    ],
                ],
            ];

            if ($c->hora_fin) {
                $evento['end'] = $c->fecha.'T'.$c->hora_fin;
            }

            return $evento;
        })->values());
    }
}
