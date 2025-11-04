<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RolPermiso;
use App\Models\Rol;
use App\Models\Log;
use Illuminate\Http\Request;

class RolPermisoController extends Controller
{
    // GET /api/rol-permisos
    public function index()
    {
        return RolPermiso::where('estado', true)
            ->with(['rol', 'permiso']) // relaciones si están definidas en el modelo
            ->get();
    }

    // POST /api/rol-permisos
    public function store(Request $request)
    {
        $request->validate([
            'rol_id' => 'required|exists:roles,id',
            'permiso_id' => 'required|exists:permisos,id',
        ]);

        // Verificar si la relación ya existe
        $relacion = RolPermiso::where('rol_id', $request->rol_id)
            ->where('permiso_id', $request->permiso_id)
            ->first();

        if ($relacion) {
            if (!$relacion->estado) {
                // Si existe pero está desactivada, se reactiva
                $relacion->estado = true;
                $relacion->save();

                // Log de reactivación
                Log::create([
                    'usuario_id'     => auth()->id(),
                    'tabla_afectada' => 'rol_permiso',
                    'id_registro'    => $relacion->id,
                    'accion'         => 'reactivar',
                    'descripcion'    => 'Se reactivó el permiso al rol ID: '.$request->rol_id,
                    'fecha'          => now(),
                ]);

                return response()->json(['message' => 'Permiso reactivado correctamente', 'data' => $relacion], 200);
            }

            return response()->json(['message' => 'El permiso ya está asignado a este rol'], 409); // 409 Conflict
        }

        // Si no existe, se crea la nueva relación
        $relacion = RolPermiso::create([
            'rol_id'     => $request->rol_id,
            'permiso_id' => $request->permiso_id,
            'estado'     => true,
        ]);

        Log::create([
            'usuario_id'     => auth()->id(),
            'tabla_afectada' => 'rol_permiso',
            'id_registro'    => $relacion->id,
            'accion'         => 'crear',
            'descripcion'    => 'Se asignó un nuevo permiso al rol ID: '.$request->rol_id,
            'fecha'          => now(),
        ]);

        return response()->json($relacion, 201);
    }


    // GET /api/rol-permisos/{id}
    public function show($id)
    {
        $relacion = RolPermiso::with(['rol', 'permiso'])->findOrFail($id);
        return response()->json($relacion);
    }

    // PUT /api/rol-permisos/{id}
    public function update(Request $request, $id)
    {
        $relacion = RolPermiso::findOrFail($id);

        $request->validate([
            'rol_id' => 'sometimes|required|exists:roles,id',
            'permiso_id' => 'sometimes|required|exists:permisos,id',
            'estado' => 'boolean',
        ]);

        // Verificar si se intenta cambiar a una relación que ya existe y está activa
        $nuevoRolId = $request->rol_id ?? $relacion->rol_id;
        $nuevoPermisoId = $request->permiso_id ?? $relacion->permiso_id;

        $duplicado = RolPermiso::where('id', '!=', $relacion->id)
            ->where('rol_id', $nuevoRolId)
            ->where('permiso_id', $nuevoPermisoId)
            ->where('estado', true)
            ->first();

        if ($duplicado) {
            return response()->json(['message' => 'Ya existe una relación activa con esos valores'], 409);
        }

        // Actualiza la relación
        $relacion->update($request->only(['rol_id', 'permiso_id', 'estado']));

        // Crear log
        Log::create([
            'usuario_id'     => auth()->id(),
            'tabla_afectada' => 'rol_permiso',
            'id_registro'    => $relacion->id,
            'accion'         => 'editar',
            'descripcion'    => 'Se actualizó la relación rol-permiso',
            'fecha'          => now(),
        ]);

        return response()->json($relacion);
    }


    // DELETE /api/rol-permisos/{id}
    public function destroy($id)
    {
        $relacion = RolPermiso::findOrFail($id);
        $relacion->estado = false;
        $relacion->save();

        // Crear el log
        Log::create([
            'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
            'tabla_afectada' => 'rol_permiso',
            'id_registro'    => $relacion->id,
            'accion'         => 'eliminar',
            'descripcion'    => 'Eliminación lógica del permiso',
            'fecha'          => now(),
        ]);

        return response()->json(['message' => 'Relación Rol-Permiso desactivada']);
    }
    
    public function asignar(Request $request, Rol $rol)
    {
        $request->validate([
            'permiso_ids' => 'required|array',
            'permiso_ids.*' => 'exists:permisos,id',
        ]);
    
        // Limpia relaciones previas
        RolPermiso::where('rol_id', $rol->id)->delete();
    
        foreach ($request->permiso_ids as $permiso_id) {
            RolPermiso::create([
                'rol_id' => $rol->id,
                'permiso_id' => $permiso_id,
                'estado' => true,
            ]);

            // Crear el log
            Log::create([
                'usuario_id'     => auth()->id(), // o auth()->id() si prefieres usar el usuario autenticado
                'tabla_afectada' => 'rol_permiso',
                'id_registro'    => $permiso_id,
                'accion'         => 'crear',
                'descripcion'    => 'Se actualizó el permiso del rol'.$rol->id,
                'fecha'          => now(),
            ]);

        }
    
        return response()->json(['mensaje' => 'Permisos asignados correctamente']);
    }
    
    public function listar(Rol $rol)
    {
        $permisos = RolPermiso::where('rol_id', $rol->id)
                    ->where('estado', true)
                    ->with('permiso')
                    ->get()
                    ->pluck('permiso');
    
        return response()->json($permisos);
    }

}
