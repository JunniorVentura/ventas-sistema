<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Factura;
use App\Models\Boleta;
use App\Models\DetallePedido;
use App\Models\Producto;
use App\Models\Stock;
use PDF;

class ReportePdfController extends Controller
{
    protected function generarPdf($vista, $datos, $nombreArchivo)
    {
        $pdf = PDF::loadView($vista, $datos);
        return $pdf->download($nombreArchivo);
    }

    // Reporte: Ventas entre fechas
    public function ventasPorFecha(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);
    
        // Verifica que esto devuelva valores correctos
        // dd($request->all());
    
        $pedidos = Pedido::with('cliente', 'usuario')
            ->whereBetween('fecha', [$request->desde, $request->hasta])
            ->where('estado_pedido', 'pagado')
            ->orderByDesc('fecha')
            ->get();
    
        return $this->generarPdf('reportes.ventas', [
            'pedidos' => $pedidos,
            'desde' => $request->desde,
            'hasta' => $request->hasta,
        ], 'reporte_ventas');
    }

    // Reporte: Top productos más vendidos
    public function productosMasVendidos()
    {
        $productos = DetallePedido::with('producto')
            ->selectRaw('producto_id, SUM(cantidad) as total_vendido')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->take(10)
            ->get()
            ->map(function ($detalle) {
                return [
                    'producto_id'   => $detalle->producto_id,
                    'nombre'        => $detalle->producto->nombre ?? 'Desconocido',
                    'total_vendido' => $detalle->total_vendido,
                ];
            });

        return $this->generarPdf('reportes.productos_mas_vendidos', [
            'productos' => $productos
        ], 'reporte_productos_mas_vendidos');
    }


    // Reporte: Productos con stock bajo (<10)
    public function stockBajo()
    {
        $productos = Stock::with('producto')
            ->where('cantidad', '<', 10)
            ->orderBy('cantidad')
            ->get()
            ->map(function ($stock) {
                return [
                    'nombre'   => $stock->producto->nombre ?? 'Desconocido',
                    'cantidad' => $stock->cantidad,
                ];
            });

        return $this->generarPdf('reportes.stock_bajo', [
            'productos' => $productos
        ], 'reporte_stock_bajo');
    }

    // Reporte: Pagos por estado dinámico (verificado, pendiente, rechazado)
    public function pdfPagosPorEstado(Request $request, $estado)
    {
        // Validar que el estado sea uno permitido
        if (!in_array($estado, ['verificado', 'pendiente', 'rechazado'])) {
            return response()->json(['error' => 'Estado de pago no válido.'], 400);
        }

        $pagos = Pago::with('pedido.cliente', 'pedido.usuario')
            ->where('estado_pago', $estado)
            ->orderByDesc('fecha_pago')
            ->get();

        return $this->generarPdf('reportes.pagos_verificados', [
            'pagos' => $pagos
        ], "reporte_pagos_{$estado}");
    }

    // Reporte: Facturas emitidas por mes
    public function facturasPorMes($anio, $mes)
    {
        if (!is_numeric($anio) || !is_numeric($mes)) {
            abort(400, 'Año o mes inválido');
        }
    
        $facturas = Factura::with('pedido.cliente')
            ->whereYear('fecha_emision', $anio)
            ->whereMonth('fecha_emision', $mes)
            ->get();
    
        return $this->generarPdf('reportes.facturas_mes', [
            'facturas' => $facturas,
            'anio'     => $anio,
            'mes'      => $mes
        ], "reporte_facturas_{$anio}_{$mes}");
    }

    // Reporte: Facturas emitidas por mes
    public function boletasPorMes($anioBoleta , $mesBoleta )
    {
        if (!is_numeric($anioBoleta ) || !is_numeric($mesBoleta)) {
            abort(400, 'Año o mes inválido');
        }
    
        $boletas = Boleta::with('pedido.cliente')
            ->whereYear('fecha_emision', $anioBoleta )
            ->whereMonth('fecha_emision', $mesBoleta )
            ->get();

        // Generar nombre con año, mes, día, hora, minuto y segundo
        $fechaActual = now()->format('Y-m-d_H-i-s');
        $nombreArchivo = "reporte_boletas_{$anioBoleta}_{$mesBoleta}_{$fechaActual}.pdf";

        return $this->generarPdf('reportes.boletas_mes', [
            'boletas'   => $boletas,
            'anioBoleta' => $anioBoleta,
            'mesBoleta'  => $mesBoleta
        ], $nombreArchivo);
    } 
}
