<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguroPais extends Model
{
    protected $table = 'seguros_pais';

    protected $attributes = ['opcional' => true, 'activo' => true];

    protected $fillable = [
        'pais_id', 'opcional', 'porcentaje_agencia', 'porcentaje_central',
        'minimo_agencia', 'minimo_central', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'opcional' => 'boolean',
            'activo' => 'boolean',
            'porcentaje_agencia' => 'decimal:4',
            'porcentaje_central' => 'decimal:4',
            'minimo_agencia' => 'decimal:2',
            'minimo_central' => 'decimal:2',
        ];
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }
}
