<?php

use App\Models\Central;
use App\Services\Logistica\AgenciaRegistrationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencias', function (Blueprint $table) {
            $table->boolean('es_central')->default(false)->after('activa');
        });

        $servicio = app(AgenciaRegistrationService::class);
        Central::query()->where('activa', true)->each(fn (Central $central) => $servicio->asegurarAgenciaDeCentral($central));
    }

    public function down(): void
    {
        Schema::table('agencias', function (Blueprint $table) {
            $table->dropColumn('es_central');
        });
    }
};
