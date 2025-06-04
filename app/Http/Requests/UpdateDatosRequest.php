<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDatosRequest extends FormRequest
{
    public function rules()
    {
        // Get the idDatos of the user being updated
        $idDatos = $this->user()->datos->idDatos ?? null;

        return [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('datos', 'email')->ignore($idDatos, 'idDatos'),
            ],
            'dni' => [
                'required',
                'string',
                Rule::unique('datos', 'dni')->ignore($idDatos, 'idDatos'),
            ],
            'telefono' => 'nullable|string|max:20',
            'especializacion' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'El correo electrónico ya está en uso.',
            'dni.unique' => 'El DNI ya está registrado.',
        ];
    }
}