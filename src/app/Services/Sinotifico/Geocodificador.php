<?php

namespace App\Services\Sinotifico;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Ubica domicilios y los guarda en `sinotifico_domicilios`.
 *
 * Orden de confianza de una ubicación:
 *   campo     GPS del notificador en una diligencia exitosa validada;
 *   manual    un supervisor movió el pin en el mapa;
 *   osm       OpenStreetMap encontró la calle y el número;
 *   osm_aprox OpenStreetMap sólo encontró la colonia o el municipio.
 */
class Geocodificador
{
    public const FUENTES_PRECISAS = ['campo', 'manual', 'osm'];

    private static float $ultimaConsulta = 0;

    private int $consultas = 0;

    /** @param  array{tipo_vialidad?:?string, calle?:?string, numero_exterior?:?string, numero_interior?:?string, colonia?:?string, cp?:?string, municipio?:?string, estado?:?string, texto?:string}  $d */
    public static function clave(array $d): string
    {
        $base = implode('|', array_map(
            fn ($v) => mb_strtoupper(preg_replace('/[^\p{L}\p{N}]+/u', '', (string) $v)),
            [$d['tipo_vialidad'] ?? '', $d['calle'] ?? '', $d['numero_exterior'] ?? '', $d['numero_interior'] ?? '',
                $d['colonia'] ?? '', $d['cp'] ?? '', $d['municipio'] ?? '', $d['estado'] ?? ''],
        ));

        return sha1($base);
    }

    /**
     * Devuelve el registro del domicilio (con lat/lng si ya se conoce). Si no
     * tiene ubicación y se permite, la busca en OpenStreetMap.
     */
    public function ubicar(array $d, bool $buscar = true): object
    {
        $clave = self::clave($d);
        $fila = DB::table('sinotifico_domicilios')->where('clave', $clave)->first();

        if (! $fila) {
            $id = DB::table('sinotifico_domicilios')->insertGetId([
                'clave' => $clave,
                'texto' => mb_substr((string) ($d['texto'] ?? ''), 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $fila = DB::table('sinotifico_domicilios')->find($id);
        }

        $intentoReciente = $fila->geocodificado_at && now()->diffInDays($fila->geocodificado_at, true) < 7;
        if ($fila->lat === null && $buscar && ! $intentoReciente && $this->puedeConsultar()) {
            $hallado = $this->buscarEnOsm($d);
            DB::table('sinotifico_domicilios')->where('id', $fila->id)->update(array_filter([
                'lat' => $hallado['lat'] ?? null,
                'lng' => $hallado['lng'] ?? null,
                'fuente' => $hallado['fuente'] ?? null,
            ], fn ($v) => $v !== null) + ['geocodificado_at' => now(), 'updated_at' => now()]);
            $fila = DB::table('sinotifico_domicilios')->find($fila->id);
        }

        return $fila;
    }

    /** Un supervisor movió el pin: esa ubicación manda (salvo una de campo posterior). */
    public function fijarManual(int $domicilioId, float $lat, float $lng, int $usuarioId): void
    {
        DB::table('sinotifico_domicilios')->where('id', $domicilioId)->update([
            'lat' => $lat, 'lng' => $lng, 'fuente' => 'manual', 'actualizado_por' => $usuarioId, 'updated_at' => now(),
        ]);
    }

    /** Diligencia exitosa validada con GPS: el domicilio queda confirmado en campo. */
    public function confirmarEnCampo(array $d, ?float $lat, ?float $lng, bool $exitosa): void
    {
        $fila = $this->ubicar($d, false);
        $cambios = [
            'validado_en_campo' => true,
            'notificaciones_exitosas' => $fila->notificaciones_exitosas + ($exitosa ? 1 : 0),
            'verificado_at' => now(),
            'updated_at' => now(),
        ];
        // El GPS de campo no pisa un pin que un supervisor acomodó a mano.
        if ($lat !== null && $lng !== null && $fila->fuente !== 'manual') {
            $cambios += ['lat' => $lat, 'lng' => $lng, 'fuente' => 'campo'];
        }
        DB::table('sinotifico_domicilios')->where('id', $fila->id)->update($cambios);
    }

    private function puedeConsultar(): bool
    {
        return config('sinotifico.geocodificador') === 'nominatim'
            && $this->consultas < (int) config('sinotifico.geocodificar_por_vez', 15);
    }

    /** @return array{lat: float, lng: float, fuente: string}|null */
    private function buscarEnOsm(array $d): ?array
    {
        $calle = trim(implode(' ', array_filter([$d['numero_exterior'] ?? null, $d['tipo_vialidad'] ?? null, $d['calle'] ?? null])));
        $base = ['format' => 'jsonv2', 'limit' => 1, 'countrycodes' => 'mx'];
        $lugar = array_filter(['city' => $d['municipio'] ?? null, 'state' => $d['estado'] ?? null, 'postalcode' => $d['cp'] ?? null]);

        $intentos = [
            [$base + ['street' => $calle] + $lugar, 'osm'],
            [$base + ['q' => implode(', ', array_filter([$calle, $d['colonia'] ?? null, $d['municipio'] ?? null, $d['estado'] ?? null, 'México']))], 'osm'],
            [$base + ['q' => implode(', ', array_filter([$d['colonia'] ?? null, $d['municipio'] ?? null, $d['estado'] ?? null, 'México']))], 'osm_aprox'],
        ];

        foreach ($intentos as [$parametros, $fuente]) {
            if (! $this->puedeConsultar()) {
                return null;
            }
            $r = $this->consultar($parametros);
            if ($r) {
                return ['lat' => (float) $r['lat'], 'lng' => (float) $r['lon'], 'fuente' => $fuente];
            }
        }

        return null;
    }

    private function consultar(array $parametros): ?array
    {
        // Nominatim pide máximo una consulta por segundo.
        $espera = 1.1 - (microtime(true) - self::$ultimaConsulta);
        if ($espera > 0 && ! app()->runningUnitTests()) {
            usleep((int) ($espera * 1_000_000));
        }
        self::$ultimaConsulta = microtime(true);
        $this->consultas++;

        try {
            $respuesta = Http::withHeaders(['User-Agent' => config('sinotifico.nominatim_agente'), 'Accept-Language' => 'es'])
                ->timeout(6)
                ->get(config('sinotifico.nominatim_url'), $parametros);

            return $respuesta->successful() ? ($respuesta->json()[0] ?? null) : null;
        } catch (\Throwable $e) {
            Log::warning('SíNotifico: no se pudo consultar OpenStreetMap', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
