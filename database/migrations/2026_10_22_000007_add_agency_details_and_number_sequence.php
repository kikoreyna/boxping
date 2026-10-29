<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencias', function (Blueprint $table) {
            $table->string('alias')->nullable()->after('central_id');
            $table->string('direccion')->nullable()->after('nombre');
            $table->string('telefono', 40)->nullable()->after('direccion');
            $table->string('email')->nullable()->after('telefono');
            $table->string('estado')->nullable()->after('ciudad');
            $table->string('pais')->nullable()->after('estado');
            $table->string('codigo_postal', 20)->nullable()->after('pais');
            $table->text('observaciones')->nullable()->after('codigo_postal');
        });

        Schema::create('secuencia_agencias', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedInteger('siguiente_numero');
        });

        $ultimoCodigoNumerico = DB::table('agencias')
            ->pluck('codigo')
            ->filter(fn (string $codigo): bool => ctype_digit($codigo))
            ->map(fn (string $codigo): int => (int) $codigo)
            ->max() ?? 0;

        DB::table('secuencia_agencias')->insert([
            'id' => 1,
            'siguiente_numero' => $ultimoCodigoNumerico + 1,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('secuencia_agencias');

        Schema::table('agencias', function (Blueprint $table) {
            $table->dropColumn([
                'alias',
                'direccion',
                'telefono',
                'email',
                'estado',
                'pais',
                'codigo_postal',
                'observaciones',
            ]);
        });
    }
};
