<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDatosRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Datos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Utilities\PaginationTrait;


class UserController extends Controller
{
    use PaginationTrait;
       public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with(['rol', 'datos']);

            // Definir campos de búsqueda
            $searchFields = [
                'datos.nombre',
                'datos.apellido', 
                'datos.email',
                'datos.dni'
            ];

            // Definir campos de filtro
            $filterFields = [
                'estado' => 'estado',
                'rol' => 'idRol',
                'area' => 'datos.idArea'
            ];

            // Aplicar paginación con filtros y búsqueda
            $usuarios = $this->applyPagination(
                $query,
                $request,
                $searchFields,
                $filterFields,
                8
            );

            return $this->paginatedResponse($usuarios, 'Usuarios obtenidos exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al obtener usuarios: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener los usuarios', $e);
        }
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            try {
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

                $datos = Datos::create($request->only([
                    'nombre', 'apellido', 'email', 'dni', 'telefono', 'especializacion', 'idArea'
                ]));

                $dataUsuario = $request->only([
                    'password', 'idRol'
                ]);
                $dataUsuario['idDatos'] = $datos->idDatos;
                $dataUsuario['password'] = bcrypt($dataUsuario['password']);

                $usuario = User::create($dataUsuario);

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

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        return DB::transaction(function () use ($request, $id) {
            try {
                $usuario = User::with(['datos'])->findOrFail($id);

                if ($request->hasAny(['nombre', 'apellido', 'email', 'dni', 'telefono', 'especializacion', 'idArea'])) {
                    $datosValidator = Validator::make(
                        $request->only(['nombre', 'apellido', 'email', 'dni', 'telefono', 'especializacion', 'idArea']),
                        [
                            'nombre' => 'required|string|max:255',
                            'apellido' => 'required|string|max:255',
                            'email' => [
                                'required',
                                'email',
                                Rule::unique('datos', 'email')->ignore($usuario->idDatos, 'idDatos'),
                            ],
                            'dni' => [
                                'nullable',
                                'string',
                                Rule::unique('datos', 'dni')->ignore($usuario->idDatos, 'idDatos'),
                            ],
                            'telefono' => 'nullable|string|max:20',
                            'especializacion' => 'nullable|string|max:255',
                            'idArea' => ['nullable', 'exists:areas,idArea'],
                        ]
                    );

                    if ($datosValidator->fails()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Error de validación en datos personales',
                            'errors' => $datosValidator->errors()
                        ], 422);
                    }

                    $datosPersonales = $request->only([
                        'nombre', 'apellido', 'email', 'dni', 'telefono', 'especializacion', 'idArea'
                    ]);

                    $datosPersonales = array_filter($datosPersonales, function($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($datosPersonales)) {
                        $usuario->datos->update($datosPersonales);
                    }
                }

                $dataUsuario = $request->only(['password', 'idRol', 'estado']);
                
                if (!empty($dataUsuario['password'])) {
                    $dataUsuario['password'] = bcrypt($dataUsuario['password']);
                } else {
                    unset($dataUsuario['password']);
                }

                $dataUsuario = array_filter($dataUsuario, function($value) {
                    return $value !== null && $value !== '';
                });

                if (!empty($dataUsuario)) {
                    $usuario->update($dataUsuario);
                }

                $usuario->refresh();
                $usuario->load(['rol', 'datos']);

                return response()->json([
                    'success' => true,
                    'data' => $usuario,
                    'message' => 'Usuario actualizado exitosamente'
                ]);
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

    public function destroy(int $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            try {
                $usuario = User::findOrFail($id);
                
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
