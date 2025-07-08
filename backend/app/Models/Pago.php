<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;
    
    protected $table = 'pagos';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'pedido_id',
        'metodo_pago',
        'estado_pago',
        'fecha_pago',
        'estado',
    ];

    // Relaciones

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
