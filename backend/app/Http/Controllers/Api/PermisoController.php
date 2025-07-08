<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\Log;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    // GET /api/permisos
    public function index()
    {
        return Permiso::where('estado', true)->get();
    }

    // POST /api/permisos
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
        ]);

        $permiso = Permiso::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'permisos',
            'id_registro'    => $permiso->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo permiso',
            'fecha'          => now(),
        ]);

        return response()->json($permiso, 201);
    }

    // GET /api/permisos/{id}
    public function show($id)
    {
        $permiso = Permiso::findOrFail($id);
        return response()->json($permiso);
    }

    // PUT /api/permisos/{id}
    public function update(Request $request, $id)
    {
        $permiso = Permiso::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
        ]);

        $permiso->update($request->only(['nombre', 'descripcion', 'estado']));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'permisos',
            'id_registro'    => $permiso->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó un permiso',
            'fecha'          => now(),
        ]);

        return response()->json($permiso);
    }

    // DELETE /api/permisos/{id}
    public function destroy($id)
    {
        $permiso = Permiso::findOrFail($id);
        $permiso->estado = false;
        $permiso->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'permisos',
            'id_registro'    => $permiso->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica de un permiso',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Permiso desactivado']);
    }
}
