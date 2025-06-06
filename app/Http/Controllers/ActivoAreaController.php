<?php

namespace App\Http\Controllers;

use App\Models\ActivoArea;
use App\Models\Area;
use App\Models\Activo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivoAreaController extends Controller
{
    /**
     * Listar activos asignados a un área específica
     */
    public function index($idArea)
    {
        $area = Area::find($idArea);
        if (!$area) {
            return response()->json(['success' => false, 'message' => 'Área no encontrada'], 404);
        }

        $activos = $area->activos()->with(['areas'])->get();
        return response()->json(['success' => true, 'data' => $activos], 200);
    }

    /**
     * Asignar un activo a un área
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idActivo' => 'required|exists:activos,idActivo',
            'idArea' => 'required|exists:areas,idArea',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Verificar si la relación ya existe
        $existing = ActivoArea::where('idActivo', $request->idActivo)
            ->where('idArea', $request->idArea)
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'El activo ya está asignado a esta área'], 409);
        }

        $activoArea = ActivoArea::create([
            'idActivo' => $request->idActivo,
            'idArea' => $request->idArea,
        ]);

        return response()->json(['success' => true, 'data' => $activoArea, 'message' => 'Activo asignado exitosamente'], 201);
    }

    /**
     * Actualizar la asignación de un activo a un área
     */
    public function update(Request $request, $id)
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

        $activoArea->update([
            'idActivo' => $request->idActivo,
            'idArea' => $request->idArea,
        ]);

        return response()->json(['success' => true, 'data' => $activoArea, 'message' => 'Asignación actualizada exitosamente'], 200);
    }

    /**
     * Eliminar la asignación de un activo a un área
     */
    public function destroy($id)
    {
        $activoArea = ActivoArea::find($id);
        if (!$activoArea) {
            return response()->json(['success' => false, 'message' => 'Relación no encontrada'], 404);
        }

        $activoArea->delete();
        return response()->json(['success' => true, 'message' => 'Asignación eliminada exitosamente'], 200);
    }
}
