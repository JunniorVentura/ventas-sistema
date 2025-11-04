<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Stock;
use App\Models\Log;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // GET /api/productos
    public function index()
    {
        return Producto::with(['categoria', 'stock']) // Añades 'stock' aquí
            ->where('estado', true)
            ->get();
    }    

    // POST /api/productos
    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:100',
            'descripcion'  => 'nullable|string',
            'precio'       => 'required|numeric|min:0',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        // Crear el producto
        $producto = Producto::create([
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'precio'       => $request->precio,
            'categoria_id' => $request->categoria_id,
            'estado'       => true,
        ]);

        // Crear el stock en 0 asociado a ese producto
        $stock = Stock::create([
            'producto_id' => $producto->id,
            'cantidad'    => 0,
            'estado'      => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'stock',
            'id_registro'    => $stock->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo stock (0) del producto '.$producto->id,
            'fecha'          => now(),
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(),
            'tabla_afectada' => 'productos',
            'id_registro'    => $producto->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo producto',
            'fecha'          => now(),
        ]);

        return response()->json($producto, 201);
    }


    // GET /api/productos/{id}
    public function show($id)
    {
        $producto = Producto::with('categoria')->findOrFail($id);
        return response()->json($producto);
    }

    // PUT /api/productos/{id}
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre'       => 'sometimes|required|string|max:100',
            'descripcion'  => 'nullable|string',
            'precio'       => 'sometimes|required|numeric|min:0',
            'categoria_id' => 'sometimes|required|exists:categorias,id',
            'estado'       => 'boolean',
        ]);

        $producto->update($request->only([
            'nombre', 'descripcion', 'precio', 'categoria_id', 'estado'
        ]));

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'productos',
            'id_registro'    => $producto->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó un producto',
            'fecha'          => now(),
        ]);

        return response()->json($producto);
    }

    // DELETE /api/productos/{id}
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->estado = false;
        $producto->save();

        // Desactivar stock relacionado (si existe)
        $stock = Stock::where('producto_id', $producto->id)->first();
        if ($stock) {
            $stock->estado = false;
            $stock->save();

            // Log del stock
            Log::create([
                'usuario_id'     => auth()->id(),
                'tabla_afectada' => 'stock',
                'id_registro'    => $stock->id,
                'accion'         => 'eliminar',
                'descripcion'    => 'Se desactivó el stock del producto: ' . $producto->nombre,
                'fecha'          => now(),
            ]);
        }

        // Log del producto
        Log::create([
            'usuario_id'     => auth()->id(),
            'tabla_afectada' => 'productos',
            'id_registro'    => $producto->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica de un producto',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Producto y su stock desactivados']);
    }

}
