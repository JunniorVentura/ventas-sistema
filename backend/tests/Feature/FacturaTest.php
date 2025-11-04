<?php

namespace Tests\Feature;

use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class FacturaTest extends TestCase
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

    
    public function test_puede_listar_facturas()
    {
        $this->autenticar();

        Factura::factory()->count(3)->create();

        $response = $this->getJson('/api/facturas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'pedido_id', 'ruc_cliente', 'razon_social', 'fecha_emision', 'total', 'estado']
                 ]);
    }

    
    public function test_puede_crear_factura()
    {
        $this->autenticar();

        $pedido = Pedido::factory()->create();

        $response = $this->postJson('/api/facturas', [
            'pedido_id' => $pedido->id,
            'ruc_cliente' => '12345678901',
            'razon_social' => 'Empresa XYZ S.A.C.',
            'fecha_emision' => now()->toDateTimeString(),
            'total' => 350.75,
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'ruc_cliente' => '12345678901',
                     'razon_social' => 'Empresa XYZ S.A.C.',
                 ]);

        $this->assertDatabaseHas('facturas', ['pedido_id' => $pedido->id, 'total' => 350.75]);
    }

    
    public function test_puede_ver_una_factura()
    {
        $this->autenticar();

        $factura = Factura::factory()->create();

        $response = $this->getJson("/api/facturas/{$factura->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $factura->id]);
    }

    
    public function test_puede_actualizar_una_factura()
    {
        $this->autenticar();

        $factura = Factura::factory()->create(['total' => 100]);

        $response = $this->putJson("/api/facturas/{$factura->id}", [
            'pedido_id' => $factura->pedido_id,
            'ruc_cliente' => $factura->ruc_cliente,
            'razon_social' => 'Empresa Modificada S.A.',
            'fecha_emision' => $factura->fecha_emision,
            'total' => 500.00,
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['total' => 500.00]);

        $this->assertDatabaseHas('facturas', ['id' => $factura->id, 'razon_social' => 'Empresa Modificada S.A.']);
    }

    
    public function test_puede_eliminar_una_factura()
    {
        $this->autenticar();
    
        $factura = Factura::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/facturas/{$factura->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Factura desactivada']);
    
        $this->assertDatabaseHas('facturas', [
            'id' => $factura->id,
            'estado' => false,
        ]);
    }
    
}
