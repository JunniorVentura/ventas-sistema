<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_usuario_puede_iniciar_sesion_y_recibir_token()
    {
        $usuario = Usuario::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
            'estado' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['usuario', 'token']);
    }

    
    public function test_usuario_no_puede_iniciar_sesion_con_datos_incorrectos()
    {
        Usuario::factory()->create([
            'email' => 'fail@example.com',
            'password' => Hash::make('correcta'),
            'estado' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'fail@example.com',
            'password' => 'incorrecta',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    
    public function test_usuario_autenticado_puede_ver_su_perfil()
    {
        $usuario = Usuario::factory()->create();
        Sanctum::actingAs($usuario);

        $response = $this->getJson('/api/perfil');

        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => $usuario->email]);
    }

    
    public function test_usuario_no_autenticado_no_puede_ver_perfil()
    {
        $response = $this->getJson('/api/perfil');

        $response->assertStatus(401);
    }

    
    public function test_usuario_puede_cerrar_sesion()
    {
        $usuario = Usuario::factory()->create();
        Sanctum::actingAs($usuario);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Sesión cerrada correctamente']);
    }

    public function test_token_expirado_no_permite_acceder_al_perfil()
    {
        $usuario = Usuario::factory()->create([
            'token_expiration' => now()->subMinutes(1), // Token ya expirado
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->getJson('/api/perfil');

        $response->assertStatus(401)
                ->assertJson(['message' => 'Token expirado. Por favor inicie sesión nuevamente.']);
    }


}
