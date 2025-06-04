<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:datos,email|max:255',
            'dni' => 'nullable|string|max:20|unique:datos,dni',
            'telefono' => 'nullable|string|max:20',
        ];

        if ($this->input('idRol') == 2) { // Usuario
            $rules['area'] = 'required|string|max:255';
        } elseif ($this->input('idRol') == 3) { // Técnico
            $rules['especializacion'] = 'required|string|max:255';
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