<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pesaje extends Model
{
    protected $table = 'pesajes';

    protected $fillable = [
        'guia_id', 'guia_hija_id', 'usuario_id', 'punto', 'peso_libras',
        'largo_pulg', 'ancho_pulg', 'alto_pulg', 'pesado_en',
    ];

    protected function casts(): array
    {
        return [
            'peso_libras' => 'decimal:3',
            'largo_pulg' => 'decimal:2',
            'ancho_pulg' => 'decimal:2',
            'alto_pulg' => 'decimal:2',
            'pesado_en' => 'datetime',
        ];
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
