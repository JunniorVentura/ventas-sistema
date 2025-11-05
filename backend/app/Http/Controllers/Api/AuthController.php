<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Iniciar sesión
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
    
        $usuario = Usuario::where('email', $request->email)
                          ->where('estado', true)
                          ->first();
    
        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }
    
        // Eliminar tokens anteriores (opcional)
        $usuario->tokens()->delete();
    
        // Crear nuevo token
        $token = $usuario->createToken('api_token')->plainTextToken;
    
        // Establecer expiración del token (por ejemplo, 1 hora desde ahora)
        $usuario->token_expiration = now()->addHour(); 
        $usuario->save();
    
        // Crear el log
        Log::create([
            'usuario_id'     => $usuario->id,
            'tabla_afectada' => 'usuarios',
            'id_registro'    => $usuario->id,
            'accion'         => 'login',
            'descripcion'    => 'Inicio de sesión registrado en Auth',
            'fecha'          => now(),
        ]);
    
        return response()->json([
            'usuario' => $usuario,
            'token'   => $token,
        ]);
    }    

    // Cerrar sesión
    public function logout(Request $request)
    {
        $usuario = $request->user();
    
        // Eliminar todos los tokens activos
        $usuario->tokens()->delete();
    
        // Opcional: Limpiar campo de expiración
        $usuario->token_expiration = null;
        $usuario->save();
    
        // Crear el log
        Log::create([
            'usuario_id'     => $usuario->id,
            'tabla_afectada' => 'usuarios',
            'id_registro'    => $usuario->id,
            'accion'         => 'logout',
            'descripcion'    => 'Fin de sesión registrado en Auth',
            'fecha'          => now(),
        ]);
    
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
    

    // Obtener usuario autenticado
    public function perfil(Request $request)
    {
        $usuario = $request->user();
    
        // Verifica token expirado
        if ($usuario->token_expiration && now()->greaterThan($usuario->token_expiration)) {
            $usuario->tokens()->delete();
            return response()->json(['message' => 'Token expirado, por favor inicie sesión nuevamente.'], 401);
        }
    
        // Obtener permisos del rol del usuario
        //$permisos = $usuario->rol->permisos()->pluck('nombre')->toArray(); 
        $permisos = $usuario->rol?->permisos()->pluck('nombre')->toArray() ?? [];// <--- OBLIGATORIO

        return response()->json([
            'usuario' => $usuario,
            'permisos' => $permisos,
        ]);
    }
    

    //registrar
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6|confirmed',
        ]);
    
        $usuario = Usuario::create([
            'nombre'   => $request->nombre,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'rol_id'   => 1, // Puedes asignar un rol por defecto
            'estado'   => true,
            'created_at' => now(),
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id()?? $usuario->id, // Si no hay login previo
            'tabla_afectada' => 'usuarios',
            'id_registro'    => $usuario->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo usuario usando Auth',
            'fecha'          => now(),
        ]);

        $token = $usuario->createToken('api_token')->plainTextToken;
    
        return response()->json([
            'usuario' => $usuario,
            'token'   => $token,
        ], 201);
    }    
    
}
