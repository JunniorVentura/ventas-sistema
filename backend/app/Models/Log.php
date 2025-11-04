<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = 'logs';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'tabla_afectada',
        'id_registro',
        'accion',
        'descripcion',
        'fecha',
    ];

    // Relaciones

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
