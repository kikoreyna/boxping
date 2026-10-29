<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_cobro', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre');
            $table->string('base_calculo', 30); // pieza, libra_facturable, libra_real, pie_cubico
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('tipos_cobro')->insert([
            ['codigo' => 'caja', 'nombre' => 'Por medida de caja', 'base_calculo' => 'pieza', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'libra', 'nombre' => 'Por libra', 'base_calculo' => 'libra_facturable', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'pie_cubico', 'nombre' => 'Por pie cúbico', 'base_calculo' => 'pie_cubico', 'activo' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('servicios', function (Blueprint $table): void {
            $table->foreignId('tipo_cobro_id')->nullable()->after('nombre')->constrained('tipos_cobro')->restrictOnDelete();
        });

        foreach (DB::table('tipos_cobro')->get() as $tipo) {
            DB::table('servicios')->where('tipo_cobro', $tipo->codigo)->update(['tipo_cobro_id' => $tipo->id]);
        }

        Schema::table('servicios', function (Blueprint $table): void {
            $table->dropColumn('tipo_cobro');
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table): void {
            $table->string('tipo_cobro', 20)->default('libra');
        });
        foreach (DB::table('tipos_cobro')->get() as $tipo) {
            DB::table('servicios')->where('tipo_cobro_id', $tipo->id)->update(['tipo_cobro' => $tipo->codigo]);
        }
        Schema::table('servicios', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tipo_cobro_id');
        });
        Schema::dropIfExists('tipos_cobro');
    }
};
