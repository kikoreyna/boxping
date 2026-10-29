<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impuestos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('formula');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('tarifas', function (Blueprint $table): void {
            $table->foreignId('impuesto_id')->nullable()->after('libras_minimas')->constrained('impuestos')->nullOnDelete();
        });

        $now = now();
        foreach (DB::table('tarifas')->whereNotNull('formula_impuestos')->distinct()->pluck('formula_impuestos') as $formula) {
            $id = DB::table('impuestos')->insertGetId([
                'nombre' => $formula,
                'formula' => $formula,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('tarifas')->where('formula_impuestos', $formula)->update(['impuesto_id' => $id]);
        }

        Schema::table('tarifas', function (Blueprint $table): void {
            $table->dropColumn('formula_impuestos');
        });
    }

    public function down(): void
    {
        Schema::table('tarifas', function (Blueprint $table): void {
            $table->string('formula_impuestos')->default('VALORDECLARADO(2000,0:0,0:30)');
        });

        foreach (DB::table('impuestos')->get() as $impuesto) {
            DB::table('tarifas')->where('impuesto_id', $impuesto->id)->update(['formula_impuestos' => $impuesto->formula]);
        }

        Schema::table('tarifas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('impuesto_id');
        });
        Schema::dropIfExists('impuestos');
    }
};
