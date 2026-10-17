<?php

namespace App\Http\Resources\Sinotifico;

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
        $roles = $this->getRoleNames()
            ->intersect(config('sinotifico.roles'))
            ->values();

        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'email' => $this->email,
            'roles' => $roles,
            // El primero según el orden de config: Notificador tiene prioridad.
            'rol' => collect(config('sinotifico.roles'))->first(fn ($r) => $roles->contains($r)),
            'sede' => $this->delegacion ? [
                'nombre' => $this->delegacion,
                'clave' => config('sinotifico.sedes')[$this->delegacion] ?? null,
            ] : null,
            'foto_url' => $this->tieneFotoDePerfil() ? $this->avatar_url : null,
        ];
    }
}
