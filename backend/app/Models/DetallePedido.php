<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;
    
    protected $table = 'detalle_pedidos';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'estado',
    ];

    // Relaciones

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
