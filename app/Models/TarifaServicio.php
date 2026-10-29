<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaServicio extends Model
{
    protected $table = 'tarifa_servicios';

    protected $fillable = ['tarifa_id', 'servicio_id', 'precio_agencia', 'precio_central'];

    protected function casts(): array
    {
        return [
            'precio_agencia' => 'decimal:4',
            'precio_central' => 'decimal:4',
        ];
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(Tarifa::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }
}
