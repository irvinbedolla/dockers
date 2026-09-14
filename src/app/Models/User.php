<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable 
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login_at',
        'last_login_ip',
        'profile_photo_path',
        'foto_perfil',
        'type',
        'remember_token',
        'delegacion',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    /**
     * URL del avatar, siempre con imagen.
     *
     * Antes esto leia profile_photo_path y armaba asset('storage/'.$valor).
     * Esa columna guarda la CURP, asi que el navegador terminaba pidiendo
     * /storage/AAAA681101HMNLRN06 y pintando el icono de imagen rota en la
     * barra de 5,140 de los 5,291 usuarios. Ahora solo mira foto_perfil y,
     * si esta vacia, devuelve el marcador.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->tieneFotoDePerfil()
            ? asset('storage/'.$this->foto_perfil)
            : asset('assets/images/user-not-found.png');
    }

    /**
     * Se conserva porque varias vistas lo usan como bandera (@if) para decidir
     * si pintan la foto o las iniciales. Devuelve null cuando no hay foto, que
     * es lo que esas vistas esperan; nunca una URL inventada.
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->tieneFotoDePerfil()
            ? asset('storage/'.$this->foto_perfil)
            : null;
    }

    public function tieneFotoDePerfil(): bool
    {
        return trim((string) $this->foto_perfil) !== '';
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function getDefaultAddressAttribute()
    {
        return $this->addresses?->first();
    }

    public function solicitudes() {
        return $this->hasMany(SeerPerGeneral::class, 'user_id');
    }
    
    public function audiencias() {
        // Relación a través de seer_general
        return $this->hasManyThrough(Audiencias::class, SeerPerGeneral::class, 'conciliador_id', 'id_solicitud');
    }
}
