<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_elimina_tokens_y_limpia_expiracion()
    {
        // Crear usuario y asignar expiración
        $usuario = Usuario::factory()->create([
            'token_expiration' => now()->addMinutes(10),
        ]);

        // Simular autenticación
        Sanctum::actingAs($usuario);

        // Verificar que el token_expiration existe
        $this->assertNotNull($usuario->fresh()->token_expiration);

        // Hacer logout
        $response = $this->postJson('/api/logout');

        // Verificar respuesta
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Sesión cerrada correctamente']);

        // Verificar que token_expiration se limpió
        $this->assertNull($usuario->fresh()->token_expiration);

        // Verificar que no quedan tokens
        $this->assertCount(0, $usuario->tokens);
    }
}
