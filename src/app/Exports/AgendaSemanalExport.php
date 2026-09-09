<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Agenda del rango visible del calendario, una hoja por conciliador.
 *
 * Reemplaza el recorte manual que se venía haciendo sobre la salida de
 * AudienciasConciliadorExport: mismas seis columnas y mismo formato, pero
 * partido por conciliador y con la fila en blanco entre días.
 *
 * El alcance (sedes y conciliadores) lo resuelve quien construye esta clase
 * a partir de App\Support\AgendaContexto. Aquí no se decide qué puede ver
 * cada rol: se recibe ya acotado.
 *
 * Lo único que la clase decide por su cuenta es descartar a los conciliadores
 * con estatus distinto de 'Activo', para que el archivo tenga las mismas
 * hojas que pastillas hay en pantalla.
 */
class AgendaSemanalExport implements WithMultipleSheets
{
    /**
     * Una audiencia reagendada ya vive en otra fecha. Si no se excluye, la
     * que se movió dentro del mismo rango aparece dos veces.
     */
    private const ESTATUS_EXCLUIDOS = ['Reagendada', 'No conciliacion reagendada'];

    /**
     * @param string             $desde          fecha inicial inclusiva (Y-m-d)
     * @param string             $hasta          fecha final inclusiva (Y-m-d)
     * @param array<int, string> $sedes          delegaciones que el usuario puede ver
     * @param array<int, int>|null $conciliadores ids permitidos; null = sin
     *                                          filtro por persona (lo acotan las sedes)
     */
    public function __construct(
        private string $desde,
        private string $hasta,
        private array $sedes,
        private ?array $conciliadores,
    ) {
    }

    public function sheets(): array
    {
        $filas = $this->consultar();

        // Excel no acepta un libro sin hojas: si no hubo audiencias se entrega
        // una hoja vacía con los encabezados en lugar de un archivo corrupto.
        if ($filas->isEmpty()) {
            return [new AgendaConciliadorSheet('SIN AUDIENCIAS', collect())];
        }

        $usados = [];

        return $filas
            ->groupBy('conciliador_id')
            ->map(fn (Collection $delConciliador) => new AgendaConciliadorSheet(
                $this->tituloHoja($delConciliador->first()->conciliador, $usados),
                $delConciliador,
            ))
            ->values()
            ->all();
    }

    private function consultar(): Collection
    {
        if ($this->sedes === [] || $this->conciliadores === []) {
            return collect();
        }

        // El citado que se imprime es el primero de la solicitud. Los demás
        // registros de seer_citados son domicilios alternos y el "QUIEN O
        // QUIENES RESULTEN RESPONSABLES", que en la agenda estorban.
        $primerCitado = "(SELECT SUBSTRING_INDEX(GROUP_CONCAT("
            . "TRIM(CONCAT_WS(' ', c.nombre, c.primer_apellido, c.segundo_apellido)) "
            . "ORDER BY c.id ASC SEPARATOR '|'), '|', 1) "
            . "FROM seer_citados c WHERE c.id_solicitud = g.id) as citado";

        $primerSolicitante = "(SELECT s.nombre FROM seer_solicitante s "
            . "WHERE s.id_solicitud = g.id ORDER BY s.id ASC LIMIT 1) as trabajador";

        return DB::table('audiencias as a')
            ->join('seer_general as g', 'g.id', '=', 'a.id_solicitud')
            // El conciliador que atiende es el de la AUDIENCIA. Sólo cuando
            // viene vacía se cae al de la solicitud: si una audiencia se
            // reasigna, las dos columnas discrepan y la agenda del día es la
            // de quien está en la sala, no la de quien llevaba el expediente.
            ->join('users as u', fn ($join) => $join->on(
                DB::raw('u.id'),
                '=',
                DB::raw('COALESCE(NULLIF(a.id_conciliador, 0), g.conciliador_id)')
            ))
            ->whereBetween('a.fecha', [$this->desde, $this->hasta])
            ->whereIn('a.delegacion', $this->sedes)
            ->whereNotIn('a.estatus', self::ESTATUS_EXCLUIDOS)
            // Solo conciliadores vigentes, igual que el selector de la agenda.
            // Esto deja fuera del archivo las audiencias de quien ya fue dado
            // de baja, aunque se hayan celebrado dentro del rango.
            ->where('u.estatus', 'Activo')
            ->when($this->conciliadores !== null, fn ($q) => $q->whereIn('u.id', $this->conciliadores))
            ->select([
                DB::raw('u.id as conciliador_id'),
                DB::raw('u.name as conciliador'),
                DB::raw("DATE_FORMAT(a.fecha, '%Y-%m-%d') as fecha"),
                'a.hora',
                'g.NUE as nue',
                DB::raw($primerSolicitante),
                DB::raw($primerCitado),
            ])
            ->orderBy('u.name')
            ->orderBy('a.fecha')
            ->orderBy('a.hora')
            ->orderBy('g.NUE')
            ->get();
    }

    /**
     * Nombre de la pestaña. Excel topa en 31 caracteres, no admite : \ / ? * [ ]
     * y no deja repetir nombre dentro del libro.
     *
     * Hoy sale del primer nombre (DANIEL, NATALIA, JUAN…). Los apodos del
     * archivo que se hacía a mano —FANY por Estefanía, LUZI por Luz Ireri— no
     * se pueden deducir del nombre; el día que exista users.apodo, basta con
     * traerlo en el select y usarlo aquí antes del primer nombre.
     *
     * @param array<string, bool> $usados
     */
    private function tituloHoja(string $nombre, array &$usados): string
    {
        $base = Str::of($nombre)->trim()->explode(' ')->first() ?: 'CONCILIADOR';
        $base = Str::upper(Str::limit(preg_replace('/[:\\\\\/?*\[\]]/', '', $base), 28, ''));

        $titulo = $base;
        $n = 2;

        while (isset($usados[$titulo])) {
            $titulo = $base . ' ' . $n++;
        }

        $usados[$titulo] = true;

        return $titulo;
    }
}
