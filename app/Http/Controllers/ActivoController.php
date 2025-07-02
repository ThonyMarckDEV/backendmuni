<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Http\Requests\StoreActivoRequest;
use App\Http\Requests\UpdateActivoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utilities\PaginationTrait;
use Illuminate\Http\Request;

class ActivoController extends Controller
{

    use PaginationTrait;
    
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Activo::query();

            // Aplicar paginación sin filtros ni búsqueda
            $activos = $this->applyPagination(
                $query,
                $request,
                [], // No search fields
                [], // No filter fields
                8   // Default items per page
            );

            return $this->paginatedResponse($activos, 'Activos obtenidos exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al obtener activos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los activos',
            ], 500);
        }
    }

    public function store(StoreActivoRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            try {
                $activo = Activo::create($request->validated());
                return response()->json([
                    'success' => true,
                    'data' => $activo,
                    'message' => 'Activo registrado exitosamente',
                ], 201);
            } catch (\Exception $e) {
                Log::error('Error al registrar activo: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el activo',
                ], 500);
            }
        });
    }

    public function update(UpdateActivoRequest $request, int $id): JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                $activo = Activo::findOrFail($id);
                $activo->update($request->validated());
                return response()->json([
                    'success' => true,
                    'data' => $activo,
                    'message' => 'Activo actualizado exitosamente',
                ]);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activo no encontrado',
                ], 404);
            } catch (\Exception $e) {
                Log::error('Error al actualizar activo: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el activo',
                ], 500);
            }
        });
    }

    public function show(int $id): JsonResponse
    {
        try {
            $activo = Activo::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $activo,
                'message' => 'Activo obtenido exitosamente',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activo no encontrado',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener activo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el activo',
            ], 500);
        }
    }
}