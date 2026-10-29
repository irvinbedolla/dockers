<?php

namespace App\Http\Resources\Sinotifico;

use App\Support\Sinotifico\Acceso;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lo que la app necesita saber de quien inició sesión. A propósito no expone
 * CURP (profile_photo_path), tipo ni otros campos internos de `users`.
 */
class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roles = collect(Acceso::ROLES_VISIBLES)
            ->intersect($this->getRoleNames())
            ->values();

        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'email' => $this->email,
            'roles' => $roles,
            // El primero según Acceso::ROLES_VISIBLES: Notificador tiene prioridad.
            'rol' => $roles->first(),
            // Qué partes de la app abre: su lista de campo y/o la supervisión.
            'acceso' => [
                'notifica' => Acceso::notifica($this->resource),
                'supervisa' => Acceso::supervisa($this->resource),
            ],
            'distintivo' => Acceso::distintivo($this->resource),
            'sede' => $this->delegacion ? [
                'nombre' => $this->delegacion,
                'clave' => config('sinotifico.sedes')[$this->delegacion] ?? null,
            ] : null,
            'foto_url' => $this->tieneFotoDePerfil() ? $this->avatar_url : null,
        ];
    }
}
