<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Boleta;
use App\Models\Log;
use Illuminate\Http\Request;

class BoletaController extends Controller
{
    // GET /api/boletas
    public function index()
    {
        return Boleta::with('pedido')
            ->where('estado', true)
            ->get();
    }

    // POST /api/boletas
    public function store(Request $request)
    {
        $request->validate([
            'pedido_id'      => 'required|exists:pedidos,id',
            'dni_cliente'    => 'required|string|max:15',
            'nombre_cliente' => 'required|string',
            'total'          => 'required|numeric|min:0',
        ]);

        $boleta = Boleta::create([
            'pedido_id'      => $request->pedido_id,
            'dni_cliente'    => $request->dni_cliente,
            'nombre_cliente' => $request->nombre_cliente,
            'total'          => $request->total,
            'fecha_emision'  => now(),
            'estado'         => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'boletas',
            'id_registro'    => $boleta->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó una nueva boleta',
            'fecha'          => now(),
        ]);

        return response()->json($boleta, 201);
    }

    // GET /api/boletas/{id}
    public function show($id)
    {
        $boleta = Boleta::with('pedido')->findOrFail($id);
        return response()->json($boleta);
    }

    // PUT /api/boletas/{id}
    public function update(Request $request, $id)
    {
        $boleta = Boleta::findOrFail($id);

        $request->validate([
            'pedido_id'      => 'sometimes|required|exists:pedidos,id',
            'dni_cliente'    => 'sometimes|required|string|max:15',
            'nombre_cliente' => 'sometimes|required|string',
            'total'          => 'sometimes|required|numeric|min:0',
            'estado'         => 'boolean',
        ]);

        $boleta->update($request->only([
            'pedido_id',
            'dni_cliente',
            'nombre_cliente',
            'total',
            'estado'
        ]));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'boletas',
            'id_registro'    => $boleta->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se modificó la boleta',
            'fecha'          => now(),
        ]);

        return response()->json($boleta);
    }

    // DELETE /api/boletas/{id}
    public function destroy($id)
    {
        $boleta = Boleta::findOrFail($id);
        $boleta->estado = false;
        $boleta->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'boletas',
            'id_registro'    => $boleta->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica de la boleta',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Boleta desactivada']);
    }
}
