<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Bajo</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <h2>Productos con Stock Bajo</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Cantidad en Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $i => $producto)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $producto['nombre'] }}</td>
                <td>{{ $producto['cantidad'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
