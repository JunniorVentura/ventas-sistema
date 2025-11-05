<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'cliente_id',
        'usuario_id',
        'fecha',
        'total',
        'estado_pedido',
        'estado',
    ];

    // Relaciones

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalle_pedidos()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id'); // "DetallePedido" (singular)
    }    

    public function factura()
    {
        return $this->hasOne(Factura::class, 'pedido_id');
    }

    public function boleta()
    {
        return $this->hasOne(Boleta::class, 'pedido_id');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'pedido_id');
    }
}
