<?php

namespace App\Http\Controllers;

use App\Exports\InventarioExport;
use App\Models\InventarioDispositivo;
use App\Models\InventarioHistorial;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Administración → Inventario (sólo Super Usuario; lo pide la ruta).
 *
 * La tabla se corrige en el momento: cada renglón sin verificar es editable y
 * se guarda solo; con el check puesto queda bloqueado, también aquí en el
 * servidor. Cada alta, corrección, verificación y bandera de revisión queda en
 * su propia tabla, inventario_historial (App\Models\InventarioHistorial).
 */
class InventarioController extends Controller
{
    public function index()
    {
        $dispositivos = InventarioDispositivo::with('verificador:id,name')->orderBy('id')->get();

        // Personal que puede quedar como resguardante
        $usuarios = User::whereHas('roles')
            ->where('estatus', 'Activo')
            ->orderBy('name')
            ->get(['id', 'name', 'delegacion']);

        // puesto mas reciente que se le ha capturado a cada usuario: se propone al elegirlo
        $puestos = $dispositivos->whereNotNull('resguardante_id')->whereNotNull('resguardante_puesto')
            ->sortBy('updated_at')
            ->mapWithKeys(fn ($d) => [$d->resguardante_id => $d->resguardante_puesto]);

        return view('administracion.inventario.index', [
            'dispositivos' => $dispositivos,
            'usuarios'     => $usuarios,
            'puestos'      => $puestos->map(fn ($p) => mb_strtoupper($p)),
            'descripciones' => collect(InventarioDispositivo::DESCRIPCIONES)
                ->merge($dispositivos->pluck('descripcion'))->filter()->unique()->sort()->values(),
            'ubicaciones'  => $dispositivos->pluck('ubicacion')->filter()->unique()->sort()->values(),
        ]);
    }

    public function guardar(Request $request): JsonResponse
    {
        $datos = $this->validar($request);

        $dispositivo = DB::transaction(function () use ($datos) {
            $d = new InventarioDispositivo($datos);
            $d->creado_por = auth()->id();
            $d->actualizado_por = auth()->id();
            $d->save();

            $this->registrar('alta', $d, null, $this->soloCampos($d->fresh()->getRawOriginal()));

            return $d;
        });

        return response()->json([
            'message' => 'Dispositivo agregado.',
            'id'      => $dispositivo->id,
        ]);
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        $dispositivo = InventarioDispositivo::findOrFail($id);

        if ($dispositivo->verificado) {
            return response()->json(['message' => 'Este dispositivo ya está verificado. Quítale el check para poder corregirlo.'], 423);
        }

        $datos = $this->validar($request, $dispositivo);

        DB::transaction(function () use ($dispositivo, $datos) {
            $antes = $dispositivo->getRawOriginal();
            $dispositivo->fill($datos);
            $cambiado = $dispositivo->getDirty();

            if (empty($cambiado)) {
                return;
            }

            $dispositivo->actualizado_por = auth()->id();
            $dispositivo->save();

            $this->registrar('edicion', $dispositivo,
                array_intersect_key($antes, $cambiado),
                array_intersect_key($dispositivo->fresh()->getRawOriginal(), $cambiado));
        });

        return response()->json([
            'message'     => 'Cambios guardados.',
            'dispositivo' => $dispositivo->fresh('verificador')->paraTabla(),
        ]);
    }

    public function verificar(Request $request, int $id): JsonResponse
    {
        $request->validate(['verificado' => 'required|boolean']);
        $dispositivo = InventarioDispositivo::findOrFail($id);
        $verificado = $request->boolean('verificado');

        if ($dispositivo->verificado !== $verificado) {
            DB::transaction(function () use ($dispositivo, $verificado) {
                $antes = ['verificado' => (int) $dispositivo->verificado];

                $dispositivo->verificado = $verificado;
                $dispositivo->verificado_por = $verificado ? auth()->id() : null;
                $dispositivo->verificado_at = $verificado ? now() : null;
                $dispositivo->save();

                $this->registrar('verificacion', $dispositivo, $antes, ['verificado' => (int) $verificado],
                    $verificado ? 'Verificado' : 'Se quitó la verificación');
            });
        }

        return response()->json([
            'message'     => $verificado ? 'Dispositivo verificado.' : 'Se quitó la verificación; ya se puede corregir.',
            'dispositivo' => $dispositivo->fresh('verificador')->paraTabla(),
        ]);
    }

    public function revision(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'revisar' => 'required|boolean',
            'nota'    => 'nullable|string|max:500',
        ]);
        $dispositivo = InventarioDispositivo::findOrFail($id);
        $revisar = $request->boolean('revisar');
        $nota = $revisar ? $request->input('nota') : null;

        DB::transaction(function () use ($dispositivo, $revisar, $nota) {
            $antes = ['revisar' => (int) $dispositivo->revisar, 'revisar_nota' => $dispositivo->revisar_nota];

            $dispositivo->revisar = $revisar;
            $dispositivo->revisar_nota = $nota;
            if (! $dispositivo->isDirty()) {
                return;
            }
            $dispositivo->save();

            $this->registrar('revision', $dispositivo, $antes, ['revisar' => (int) $revisar, 'revisar_nota' => $nota],
                $revisar ? 'Marcado para revisión'.($nota ? ': '.$nota : '') : 'Revisión atendida');
        });

        return response()->json([
            'message'     => $revisar ? 'Marcado para revisión.' : 'Se quitó la bandera de revisión.',
            'dispositivo' => $dispositivo->fresh('verificador')->paraTabla(),
        ]);
    }

    /** Movimientos de un dispositivo (?dispositivo=id) o de todo el inventario, más recientes primero. */
    public function historial(Request $request): JsonResponse
    {
        $request->validate(['dispositivo' => 'nullable|integer']);

        $movimientos = InventarioHistorial::with('dispositivo:id,descripcion,marca,modelo,numero_serie')
            ->when($request->filled('dispositivo'), fn ($q) => $q->where('dispositivo_id', $request->integer('dispositivo')))
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        return response()->json($movimientos->map(function (InventarioHistorial $m) {
            $antes = $m->datos_antes ?? [];
            $despues = $m->datos_despues ?? [];
            $d = $m->dispositivo;

            $cambios = [];
            foreach ($despues as $campo => $valor) {
                if (in_array($campo, ['resguardante_id', 'verificado', 'revisar', 'revisar_nota'], true)) {
                    continue; // se leen mejor en el texto del movimiento o con el nombre del resguardante
                }
                if ($m->movimiento === 'alta' &&($valor === null || $valor === '')) {
                    continue; // en el alta sólo lo que se capturó
                }
                $cambios[] = [
                    'campo'   => InventarioDispositivo::CAMPOS[$campo] ?? $campo,
                    'antes'   => $this->legible($campo, $antes[$campo] ?? null),
                    'despues' => $this->legible($campo, $valor),
                ];
            }

            return [
                'cuando'      => $m->created_at->format('d/m/Y H:i'),
                'movimiento'  => InventarioHistorial::MOVIMIENTOS[$m->movimiento] ?? $m->movimiento,
                'tipo'        => $m->movimiento,
                'dispositivo' => $d ? trim($d->descripcion.' '.$d->marca.' '.$d->modelo.($d->numero_serie ? ' · '.$d->numero_serie : '')) : '#'.$m->dispositivo_id,
                'nota'        => $m->nota,
                'quien'       => $m->user_nombre ?? '—',
                'alta'        => $m->movimiento === 'alta',
                'cambios'     => $cambios,
            ];
        }));
    }

    public function exportar()
    {
        $dispositivos = InventarioDispositivo::with('verificador:id,name')->orderBy('id')->get();

        return Excel::download(new InventarioExport($dispositivos), 'inventario_'.now()->format('Y-m-d_His').'.xlsx');
    }

    /** Campos de texto que se guardan en mayúsculas. */
    private const MAYUSCULAS = ['descripcion', 'marca', 'modelo', 'numero_serie', 'razon_malo', 'ubicacion',
        'resguardante_nombre', 'resguardante_puesto', 'caracteristicas', 'observaciones'];

    private function validar(Request $request, ?InventarioDispositivo $dispositivo = null): array
    {
        // Antes de validar, para que la serie única también compare en mayúsculas.
        $request->merge(collect($request->only(self::MAYUSCULAS))
            ->map(fn ($v) => is_string($v) ? mb_strtoupper($v) : $v)
            ->all());

        $datos = $request->validate([
            'descripcion'         => 'required|string|max:120',
            'marca'               => 'required|string|max:80',
            'modelo'              => 'required|string|max:120',
            'numero_serie'        => ['nullable', 'string', 'max:120', Rule::unique('inventario_dispositivos', 'numero_serie')->ignore($dispositivo?->id)],
            'estado_uso'          => 'required|in:bueno,regular,malo',
            'razon_malo'          => 'nullable|required_if:estado_uso,malo|string|max:1000',
            'ubicacion'           => 'required|string|max:150',
            'resguardante_id'     => 'nullable|integer|exists:users,id',
            'resguardante_nombre' => 'nullable|string|max:191',
            'resguardante_puesto' => 'nullable|string|max:150',
            'caracteristicas'     => 'nullable|string|max:2000',
            'observaciones'       => 'nullable|string|max:2000',
        ], [
            'descripcion.required'   => 'Falta la descripción (qué es el dispositivo).',
            'marca.required'         => 'Falta la marca.',
            'modelo.required'        => 'Falta el modelo.',
            'numero_serie.unique'    => 'Ya hay otro dispositivo con ese número de serie.',
            'estado_uso.required'    => 'Falta el estado de uso.',
            'estado_uso.in'          => 'El estado de uso debe ser bueno, regular o malo.',
            'razon_malo.required_if' => 'Explica por qué el dispositivo está en mal estado.',
            'ubicacion.required'     => 'Falta la ubicación física.',
            'resguardante_id.exists' => 'El usuario resguardante no existe.',
        ]);

        // El nombre del usuario elegido manda sobre lo escrito; sin usuario se
        // acepta un nombre libre (personal que no tiene cuenta).
        if (! empty($datos['resguardante_id'])) {
            $datos['resguardante_nombre'] = mb_strtoupper((string) User::whereKey($datos['resguardante_id'])->value('name'));
        }
        if ($datos['estado_uso'] !== 'malo') {
            $datos['razon_malo'] = null;
        }

        return $datos;
    }

    private function soloCampos(array $fila): array
    {
        return array_intersect_key($fila, InventarioDispositivo::CAMPOS);
    }

    private function legible(string $campo, $valor): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        return $campo === 'estado_uso' ? (InventarioDispositivo::ESTADOS[$valor] ?? $valor) : (string) $valor;
    }

    private function registrar(string $movimiento, InventarioDispositivo $d, ?array $antes, ?array $despues, ?string $nota = null): void
    {
        InventarioHistorial::create([
            'dispositivo_id' => $d->id,
            'movimiento'     => $movimiento,
            'datos_antes'    => $antes,
            'datos_despues'  => $despues,
            'nota'           => $nota,
            'user_id'        => auth()->id(),
            'user_nombre'    => auth()->user()->name ?? null,
            'ip'             => request()->ip(),
        ]);
    }
}
