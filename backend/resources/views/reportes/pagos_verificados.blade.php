<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pagos Verificados</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <h2>Pagos Verificados</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Usuario</th>
                <th>Método</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $i => $pago)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pago->pedido->cliente->nombre }}</td>
                <td>{{ $pago->pedido->usuario->nombre }}</td>
                <td>{{ ucfirst($pago->metodo_pago) }}</td>
                <td>{{ $pago->fecha_pago }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
