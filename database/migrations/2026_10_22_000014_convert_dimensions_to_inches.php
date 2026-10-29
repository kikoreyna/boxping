<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLAS = ['guias', 'guias_hijas', 'pesajes', 'servicios'];

    private const CM_POR_PULGADA = 2.54;

    private const CM3_POR_PULGADA3 = 16.387064;

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table): void {
                $table->renameColumn('largo_cm', 'largo_pulg');
                $table->renameColumn('ancho_cm', 'ancho_pulg');
                $table->renameColumn('alto_cm', 'alto_pulg');
            });

            foreach (['largo_pulg', 'ancho_pulg', 'alto_pulg'] as $columna) {
                DB::table($tabla)->whereNotNull($columna)->update([
                    $columna => DB::raw("ROUND({$columna} / ".self::CM_POR_PULGADA.', 2)'),
                ]);
            }
        }

        DB::table('tarifas')->update([
            'divisor_volumetrico' => DB::raw('ROUND(divisor_volumetrico / '.self::CM3_POR_PULGADA3.', 4)'),
        ]);
    }

    public function down(): void
    {
        DB::table('tarifas')->update([
            'divisor_volumetrico' => DB::raw('ROUND(divisor_volumetrico * '.self::CM3_POR_PULGADA3.', 4)'),
        ]);

        foreach (self::TABLAS as $tabla) {
            foreach (['largo_pulg', 'ancho_pulg', 'alto_pulg'] as $columna) {
                DB::table($tabla)->whereNotNull($columna)->update([
                    $columna => DB::raw("ROUND({$columna} * ".self::CM_POR_PULGADA.', 2)'),
                ]);
            }

            Schema::table($tabla, function (Blueprint $table): void {
                $table->renameColumn('largo_pulg', 'largo_cm');
                $table->renameColumn('ancho_pulg', 'ancho_cm');
                $table->renameColumn('alto_pulg', 'alto_cm');
            });
        }
    }
};
