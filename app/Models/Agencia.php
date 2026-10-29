<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agencia extends Model
{
    protected $table = 'agencias';

    protected $attributes = [
        'activa' => true,
        'siguiente_consecutivo_guia' => 1,
    ];

    protected $fillable = [
        'central_id', 'tarifa_id', 'alias', 'nombre', 'direccion', 'telefono', 'email', 'codigo',
        'codigo_iata', 'siguiente_consecutivo_guia', 'ciudad', 'estado', 'pais',
        'codigo_postal', 'observaciones', 'codigo_pais', 'activa', 'es_central',
    ];

    protected function casts(): array
    {
        return ['activa' => 'boolean', 'es_central' => 'boolean', 'siguiente_consecutivo_guia' => 'integer'];
    }

    public function central(): BelongsTo
    {
        return $this->belongsTo(Central::class);
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(Tarifa::class);
    }

    public function tarifaAplicable(): ?Tarifa
    {
        if ($this->tarifa_id !== null) {
            $asignada = Tarifa::query()->whereKey($this->tarifa_id)->where('activa', true)->first();
            if ($asignada !== null) {
                return $asignada;
            }
        }

        return Tarifa::query()
            ->where('activa', true)
            ->where('agencia_id', $this->id)
            ->latest('id')
            ->first();
    }

    public function guias(): HasMany
    {
        return $this->hasMany(Guia::class);
    }

    public function tarifas(): HasMany
    {
        return $this->hasMany(Tarifa::class);
    }

    public function consolidados(): HasMany
    {
        return $this->hasMany(Consolidado::class);
    }
}
