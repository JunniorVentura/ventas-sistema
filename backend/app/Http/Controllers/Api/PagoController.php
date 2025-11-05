<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Log;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    // GET /api/pagos
    /*public function index()
    {
        return Pago::with('pedido')
            ->where('estado', true)
            ->get();
    }*/
    // GET /api/pagos
    public function index()
    {
        $pagos = Pago::with(['pedido.cliente', 'pedido.usuario', 'pedido.boleta', 'pedido.factura'])
            ->where('estado', true)
            ->orderBy('pedido_id', 'asc') // 🔹 Ordena por pedido_id
            ->get();
    
        return $pagos->map(function ($pago) {
            return [
                'id'              => $pago->id,
                'pedido_id'       => $pago->pedido_id,
                'cliente_id'      => $pago->pedido->cliente_id,
                'cliente'         => $pago->pedido->cliente->nombre ?? null,
                'dni'             => $pago->pedido->cliente->dni ?? null,
                'ruc'             => $pago->pedido->cliente->ruc ?? null,
                'razon_social'    => $pago->pedido->cliente->razon_social ?? null,
                'usuario_id'      => $pago->pedido->usuario_id,
                'usuario'         => $pago->pedido->usuario->nombre ?? null,
                'metodo_pago'     => $pago->metodo_pago,
                'estado_pago'     => $pago->estado_pago,
                'fecha_pago'      => $pago->fecha_pago,
                'monto_pago'      => $pago->pedido->total,
                'fecha_pedido'    => $pago->pedido->fecha,
                'boleta_emitida'  => $pago->pedido->boleta !== null,
                'factura_emitida' => $pago->pedido->factura !== null,
                'estado'          => $pago->estado,
            ];
        });
    }
    
    // POST /api/pagos
    public function store(Request $request)
    {
        $request->validate([
            'pedido_id'    => 'required|exists:pedidos,id',
            'metodo_pago'  => 'required|in:efectivo,yape,transferencia',
            'estado_pago'  => 'in:pendiente,verificado,rechazado',
        ]);

        $pago = Pago::create([
            'pedido_id'    => $request->pedido_id,
            'metodo_pago'  => $request->metodo_pago,
            'estado_pago'  => $request->estado_pago ?? 'pendiente',
            'fecha_pago'   => now(),
            'estado'       => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'pagos',
            'id_registro'    => $pago->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se realizó un pago',
            'fecha'          => now(),
        ]);

        return response()->json($pago, 201);
    }

    // GET /api/pagos/{id}
    /*public function show($id)
    {
        $pago = Pago::with('pedido')->findOrFail($id);
        return response()->json($pago);
    }*/
    // GET /api/pagos/{id}
    public function show($id)
    {
        $pago = Pago::with([
            'pedido.cliente',   // Incluye los datos del cliente asociado al pedido
            'pedido.usuario'    // Incluye los datos del usuario asociado al pedido
        ])->findOrFail($id);

        return response()->json([
            'id'           => $pago->id,
            'pedido_id'    => $pago->pedido_id,
            'cliente_id'   => $pago->pedido->cliente_id,
            'cliente'      => $pago->pedido->cliente->nombre ?? null,
            'dni'          => $pago->pedido->cliente->dni ?? null,
            'ruc'          => $pago->pedido->cliente->ruc ?? null,
            'razon_social' => $pago->pedido->cliente->razon_social ?? null,
            'usuario_id'   => $pago->pedido->usuario_id,
            'usuario'      => $pago->pedido->usuario->nombre ?? null,
            'usuario'      => $pago->pedido->usuario->nombre ?? null,
            'metodo_pago'  => $pago->metodo_pago,
            'estado_pago'  => $pago->estado_pago,
            'fecha_pago'   => $pago->fecha_pago,
            'monto_pago'   => $pago->pedido->total,
            'fecha_pedido' => $pago->pedido->fecha,
            'estado'       => $pago->estado,
        ]);
    }
 
    // PUT /api/pagos/{id}
    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $request->validate([
            'pedido_id'    => 'sometimes|required|exists:pedidos,id',
            'metodo_pago'  => 'sometimes|required|in:efectivo,yape,transferencia',
            'estado_pago'  => 'sometimes|required|in:pendiente,verificado,rechazado',
            'estado'       => 'boolean',
        ]);

        $pago->update($request->only([
            'pedido_id',
            'metodo_pago',
            'estado_pago',
            'estado'
        ]));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'pagos',
            'id_registro'    => $pago->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó el estado de un pago',
            'fecha'          => now(),
        ]);

        return response()->json($pago);
    }

    // DELETE /api/pagos/{id}
    public function destroy($id)
    {
        $pago = Pago::findOrFail($id);
        $pago->estado = false;
        $pago->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'pagos',
            'id_registro'    => $pago->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica de un pago',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Pago desactivado']);
    }
}
