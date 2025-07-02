<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoArea;
use App\Models\Area;
use App\Models\Datos;
use App\Models\Incidente;
use App\Models\User;
use App\Utilities\PaginationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use PaginationTrait;
      /**
     * Get dashboard data for assets based on user role.
     * idRol = 1: All active assets across all areas.
     * idRol = 2: Assets in the user's assigned area.
     * idRol = 3: Unauthorized (technicians don't access assets).
     */
    public function getActivosporArea()
    {
        try {
            // Get the authenticated user
            $user = Auth::user();

            // Initialize $userData for idRol = 2
            $userData = null;
            if ($user->idRol == 2) {
                $userData = Datos::where('idDatos', $user->idDatos)->first();
                if (!$userData || !$userData->idArea) {
                    return response()->json([
                        'error' => 'No area assigned',
                        'message' => 'User has no assigned area',
                    ], 404);
                }
            }

            // Initialize query
            $query = ActivoArea::join('activos', 'activos_areas.idActivo', '=', 'activos.idActivo')
                ->join('areas', 'activos_areas.idArea', '=', 'areas.idArea')
                ->where('activos.estado', 1);

            // Filter by area for idRol = 2
            if ($user->idRol == 2) {
                $query->where('activos_areas.idArea', $userData->idArea);
            }

            // Get total number of active assets
            $totalAssets = $query->count();

            // Get assets grouped by area
            $assetsByArea = $query->select(
                'areas.nombre as area',
                DB::raw('count(activos_areas.idActivo) as count')
            )
                ->groupBy('areas.idArea', 'areas.nombre')
                ->get();

            // Get detailed asset information (no GROUP BY)
            $assetsDetailsQuery = ActivoArea::select(
                'areas.nombre as area',
                'activos.codigo_inventario',
                'activos.marca_modelo',
                'activos.tipo',
                'activos.ubicacion'
            )
                ->join('activos', 'activos_areas.idActivo', '=', 'activos.idActivo')
                ->join('areas', 'activos_areas.idArea', '=', 'areas.idArea')
                ->where('activos.estado', 1);

            // Apply area filter for idRol = 2
            if ($user->idRol == 2 && $userData) {
                $assetsDetailsQuery->where('activos_areas.idArea', $userData->idArea);
            }

            $assetsDetails = $assetsDetailsQuery->orderBy('areas.nombre')
                ->orderBy('activos.codigo_inventario')
                ->get();

            return response()->json([
                'totalAssets' => $totalAssets,
                'assetsByArea' => $assetsByArea,
                'assetsDetails' => $assetsDetails,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getActivosporArea: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch assets dashboard data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get dashboard data for incidents based on user role.
     * idRol = 1: All incidents.
     * idRol = 2: Incidents reported by the user.
     * idRol = 3: Incidents assigned to the technician.
     */
    public function getIncidentsData()
    {
        try {
            // Get the authenticated user
            $user = Auth::user();

            // Initialize query
            $query = Incidente::query();

            // Filter based on role
            if ($user->idRol == 2) {
                // User: Incidents they reported
                $query->where('idUsuario', $user->idUsuario);
            } elseif ($user->idRol == 3) {
                // Technician: Incidents assigned to them
                $query->where('idTecnico', $user->idUsuario);
            }
            // idRol = 1: No filter (all incidents)

            // Get total number of incidents
            $totalIncidents = $query->count();

            // Get incidents grouped by status
            $incidentsByStatus = $query->select(
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
            $detailsQuery = Incidente::select(
                'incidentes.titulo',
                'incidentes.descripcion',
                'incidentes.fecha_reporte',
                'incidentes.prioridad', // Raw numeric value (0, 1, 2)
                'incidentes.estado', // Raw numeric value (0, 1, 2)
                'areas.nombre as area',
                DB::raw('CONCAT(activos.codigo_inventario, " - ", activos.marca_modelo, " (", activos.tipo, ")") as activo')
            )
                ->leftJoin('areas', 'incidentes.idArea', '=', 'areas.idArea')
                ->join('activos', 'incidentes.idActivo', '=', 'activos.idActivo');

            // Add technician/admin-specific fields
            if (in_array($user->idRol, [1, 3])) {
                $detailsQuery->addSelect(
                    'incidentes.comentarios_tecnico',
                    DB::raw('CONCAT(datos.nombre, " ", datos.apellido) as reportado_por')
                )
                    ->join('usuarios', 'incidentes.idUsuario', '=', 'usuarios.idUsuario')
                    ->join('datos', 'usuarios.idDatos', '=', 'datos.idDatos');
            }

            // Apply role-based filter to details query
            if ($user->idRol == 2) {
                $detailsQuery->where('incidentes.idUsuario', $user->idUsuario);
            } elseif ($user->idRol == 3) {
                $detailsQuery->where('incidentes.idTecnico', $user->idUsuario);
            }
            // idRol = 1: No filter

            $incidentsDetails = $detailsQuery->orderBy('incidentes.fecha_reporte', 'desc')->get();

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

    public function getUsersByArea(Request $request): JsonResponse
    {
        try {
            // Get total number of active client users (unfiltered for charts)
            $totalUsers = User::where('estado', 1)
                ->where('idRol', 2)
                ->count();

            // Get client users grouped by area (unfiltered for charts)
            $usersByArea = Datos::select('areas.nombre as area', DB::raw('count(datos.idDatos) as count'))
                ->leftJoin('areas', 'datos.idArea', '=', 'areas.idArea')
                ->join('usuarios', 'datos.idDatos', '=', 'usuarios.idDatos')
                ->where('usuarios.estado', 1)
                ->where('usuarios.idRol', 2)
                ->groupBy('areas.idArea', 'areas.nombre')
                ->get();

            // Get detailed client user information by area with filters and pagination
            $query = Datos::select(
                'areas.nombre as area',
                'datos.nombre',
                'datos.apellido',
                'roles.nombre as rol'
            )
                ->leftJoin('areas', 'datos.idArea', '=', 'areas.idArea')
                ->join('usuarios', 'datos.idDatos', '=', 'usuarios.idDatos')
                ->join('roles', 'usuarios.idRol', '=', 'roles.idRol')
                ->where('usuarios.estado', 1)
                ->where('usuarios.idRol', 2);

            // Apply filters
            if ($request->has('area') && $request->area !== 'all') {
                if ($request->area === 'null') {
                    $query->whereNull('areas.nombre');
                } else {
                    $query->where('areas.nombre', $request->area);
                }
            }

            if ($request->has('name') && !empty($request->name)) {
                $query->where(function ($q) use ($request) {
                    $q->where('datos.nombre', 'LIKE', '%' . $request->name . '%')
                      ->orWhere('datos.apellido', 'LIKE', '%' . $request->name . '%');
                });
            }

            // Apply pagination
            $usersDetails = $this->applyPagination(
                $query,
                $request,
                [], // No additional search fields
                [], // No additional filter fields
                $request->input('per_page', 10) // Default items per page
            );

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

}