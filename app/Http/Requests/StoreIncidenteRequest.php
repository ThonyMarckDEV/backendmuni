<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        return [
            'idActivo' => 'required|integer|exists:activos,idActivo',
            'titulo' => 'nullable|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'fecha_reporte' => 'required|date',
            'prioridad' => 'required|integer|in:0,1,2',
        ];
    }

    public function messages(): array
    {
        return [
            'idActivo.required' => 'El activo es requerido.',
            'idActivo.integer' => 'El ID del activo debe ser un número entero.',
            'idActivo.exists' => 'El activo seleccionado no existe.',
            'titulo.max' => 'El título no debe exceder los 255 caracteres.',
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