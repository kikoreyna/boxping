<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportistas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 30);
            $table->char('codigo_pais', 2)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('choferes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('identificacion')->nullable()->unique();
            $table->string('telefono', 40)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transportista_id')->nullable()->constrained('transportistas')->nullOnDelete();
            $table->string('placa', 30)->unique();
            $table->string('tipo', 30)->nullable();
            $table->string('numero_economico')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('bodegas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre');
            $table->string('tipo', 30);
            $table->char('codigo_pais', 2)->index();
            $table->string('ciudad')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('bodega_escaneos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bodega_id')->constrained('bodegas')->restrictOnDelete();
            $table->foreignId('guia_id')->nullable()->constrained('guias')->cascadeOnDelete();
            $table->foreignId('guia_hija_id')->nullable()->constrained('guias_hijas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('codigo_barras', 80)->index();
            $table->string('evento', 40);
            $table->decimal('peso_libras', 10, 3)->nullable();
            $table->decimal('largo_cm', 10, 2)->nullable();
            $table->decimal('ancho_cm', 10, 2)->nullable();
            $table->decimal('alto_cm', 10, 2)->nullable();
            $table->timestamp('escaneado_en')->useCurrent();
            $table->timestamps();
            $table->index(['bodega_id', 'escaneado_en']);
            $table->index(['guia_id', 'escaneado_en']);
        });

        Schema::create('manifiestos_carga', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 40)->unique();
            $table->foreignId('transportista_id')->nullable()->constrained('transportistas')->nullOnDelete();
            $table->string('estatus', 30)->default('abierto');
            $table->timestamp('salida_programada_en')->nullable();
            $table->timestamp('despachado_en')->nullable();
            $table->timestamps();
        });

        Schema::create('manifiesto_guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manifiesto_id')->constrained('manifiestos_carga')->cascadeOnDelete();
            $table->foreignId('guia_id')->constrained('guias')->restrictOnDelete();
            $table->timestamp('agregada_en')->useCurrent();
            $table->unique(['manifiesto_id', 'guia_id']);
            $table->index('guia_id');
        });

        Schema::create('consolidados_logisticos', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 40)->unique();
            $table->foreignId('manifiesto_id')->nullable()->constrained('manifiestos_carga')->nullOnDelete();
            $table->string('estatus', 30)->default('abierto');
            $table->string('tipo_cruce', 40)->nullable();
            $table->foreignId('transportista_id')->nullable()->constrained('transportistas')->nullOnDelete();
            $table->foreignId('chofer_id')->nullable()->constrained('choferes')->nullOnDelete();
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos')->nullOnDelete();
            $table->string('aliado_ocurre')->nullable();
            $table->timestamp('cruce_programado_en')->nullable();
            $table->timestamp('cruzado_en')->nullable();
            $table->timestamps();
            $table->index(['estatus', 'cruce_programado_en']);
        });

        Schema::create('consolidado_logistico_guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consolidado_logistico_id')->constrained('consolidados_logisticos')->cascadeOnDelete();
            $table->foreignId('guia_id')->constrained('guias')->restrictOnDelete();
            $table->timestamp('agregada_en')->useCurrent();
            $table->unique(
                ['consolidado_logistico_id', 'guia_id'],
                'consolidado_logistico_guias_pair_unique',
            );
            $table->index('guia_id');
        });

        Schema::create('reempaques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->nullable()->constrained('guias')->cascadeOnDelete();
            $table->foreignId('guia_hija_id')->nullable()->constrained('guias_hijas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estatus', 30)->default('en_proceso');
            $table->timestamp('iniciado_en')->useCurrent();
            $table->timestamp('completado_en')->nullable();
            $table->decimal('peso_anterior_libras', 10, 3)->nullable();
            $table->decimal('peso_nuevo_libras', 10, 3)->nullable();
            $table->json('medidas_nuevas')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index(['estatus', 'iniciado_en']);
        });

        Schema::create('importaciones_guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('archivo');
            $table->string('estatus', 30)->default('pendiente');
            $table->unsignedInteger('filas_totales')->default(0);
            $table->unsignedInteger('filas_procesadas')->default(0);
            $table->json('errores')->nullable();
            $table->timestamp('procesada_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
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
};
