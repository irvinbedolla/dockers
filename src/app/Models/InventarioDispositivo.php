<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Equipo del inventario (Administración → Inventario). Ver la migración
 * create_inventario_dispositivos_table y App\Http\Controllers\InventarioController.
 */
class InventarioDispositivo extends Model
{
    protected $table = 'inventario_dispositivos';

    public const ESTADOS = ['bueno' => 'Bueno', 'regular' => 'Regular', 'malo' => 'Malo'];

    /** Campos que se capturan y se corrigen en la tabla, con su nombre para pantalla, historial y Excel. */
    public const CAMPOS = [
        'descripcion'         => 'Descripción',
        'marca'               => 'Marca',
        'modelo'              => 'Modelo',
        'numero_serie'        => 'Número de serie',
        'estado_uso'          => 'Estado de uso',
        'razon_malo'          => 'Razón del mal estado',
        'ubicacion'           => 'Ubicación física',
        'resguardante_id'     => 'Resguardante (usuario)',
        'resguardante_nombre' => 'Resguardante',
        'resguardante_puesto' => 'Puesto del resguardante',
        'caracteristicas'     => 'Características específicas',
        'observaciones'       => 'Observaciones adicionales',
        'verificado'          => 'Verificado',
        'revisar'             => 'Marcado para revisión',
        'revisar_nota'        => 'Nota de revisión',
    ];

    /** Sugerencias para la descripción; se suman las que ya se hayan capturado. */
    public const DESCRIPCIONES = [
        'COMPUTADORA DE ESCRITORIO', 'LAPTOP', 'MONITOR', 'TECLADO', 'MOUSE', 'IMPRESORA', 'ESCÁNER',
        'MULTIFUNCIONAL', 'ROUTER', 'SWITCH', 'ACCESS POINT', 'NO BREAK', 'PROYECTOR', 'TELÉFONO', 'TABLET', 'SERVIDOR',
    ];

    protected $fillable = [
        'descripcion', 'marca', 'modelo', 'numero_serie', 'estado_uso', 'razon_malo', 'ubicacion',
        'resguardante_id', 'resguardante_nombre', 'resguardante_puesto', 'caracteristicas', 'observaciones',
    ];

    protected $casts = [
        'verificado'    => 'boolean',
        'revisar'       => 'boolean',
        'verificado_at' => 'datetime',
    ];

    public function resguardante()
    {
        return $this->belongsTo(User::class, 'resguardante_id');
    }

    public function verificador()
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }

    public function historial()
    {
        return $this->hasMany(InventarioHistorial::class, 'dispositivo_id')->latest('id');
    }

    /** Lo que la tabla necesita para pintar o repintar un renglón. */
    public function paraTabla(): array
    {
        return [
            'id'                  => $this->id,
            'descripcion'         => $this->descripcion,
            'marca'               => $this->marca,
            'modelo'              => $this->modelo,
            'numero_serie'        => $this->numero_serie,
            'estado_uso'          => $this->estado_uso,
            'razon_malo'          => $this->razon_malo,
            'ubicacion'           => $this->ubicacion,
            'resguardante_id'     => $this->resguardante_id,
            'resguardante_nombre' => $this->resguardante_nombre,
            'resguardante_puesto' => $this->resguardante_puesto,
            'caracteristicas'     => $this->caracteristicas,
            'observaciones'       => $this->observaciones,
            'verificado'          => $this->verificado,
            'verificado_texto'    => $this->verificado
                ? 'Verificado por '.($this->verificador->name ?? '—').' el '.$this->verificado_at?->format('d/m/Y H:i')
                : '',
            'revisar'             => $this->revisar,
            'revisar_nota'        => $this->revisar_nota,
        ];
    }
}
