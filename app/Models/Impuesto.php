<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Impuesto extends Model
{
    protected $table = 'impuestos';

    protected $attributes = ['activo' => true];

    protected $fillable = ['nombre', 'formula', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function tarifas(): HasMany
    {
        return $this->hasMany(Tarifa::class);
    }
}
