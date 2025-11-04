<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    
    protected $table = 'productos';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'categoria_id',
        'estado',
    ];

    // Relaciones

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'producto_id');
    }

    public function detallePedidos()
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }

    public function detalleFacturas()
    {
        return $this->hasMany(DetalleFactura::class, 'producto_id');
    }

    public function detalleBoletas()
    {
        return $this->hasMany(DetalleBoleta::class, 'producto_id');
    }
}
