<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RolController extends Controller
{
    /**
     * Obtiene todos los roles activos para el formulario.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $roles = Rol::where('estado', 1)->get(['idRol as id', 'nombre']);

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Roles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener roles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo rol en la base de datos.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:roles,nombre',
                'estado' => 'required|in:0,1',
            ]);

            $rol = Rol::create($validated);

            return response()->json([
                'success' => true,
                'data' => $rol,
                'message' => 'Rol creado exitosamente'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear rol: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un rol específico.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $rol = Rol::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $rol,
                'message' => 'Rol obtenido exitosamente'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener rol: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un rol específico.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        try {
            $rol = Rol::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:255|unique:roles,nombre,' . $id . ',idRol',
                'estado' => 'sometimes|required|in:0,1',
            ]);

            $rol->update($validated);

            return response()->json([
                'success' => true,
                'data' => $rol,
                'message' => 'Rol actualizado exitosamente'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar rol: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un rol de la base de datos.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $rol = Rol::findOrFail($id);
            
            // Verificar si el rol está siendo usado por algún usuario
            if ($rol->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el rol porque está asignado a usuarios'
                ], 400);
            }

            $rol->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar rol: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}