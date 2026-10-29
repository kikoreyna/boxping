<?php

namespace Tests\Feature;

use App\Filament\Resources\Guias\Pages\CreateGuia;
use App\Models\Agencia;
use App\Models\Central;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AgencyUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_user_is_limited_to_own_agency_and_guides(): void
    {
        $central = Central::create(['codigo' => 'C1', 'nombre' => 'Central Uno', 'codigo_pais' => 'MX', 'activa' => true]);
        $miAgencia = Agencia::create(['central_id' => $central->id, 'nombre' => 'Mi Agencia', 'codigo' => '00001', 'codigo_iata' => 'MEX', 'codigo_pais' => 'MX']);
        Agencia::create(['central_id' => $central->id, 'nombre' => 'Otra Agencia', 'codigo' => '00002', 'codigo_iata' => 'MEX', 'codigo_pais' => 'MX']);

        $usuario = User::factory()->create(['is_admin' => false, 'agencia_id' => $miAgencia->id]);

        $this->actingAs($usuario)->get('/admin/guias/create')
            ->assertOk()
            ->assertSee('Mi Agencia')
            ->assertDontSee('Otra Agencia');

        $this->actingAs($usuario)->get('/admin/tarifas')->assertForbidden();
        $this->actingAs($usuario)->get('/admin/usuarios')->assertForbidden();
        $this->actingAs($usuario)->get('/admin/guias')->assertOk();
    }

    public function test_administrator_defaults_to_central_and_manages_users(): void
    {
        Central::create(['codigo' => 'C1', 'nombre' => 'Central Uno', 'codigo_pais' => 'MX', 'activa' => true]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/guias/create')
            ->assertOk()
            ->assertSee('Tarifa aplicada');

        $propia = Agencia::create(['central_id' => 1, 'nombre' => 'Agencia Admin', 'codigo' => '00009', 'codigo_iata' => 'MEX', 'codigo_pais' => 'MX']);
        Agencia::create(['central_id' => 1, 'nombre' => 'Agencia Ajena', 'codigo' => '00008', 'codigo_iata' => 'MEX', 'codigo_pais' => 'MX']);
        $admin->update(['agencia_id' => $propia->id]);

        Livewire::actingAs($admin)->test(CreateGuia::class)
            ->assertFormSet(['agencia_id' => $propia->id])
            ->assertSee('Agencia Ajena');

        $this->actingAs($admin)->get('/admin/usuarios/create')->assertOk()->assertSee('Agencia');
    }
}
