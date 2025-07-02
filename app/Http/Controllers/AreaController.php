<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Utilities\PaginationTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class AreaController extends Controller
{

    use PaginationTrait;
    
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Area::query();

            // Aplicar paginación sin filtros ni búsqueda
            $areas = $this->applyPagination(
                $query,
                $request,
                [],
                [],
                8
            );

            return $this->paginatedResponse($areas, 'Areas obtenidos exitosamente');
        } catch (\Exception $e) {
            Log::error('Error al obtener areas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los areas',
            ], 500);
        }
    }

    public function index2()
    {
        $areas = Area::all();
        return response()->json(['success' => true, 'data' => $areas], 200);
    }


    public function show($id)
    {
        $area = Area::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Área no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $area], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $area = Area::create($request->only('nombre'));

        return response()->json(['success' => true, 'data' => $area, 'message' => 'Área registrada exitosamente'], 201);
    }

    public function update(Request $request, $id)
    {
        $area = Area::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Área no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $area->update($request->only('nombre'));

        return response()->json(['success' => true, 'data' => $area, 'message' => 'Área actualizada exitosamente'], 200);
    }

    public function destroy($id)
    {
        $area = Area::find($id);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Área no encontrada'], 404);
        }

        $area->delete();
        return response()->json(['success' => true, 'message' => 'Área eliminada exitosamente'], 200);
    }
}