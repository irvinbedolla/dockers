<?php

namespace App\Support;

use App\Models\DatoReferencia;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Datos de referencia del Reporte estadístico (/estadisticas/datos-referencia):
 * lo que el reporte necesita y no sale del SICONCILIO. Cambian poco y los
 * captura Estadística (o Super Usuario / Directivo).
 *
 * Cada dato del CATÁLOGO es de uno de dos tipos:
 *   - 'campos': un grupo de valores sueltos (p. ej. los totales del histórico).
 *   - 'tabla':  renglones con las mismas columnas (p. ej. conciliadores por
 *               estado). Con 'fijas' => true no se agregan ni quitan renglones.
 *
 * El 'valor' del catálogo es el inicial (tomado del reporte de septiembre
 * 2026); lo capturado se guarda en la tabla datos_referencia y lo sustituye.
 * Para agregar un dato nuevo basta con sumarlo aquí y usarlo en
 * App\Support\ReporteEstadistico.
 *
 * Tipos de columna/campo: entero, anio, decimal, moneda, fecha, texto, parrafo, sino, fijo.
 * En los párrafos, **así** sale en negritas.
 */
class DatosReferencia
{
    public const GRUPOS = [
        'historico'  => ['Histórico del Centro', 'bi-clock-history', 'Lo que se reportó antes de que el sistema llevara la cuenta. El reporte le suma lo que registra el SICONCILIO desde la fecha de corte.'],
        'externos'   => ['Comparativos', 'bi-bar-chart-line', 'Junta Local y presupuesto: datos de otras dependencias.'],
        'estados'    => ['Conciliadores por estado', 'bi-geo-alt', 'Población, PEA y conciliadores de cada entidad (tercera etapa marcada).'],
        'territorio' => ['Población y empresas', 'bi-map', 'Las cifras que aparecen en el mapa de delegaciones.'],
        'textos'     => ['Textos del reporte', 'bi-chat-left-text', 'Notas y conclusión. Escribe **así** para que salga en negritas; un renglón por viñeta.'],
    ];

    /** @return array<string, array> */
    public static function catalogo(): array
    {
        return [
            // ------------------------------------------------------------ histórico
            'corte' => [
                'grupo' => 'historico', 'tipo' => 'campos',
                'titulo' => 'Fecha de corte',
                'ayuda' => 'Hasta el día anterior a esta fecha manda el histórico capturado abajo; desde esta fecha, el sistema suma lo que registra.',
                'campos' => [
                    'fecha_corte'   => ['El sistema suma desde', 'fecha'],
                    'inicio_texto'  => ['El histórico empieza en', 'texto'],
                ],
                'valor' => ['fecha_corte' => '2026-10-01', 'inicio_texto' => 'Octubre 2022'],
            ],
            'historico_totales' => [
                'grupo' => 'historico', 'tipo' => 'campos',
                'titulo' => 'Histórica: totales hasta el corte',
                'ayuda' => 'Lámina "Histórica". Convenios incluye ratificaciones.',
                'campos' => [
                    'personas'    => ['Personas atendidas', 'entero'],
                    'asesorias'   => ['Asesorías', 'entero'],
                    'solicitudes' => ['Solicitudes confirmadas', 'entero'],
                    'audiencias'  => ['Audiencias programadas', 'entero'],
                    'constancias' => ['Constancias de no conciliación', 'entero'],
                    'convenios'   => ['Convenios (con ratificaciones)', 'entero'],
                    'archivadas'  => ['Archivadas por falta de interés', 'entero'],
                    'montos'      => ['Montos convenidos', 'moneda'],
                ],
                'valor' => [
                    'personas' => 177080, 'asesorias' => 81247, 'solicitudes' => 44902, 'audiencias' => 57944,
                    'constancias' => 10579, 'convenios' => 27511, 'archivadas' => 6110, 'montos' => 917780008.37,
                ],
            ],
            'historico_delegaciones' => [
                'grupo' => 'historico', 'tipo' => 'tabla', 'fijas' => true,
                'titulo' => 'Histórico por delegación hasta el corte',
                'ayuda' => 'Lámina "Histórico desglosado por delegación". Cada delegación incluye su oficina de apoyo.',
                'columnas' => [
                    'delegacion'     => ['Delegación', 'fijo'],
                    'solicitudes'    => ['Solicitudes confirmadas', 'entero'],
                    'incompetencias' => ['Incompet.', 'entero'],
                    'convenios'      => ['Convenios', 'entero'],
                    'ratificaciones' => ['Ratificaciones', 'entero'],
                    'constancias'    => ['Constancias', 'entero'],
                    'archivadas'     => ['Archivadas', 'entero'],
                ],
                'valor' => [
                    ['delegacion' => 'Morelia', 'solicitudes' => 23579, 'incompetencias' => 23, 'convenios' => 9720, 'ratificaciones' => 5011, 'constancias' => 5855, 'archivadas' => 2956],
                    ['delegacion' => 'Uruapan', 'solicitudes' => 11133, 'incompetencias' => 5, 'convenios' => 4022, 'ratificaciones' => 2691, 'constancias' => 2265, 'archivadas' => 1824],
                    ['delegacion' => 'Zamora',  'solicitudes' => 10200, 'incompetencias' => 3, 'convenios' => 3362, 'ratificaciones' => 2704, 'constancias' => 2461, 'archivadas' => 1333],
                ],
            ],
            'audiencias_anio' => [
                'grupo' => 'historico', 'tipo' => 'tabla',
                'titulo' => 'Audiencias por año hasta el corte',
                'ayuda' => 'Lámina "Audiencias por conciliador". El año del corte lleva sólo lo previo al corte; el sistema le suma el resto.',
                'columnas' => [
                    'anio'       => ['Año', 'anio'],
                    'etiqueta'   => ['Cómo se muestra', 'texto'],
                    'audiencias' => ['Audiencias', 'entero'],
                ],
                'valor' => [
                    ['anio' => 2022, 'etiqueta' => '2022 (Octubre-Diciembre)', 'audiencias' => 1735],
                    ['anio' => 2023, 'etiqueta' => '2023', 'audiencias' => 9073],
                    ['anio' => 2024, 'etiqueta' => '2024', 'audiencias' => 12815],
                    ['anio' => 2025, 'etiqueta' => '2025', 'audiencias' => 17095],
                    ['anio' => 2026, 'etiqueta' => '2026', 'audiencias' => 16850],
                ],
            ],
            'conciliadores_ccl' => [
                'grupo' => 'historico', 'tipo' => 'campos',
                'titulo' => 'Conciliadores del Centro',
                'ayuda' => 'Con este número se saca el promedio de audiencias por conciliador.',
                'campos' => ['total' => ['Total de conciliadores', 'entero']],
                'valor' => ['total' => 12],
            ],

            // ------------------------------------------------------------ comparativos
            'jlca_ccl' => [
                'grupo' => 'externos', 'tipo' => 'tabla',
                'titulo' => 'Asuntos por año: Junta Local vs. Centro',
                'ayuda' => 'JLCA son demandas; CCL, solicitudes. Deja vacío el año en que no aplica.',
                'columnas' => [
                    'anio' => ['Año', 'anio'],
                    'jlca' => ['JLCA (demandas)', 'entero'],
                    'ccl'  => ['CCL (solicitudes)', 'entero'],
                ],
                'valor' => [
                    ['anio' => 2016, 'jlca' => 6367, 'ccl' => null],
                    ['anio' => 2017, 'jlca' => 5754, 'ccl' => null],
                    ['anio' => 2018, 'jlca' => 5456, 'ccl' => null],
                    ['anio' => 2019, 'jlca' => 5576, 'ccl' => null],
                    ['anio' => 2020, 'jlca' => 4843, 'ccl' => null],
                    ['anio' => 2021, 'jlca' => 4206, 'ccl' => null],
                    ['anio' => 2022, 'jlca' => 2110, 'ccl' => 1668],
                    ['anio' => 2023, 'jlca' => null, 'ccl' => 9073],
                    ['anio' => 2024, 'jlca' => null, 'ccl' => 11155],
                    ['anio' => 2025, 'jlca' => null, 'ccl' => 12243],
                ],
            ],
            'presupuesto' => [
                'grupo' => 'externos', 'tipo' => 'tabla',
                'titulo' => 'Presupuesto por ejercicio fiscal',
                'ayuda' => 'El reporte calcula la diferencia entre ambos y el aumento del último año.',
                'columnas' => [
                    'anio' => ['Año', 'anio'],
                    'jlca' => ['Junta Local (JLCA)', 'moneda'],
                    'ccl'  => ['Centro (CCL)', 'moneda'],
                ],
                'valor' => [
                    ['anio' => 2024, 'jlca' => 73400359, 'ccl' => 36458554],
                    ['anio' => 2025, 'jlca' => 77875692, 'ccl' => 37093095],
                    ['anio' => 2026, 'jlca' => 84928957, 'ccl' => 38391353],
                ],
            ],

            // ------------------------------------------------------------ estados
            'estados' => [
                'grupo' => 'estados', 'tipo' => 'tabla',
                'titulo' => 'Conciliadores y PEA por entidad',
                'ayuda' => 'PEA = población económicamente activa. Marca "Sí" en tercera etapa para que salga en las gráficas de crecimiento; población e inicio sólo hacen falta en esas.',
                'columnas' => [
                    'estado'    => ['Entidad', 'texto'],
                    'tercera'   => ['Tercera etapa', 'sino'],
                    'poblacion' => ['Población', 'entero'],
                    'inicio'    => ['Conciliadores al inicio', 'entero'],
                    'actuales'  => ['Conciliadores actuales', 'entero'],
                    'pea'       => ['PEA', 'entero'],
                ],
                'valor' => self::estadosIniciales(),
            ],

            // ------------------------------------------------------------ territorio
            'poblacion_sedes' => [
                'grupo' => 'territorio', 'tipo' => 'tabla', 'fijas' => true,
                'titulo' => 'Población y empresas por delegación y oficina',
                'ayuda' => 'Aparecen junto a cada sede en el mapa.',
                'columnas' => [
                    'sede'      => ['Sede', 'fijo'],
                    'poblacion' => ['Población', 'entero'],
                    'empresas'  => ['Empresas', 'entero'],
                ],
                'valor' => [
                    ['sede' => 'Morelia',          'poblacion' => 849053, 'empresas' => 52775],
                    ['sede' => 'Zitácuaro',        'poblacion' => 157056, 'empresas' => 8580],
                    ['sede' => 'Uruapan',          'poblacion' => 356786, 'empresas' => 26719],
                    ['sede' => 'Lázaro Cárdenas',  'poblacion' => 196003, 'empresas' => 10684],
                    ['sede' => 'Zamora',           'poblacion' => 204860, 'empresas' => 11725],
                    ['sede' => 'Sahuayo',          'poblacion' => 78477,  'empresas' => 8398],
                ],
            ],

            // ------------------------------------------------------------ textos
            'textos' => [
                'grupo' => 'textos', 'tipo' => 'campos',
                'titulo' => 'Notas de las láminas',
                'ayuda' => 'Un renglón por viñeta.',
                'campos' => [
                    'crecimiento'     => ['Crecimiento de conciliadores', 'parrafo'],
                    'pea'             => ['PEA por estado (viñetas)', 'parrafo'],
                    'pea_conciliador' => ['PEA por conciliador (viñetas)', 'parrafo'],
                    'conclusion_intro'=> ['Conclusión: entrada', 'parrafo'],
                    'conclusion'      => ['Conclusión (viñetas)', 'parrafo'],
                ],
                'valor' => [
                    'crecimiento' => 'Estados como **CDMX, Jalisco y Nuevo León** han incrementado notablemente el número de conciliadores desde el inicio, han duplicado o incluso triplicado su número inicial. En contraste, **Michoacán no ha aumentado su plantilla**, manteniéndose en 12 conciliadores desde el inicio, siendo el único estado que no ha aumentado su número de conciliadores.',
                    'pea' => "**CDMX** lidera con más de 5 millones de personas en la PEA, le siguen Jalisco, Guerrero, Nuevo León y Michoacán.\nMichoacán tiene una PEA considerable (2.29 millones), por encima del promedio nacional en esta tabla. **A pesar de esta alta demanda potencial de servicios, no ha habido un incremento de conciliadores en Michoacán**.\nEl estado con menor PEA es Nayarit.",
                    'pea_conciliador' => '**Michoacán** tiene el segundo lugar con mayor carga laboral por conciliador (191,338 personas), lo que indica la **urgente necesidad de más conciliadores**, este dato **triplica o cuadruplica** la carga que tienen conciliadores en estados como **Coahuila**.',
                    'conclusion_intro' => 'Michoacán enfrenta una situación crítica en términos de carga de trabajo y atención ciudadana:',
                    'conclusion' => "A pesar de tener una alta demanda de conciliación laboral, no ha reforzado su equipo de conciliadores. Esto contrasta con estados como CDMX (de 32 a 84 conciliadores) y Jalisco (de 23 a 43), que han respondido a su crecimiento poblacional.\nLa sobrecarga de trabajo en Michoacán no solo pone en riesgo la calidad de los servicios, sino también el cumplimiento de plazos y atención efectiva a los ciudadanos, comprometiendo la eficiencia del servicio y la atención al público.\nResulta urgente considerar una ampliación de la plantilla para equilibrar la demanda y asegurar la calidad de la conciliación laboral en el estado.",
                ],
            ],
        ];
    }

    /** Las 32 entidades del reporte de septiembre 2026. */
    private static function estadosIniciales(): array
    {
        // [entidad, tercera etapa, población, conciliadores al inicio, actuales, PEA]
        $filas = [
            ['Michoacán', true, 4748846, 12, 12, 2296059],
            ['Guerrero', true, 3540685, 9, 12, 1533997],
            ['Sinaloa', true, 3026943, 6, 13, 1481009],
            ['Yucatán', true, 2320898, 5, 18, 1239857],
            ['Jalisco', true, 8348151, 23, 43, 3992241],
            ['Tamaulipas', true, 3527735, 16, 25, 1690458],
            ['Nuevo León', true, 5784442, 28, 34, 2926208],
            ['Chihuahua', true, 3741869, 21, 27, 1817473],
            ['Nayarit', true, 1235456, 8, 7, 644192],
            ['CDMX', true, 9209944, 32, 84, 5066685],
            ['Sonora', true, 2944840, 28, 26, 1425411],
            ['Coahuila', true, 3146771, 35, 30, 1572146],
            ['San Luis Potosí', false, null, null, 3, 1285696],
            ['Oaxaca', false, null, null, 5, 1829474],
            ['Zacatecas', false, null, null, 3, 670058],
            ['Puebla', false, null, null, 14, 3092122],
            ['Chiapas', false, null, null, 11, 2309506],
            ['Tlaxcala', false, null, null, 4, 692989],
            ['Tabasco', false, null, null, 7, 1121839],
            ['Durango', false, null, null, 8, 873367],
            ['Veracruz', false, null, null, 34, 3486109],
            ['Hidalgo', false, null, null, 15, 1522196],
            ['Estado de México', false, null, null, 82, 8230321],
            ['Colima', false, null, null, 4, 372980],
            ['Aguascalientes', false, null, null, 10, 683487],
            ['Guanajuato', false, null, null, 42, 2859767],
            ['Querétaro', false, null, null, 18, 1216822],
            ['Morelos', false, null, null, 18, 888797],
            ['Baja California', false, null, null, 39, 1800869],
            ['Campeche', false, null, null, 14, 449083],
            ['Baja California Sur', false, null, null, 16, 463281],
            ['Quintana Roo', false, null, null, 35, 995271],
        ];

        return array_map(fn ($f) => array_combine(['estado', 'tercera', 'poblacion', 'inicio', 'actuales', 'pea'], $f), $filas);
    }

    /** @var array<string, DatoReferencia>|null */
    private static ?array $guardados = null;

    /** Lo capturado (si la tabla ya existe), por clave. */
    private static function guardados(): array
    {
        if (self::$guardados === null) {
            self::$guardados = Schema::hasTable('datos_referencia')
                ? DatoReferencia::with('editor:id,name')->get()->keyBy('clave')->all()
                : [];
        }

        return self::$guardados;
    }

    /** Valor vigente de un dato: lo capturado o, si no hay, el inicial. */
    public static function valor(string $clave): mixed
    {
        $def = self::catalogo()[$clave] ?? null;
        if (! $def) {
            return null;
        }
        $guardado = self::guardados()[$clave] ?? null;
        if (! $guardado || ! is_array($guardado->valor)) {
            return $def['valor'];
        }

        // Si después se agrega un campo al catálogo, toma su valor inicial.
        return $def['tipo'] === 'campos' ? $guardado->valor + $def['valor'] : $guardado->valor;
    }

    /** Para la pantalla: catálogo agrupado con valor y quién lo cambió. */
    public static function paraEditar(): array
    {
        $grupos = [];
        foreach (self::catalogo() as $clave => $def) {
            $g = self::guardados()[$clave] ?? null;
            $grupos[$def['grupo']][$clave] = $def + [
                'actual'    => self::valor($clave),
                'editado'   => $g?->updated_at,
                'editor'    => $g?->editor?->name,
            ];
        }

        return $grupos;
    }

    /** Valida y guarda un dato capturado en la pantalla. */
    public static function guardar(string $clave, array $entrada, ?int $usuario): void
    {
        $def = self::catalogo()[$clave] ?? abort(404);

        if ($def['tipo'] === 'campos') {
            $valor = [];
            foreach ($def['campos'] as $campo => [$etiqueta, $tipo]) {
                $valor[$campo] = self::limpiar($entrada['campos'][$campo] ?? null, $tipo, "campos.$campo", $etiqueta, true);
            }
        } else {
            $valor = [];
            $filas = array_values($entrada['filas'] ?? []);
            if (! empty($def['fijas'])) {
                // Las filas fijas no cambian de nombre ni de número.
                $filas = array_map(fn ($i) => (array) ($filas[$i] ?? []), array_keys($def['valor']));
            }
            foreach ($filas as $i => $fila) {
                $limpia = [];
                $vacia = true;
                foreach ($def['columnas'] as $col => [$etiqueta, $tipo]) {
                    if ($tipo === 'fijo') {
                        $limpia[$col] = $def['valor'][$i][$col] ?? '';
                        continue;
                    }
                    $limpia[$col] = self::limpiar($fila[$col] ?? null, $tipo, "filas.$i.$col", $etiqueta.' (renglón '.($i + 1).')', false);
                    if ($limpia[$col] !== null && $limpia[$col] !== '' && $limpia[$col] !== false) {
                        $vacia = false;
                    }
                }
                if (! $vacia || ! empty($def['fijas'])) {
                    $valor[] = $limpia;
                }
            }
        }

        DatoReferencia::updateOrCreate(['clave' => $clave], ['valor' => $valor, 'updated_by' => $usuario]);
        self::$guardados = null;
    }

    private static function limpiar(mixed $v, string $tipo, string $campo, string $etiqueta, bool $obligatorio): mixed
    {
        $v = is_string($v) ? trim($v) : $v;
        $falla = fn (string $msg) => throw ValidationException::withMessages([$campo => "$etiqueta: $msg"]);

        if ($tipo === 'sino') {
            return in_array($v, ['1', 1, true, 'on', 'si', 'sí'], true);
        }
        if ($v === null || $v === '') {
            if ($obligatorio && in_array($tipo, ['fecha', 'entero'], true)) {
                $falla('no puede quedar vacío.');
            }

            return null;
        }

        return match ($tipo) {
            'entero', 'anio' => (($n = str_replace([',', ' '], '', (string) $v)) !== '' && ctype_digit($n)) ? (int) $n : $falla('escribe un número entero sin decimales.'),
            'decimal', 'moneda' => is_numeric($n = str_replace([',', ' ', '$'], '', (string) $v)) && (float) $n >= 0 ? round((float) $n, 2) : $falla('escribe una cantidad válida.'),
            'fecha' => self::fecha($v) ?? $falla('la fecha no es válida.'),
            'texto' => mb_strlen($v) <= 120 ? $v : $falla('máximo 120 caracteres.'),
            'parrafo' => mb_strlen($v) <= 3000 ? str_replace("\r\n", "\n", $v) : $falla('máximo 3,000 caracteres.'),
            default => $v,
        };
    }

    private static function fecha(string $v): ?string
    {
        return preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $v : null;
    }

    /** **negritas** → <b>, con lo demás escapado. */
    public static function conNegritas(?string $texto): string
    {
        return preg_replace('/\*\*(.+?)\*\*/s', '<b>$1</b>', e((string) $texto));
    }

    /** Un renglón por viñeta, sin renglones vacíos. */
    public static function vinetas(?string $texto): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $texto)), fn ($l) => $l !== ''));
    }
}
