<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_clientes', 'crear_clientes', 'editar_clientes', 'eliminar_clientes'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    
    public function test_puede_listar_clientes()
    {
        $this->autenticar();

        Cliente::factory()->count(5)->create();

        $response = $this->getJson('/api/clientes');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'nombre', 'email']]);
    }

    
    public function test_puede_crear_un_cliente()
    {
        $this->autenticar();

        $response = $this->postJson('/api/clientes', [
            'nombre' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'telefono' => '987654321',
            'direccion' => 'Av. Siempre Viva 123',
            'dni' => '12345678',
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['email' => 'juan@example.com']);

        $this->assertDatabaseHas('clientes', ['email' => 'juan@example.com']);
    }

    
    public function test_puede_ver_un_cliente_especifico()
    {
        $this->autenticar();

        $cliente = Cliente::factory()->create();

        $response = $this->getJson("/api/clientes/{$cliente->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => $cliente->email]);
    }

    
    public function test_puede_actualizar_un_cliente()
    {
        $this->autenticar();

        $cliente = Cliente::factory()->create(['nombre' => 'Antiguo']);

        $response = $this->putJson("/api/clientes/{$cliente->id}", [
            'nombre' => 'Nuevo Nombre',
            'email' => $cliente->email,
            'telefono' => $cliente->telefono,
            'direccion' => $cliente->direccion,
            'dni' => $cliente->dni,
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'Nuevo Nombre']);

        $this->assertDatabaseHas('clientes', ['nombre' => 'Nuevo Nombre']);
    }

    
    public function test_puede_eliminar_un_cliente()
    {
        $this->autenticar();
    
        $cliente = Cliente::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/clientes/{$cliente->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Cliente desactivado']);
    
        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'estado' => false,
        ]);
    }
    
}
