<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoArea;
use App\Models\Area;
use App\Models\Datos;
use App\Models\Incidente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Get all dashboard data for assets.
     */
    public function getActivosporArea()
    {
        try {
            $totalAssets = Activo::where('estado', 1)->count();

            $assetsByArea = ActivoArea::select('areas.nombre as area', DB::raw('count(activos_areas.idActivo) as count'))
                ->join('areas', 'activos_areas.idArea', '=', 'areas.idArea')
                ->join('activos', 'activos_areas.idActivo', '=', 'activos.idActivo')
                ->where('activos.estado', 1)
                ->groupBy('areas.idArea', 'areas.nombre')
                ->get();

            return response()->json([
                'totalAssets' => $totalAssets,
                'assetsByArea' => $assetsByArea,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getActivosporArea: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch dashboard data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dashboard data for users with client role (idRol = 2).
     */
    public function getUsersByArea()
    {
        try {
            // Get total number of active client users
            $totalUsers = User::where('estado', 1)
                ->where('idRol', 2)
                ->count();

            // Get client users grouped by area
            $usersByArea = Datos::select('areas.nombre as area', DB::raw('count(datos.idDatos) as count'))
                ->leftJoin('areas', 'datos.idArea', '=', 'areas.idArea')
                ->join('usuarios', 'datos.idDatos', '=', 'usuarios.idDatos')
                ->where('usuarios.estado', 1)
                ->where('usuarios.idRol', 2)
                ->groupBy('areas.idArea', 'areas.nombre')
                ->get();

            // Get detailed client user information by area
            $usersDetails = Datos::select(
                'areas.nombre as area',
                'datos.nombre',
                'datos.apellido',
                'roles.nombre as rol'
            )
                ->leftJoin('areas', 'datos.idArea', '=', 'areas.idArea')
                ->join('usuarios', 'datos.idDatos', '=', 'usuarios.idDatos')
                ->join('roles', 'usuarios.idRol', '=', 'roles.idRol')
                ->where('usuarios.estado', 1)
                ->where('usuarios.idRol', 2)
                ->orderBy('areas.nombre')
                ->orderBy('datos.nombre')
                ->get();

            return response()->json([
                'totalUsers' => $totalUsers,
                'usersByArea' => $usersByArea,
                'usersDetails' => $usersDetails,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getUsersByArea: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch user dashboard data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Get dashboard data for areas.
     */
    public function getAreasData()
    {
        try {
            // Get total number of areas
            $totalAreas = Area::count();

            // Get detailed area information
            $areasDetails = Area::select('nombre as area')
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'totalAreas' => $totalAreas,
                'areasDetails' => $areasDetails,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getAreasData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch areas dashboard data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dashboard data for incidents.
     */
    public function getIncidentsData()
    {
        try {
            // Get total number of incidents
            $totalIncidents = Incidente::count();

            // Get incidents grouped by status
            $incidentsByStatus = Incidente::select(
                DB::raw('CASE 
                    WHEN estado = 0 THEN "Pendiente" 
                    WHEN estado = 1 THEN "En progreso" 
                    WHEN estado = 2 THEN "Resuelto" 
                    END as status'),
                DB::raw('count(*) as count')
            )
                ->groupBy('estado')
                ->get();

            // Get detailed incident information
            $incidentsDetails = Incidente::select(
                'incidentes.titulo',
                'incidentes.descripcion',
                'incidentes.fecha_reporte',
                'incidentes.prioridad', // Raw numeric value (0, 1, 2)
                'incidentes.estado', // Raw numeric value (0, 1, 2)
                'areas.nombre as area',
                DB::raw('CONCAT(activos.codigo_inventario, " - ", activos.marca_modelo, " (", activos.tipo, ")") as activo')
            )
                ->leftJoin('areas', 'incidentes.idArea', '=', 'areas.idArea')
                ->join('activos', 'incidentes.idActivo', '=', 'activos.idActivo')
                ->orderBy('incidentes.fecha_reporte', 'desc')
                ->get();

            return response()->json([
                'totalIncidents' => $totalIncidents,
                'incidentsByStatus' => $incidentsByStatus,
                'incidentsDetails' => $incidentsDetails,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getIncidentsData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch incidents dashboard data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}