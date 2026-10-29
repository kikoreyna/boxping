<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuiaInteraccion extends Model
{
    protected $table = 'guias_interacciones';

    protected $fillable = ['guia_id', 'usuario_id', 'tipo', 'contenido', 'visible_cliente'];

    protected function casts(): array
    {
        return ['visible_cliente' => 'boolean'];
    }

    public function guia(): BelongsTo
    {
        return $this->belongsTo(Guia::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
