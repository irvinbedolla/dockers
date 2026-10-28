<?php

namespace App\Http\Controllers;

use App\Support\AtencionTramite;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * /atencion-tramite-conciliacion (público, sin sesión): formulario de
 * atención del trámite de conciliación. Se entra con el folio de la
 * solicitud (NUE); ver App\Support\AtencionTramite.
 */
class AtencionTramiteController extends Controller
{
    public function formulario(Request $request)
    {
        $folio = trim((string) $request->query('folio', ''));
        $datos = ['folio' => $folio, 'general' => null, 'estado' => null, 'resumen' => null, 'contestadas' => [], 'parte' => null, 'noEncontrado' => false];

        if (! AtencionTramite::migrado()) {
            return view('atencion.formulario', $datos + ['noDisponible' => true]);
        }

        if ($folio !== '') {
            $general = AtencionTramite::buscar($folio);
            if (! $general) {
                $datos['noEncontrado'] = true;
            } else {
                $estado = AtencionTramite::estado($general);
                $parte = (string) $request->query('parte', '');
                $datos = array_merge($datos, [
                    'folio'       => $general->NUE,
                    'general'     => $general,
                    'estado'      => $estado['estado'],
                    'contestadas' => $estado['contestadas'],
                    'resumen'     => AtencionTramite::resumen($general),
                    'parte'       => array_key_exists($parte, AtencionTramite::PARTES) && ! in_array($parte, $estado['contestadas'], true) ? $parte : null,
                ]);
            }
        }

        return view('atencion.formulario', $datos + ['noDisponible' => false]);
    }

    public function guardar(Request $request)
    {
        abort_unless(AtencionTramite::migrado(), 503);

        $general = AtencionTramite::buscar($request->input('folio'));
        if (! $general) {
            return redirect()->route('atencion.formulario')->with('error', 'No encontramos ese folio. Revisa que esté bien escrito.');
        }
        $volver = route('atencion.formulario', ['folio' => $general->NUE]);
        $estado = AtencionTramite::estado($general);
        if ($estado['estado'] !== 'abierto') {
            return redirect($volver);
        }

        $datos = validator($request->all(), AtencionTramite::reglas($request), AtencionTramite::mensajes())->validate();

        if (in_array($datos['parte'], $estado['contestadas'], true)) {
            return redirect($volver)->with('error', 'La '.mb_strtolower(AtencionTramite::PARTES[$datos['parte']]).' ya contestó el formulario de este folio.');
        }

        try {
            AtencionTramite::guardar($general, $datos, $request);
        } catch (QueryException $e) {
            // Dos envíos al mismo tiempo de la misma parte: el segundo choca con el índice único.
            Log::info('Formulario de atención repetido', ['solicitud' => $general->id, 'parte' => $datos['parte']]);

            return redirect($volver)->with('error', 'Este formulario ya se había enviado. ¡Gracias!');
        }

        return redirect()->route('atencion.gracias', ['folio' => $general->NUE]);
    }

    public function gracias(Request $request)
    {
        return view('atencion.gracias', ['folio' => trim((string) $request->query('folio', ''))]);
    }
}
