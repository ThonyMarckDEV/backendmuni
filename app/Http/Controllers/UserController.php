<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatosRequest;
use App\Http\Requests\UpdateDatosRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Datos;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios con sus relaciones de rol y datos personales.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with(['rol', 'datos']);

            // Filtros opcionales
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('rol')) {
                $query->where('idRol', $request->rol);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('datos', function ($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('apellido', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $usuarios = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $usuarios,
                'message' => 'Usuarios obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo usuario junto con sus datos personales en la base de datos.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            try {
                // Validar datos personales
                $datosValidator = Validator::make(
                    $request->all(),
                    (new StoreDatosRequest())->rules()
                );

                if ($datosValidator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación en datos personales',
                        'errors' => $datosValidator->errors()
                    ], 422);
                }

                // Crear datos personales
                $datos = Datos::create($request->only([
                    'nombre', 'apellido', 'email', 'direccion', 'dni', 'ruc', 'telefono',
                    'telefonoTecnico', 'especializacion', 'area'
                ]));

                // Preparar datos del usuario
                $dataUsuario = $request->only([
                    'username', 'password', 'idRol', 'estado'
                ]);
                $dataUsuario['idDatos'] = $datos->idDatos;
                $dataUsuario['password'] = bcrypt($dataUsuario['password']);

                // Crear usuario
                $usuario = User::create($dataUsuario);

                // Cargar relaciones para la respuesta
                $usuario->load(['rol', 'datos']);

                return response()->json([
                    'success' => true,
                    'data' => $usuario,
                    'message' => 'Usuario creado exitosamente',
                ], 201);

            } catch (\Exception $e) {
                Log::error('Error al crear usuario: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el usuario',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    }

    /**
     * Muestra un usuario específico junto con sus relaciones de rol y datos personales.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $usuario = User::with(['rol', 'datos'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $usuario,
                'message' => 'Usuario obtenido exitosamente'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener usuario: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza la información de un usuario específico.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                $usuario = User::with(['datos'])->findOrFail($id);

                // Validar datos personales si están presentes
                if ($request->hasAny(['nombre', 'apellido', 'email', 'direccion', 'dni', 'ruc', 'telefono', 'telefonoTecnico', 'especializacion', 'area'])) {
                    $datosValidator = Validator::make(
                        $request->only(['nombre', 'apellido', 'email', 'direccion', 'dni', 'ruc', 'telefono', 'telefonoTecnico', 'especializacion', 'area']),
                        (new UpdateDatosRequest())->rules()
                    );

                    if ($datosValidator->fails()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Error de validación en datos personales',
                            'errors' => $datosValidator->errors()
                        ], 422);
                    }

                    // Actualizar datos personales
                    $datosPersonales = $request->only([
                        'nombre', 'apellido', 'email', 'direccion', 'dni', 'ruc', 'telefono',
                        'telefonoTecnico', 'especializacion', 'area'
                    ]);
                    
                    // Filtrar solo los campos que no están vacíos
                    $datosPersonales = array_filter($datosPersonales, function($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($datosPersonales)) {
                        $usuario->datos->update($datosPersonales);
                    }
                }

                // Actualizar datos del usuario
                $dataUsuario = $request->only(['username', 'password', 'idRol', 'estado']);
                
                // Encriptar contraseña si está presente
                if (!empty($dataUsuario['password'])) {
                    $dataUsuario['password'] = bcrypt($dataUsuario['password']);
                } else {
                    unset($dataUsuario['password']);
                }

                // Filtrar campos vacíos
                $dataUsuario = array_filter($dataUsuario, function($value) {
                    return $value !== null && $value !== '';
                });

                if (!empty($dataUsuario)) {
                    $usuario->update($dataUsuario);
                }

                // Recargar relaciones
                $usuario->refresh();
                $usuario->load(['rol', 'datos']);

                return response()->json([
                    'success' => true,
                    'data' => $usuario,
                    'message' => 'Usuario actualizado exitosamente'
                ]);

            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            } catch (\Exception $e) {
                Log::error('Error al actualizar usuario: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el usuario',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            try {
                $usuario = User::findOrFail($id);
                
                // Eliminar los datos personales
                if ($usuario->datos) {
                    $usuario->datos->delete();
                }
                
                $usuario->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Usuario eliminado exitosamente'
                ]);

            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            } catch (\Exception $e) {
                Log::error('Error al eliminar usuario: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el usuario',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    }

    /**
     * Cambia el estado de un usuario específico.
     *
     * @param int $id
     * @param UserService $userService
     * @return JsonResponse
     */
    public function cambiarEstado(int $id, UserService $userService): JsonResponse
    {
        try {
            $usuario = User::findOrFail($id);
            $usuarioActualizado = $userService->cambiarEstado($usuario);

            return response()->json([
                'success' => true,
                'data' => $usuarioActualizado,
                'message' => 'Estado actualizado exitosamente',
                'estado' => $usuarioActualizado->estado
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}