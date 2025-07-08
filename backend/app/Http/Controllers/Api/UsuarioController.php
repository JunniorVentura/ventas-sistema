<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Log;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    // GET /api/usuarios
    public function index()
    {
        return Usuario::with('rol')
            ->where('estado', true)
            ->get();
    }

    

    // POST /api/usuarios
    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:100',
            'email'     => 'required|email|unique:usuarios,email',
            'password'  => 'required|string|min:8',
            'rol_id'    => 'required|exists:roles,id',
        ]);

        $usuario = Usuario::create([
            'nombre'     => $request->nombre,
            'email'      => $request->email,
            'password'   => Hash::make($request->password), // Hashear contraseña
            'rol_id'     => $request->rol_id,
            'estado'     => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id()?? $usuario->id, // Si no hay login previo
            'tabla_afectada' => 'usuarios',
            'id_registro'    => $usuario->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo usuario',
            'fecha'          => now(),
        ]);

        return response()->json($usuario, 201);
    }

    // GET /api/usuarios/{id}
    public function show($id)
    {
        $usuario = Usuario::with('rol')->findOrFail($id);
        return response()->json($usuario);
    }

    // PUT /api/usuarios/{id}
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
    
        $request->validate([
            'nombre'    => 'sometimes|required|string|max:100',
            'email'     => 'sometimes|required|email|unique:usuarios,email,' . $id,
            'password'  => 'nullable|string|min:8',
            'rol_id'    => 'sometimes|required|exists:roles,id',
            'estado'    => 'boolean',
        ]);
    
        $data = $request->only(['nombre', 'email', 'rol_id', 'estado']);
    
        // Si desea cambiar contraseña, validar contraseña actual
        if ($request->filled('password')) {
            if (!$request->filled('current_password')) {
                return response()->json(['errors' => ['current_password' => ['Debes ingresar la contraseña actual']]], 422);
            }
    
            if (!Hash::check($request->current_password, $usuario->password)) {
                return response()->json(['errors' => ['current_password' => ['Contraseña actual incorrecta']]], 422);
            }
    
            $data['password'] = Hash::make($request->password);
        }
    
        $usuario->update($data);
    
        // Crear log
        Log::create([
            'usuario_id'     => auth()->id(),
            'tabla_afectada' => 'usuarios',
            'id_registro'    => $usuario->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó los datos del usuario',
            'fecha'          => now(),
        ]);
    
        return response()->json($usuario);
    }

    // DELETE /api/usuarios/{id}
    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        // Verificar si tiene pedidos asociados
        if ($usuario->pedidos()->exists()) {
            return response()->json([
                'error' => 'No se puede eliminar el usuario porque tiene pedidos asociados.'
            ], 409); // 409 Conflict
        }

        $usuario->estado = false;
        $usuario->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(),
            'tabla_afectada' => 'usuarios',
            'id_registro'    => $usuario->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica del usuario',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Usuario desactivado']);
    }

    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('email', $request->email)
                          ->where('estado', true)
                          ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        // Crear el log
        Log::create([
            'usuario_id'     => $usuario->id, // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'usuarios',
            'id_registro'    => $usuario->id,
            'accion'         => 'login',
            'descripcion'    => 'Inicio de sesión registrado en usuario',
            'fecha'          => now(),
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'usuario'      => $usuario,
        ]);
    }
}
