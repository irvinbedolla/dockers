<?php

namespace App\Http\Controllers\Api\Sinotifico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/** La app avisa que está abierta y cuántas capturas tiene sin enviar. */
class PresenciaController extends Controller
{
    public function store(Request $request): Response
    {
        $datos = $request->validate([
            'en_cola' => ['required', 'integer', 'min:0', 'max:999'],
            'version' => ['nullable', 'string', 'max:20'],
        ]);

        $fila = ['visto_at' => now(), 'en_cola' => $datos['en_cola'], 'version' => $datos['version'] ?? null];
        // Sin nada en el teléfono, todo lo capturado ya llegó: cuenta como sincronizado.
        if ((int) $datos['en_cola'] === 0) {
            $fila['sincronizado_at'] = now();
        }

        DB::table('sinotifico_presencia')->updateOrInsert(['user_id' => $request->user()->id], $fila);

        return response()->noContent();
    }
}
