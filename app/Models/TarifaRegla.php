<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaRegla extends Model
{
    protected $table = 'tarifa_reglas';

    protected $fillable = [
        'tarifa_id', 'zona_id', 'servicio', 'componente', 'base_calculo',
        'es_porcentaje', 'tarifa_unitaria', 'cargo_minimo',
    ];

    protected function casts(): array
    {
        return [
            'es_porcentaje' => 'boolean',
            'tarifa_unitaria' => 'decimal:4',
            'cargo_minimo' => 'decimal:2',
        ];
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(Tarifa::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }
}
