<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Log;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    // GET /api/clientes
    public function index()
    {
        return Cliente::where('estado', true)->get();
    }

    // POST /api/clientes
    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:100',
            'dni'           => 'nullable|string|max:15',
            'ruc'           => 'nullable|string|max:15',
            'razon_social'  => 'nullable|string|max:150',
            'direccion'     => 'nullable|string',
            'telefono'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:100',
        ]);

        $cliente = Cliente::create([
            'nombre'        => $request->nombre,
            'dni'           => $request->dni,
            'ruc'           => $request->ruc,
            'razon_social'  => $request->razon_social,
            'direccion'     => $request->direccion,
            'telefono'      => $request->telefono,
            'email'         => $request->email,
            'estado'        => true,
        ]);

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'clientes',
            'id_registro'    => $cliente->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se creó un nuevo cliente',
            'fecha'          => now(),
        ]);

        return response()->json($cliente, 201);
    }

    // GET /api/clientes/{id}
    public function show($id)
    {
        $cliente = Cliente::findOrFail($id);
        return response()->json($cliente);
    }

    // PUT /api/clientes/{id}
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $request->validate([
            'nombre'        => 'sometimes|required|string|max:100',
            'dni'           => 'nullable|string|max:15',
            'ruc'           => 'nullable|string|max:15',
            'razon_social'  => 'nullable|string|max:150',
            'direccion'     => 'nullable|string',
            'telefono'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:100',
            'estado'        => 'boolean',
        ]);

        $cliente->update($request->only([
            'nombre', 'dni', 'ruc', 'razon_social', 'direccion', 'telefono', 'email', 'estado'
        ]));
        
        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'clientes',
            'id_registro'    => $cliente->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizaron los datos del cliente',
            'fecha'          => now(),
        ]);

        return response()->json($cliente);
    }

    // DELETE /api/clientes/{id}
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->estado = false;
        $cliente->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'clientes',
            'id_registro'    => $cliente->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica del cliente',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Cliente desactivado']);
    }
}
