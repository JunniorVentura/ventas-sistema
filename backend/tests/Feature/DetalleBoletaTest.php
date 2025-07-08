<?php

namespace Tests\Feature;

use App\Models\DetalleBoleta;
use App\Models\Boleta;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class DetalleBoletaTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_boletas', 'crear_boletas', 'editar_boletas', 'eliminar_boletas'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    
    public function test_puede_listar_detalles_boleta()
    {
        $this->autenticar();

        DetalleBoleta::factory()->count(3)->create();

        $response = $this->getJson('/api/detalle-boletas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'boleta_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal', 'estado']
                 ]);
    }

    
    public function test_puede_crear_detalle_boleta()
    {
        $this->autenticar();

        $boleta = Boleta::factory()->create();
        $producto = Producto::factory()->create();
        $cantidad = 3;
        $precio = $producto->precio ?? 100;

        $response = $this->postJson('/api/detalle-boletas', [
            'boleta_id' => $boleta->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => round($cantidad * $precio, 2),
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'boleta_id' => $boleta->id,
                     'producto_id' => $producto->id
                 ]);

        $this->assertDatabaseHas('detalle_boleta', [
            'boleta_id' => $boleta->id,
            'producto_id' => $producto->id,
        ]);
    }

    
    public function test_puede_ver_detalle_boleta()
    {
        $this->autenticar();

        $detalle = DetalleBoleta::factory()->create();

        $response = $this->getJson("/api/detalle-boletas/{$detalle->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $detalle->id]);
    }

    
    public function test_puede_actualizar_detalle_boleta()
    {
        $this->autenticar();

        $detalle = DetalleBoleta::factory()->create(['cantidad' => 2]);

        $nuevaCantidad = 5;
        $nuevoSubtotal = round($nuevaCantidad * $detalle->precio_unitario, 2);

        $response = $this->putJson("/api/detalle-boletas/{$detalle->id}", [
            'boleta_id' => $detalle->boleta_id,
            'producto_id' => $detalle->producto_id,
            'cantidad' => $nuevaCantidad,
            'precio_unitario' => $detalle->precio_unitario,
            'subtotal' => $nuevoSubtotal,
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['cantidad' => $nuevaCantidad]);

        $this->assertDatabaseHas('detalle_boleta', [
            'id' => $detalle->id,
            'cantidad' => $nuevaCantidad,
        ]);
    }

    
    public function test_puede_eliminar_detalle_boleta()
    {
        $this->autenticar();
    
        $detalle = DetalleBoleta::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/detalle-boletas/{$detalle->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Detalle de boleta desactivado']);
    
        $this->assertDatabaseHas('detalle_boleta', [
            'id' => $detalle->id,
            'estado' => false,
        ]);
    }
    
}
