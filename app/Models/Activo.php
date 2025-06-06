<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    use HasFactory;
    protected $table = 'activos';

    protected $primaryKey = 'idActivo';

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

      /**
     * Relación muchos a muchos con Area a través de la tabla pivot activos_areas
     */
    public function areas()
    {
        return $this->belongsToMany(Area::class, 'activos_areas', 'idActivo', 'idArea');
    }
}