<?php

namespace App\Http\Controllers;

use App\Models\Incidente;
use App\Models\Activo;
use App\Http\Requests\StoreIncidenteRequest;
use App\Http\Requests\UpdateIncidenteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncidenteController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $incidentes = Incidente::with('activo')->paginate(15);
            return response()->json([
                'success' => true,
                'data' => $incidentes,
                'message' => 'Incidentes obtenidos exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener incidentes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los incidentes',
            ], 500);
        }
    }

    public function store(StoreIncidenteRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            try {
                $incidente = Incidente::create($request->validated());
                $incidente->load('activo');
                return response()->json([
                    'success' => true,
                    'data' => $incidente,
                    'message' => 'Incidente registrado exitosamente',
                ], 201);
            } catch (\Exception $e) {
                Log::error('Error al registrar incidente: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el incidente',
                ], 500);
            }
        });
    }

    public function update(UpdateIncidenteRequest $request, $id): JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                $incidente = Incidente::findOrFail($id);
                $incidente->update($request->validated());
                $incidente->load('activo');
                return response()->json([
                    'success' => true,
                    'data' => $incidente,
                    'message' => 'Incidente actualizado exitosamente',
                ]);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incidente no encontrado',
                ], 404);
            } catch (\Exception $e) {
                Log::error('Error al actualizar incidente: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el incidente',
                ], 500);
            }
        });
    }

    public function show($id): JsonResponse
    {
        try {
            $incidente = Incidente::with('activo')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $incidente,
                'message' => 'Incidente obtenido exitosamente',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Incidente no encontrado',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener incidente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el incidente',
            ], 500);
        }
    }

    public function getActivos(): JsonResponse
    {
        try {
            $activos = Activo::where('estado', true)->get(['id', 'codigo_inventario']);
            return response()->json([
                'success' => true,
                'data' => $activos,
                'message' => 'Activos obtenidos exitosamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener activos para incidentes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los activos',
            ], 500);
        }
    }
}