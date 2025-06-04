<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'sometimes|required|string|min:6',
            'idRol' => 'sometimes|required|exists:roles,idRol',
            'estado' => 'sometimes|required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'idRol.required' => 'Debe seleccionar un rol.',
            'idRol.exists' => 'El rol seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }
}