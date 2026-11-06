<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Recepcion;
use App\Support\VerComo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Administración → "Ver como" (App\Support\VerComo). Sólo Super Usuario; estas
 * rutas siempre corren con el usuario real (VerComoMiddleware).
 */
class VerComoController extends Controller
{
    public function index(Request $request)
    {
        $real = $request->user();
        abort_unless(VerComo::puede($real), 403);

        $f = $request->validate([
            'q'          => ['nullable', 'string', 'max:100'],
            'rol'        => ['nullable', 'string', 'max:60'],
            'sede'       => ['nullable', 'string', 'max:40'],
            'inactivos'  => ['nullable', 'boolean'],
        ]);
        $q = trim($f['q'] ?? '');
        $roles = Role::where('guard_name', 'web')->orderBy('name')->pluck('name')->all();
        $rol   = in_array($f['rol'] ?? null, $roles, true) ? $f['rol'] : null;

        $usuarios = User::with('roles:id,name')
            ->when(! ($f['inactivos'] ?? false), fn ($w) => $w->where('estatus', 'Activo'))
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($rol, fn ($w, $r) => $w->role($r))
            ->when($f['sede'] ?? null, fn ($w, $sede) => $w->where('delegacion', $sede))
            ->where('id', '!=', $real->id)
            ->orderBy('name')
            ->paginate(25, ['id', 'name', 'email', 'delegacion', 'estatus'])
            ->withQueryString();

        $datos = VerComo::datos();

        return view('ver_como.index', [
            'usuarios' => $usuarios,
            'roles'    => $roles,
            'sedes'    => Recepcion::TODAS_LAS_SEDES,
            'f'        => ['q' => $q, 'rol' => $rol, 'sede' => $f['sede'] ?? null, 'inactivos' => (bool) ($f['inactivos'] ?? false)],
            'actual'   => $datos ? VerComo::etiqueta($datos, VerComo::usuario($datos, $real)) : null,
        ]);
    }

    /** Búsqueda del modal del encabezado: hasta 20 personas por nombre o correo. */
    public function buscar(Request $request)
    {
        $real = $request->user();
        abort_unless(VerComo::puede($real), 403);

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['personas' => []]);
        }

        $personas = User::with('roles:id,name')
            ->where('id', '!=', $real->id)
            ->where(fn ($s) => $s->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
            ->orderByRaw("CASE WHEN estatus = 'Activo' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email', 'delegacion', 'estatus', 'sexo'])
            ->map(fn (User $u) => [
                'id'       => $u->id,
                'nombre'   => \Illuminate\Support\Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $u->name)))),
                'cargo'    => trim((VerComo::rolesLegibles($u->roles->pluck('name')->all(), $u->sexo) ?: 'Sin rol').($u->delegacion ? ' de '.$u->delegacion : '')),
                'correo'   => $u->email,
                'inactivo' => $u->estatus !== 'Activo',
            ]);

        return response()->json(['personas' => $personas])->header('Cache-Control', 'no-store');
    }

    /** Empieza a ver como una persona o como un rol con su sede. */
    public function iniciar(Request $request)
    {
        $real = $request->user();
        abort_unless(VerComo::puede($real), 403);

        $tipo = $request->input('tipo');
        if ($tipo === 'usuario') {
            $id = (int) $request->validate(['user_id' => ['required', 'integer']])['user_id'];
            $objetivo = User::find($id);
            abort_unless($objetivo && $objetivo->id !== $real->id, 404);
            VerComo::iniciarUsuario($real, $objetivo);
        } else {
            $datos = $request->validate([
                'roles'   => ['required', 'array', 'min:1', 'max:6'],
                'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
                'sede'    => ['required', Rule::in(Recepcion::TODAS_LAS_SEDES)],
            ], [
                'roles.required' => 'Elige al menos un rol.',
                'sede.required'  => 'Elige la sede.',
            ]);
            VerComo::iniciarRol($real, $datos['roles'], $datos['sede']);
        }

        return redirect()->route('inicio');
    }

    public function salir(Request $request)
    {
        VerComo::salir($request->user());

        // De regreso a donde estaba (ahora con su usuario); si era la misma pantalla de Ver como, a ella.
        return redirect()->to(url()->previous() ?: route('inicio'))->with('success', 'Saliste de "Ver como": ya ves el sistema con tu usuario.');
    }
}
