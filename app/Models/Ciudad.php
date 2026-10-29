<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ciudad extends Model
{
    protected $table = 'ciudades';

    protected $fillable = ['pais_id', 'estado_id', 'zona_id', 'nombre', 'codigo_externo'];

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }
}
