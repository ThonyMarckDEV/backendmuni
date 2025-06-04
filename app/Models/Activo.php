<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_inventario',
        'ubicacion',
        'tipo',
        'marca_modelo',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function incidentes()
    {
        return $this->hasMany(Incidente::class);
    }
}