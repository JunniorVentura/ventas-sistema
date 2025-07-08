<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleFactura;
use App\Models\Log;
use Illuminate\Http\Request;

class DetalleFacturaController extends Controller
{
    // GET /api/detalle-facturas
    public function index()
    {
        return DetalleFactura::with(['factura', 'producto'])
            ->where('estado', true)
            ->get();
    }

    // POST /api/detalle-facturas
    public function store(Request $request)
    {
        $request->validate([
            'factura_id'      => 'required|exists:facturas,id',
            'producto_id'     => 'required|exists:productos,id',
            'cantidad'        => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $subtotal = $request->cantidad * $request->precio_unitario;

        $detalle = DetalleFactura::create([
            'factura_id'      => $request->factura_id,
            'producto_id'     => $request->producto_id,
            'cantidad'        => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'subtotal'        => $subtotal,
            'estado'          => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_factura',
            'id_registro'    => $detalle->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo detalle de factura',
            'fecha'          => now(),
        ]);

        return response()->json($detalle, 201);
    }

    // GET /api/detalle-facturas/{id}
    public function show($id)
    {
        $detalle = DetalleFactura::with(['factura', 'producto'])->findOrFail($id);
        return response()->json($detalle);
    }

    // PUT /api/detalle-facturas/{id}
    public function update(Request $request, $id)
    {
        $detalle = DetalleFactura::findOrFail($id);

        $request->validate([
            'factura_id'      => 'sometimes|required|exists:facturas,id',
            'producto_id'     => 'sometimes|required|exists:productos,id',
            'cantidad'        => 'sometimes|required|integer|min:1',
            'precio_unitario' => 'sometimes|required|numeric|min:0',
            'estado'          => 'boolean',
        ]);

        $data = $request->only(['factura_id', 'producto_id', 'cantidad', 'precio_unitario', 'estado']);

        // Recalcular subtotal si se actualiza cantidad o precio
        $cantidad = $data['cantidad'] ?? $detalle->cantidad;
        $precio   = $data['precio_unitario'] ?? $detalle->precio_unitario;
        $data['subtotal'] = $cantidad * $precio;

        $detalle->update($data);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_factura',
            'id_registro'    => $detalle->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó el detalle de una factura',
            'fecha'          => now(),
        ]);

        return response()->json($detalle);
    }

    // DELETE /api/detalle-facturas/{id}
    public function destroy($id)
    {
        $detalle = DetalleFactura::findOrFail($id);
        $detalle->estado = false;
        $detalle->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'detalle_factura',
            'id_registro'    => $detalle->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica del detalle de una factura',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Detalle de factura desactivado']);
    }
}
