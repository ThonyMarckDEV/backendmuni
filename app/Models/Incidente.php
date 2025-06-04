<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidente extends Model
{
    use HasFactory;

    protected $fillable = [
        'activo_id',
        'prioridad',
        'titulo',
        'descripcion',
        'fecha_reporte',
        'estado',
    ];

    protected $casts = [
        'prioridad' => 'integer',
        'estado' => 'integer',
        'fecha_reporte' => 'date',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class);
    }
}