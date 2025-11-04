<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    
    protected $table = 'clientes';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'nombre',
        'dni',
        'ruc',
        'razon_social',
        'direccion',
        'telefono',
        'email',
        'estado',
    ];

    // Relaciones

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }
}
