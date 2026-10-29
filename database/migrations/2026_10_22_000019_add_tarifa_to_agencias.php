<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencias', function (Blueprint $table) {
            $table->foreignId('tarifa_id')->nullable()->after('central_id')->constrained('tarifas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agencias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tarifa_id');
        });
    }
};
