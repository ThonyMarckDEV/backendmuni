<?php

   namespace App\Http\Controllers;

   use App\Models\Incidente;
   use App\Models\Activo;
   use App\Http\Requests\StoreIncidenteRequest;
   use App\Http\Requests\UpdateIncidenteRequest;
   use Illuminate\Http\JsonResponse;
   use Illuminate\Support\Facades\DB;
   use Illuminate\Support\Facades\Log;
   use Illuminate\Support\Facades\Auth;
   use Barryvdh\DomPDF\Facade\Pdf;

   class IncidenteController extends Controller
   {
       public function index(): JsonResponse
       {
           try {
               $incidentes = Incidente::with(['activo', 'area', 'tecnico.datos'])
                   ->paginate(15)
                   ->through(function ($incidente) {
                       return [
                           'idIncidente' => $incidente->idIncidente,
                           'activo' => $incidente->activo ? [
                               'idActivo' => $incidente->activo->idActivo,
                               'codigo_inventario' => $incidente->activo->codigo_inventario,
                               'tipo' => $incidente->activo->tipo,
                               'marca_modelo' => $incidente->activo->marca_modelo,
                               'ubicacion' => $incidente->activo->ubicacion,
                           ] : null,
                           'area' => $incidente->area ? [
                               'idArea' => $incidente->area->idArea,
                               'nombre' => $incidente->area->nombre,
                           ] : null,
                           'tecnico' => $incidente->tecnico && $incidente->tecnico->datos ? [
                               'idUsuario' => $incidente->tecnico->idUsuario,
                               'nombre' => $incidente->tecnico->datos->nombre,
                               'apellido' => $incidente->tecnico->datos->apellido,
                           ] : null,
                           'prioridad' => $incidente->prioridad,
                           'titulo' => $incidente->titulo,
                           'descripcion' => $incidente->descripcion,
                           'fecha_reporte' => $incidente->fecha_reporte,
                           'estado' => $incidente->estado,
                           'created_at' => $incidente->created_at,
                           'updated_at' => $incidente->updated_at,
                       ];
                   });

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
                   $user = Auth::user();
                   if (!$user || !$user->datos || !$user->datos->idArea) {
                       return response()->json([
                           'success' => false,
                           'message' => 'Usuario no autenticado o sin área asignada',
                       ], 403);
                   }

                   $validated = $request->validated();
                   $validated['estado'] = 0; // Pendiente
                   $validated['idUsuario'] = $user->idUsuario;
                   $validated['idArea'] = $user->datos->idArea;

                   if (!isset($validated['idActivo'])) {
                       throw new \Exception('El campo idActivo es requerido.');
                   }

                   $incidente = Incidente::create($validated);
                   $incidente->load(['activo', 'area', 'tecnico.datos']);

                   return response()->json([
                       'success' => true,
                       'data' => [
                           'idIncidente' => $incidente->idIncidente,
                           'activo' => $incidente->activo ? [
                               'idActivo' => $incidente->activo->idActivo,
                               'codigo_inventario' => $incidente->activo->codigo_inventario,
                               'tipo' => $incidente->activo->tipo,
                               'marca_modelo' => $incidente->activo->marca_modelo,
                               'ubicacion' => $incidente->activo->ubicacion,
                           ] : null,
                           'area' => $incidente->area ? [
                               'idArea' => $incidente->area->idArea,
                               'nombre' => $incidente->area->nombre,
                           ] : null,
                           'tecnico' => $incidente->tecnico && $incidente->tecnico->datos ? [
                               'idUsuario' => $incidente->tecnico->idUsuario,
                               'nombre' => $incidente->tecnico->datos->nombre,
                               'apellido' => $incidente->tecnico->datos->apellido,
                           ] : null,
                           'prioridad' => $incidente->prioridad,
                           'titulo' => $incidente->titulo,
                           'descripcion' => $incidente->descripcion,
                           'fecha_reporte' => $incidente->fecha_reporte,
                           'estado' => $incidente->estado,
                           'created_at' => $incidente->created_at,
                           'updated_at' => $incidente->updated_at,
                       ],
                       'message' => 'Incidente registrado exitosamente',
                   ], 201);
               } catch (\Exception $e) {
                   Log::error('Error al registrar incidente: ' . $e->getMessage());
                   return response()->json([
                       'success' => false,
                       'message' => 'Error al registrar el incidente: ' . $e->getMessage(),
                   ], 500);
               }
           });
       }

       public function update(UpdateIncidenteRequest $request, $id): JsonResponse
       {
           return DB::transaction(function () use ($request, $id) {
               try {
                   $incidente = Incidente::findOrFail($id);
                   $validated = $request->validated();

                   if (!isset($validated['idActivo'])) {
                       throw new \Exception('El campo idActivo es requerido.');
                   }

                   $incidente->update($validated);
                   $incidente->load(['activo', 'area', 'tecnico.datos']);

                   return response()->json([
                       'success' => true,
                       'data' => [
                           'idIncidente' => $incidente->idIncidente,
                           'activo' => $incidente->activo ? [
                               'idActivo' => $incidente->activo->idActivo,
                               'codigo_inventario' => $incidente->activo->codigo_inventario,
                               'tipo' => $incidente->activo->tipo,
                               'marca_modelo' => $incidente->activo->marca_modelo,
                               'ubicacion' => $incidente->activo->ubicacion,
                           ] : null,
                           'area' => $incidente->area ? [
                               'idArea' => $incidente->area->idArea,
                               'nombre' => $incidente->area->nombre,
                           ] : null,
                           'tecnico' => $incidente->tecnico && $incidente->tecnico->datos ? [
                               'idUsuario' => $incidente->tecnico->idUsuario,
                               'nombre' => $incidente->tecnico->datos->nombre,
                               'apellido' => $incidente->tecnico->datos->apellido,
                           ] : null,
                           'prioridad' => $incidente->prioridad,
                           'titulo' => $incidente->titulo,
                           'descripcion' => $incidente->descripcion,
                           'fecha_reporte' => $incidente->fecha_reporte,
                           'estado' => $incidente->estado,
                           'created_at' => $incidente->created_at,
                           'updated_at' => $incidente->updated_at,
                       ],
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
                       'message' => 'Error al actualizar el incidente: ' . $e->getMessage(),
                   ], 500);
               }
           });
       }

       public function show($id): JsonResponse
       {
           try {
               $incidente = Incidente::with(['activo', 'area', 'tecnico.datos'])->findOrFail($id);

               return response()->json([
                   'success' => true,
                   'data' => [
                       'idIncidente' => $incidente->idIncidente,
                       'activo' => $incidente->activo ? [
                           'idActivo' => $incidente->activo->idActivo,
                           'codigo_inventario' => $incidente->activo->codigo_inventario,
                           'tipo' => $incidente->activo->tipo,
                           'marca_modelo' => $incidente->activo->marca_modelo,
                           'ubicacion' => $incidente->activo->ubicacion,
                       ] : null,
                       'area' => $incidente->area ? [
                           'idArea' => $incidente->area->idArea,
                           'nombre' => $incidente->area->nombre,
                       ] : null,
                       'tecnico' => $incidente->tecnico && $incidente->tecnico->datos ? [
                           'idUsuario' => $incidente->tecnico->idUsuario,
                           'nombre' => $incidente->tecnico->datos->nombre,
                           'apellido' => $incidente->tecnico->datos->apellido,
                       ] : null,
                       'prioridad' => $incidente->prioridad,
                       'titulo' => $incidente->titulo,
                       'descripcion' => $incidente->descripcion,
                       'fecha_reporte' => $incidente->fecha_reporte,
                       'estado' => $incidente->estado,
                       'created_at' => $incidente->created_at,
                       'updated_at' => $incidente->updated_at,
                   ],
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
               $user = Auth::user();
               if (!$user || !$user->datos || !$user->datos->idArea) {
                   return response()->json([
                       'success' => false,
                       'message' => 'Usuario no autenticado o sin área asignada',
                   ], 403);
               }

               $activos = Activo::where('estado', true)
                   ->whereHas('areas', function ($query) use ($user) {
                       $query->where('activos_areas.idArea', $user->datos->idArea);
                   })
                   ->get(['idActivo', 'codigo_inventario', 'tipo', 'marca_modelo', 'ubicacion']);

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

       public function generatePdf($id)
       {
           try {
               $incidente = Incidente::with([
                   'activo' => function ($query) {
                       $query->select('idActivo', 'codigo_inventario', 'tipo', 'marca_modelo', 'ubicacion');
                   },
                   'area' => function ($query) {
                       $query->select('idArea', 'nombre');
                   },
                   'tecnico.datos' => function ($query) {
                       $query->select('idDatos', 'nombre', 'apellido');
                   }
               ])->findOrFail($id);

               $pdf = Pdf::loadView('incidentes.pdf', ['incidente' => $incidente]);

               return $pdf->download('incidente_' . $id . '.pdf');
           } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
               Log::warning('Incidente no encontrado: idIncidente ' . $id);
               return response()->json([
                   'success' => false,
                   'message' => 'Incidente no encontrado',
               ], 404);
           } catch (\Exception $e) {
               Log::error('Error al generar PDF del incidente: ' . $e->getMessage(), [
                   'idIncidente' => $id,
                   'stack' => $e->getTraceAsString(),
               ]);
               return response()->json([
                   'success' => false,
                   'message' => 'Error al generar el PDF: ' . $e->getMessage(),
               ], 500);
           }
       }

       public function getUserData(): JsonResponse
       {
           try {
               $user = Auth::user()->load('datos.area');
               if (!$user) {
                   return response()->json([
                       'success' => false,
                       'message' => 'Usuario no autenticado',
                   ], 401);
               }

               return response()->json([
                   'success' => true,
                   'data' => [
                       'idUsuario' => $user->idUsuario,
                       'datos' => $user->datos ? [
                           'idDatos' => $user->datos->idDatos,
                           'nombre' => $user->datos->nombre,
                           'apellido' => $user->datos->apellido,
                           'email' => $user->datos->email,
                           'dni' => $user->datos->dni,
                           'telefono' => $user->datos->telefono,
                           'especializacion' => $user->datos->especializacion,
                           'area' => $user->datos->area ? [
                               'idArea' => $user->datos->area->idArea,
                               'nombre' => $user->datos->area->nombre,
                           ] : null,
                       ] : null,
                   ],
                   'message' => 'Datos del usuario obtenidos exitosamente',
               ]);
           } catch (\Exception $e) {
               Log::error('Error al obtener datos del usuario: ' . $e->getMessage());
               return response()->json([
                   'success' => false,
                   'message' => 'Error al obtener los datos del usuario',
               ], 500);
           }
       }
   }
