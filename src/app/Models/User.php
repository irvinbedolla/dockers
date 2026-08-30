<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return $this->profile_photo_path;
    }

    /**
     * URL del avatar, siempre con imagen: si el usuario todavía no sube su
     * foto devuelve el marcador. Va aparte de profile_photo_url porque varias
     * vistas usan ese como bandera (@if) para decidir si pintan iniciales.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->tieneFotoDePerfil()
            ? asset('storage/' . $this->profile_photo_path)
            : asset('assets/images/user-not-found.png');
    }

    /**
     * ¿profile_photo_path apunta de verdad a una foto?
     *
     * La columna trae basura de cargas anteriores: valores 'tmp_1', 'tmp_10'…
     * que quedaron de registros a medias y nunca correspondieron a un archivo.
     * Se descartan para que el avatar caiga en el marcador en vez de pedir una
     * imagen que no existe y quedarse con el ícono de imagen rota.
     */
    public function tieneFotoDePerfil(): bool
    {
        $ruta = trim((string) $this->profile_photo_path);

        if ($ruta === '') {
            return false;
        }

        return ! Str::startsWith(Str::lower(basename($ruta)), 'tmp_');
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
