<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidenteRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'activo_id' => 'required|exists:activos,id',
            'titulo' => 'nullable|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'fecha_reporte' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'activo_id.required' => 'El activo es requerido.',
            'activo_id.exists' => 'El activo seleccionado no existe.',
            'titulo.max' => 'El título no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La descripción es requerida.',
            'fecha_reporte.required' => 'La fecha de reporte es requerida.',
            'fecha_reporte.date' => 'La fecha de reporte debe ser una fecha válida.',
        ];
    }
}