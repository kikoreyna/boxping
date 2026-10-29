<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consolidados', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 40)->unique();
            $table->foreignId('agencia_id')->nullable()->constrained('agencias')->nullOnDelete();
            $table->foreignId('central_id')->nullable()->constrained('centrales')->nullOnDelete();
            $table->char('codigo_pais_destino', 2)->index();
            $table->string('estatus', 30)->default('abierto');
            $table->timestamp('cerrado_en')->nullable();
            $table->timestamp('despachado_en')->nullable();
            $table->timestamps();
            $table->index(['central_id', 'estatus']);
            $table->index(['agencia_id', 'estatus']);
        });

        Schema::create('consolidado_guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consolidado_id')->constrained('consolidados')->cascadeOnDelete();
            $table->foreignId('guia_id')->constrained('guias')->restrictOnDelete();
            $table->timestamp('agregada_en')->useCurrent();
            $table->unique(['consolidado_id', 'guia_id']);
            $table->index('guia_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consolidado_guias');
        Schema::dropIfExists('consolidados');
    }
};
