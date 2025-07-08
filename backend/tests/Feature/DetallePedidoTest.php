<?php

namespace Tests\Feature;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class DetallePedidoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_pedidos', 'crear_pedidos', 'editar_pedidos', 'eliminar_pedidos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 


    
    public function test_puede_listar_detalle_pedidos()
    {
        $this->autenticar();

        DetallePedido::factory()->count(3)->create();

        $response = $this->getJson('/api/detalle-pedidos');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'pedido_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal', 'estado']
                 ]);
    }

    
    public function test_puede_crear_un_detalle_pedido()
    {
        $this->autenticar();

        $pedido = Pedido::factory()->create();
        $producto = Producto::factory()->create();
        $cantidad = 3;
        $precio = $producto->precio ?? 100;

        $response = $this->postJson('/api/detalle-pedidos', [
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => round($cantidad * $precio, 2),
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'pedido_id' => $pedido->id,
                     'producto_id' => $producto->id
                 ]);

        $this->assertDatabaseHas('detalle_pedidos', [
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad
        ]);
    }

    
    public function test_puede_ver_detalle_pedido()
    {
        $this->autenticar();

        $detalle = DetallePedido::factory()->create();

        $response = $this->getJson("/api/detalle-pedidos/{$detalle->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $detalle->id]);
    }

    
    public function test_puede_actualizar_detalle_pedido()
    {
        $this->autenticar();

        $detalle = DetallePedido::factory()->create(['cantidad' => 2]);

        $response = $this->putJson("/api/detalle-pedidos/{$detalle->id}", [
            'pedido_id' => $detalle->pedido_id,
            'producto_id' => $detalle->producto_id,
            'cantidad' => 5,
            'precio_unitario' => $detalle->precio_unitario,
            'subtotal' => round(5 * $detalle->precio_unitario, 2),
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['cantidad' => 5]);

        $this->assertDatabaseHas('detalle_pedidos', ['id' => $detalle->id, 'cantidad' => 5]);
    }

    
    public function test_puede_eliminar_detalle_pedido()
    {
        $this->autenticar();
    
        $detalle = DetallePedido::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/detalle-pedidos/{$detalle->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Detalle de pedido desactivado']);
    
        $this->assertDatabaseHas('detalle_pedidos', [
            'id' => $detalle->id,
            'estado' => false, // Verificamos desactivación lógica
        ]);
    }
    
}
