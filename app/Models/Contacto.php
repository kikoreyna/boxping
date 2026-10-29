<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contacto extends Model
{
    protected $table = 'contactos';

    protected $fillable = [
        'tipo', 'nombre', 'nombre_empresa', 'identificacion_fiscal', 'email',
        'telefono', 'telefono2', 'telefono3', 'direccion', 'entre_calles', 'ciudad', 'estado_region', 'codigo_postal', 'codigo_pais',
    ];

    public function guiasComoRemitente(): HasMany
    {
        return $this->hasMany(Guia::class, 'remitente_id');
    }

    public function guiasComoDestinatario(): HasMany
    {
        return $this->hasMany(Guia::class, 'destinatario_id');
    }
}
