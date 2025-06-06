<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivoArea extends Model
{
    protected $table = 'activos_areas';
    protected $fillable = ['idActivo', 'idArea'];
    public $timestamps = true;

    /**
     * Relación con Activo
     */
    public function activo()
    {
        return $this->belongsTo(Activo::class, 'idActivo', 'idActivo');
    }

    /**
     * Relación con Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'idArea', 'idArea');
    }
}
