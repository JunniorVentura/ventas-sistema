<?php

namespace Tests\Feature;

use App\Models\DetalleFactura;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class DetalleFacturaTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_facturas', 'crear_facturas', 'editar_facturas', 'eliminar_facturas'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    
    public function test_puede_listar_detalle_facturas()
    {
        $this->autenticar();

        DetalleFactura::factory()->count(3)->create();

        $response = $this->getJson('/api/detalle-facturas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'factura_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal', 'estado']
                 ]);
    }

    
    public function test_puede_crear_detalle_factura()
    {
        $this->autenticar();

        $factura = Factura::factory()->create();
        $producto = Producto::factory()->create();
        $cantidad = 4;
        $precio = $producto->precio ?? 100;

        $response = $this->postJson('/api/detalle-facturas', [
            'factura_id' => $factura->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => round($cantidad * $precio, 2),
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'factura_id' => $factura->id,
                     'producto_id' => $producto->id
                 ]);

        $this->assertDatabaseHas('detalle_factura', [
            'factura_id' => $factura->id,
            'producto_id' => $producto->id
        ]);
    }

    
    public function test_puede_ver_detalle_factura_individual()
    {
        $this->autenticar();

        $detalle = DetalleFactura::factory()->create();

        $response = $this->getJson("/api/detalle-facturas/{$detalle->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $detalle->id]);
    }

    
    public function test_puede_actualizar_detalle_factura()
    {
        $this->autenticar();

        $detalle = DetalleFactura::factory()->create(['cantidad' => 2]);

        $nuevaCantidad = 6;
        $nuevoSubtotal = round($nuevaCantidad * $detalle->precio_unitario, 2);

        $response = $this->putJson("/api/detalle-facturas/{$detalle->id}", [
            'factura_id' => $detalle->factura_id,
            'producto_id' => $detalle->producto_id,
            'cantidad' => $nuevaCantidad,
            'precio_unitario' => $detalle->precio_unitario,
            'subtotal' => $nuevoSubtotal,
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['cantidad' => $nuevaCantidad]);

        $this->assertDatabaseHas('detalle_factura', [
            'id' => $detalle->id,
            'cantidad' => $nuevaCantidad,
        ]);
    }

    
    public function test_puede_eliminar_detalle_factura()
    {
        $this->autenticar();
    
        $detalle = DetalleFactura::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/detalle-facturas/{$detalle->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Detalle de factura desactivado']);
    
        $this->assertDatabaseHas('detalle_factura', [
            'id' => $detalle->id,
            'estado' => false,
        ]);
    }
    
}
