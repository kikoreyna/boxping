<?php

namespace Tests\Feature;

use App\Filament\Resources\Paises\Pages\ListPaises;
use App\Models\Pais;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_admin_login_is_available_in_spanish(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('lang="es"', false)
            ->assertSee('Correo electrónico')
            ->assertSee('Contraseña')
            ->assertSee('Entrar');
    }

    public function test_only_admin_users_can_open_the_panel(): void
    {
        $regularUser = User::factory()->create(['is_admin' => false]);

        $this->actingAs($regularUser)
            ->get('/admin')
            ->assertForbidden();

        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)
            ->get('/admin')
            ->assertOk();
    }

    public function test_guide_and_tariff_forms_render_for_an_administrator(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)
            ->get('/admin/guias/create')
            ->assertOk()
            ->assertSee('Folio de guía')
            ->assertSee('Valor declarado')
            ->assertSee('País de destino')
            ->assertSee('Ciudad de destino');

        $this->actingAs($administrator)
            ->get('/admin/tarifas/create')
            ->assertOk()
            ->assertSee('Servicios de la tarifa')
            ->assertSee('Divisor volumétrico');

        $this->actingAs($administrator)
            ->get('/admin/servicios/create')
            ->assertOk()
            ->assertSee('Tipo de cobro');

        $this->actingAs($administrator)
            ->get('/admin/tipos-de-cobro/create')
            ->assertOk()
            ->assertSee('Cómo se calcula');

        $this->actingAs($administrator)
            ->get('/admin/impuestos/create')
            ->assertOk()
            ->assertSee('Fórmula');

        $this->actingAs($administrator)
            ->get('/admin/seguros-por-pais/create')
            ->assertOk()
            ->assertSee('Seguro opcional');

        $this->actingAs($administrator)
            ->get('/admin/centrales/create')
            ->assertOk()
            ->assertSee('Nombre');
    }

    public function test_agency_form_contains_business_details_and_an_automatically_assigned_code(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        $this->actingAs($administrator)
            ->get('/admin/agencias/create')
            ->assertOk()
            ->assertSee('Alias')
            ->assertSee('Nombre de la agencia')
            ->assertSee('Central')
            ->assertSee('Código IATA')
            ->assertSee('Código de agencia (5 dígitos)')
            ->assertSee('Se asigna automáticamente al guardar')
            ->assertSee('Dirección')
            ->assertSee('Teléfono')
            ->assertSee('Correo electrónico')
            ->assertSee('Ciudad')
            ->assertSee('Estado')
            ->assertSee('País')
            ->assertSee('Código postal')
            ->assertSee('Observaciones');
    }

    public function test_administrator_can_activate_and_deactivate_all_countries(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);
        $activeCountry = Pais::query()->create([
            'codigo_iso2' => 'MX',
            'nombre' => 'México',
            'activo' => true,
        ]);
        $inactiveCountry = Pais::query()->create([
            'codigo_iso2' => 'US',
            'nombre' => 'Estados Unidos',
            'activo' => false,
        ]);

        Livewire::actingAs($administrator)
            ->test(ListPaises::class)
            ->assertSee('Activar todos')
            ->assertSee('Desactivar todos')
            ->callAction('desactivarTodos')
            ->assertNotified('Todos los países están inactivos');

        $this->assertFalse($activeCountry->fresh()->activo);
        $this->assertFalse($inactiveCountry->fresh()->activo);

        Livewire::actingAs($administrator)
            ->test(ListPaises::class)
            ->callAction('activarTodos')
            ->assertNotified('Todos los países están activos');

        $this->assertTrue($activeCountry->fresh()->activo);
        $this->assertTrue($inactiveCountry->fresh()->activo);
    }

    public function test_remaining_catalogs_and_consolidation_forms_render(): void
    {
        $administrator = User::factory()->create(['is_admin' => true]);

        foreach ([
            '/admin/agencias',
            '/admin/agencias/create',
            '/admin/centrales',
            '/admin/centrales/create',
            '/admin/contactos',
            '/admin/contactos/create',
            '/admin/zonas',
            '/admin/zonas/create',
            '/admin/ciudades',
            '/admin/ciudades/create',
            '/admin/paises',
            '/admin/paises/create',
            '/admin/consolidados',
            '/admin/consolidados/create',
            '/admin/guias',
            '/admin/tarifas',
        ] as $path) {
            $this->actingAs($administrator)
                ->get($path)
                ->assertOk();
        }

        foreach ([
            '/admin/bodegas',
            '/admin/bodega-escaneos',
            '/admin/bodega-escaneos/create',
            '/admin/escaneos-bodega',
            '/admin/escaneos-bodega/create',
            '/admin/manifiestos',
            '/admin/manifiestos/create',
            '/admin/consolidados-logisticos',
            '/admin/consolidados-logisticos/create',
            '/admin/transportistas',
            '/admin/choferes',
            '/admin/vehiculos',
        ] as $path) {
            $this->actingAs($administrator)
                ->get($path)
                ->assertNotFound();
        }

        $this->actingAs($administrator)
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('Logística transfronteriza')
            ->assertDontSee('Escaneos de bodega');
    }

    public function test_cross_border_logistics_tables_are_removed_but_core_tables_remain(): void
    {
        foreach ([
            'importaciones_guias',
            'reempaques',
            'consolidado_logistico_guias',
            'consolidados_logisticos',
            'manifiesto_guias',
            'manifiestos_carga',
            'bodega_escaneos',
            'bodegas',
            'vehiculos',
            'choferes',
            'transportistas',
        ] as $table) {
            $this->assertFalse(Schema::hasTable($table), "La tabla [{$table}] debería haber sido eliminada.");
        }

        foreach (['guias', 'guias_hijas', 'consolidados', 'contactos', 'tarifas', 'agencias', 'centrales'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "La tabla principal [{$table}] debe conservarse.");
        }
    }
}
