<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidenteRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'activo_id' => 'required|exists:activos,id',
            'descripcion' => 'required|string|max:1000',
            'fecha_reporte' => 'required|date',
            'estado' => 'required|integer|in:0,1,2',
        ];
    }

    public function messages()
    {
        return [
            'activo_id.required' => 'El activo es requerido.',
            'activo_id.exists' => 'El activo seleccionado no existe.',
            'descripcion.required' => 'La descripción es requerida.',
            'fecha_reporte.required' => 'La fecha de reporte es requerida.',
            'fecha_reporte.date' => 'La fecha de reporte debe ser una fecha válida.',
            'estado.required' => 'El estado es requerido.',
            'estado.integer' => 'El estado debe ser un número entero.',
            'estado.in' => 'El estado debe ser 0 (Pendiente), 1 (En progreso) o 2 (Resuelto).',
        ];
    }
}