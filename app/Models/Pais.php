<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pais extends Model
{
    protected $table = 'paises';

    protected $attributes = ['activo' => true];

    protected $fillable = ['codigo_iso2', 'nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function estados(): HasMany
    {
        return $this->hasMany(Estado::class);
    }

    public function ciudades(): HasMany
    {
        return $this->hasMany(Ciudad::class);
    }
}
