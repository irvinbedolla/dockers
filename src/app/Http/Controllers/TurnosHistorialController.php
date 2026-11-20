<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\FolioCita;
use App\Support\ReasignacionCita;
use App\Support\Recepcion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Todos los turnos de la tabla recepcion, no sólo los de hoy.
 *
 * Paginado en el servidor: son miles de registros y la pantalla de "hoy"
 * cargaba todo de golpe para que DataTables lo filtrara en el navegador.
 * Aquí cada filtro viaja en la URL, así que un enlace con ?fecha=... se puede
 * compartir o guardar y abre exactamente la misma vista.
 */
class TurnosHistorialController extends Controller
{
    public const TIPOS    = ['Solicitud', 'Ratificación', 'Asesoría', 'Cumplimiento'];
    public const ESTATUS  = ['pendiente', 'confirmada', 'atendido', 'expirada', 'cancelada'];
    private const POR_PAGINA = 25;

    /**
     * Quién atiende cada trámite: las solicitudes las toma un orientador y
     * las ratificaciones un auxiliar. Con los demás trámites (o sin elegir
     * trámite) el filtro "Atiende" ofrece a los dos.
     */
    public const ROL_POR_TIPO = ['Solicitud' => 'Orientador', 'Ratificación' => 'Auxiliar'];

    public function __invoke(Request $request)
    {
        $sedesPermitidas = Recepcion::sedesVisibles($request->user());
        // Morelia 02 sólo ve ratificaciones y cumplimientos; los demás, todo.
        $tiposPermitidos = Recepcion::tramitesVisibles($request->user()) ?? self::TIPOS;

        $atendientes = self::atendientes($sedesPermitidas);

        $filtros = $request->validate([
            'fecha'   => ['nullable', 'date_format:Y-m-d'],
            'sede'    => ['nullable', Rule::in($sedesPermitidas)],
            'tipo'    => ['nullable', Rule::in($tiposPermitidos)],
            'estatus' => ['nullable', Rule::in(self::ESTATUS)],
            'origen'  => ['nullable', Rule::in(['linea', 'ventanilla'])],
            'q'       => ['nullable', 'string', 'max:100'],
            'atiende' => ['nullable', 'integer', Rule::in($atendientes->pluck('id')->all())],
        ]);

        $sedes = ! empty($filtros['sede']) ? [$filtros['sede']] : $sedesPermitidas;

        // Base con todos los filtros menos el estatus: de aquí salen los
        // conteos de las pastillas, que deben decir cuántos hay de cada uno
        // aunque en ese momento se esté viendo sólo uno.
        $base = DB::table('recepcion as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.auxiliar')
            ->whereIn('r.delegacion', $sedes === [] ? ['__ninguna__'] : $sedes)
            ->when($tiposPermitidos !== self::TIPOS, fn ($q) => $q->whereIn('r.tipo', $tiposPermitidos));

        if (! empty($filtros['fecha'])) {
            $base->where('r.fecha', $filtros['fecha']);
        }
        if (! empty($filtros['tipo'])) {
            $base->where('r.tipo', $filtros['tipo']);
        }
        if (! empty($filtros['origen'])) {
            $base->where('r.origen', $filtros['origen']);
        }
        if (! empty($filtros['atiende'])) {
            $base->where('r.auxiliar', (int) $filtros['atiende']);
        }
        if (! empty($filtros['q'])) {
            $texto = trim($filtros['q']);
            $base->where(function ($q) use ($texto) {
                $q->where('r.solicitante', 'like', '%'.$texto.'%');
                if ($folio = FolioCita::normalizar($texto)) {
                    $q->orWhere('r.codigo', $folio);
                } elseif (preg_match('/^[A-Za-z]{3}-[A-Za-z0-9-]*$/', $texto)) {
                    // Parte del folio, p. ej. "MOR-S26": todas las de ese prefijo.
                    $q->orWhere('r.codigo', 'like', strtoupper($texto).'%');
                }
                if (ctype_digit($texto)) {
                    $q->orWhere('r.consecutivo', (int) $texto);
                }
            });
        }

        $conteos = (clone $base)
            ->select('r.estatus', DB::raw('COUNT(*) as total'))
            ->groupBy('r.estatus')
            ->pluck('total', 'estatus');

        $consulta = clone $base;
        if (! empty($filtros['estatus'])) {
            $consulta->where('r.estatus', $filtros['estatus']);
        }

        // Un día se lee en orden de llegada; varios días, lo más reciente
        // arriba y dentro de cada día por hora.
        if (! empty($filtros['fecha'])) {
            $consulta->orderBy('r.hora');
        } else {
            $consulta->orderByDesc('r.fecha')->orderBy('r.hora');
        }

        $turnos = $consulta
            ->select([
                'r.id', 'r.consecutivo', 'r.codigo', 'r.solicitante', 'r.tipo', 'r.fecha', 'r.hora',
                'r.hora_fin', 'r.estatus', 'r.exepcion', 'r.lugar_auxiliar', 'r.delegacion',
                'r.correo', 'r.telefono', 'r.origen', 'u.name as auxiliar',
                'r.modulo_id', 'r.auxiliar as auxiliar_id',
                // Citas de prueba del flujo nuevo (columna de la fase 0).
                ...(\App\Support\FlujoCita::migrado() ? ['r.es_prueba', 'r.llamado_at'] : []),
                // En sala desde cuándo (aviso de espera larga) y si se atendió aunque llegó tarde.
                ...(\App\Support\FlujoCita::conSeguimiento() ? ['r.confirmada_at'] : []),
                ...(\App\Support\FlujoCita::conTardias() ? ['r.llego_tarde'] : []),
            ])
            ->paginate(self::POR_PAGINA)
            ->withQueryString();

        $hoy   = Carbon::today()->toDateString();
        $fecha = $filtros['fecha'] ?? null;

        // Diálogo "Reasignar módulo": sólo para las citas de esta página que
        // se pueden mover, y sólo si quien ve tiene el permiso.
        $opciones = $request->user()->can('turnos_asignar')
            ? ReasignacionCita::opciones($turnos->getCollection()->map(fn ($t) => (object) [
                'id' => $t->id, 'delegacion' => $t->delegacion, 'fecha' => $t->fecha, 'hora' => $t->hora,
                'tipo' => $t->tipo, 'estatus' => strtolower((string) $t->estatus), 'exepcion' => $t->exepcion,
                'modulo_id' => $t->modulo_id, 'lugar_auxiliar' => $t->lugar_auxiliar, 'auxiliar' => $t->auxiliar_id,
            ]), $hoy)
            : [];

        return view('turnos.todos', [
            'opciones'        => $opciones,
            // "Atenderla de todos modos" para las que expiraron hoy (ValidadorCita::atenderTarde).
            'puedeTarde'      => \App\Support\ValidadorCita::puedeReactivar($request->user()) && \App\Support\FlujoCita::conTardias(),
            'ahora'           => now(),
            'turnos'          => $turnos,
            'filtros'         => $filtros,
            'fecha'           => $fecha,
            'hoy'             => $hoy,
            'diaAnterior'     => Carbon::parse($fecha ?? $hoy)->subDay()->toDateString(),
            'diaSiguiente'    => Carbon::parse($fecha ?? $hoy)->addDay()->toDateString(),
            'sedesPermitidas' => $sedesPermitidas,
            'atendientes'     => $atendientes,
            // Descargar agenda → Persona (App\Support\AgendaTurnos::personas).
            'personasAgenda'  => \App\Support\AgendaTurnos::puedeDescargar($request->user())
                ? \App\Support\AgendaTurnos::personas(\App\Support\Recepcion::sedesVisibles($request->user()))
                : collect(),
            'conteos'         => $conteos,
            'totalSinEstatus' => $conteos->sum(),
        ]);
    }

    /**
     * Orientadores y auxiliares de las sedes que puede ver quien consulta,
     * para el filtro "Atiende". Cada uno lleva su sede y sus roles: la vista
     * deja sólo los que corresponden al trámite y la sede elegidos.
     */
    private static function atendientes(array $sedes)
    {
        $roles = array_values(array_unique(self::ROL_POR_TIPO));

        return User::role($roles)
            ->with('roles:id,name')
            ->whereIn('delegacion', $sedes === [] ? ['__ninguna__'] : $sedes)
            ->orderBy('name')
            ->get(['id', 'name', 'delegacion'])
            ->map(fn ($u) => (object) [
                'id'         => $u->id,
                'name'       => $u->name,
                'delegacion' => $u->delegacion,
                'roles'      => $u->roles->pluck('name')->intersect($roles)->values()->all(),
            ]);
    }
}
