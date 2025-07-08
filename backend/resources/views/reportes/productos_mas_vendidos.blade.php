<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Productos Más Vendidos</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <h2>Productos Más Vendidos</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID Producto</th>
                <th>Producto</th>
                <th>Cantidad Vendida</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $i => $producto)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $producto['producto_id'] }}</td>
                <td>{{ $producto['nombre'] }}</td>
                <td>{{ $producto['total_vendido'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
