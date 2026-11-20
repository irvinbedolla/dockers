<?php

namespace App\Http\Controllers;

use App\Models\ComparecenciaSinFacultades;
use App\Models\SeerCitados;
use App\Models\SeerPerGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Formato "Comparecencia sin Acreditación de Facultades".
 *
 *   guardar()  lo captura (o lo vuelve a editar) el conciliador desde la
 *              audiencia iniciada; uno por citado.
 *   pdf()      /pdfsinPoder/{solicitud}[?citado=ID]: el PDF con lo guardado.
 *              Sin ?citado sale el último que se editó; si no hay nada
 *              guardado, sale el formato con los huecos como antes.
 */
class ComparecenciaSinFacultadesController extends Controller
{
    public function guardar(Request $request)
    {
        abort_unless($request->user()->can('audiencia_comparece_sin_facultades'), 403);

        $datos = $request->validate([
            'solicitud_id'          => ['required', 'integer', 'exists:seer_general,id'],
            'citado_id'             => ['required', 'integer', Rule::exists('seer_citados', 'id')->where('id_solicitud', $request->input('solicitud_id'))],
            'audiencia_id'          => ['nullable', 'integer'],
            'fecha_acta'            => ['required', 'date_format:Y-m-d'],
            'hora_acta'             => ['required', 'date_format:H:i'],
            'compareciente'         => ['required', 'string', 'max:200'],
            'tipo_identificacion'   => ['required', Rule::in(ComparecenciaSinFacultades::IDENTIFICACIONES)],
            'num_identificacion'    => ['required', 'string', 'max:50'],
            'representa'            => ['required', 'string', 'max:255'],
            'documento'             => ['required', Rule::in(array_keys(ComparecenciaSinFacultades::DOCUMENTOS))],
            'documento_descripcion' => ['nullable', 'required_if:documento,insuficiente', 'string', 'max:1000'],
            'fecha_notificacion'    => ['nullable', 'date_format:Y-m-d'],
            'hora_notificacion'     => ['nullable', 'date_format:H:i'],
        ], [
            'compareciente.required'             => 'Escribe el nombre de quien comparece.',
            'num_identificacion.required'        => 'Escribe el número de la identificación.',
            'representa.required'                => 'Escribe a qué parte citada dice representar.',
            'documento_descripcion.required_if'  => 'Describe el documento que exhibió.',
        ]);

        foreach (['compareciente', 'num_identificacion', 'representa'] as $campo) {
            $datos[$campo] = mb_strtoupper(trim($datos[$campo]));
        }
        if ($datos['documento'] === 'ninguno') {
            $datos['documento_descripcion'] = null;
        }

        $formato = ComparecenciaSinFacultades::updateOrCreate(
            ['solicitud_id' => $datos['solicitud_id'], 'citado_id' => $datos['citado_id']],
            $datos + ['user_id' => $request->user()->id]
        );

        return response()->json([
            'ok'          => true,
            'actualizado' => $formato->updated_at->format('d/m/Y H:i'),
            'pdf'         => route('PDFcompareceSP', $formato->solicitud_id).'?citado='.$formato->citado_id,
        ]);
    }

    public function pdf(Request $request, $id)
    {
        $solicitud = SeerPerGeneral::findOrFail($id);
        $conciliador = DB::table('users')->where('id', $solicitud->conciliador_id)->select('name')->first();
        $solicitante = DB::table('seer_solicitante')->where('id_solicitud', $solicitud->id)->first();

        $guardados = ComparecenciaSinFacultades::where('solicitud_id', $solicitud->id);
        $formato = $request->filled('citado')
            ? (clone $guardados)->where('citado_id', (int) $request->input('citado'))->first()
            : (clone $guardados)->latest('updated_at')->first();

        $citadoId = $formato->citado_id ?? ($request->filled('citado') ? (int) $request->input('citado') : null);
        $citado = $citadoId
            ? SeerCitados::where('id_solicitud', $solicitud->id)->where('id', $citadoId)->first()
            : null;
        $citado ??= SeerCitados::where('id_solicitud', $solicitud->id)->first();

        $html = view('PDF/Solicitudes/compareceSinPoder', compact('id', 'solicitud', 'conciliador', 'solicitante', 'citado', 'formato'))->render();
        $pdf = \PDF::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->stream('compareceSinPoder.pdf');
    }
}
