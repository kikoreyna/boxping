<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Central extends Model
{
    protected $table = 'centrales';

    protected $fillable = ['codigo', 'nombre', 'ciudad', 'codigo_pais', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function agencias(): HasMany
    {
        return $this->hasMany(Agencia::class);
    }

    public function guias(): HasMany
    {
        return $this->hasMany(Guia::class);
    }

    public function consolidados(): HasMany
    {
        return $this->hasMany(Consolidado::class);
    }
}
