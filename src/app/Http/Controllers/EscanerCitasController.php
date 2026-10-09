<?php

namespace App\Http\Controllers;

use App\Models\Recepcion as Cita;
use App\Support\Recepcion;
use App\Support\ValidadorCita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Escáner de QR de citas para recepción y Super Usuario.
 *
 * El QR del acuse trae la URL /citas/{id}/confirmar. La cámara del navegador
 * lo lee (o un lector USB lo "teclea", o se escribe el folio a mano) y aquí se
 * aplica la misma regla que al abrir esa URL: App\Support\ValidadorCita.
 *
 * La recepción sólo puede validar citas de sus sedes; si le llega una de otra
 * sede se le avisa y la cita no se toca.
 */
class EscanerCitasController extends Controller
{
    public function index(Request $request)
    {
        return view('recepcion.escaner', [
            'sedes'      => $this->sedes($request),
            'tolerancia' => ValidadorCita::TOLERANCIA_MINUTOS,
        ]);
    }

    public function validar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:500'],
        ]);

        $codigo = trim($datos['codigo']);
        $sedes  = $this->sedes($request);

        $cita = $this->buscar($codigo, $sedes);

        if (! $cita) {
            return response()->json([
                'resultado' => 'no_encontrada',
                'titulo'    => 'No se encontró la cita',
                'mensaje'   => ctype_digit($codigo)
                    ? "No hay una cita con el folio {$codigo} en ".$this->textoSedes($sedes).'.'
                    : 'El código no es un QR de cita de SICONCILIO.',
            ]);
        }

        if (! in_array($cita->delegacion, $sedes, true)) {
            return response()->json([
                'resultado' => 'otra_sede',
                'titulo'    => 'Cita de otra sede',
                'mensaje'   => "Esta cita es para la sede {$cita->delegacion}. No se modificó.",
                'cita'      => $this->resumen($cita),
            ]);
        }

        $antes = $cita->estatus;
        ['resultado' => $resultado, 'cambio' => $cambio] = ValidadorCita::validar($cita);

        if ($cambio) {
            Log::info('Cita validada en recepción', [
                'cita' => $cita->id, 'de' => $antes, 'a' => $cita->estatus,
                'por' => $request->user()->id, 'via' => Str::contains($codigo, '/') ? 'qr' : 'folio',
            ]);
        }

        return response()->json([
            'resultado' => $resultado,
            'titulo'    => $this->titulo($resultado),
            'mensaje'   => $this->mensaje($resultado, $cita),
            'cita'      => $this->resumen($cita->fresh()),
        ]);
    }

    /** QR (URL con /citas/{id}/confirmar) o folio escrito a mano. */
    private function buscar(string $codigo, array $sedes): ?Cita
    {
        if (preg_match('#/citas/(\d+)/confirmar#', $codigo, $m)) {
            return Cita::find((int) $m[1]);
        }

        if (ctype_digit($codigo)) {
            // El folio se repite entre sedes y con los años: se busca en las
            // sedes de quien escanea, primero la de hoy y si no la más reciente.
            return Cita::where('consecutivo', (int) $codigo)
                ->whereIn('delegacion', $sedes)
                ->orderByRaw('fecha = CURDATE() DESC')
                ->orderByDesc('fecha')
                ->first();
        }

        return null;
    }

    private function sedes(Request $request): array
    {
        $usuario = $request->user();

        return $usuario->hasAnyRole(Recepcion::SUPERVISAN)
            ? Recepcion::TODAS_LAS_SEDES
            : Recepcion::sedes($usuario);
    }

    private function textoSedes(array $sedes): string
    {
        return count($sedes) > 2 ? 'tus sedes' : implode(' ni ', $sedes);
    }

    private function titulo(string $resultado): string
    {
        return [
            'confirmada'    => 'Asistencia confirmada',
            'ya_confirmada' => 'Ya estaba confirmada',
            'atendida'      => 'Esta cita ya fue atendida',
            'tarde'         => 'Llegó tarde: cita expirada',
            'expirada'      => 'Cita expirada',
            'otro_dia'      => 'Su cita es otro día',
            'pasada'        => 'Cita de un día anterior',
        ][$resultado] ?? 'Resultado';
    }

    private function mensaje(string $resultado, Cita $cita): string
    {
        $hora = $cita->hora->format('H:i');
        $dia  = $cita->fecha->locale('es')->isoFormat('dddd D [de] MMMM');
        $modulo = $cita->lugar_auxiliar ? "Pasa a {$cita->lugar_auxiliar}." : '';

        return match ($resultado) {
            'confirmada'    => trim("Cita de las {$hora}. {$modulo}"),
            'ya_confirmada' => trim("Se confirmó antes. {$modulo}"),
            'atendida'      => 'No hace falta volver a confirmarla.',
            'tarde'         => "Su cita era a las {$hora} y la tolerancia es de ".ValidadorCita::TOLERANCIA_MINUTOS.' minutos.',
            'expirada'      => "Era el {$dia} a las {$hora}.",
            'otro_dia'      => "Está agendada para el {$dia} a las {$hora}. No se modificó.",
            'pasada'        => "Era el {$dia}. No se modificó.",
            default         => '',
        };
    }

    private function resumen(Cita $cita): array
    {
        $atiende = $cita->auxiliar
            ? optional(\App\Models\User::find($cita->auxiliar, ['name']))->name
            : null;

        return [
            'folio'       => str_pad($cita->consecutivo, 5, '0', STR_PAD_LEFT),
            'solicitante' => $cita->solicitante,
            'tipo'        => $cita->tipo,
            'fecha'       => Str::ucfirst($cita->fecha->locale('es')->isoFormat('ddd D MMM YYYY')),
            'hora'        => $cita->hora->format('H:i'),
            'modulo'      => $cita->lugar_auxiliar,
            'atiende'     => $atiende ? Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim($atiende)))) : null,
            'sede'        => $cita->delegacion,
            'origen'      => $cita->origen,
            'estatus'     => $cita->estatus,
            'excepcion'   => $cita->exepcion === 'Si',
            'acuse'       => route('citas.documento', $cita->id),
        ];
    }
}
