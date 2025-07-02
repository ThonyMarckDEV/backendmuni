<?php

namespace App\Utilities;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

trait PaginationTrait
{
    /**
     * Aplica paginación a una consulta de Eloquent
     *
     * @param Builder $query
     * @param Request $request
     * @param array $searchFields
     * @param array $filterFields
     * @param int $defaultPerPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function applyPagination(
        Builder $query,
        Request $request,
        array $searchFields = [],
        array $filterFields = [],
        int $defaultPerPage = 8
    ) {
        // Aplicar filtros
        foreach ($filterFields as $field => $column) {
            if ($request->has($field)) {
                $value = $request->get($field);
                if ($value !== null && $value !== '') {
                    $query->where($column, $value);
                }
            }
        }

        // Aplicar búsqueda
        if ($request->has('search') && !empty($searchFields)) {
            $search = $request->get('search');
            if ($search) {
                $query->where(function ($q) use ($searchFields, $search) {
                    foreach ($searchFields as $index => $field) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        
                        if (str_contains($field, '.')) {
                            // Campo de relación
                            [$relation, $column] = explode('.', $field, 2);
                            $q->orWhereHas($relation, function ($relationQuery) use ($column, $search) {
                                $relationQuery->where($column, 'LIKE', "%{$search}%");
                            });
                        } else {
                            // Campo directo
                            $q->{$method}($field, 'LIKE', "%{$search}%");
                        }
                    }
                });
            }
        }

        // Aplicar ordenamiento
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if (str_contains($sortBy, '.')) {
                // Ordenamiento por campo de relación
                [$relation, $column] = explode('.', $sortBy, 2);
                $query->join(
                    app("App\\Models\\" . ucfirst($relation))->getTable(),
                    function ($join) use ($relation) {
                        $join->on($this->getTable() . '.id' . ucfirst($relation), '=', app("App\\Models\\" . ucfirst($relation))->getTable() . '.id');
                    }
                )->orderBy(app("App\\Models\\" . ucfirst($relation))->getTable() . '.' . $column, $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        // Aplicar paginación
        $perPage = $request->get('per_page', $defaultPerPage);
        $perPage = min($perPage, 100); // Límite máximo de 100 por página
        
        return $query->paginate($perPage);
    }

    /**
     * Respuesta JSON estándar para paginación
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function paginatedResponse($data, string $message = 'Datos obtenidos exitosamente'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more_pages' => $data->hasMorePages(),
                'prev_page_url' => $data->previousPageUrl(),
                'next_page_url' => $data->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Respuesta de error estándar
     *
     * @param string $message
     * @param \Exception|null $exception
     * @param int $status
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message,
        \Exception $exception = null,
        int $status = 500
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($exception && config('app.debug')) {
            $response['error'] = $exception->getMessage();
            $response['trace'] = $exception->getTraceAsString();
        }

        return response()->json($response, $status);
    }
}