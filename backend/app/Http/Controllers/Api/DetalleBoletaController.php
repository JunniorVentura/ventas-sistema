<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleBoleta;
use App\Models\Log;
use Illuminate\Http\Request;

class DetalleBoletaController extends Controller
{
    // GET /api/detalle-boletas
    public function index()
    {
        return DetalleBoleta::with(['boleta', 'producto'])
            ->where('estado', true)
            ->get();
    }

    // POST /api/detalle-boletas
    public function store(Request $request)
    {
        $request->validate([
            'boleta_id'       => 'required|exists:boletas,id',
            'producto_id'     => 'required|exists:productos,id',
            'cantidad'        => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $subtotal = $request->cantidad * $request->precio_unitario;

        $detalle = DetalleBoleta::create([
            'boleta_id'       => $request->boleta_id,
            'producto_id'     => $request->producto_id,
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'subtotal'        => $subtotal,
            'estado'          => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_boleta',
            'id_registro'    => $detalle->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó el detalle de una boleta',
            'fecha'          => now(),
        ]);

        return response()->json($detalle, 201);
    }

    // GET /api/detalle-boletas/{id}
    public function show($id)
    {
        $detalle = DetalleBoleta::with(['boleta', 'producto'])->findOrFail($id);
        return response()->json($detalle);
    }

    // PUT /api/detalle-boletas/{id}
    public function update(Request $request, $id)
    {
        $detalle = DetalleBoleta::findOrFail($id);

        $request->validate([
            'boleta_id'       => 'sometimes|required|exists:boletas,id',
            'producto_id'     => 'sometimes|required|exists:productos,id',
            'cantidad'        => 'sometimes|required|integer|min:1',
            'precio_unitario' => 'sometimes|required|numeric|min:0',
            'estado'          => 'boolean',
        ]);

        $data = $request->only(['boleta_id', 'producto_id', 'cantidad', 'precio_unitario', 'estado']);

        $cantidad = $data['cantidad'] ?? $detalle->cantidad;
        $precio   = $data['precio_unitario'] ?? $detalle->precio_unitario;
        $data['subtotal'] = $cantidad * $precio;

        $detalle->update($data);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_boleta',
            'id_registro'    => $detalle->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó el detalle de una boleta',
            'fecha'          => now(),
        ]);

        return response()->json($detalle);
    }

    // DELETE /api/detalle-boletas/{id}
    public function destroy($id)
    {
        $detalle = DetalleBoleta::findOrFail($id);
        $detalle->estado = false;
        $detalle->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_boleta',
            'id_registro'    => $detalle->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica del detalle de una boleta',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Detalle de boleta desactivado']);
    }
}
