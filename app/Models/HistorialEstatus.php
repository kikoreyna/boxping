<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstatus extends Model
{
    protected $table = 'historial_estatus';

    protected $fillable = [
        'guia_id', 'guia_hija_id', 'usuario_id', 'estatus', 'ubicacion', 'notas', 'registrado_en',
    ];

    protected function casts(): array
    {
        return ['registrado_en' => 'datetime'];
    }

    public function guia(): BelongsTo
    {
        return $this->belongsTo(Guia::class);
    }

    public function guiaHija(): BelongsTo
    {
        return $this->belongsTo(GuiaHija::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
