<?php

namespace Tests\Feature;

use App\Filament\Resources\Tarifas\Pages\EditTarifa;
use App\Models\Servicio;
use App\Models\Tarifa;
use App\Models\TipoCobro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TarifaServiciosFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_are_saved_from_the_tariff_form(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $tipo = TipoCobro::firstOrFail();
        $servicio = Servicio::create(['codigo' => 'LBR', 'nombre' => 'LIBRA', 'tipo_cobro_id' => $tipo->id, 'activo' => true]);
        $tarifa = Tarifa::create([
            'codigo' => 'T1', 'nombre' => 'Tarifa', 'version' => 1, 'moneda' => 'USD',
            'divisor_volumetrico' => 139, 'vigente_desde' => now()->toDateString(), 'activa' => true,
        ]);

        Livewire::test(EditTarifa::class, ['record' => $tarifa->getRouteKey()])
            ->fillForm(['servicios' => [['servicio_id' => $servicio->id, 'precio_agencia' => 1, 'precio_central' => 2]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tarifa_servicios', ['tarifa_id' => $tarifa->id, 'servicio_id' => $servicio->id]);
    }
}
