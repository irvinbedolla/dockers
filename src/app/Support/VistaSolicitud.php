<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Datos de una solicitud para verla en modo consulta (sin editar nada):
 * la usan Casos de excepción (excepciones/solicitud) y el Seguimiento del
 * caso (en un modal). La vista es solicitudes/_vista.
 */
class VistaSolicitud
{
    public static function datos(int $id): ?array
    {
        $general = DB::table('seer_general')->where('id', $id)->first();
        if (! $general) {
            return null;
        }

        $solicitantes = DB::table('seer_solicitante as s')
            ->leftJoin('municipios as m', 'm.id', '=', 's.municipio_domicilio')
            ->where('s.id_solicitud', $id)
            ->select('s.*', 'm.nombre as municipio')
            ->get();

        $citados = DB::table('seer_citados as c')
            ->leftJoin('municipios as m', 'm.id', '=', 'c.municipio_citado')
            ->where('c.id_solicitud', $id)
            ->select('c.*', 'm.nombre as municipio')
            ->get();

        $audiencias = DB::table('audiencias as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.id_conciliador')
            ->where('a.id_solicitud', $id)
            ->orderBy('a.fecha')->orderBy('a.hora')
            ->get(['a.numero_audiencia', 'a.folio_audiencia', 'a.fecha', 'a.hora', 'a.sala', 'a.estatus', 'u.name as conciliador']);

        $cita = $general->recepcion_id
            ? DB::table('recepcion')->where('id', $general->recepcion_id)->first()
            : null;

        $motivos = DB::table('seer_motivos as sm')
            ->join('catalogo_motivos as cm', 'cm.id', '=', 'sm.id_motivo')
            ->where('sm.id_solicitud', $id)
            ->pluck('cm.motivo')
            ->unique()->values()->all();

        return [
            'general'      => $general,
            'solicitantes' => $solicitantes,
            'citados'      => $citados,
            'audiencias'   => $audiencias,
            'cita'         => $cita,
            'motivos'      => $motivos,
            'rama'         => $general->id_rama ? DB::table('catalogo_rama')->where('id', $general->id_rama)->value('rama_industrial') : null,
            'conciliador'  => $general->conciliador_id ? DB::table('users')->where('id', $general->conciliador_id)->value('name') : null,
            'capturo'      => $general->user_id ? DB::table('users')->where('id', $general->user_id)->value('name') : null,
        ];
    }
}
