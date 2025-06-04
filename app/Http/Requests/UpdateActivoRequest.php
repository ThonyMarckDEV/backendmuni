<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivoRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        $activoId = $this->route('id');

        return [
            'codigo_inventario' => [
                'required',
                'string',
                'max:255',
                Rule::unique('activos', 'codigo_inventario')->ignore($activoId),
            ],
            'ubicacion' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'marca_modelo' => 'required|string|max:255',
            'estado' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'codigo_inventario.unique' => 'El código de inventario ya está registrado.',
            'codigo_inventario.required' => 'El código de inventario es requerido.',
            'ubicacion.required' => 'La ubicación es requerida.',
            'tipo.required' => 'El tipo es requerido.',
            'marca_modelo.required' => 'La marca/modelo es requerida.',
            'estado.required' => 'El estado es requerido.',
        ];
    }
}