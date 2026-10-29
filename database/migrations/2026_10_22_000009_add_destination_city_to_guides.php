<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->foreignId('ciudad_destino_id')
                ->nullable()
                ->after('destinatario_id')
                ->constrained('ciudades')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ciudad_destino_id');
        });
    }
};
