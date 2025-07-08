<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    
    protected $table = 'roles';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    // Relaciones

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id')
                    ->withPivot('estado')
                    ->wherePivot('estado', true); // Solo permisos activos
    }
    
}
