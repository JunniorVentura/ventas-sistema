<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    // GET /api/logs
    public function index()
    {
        return Log::with('usuario')
            ->orderByDesc('fecha')
            ->get();
    }

    // POST /api/logs
    public function store(Request $request)
    {
        $request->validate([
            'usuario_id'     => 'required|exists:usuarios,id',
            'tabla_afectada' => 'required|string|max:50',
            'id_registro'    => 'required|integer',
            'accion'         => 'required|in:crear,editar,eliminar,login,logout',
            'descripcion'    => 'nullable|string',
        ]);

        $log = Log::create([
            'usuario_id'     => $request->usuario_id,
            'tabla_afectada' => $request->tabla_afectada,
            'id_registro'    => $request->id_registro,
            'accion'         => $request->accion,
            'descripcion'    => $request->descripcion,
            'fecha'          => now(),
        ]);

        return response()->json($log, 201);
    }

    // GET /api/logs/{id}
    public function show($id)
    {
        $log = Log::with('usuario')->findOrFail($id);
        return response()->json($log);
    }

    public function filtrarPorUsuario($id)
    {
        return Log::where('usuario_id', $id)->orderBy('fecha', 'desc')->get();
    }

    public function filtrarPorTabla($tabla)
    {
        return Log::where('tabla_afectada', $tabla)->orderBy('fecha', 'desc')->get();
    }

    public function filtrarPorAccion($accion)
    {
        return Log::where('accion', $accion)->orderBy('fecha', 'desc')->get();
    }

}
