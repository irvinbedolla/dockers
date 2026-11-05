<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Recepcion;
use App\Models\User;
use App\Support\AsignadorModulos;
use App\Support\Especialidades;
use App\Support\LimiteCitas;
use App\Support\ReasignacionCita;
use App\Support\Recepcion as RecepcionRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Administración → Módulos. Sólo Super Usuario.
 *
 * Dos cosas en una pantalla, por sede:
 *   1. El catálogo: qué módulos hay, quién atiende cada uno y qué trámites
 *      recibe. De aquí lee la asignación automática (AsignadorModulos).
 *   2. Las citas de un día repartidas por módulo, en línea y de ventanilla,
 *      con los datos de quien la hizo y la opción de moverla a otro módulo.
 */
class ModulosController extends Controller
{
    /** Roles que pueden atender un módulo. */
    private const ROLES_PERSONAL = [
        'Auxiliar', 'Orientador', 'Orientadores', 'Cumplimientos', 'Turnos', 'Enlace', 'Delegado', 'Excepcion',
        RecepcionRoles::MORELIA_01, RecepcionRoles::MORELIA_02, RecepcionRoles::REGIONAL,
    ];

    /** Estatus en los que una cita todavía se puede mover. */
    private const MOVIBLES = ReasignacionCita::MOVIBLES;

    public function index(Request $request)
    {
        $datos = $request->validate([
            'sede'   => ['nullable', Rule::in(RecepcionRoles::TODAS_LAS_SEDES)],
            'fecha'  => ['nullable', 'date_format:Y-m-d'],
            'origen' => ['nullable', Rule::in(['linea', 'ventanilla'])],
        ]);

        $usuario = $request->user();
        $sede    = $datos['sede']
            ?? (in_array($usuario->delegacion, RecepcionRoles::TODAS_LAS_SEDES, true) ? $usuario->delegacion : 'Morelia');
        $hoy     = Carbon::today()->toDateString();
        $fecha   = $datos['fecha'] ?? $hoy;
        $origen  = $datos['origen'] ?? null;

        $modulos  = AsignadorModulos::deSede($sede);
        $porNombre = $modulos->keyBy('nombre');
        $enLineaMigrado = Modulo::enLineaMigrado();
        if ($enLineaMigrado) {
            $modulos->load('cambioEnLineaPor:id,name');
        }

        // Todas las del día, sin filtro de origen: con ellas se calcula qué
        // módulo está ocupado a qué hora, aunque en pantalla se filtre.
        $delDia = DB::table('recepcion as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.auxiliar')
            ->leftJoin('municipios as mu', 'mu.id', '=', 'r.municipio')
            ->where('r.delegacion', $sede)
            ->where('r.fecha', $fecha)
            ->orderBy('r.hora')
            ->get([
                'r.id', 'r.consecutivo', 'r.codigo', 'r.solicitante', 'r.tipo', 'r.fecha', 'r.hora', 'r.hora_fin',
                'r.estatus', 'r.exepcion', 'r.lugar_auxiliar', 'r.modulo_id', 'r.auxiliar', 'r.origen',
                'r.correo', 'r.telefono', 'r.edad', 'r.sexo', 'r.vulnerables', 'r.tipo_caso',
                'r.conflicto', 'r.observaciones', 'r.created_at', 'mu.nombre as municipio',
                'u.name as atiende',
            ])
            ->map(function ($c) use ($porNombre) {
                // Las citas viejas no tienen modulo_id: se reconocen por nombre.
                $c->modulo_clave = $c->modulo_id ?? optional($porNombre->get($c->lugar_auxiliar))->id;

                return $c;
            });

        $citas = $origen ? $delDia->where('origen', $origen)->values() : $delDia;

        $porModulo = $citas->groupBy(fn ($c) => $c->modulo_clave ?? 'sin');

        // Para el diálogo de reasignar: a cada cita movible, cómo está cada
        // módulo a su hora. El servidor lo vuelve a validar al guardar.
        $opciones = ReasignacionCita::opcionesDelDia($modulos, $delDia, $citas, $hoy);

        $personal = User::whereHas('roles', fn ($q) => $q->whereIn('name', self::ROLES_PERSONAL))
            ->where('estatus', 'Activo')
            ->orderBy('name')
            ->get(['id', 'name', 'delegacion'])
            ->sortBy(fn ($u) => $u->delegacion === $sede ? 0 : 1, SORT_REGULAR, false)
            ->groupBy('delegacion');

        return view('administracion.modulos.index', [
            'sede'      => $sede,
            'sedes'     => RecepcionRoles::TODAS_LAS_SEDES,
            'fecha'     => $fecha,
            'hoy'       => $hoy,
            'origen'    => $origen,
            'modulos'   => $modulos,
            'porModulo' => $porModulo,
            'citas'     => $citas,
            'conteo'    => [
                'total'      => $delDia->count(),
                'linea'      => $delDia->where('origen', 'linea')->count(),
                'ventanilla' => $delDia->where('origen', 'ventanilla')->count(),
            ],
            'opciones'  => $opciones,
            'personal'  => $personal,
            'tramites'  => AsignadorModulos::TRAMITES,
            'limite'    => LimiteCitas::config(),
            'limiteHoy' => LimiteCitas::hoy(),
            'enLineaMigrado' => $enLineaMigrado,
            'ventanillaMigrada' => Modulo::pausaVentanillaMigrada(),
            'pausaDesdeMigrada' => Modulo::pausaDesdeMigrada(),
            'sinEnLinea'     => $enLineaMigrado ? self::tramitesEnPausa($sede, 'linea', $modulos) : [],
            'sinVentanilla'  => $enLineaMigrado ? self::tramitesEnPausa($sede, 'ventanilla', $modulos) : [],
        ]);
    }

    /**
     * Switch "Recibe citas" de cada módulo. Apagado, y hasta que se prenda
     * otra vez, no se le asignan citas nuevas:
     *   linea  sólo las del formulario público; la ventanilla sí.
     *   todas  tampoco las de ventanilla, la reagenda ni la reasignación manual.
     * Desde ahora (desde = null) o a partir de una fecha: las citas de ese día
     * en adelante. Las que ya tenía siguen con él.
     */
    public function enLinea(Request $request, Modulo $modulo)
    {
        if (! Modulo::enLineaMigrado()) {
            $mensaje = 'Falta correr la migración de módulos (php artisan migrate) para poder pausar las citas en línea.';

            return $request->expectsJson()
                ? response()->json(['message' => $mensaje], 409)
                : $this->volver($request, $modulo->delegacion)->with('error', $mensaje);
        }

        // 'pausa' (ninguna | linea | todas); 'en_linea' se acepta por si una
        // pestaña quedó abierta con la versión anterior del switch.
        $hoy = Carbon::today()->toDateString();
        $request->validate([
            'pausa'    => ['required_without:en_linea', Rule::in(['ninguna', 'linea', 'todas'])],
            'en_linea' => ['required_without:pausa', 'boolean'],
            'desde'    => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.$hoy],
        ], [
            'desde.after_or_equal' => 'La fecha de la pausa no puede ser de un día que ya pasó.',
        ]);
        $pausa = $request->filled('pausa') ? $request->input('pausa') : ($request->boolean('en_linea') ? 'ninguna' : 'linea');
        // Hoy o sin fecha = desde ahora.
        $desde = $pausa !== 'ninguna' && $request->filled('desde') && $request->input('desde') > $hoy ? $request->input('desde') : null;

        if ($desde && ! Modulo::pausaDesdeMigrada()) {
            $mensaje = 'Falta correr la migración de módulos (php artisan migrate) para poder programar la pausa con fecha.';

            return $request->expectsJson()
                ? response()->json(['message' => $mensaje], 409)
                : $this->volver($request, $modulo->delegacion)->with('error', $mensaje);
        }

        if ($pausa === 'todas' && ! Modulo::pausaVentanillaMigrada()) {
            $mensaje = 'Falta correr la migración de módulos (php artisan migrate) para poder pausar también la ventanilla.';

            return $request->expectsJson()
                ? response()->json(['message' => $mensaje], 409)
                : $this->volver($request, $modulo->delegacion)->with('error', $mensaje);
        }

        $desdeAntes = $modulo->pausa_desde?->toDateString();
        if (($modulo->pausa() ?? 'ninguna') !== $pausa || $desdeAntes !== $desde) {
            $antes = $modulo->pausa() ?? 'ninguna';
            $modulo->forceFill(array_merge([
                'en_linea'            => $pausa === 'ninguna',
                'en_linea_cambio_at'  => now(),
                'en_linea_cambio_por' => $request->user()->id,
            ], Modulo::pausaVentanillaMigrada() ? ['pausa_ventanilla' => $pausa === 'todas'] : [],
               Modulo::pausaDesdeMigrada() ? ['pausa_desde' => $desde] : []))->save();

            Log::info('Pausa de citas del módulo', [
                'modulo' => $modulo->id, 'nombre' => $modulo->nombre, 'sede' => $modulo->delegacion,
                'persona' => $modulo->user_id, 'antes' => $antes, 'ahora' => $pausa,
                'desde_antes' => $desdeAntes, 'desde' => $desde, 'por' => $request->user()->id,
            ]);
        }

        $modulo->load(['persona:id,name', 'cambioEnLineaPor:id,name']);
        $quien = $modulo->persona ? ' ('.$modulo->persona->name.')' : '';
        $cuando = $desde ? 'a partir del '.Carbon::parse($desde)->locale('es')->isoFormat('dddd D [de] MMMM') : 'desde ahora';
        $mensaje = match ($pausa) {
            'todas' => "{$modulo->nombre}{$quien} no recibirá citas nuevas, ni en línea ni de ventanilla, {$cuando}; tampoco se le podrán reasignar.",
            'linea' => "{$modulo->nombre}{$quien} no recibirá citas en línea {$cuando}; la ventanilla sí le sigue asignando.",
            default => "{$modulo->nombre}{$quien} vuelve a recibir citas en línea y de ventanilla.",
        };

        // Las que ya tiene de esa fecha en adelante no se mueven solas: se avisa.
        $pendientes = $pausa === 'ninguna' ? 0 : DB::table('recepcion')
            ->where('modulo_id', $modulo->id)
            ->where('fecha', '>=', $desde ?? $hoy)
            ->whereIn('estatus', self::MOVIBLES)
            ->count();
        $aviso = $pendientes > 0
            ? "Tiene {$pendientes} ".($pendientes === 1 ? 'cita' : 'citas').' '.($desde ? 'desde esa fecha' : 'de hoy en adelante')
              .($pendientes === 1 ? ' que sigue' : ' que siguen').' con él; si hace falta, muévelas a otro módulo.'
            : null;
        if ($aviso) {
            $mensaje .= ' '.$aviso;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'pausa'         => $pausa,
                'desde'         => $desde,
                'programada'    => $modulo->pausaProgramada(),
                'en_linea'      => $pausa === 'ninguna',
                'nota'          => $modulo->notaPausa(),
                'aviso'         => $aviso,
                'message'       => $mensaje,
                'sinEnLinea'    => self::tramitesEnPausa($modulo->delegacion, 'linea'),
                'sinVentanilla' => self::tramitesEnPausa($modulo->delegacion, 'ventanilla'),
            ]);
        }

        return $this->volver($request, $modulo->delegacion)->with('success', $mensaje);
    }

    /**
     * Trámites que en la sede nadie recibe por ese canal ('linea' o
     * 'ventanilla') porque todos los módulos que los atienden tienen la pausa
     * puesta: el calendario de ese canal no da horarios para ellos.
     */
    private static function tramitesEnPausa(string $sede, string $canal, $modulos = null): array
    {
        $modulos ??= AsignadorModulos::deSede($sede);

        return array_values(array_filter(
            AsignadorModulos::TRAMITES,
            fn ($t) => ($canal === 'ventanilla' || $t !== 'Cumplimiento')
                && AsignadorModulos::enPausa($sede, $t, $canal, $modulos->where('activo', true))
        ));
    }

    /**
     * Tope de citas en línea por conexión (IP), correo y teléfono, para todas
     * las sedes (App\Support\LimiteCitas). 0 = sin límite.
     */
    public function limite(Request $request)
    {
        abort_unless(LimiteCitas::migrado(), 404);

        $numero = ['required', 'integer', 'min:0', 'max:'.LimiteCitas::MAXIMO];
        $datos = $request->validate([
            'por_ip'       => $numero,
            'por_correo'   => $numero,
            'por_telefono' => $numero,
            'periodo'      => ['required', Rule::in(array_keys(LimiteCitas::PERIODOS))],
            'exentas'      => ['nullable', 'string', 'max:2000'],
        ], [], [
            'por_ip'       => 'límite por conexión (IP)',
            'por_correo'   => 'límite por correo',
            'por_telefono' => 'límite por teléfono',
            'periodo'      => 'periodo',
            'exentas'      => 'conexiones sin límite',
        ]);

        $escritas = preg_split('/[\s,;]+/', trim((string) ($datos['exentas'] ?? '')), -1, PREG_SPLIT_NO_EMPTY);
        $invalidas = array_values(array_diff($escritas, LimiteCitas::listaExentas((string) ($datos['exentas'] ?? ''))));
        if ($invalidas) {
            return back()->withInput()->withErrors(['exentas' => 'Estas no son direcciones IP válidas: '.implode(', ', $invalidas).'.']);
        }

        $antes = LimiteCitas::config();
        LimiteCitas::guardar($datos, $request->user());
        $ahora = LimiteCitas::config();

        Log::info('Límite de citas en línea actualizado', [
            'por'   => $request->user()->id,
            'antes' => [$antes->por_ip, $antes->por_correo, $antes->por_telefono, $antes->periodo],
            'ahora' => [$ahora->por_ip, $ahora->por_correo, $ahora->por_telefono, $ahora->periodo],
        ]);

        return redirect()->route('modulos.index', array_filter([
            'sede'   => $request->input('sede_vista'),
            'fecha'  => $request->input('fecha_vista'),
            'origen' => $request->input('origen_vista'),
        ]))->with('success', LimiteCitas::activo($ahora)
            ? 'Se guardó el límite de citas en línea; aplica desde ahora en todas las sedes.'
            : 'Se quitó el límite: las citas en línea ya no tienen tope.');
    }

    public function guardar(Request $request, ?Modulo $modulo = null)
    {
        $sede = $modulo?->delegacion ?? $request->input('delegacion');

        $datos = $request->validate([
            'delegacion'  => [$modulo ? 'nullable' : 'required', Rule::in(RecepcionRoles::TODAS_LAS_SEDES)],
            'nombre'      => ['required', 'string', 'max:60',
                Rule::unique('modulos', 'nombre')->where('delegacion', $sede)->ignore($modulo?->id)],
            'user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'tramites'    => ['required', 'array', 'min:1'],
            'tramites.*'  => [Rule::in(AsignadorModulos::TRAMITES)],
            'respaldo'    => ['nullable', 'array'],
            'respaldo.*'  => [Rule::in(AsignadorModulos::TRAMITES)],
            'hora_comida' => ['nullable', 'date_format:H:i'],
            'activo'      => ['nullable', 'boolean'],
            'orden'       => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'tramites.required' => 'Marca al menos un trámite que reciba el módulo.',
            'nombre.unique'     => 'Ya hay un módulo con ese nombre en la sede.',
        ]);

        $valores = [
            'nombre'      => trim($datos['nombre']),
            'user_id'     => $datos['user_id'] ?? null,
            'tramites'    => array_values($datos['tramites']),
            // Lo que ya recibe de primera mano no se repite como respaldo.
            'respaldo'    => array_values(array_diff($datos['respaldo'] ?? [], $datos['tramites'])) ?: null,
            'hora_comida' => $datos['hora_comida'] ?? null,
            'activo'      => $request->boolean('activo'),
            'orden'       => $datos['orden'] ?? ($modulo?->orden ?? Modulo::where('delegacion', $sede)->max('orden') + 1),
        ];

        if (! $modulo) {
            $modulo = Modulo::create($valores + ['delegacion' => $sede]);

            return $this->volver($request, $sede)->with('success', "Se creó {$modulo->nombre} en {$sede}.".$this->avisoPermisos($modulo));
        }

        $personaAnterior = $modulo->user_id;
        $nombreAnterior  = $modulo->nombre;
        $modulo->update($valores);

        $aviso = "Se guardó {$modulo->nombre}.";
        $aviso .= $this->avisoPermisos($modulo);

        // Las citas que vienen y aún no se atienden siguen al módulo: si
        // cambió la persona o el nombre, se les actualiza.
        if ($personaAnterior !== $modulo->user_id || $nombreAnterior !== $modulo->nombre) {
            $movidas = DB::table('recepcion')
                ->where('modulo_id', $modulo->id)
                ->where('fecha', '>=', Carbon::today()->toDateString())
                ->whereIn('estatus', self::MOVIBLES)
                ->update(['auxiliar' => $modulo->user_id ?? 0, 'lugar_auxiliar' => $modulo->nombre]);

            if ($movidas > 0) {
                $aviso .= " {$movidas} ".($movidas === 1 ? 'cita pendiente pasó' : 'citas pendientes pasaron')
                    .' a '.($modulo->persona?->name ?? 'nadie (módulo sin persona)').'.';
            }

            Log::info('Módulo actualizado', [
                'modulo' => $modulo->id, 'por' => $request->user()->id,
                'persona_antes' => $personaAnterior, 'persona_ahora' => $modulo->user_id, 'citas' => $movidas,
            ]);
        }

        return $this->volver($request, $modulo->delegacion)->with('success', $aviso);
    }

    public function reasignar(Request $request, int $id)
    {
        $datos = $request->validate(['modulo_id' => ['required', 'integer', 'exists:modulos,id']]);

        $cita      = Recepcion::findOrFail($id);
        $resultado = ReasignacionCita::mover($cita, Modulo::findOrFail($datos['modulo_id']), $request->user()->id);

        $respuesta = $this->volver($request, $cita->delegacion, $cita->fecha->format('Y-m-d'));
        foreach (array_filter($resultado) as $tipo => $mensaje) {
            $respuesta->with($tipo, $mensaje);
        }

        return $respuesta;
    }

    private function volver(Request $request, string $sede, ?string $fecha = null)
    {
        return redirect()->route('modulos.index', array_filter([
            'sede'   => $sede,
            'fecha'  => $fecha ?? $request->input('fecha_vista'),
            'origen' => $request->input('origen_vista'),
        ]));
    }

    /**
     * Trámites del módulo (incluido el respaldo) que su persona no puede
     * atender con los permisos de su rol. Se avisa, no se bloquea: puede que
     * primero se arme el módulo y después se ajuste la cuenta en Usuarios.
     */
    public static function tramitesSinPermiso(Modulo $modulo): array
    {
        if (! $modulo->persona) {
            return [];
        }

        $tramites = array_unique(array_merge($modulo->tramites ?? [], $modulo->respaldo ?? []));

        return array_values(array_filter($tramites, fn ($t) => ! Especialidades::puedeAtender($modulo->persona, $t)));
    }

    private function avisoPermisos(Modulo $modulo): string
    {
        $modulo->load('persona');
        $faltan = self::tramitesSinPermiso($modulo);

        return $faltan
            ? ' Ojo: '.$modulo->persona->name.' no tiene permisos para '.implode(', ', $faltan)
              .'; asígnale la especialidad en Usuarios o esas citas no las podrá atender.'
            : '';
    }
}
