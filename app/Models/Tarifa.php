<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarifa extends Model
{
    protected $table = 'tarifas';

    protected $attributes = [
        'version' => 1,
        'moneda' => 'USD',
        'activa' => true,
    ];

    protected $fillable = [
        'agencia_id', 'clonada_de_id', 'codigo', 'nombre', 'version', 'moneda',
        'divisor_volumetrico', 'libras_minimas', 'impuesto_id', 'vigente_desde', 'vigente_hasta', 'activa',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'divisor_volumetrico' => 'decimal:4',
            'libras_minimas' => 'decimal:3',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
            'activa' => 'boolean',
        ];
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function tarifaClonadaDe(): BelongsTo
    {
        return $this->belongsTo(self::class, 'clonada_de_id');
    }

    public function clones(): HasMany
    {
        return $this->hasMany(self::class, 'clonada_de_id');
    }

    public function reglas(): HasMany
    {
        return $this->hasMany(TarifaRegla::class);
    }

    public function impuesto(): BelongsTo
    {
        return $this->belongsTo(Impuesto::class);
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(TarifaServicio::class);
    }

    public function guias(): HasMany
    {
        return $this->hasMany(Guia::class);
    }
}
