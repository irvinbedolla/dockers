<?php

namespace App\Http\Controllers;

use App\Models\Recepcion;
use App\Models\Turnos;
use App\Models\User;
use App\Support\FlujoCita;
use App\Support\FolioCita;
use App\Support\ValidadorCita;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Ratificaciones → "Atender de todos modos": la cita de la ratificación
 * (turnos.recepcion_id) expiró hoy porque la persona llegó tarde (o el módulo
 * la marcó "No se presentó"), pero sí está. Igual que en el escáner y en Todos
 * los turnos (ValidadorCita::atenderTarde): la cita vuelve a "confirmada", a
 * la fila de su módulo después de quienes llegaron a tiempo, y cuenta como
 * llegada para el conciliador. Ya en atención, al dar "Terminar" en Mi módulo
 * la ratificación queda Confirmada y se puede concluir.
 */
class RatificacionTardeController extends Controller
{
    /** Sedes que se ven juntas (las mismas de la lista de Ratificaciones). */
    private const REGIONES = [
        'Morelia'         => ['Morelia', 'Zitácuaro'],
        'Zitácuaro'       => ['Morelia', 'Zitácuaro'],
        'Uruapan'         => ['Uruapan', 'Lázaro Cárdenas'],
        'Lázaro Cárdenas' => ['Uruapan', 'Lázaro Cárdenas'],
        'Zamora'          => ['Zamora', 'Sahuayo'],
        'Sahuayo'         => ['Zamora', 'Sahuayo'],
    ];

    /**
     * ¿Ve el botón? Recepción y Super Usuario (como en el escáner) y quien
     * atiende o concluye ratificaciones.
     */
    public static function puede(?User $usuario): bool
    {
        return $usuario !== null && FlujoCita::conTardias()
            && (ValidadorCita::puedeReactivar($usuario)
                || $usuario->can('ratificaciones_concluir')
                || $usuario->can('ratificaciones_crear'));
    }

    /**
     * De las ratificaciones de la página, cuáles tienen su cita expirada hoy:
     * [turno id => cita]. Vacío si quien ve no puede usar el botón.
     */
    public static function expiradasHoy(Collection $turnos, ?User $usuario): array
    {
        if (! self::puede($usuario)) {
            return [];
        }
        $porCita = $turnos->filter(fn ($t) => ! empty($t->recepcion_id))->pluck('id', 'recepcion_id');
        if ($porCita->isEmpty()) {
            return [];
        }

        $res = [];
        Recepcion::whereIn('id', $porCita->keys()->all())->where('estatus', 'expirada')->get()
            ->filter(fn (Recepcion $c) => ValidadorCita::puedeAtenderTarde($c))
            ->each(function (Recepcion $c) use ($porCita, &$res) {
                $res[$porCita[$c->id]] = $c;
            });

        return $res;
    }

    public function __invoke(Request $request, int $id)
    {
        $usuario = $request->user();
        abort_unless(self::puede($usuario), 403);

        $turno = Turnos::where('tipo', 'Ratificación')->findOrFail($id);
        abort_unless($this->enSuAlcance($usuario, $turno), 403, 'Esta ratificación no es de tu sede.');

        $cita = $turno->recepcion_id ? Recepcion::find($turno->recepcion_id) : null;
        if (! $cita) {
            return back()->withErrors(['tarde' => "La ratificación {$turno->NUE} no tiene una cita ligada: no hay nada que reactivar."]);
        }
        if ($cita->estatus === 'confirmada') {
            return back()->with('success', 'La cita '.FolioCita::mostrar($cita)." de la ratificación {$turno->NUE} ya está en la fila de su módulo.");
        }
        if (! ValidadorCita::atenderTarde($cita, $usuario)) {
            return back()->withErrors(['tarde' => 'Sólo se puede atender de todos modos una cita que expiró hoy (la de '.FolioCita::mostrar($cita).' está '.$cita->estatus.').']);
        }

        Log::info('Ratificación: cita expirada atendida de todos modos', ['turno' => $turno->id, 'cita' => $cita->id, 'por' => $usuario->id]);

        $cita  = $cita->fresh();
        $donde = $cita->lugar_auxiliar ? 'Pasa a '.str_replace('Modulo', 'Módulo', $cita->lugar_auxiliar) : 'Pasa a sala';

        return back()->with('success', 'La cita '.FolioCita::mostrar($cita)." de la ratificación {$turno->NUE} volvió a la fila. {$donde}: va después de quienes llegaron a tiempo."
            .' Al terminar su atención en Mi módulo, la ratificación queda Confirmada para concluirla.');
    }

    private function enSuAlcance(User $usuario, Turnos $turno): bool
    {
        if ($usuario->hasAnyRole(['Super Usuario', 'Administrador', 'Directivo'])) {
            return true;
        }
        // Auxiliares, sólo su sede (como en la lista); los demás, su región.
        $sedes = $usuario->hasAnyRole(['Delegado', 'Enlace', 'Conciliador', ...\App\Support\Recepcion::ROLES])
            ? (self::REGIONES[$usuario->delegacion] ?? [$usuario->delegacion])
            : [$usuario->delegacion];

        return in_array($turno->delegacion, $sedes, true);
    }
}
