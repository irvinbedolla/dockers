<?php

namespace App\Http\Controllers;

use App\Models\Recepcion as Cita;
use App\Support\FolioCita;
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
                'mensaje'   => ctype_digit($codigo) || FolioCita::normalizar($codigo)
                    ? 'No hay una cita con el folio '.(FolioCita::normalizar($codigo) ?? $codigo).' en '.$this->textoSedes($sedes).'.'
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

        // Morelia 02 no recibe solicitudes ni asesorías: no las valida.
        $tramites = Recepcion::tramitesVisibles($request->user());
        if ($tramites !== null && ! in_array($cita->tipo, $tramites, true)) {
            return response()->json([
                'resultado' => 'otra_sede',
                'titulo'    => 'Cita de otra recepción',
                'mensaje'   => "Esta cita es de {$cita->tipo}: pásala a la recepción que la atiende. No se modificó.",
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

        $cita = $cita->fresh();

        return response()->json([
            'resultado' => $resultado,
            'titulo'    => $this->titulo($resultado),
            'mensaje'   => $this->mensaje($resultado, $cita),
            'cita'      => $this->resumen($cita),
            // Expiró hoy pero la persona está aquí: se ofrece atenderla de todos modos.
            'atenderTarde' => ValidadorCita::puedeAtenderTarde($cita)
                ? route('recepcion.escaner.tarde', $cita->id)
                : null,
        ]);
    }

    /**
     * "Atenderla de todos modos": la cita expiró hoy (llegó tarde o el módulo
     * la marcó como no presentada) y la persona sí está. Vuelve a la fila de
     * su módulo, después de quienes llegaron a tiempo.
     */
    public function atenderTarde(Request $request, int $id): JsonResponse
    {
        $cita = Cita::find($id);
        abort_unless($cita && in_array($cita->delegacion, $this->sedes($request), true), 404);

        $tramites = Recepcion::tramitesVisibles($request->user());
        abort_if($tramites !== null && ! in_array($cita->tipo, $tramites, true), 403);

        if (! ValidadorCita::atenderTarde($cita, $request->user())) {
            return response()->json([
                'resultado' => 'error',
                'titulo'    => 'Ya no se puede reactivar',
                'mensaje'   => 'Sólo se reactiva una cita que expiró hoy.',
                'cita'      => $this->resumen($cita->fresh()),
            ]);
        }

        Log::info('Cita expirada atendida de todos modos', ['cita' => $cita->id, 'por' => $request->user()->id]);

        $cita = $cita->fresh();
        $modulo = $cita->lugar_auxiliar ? "Pasa a {$cita->lugar_auxiliar}" : 'Pasa a sala';

        return response()->json([
            'resultado' => 'confirmada_tarde',
            'titulo'    => 'Se atenderá aunque llegó tarde',
            'mensaje'   => $modulo.'. Va después de quienes llegaron a tiempo.',
            'cita'      => $this->resumen($cita),
        ]);
    }

    /** QR (URL con /citas/{id}/confirmar) o folio escrito a mano. */
    private function buscar(string $codigo, array $sedes): ?Cita
    {
        if (preg_match('#/citas/(\d+)/confirmar#', $codigo, $m)) {
            return Cita::find((int) $m[1]);
        }

        // Folio nuevo: MOR-S26-0142 (también sin guiones o en minúsculas).
        if ($folio = FolioCita::normalizar($codigo)) {
            return Cita::where('codigo', $folio)->first();
        }

        if (ctype_digit($codigo)) {
            // Folio anterior al cambio: se repite entre sedes y con los años: se busca en las
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
            'folio'       => $cita->folio_cita,
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
