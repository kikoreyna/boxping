<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuiaHija extends Model
{
    protected $table = 'guias_hijas';

    protected $fillable = [
        'guia_madre_id', 'folio', 'rastreo_destino', 'descripcion', 'estatus',
        'peso_libras', 'largo_pulg', 'ancho_pulg', 'alto_pulg',
    ];

    protected function casts(): array
    {
        return [
            'peso_libras' => 'decimal:3',
            'largo_pulg' => 'decimal:2',
            'ancho_pulg' => 'decimal:2',
            'alto_pulg' => 'decimal:2',
        ];
    }

    public function guiaMadre(): BelongsTo
    {
        return $this->belongsTo(Guia::class, 'guia_madre_id');
    }

    public function historialEstatus(): HasMany
    {
        return $this->hasMany(HistorialEstatus::class);
    }

    public function pesajes(): HasMany
    {
        return $this->hasMany(Pesaje::class);
    }
}
