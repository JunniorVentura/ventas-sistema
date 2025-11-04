<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Factura;
use App\Models\Boleta;
use App\Models\DetallePedido;
use App\Models\Cliente;
use App\Models\Producto;
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

    // Productos más vendidos con filtro de año y mes
    public function topProductosVendidos(Request $request)
    {
        $productos = DetallePedido::selectRaw('productos.id as producto_id, productos.nombre, SUM(detalle_pedidos.cantidad) as total_vendido')
            ->join('pedidos', 'detalle_pedidos.pedido_id', '=', 'pedidos.id')
            ->join('productos', 'detalle_pedidos.producto_id', '=', 'productos.id')
            ->where('pedidos.estado_pedido', 'pagado')
            ->when($request->filled('anio') && is_numeric($request->anio), function ($query) use ($request) {
                return $query->whereYear('pedidos.fecha', (int) $request->anio);
            })
            ->when($request->filled('mes') && is_numeric($request->mes), function ($query) use ($request) {
                return $query->whereMonth('pedidos.fecha', (int) $request->mes);
            })
            ->groupBy('productos.id', 'productos.nombre')  // PostgreSQL requiere todos los SELECT que no estén agregados aquí
            ->orderByDesc('total_vendido')
            ->take(10)
            ->get();
    
        return response()->json($productos);
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
    
    public function ventasPorMes($anio)
    {
        try {
            if (!is_numeric($anio) || $anio < 2000 || $anio > date('Y') + 1) {
                return response()->json(['error' => 'Año inválido'], 400);
            }
    
            $ventas = Pedido::selectRaw('EXTRACT(MONTH FROM fecha) as mes, SUM(total) as total_ventas')
                ->whereYear('fecha', $anio)
                ->where('estado_pedido', 'pagado')
                ->groupByRaw('EXTRACT(MONTH FROM fecha)')
                ->orderByRaw('EXTRACT(MONTH FROM fecha)')
                ->get()
                ->keyBy('mes');
    
            $resultados = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $resultados[] = [
                    'mes' => $mes,
                    'total_ventas' => isset($ventas[$mes]) ? (float) $ventas[$mes]->total_ventas : 0,
                ];
            }
    
            return response()->json($resultados);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }    
        
    // Clientes registrados por mes
    public function clientesPorMes($anio)
    {
        try {
            if (!is_numeric($anio) || $anio < 2000 || $anio > date('Y') + 1) {
                return response()->json(['error' => 'Año inválido'], 400);
            }

            $clientes = Cliente::selectRaw('EXTRACT(MONTH FROM created_at) as mes, COUNT(*) as total_clientes')
                ->whereYear('created_at', $anio)
                ->groupByRaw('EXTRACT(MONTH FROM created_at)')
                ->orderByRaw('EXTRACT(MONTH FROM created_at)')
                ->get()
                ->keyBy('mes');

            $resultados = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $resultados[] = [
                    'mes' => $mes,
                    'total_clientes' => isset($clientes[$mes]) ? (int) $clientes[$mes]->total_clientes : 0,
                ];
            }

            return response()->json($resultados);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
