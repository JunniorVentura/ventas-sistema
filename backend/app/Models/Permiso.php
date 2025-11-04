<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;
    
    protected $table = 'permisos';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    // Relaciones

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'permiso_id', 'rol_id')
                    ->withPivot('estado');
    }
}
