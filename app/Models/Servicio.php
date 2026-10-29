<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $attributes = ['activo' => true];

    protected $fillable = ['codigo', 'nombre', 'tipo_cobro_id', 'largo_pulg', 'ancho_pulg', 'alto_pulg', 'activo'];

    protected function casts(): array
    {
        return [
            'largo_pulg' => 'decimal:2',
            'ancho_pulg' => 'decimal:2',
            'alto_pulg' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function tipoCobro(): BelongsTo
    {
        return $this->belongsTo(TipoCobro::class);
    }

    public function tarifaServicios(): HasMany
    {
        return $this->hasMany(TarifaServicio::class);
    }
}
