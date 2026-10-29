<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contactos', function (Blueprint $table): void {
            $table->string('direccion')->nullable()->change();
            $table->string('ciudad')->nullable()->change();
            $table->char('codigo_pais', 2)->nullable()->change();
            $table->string('telefono2', 40)->nullable()->after('telefono');
            $table->string('telefono3', 40)->nullable()->after('telefono2');
            $table->string('entre_calles')->nullable()->after('direccion');
        });
    }

    public function down(): void
    {
        Schema::table('contactos', function (Blueprint $table): void {
            $table->dropColumn(['telefono2', 'telefono3', 'entre_calles']);
        });
    }
};
