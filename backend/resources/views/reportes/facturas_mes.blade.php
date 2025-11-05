<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facturas Emitidas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <h2>Facturas Emitidas - {{ $mes }}/{{ $anio }}</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>RUC</th>
                <th>Total</th>
                <th>Fecha Emisión</th>
            </tr>
        </thead>
        <tbody>
            @foreach($facturas as $i => $factura)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $factura->pedido->cliente->nombre }}</td>
                <td>{{ $factura->ruc_cliente }}</td>
                <td>{{ number_format($factura->total, 2) }}</td>
                <td>{{ $factura->fecha_emision }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
