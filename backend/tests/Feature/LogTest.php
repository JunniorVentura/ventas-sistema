<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class LogTest extends TestCase
{
    use RefreshDatabase;

    private function autenticar()
    {
        $usuario = Usuario::factory()->create();
    
        $permisos = ['ver_logs', 'crear_logs'];
        foreach ($permisos as $permiso) {
            $permisoCreado = Permiso::firstOrCreate(['nombre' => $permiso]);
    
            // Asigna permiso al rol del usuario
            $usuario->rol->permisos()->attach($permisoCreado->id, ['estado' => true]);
        }
    
        Sanctum::actingAs($usuario);
    } 
    
    public function test_puede_listar_logs()
    {
        $this->autenticar();

        Log::factory()->count(3)->create();

        $response = $this->getJson('/api/logs');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'usuario_id', 'tabla_afectada', 'id_registro', 'accion', 'descripcion', 'fecha']
                 ]);
    }

    
    public function test_puede_crear_log()
    {
        $this->autenticar();

        $usuario = Usuario::factory()->create();

        $response = $this->postJson('/api/logs', [
            'usuario_id' => $usuario->id,
            'tabla_afectada' => 'productos',
            'id_registro' => 5,
            'accion' => 'crear',
            'descripcion' => 'Se creó un nuevo producto',
            'fecha' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'tabla_afectada' => 'productos',
                     'accion' => 'crear'
                 ]);

        $this->assertDatabaseHas('logs', [
            'usuario_id' => $usuario->id,
            'tabla_afectada' => 'productos',
            'accion' => 'crear'
        ]);
    }

    
    public function test_puede_ver_log()
    {
        $this->autenticar();

        $log = Log::factory()->create();

        $response = $this->getJson("/api/logs/{$log->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $log->id]);
    }

}
