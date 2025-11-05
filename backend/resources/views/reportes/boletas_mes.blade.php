<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Boletas Emitidas</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <h2>Boletas Emitidas - {{ $mesBoleta }}/{{ $anioBoleta }}</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>DNI</th>
                <th>Total</th>
                <th>Fecha Emisión</th>
            </tr>
        </thead>
        <tbody>
            @foreach($boletas as $i => $boleta)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $boleta->pedido->cliente->nombre }}</td>
                <td>{{ $boleta->dni_cliente }}</td>
                <td>{{ number_format($boleta->total, 2) }}</td>
                <td>{{ $boleta->fecha_emision }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
