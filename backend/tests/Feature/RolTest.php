<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class RolTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_roles', 'crear_roles', 'editar_roles', 'eliminar_roles'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    }
    
    public function test_puede_listar_roles()
    {
        $this->autenticar();

        Rol::factory()->count(3)->create();

        $response = $this->getJson('/api/roles');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'nombre', 'descripcion', 'estado', 'created_at']
                 ]);
    }

   
    public function test_puede_crear_un_rol()
    {
        $this->autenticar();

        $data = [
            'nombre' => 'Administrador',
            'descripcion' => 'Acceso completo al sistema',
            'estado' => true,
        ];

        $response = $this->postJson('/api/roles', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nombre' => 'Administrador']);

        $this->assertDatabaseHas('roles', ['nombre' => 'Administrador']);
    }

    
    public function test_puede_ver_un_rol()
    {
        $this->autenticar();

        $rol = Rol::factory()->create();

        $response = $this->getJson("/api/roles/{$rol->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $rol->id]);
    }

    
    public function test_puede_actualizar_un_rol()
    {
        $this->autenticar();

        $rol = Rol::factory()->create(['nombre' => 'Vendedor']);

        $response = $this->putJson("/api/roles/{$rol->id}", [
            'nombre' => 'Supervisor',
            'descripcion' => 'Rol actualizado',
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'Supervisor']);

        $this->assertDatabaseHas('roles', ['id' => $rol->id, 'nombre' => 'Supervisor']);
    }

    
    public function test_puede_eliminar_un_rol()
    {
        $this->autenticar();
    
        $rol = Rol::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/roles/{$rol->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Rol desactivado']);
    
        $this->assertDatabaseHas('roles', [
            'id' => $rol->id,
            'estado' => false,
        ]);
    }
    
}
