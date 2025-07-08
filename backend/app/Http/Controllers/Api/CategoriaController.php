<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Log;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // GET /api/categorias
    public function index()
    {
        return Categoria::where('estado', true)->get();
    }

    // POST /api/categorias
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $categoria = Categoria::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'estado'      => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'categorias',
            'id_registro'    => $categoria->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó una nueva categoría',
            'fecha'          => now(),
        ]);

        return response()->json($categoria, 201);
    }

    // GET /api/categorias/{id}
    public function show($id)
    {
        $categoria = Categoria::findOrFail($id);
        return response()->json($categoria);
    }

    // PUT /api/categorias/{id}
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
        ]);

        $categoria->update($request->only(['nombre', 'descripcion', 'estado']));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'categorias',
            'id_registro'    => $categoria->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se modificó la categoría',
            'fecha'          => now(),
        ]);

        return response()->json($categoria);
    }

    // DELETE /api/categorias/{id}
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->estado = false;
        $categoria->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'categorias',
            'id_registro'    => $categoria->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica de la categoría',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Categoría desactivada']);
    }
}
