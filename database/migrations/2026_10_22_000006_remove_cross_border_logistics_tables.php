<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('importaciones_guias');
        Schema::dropIfExists('reempaques');
        Schema::dropIfExists('consolidado_logistico_guias');
        Schema::dropIfExists('consolidados_logisticos');
        Schema::dropIfExists('manifiesto_guias');
        Schema::dropIfExists('manifiestos_carga');
        Schema::dropIfExists('bodega_escaneos');
        Schema::dropIfExists('bodegas');
        Schema::dropIfExists('vehiculos');
        Schema::dropIfExists('choferes');
        Schema::dropIfExists('transportistas');
    }

    public function down(): void
    {
        throw new LogicException('La eliminación de las tablas y los datos de logística transfronteriza no se puede revertir.');
    }
};
