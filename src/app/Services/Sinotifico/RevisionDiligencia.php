<?php

namespace App\Services\Sinotifico;

use App\Models\SeerCitados;
use App\Models\Sinotifico\Diligencia;
use App\Models\User;
use App\Support\Sinotifico\Acceso;
use App\Support\Sinotifico\Catalogos as C;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Revisión de las diligencias capturadas en campo. Validar es lo único que
 * escribe en `seer_citados`, con las mismas reglas que el formulario web del
 * notificador (SeerController::update_notificador).
 */
class RevisionDiligencia
{
    public function __construct(private readonly Geocodificador $geo) {}

    /** Diligencias que este supervisor puede ver: las de su sede o todas. */
    public function visibles(User $supervisor): Builder
    {
        $alcance = Acceso::supervisa($supervisor);
        abort_if($alcance === null, 403, 'Tu cuenta no supervisa notificadores.');

        return Diligencia::query()
            ->when($alcance === 'sede', fn (Builder $q) => $q->where('sede', $supervisor->delegacion));
    }

    public function puedeVer(User $usuario, Diligencia $d): bool
    {
        if ((int) $d->notificador_id === (int) $usuario->id) {
            return true;
        }

        return match (Acceso::supervisa($usuario)) {
            'todas' => true,
            'sede' => $d->sede === $usuario->delegacion,
            default => false,
        };
    }

    public function validar(Diligencia $d, User $revisor): Diligencia
    {
        return DB::transaction(function () use ($d, $revisor) {
            $d = Diligencia::whereKey($d->id)->lockForUpdate()->firstOrFail();
            $this->exigirPorRevisar($d);

            $citados = SeerCitados::whereIn('id', $d->citadoIds())->get();
            $municipios = DB::table('municipios')->whereIn('id', $citados->pluck('municipio_citado')->filter())->pluck('nombre', 'id');
            foreach ($citados as $c) {
                $sigue = (int) $c->id_notificador === (int) $d->notificador_id
                    && in_array($c->estatus, config('sinotifico.estatus_pendientes'), true);
                if (! $sigue) {
                    throw new HttpException(409, "El citado {$c->id} ya cambió en SICONCILIO (estatus o notificador). Devuelve la diligencia o revísala allá.");
                }
            }

            $fotos = $d->evidencias->where('tipo', 'foto')->values();
            $datos = $d->datos;
            $exhorto = $d->quien_atiende === 'EXHORTO';

            foreach ($citados as $c) {
                $documentos = ['Sin documento', 'Sin documento', 'Sin documento'];
                foreach ($fotos->take(3) as $i => $foto) {
                    // Mismo lugar y nombre que usa el formulario web.
                    $nombre = $c->id.'-foto'.($i + 1).'.jpg';
                    Storage::disk('s3')->put(
                        'documentos_notificacion/'.$c->id_solicitud.'/'.$nombre,
                        Storage::disk('local')->get($foto->ruta)
                    );
                    $documentos[$i] = $nombre;
                }

                $c->update([
                    'estatus' => $exhorto ? 'Exhorto' : $d->resultado,
                    'notificacion' => $exhorto ? 'Exhorto' : $c->notificacion,
                    'id_notificador' => $exhorto
                        ? (C::NOTIFICADOR_EXHORTO_POR_MUNICIPIO[$municipios[$c->municipio_citado] ?? ''] ?? $d->notificador_id)
                        : $d->notificador_id,
                    'fecha' => $d->llegada_at,
                    'quien_atiende' => $d->quien_atiende,
                    'documento' => $documentos[0],
                    'documento1' => $documentos[1],
                    'documento2' => $documentos[2],
                ] + array_intersect_key($datos, array_flip(RegistroCaptura::CAMPOS)));
            }

            // El notificador estuvo en el domicilio: su GPS confirma la ubicación en el catálogo.
            if (in_array($d->resultado, ['Finalizado exitosamente', 'Recibe pero no firma', 'No notificada'], true)) {
                $c = $citados->first();
                $this->geo->confirmarEnCampo([
                    'tipo_vialidad' => $c->tipo_vialidad, 'calle' => $c->calle, 'numero_exterior' => $c->n_ext,
                    'numero_interior' => $c->n_int, 'colonia' => $c->colonia, 'cp' => $c->cp,
                    'municipio' => $municipios[$c->municipio_citado] ?? null,
                    'estado' => DB::table('estados')->where('id', $c->estado_citado)->value('nombre'),
                    'texto' => VisitasDelNotificador::textoDomicilio((object) ($c->getAttributes() + [
                        'municipio' => $municipios[$c->municipio_citado] ?? null,
                        'estado' => DB::table('estados')->where('id', $c->estado_citado)->value('nombre'),
                    ])),
                ], $d->lat, $d->lng, $d->resultado !== 'No notificada');
            }

            $d->forceFill([
                'estatus' => Diligencia::VALIDADA,
                'revisor_id' => $revisor->id,
                'revisada_at' => now(),
                'comentario_revision' => null,
            ])->save();
            HistorialRutas::registrar($d);

            return $d;
        });
    }

    public function devolver(Diligencia $d, User $revisor, string $comentario): Diligencia
    {
        return DB::transaction(function () use ($d, $revisor, $comentario) {
            $d = Diligencia::whereKey($d->id)->lockForUpdate()->firstOrFail();
            $this->exigirPorRevisar($d);

            $d->forceFill([
                'estatus' => Diligencia::DEVUELTA,
                'revisor_id' => $revisor->id,
                'revisada_at' => now(),
                'comentario_revision' => $comentario,
            ])->save();
            HistorialRutas::registrar($d);

            return $d;
        });
    }

    private function exigirPorRevisar(Diligencia $d): void
    {
        if ($d->estatus !== Diligencia::POR_REVISAR) {
            throw new HttpException(409, 'Esta diligencia ya fue revisada.');
        }
    }
}
