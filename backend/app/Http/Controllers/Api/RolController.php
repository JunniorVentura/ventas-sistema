<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Log;
use Illuminate\Http\Request;

class RolController extends Controller
{
    // GET /api/roles
    public function index()
    {
        return Rol::where('estado', true)->get();
    }

    // POST /api/roles
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
        ]);

        $rol = Rol::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'roles',
            'id_registro'    => $rol->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo rol',
            'fecha'          => now(),
        ]);


        return response()->json($rol, 201);
    }

    // GET /api/roles/{id}
    public function show($id)
    {
        $rol = Rol::findOrFail($id);
        return response()->json($rol);
    }

    // PUT /api/roles/{id}
    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
        ]);

        $rol->update($request->only(['nombre', 'descripcion', 'estado']));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'roles',
            'id_registro'    => $rol->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó un rol',
            'fecha'          => now(),
        ]);

        return response()->json($rol);
    }

    // DELETE /api/roles/{id}
    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->estado = false;
        $rol->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'roles',
            'id_registro'    => $rol->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica de un rol',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Rol desactivado']);
    }
}
