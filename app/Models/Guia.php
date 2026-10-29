<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guia extends Model
{
    protected $table = 'guias';

    protected $fillable = [
        'folio', 'agencia_id', 'central_id', 'remitente_id', 'destinatario_id', 'ciudad_destino_id',
        'tarifa_id', 'zona_id', 'servicio', 'servicio_id', 'asegurada', 'estatus', 'codigo_pais_destino',
        'rastreo_destino', 'peso_libras', 'largo_pulg', 'ancho_pulg', 'alto_pulg',
        'valor_declarado', 'moneda', 'subtotal', 'cargo_zona', 'seguro', 'impuestos', 'total',
        'snapshot_tarifa', 'recibida_en',
    ];

    protected function casts(): array
    {
        return [
            'peso_libras' => 'decimal:3',
            'largo_pulg' => 'decimal:2',
            'ancho_pulg' => 'decimal:2',
            'alto_pulg' => 'decimal:2',
            'valor_declarado' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'cargo_zona' => 'decimal:2',
            'seguro' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'total' => 'decimal:2',
            'snapshot_tarifa' => 'array',
            'asegurada' => 'boolean',
            'recibida_en' => 'datetime',
        ];
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function central(): BelongsTo
    {
        return $this->belongsTo(Central::class);
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'remitente_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'destinatario_id');
    }

    public function ciudadDestino(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_destino_id');
    }

    public function tarifa(): BelongsTo
    {
        return $this->belongsTo(Tarifa::class);
    }

    public function servicioCatalogo(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }

    public function hijas(): HasMany
    {
        return $this->hasMany(GuiaHija::class, 'guia_madre_id');
    }

    public function historialEstatus(): HasMany
    {
        return $this->hasMany(HistorialEstatus::class);
    }

    public function interacciones(): HasMany
    {
        return $this->hasMany(GuiaInteraccion::class);
    }

    public function pesajes(): HasMany
    {
        return $this->hasMany(Pesaje::class);
    }

    public function consolidados(): BelongsToMany
    {
        return $this->belongsToMany(Consolidado::class, 'consolidado_guias')
            ->withPivot('agregada_en');
    }
}
