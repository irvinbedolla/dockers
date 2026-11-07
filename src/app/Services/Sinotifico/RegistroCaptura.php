<?php

namespace App\Services\Sinotifico;

use App\Models\Sinotifico\Diligencia;
use App\Models\User;
use App\Support\Sinotifico\Catalogos as C;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Recibe una diligencia capturada en el teléfono y la deja "por revisar".
 *
 * Es idempotente por `uuid`: con mala señal el teléfono puede reenviar la
 * misma captura; la segunda vez se responde con la que ya existe.
 */
class RegistroCaptura
{
    /** Campos del formulario de SICONCILIO que se guardan en `datos`. */
    public const CAMPOS = [
        'nombre_notificacion', 'relacion_notificacion', 'puesto', 'identificacion_notificacion', 'num_identificacion',
        'motivo_identificacion', 'medio', 'vialidad_notificacion', 'abundar_area', 'abundar_inmueble', 'firma',
        'problema_diligencia', 'especificar', 'giro_comercial', 'genero', 'edad_filiacion', 'altura', 'complexion',
        'tez', 'cabello', 'ojos', 'particulares', 'observaciones',
    ];

    public const MAX_FOTOS = 3;

    /**
     * @param  array<int, UploadedFile>  $fotos
     * @return array{0: Diligencia, 1: bool} la diligencia y si se creó ahora
     */
    public function registrar(User $notificador, array $captura, array $fotos, ?UploadedFile $firma): array
    {
        if ($existente = Diligencia::where('uuid', $captura['uuid'] ?? null)->first()) {
            abort_unless((int) $existente->notificador_id === (int) $notificador->id, 409, 'Esta captura pertenece a otra cuenta.');

            return [$existente, false];
        }

        $v = $this->validar($captura, $fotos, $firma);
        $citados = $this->citadosPropios($notificador, $v['citados']);

        $ocupados = DB::table('sinotifico_diligencia_citado as dc')
            ->join('sinotifico_diligencias as d', 'd.id', '=', 'dc.diligencia_id')
            ->whereIn('dc.citado_id', $citados->pluck('id'))
            ->where('d.estatus', Diligencia::POR_REVISAR)
            ->exists();
        if ($ocupados) {
            throw new HttpException(409, 'Ya enviaste una diligencia de este domicilio y está en revisión.');
        }

        $carpeta = 'sinotifico/evidencias/'.now()->format('Y/m').'/'.$v['uuid'];
        $guardados = [];

        try {
            return DB::transaction(function () use ($notificador, $v, $citados, $fotos, $firma, $carpeta, &$guardados) {
                $diligencia = Diligencia::create([
                    'uuid' => $v['uuid'],
                    'notificador_id' => $notificador->id,
                    'solicitud_id' => $citados->first()->id_solicitud,
                    'sede' => $notificador->delegacion,
                    'estatus' => Diligencia::POR_REVISAR,
                    'resultado' => $v['quien_atiende'] === 'EXHORTO' ? null : $v['resultado'],
                    'quien_atiende' => $v['quien_atiende'],
                    'datos' => $this->datosLimpios($v),
                    'llegada_at' => CarbonImmutable::parse($v['llegada_at'])->setTimezone(config('app.timezone')),
                    'capturada_at' => CarbonImmutable::parse($v['capturada_at'])->setTimezone(config('app.timezone')),
                    'lat' => Arr::get($v, 'ubicacion.lat'),
                    'lng' => Arr::get($v, 'ubicacion.lng'),
                    'precision_m' => ($p = Arr::get($v, 'ubicacion.precision')) !== null ? (int) round($p) : null,
                    'dispositivo' => mb_substr((string) ($v['dispositivo'] ?? ''), 0, 120) ?: null,
                ]);

                DB::table('sinotifico_diligencia_citado')->insert(
                    $citados->map(fn ($c) => ['diligencia_id' => $diligencia->id, 'citado_id' => $c->id])->all()
                );

                foreach (array_values($fotos) as $i => $foto) {
                    $ruta = $foto->storeAs($carpeta, 'foto-'.($i + 1).'.jpg', 'local');
                    $guardados[] = $ruta;
                    $meta = $v['fotos'][$i] ?? [];
                    $diligencia->evidencias()->create([
                        'tipo' => 'foto',
                        'orden' => $i + 1,
                        'ruta' => $ruta,
                        'bytes' => $foto->getSize(),
                        'lat' => $meta['lat'] ?? null,
                        'lng' => $meta['lng'] ?? null,
                        'tomada_at' => isset($meta['tomada_at']) ? CarbonImmutable::parse($meta['tomada_at'])->setTimezone(config('app.timezone')) : null,
                    ]);
                }

                if ($firma) {
                    $ruta = $firma->storeAs($carpeta, 'firma.png', 'local');
                    $guardados[] = $ruta;
                    $diligencia->evidencias()->create(['tipo' => 'firma', 'orden' => 1, 'ruta' => $ruta, 'bytes' => $firma->getSize()]);
                }

                return [$diligencia, true];
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($guardados);
            throw $e;
        }
    }

    private function validar(array $captura, array $fotos, ?UploadedFile $firma): array
    {
        $d = (array) ($captura['datos'] ?? []);
        $quien = $captura['quien_atiende'] ?? null;
        $resultado = $captura['resultado'] ?? null;
        $exhorto = $quien === 'EXHORTO';
        $alguien = in_array($quien, C::ATIENDE_ALGUIEN, true);
        $sinExito = in_array($resultado, C::SIN_EXITO, true);
        $sinIdentificacion = in_array($d['identificacion_notificacion'] ?? null, C::SIN_IDENTIFICACION, true);
        $problemas = $resultado === 'No exitosa no se constituye' ? C::PROBLEMAS_NO_SE_CONSTITUYE : C::PROBLEMAS_SE_CONSTITUYE;

        $reglas = [
            'uuid' => ['required', 'uuid'],
            'citados' => ['required', 'array', 'min:1', 'max:20'],
            'citados.*' => ['integer', 'distinct'],
            'quien_atiende' => ['required', Rule::in(C::QUIEN_ATIENDE)],
            'resultado' => [Rule::requiredIf(! $exhorto), 'nullable', Rule::in(C::RESULTADOS)],
            'llegada_at' => ['required', 'date'],
            'capturada_at' => ['required', 'date'],
            'ubicacion' => ['nullable', 'array'],
            'ubicacion.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'ubicacion.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'ubicacion.precision' => ['nullable', 'numeric', 'min:0'],
            'dispositivo' => ['nullable', 'string'],
            'fotos' => ['nullable', 'array', 'max:'.self::MAX_FOTOS],
            'fotos.*.lat' => ['nullable', 'numeric'],
            'fotos.*.lng' => ['nullable', 'numeric'],
            'fotos.*.tomada_at' => ['nullable', 'date'],
            'datos' => ['nullable', 'array'],
            'datos.medio' => [Rule::requiredIf(! $exhorto && ! $sinExito), 'nullable', 'array'],
            'datos.medio.*' => [Rule::in(C::MEDIOS)],
            'datos.abundar_area' => [Rule::requiredIf(! $exhorto), 'nullable', 'string', 'max:2000'],
            'datos.abundar_inmueble' => [Rule::requiredIf(! $exhorto), 'nullable', 'string', 'max:2000'],
            'datos.vialidad_notificacion' => ['nullable', Rule::in(C::VIALIDADES)],
            'datos.nombre_notificacion' => [Rule::requiredIf($alguien), 'nullable', 'string', 'max:191'],
            'datos.relacion_notificacion' => [Rule::requiredIf($alguien), 'nullable', Rule::in(C::RELACION)],
            'datos.puesto' => ['nullable', 'string', 'max:191'],
            'datos.identificacion_notificacion' => [Rule::requiredIf($alguien), 'nullable', Rule::in(C::IDENTIFICACION)],
            'datos.num_identificacion' => ['nullable', 'string', 'max:60'],
            'datos.motivo_identificacion' => [Rule::requiredIf($alguien && $sinIdentificacion), 'nullable', 'string', 'max:1000'],
            'datos.firma' => [Rule::requiredIf($alguien && ! $sinExito), 'nullable', Rule::in(C::FIRMA)],
            'datos.problema_diligencia' => [Rule::requiredIf(! $exhorto && $sinExito), 'nullable', Rule::in($problemas)],
            'datos.especificar' => [Rule::requiredIf(($d['problema_diligencia'] ?? null) === 'OTROS'), 'nullable', 'string', 'max:2000'],
            'datos.giro_comercial' => ['nullable', 'string', 'max:191'],
            'datos.genero' => [Rule::requiredIf($alguien && $sinIdentificacion), 'nullable', Rule::in(C::GENERO)],
            'datos.edad_filiacion' => [Rule::requiredIf($alguien && $sinIdentificacion), 'nullable', 'string', 'max:40'],
            'datos.complexion' => [Rule::requiredIf($alguien && $sinIdentificacion), 'nullable', 'string', 'max:60'],
            'datos.altura' => ['nullable', 'string', 'max:40'],
            'datos.tez' => ['nullable', 'string', 'max:60'],
            'datos.cabello' => ['nullable', 'string', 'max:60'],
            'datos.ojos' => ['nullable', 'string', 'max:60'],
            'datos.particulares' => ['nullable', 'string', 'max:500'],
            'datos.observaciones' => ['nullable', 'string', 'max:4000'],
        ];

        $v = Validator::make($captura, $reglas, [
            'required' => 'Falta capturar este dato.',
            'required_if' => 'Falta capturar este dato.',
            'in' => 'El valor elegido no es válido.',
            'datos.medio.required' => 'Marca al menos un medio de cercioramiento.',
            'citados.required' => 'Elige al menos un citado.',
        ], $this->nombres())->validate();

        $errores = [];
        if (! $exhorto && count($fotos) < 1) {
            $errores['fotos'] = 'Toma al menos una fotografía del domicilio.';
        }
        if (count($fotos) > self::MAX_FOTOS) {
            $errores['fotos'] = 'Se permiten hasta '.self::MAX_FOTOS.' fotografías.';
        }
        foreach ($fotos as $foto) {
            if (! $foto->isValid() || ! in_array($foto->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true) || $foto->getSize() > 8 * 1024 * 1024) {
                $errores['fotos'] = 'Una fotografía no es válida o pesa más de 8 MB.';
            }
        }
        if ($firma && (! $firma->isValid() || ! str_starts_with((string) $firma->getMimeType(), 'image/') || $firma->getSize() > 2 * 1024 * 1024)) {
            $errores['firma_imagen'] = 'La firma no es válida.';
        }
        if ($errores) {
            throw ValidationException::withMessages($errores);
        }

        return $v;
    }

    /** Sólo los campos que aplican según quién atendió y el resultado. */
    private function datosLimpios(array $v): array
    {
        $d = Arr::only((array) ($v['datos'] ?? []), self::CAMPOS);
        $d = array_map(fn ($x) => is_string($x) ? (trim($x) === '' ? null : trim($x)) : $x, $d);

        $quien = $v['quien_atiende'];
        $sinExito = in_array($v['resultado'] ?? null, C::SIN_EXITO, true);

        if (! in_array($quien, C::ATIENDE_ALGUIEN, true)) {
            foreach (['nombre_notificacion', 'relacion_notificacion', 'puesto', 'identificacion_notificacion', 'num_identificacion', 'motivo_identificacion', 'genero', 'edad_filiacion', 'altura', 'complexion', 'tez', 'cabello', 'ojos', 'particulares'] as $k) {
                $d[$k] = null;
            }
        }
        if ($quien === 'EXHORTO') {
            $d['medio'] = null;
        }
        if (! $sinExito) {
            $d['problema_diligencia'] = null;
        }
        if (($d['problema_diligencia'] ?? null) !== 'OTROS') {
            $d['especificar'] = null;
        }
        if (in_array($d['identificacion_notificacion'] ?? null, C::SIN_IDENTIFICACION, true)) {
            $d['num_identificacion'] = null;
        } else {
            $d['motivo_identificacion'] = null;
            foreach (['genero', 'edad_filiacion', 'altura', 'complexion', 'tez', 'cabello', 'ojos', 'particulares'] as $k) {
                $d[$k] = null;
            }
        }

        return array_merge(array_fill_keys(self::CAMPOS, null), $d);
    }

    /** Los citados deben ser del notificador, seguir pendientes y ser de una sola solicitud. */
    private function citadosPropios(User $notificador, array $ids)
    {
        $citados = DB::table('seer_citados')
            ->whereIn('id', $ids)
            ->where('id_notificador', $notificador->id)
            ->whereIn('estatus', config('sinotifico.estatus_pendientes'))
            ->whereIn('notificacion', config('sinotifico.notifica_el_centro'))
            ->get(['id', 'id_solicitud']);

        if ($citados->count() !== count(array_unique($ids))) {
            throw new HttpException(422, 'Alguno de los citados ya no está asignado a tu cuenta o ya se notificó. Actualiza tu lista.');
        }
        if ($citados->pluck('id_solicitud')->unique()->count() !== 1) {
            throw new HttpException(422, 'Los citados de una diligencia deben ser de la misma solicitud.');
        }

        return $citados;
    }

    private function nombres(): array
    {
        return [
            'quien_atiende' => 'quién atiende', 'resultado' => 'resultado', 'llegada_at' => 'hora de llegada',
            'datos.abundar_area' => 'descripción de la zona', 'datos.abundar_inmueble' => 'descripción del inmueble',
            'datos.nombre_notificacion' => 'nombre de quien atiende', 'datos.relacion_notificacion' => 'relación con el domicilio',
            'datos.identificacion_notificacion' => 'identificación', 'datos.motivo_identificacion' => 'motivo',
            'datos.firma' => 'firma', 'datos.problema_diligencia' => 'motivo', 'datos.especificar' => 'especificación',
            'datos.genero' => 'género', 'datos.edad_filiacion' => 'edad aproximada', 'datos.complexion' => 'complexión',
        ];
    }
}
