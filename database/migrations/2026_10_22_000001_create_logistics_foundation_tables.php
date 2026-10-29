<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->default('persona');
            $table->string('nombre');
            $table->string('nombre_empresa')->nullable();
            $table->string('identificacion_fiscal')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('direccion');
            $table->string('ciudad');
            $table->string('estado_region')->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->char('codigo_pais', 2)->index();
            $table->timestamps();
        });

        Schema::create('centrales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre');
            $table->string('ciudad')->nullable();
            $table->char('codigo_pais', 2)->index();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('agencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('central_id')->nullable()->constrained('centrales')->nullOnDelete();
            $table->string('nombre');
            $table->string('codigo', 5);
            $table->char('codigo_iata', 3);
            $table->unsignedInteger('siguiente_consecutivo_guia')->default(1);
            $table->string('ciudad')->nullable();
            $table->char('codigo_pais', 2)->index();
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->unique(['codigo_iata', 'codigo']);
            $table->index(['central_id', 'activa']);
        });

        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre');
            $table->char('codigo_pais', 2)->index();
            $table->string('estado_region')->nullable();
            $table->string('codigo_postal_inicio', 20)->nullable();
            $table->string('codigo_postal_fin', 20)->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agencia_id')->nullable()->constrained('agencias')->nullOnDelete();
            $table->foreignId('clonada_de_id')->nullable()->constrained('tarifas')->nullOnDelete();
            $table->string('codigo', 40);
            $table->string('nombre');
            $table->unsignedInteger('version')->default(1);
            $table->char('moneda', 3)->default('USD');
            $table->decimal('divisor_volumetrico', 12, 4);
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->unique(['agencia_id', 'codigo', 'version']);
            $table->index(['agencia_id', 'activa', 'vigente_desde']);
        });

        Schema::create('tarifa_reglas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarifa_id')->constrained('tarifas')->cascadeOnDelete();
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->nullOnDelete();
            $table->string('servicio', 40)->default('standard');
            $table->string('componente', 20);
            $table->string('base_calculo', 30);
            $table->boolean('es_porcentaje')->default(false);
            $table->decimal('tarifa_unitaria', 12, 4);
            $table->decimal('cargo_minimo', 12, 2)->default(0);
            $table->timestamps();
            $table->index(['tarifa_id', 'zona_id', 'servicio', 'componente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifa_reglas');
        Schema::dropIfExists('tarifas');
        Schema::dropIfExists('zonas');
        Schema::dropIfExists('agencias');
        Schema::dropIfExists('centrales');
        Schema::dropIfExists('contactos');
    }
};
