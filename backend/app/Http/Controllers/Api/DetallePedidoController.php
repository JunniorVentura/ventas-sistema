<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\Log;
use Illuminate\Http\Request;

class DetallePedidoController extends Controller
{
    // GET /api/detalle-pedidos
    public function index()
    {
        return DetallePedido::with(['pedido', 'producto'])
            ->where('estado', true)
            ->get();
    }

    // POST /api/detalle-pedidos
    public function store(Request $request)
    {
        $request->validate([
            'pedido_id'      => 'required|exists:pedidos,id',
            'producto_id'    => 'required|exists:productos,id',
            'cantidad'       => 'required|integer|min:1',
            'precio_unitario'=> 'required|numeric|min:0',
        ]);

        $subtotal = $request->cantidad * $request->precio_unitario;

        $detalle = DetallePedido::create([
            'pedido_id'       => $request->pedido_id,
            'producto_id'     => $request->producto_id,
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'subtotal'        => $subtotal,
            'estado'          => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_pedidos',
            'id_registro'    => $detalle->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo detalle de pedido',
            'fecha'          => now(),
        ]);

        return response()->json($detalle, 201);
    }

    // GET /api/detalle-pedidos/{id}
    public function show($id)
    {
        $detalle = DetallePedido::with(['pedido', 'producto'])->findOrFail($id);
        return response()->json($detalle);
    }

    // PUT /api/detalle-pedidos/{id}
    public function update(Request $request, $id)
    {
        $detalle = DetallePedido::findOrFail($id);

        $request->validate([
            'pedido_id'       => 'sometimes|required|exists:pedidos,id',
            'producto_id'     => 'sometimes|required|exists:productos,id',
            'cantidad'        => 'sometimes|required|integer|min:1',
            'precio_unitario' => 'sometimes|required|numeric|min:0',
            'estado'          => 'boolean',
        ]);

        $data = $request->only(['pedido_id', 'producto_id', 'cantidad', 'precio_unitario', 'estado']);

        // Recalcular subtotal si cantidad o precio cambiaron
        $cantidad = $data['cantidad'] ?? $detalle->cantidad;
        $precio = $data['precio_unitario'] ?? $detalle->precio_unitario;
        $data['subtotal'] = $cantidad * $precio;

        $detalle->update($data);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_pedidos',
            'id_registro'    => $detalle->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó un nuevo detalle de pedido',
            'fecha'          => now(),
        ]);

        return response()->json($detalle);
    }

    // DELETE /api/detalle-pedidos/{id}
    public function destroy($id)
    {
        $detalle = DetallePedido::findOrFail($id);
        $detalle->estado = false;
        $detalle->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_pedidos',
            'id_registro'    => $detalle->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Elimianción lógica de un detalle de pedido',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Detalle de pedido desactivado']);
    }
}
