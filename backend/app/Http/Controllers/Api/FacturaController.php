<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\Log;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    // GET /api/facturas
    public function index()
    {
        return Factura::with('pedido')
            ->where('estado', true)
            ->get();
    }

    // POST /api/facturas
    public function store(Request $request)
    {
        $request->validate([
            'pedido_id'     => 'required|exists:pedidos,id',
            'ruc_cliente'   => 'required|string|max:15',
            'razon_social'  => 'required|string',
            'total'         => 'required|numeric|min:0',
        ]);

        $factura = Factura::create([
            'pedido_id'     => $request->pedido_id,
            'ruc_cliente'   => $request->ruc_cliente,
            'razon_social'  => $request->razon_social,
            'total'         => $request->total,
            'fecha_emision' => now(),
            'estado'        => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'facturas',
            'id_registro'    => $factura->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó una nueva factura',
            'fecha'          => now(),
        ]);

        return response()->json($factura, 201);
    }

    // GET /api/facturas/{id}
    public function show($id)
    {
        $factura = Factura::with('pedido')->findOrFail($id);
        return response()->json($factura);
    }

    // PUT /api/facturas/{id}
    public function update(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);

        $request->validate([
            'pedido_id'     => 'sometimes|required|exists:pedidos,id',
            'ruc_cliente'   => 'sometimes|required|string|max:15',
            'razon_social'  => 'sometimes|required|string',
            'total'         => 'sometimes|required|numeric|min:0',
            'estado'        => 'boolean',
        ]);

        $factura->update($request->only([
            'pedido_id',
            'ruc_cliente',
            'razon_social',
            'total',
            'estado'
        ]));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'facturas',
            'id_registro'    => $factura->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó una factura',
            'fecha'          => now(),
        ]);

        return response()->json($factura);
    }

    // DELETE /api/facturas/{id}
    public function destroy($id)
    {
        $factura = Factura::findOrFail($id);
        $factura->estado = false;
        $factura->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'facturas',
            'id_registro'    => $factura->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica de una factura',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Factura desactivada']);
    }
}
