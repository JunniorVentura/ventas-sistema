<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class StockProductoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['crear_stock', 'ver_stock', 'actualizar_stock', 'eliminar_stock'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    public function test_flujo_completo_de_stock_para_un_producto()
    {
        // Autenticación
        $this->autenticar();

        // Crear producto
        $producto = Producto::factory()->create();

        // Crear stock para el producto
        $responseCrear = $this->postJson('/api/stock', [
            'producto_id' => $producto->id,
            'cantidad' => 100,
            'estado' => true,
        ]);

        $responseCrear->assertStatus(201)
                      ->assertJsonFragment(['producto_id' => $producto->id]);

        $this->assertDatabaseHas('stock', [
            'producto_id' => $producto->id,
            'cantidad' => 100,
            'estado' => true,
        ]);

        // Listar stocks
        $responseLista = $this->getJson('/api/stock');
        $responseLista->assertStatus(200)
                      ->assertJsonFragment(['producto_id' => $producto->id]);

        $stockId = $responseCrear->json('id');

        // Ver stock específico
        $responseVer = $this->getJson("/api/stock/{$stockId}");
        $responseVer->assertStatus(200)
                    ->assertJsonFragment(['producto_id' => $producto->id]);

        // Actualizar stock
        $responseActualizar = $this->putJson("/api/stock/{$stockId}", [
            'cantidad' => 150,
            'estado' => true,
        ]);

        $responseActualizar->assertStatus(200)
                           ->assertJsonFragment(['cantidad' => 150]);

        $this->assertDatabaseHas('stock', [
            'id' => $stockId,
            'cantidad' => 150,
        ]);

        // Eliminar (desactivar) stock
        $responseEliminar = $this->deleteJson("/api/stock/{$stockId}");
        $responseEliminar->assertStatus(200)
                         ->assertJsonFragment(['message' => 'Stock desactivado']);

        $this->assertDatabaseHas('stock', [
            'id' => $stockId,
            'estado' => false,
        ]);
    }
}
