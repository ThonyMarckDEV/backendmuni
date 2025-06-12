<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateIncidenteRequest extends FormRequest
{
    public function rules(): array
    {
        $user = Auth::user();
        $isTechnician = $user && $user->idRol === 3;
        $isAdmin = $user && $user->idRol === 1;

        if ($isTechnician) {
            return [
                'estado' => 'required|integer|in:2',
                'comentarios_tecnico' => 'nullable|string|max:1000',
            ];
        } elseif ($isAdmin) {
            return [
                'idActivo' => 'required|exists:activos,idActivo',
                'idTecnico' => 'nullable|exists:usuarios,idUsuario,estado,1,idRol,3',
                'estado' => 'nullable|integer|in:0,1,2',
            ];
        } else {
            return [
                'idActivo' => 'required|exists:activos,idActivo',
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string|max:1000',
                'fecha_reporte' => 'required|date',
                'prioridad' => 'required|integer|in:0,1,2',
            ];
        }
    }

    public function messages(): array
    {
        return [
            'idActivo.required' => 'El activo es requerido.',
            'idActivo.exists' => 'El activo seleccionado no existe.',
            'idTecnico.exists' => 'El técnico seleccionado no existe o no es válido.',
            'estado.required' => 'El estado es requerido.',
            'estado.integer' => 'El estado debe ser un número entero.',
            'estado.in' => 'El estado debe ser Resuelto (2) para técnicos o 0, 1, 2 para administradores.',
            'comentarios_tecnico.string' => 'Los comentarios del técnico deben ser una cadena de texto.',
            'comentarios_tecnico.max' => 'Los comentarios del técnico no deben exceder los 1000 caracteres.',
            'titulo.required' => 'El título es requerido.',
            'titulo.string' => 'El título debe ser una cadena de texto.',
            'titulo.max' => 'El título no puede exceder los 255 caracteres.',
            'descripcion.required' => 'La descripción es requerida.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción no debe exceder los 1000 caracteres.',
            'fecha_reporte.required' => 'La fecha de reporte es requerida.',
            'fecha_reporte.date' => 'La fecha de reporte debe ser una fecha válida.',
            'prioridad.required' => 'La prioridad es requerida.',
            'prioridad.integer' => 'La prioridad debe ser un número entero.',
            'prioridad.in' => 'La prioridad debe ser 0 (Baja), 1 (Media) o 2 (Alta).',
        ];
    }
}
