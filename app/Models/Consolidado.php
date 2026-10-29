<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Consolidado extends Model
{
    protected $table = 'consolidados';

    protected $fillable = [
        'folio', 'agencia_id', 'central_id', 'codigo_pais_destino',
        'estatus', 'cerrado_en', 'despachado_en',
    ];

    protected function casts(): array
    {
        return ['cerrado_en' => 'datetime', 'despachado_en' => 'datetime'];
    }

    public function agencia(): BelongsTo
    {
        return $this->belongsTo(Agencia::class);
    }

    public function central(): BelongsTo
    {
        return $this->belongsTo(Central::class);
    }

    public function guias(): BelongsToMany
    {
        return $this->belongsToMany(Guia::class, 'consolidado_guias')
            ->withPivot('agregada_en');
    }
}
