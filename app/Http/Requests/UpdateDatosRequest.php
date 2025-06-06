<?php

namespace App\Http\Requests;

use App\Models\Datos;
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
        // Get the idDatos of the datos record being updated
        $idDatos = $this->route('user') ? Datos::where('idDatos', $this->user()->idDatos)->first()->idDatos : null;

        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('datos', 'email')->ignore($idDatos, 'idDatos'),
            ],
            'dni' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('datos', 'dni')->ignore($idDatos, 'idDatos'),
            ],
            'telefono' => 'nullable|string|max:20',
            'especializacion' => 'nullable|string|max:255',
            'idArea' => 'nullable|integer|exists:areas,idArea',
        ];

        if ($this->input('idRol') == 2) { // Usuario
            $rules['idArea'] = 'required|integer|exists:areas,idArea';
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
            'email.unique' => 'El correo electrónico ya está en uso.',
            'dni.unique' => 'El DNI ya está registrado.',
            'especializacion.required' => 'La especialización es obligatoria para técnicos.',
            'idArea.required' => 'El área es obligatoria para usuarios.',
            'idArea.integer' => 'El área debe ser un valor válido.',
            'idArea.exists' => 'El área seleccionada no existe.',
        ];
    }
}
