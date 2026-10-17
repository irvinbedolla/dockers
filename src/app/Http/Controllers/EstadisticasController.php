<?php

namespace App\Http\Controllers;

use App\Models\Sedes;
use App\Models\User;
use App\Support\ConveniosDelMes;
use App\Support\ConveniosPagados;
use App\Support\CasosExcepcion;
use App\Support\DesempenoNotificadores;
use App\Support\EstadisticasExcepcion;
use App\Support\ResumenSedes;
use App\Support\TasaConciliacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            // Tarjeta de la sección de notificaciones: cifras del mes.
            'notificacionesMes' => DesempenoNotificadores::resumen(
                DesempenoNotificadores::base(),
                DesempenoNotificadores::rango(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString())
            ),
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

        $rango = EstadisticasExcepcion::rango($filtros['desde'], $filtros['hasta']);
        $sede  = $filtros['sede'] ?? null;

        return view('estadisticas.excepciones', [
            'filtros'       => $filtros,
            'migrado'       => CasosExcepcion::conFormato(),
            'resumen'       => EstadisticasExcepcion::resumen($rango, $sede),
            'tendencia'     => EstadisticasExcepcion::tendencia($rango, $sede),
            'instituciones' => EstadisticasExcepcion::porJson('instituciones', CasosExcepcion::INSTITUCIONES, $rango, $sede, 'canaliza'),
            'tipos'         => EstadisticasExcepcion::porJson('tipos', CasosExcepcion::TIPOS, $rango, $sede),
            'porSede'       => $sede ? collect() : EstadisticasExcepcion::porSede($rango),
            'generos'       => EstadisticasExcepcion::porColumna('genero', ['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'], $rango, $sede),
            'vulnerables'   => EstadisticasExcepcion::porColumna('vulnerables', ['LGBTTTIQ' => 'LGBTTTIQ+'], $rango, $sede),
            'frecuencias'   => EstadisticasExcepcion::porColumna('frecuencia', CasosExcepcion::FRECUENCIAS, $rango, $sede),
            'esPanel'       => $request->user()->hasAnyRole(['Super Usuario', 'Estadistica', 'Directivo']),
        ]);
    }
}
