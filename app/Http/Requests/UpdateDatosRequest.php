<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nombre' => 'sometimes|required|string|max:255',
            'apellido' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('datos', 'email')->ignore($this->route('id'), 'idDatos'),
                'max:255',
            ],
            'dni' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('datos', 'dni')->ignore($this->route('id'), 'idDatos'),
            ],
            'telefono' => 'nullable|string|max:20',
        ];

        if ($this->input('idRol') == 2) { // Usuario
            $rules['area'] = 'sometimes|required|string|max:255';
        } elseif ($this->input('idRol') == 3) { // Técnico
            $rules['especializacion'] = 'sometimes|required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'dni.unique' => 'El DNI ya está registrado.',
            'especializacion.required' => 'La especialización es obligatoria para técnicos.',
            'area.required' => 'El área es obligatoria para usuarios.',
        ];
    }
}