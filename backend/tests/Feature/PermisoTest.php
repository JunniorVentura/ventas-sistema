<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermisoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_permisos', 'crear_permisos', 'editar_permisos', 'eliminar_permisos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 

    
    public function test_puede_listar_permisos()
    {
        $this->autenticar();

        Permiso::factory()->count(3)->create();

        $response = $this->getJson('/api/permisos');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'nombre', 'descripcion', 'estado', 'created_at']
                 ]);
    }

    
    public function test_puede_crear_un_permiso()
    {
        $this->autenticar();

        $data = [
            'nombre' => 'crear_usuario',
            'descripcion' => 'Permite crear usuarios',
            'estado' => true,
        ];

        $response = $this->postJson('/api/permisos', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nombre' => 'crear_usuario']);

        $this->assertDatabaseHas('permisos', ['nombre' => 'crear_usuario']);
    }

    
    public function test_puede_ver_un_permiso()
    {
        $this->autenticar();

        $permiso = Permiso::factory()->create();

        $response = $this->getJson("/api/permisos/{$permiso->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $permiso->id]);
    }

    
    public function test_puede_actualizar_un_permiso()
    {
        $this->autenticar();

        $permiso = Permiso::factory()->create(['nombre' => 'editar_producto']);

        $response = $this->putJson("/api/permisos/{$permiso->id}", [
            'nombre' => 'eliminar_producto',
            'descripcion' => 'Permiso actualizado',
            'estado' => false,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'eliminar_producto']);

        $this->assertDatabaseHas('permisos', ['id' => $permiso->id, 'nombre' => 'eliminar_producto']);
    }

    
    public function test_puede_eliminar_un_permiso()
    {
        $this->autenticar();
    
        $permiso = Permiso::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/permisos/{$permiso->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Permiso desactivado']);
    
        $this->assertDatabaseHas('permisos', [
            'id' => $permiso->id,
            'estado' => false,
        ]);
    }
    
}
