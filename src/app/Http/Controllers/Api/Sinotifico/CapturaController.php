<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use App\Models\Sinotifico\Diligencia;
use App\Services\Sinotifico\RegistroCaptura;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Diligencias que el notificador captura en campo. Llegan como multipart:
 * `captura` (JSON con los datos), `fotos[]` y, si la hay, `firma_imagen`.
 */
class CapturaController extends Controller
{
    public function store(Request $request, RegistroCaptura $registro): JsonResponse
    {
        $captura = json_decode((string) $request->input('captura'), true);
        if (! is_array($captura)) {
            return response()->json(['message' => 'La captura llegó incompleta. Vuelve a intentarlo.'], 422);
        }

        [$diligencia, $nueva] = $registro->registrar(
            $request->user(),
            $captura,
            array_values(array_filter((array) $request->file('fotos', []))),
            $request->file('firma_imagen'),
        );

        return response()->json(['data' => $this->resumen($diligencia)], $nueva ? 201 : 200);
    }

    /** Lo enviado en los últimos 30 días, para la pantalla Sincronizar. */
    public function index(Request $request): JsonResponse
    {
        $diligencias = Diligencia::where('notificador_id', $request->user()->id)
            ->where('capturada_at', '>=', now()->subDays(30))
            ->latest('capturada_at')
            ->limit(100)
            ->get();

        return response()->json(['data' => $diligencias->map(fn ($d) => $this->resumen($d))]);
    }

    private function resumen(Diligencia $d): array
    {
        return [
            'id' => $d->id,
            'uuid' => $d->uuid,
            'estatus' => $d->estatus,
            'resultado' => $d->resultado ?? 'Exhorto',
            'quien_atiende' => $d->quien_atiende,
            'expediente' => DB::table('seer_general')->where('id', $d->solicitud_id)->value('NUE'),
            'citados' => $d->citadoIds(),
            'llegada_at' => $d->llegada_at?->toIso8601String(),
            'capturada_at' => $d->capturada_at?->toIso8601String(),
            'revisada_at' => $d->revisada_at?->toIso8601String(),
            'comentario_revision' => $d->comentario_revision,
        ];
    }
}
