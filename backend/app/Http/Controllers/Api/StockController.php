<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Log;
use Illuminate\Http\Request;

class StockController extends Controller
{
    // GET /api/stock
    public function index()
    {
        return Stock::with('producto')
            ->where('estado', true)
            ->get();
    }

    // POST /api/stock
    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad'    => 'required|integer|min:0',
        ]);

        $stock = Stock::create([
            'producto_id' => $request->producto_id,
            'cantidad'    => $request->cantidad,
            'estado'      => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'stock',
            'id_registro'    => $stock->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo stock ('.$request->cantidad.') del producto '.$request->producto_id,
            'fecha'          => now(),
        ]);

        return response()->json($stock, 201);
    }

    // GET /api/stock/{id}
    public function show($id)
    {
        $stock = Stock::with('producto')->findOrFail($id);
        return response()->json($stock);
    }

    // PUT /api/stock/{id}
    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);

        $request->validate([
            'cantidad'    => 'sometimes|required|integer|min:0',
            'producto_id' => 'sometimes|required|exists:productos,id',
            'estado'      => 'boolean',
        ]);

        $stock->update(array_merge(
            $request->only(['cantidad', 'producto_id', 'estado']),
            ['updated_at' => now()]
        ));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'stock',
            'id_registro'    => $stock->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó en ('.$request->cantidad.') el stock del producto '.$request->producto_id,
            'fecha'          => now(),
        ]);

        return response()->json($stock);
    }

    // DELETE /api/stock/{id}
    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        $stock->estado = false;
        $stock->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'stock',
            'id_registro'    => $stock->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica del stock',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Stock desactivado']);
    }
}
