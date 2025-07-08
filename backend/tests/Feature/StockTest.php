<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class StockTest extends TestCase
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

    
    public function test_puede_listar_stock()
    {
        // Autenticación
        $this->autenticar();

        Stock::factory()->count(3)->create();

        $response = $this->getJson('/api/stock');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'producto_id', 'cantidad', 'estado']]);
    }

    
    public function test_puede_crear_stock_para_un_producto()
    {
        // Autenticación
        $this->autenticar();

        $producto = Producto::factory()->create();

        $response = $this->postJson('/api/stock', [
            'producto_id' => $producto->id,
            'cantidad' => 50,
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['producto_id' => $producto->id]);

        $this->assertDatabaseHas('stock', ['producto_id' => $producto->id, 'cantidad' => 50]);
    }

    
    public function test_puede_ver_stock_de_producto()
    {
        // Autenticación
        $this->autenticar();

        $stock = Stock::factory()->create();

        $response = $this->getJson("/api/stock/{$stock->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['producto_id' => $stock->producto_id]);
    }

    
    public function test_puede_actualizar_stock()
    {
        // Autenticación
        $this->autenticar();

        $stock = Stock::factory()->create(['cantidad' => 20]);

        $response = $this->putJson("/api/stock/{$stock->id}", [
            'producto_id' => $stock->producto_id,
            'cantidad' => 100,
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['cantidad' => 100]);

        $this->assertDatabaseHas('stock', ['id' => $stock->id, 'cantidad' => 100]);
    }

    
    public function test_puede_eliminar_stock()
    {
        // Autenticación
        $this->autenticar();
            
        $stock = Stock::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/stock/{$stock->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Stock desactivado']);
    
        $this->assertDatabaseHas('stock', [
            'id' => $stock->id,
            'estado' => false,
        ]);
    }
    
}
