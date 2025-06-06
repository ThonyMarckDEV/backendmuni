<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'idArea';
    public $incrementing = true;
    protected $fillable = ['nombre'];
    public $timestamps = true;

    /**
     * Relación muchos a muchos con Activo a través de la tabla pivot activos_areas
     */
    public function activos()
    {
        return $this->belongsToMany(Activo::class, 'activos_areas', 'idArea', 'idActivo');
    }

    public function datos()
    {
        return $this->hasMany(Datos::class, 'idArea', 'idArea');
    }
}