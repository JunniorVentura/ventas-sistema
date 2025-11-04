<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        h2 { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h2>Reporte de Ventas</h2>
    <p><strong>Desde:</strong> {{ $desde }} | <strong>Hasta:</strong> {{ $hasta }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Total (S/)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $i => $pedido)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pedido->cliente->nombre }}</td>
                <td>{{ $pedido->usuario->nombre }}</td>
                <td>{{ $pedido->fecha }}</td>
                <td>{{ number_format($pedido->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
