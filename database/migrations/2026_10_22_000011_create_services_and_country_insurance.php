<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre');
            $table->string('tipo_cobro', 20); // caja, libra, pie_cubico
            $table->decimal('largo_cm', 10, 2)->nullable();
            $table->decimal('ancho_cm', 10, 2)->nullable();
            $table->decimal('alto_cm', 10, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('tarifa_servicios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tarifa_id')->constrained('tarifas')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->restrictOnDelete();
            $table->decimal('precio_agencia', 12, 4)->default(0);
            $table->decimal('precio_central', 12, 4)->default(0);
            $table->timestamps();

            $table->unique(['tarifa_id', 'servicio_id']);
        });

        Schema::create('seguros_pais', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pais_id')->unique()->constrained('paises')->cascadeOnDelete();
            $table->boolean('opcional')->default(true);
            $table->decimal('porcentaje_agencia', 8, 4)->default(0);
            $table->decimal('porcentaje_central', 8, 4)->default(0);
            $table->decimal('minimo_agencia', 12, 2)->default(0);
            $table->decimal('minimo_central', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('tarifas', function (Blueprint $table): void {
            $table->string('formula_impuestos')->default('VALORDECLARADO(2000,0:0,0:30)')->after('divisor_volumetrico');
        });

        Schema::table('guias', function (Blueprint $table): void {
            $table->foreignId('servicio_id')->nullable()->after('servicio')->constrained('servicios')->nullOnDelete();
            $table->boolean('asegurada')->default(false)->after('servicio_id');
        });
    }

    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('servicio_id');
            $table->dropColumn('asegurada');
        });
        Schema::table('tarifas', function (Blueprint $table): void {
            $table->dropColumn('formula_impuestos');
        });
        Schema::dropIfExists('seguros_pais');
        Schema::dropIfExists('tarifa_servicios');
        Schema::dropIfExists('servicios');
    }
};
