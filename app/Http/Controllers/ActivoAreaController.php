<?php

namespace App\Http\Controllers;

use App\Models\ActivoArea;
use App\Models\Area;
use App\Models\Activo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ActivoAreaController extends Controller
{
    /**
     * Listar activos asignados a un área específica
     */
    public function index($idArea): JsonResponse
    {
        try {
            $area = Area::find($idArea);
            if (!$area) {
                return response()->json(['success' => false, 'message' => 'Área no encontrada'], 404);
            }

            $activos = ActivoArea::where('idArea', $idArea)
                ->with(['activo' => function ($query) {
                    $query->select('idActivo', 'codigo_inventario', 'tipo', 'marca_modelo', 'estado');
                }, 'area' => function ($query) {
                    $query->select('idArea', 'nombre');
                }])
                ->get()
                ->map(function ($activoArea) {
                    $activo = $activoArea->activo;
                    return [
                        'idActivoArea' => $activoArea->id, // Primary key of activos_areas
                        'idActivo' => $activo->idActivo,
                        'idArea' => $activoArea->idArea,
                        'codigo_inventario' => $activo->codigo_inventario,
                        'tipo' => $activo->tipo,
                        'marca_modelo' => $activo->marca_modelo,
                        'estado' => $activo->estado,
                        'area' => [
                            'idArea' => $activoArea->area->idArea,
                            'nombre' => $activoArea->area->nombre,
                        ],
                        'created_at' => $activoArea->created_at,
                        'updated_at' => $activoArea->updated_at,
                    ];
                });

            return response()->json(['success' => true, 'data' => $activos, 'message' => 'Activos obtenidos exitosamente'], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener activos por área: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener los activos'], 500);
        }
    }

    /**
     * Listar todos los activos con su estado de asignación
     */
    public function indexActivos(): JsonResponse
    {
        try {
            $activos = Activo::with(['areas' => function ($query) {
                $query->select('areas.idArea', 'areas.nombre');
            }])->get()->map(function ($activo) {
                return [
                    'idActivo' => $activo->idActivo,
                    'codigo_inventario' => $activo->codigo_inventario,
                    'tipo' => $activo->tipo,
                    'marca_modelo' => $activo->marca_modelo,
                    'estado' => $activo->estado,
                    'isAssigned' => $activo->areas->isNotEmpty(),
                    'assigned_area' => $activo->areas->isNotEmpty() ? [
                        'idArea' => $activo->areas->first()->idArea,
                        'nombre' => $activo->areas->first()->nombre
                    ] : null,
                    'created_at' => $activo->created_at,
                    'updated_at' => $activo->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $activos,
                'message' => 'Activos obtenidos exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener activos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los activos',
            ], 500);
        }
    }

    /**
     * Asignar un activo a un área
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'idActivo' => 'required|exists:activos,idActivo',
            'idArea' => 'required|exists:areas,idArea',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Verificar si el activo ya está asignado a cualquier área
        $existingAssignment = ActivoArea::where('idActivo', $request->idActivo)->first();
        if ($existingAssignment) {
            return response()->json([
                'success' => false,
                'message' => 'El activo ya está asignado a un área. Actualice la asignación existente.',
            ], 409);
        }

        // Verificar si la relación ya existe para esta área específica
        $existing = ActivoArea::where('idActivo', $request->idActivo)
            ->where('idArea', $request->idArea)
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'El activo ya está asignado a esta área'], 409);
        }

        try {
            $activoArea = ActivoArea::create([
                'idActivo' => $request->idActivo,
                'idArea' => $request->idArea,
            ]);

            return response()->json([
                'success' => true,
                'data' => $activoArea,
                'message' => 'Activo asignado exitosamente',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al asignar activo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al asignar el activo'], 500);
        }
    }

    /**
     * Actualizar la asignación de un activo a un área
     */
    public function update(Request $request, $id): JsonResponse
    {
        $activoArea = ActivoArea::find($id);
        if (!$activoArea) {
            return response()->json(['success' => false, 'message' => 'Relación no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'idActivo' => 'required|exists:activos,idActivo',
            'idArea' => 'required|exists:areas,idArea',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Verificar si la nueva relación ya existe
        $existing = ActivoArea::where('idActivo', $request->idActivo)
            ->where('idArea', $request->idArea)
            ->where('id', '!=', $id)
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'El activo ya está asignado a esta área'], 409);
        }

        try {
            $activoArea->update([
                'idActivo' => $request->idActivo,
                'idArea' => $request->idArea,
            ]);

            return response()->json([
                'success' => true,
                'data' => $activoArea,
                'message' => 'Asignación actualizada exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar asignación: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar la asignación'], 500);
        }
    }

    /**
     * Eliminar la asignación de un activo a un área
     */
    public function destroy($id): JsonResponse
    {
        $activoArea = ActivoArea::find($id);
        if (!$activoArea) {
            return response()->json(['success' => false, 'message' => 'Relación no encontrada'], 404);
        }

        try {
            $activoArea->delete();
            return response()->json(['success' => true, 'message' => 'Asignación eliminada exitosamente'], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar asignación: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar la asignación'], 500);
        }
    }
}