<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;
    
    protected $table = 'facturas';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'pedido_id',
        'ruc_cliente',
        'razon_social',
        'fecha_emision',
        'total',
        'estado',
    ];

    // Relaciones

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function detalleFactura()
    {
        return $this->hasMany(DetalleFactura::class, 'factura_id');
    }
}
