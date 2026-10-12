<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Recepcion;
use App\Support\ReasignacionCita;
use App\Support\Recepcion as RecepcionRoles;
use Illuminate\Http\Request;

/**
 * Turnos → Todos los turnos → "Reasignar módulo".
 *
 * Mismas reglas que Administración → Módulos (ReasignacionCita::mover); aquí
 * además sólo se tocan citas de las sedes que la persona puede ver, y se
 * regresa a la misma lista con sus filtros.
 */
class ReasignarTurnoController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        $datos = $request->validate(['modulo_id' => ['required', 'integer', 'exists:modulos,id']]);

        $cita = Recepcion::findOrFail($id);

        abort_unless(in_array($cita->delegacion, RecepcionRoles::sedesVisibles($request->user()), true), 403);

        $resultado = ReasignacionCita::mover($cita, Modulo::findOrFail($datos['modulo_id']), $request->user()->id);

        $respuesta = back();
        foreach (array_filter($resultado) as $tipo => $mensaje) {
            $respuesta->with($tipo, $mensaje);
        }

        return $respuesta;
    }
}
