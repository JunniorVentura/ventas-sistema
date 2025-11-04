<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;
    
    protected $table = 'usuarios';
    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol_id',
        'estado',
    ];

    protected $hidden = [
        'password',
    ];

    // Relaciones
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'usuario_id');
    }
    //  validar si el usuario tiene permisos según los asignados en rol_permiso

    public function tienePermiso($permisoNombre)
    {
        $this->loadMissing('rol.permisos'); // Asegura que carga los permisos
        return $this->rol
            ? $this->rol->permisos()
                        ->where('nombre', $permisoNombre)
                        ->where('permisos.estado', true)
                        ->exists()
            : false;
    }

}
