<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateIncidenteRequest extends FormRequest
{

    public function rules(): array
    {
        $user = Auth::user();
        $isAdmin = $user && $user->idRol === 1;

        // Base rules for all roles
        $rules = [
            'idActivo' => 'required|exists:activos,idActivo',
        ];

        if ($isAdmin) {
            // Admins only need idTecnico and estado (optional)
            $rules = array_merge($rules, [
                'idTecnico' => 'nullable|exists:usuarios,idUsuario,estado,1,idRol,3', // Active technicians only
                'estado' => 'nullable|integer|in:0,1,2',
            ]);
        } else {
            // Non-admins (e.g., idRol = 2) require full fields
            $rules = array_merge($rules, [
                'descripcion' => 'required|string|max:1000',
                'fecha_reporte' => 'required|date',
                'prioridad' => 'required|integer|in:0,1,2',
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'idActivo.required' => 'El activo es requerido.',
            'idActivo.exists' => 'El activo seleccionado no existe.',
            'idTecnico.exists' => 'El técnico seleccionado no existe o no es válido.',
            'estado.integer' => 'El estado debe ser un número entero.',
            'estado.in' => 'El estado debe ser 0 (Pendiente), 1 (En progreso) o 2 (Resuelto).',
            'descripcion.required' => 'La descripción es requerida.',
            'descripcion.max' => 'La descripción no debe exceder los 1000 caracteres.',
            'fecha_reporte.required' => 'La fecha de reporte es requerida.',
            'fecha_reporte.date' => 'La fecha de reporte debe ser una fecha válida.',
            'prioridad.required' => 'La prioridad es requerida.',
            'prioridad.integer' => 'La prioridad debe ser un número entero.',
            'prioridad.in' => 'La prioridad debe ser 0 (Baja), 1 (Media) o 2 (Alta).',
        ];
    }
}