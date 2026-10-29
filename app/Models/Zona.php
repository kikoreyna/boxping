<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    protected $table = 'zonas';

    protected $fillable = [
        'codigo', 'nombre', 'codigo_pais', 'estado_region',
        'codigo_postal_inicio', 'codigo_postal_fin', 'activa',
    ];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function reglasTarifa(): HasMany
    {
        return $this->hasMany(TarifaRegla::class);
    }

    public function guias(): HasMany
    {
        return $this->hasMany(Guia::class);
    }
}
