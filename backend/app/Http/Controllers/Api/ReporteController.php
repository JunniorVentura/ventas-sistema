<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Factura;
use App\Models\Boleta;
use App\Models\DetallePedido;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function ventasPorFecha(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);
    
        return Pedido::with('cliente', 'usuario')
            ->whereBetween('fecha', [$request->desde, $request->hasta])
            ->where('estado_pedido', 'pagado')
            ->orderBy('fecha', 'desc')
            ->get();
    }    

    //productos mas vendidos
    public function topProductosVendidos()
    {
        return DetallePedido::with('producto')
            ->selectRaw('producto_id, SUM(cantidad) as total_vendido')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->take(10)
            ->get()
            ->map(function ($detalle) {
                return [
                    'producto_id' => $detalle->producto_id,
                    'nombre' => $detalle->producto->nombre ?? 'Desconocido',
                    'total_vendido' => $detalle->total_vendido,
                ];
            });
    }

    public function pagosPorEstado($estado)
    {
        return Pago::with('pedido.cliente', 'pedido')
            ->where('estado_pago', $estado)
            ->orderBy('fecha_pago', 'desc')
            ->get();
    }
    
    //contar facturas
    public function contarFacturas($anio, $mes)
    {

        try {
            if (!is_numeric($anio) || !is_numeric($mes)) {
                return response()->json(['error' => 'Año o mes inválido'], 400);
            }

            //return response()->json(['anio' => $anio,'mes' => $mes]);

            $total = Factura::whereNotNull('fecha_emision')
            ->whereYear('fecha_emision', $anio)
            ->whereMonth('fecha_emision', $mes)
            ->count();

    
            return response()->json(['total' => $total]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    //contar boletas
    public function contarBoletas($anioBoleta, $mesBoleta)
    {

        try {
            if (!is_numeric($anioBoleta) || !is_numeric($mesBoleta)) {
                return response()->json(['error' => 'Año o mes inválido'], 400);
            }

            //return response()->json(['anioBoleta' => $anioBoleta,'mesBoleta' => $mesBoleta]);

            $total = Boleta::whereNotNull('fecha_emision')
            ->whereYear('fecha_emision', $anioBoleta)
            ->whereMonth('fecha_emision', $mesBoleta)
            ->count();
    
            return response()->json(['total' => $total]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

}
