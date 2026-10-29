<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCobro extends Model
{
    public const BASES = [
        'pieza' => 'Por pieza / caja',
        'libra_facturable' => 'Por libra facturable (mayor entre real y volumétrico)',
        'libra_real' => 'Por libra real',
        'pie_cubico' => 'Por pie cúbico',
    ];

    protected $table = 'tipos_cobro';

    protected $attributes = ['activo' => true];

    protected $fillable = ['codigo', 'nombre', 'base_calculo', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }
}
