<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_productos', 'crear_productos', 'editar_productos', 'eliminar_productos'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    }

    
    public function test_puede_listar_productos()
    {
        $this->autenticar();

        Producto::factory()->count(5)->create();

        $response = $this->getJson('/api/productos');

        $response->assertStatus(200)
                 ->assertJsonStructure([['id', 'nombre', 'descripcion', 'precio', 'categoria_id']]);
    }

    
    public function test_puede_crear_un_producto()
    {
        $this->autenticar();

        $categoria = Categoria::factory()->create();

        $response = $this->postJson('/api/productos', [
            'nombre' => 'Teclado Mecánico',
            'descripcion' => 'Producto gamer con luces RGB',
            'precio' => 150.75,
            'categoria_id' => $categoria->id,
            'estado' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nombre' => 'Teclado Mecánico']);

        $this->assertDatabaseHas('productos', ['nombre' => 'Teclado Mecánico']);
    }

    
    public function test_puede_ver_un_producto_especifico()
    {
        $this->autenticar();

        $producto = Producto::factory()->create();

        $response = $this->getJson("/api/productos/{$producto->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => $producto->nombre]);
    }

    
    public function test_puede_actualizar_un_producto()
    {
        $this->autenticar();

        $producto = Producto::factory()->create(['nombre' => 'Producto Viejo']);

        $response = $this->putJson("/api/productos/{$producto->id}", [
            'nombre' => 'Producto Nuevo',
            'descripcion' => $producto->descripcion,
            'precio' => $producto->precio,
            'categoria_id' => $producto->categoria_id,
            'estado' => true,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'Producto Nuevo']);

        $this->assertDatabaseHas('productos', ['nombre' => 'Producto Nuevo']);
    }

    
    public function test_puede_eliminar_un_producto()
    {
        $this->autenticar();
    
        $producto = Producto::factory()->create();
    
        $response = $this->deleteJson("/api/productos/{$producto->id}");
    
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Producto y su stock desactivados']);
    
        // Verificamos que el producto sigue existiendo pero está desactivado
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'estado' => false,
        ]);
    }
    
}
