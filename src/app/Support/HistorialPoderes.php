<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Historial de cambios de un poder (registro patronal, tabla abogados).
 *
 * Cada vez que se da de alta, se edita, se valida, se le agrega un
 * representante o se borra un poder se guarda una foto completa del registro
 * en historial_abogados, con quién (id_user) y cuándo (created_at, con
 * segundos). Aquí se comparan las fotos una contra la anterior para decir en
 * palabras qué cambió: lo usan el modal "Historial" de Poderes y la sección
 * Poderes de Incidencias (App\Support\BitacoraIncidencias).
 *
 *   accion   alta, edicion, validacion (Pendiente → Validado),
 *            representante (folio nuevo al agregar un representante;
 *            origen_abogado es el folio del que salió) o baja (lo que había
 *            justo antes de borrarlo). La llena el modelo HistorialAbogado.
 *   previo_id  la foto anterior del mismo folio.
 */
class HistorialPoderes
{
    public const TABLA = 'historial_abogados';

    /** Qué se compara y cómo se llama cada dato. */
    public const CAMPOS = [
        'estatus'                        => 'Estatus',
        'tipo'                           => 'Tipo de persona',
        'nombres_patronal'               => 'Nombre o razón social',
        'primer_apellido_patronal'       => 'Primer apellido',
        'segundo_apellido_patronal'      => 'Segundo apellido',
        'rfc_patronal'                   => 'RFC',
        'curp_patronal'                  => 'CURP',
        'sexo_patronal'                  => 'Sexo',
        'giroComercial'                  => 'Giro comercial',
        'email_patronal'                 => 'Correo',
        'telefono_patronal'              => 'Teléfono',
        'estado_patronal'                => 'Estado',
        'municipio_patronal'             => 'Municipio',
        'tipo_vialidad_patronal'         => 'Tipo de vialidad',
        'vialidad_patronal'              => 'Vialidad',
        'num_ext_patronal'               => 'Núm. exterior',
        'mun_int_patronal'               => 'Núm. interior',
        'colonia_patronal'               => 'Colonia',
        'cp_patronal'                    => 'Código postal',
        'reprecentante'                  => 'Con representante',
        'nombre_representante'           => 'Nombre del representante',
        'primer_apellido_representante'  => 'Primer apellido del representante',
        'segundo_apellido_representante' => 'Segundo apellido del representante',
        'curp_representante'             => 'CURP del representante',
        'sexo_representante'             => 'Sexo del representante',
        'correo_representante'           => 'Correo del representante',
        'numero_representante'           => 'Teléfono del representante',
        'tipo_documento_representante'   => 'Documento que acredita la personería',
        'descipcion_poder'               => 'Descripción del poder',
        'fechaRegistro'                  => 'Fecha de expedición',
        'fechaVigencia'                  => 'Vigencia',
        'tipo_identificacion'            => 'Identificación oficial',
        'num_identificacion'             => 'Núm. de identificación',
    ];

    /** Documentos: se dice si se subió, se reemplazó o se quitó. */
    public const DOCUMENTOS = [
        'ineDocumento'            => null, // acta constitutiva o identificación del empleador, según el tipo
        'representacionDocumento' => 'la identificación del representante',
        'cedulaDocumento'         => 'el documento que acredita la personería',
        'anexo_documeto'          => 'el anexo',
    ];

    /** Valores que en realidad son "no hay archivo". */
    private const SIN_ARCHIVO = ['', 'Sin anexo', 'Sin documento', 'Sin carta poder', 'PENDIENTE'];

    public const ACCIONES = [
        'alta'          => 'Alta',
        'en_linea'      => 'Registro en línea',
        'edicion'       => 'Edición',
        'validacion'    => 'Validación',
        'representante' => 'Representante agregado (folio nuevo)',
        'baja'          => 'Borrado',
    ];

    public static function existe(): bool
    {
        static $hay = null;

        return $hay ??= Schema::hasTable(self::TABLA);
    }

    /** ¿Ya corrió la migración de accion / origen_abogado? */
    public static function conAccion(): bool
    {
        static $hay = null;

        return $hay ??= self::existe() && Schema::hasColumn(self::TABLA, 'accion');
    }

    /**
     * Lo que se muestra en el modal: del cambio más reciente al más antiguo.
     *
     * @return array{folio: int, nombre: string, eventos: array}
     */
    public static function resumen(int $idAbogado): array
    {
        $fotos = self::existe()
            ? DB::table(self::TABLA.' as h')
                ->leftJoin('users as u', 'u.id', '=', 'h.id_user')
                ->where('h.id_abogado', $idAbogado)
                ->orderBy('h.id')
                ->get(['h.*', 'u.name as usuario_nombre'])
            : collect();

        $eventos = [];
        $anterior = null;
        foreach ($fotos as $foto) {
            $eventos[] = self::evento($foto, $anterior);
            $anterior = $foto;
        }

        // Folios que salieron de éste al agregarle un representante.
        if (self::conAccion()) {
            $hijos = DB::table(self::TABLA.' as h')
                ->leftJoin('users as u', 'u.id', '=', 'h.id_user')
                ->where('h.origen_abogado', $idAbogado)
                ->where('h.accion', 'representante')
                ->orderBy('h.id')
                ->get(['h.*', 'u.name as usuario_nombre']);
            foreach ($hijos as $h) {
                $eventos[] = [
                    'orden'   => $h->created_at.'#'.$h->id,
                    'fecha'   => self::fecha($h->created_at),
                    'usuario' => self::usuario($h),
                    'accion'  => 'Se agregó un representante',
                    'clave'   => 'representante',
                    'cambios' => ['Se creó el folio '.$h->id_abogado.' con '.self::nombreRepresentante($h).' como representante.'],
                ];
            }
        }

        usort($eventos, fn ($a, $b) => strcmp($b['orden'], $a['orden']));

        $ultima = $fotos->last();
        $actual = DB::table('abogados')->where('idAbogado', $idAbogado)->first();

        return [
            'folio'   => $idAbogado,
            'nombre'  => self::nombrePatron($actual ?? $ultima),
            'borrado' => $actual === null && $fotos->isNotEmpty(),
            'eventos' => array_map(fn ($e) => array_diff_key($e, ['orden' => 1]), $eventos),
        ];
    }

    /**
     * Qué cambió en una foto respecto a la anterior (para Incidencias). Se
     * guarda en memoria para no repetir consultas en la misma página.
     */
    public static function cambiosDe(int $historialId, bool $conEstatus = true): array
    {
        static $cache = [];
        $clave = $historialId.($conEstatus ? '' : '-s');
        if (isset($cache[$clave])) {
            return $cache[$clave];
        }

        $foto = DB::table(self::TABLA)->find($historialId);
        if (! $foto) {
            return $cache[$clave] = [];
        }
        $anterior = ! empty($foto->previo_id)
            ? DB::table(self::TABLA)->find($foto->previo_id)
            : DB::table(self::TABLA)->where('id_abogado', $foto->id_abogado)->where('id', '<', $foto->id)->orderByDesc('id')->first();

        $cambios = self::evento($foto, $anterior)['cambios'];
        if (! $conEstatus) {
            $cambios = array_values(array_filter($cambios, fn ($c) => ! str_starts_with($c, self::CAMPOS['estatus'].':')));
        }

        return $cache[$clave] = $cambios;
    }

    /** Razón social o nombre del patrón, para mostrar junto al folio. */
    public static function nombre(int $idAbogado): string
    {
        static $cache = [];
        if (! isset($cache[$idAbogado])) {
            $fila = DB::table('abogados')->where('idAbogado', $idAbogado)->first()
                ?? (self::existe() ? DB::table(self::TABLA)->where('id_abogado', $idAbogado)->orderByDesc('id')->first() : null);
            $cache[$idAbogado] = self::nombrePatron($fila);
        }

        return $cache[$idAbogado];
    }

    // ------------------------------------------------------------------

    private static function evento(object $foto, ?object $anterior): array
    {
        $accion = self::conAccion() ? ($foto->accion ?? null) : null;

        if ($accion === 'baja') {
            $clave   = 'baja';
            $cambios = ['Se borró el poder. Así estaba: '.self::nombrePatron($foto).', estatus '.($foto->estatus ?: '—').'.'];
        } elseif ($accion === 'representante') {
            $clave   = 'representante';
            $origen  = $foto->origen_abogado ?? null;
            $cambios = array_values(array_filter([
                $origen ? 'Se creó a partir del folio '.$origen.'.' : null,
                'Representante: '.self::nombreRepresentante($foto).'.',
            ]));
        } elseif ($anterior === null) {
            $clave   = empty($foto->id_user) ? 'en_linea' : 'alta';
            $cambios = ['Se registró '.self::nombrePatron($foto).($foto->estatus ? ' con estatus '.$foto->estatus : '').'.'];
        } else {
            $cambios = self::comparar($anterior, $foto);
            $clave   = ($anterior->estatus ?? null) === 'Pendiente' && ($foto->estatus ?? null) === 'Validado' ? 'validacion' : 'edicion';
            if ($cambios === []) {
                $cambios = ['Se guardó sin cambios.'];
            }
        }

        return [
            'orden'   => $foto->created_at.'#'.$foto->id,
            'fecha'   => self::fecha($foto->created_at),
            'usuario' => self::usuario($foto),
            'accion'  => self::ACCIONES[$clave],
            'clave'   => $clave,
            'cambios' => $cambios,
        ];
    }

    /** Lista en palabras de lo que cambió entre dos fotos. */
    public static function comparar(object $antes, object $despues): array
    {
        $cambios = [];

        foreach (self::CAMPOS as $campo => $nombre) {
            $a = self::normal($antes->{$campo} ?? null);
            $d = self::normal($despues->{$campo} ?? null);
            if ($a === $d) {
                continue;
            }
            if ($campo === 'descipcion_poder') {
                $cambios[] = 'Se modificó la descripción del poder.';
                continue;
            }
            $cambios[] = $nombre.': '.self::valor($campo, $a).' → '.self::valor($campo, $d);
        }

        foreach (self::DOCUMENTOS as $campo => $nombre) {
            $a = self::normal($antes->{$campo} ?? null);
            $d = self::normal($despues->{$campo} ?? null);
            if ($a === $d) {
                continue;
            }
            $nombre ??= ($despues->tipo ?? $antes->tipo ?? null) === 'Moral' ? 'el acta constitutiva' : 'la identificación del empleador';
            $sinAntes   = in_array($a, self::SIN_ARCHIVO, true);
            $sinDespues = in_array($d, self::SIN_ARCHIVO, true);
            $cambios[] = match (true) {
                $sinAntes && ! $sinDespues => 'Se subió '.$nombre.'.',
                ! $sinAntes && $sinDespues => 'Se quitó '.$nombre.'.',
                default                    => 'Se reemplazó '.$nombre.'.',
            };
        }

        return $cambios;
    }

    private static function normal($v): string
    {
        $v = trim((string) ($v ?? ''));

        // Las fechas pueden venir con hora ("2027-03-21 00:00:00").
        return preg_match('/^\d{4}-\d{2}-\d{2}( 00:00:00)?$/', $v) ? substr($v, 0, 10) : $v;
    }

    private static function valor(string $campo, string $v): string
    {
        if ($v === '') {
            return '—';
        }
        if (in_array($campo, ['fechaRegistro', 'fechaVigencia'], true) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return Carbon::parse($v)->format('d/m/Y');
        }
        if ($campo === 'estado_patronal' && ctype_digit($v)) {
            return self::catalogo('estados', (int) $v) ?? $v;
        }
        if ($campo === 'municipio_patronal' && ctype_digit($v)) {
            return self::catalogo('municipios', (int) $v) ?? $v;
        }

        return Str::limit($v, 60);
    }

    private static function catalogo(string $tabla, int $id): ?string
    {
        static $cache = [];
        $clave = $tabla.$id;
        if (! array_key_exists($clave, $cache)) {
            try {
                $cache[$clave] = DB::table($tabla)->where('id', $id)->value('nombre');
            } catch (\Throwable $e) {
                $cache[$clave] = null;
            }
        }

        return $cache[$clave];
    }

    private static function fecha($valor): string
    {
        return $valor ? Carbon::parse($valor)->format('d/m/Y H:i:s') : '—';
    }

    private static function usuario(object $foto): string
    {
        if (empty($foto->id_user)) {
            return 'Registro en línea (sin sesión)';
        }

        return $foto->usuario_nombre ?? DB::table('users')->where('id', $foto->id_user)->value('name') ?? 'Usuario '.$foto->id_user;
    }

    private static function nombrePatron(?object $fila): string
    {
        if (! $fila) {
            return 'Sin nombre';
        }
        $nombre = trim(preg_replace('/\s+/', ' ', ($fila->nombres_patronal ?? '').' '.($fila->primer_apellido_patronal ?? '').' '.($fila->segundo_apellido_patronal ?? '')));

        return $nombre !== '' ? $nombre : 'Sin nombre';
    }

    private static function nombreRepresentante(object $fila): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', ($fila->nombre_representante ?? '').' '.($fila->primer_apellido_representante ?? '').' '.($fila->segundo_apellido_representante ?? '')));

        return $nombre !== '' ? $nombre : 'sin nombre';
    }
}
