<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleBoleta extends Model
{
    use HasFactory;

    protected $table = 'detalle_boleta';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'boleta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'estado',
    ];

    // Relaciones

    public function boleta()
    {
        return $this->belongsTo(Boleta::class, 'boleta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
