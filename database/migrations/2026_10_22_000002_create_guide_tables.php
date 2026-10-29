<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 13)->unique();
            $table->foreignId('agencia_id')->constrained('agencias')->restrictOnDelete();
            $table->foreignId('central_id')->nullable()->constrained('centrales')->nullOnDelete();
            $table->foreignId('remitente_id')->constrained('contactos')->restrictOnDelete();
            $table->foreignId('destinatario_id')->constrained('contactos')->restrictOnDelete();
            $table->foreignId('tarifa_id')->nullable()->constrained('tarifas')->nullOnDelete();
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->nullOnDelete();
            $table->string('servicio', 40)->default('standard');
            $table->string('estatus', 30)->default('recibida');
            $table->char('codigo_pais_destino', 2)->index();
            $table->string('rastreo_destino')->nullable()->unique();
            $table->decimal('peso_libras', 10, 3)->default(0);
            $table->decimal('largo_cm', 10, 2)->nullable();
            $table->decimal('ancho_cm', 10, 2)->nullable();
            $table->decimal('alto_cm', 10, 2)->nullable();
            $table->decimal('valor_declarado', 12, 2)->default(0);
            $table->char('moneda', 3)->default('USD');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('seguro', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->json('snapshot_tarifa')->nullable();
            $table->timestamp('recibida_en')->nullable();
            $table->timestamps();
            $table->index(['agencia_id', 'estatus', 'created_at']);
            $table->index(['central_id', 'estatus']);
        });

        Schema::create('guias_hijas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_madre_id')->constrained('guias')->cascadeOnDelete();
            $table->string('folio', 13)->unique();
            $table->string('rastreo_destino')->nullable()->unique();
            $table->string('descripcion')->nullable();
            $table->string('estatus', 30)->default('recibida');
            $table->decimal('peso_libras', 10, 3)->default(0);
            $table->decimal('largo_cm', 10, 2)->nullable();
            $table->decimal('ancho_cm', 10, 2)->nullable();
            $table->decimal('alto_cm', 10, 2)->nullable();
            $table->timestamps();
            $table->index(['guia_madre_id', 'estatus']);
        });

        Schema::create('historial_estatus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->nullable()->constrained('guias')->cascadeOnDelete();
            $table->foreignId('guia_hija_id')->nullable()->constrained('guias_hijas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estatus', 30);
            $table->string('ubicacion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('registrado_en')->useCurrent();
            $table->timestamps();
            $table->index(['guia_id', 'registrado_en']);
            $table->index(['guia_hija_id', 'registrado_en']);
        });

        Schema::create('guias_interacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->constrained('guias')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 40);
            $table->text('contenido');
            $table->boolean('visible_cliente')->default(false);
            $table->timestamps();
            $table->index(['guia_id', 'created_at']);
        });

        Schema::create('pesajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->nullable()->constrained('guias')->cascadeOnDelete();
            $table->foreignId('guia_hija_id')->nullable()->constrained('guias_hijas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('punto', 40);
            $table->decimal('peso_libras', 10, 3);
            $table->decimal('largo_cm', 10, 2)->nullable();
            $table->decimal('ancho_cm', 10, 2)->nullable();
            $table->decimal('alto_cm', 10, 2)->nullable();
            $table->timestamp('pesado_en')->useCurrent();
            $table->timestamps();
            $table->index(['guia_id', 'punto', 'pesado_en']);
            $table->index(['guia_hija_id', 'punto', 'pesado_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesajes');
        Schema::dropIfExists('guias_interacciones');
        Schema::dropIfExists('historial_estatus');
        Schema::dropIfExists('guias_hijas');
        Schema::dropIfExists('guias');
    }
};
