<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Boleta;
use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class RegistroDePagoCompletoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['registrar_pagos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    public function test_registro_de_pago_completo()
    {
        // Autenticación
        $this->autenticar();

        // Crear producto
        $producto = Producto::factory()->create();

        // Crear pedido
        $pedido = Pedido::factory()->create([
            'usuario_id' => auth()->user()->id,
        ]);

        // Asociar detalle del pedido
        DetallePedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => $producto->precio,
            'subtotal' => 2 * $producto->precio,
        ]);

        // Crear boleta asociada al pedido
        $boleta = Boleta::factory()->create([
            'pedido_id' => $pedido->id,
            'total' => $pedido->total,
        ]);

        // Crear factura asociada al pedido
        $factura = Factura::factory()->create([
            'pedido_id' => $pedido->id,
            'total' => $pedido->total,
        ]);

        // Registrar pago
        $response = $this->postJson("/api/pagos", [
            'pedido_id' => $pedido->id,
            'metodo_pago' => 'efectivo',
            'estado_pago' => 'verificado',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['pedido_id' => $pedido->id]);

        // Verificar en la base de datos
        $this->assertDatabaseHas('pagos', [
            'pedido_id' => $pedido->id,
            'estado_pago' => 'verificado',
        ]);

    }
}
