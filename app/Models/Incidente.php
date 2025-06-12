<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidente extends Model
{
    use HasFactory;

     protected $primaryKey = 'idIncidente';

    protected $fillable = [
        'idActivo',
        'idUsuario',
        'idTecnico',
        'prioridad',
        'titulo',
        'descripcion',
        'fecha_reporte',
        'idArea',
        'estado',
        'comentarios_tecnico'
    ];

    protected $casts = [
        'prioridad' => 'integer',
        'estado' => 'integer',
        'fecha_reporte' => 'date',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class,'idActivo', 'idActivo');
    }

     public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'idTecnico', 'idUsuario');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'idArea', 'idArea');
    }
}