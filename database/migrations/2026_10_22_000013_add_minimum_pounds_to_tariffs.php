<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarifas', function (Blueprint $table): void {
            $table->decimal('libras_minimas', 10, 3)->default(0)->after('divisor_volumetrico');
        });
    }

    public function down(): void
    {
        Schema::table('tarifas', function (Blueprint $table): void {
            $table->dropColumn('libras_minimas');
        });
    }
};
