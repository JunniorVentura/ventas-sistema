<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    
    protected $table = 'stock';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'producto_id',
        'cantidad',
        'estado',
    ];

    // Relaciones

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
