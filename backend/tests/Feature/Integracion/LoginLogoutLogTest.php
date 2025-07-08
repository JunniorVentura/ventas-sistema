<?php

namespace Tests\Feature\Integracion;

use App\Models\Usuario;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Permiso;

class LoginLogoutLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_se_registra_log_en_login_y_logout()
    {
        // Crear usuario con contraseña conocida
        $usuario = Usuario::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);
    
        // LOGIN
        $responseLogin = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);
    
        $responseLogin->assertStatus(200)
                      ->assertJsonStructure(['usuario', 'token']);
    
        $token = $responseLogin->json('token');
    
        $this->assertDatabaseHas('logs', [
            'usuario_id' => $usuario->id,
            'accion' => 'login',
        ]);
    
        // LOGOUT usando el token devuelto
        $responseLogout = $this->withHeader('Authorization', 'Bearer ' . $token)
                               ->postJson('/api/logout');
    
        $responseLogout->assertStatus(200)
                       ->assertJsonFragment(['message' => 'Sesión cerrada correctamente']);
    
        $this->assertDatabaseHas('logs', [
            'usuario_id' => $usuario->id,
            'accion' => 'logout',
        ]);
    }
    
}
