<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->char('codigo_iso2', 2)->unique();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pais_id')->constrained('paises')->cascadeOnDelete();
            $table->string('codigo', 20)->nullable();
            $table->string('nombre');
            $table->timestamps();
            $table->unique(['pais_id', 'codigo']);
            $table->index(['pais_id', 'nombre']);
        });

        Schema::create('ciudades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pais_id')->constrained('paises')->cascadeOnDelete();
            $table->foreignId('estado_id')->nullable()->constrained('estados')->nullOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->restrictOnDelete();
            $table->string('nombre');
            $table->string('codigo_externo', 40)->nullable();
            $table->timestamps();
            $table->index(['pais_id', 'estado_id', 'nombre']);
            $table->unique(['pais_id', 'codigo_externo']);
        });

        Schema::table('guias', function (Blueprint $table) {
            $table->decimal('cargo_zona', 12, 2)->default(0)->after('subtotal');
        });

        $now = now();
        foreach ([
            ['A', 'Zona A (base)'],
            ['B', 'Zona B'],
            ['C', 'Zona C'],
            ['D', 'Zona D'],
        ] as [$codigo, $nombre]) {
            DB::table('zonas')->insertOrIgnore([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'codigo_pais' => 'ZZ',
                'activa' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $zonaBaseId = DB::table('zonas')->where('codigo', 'A')->value('id');
        DB::table('ciudades')->update(['zona_id' => $zonaBaseId]);
    }

    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn('cargo_zona');
        });

        Schema::dropIfExists('ciudades');
        Schema::dropIfExists('estados');
        Schema::dropIfExists('paises');

    }
};
