<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    
    protected $table = 'categorias';

    public $timestamps = true; // uso de timestamp por defecto

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    // Relaciones

    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
