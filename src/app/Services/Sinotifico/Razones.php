<?php

namespace App\Services\Sinotifico;

use App\Http\Controllers\SeerController;
use App\Models\SeerCitados;
use App\Models\Sinotifico\Diligencia;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Módulo 3: razones de notificación.
 *
 * El PDF es el mismo que da SICONCILIO (mismas plantillas y mismas reglas
 * por estatus que Notificaciones → Historial); aquí se decide cuál toca, se
 * arma la vista previa para el supervisor y se registra la firma del
 * notificador. Firmar en la app estampa su firma sobre la línea del PDF
 * (PDF/Solicitudes/_firma_notificador); firmar en papel sólo lo registra.
 */
class Razones
{
    public const POR_FIRMAR = 'por_firmar';
    public const FIRMADA = 'firmada';

    /**
     * Estatus del citado → documento por tipo de notificación: [método de
     * SeerController, nombre]. Mismas reglas que Notificaciones → Historial.
     */
    public const DOCUMENTOS = [
        'Finalizado exitosamente' => [
            'Citatorio' => ['VerPDFRNotificacion', 'Razón de notificación'],
            'Multa' => ['VerPDFMultaNotificacion', 'Notificación de multa'],
        ],
        'Recibe pero no firma' => [
            'Citatorio' => ['VerPDFRNotificacion', 'Razón de notificación'],
            'Multa' => ['VerPDFMultaNotificacion', 'Notificación de multa'],
        ],
        'No notificada' => [
            'Citatorio' => ['PDFnotificadoInstructivo', 'Razón por instructivo'],
            'Multa' => ['VerPDFMultaInstructivo', 'Multa por instructivo'],
        ],
        'No exitosa se constituye' => [
            'Citatorio' => ['VerPDFNoExitConstituye', 'Razón de notificación no exitosa'],
            'Multa' => ['VerPDFMultaNoExitConstituye', 'Multa no exitosa'],
        ],
        'No exitosa no se constituye' => [
            'Citatorio' => ['PDFnotificadoNoexitosaNS', 'Razón de no localización'],
        ],
    ];

    /** Firmas ya leídas en esta petición (un PDF puede pedirla varias veces). */
    private static array $firmas = [];

    public static function permitePapel(): bool
    {
        return (bool) config('sinotifico.firma_en_papel', true);
    }

    /** [método, nombre] del documento que corresponde al citado, o null si no hay razón. */
    public static function documento(object $citado): ?array
    {
        return self::DOCUMENTOS[$citado->estatus][$citado->tipo_notificacion] ?? null;
    }

    /** Al validar: una razón por firmar por citado (si ya había una, se rehace). */
    public static function alValidar(Diligencia $d, Collection $citados): void
    {
        if ($d->quien_atiende === 'EXHORTO') {
            return; // pasa al notificador de exhortos: la razón será de su visita
        }
        foreach ($citados as $c) {
            if (! self::documento($c->fresh())) {
                continue;
            }
            DB::table('sinotifico_razones')->updateOrInsert(['citado_id' => $c->id], [
                'diligencia_id' => $d->id,
                'notificador_id' => $d->notificador_id,
                'estatus' => self::POR_FIRMAR,
                'modo' => null,
                'firmada_at' => null,
                'firma_ruta' => null,
                'papel_ruta' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        self::olvidar();
    }

    /** Bytes del PDF tal como lo genera SICONCILIO hoy. */
    public function pdf(SeerCitados $citado): string
    {
        $doc = self::documento($citado);
        abort_unless($doc, 404, 'Este citado todavía no tiene razón de notificación.');

        $respuesta = app(SeerController::class)->{$doc[0]}($citado->id, $citado->id_solicitud);
        $contenido = method_exists($respuesta, 'getContent') ? $respuesta->getContent() : null;
        abort_unless(is_string($contenido) && str_starts_with($contenido, '%PDF'), 404, 'No se pudo generar la razón.');

        return $contenido;
    }

    /**
     * Vista previa para el supervisor antes de validar: escribe la captura en
     * `seer_citados` dentro de una transacción, genera el PDF y lo revierte
     * todo (también las fotos, que van con nombre propio).
     */
    public function vistaPrevia(Diligencia $d, int $citadoId): string
    {
        abort_unless(in_array($citadoId, $d->citadoIds(), true), 404);
        if ($d->estatus !== Diligencia::POR_REVISAR) {
            return $this->pdf(SeerCitados::findOrFail($citadoId));
        }
        abort_if($d->quien_atiende === 'EXHORTO', 404, 'Un exhorto no lleva razón de este notificador.');

        $escritas = [];
        DB::beginTransaction();
        try {
            $citados = SeerCitados::whereIn('id', $d->citadoIds())->get();
            $municipios = DB::table('municipios')->whereIn('id', $citados->pluck('municipio_citado')->filter())->pluck('nombre', 'id');
            $escritas = app(RevisionDiligencia::class)->escribirEnCitados($d, $citados, $municipios, 'previa'.Str::random(6));

            return $this->pdf(SeerCitados::findOrFail($citadoId));
        } finally {
            DB::rollBack();
            Storage::disk('s3')->delete($escritas);
        }
    }

    public function firmar(object $razon, User $notificador, string $modo, $foto = null): void
    {
        abort_unless((int) $razon->notificador_id === (int) $notificador->id, 404);
        if ($razon->estatus === self::FIRMADA) {
            throw new HttpException(409, 'Esta razón ya está firmada.');
        }

        $cambios = ['estatus' => self::FIRMADA, 'modo' => $modo, 'firmada_at' => now(), 'updated_at' => now()];
        if ($modo === 'app') {
            $firma = DB::table('sinotifico_firmas')->where('user_id', $notificador->id)->value('ruta');
            if (! $firma) {
                throw new HttpException(422, 'Primero guarda tu firma en la app.');
            }
            $cambios['firma_ruta'] = $firma;
        } else {
            abort_unless(self::permitePapel(), 422, 'Las razones ya sólo se firman en la app.');
            if ($foto) {
                $cambios['papel_ruta'] = $foto->storeAs('sinotifico/razones', $razon->id.'-papel-'.Str::random(6).'.'.$foto->extension(), 'local');
            }
        }
        DB::table('sinotifico_razones')->where('id', $razon->id)->update($cambios);
        self::olvidar();
    }

    /** Guarda la firma del notificador; las razones ya firmadas conservan la de su momento. */
    public function guardarFirma(User $notificador, $imagen): void
    {
        $ruta = $imagen->storeAs('sinotifico/firmas', $notificador->id.'-'.now()->format('YmdHis').'.png', 'local');
        DB::table('sinotifico_firmas')->updateOrInsert(['user_id' => $notificador->id], ['ruta' => $ruta, 'updated_at' => now(), 'created_at' => now()]);
    }

    /**
     * La usan las plantillas de razón: la firma del notificador como data URI
     * si la razón de este citado se firmó en la app; si no, null (la línea
     * queda para firmar a mano). Nunca rompe el PDF.
     */
    public static function firmaEstampada(?object $citado): ?string
    {
        $id = $citado->id ?? null;
        if (! $id) {
            return null;
        }
        if (array_key_exists($id, self::$firmas)) {
            return self::$firmas[$id];
        }
        try {
            $ruta = DB::table('sinotifico_razones')->where('citado_id', $id)
                ->where('estatus', self::FIRMADA)->where('modo', 'app')->value('firma_ruta');
            $png = $ruta ? Storage::disk('local')->get($ruta) : null;

            return self::$firmas[$id] = $png ? 'data:image/png;base64,'.base64_encode($png) : null;
        } catch (Throwable) {
            return self::$firmas[$id] = null;
        }
    }

    /** Para pruebas: olvidar las firmas ya leídas. */
    public static function olvidar(): void
    {
        self::$firmas = [];
    }
}
