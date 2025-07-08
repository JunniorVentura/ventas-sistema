<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boleta extends Model
{
    use HasFactory;
    
    protected $table = 'boletas';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'pedido_id',
        'dni_cliente',
        'nombre_cliente',
        'fecha_emision',
        'total',
        'estado',
    ];

    // Relaciones

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function detalleBoleta()
    {
        return $this->hasMany(DetalleBoleta::class, 'boleta_id');
    }
}
