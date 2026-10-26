<?php

namespace App\Http\Controllers;

use App\Models\Sedes;
use App\Models\User;
use App\Support\ConveniosDelMes;
use App\Support\ConveniosPagados;
use App\Support\CasosExcepcion;
use App\Support\DesempenoNotificadores;
use App\Support\EmbudoCasos;
use App\Support\BitacoraIncidencias;
use App\Exports\EstadisticasTurnosExport;
use App\Support\EstadisticasExcepcion;
use App\Support\EstadisticasTurnos;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ResumenSedes;
use App\Support\TasaConciliacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * /estadisticas: el panel general del Centro.
 *
 * Arriba, la descarga de los mismos reportes que /seer/estadistica (el
 * formulario manda a seer.mostar, que es quien los arma); luego las cifras
 * del mes (convenios pagados, montos convenidos y tasa de conciliación, que
 * antes iban en el Inicio de todos) y abajo el mapa de sedes y las gráficas
 * que antes sólo veía el Directivo en su Inicio. Aquí se irán sumando más.
 *
 * Cada tema con más detalle tiene su sección (/estadisticas/notificaciones)
 * y aquí sólo una tarjeta que lleva a ella.
 *
 * Sólo Super Usuario, Estadistica y Directivo (lo cuida la ruta). Los tres
 * ven todas las sedes.
 */
class EstadisticasController extends Controller
{
    public function index()
    {
        $activosConRol = fn (string $rol) => User::role($rol)
            ->where('estatus', 'Activo')
            ->where('email', 'not like', '%temporal%')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('estadisticas.panel', [
            'sedes'         => Sedes::orderBy('id')->pluck('nombre'),
            'conciliadores' => $activosConRol('Conciliador'),
            'notificadores' => $activosConRol('Notificador'),
            'resumen'       => ResumenSedes::totales(),
            'resumenSedes'  => ResumenSedes::detalle(),
            // Tarjetas de cifras del mes (inicio/_tarjetas).
            'convenios'        => ConveniosPagados::porSede(),
            'conveniosMes'     => ConveniosDelMes::resumen(),
            'tasaConciliacion' => TasaConciliacion::resumen(),
            // Tarjeta de casos de excepción: cifras del mes.
            'excepcionesMes' => EstadisticasExcepcion::resumen(
                EstadisticasExcepcion::rango(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()), null
            ),
            // Tarjeta de turnos y citas: cifras del mes.
            'turnosMes' => EstadisticasTurnos::resumen(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()),
            // Tarjeta de incidencias: hoy, este mes y este año.
            'incidenciasMes' => BitacoraIncidencias::cifras(),
            // Tarjeta de la sección de notificaciones: cifras del mes.
            'notificacionesMes' => DesempenoNotificadores::resumen(
                DesempenoNotificadores::base(),
                DesempenoNotificadores::rango(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString())
            ),
        ]);
    }

    /**
     * /estadisticas/seguimiento: cuántos casos llegan a cada etapa (cita,
     * solicitud, audiencia, resultado, pago) y cuántos días tardan
     * (App\Support\EmbudoCasos). Por omisión, los últimos tres meses.
     */
    public function seguimiento(Request $request)
    {
        $sedes = Sedes::orderBy('id')->pluck('nombre');
        $mes   = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}$/', $v) ? $v : null;

        $desde = $mes($request->query('desde')) ?? now()->subMonths(2)->format('Y-m');
        $hasta = $mes($request->query('hasta')) ?? now()->format('Y-m');
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }
        $sede = in_array($request->query('sede'), $sedes->all(), true) ? $request->query('sede') : null;

        $inicio = Carbon::parse($desde.'-01')->startOfMonth();
        $fin    = Carbon::parse($hasta.'-01')->endOfMonth();
        $nombre = fn (Carbon $c) => \Illuminate\Support\Str::ucfirst($c->locale('es')->translatedFormat('F Y'));

        return view('estadisticas.seguimiento', [
            'sedes'   => $sedes,
            'filtros' => [
                'desde' => $desde,
                'hasta' => $hasta,
                'sede'  => $sede,
                'texto' => ($desde === $hasta ? $nombre($inicio) : $nombre($inicio).' a '.$nombre($fin)).($sede ? ' · '.$sede : ' · todas las sedes'),
            ],
            'datos'   => EmbudoCasos::calcular($inicio->toDateString(), $fin->toDateString(), $sede),
        ]);
    }

    /**
     * /estadisticas/notificaciones: desempeño de los notificadores (lo que
     * antes era el filtro avanzado del historial de notificaciones).
     *
     * Sin periodo en la URL se toma el mes en curso; "Todo" manda
     * periodo=todo. La gráfica usa el mismo periodo y, con "Todo", los
     * últimos doce meses.
     */
    public function notificaciones(Request $request)
    {
        $sedes = Sedes::orderBy('id')->pluck('nombre');

        $filtros = $request->validate([
            'sede'        => ['nullable', Rule::in($sedes->all())],
            'notificador' => ['nullable', 'string', 'max:12'],
            'tipo'        => ['nullable', Rule::in(['Citatorio', 'Multa'])],
            'desde'       => ['nullable', 'date_format:Y-m-d'],
            'hasta'       => ['nullable', 'date_format:Y-m-d'],
            'periodo'     => ['nullable', Rule::in(['todo'])],
        ]);

        $todo = ($filtros['periodo'] ?? null) === 'todo';
        if (! $todo && empty($filtros['desde']) && empty($filtros['hasta'])) {
            $filtros['desde'] = now()->startOfMonth()->toDateString();
            $filtros['hasta'] = now()->endOfMonth()->toDateString();
        }
        if ($todo) {
            $filtros['desde'] = $filtros['hasta'] = null;
        }
        // Si las pusieron al revés, se acomodan en lugar de marcar error.
        if (! empty($filtros['desde']) && ! empty($filtros['hasta']) && $filtros['desde'] > $filtros['hasta']) {
            [$filtros['desde'], $filtros['hasta']] = [$filtros['hasta'], $filtros['desde']];
        }

        $notificador = $filtros['notificador'] ?? null;
        $porId = $notificador && ctype_digit($notificador);

        $base = DesempenoNotificadores::base()
            ->when(! empty($filtros['sede']), fn ($q) => $q->where('g.delegacion', $filtros['sede']))
            ->when(! empty($filtros['tipo']), fn ($q) => $q->where('c.tipo_notificacion', $filtros['tipo']))
            ->when($notificador === 'sin', fn ($q) => $q->where('c.id_notificador', 0))
            ->when($porId, fn ($q) => $q->where('c.id_notificador', (int) $notificador));

        $rango = DesempenoNotificadores::rango($filtros['desde'] ?? null, $filtros['hasta'] ?? null);

        // La gráfica necesita un principio y un fin reales.
        $hastaGrafica = ! empty($filtros['hasta']) ? Carbon::parse($filtros['hasta']) : Carbon::today();
        $desdeGrafica = ! empty($filtros['desde'])
            ? Carbon::parse($filtros['desde'])
            : $hastaGrafica->copy()->subMonthsNoOverflow(11)->startOfMonth();
        $rangoGrafica = [$desdeGrafica->startOfDay()->toDateTimeString(), $hastaGrafica->endOfDay()->toDateTimeString()];

        $notificadores = User::role('Notificador')
            ->where('estatus', 'Activo')
            ->where('email', 'not like', '%temporal%')
            ->orderBy('name')
            ->get(['id', 'name', 'delegacion'])
            ->groupBy(fn ($u) => $u->delegacion ?: 'Sin sede');

        return view('estadisticas.notificaciones', [
            'filtros'        => $filtros,
            'todo'           => $todo,
            'sedes'          => $sedes,
            'notificadores'  => $notificadores,
            'elegido'        => $porId ? User::find((int) $notificador, ['id', 'name', 'email', 'delegacion']) : null,
            'resumen'        => DesempenoNotificadores::resumen($base, $rango),
            'porNotificador' => $porId || $notificador === 'sin' ? collect() : DesempenoNotificadores::porNotificador($base, $rango),
            'porSede'        => ! empty($filtros['sede']) ? collect() : DesempenoNotificadores::porSede($base, $rango),
            'tendencia'      => DesempenoNotificadores::tendencia($base, $rangoGrafica),
        ]);
    }

    /**
     * /estadisticas/excepciones: casos del art. 685 Ter LFT detectados,
     * atendidos, que continúan con la conciliación y canalizados, por
     * periodo y sede (App\Support\EstadisticasExcepcion). Lo ven el Panel
     * estadístico y Conciliación, Género e Igualdad.
     */
    public function excepciones(Request $request)
    {
        [$filtros, $rango, $sede] = $this->filtrosExcepciones($request);

        return view('estadisticas.excepciones', [
            'filtros'       => $filtros,
            'migrado'       => CasosExcepcion::conFormato(),
            'resumen'       => EstadisticasExcepcion::resumen($rango, $sede),
            'tendencia'     => EstadisticasExcepcion::tendencia($rango, $sede),
            'instituciones' => EstadisticasExcepcion::porJson('instituciones', CasosExcepcion::INSTITUCIONES, $rango, $sede),
            'tipos'         => EstadisticasExcepcion::porJson('tipos', CasosExcepcion::TIPOS, $rango, $sede),
            'porSede'       => $sede ? collect() : EstadisticasExcepcion::porSede($rango),
            'generos'       => EstadisticasExcepcion::porColumna('genero', ['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'], $rango, $sede),
            'vulnerables'   => EstadisticasExcepcion::porColumna('vulnerables', ['LGBTTTIQ' => 'LGBTTTIQ+'], $rango, $sede),
            'frecuencias'   => EstadisticasExcepcion::porColumna('frecuencia', CasosExcepcion::FRECUENCIAS, $rango, $sede),
            'esPanel'       => $request->user()->hasAnyRole(['Super Usuario', 'Estadistica', 'Directivo']),
            'motivos'       => EstadisticasExcepcion::porMotivo($rango, $sede),
        ]);
    }

    /**
     * /estadisticas/turnos: citas y turnos de recepción por sede y por día,
     * semana o mes: atendidas, no acudieron, sin cerrar, atenciones largas y
     * lo mismo de los casos de excepción (App\Support\EstadisticasTurnos).
     */
    public function turnos(Request $request)
    {
        return view('estadisticas.turnos.index', EstadisticasTurnos::armar(EstadisticasTurnos::filtros($request), $request->user()->name));
    }

    /** Lo mismo de /estadisticas/turnos en PDF, Excel o imagen (PNG). */
    public function turnosDescargar(Request $request)
    {
        $formato = $request->validate(['formato' => ['required', Rule::in(EstadisticasTurnos::FORMATOS)]])['formato'];
        $datos   = EstadisticasTurnos::armar(EstadisticasTurnos::filtros($request), $request->user()->name);

        return match ($formato) {
            'xlsx' => Excel::download(new EstadisticasTurnosExport($datos), $datos['archivo'].'.xlsx'),
            'pdf'  => Pdf::loadView('estadisticas.turnos.pdf', $datos)
                ->setPaper('letter', 'landscape')
                ->setOption('isPhpEnabled', true)
                ->download($datos['archivo'].'.pdf'),
            'png'  => view('estadisticas.turnos.imagen', $datos),
        };
    }

    /** Casos por motivo y por mes, para abrir en Excel (CSV con BOM). */
    public function excepcionesMotivosCsv(Request $request)
    {
        [$filtros, $rango, $sede] = $this->filtrosExcepciones($request);
        $datos = EstadisticasExcepcion::porMotivo($rango, $sede);
        $nombre = 'casos_excepcion_por_motivo_'.$filtros['desde'].'_'.$filtros['hasta'].($sede ? '_'.\Illuminate\Support\Str::slug($sede) : '').'.csv';

        return response()->streamDownload(function () use ($datos, $filtros, $sede) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Casos de excepción por motivo', 'Del '.$filtros['desde'].' al '.$filtros['hasta'], $sede ?: 'Todas las sedes']);
            fputcsv($out, array_merge(['Motivo', 'Grupo'], array_values($datos['meses']), ['Total']));
            foreach ($datos['filas'] as $f) {
                $fila = [$f['etiqueta'], $f['grupo']];
                foreach (array_keys($datos['meses']) as $mes) {
                    $fila[] = $f['meses'][$mes] ?? 0;
                }
                $fila[] = $f['total'];
                fputcsv($out, $fila);
            }
            fputcsv($out, array_merge(['Total', ''], array_map(fn ($m) => array_sum(array_map(fn ($f) => $f['meses'][$m] ?? 0, $datos['filas'])), array_keys($datos['meses'])), [$datos['total']]));
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: array, 1: array, 2: ?string} */
    private function filtrosExcepciones(Request $request): array
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
            'sede'  => ['nullable', Rule::in(CasosExcepcion::SEDES)],
        ]);
        $filtros['desde'] ??= ($filtros['hasta'] ?? null) ? $filtros['hasta'] : now()->startOfMonth()->toDateString();
        $filtros['hasta'] ??= now()->endOfMonth()->toDateString();
        if ($filtros['desde'] > $filtros['hasta']) {
            [$filtros['desde'], $filtros['hasta']] = [$filtros['hasta'], $filtros['desde']];
        }

        return [$filtros, EstadisticasExcepcion::rango($filtros['desde'], $filtros['hasta']), $filtros['sede'] ?? null];
    }

    /**
     * /estadisticas/incidencias: retrocesos, correcciones y borrados hechos a
     * mano en ratificaciones, solicitudes, audiencias y cumplimientos, con
     * quién, cuándo y por qué (App\Support\BitacoraIncidencias). Cuántas hay
     * por año, mes y día, y por usuario.
     */
    public function incidencias(Request $request)
    {
        $f = $this->filtrosIncidencias($request);
        [$desde, $hasta, $texto] = BitacoraIncidencias::periodo($f);
        $mesDias = $f['mes'] ?: ($f['anio'] === (int) now()->year ? (int) now()->month : 12);

        return view('estadisticas.incidencias', [
            'filtros'    => $f,
            'texto'      => $texto,
            'sedes'      => Sedes::orderBy('id')->pluck('nombre'),
            'usuarios'   => BitacoraIncidencias::usuarios(),
            'anios'      => range((int) now()->year, 2024),
            'cifras'     => BitacoraIncidencias::cifras($f),
            'porMes'     => BitacoraIncidencias::porMes($f['anio'], $f),
            'porDia'     => BitacoraIncidencias::porDia($f['anio'], $mesDias, $f),
            'mesDias'    => $mesDias,
            'porArea'    => BitacoraIncidencias::porArea($desde, $hasta, $f),
            'porAccion'  => BitacoraIncidencias::porAccion($desde, $hasta, $f),
            'porUsuario' => BitacoraIncidencias::porUsuario($desde, $hasta, $f),
            'porSede'    => $f['sede'] ? [] : BitacoraIncidencias::porSede($desde, $hasta, $f),
            'registro'   => BitacoraIncidencias::registro($desde, $hasta, $f),
            'marcadas'   => BitacoraIncidencias::marcadas(),
        ]);
    }

    public function incidenciasCsv(Request $request)
    {
        $f = $this->filtrosIncidencias($request);
        [$desde, $hasta, $texto] = BitacoraIncidencias::periodo($f);
        $filas  = BitacoraIncidencias::consultaRegistro($desde, $hasta, $f)->get();
        $nombre = 'incidencias_'.$desde->format('Y-m-d').'_'.$hasta->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($filas, $texto) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Incidencias', $texto]);
            fputcsv($out, ['Fecha', 'Área', 'Movimiento', 'Expediente', 'Sede', 'Estatus antes', 'Estatus después', 'Cambios', 'Motivo', 'Lo hizo']);
            foreach ($filas as $r) {
                fputcsv($out, [
                    Carbon::parse($r->created_at)->format('Y-m-d H:i'),
                    BitacoraIncidencias::AREAS[$r->area] ?? $r->area,
                    BitacoraIncidencias::ACCIONES[$r->accion] ?? $r->accion,
                    $r->NUE,
                    $r->delegacion,
                    $r->estatus_previo,
                    $r->estatus_nuevo,
                    implode('; ', BitacoraIncidencias::cambios($r)),
                    $r->motivo,
                    $r->user_nombre,
                ]);
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Año (por omisión el actual), mes y día opcionales, y el resto de filtros. */
    private function filtrosIncidencias(Request $request): array
    {
        $hoy  = now();
        $anio = (int) $request->query('anio', $hoy->year);
        $anio = $anio >= 2024 && $anio <= $hoy->year ? $anio : (int) $hoy->year;
        $mes  = (int) $request->query('mes', 0);
        $mes  = $mes >= 1 && $mes <= 12 ? $mes : 0;
        $dia  = (string) $request->query('dia', '');

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dia) && strtotime($dia) && substr($dia, 0, 4) >= 2024) {
            $d    = Carbon::parse($dia);
            $anio = (int) $d->year;
            $mes  = (int) $d->month;
            $dia  = $d->toDateString();
        } else {
            $dia = '';
        }

        $sedes   = Sedes::pluck('nombre')->all();
        $usuario = (int) $request->query('usuario', 0);

        return [
            'anio'    => $anio,
            'mes'     => $mes,
            'dia'     => $dia,
            'area'    => array_key_exists((string) $request->query('area'), BitacoraIncidencias::AREAS) ? (string) $request->query('area') : '',
            'accion'  => array_key_exists((string) $request->query('accion'), BitacoraIncidencias::ACCIONES) ? (string) $request->query('accion') : '',
            'usuario' => max(0, $usuario),
            'sede'    => in_array($request->query('sede'), $sedes, true) ? (string) $request->query('sede') : '',
            'q'       => Str::limit(trim((string) $request->query('q', '')), 60, ''),
        ];
    }
}
