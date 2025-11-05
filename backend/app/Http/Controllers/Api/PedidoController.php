<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Log;
use Illuminate\Http\Request;

class PedidoController extends Controller
{ 
    // GET /api/pedidos
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'usuario', 'pago', 'factura', 'boleta', 'detalle_pedidos.producto'])
            ->where('estado', true)
            ->get();
    
        return $pedidos->map(function ($pedido) {
            return [
                'id' => $pedido->id,
                'cliente' => $pedido->cliente->nombre ?? 'Cliente',
                'dni' => $pedido->cliente->dni ?? 'dni',
                'ruc' => $pedido->cliente->ruc ?? 'ruc',
                'razon_social' => $pedido->cliente->razon_social ?? 'razon_social',
                'usuario' => $pedido->usuario->nombre ?? 'Usuario',
                'total' => $pedido->total,
                'estado_pedido' => $pedido->estado_pedido,
                'boleta_emitida' => $pedido->boleta !== null,
                'factura_emitida' => $pedido->factura !== null,
                'pagos' => $pedido->pago,
                'detalle_pedidos' => $pedido->detalle_pedidos,
            ];
        });
    }
   
    // POST /api/pedidos
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'usuario_id'     => 'required|exists:usuarios,id',
            'total'          => 'required|numeric|min:0',
            'estado_pedido'  => 'in:pendiente,pagado,cancelado',
        ]);

        $pedido = Pedido::create([
            'cliente_id'     => $request->cliente_id,
            'usuario_id'     => $request->usuario_id,
            'total'          => $request->total,
            'estado_pedido'  => $request->estado_pedido ?? 'pendiente',
            'estado'         => true,
            'fecha'          => now(),
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'pedidos',
            'id_registro'    => $pedido->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo pedido',
            'fecha'          => now(),
        ]);

        return response()->json($pedido, 201);
    }

    // GET /api/pedidos/{id}
    public function show($id)
    {
        $pedido = Pedido::with(['cliente', 'usuario'])->findOrFail($id);
        return response()->json($pedido);
    }

    // PUT /api/pedidos/{id}
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'cliente_id'     => 'sometimes|required|exists:clientes,id',
            'usuario_id'     => 'sometimes|required|exists:usuarios,id',
            'total'          => 'sometimes|required|numeric|min:0',
            'estado_pedido'  => 'in:pendiente,pagado,cancelado',
            'estado'         => 'boolean',
        ]);

        $pedido->update($request->only([
            'cliente_id',
            'usuario_id',
            'total',
            'estado_pedido',
            'estado',
        ]));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'pedidos',
            'id_registro'    => $pedido->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó el estado del pedido',
            'fecha'          => now(),
        ]);

        return response()->json($pedido);
    }

    // DELETE /api/pedidos/{id}
    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->estado = false;
        $pedido->save();
        // Crear el log

        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'pedidos',
            'id_registro'    => $pedido->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica del pedido',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Pedido desactivado']);
    }
}
