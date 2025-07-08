<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_categorias', 'crear_categorias', 'editar_categorias', 'eliminar_categorias'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    }
    
    public function test_puede_listar_categorias()
    {
        $this->autenticar();

        Categoria::factory()->count(3)->create();

        $response = $this->getJson('/api/categorias');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'nombre', 'descripcion']]);
    }

    
    public function test_puede_crear_una_categoria()
    {
        $this->autenticar();

        $response = $this->postJson('/api/categorias', [
            'nombre' => 'Electrónica',
            'descripcion' => 'Dispositivos electrónicos',
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nombre' => 'Electrónica']);

        $this->assertDatabaseHas('categorias', ['nombre' => 'Electrónica']);
    }

    
    public function test_puede_ver_una_categoria()
    {
        $this->autenticar();

        $categoria = Categoria::factory()->create();

        $response = $this->getJson("/api/categorias/{$categoria->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => $categoria->nombre]);
    }

    
    public function test_puede_actualizar_una_categoria()
    {
        $this->autenticar();

        $categoria = Categoria::factory()->create(['nombre' => 'Antiguo']);

        $response = $this->putJson("/api/categorias/{$categoria->id}", [
            'nombre' => 'Nuevo Nombre',
            'descripcion' => 'Nueva descripción',
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'Nuevo Nombre']);

        $this->assertDatabaseHas('categorias', ['nombre' => 'Nuevo Nombre']);
    }

    
    public function test_puede_eliminar_una_categoria()
    {
        $this->autenticar();
    
        $categoria = Categoria::factory()->create(['estado' => true]);
    
        $response = $this->deleteJson("/api/categorias/{$categoria->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Categoría desactivada']);
    
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'estado' => false,
        ]);
    }
    
}
