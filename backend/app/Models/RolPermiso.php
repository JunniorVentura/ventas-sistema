<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolPermiso extends Model
{
    use HasFactory;
    
    protected $table = 'rol_permiso';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'rol_id',
        'permiso_id',
        'estado',
    ];

    // Relaciones

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function permiso()
    {
        return $this->belongsTo(Permiso::class, 'permiso_id');
    }
}
