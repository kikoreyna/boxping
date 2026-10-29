<?php

namespace Tests\Feature;

use App\Filament\Resources\Contactos\Schemas\ContactoForm;
use App\Filament\Resources\Guias\Pages\CreateGuia;
use App\Models\Agencia;
use App\Models\Central;
use App\Models\Ciudad;
use App\Models\Contacto;
use App\Models\Estado;
use App\Models\Guia;
use App\Models\Impuesto;
use App\Models\Pais;
use App\Models\SeguroPais;
use App\Models\Servicio;
use App\Models\Tarifa;
use App\Models\TarifaRegla;
use App\Models\TarifaServicio;
use App\Models\TipoCobro;
use App\Models\User;
use App\Models\Zona;
use App\Services\Logistica\AgenciaRegistrationService;
use App\Services\Logistica\GuiaFolioService;
use App\Services\Tarifas\CotizadorServicio;
use App\Services\Tarifas\FormulaImpuestos;
use App\Services\Tarifas\TarifaCalculator;
use App\Services\Tarifas\TarifaVersioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use Livewire\Livewire;
use OverflowException;
use Tests\TestCase;

class LogisticsCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_a_padded_agency_guide_folio_and_advances_sequence(): void
    {
        $agency = Agencia::query()->create([
            'nombre' => 'Agencia Centro',
            'codigo' => '00123',
            'codigo_iata' => 'MEX',
            'codigo_pais' => 'MX',
        ]);

        $service = app(GuiaFolioService::class);

        $this->assertSame('MEX0012300001', $service->generar($agency));
        $this->assertSame('MEX0012300002', $service->generar($agency));
        $this->assertSame(3, $agency->fresh()->siguiente_consecutivo_guia);
    }

    public function test_it_refuses_to_overflow_the_five_digit_guide_sequence(): void
    {
        $agency = Agencia::query()->create([
            'nombre' => 'Agencia Centro',
            'codigo' => '00123',
            'codigo_iata' => 'MEX',
            'siguiente_consecutivo_guia' => 100000,
            'codigo_pais' => 'MX',
        ]);

        $this->expectException(OverflowException::class);

        app(GuiaFolioService::class)->generar($agency);
    }

    public function test_it_assigns_sequential_five_digit_agency_codes_and_requires_an_active_central(): void
    {
        $central = Central::query()->create([
            'codigo' => 'CENTRO',
            'nombre' => 'Central Centro',
            'codigo_pais' => 'MX',
        ]);
        $service = app(AgenciaRegistrationService::class);

        $first = $service->crear([
            'central_id' => $central->id,
            'nombre' => 'Agencia Uno',
            'codigo_iata' => 'MEX',
            'codigo_pais' => 'MX',
        ]);
        $second = $service->crear([
            'central_id' => $central->id,
            'nombre' => 'Agencia Dos',
            'codigo_iata' => 'MEX',
            'codigo_pais' => 'MX',
        ]);

        $this->assertSame('00001', $first->codigo);
        $this->assertSame('00002', $second->codigo);
        $this->assertSame($central->id, $first->central_id);

        $this->expectException(InvalidArgumentException::class);
        $service->crear([
            'nombre' => 'Agencia sin central',
            'codigo_iata' => 'MEX',
            'codigo_pais' => 'MX',
        ]);
    }

    public function test_it_calculates_freight_insurance_and_tax_and_returns_a_snapshot(): void
    {
        $tariff = Tarifa::query()->create([
            'codigo' => 'BASE',
            'nombre' => 'Tarifa base',
            'moneda' => 'USD',
            'divisor_volumetrico' => 1000,
        ]);

        TarifaRegla::query()->create([
            'tarifa_id' => $tariff->id,
            'servicio' => 'standard',
            'componente' => 'flete',
            'base_calculo' => 'chargeable_weight',
            'tarifa_unitaria' => 3,
        ]);
        TarifaRegla::query()->create([
            'tarifa_id' => $tariff->id,
            'servicio' => 'standard',
            'componente' => 'seguro',
            'base_calculo' => 'declared_value',
            'es_porcentaje' => true,
            'tarifa_unitaria' => 2,
        ]);
        TarifaRegla::query()->create([
            'tarifa_id' => $tariff->id,
            'servicio' => 'standard',
            'componente' => 'impuesto',
            'base_calculo' => 'freight_subtotal',
            'es_porcentaje' => true,
            'tarifa_unitaria' => 10,
        ]);

        $quote = app(TarifaCalculator::class)->calculate(
            $tariff,
            pesoRealLibras: 2,
            largoPulg: 10,
            anchoPulg: 10,
            altoPulg: 10,
            valorDeclarado: 100,
        );

        $this->assertSame(6.0, $quote['subtotal']);
        $this->assertSame(2.0, $quote['seguro']);
        $this->assertSame(0.8, $quote['impuestos']);
        $this->assertSame(8.8, $quote['total']);
        $this->assertCount(3, $quote['detalle']);
    }

    public function test_city_importer_assigns_zone_a_by_default_and_can_update_to_zone_b(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'boxping-ciudades-');
        file_put_contents($path, implode("\n", [
            'country_iso2,country_name,state_code,state_name,city,city_id,zone_code',
            'MX,México,CMX,Ciudad de México,Centro,city-001,',
            'MX,México,CMX,Ciudad de México,Norte,city-002,B',
        ]));

        try {
            $this->assertSame(0, Artisan::call('boxping:importar-ciudades', ['archivo' => $path]));
            $this->assertSame(2, Ciudad::query()->count());
            $this->assertSame('A', Ciudad::query()->where('codigo_externo', 'city-001')->firstOrFail()->zona->codigo);
            $this->assertSame('B', Ciudad::query()->where('codigo_externo', 'city-002')->firstOrFail()->zona->codigo);

            $this->assertSame(0, Artisan::call('boxping:importar-ciudades', ['archivo' => $path]));
            $this->assertSame(2, Ciudad::query()->count());
            $this->assertSame(1, Pais::query()->where('codigo_iso2', 'MX')->count());
            $this->assertSame(1, Estado::query()->where('codigo', 'CMX')->count());
        } finally {
            unlink($path);
        }
    }

    public function test_tariff_applies_zone_surcharge_only_to_the_selected_zone(): void
    {
        $zoneB = Zona::query()->where('codigo', 'B')->firstOrFail();
        $tariff = Tarifa::query()->create([
            'codigo' => 'BASE',
            'nombre' => 'Tarifa base',
            'divisor_volumetrico' => 5000,
        ]);

        TarifaRegla::query()->create([
            'tarifa_id' => $tariff->id,
            'servicio' => 'standard',
            'componente' => 'flete',
            'base_calculo' => 'chargeable_weight',
            'tarifa_unitaria' => 5,
        ]);
        TarifaRegla::query()->create([
            'tarifa_id' => $tariff->id,
            'zona_id' => $zoneB->id,
            'servicio' => 'standard',
            'componente' => 'cargo_zona',
            'base_calculo' => 'fixed',
            'tarifa_unitaria' => 7,
        ]);

        $baseZoneQuote = app(TarifaCalculator::class)->calculate($tariff, pesoRealLibras: 2);
        $zoneBQuote = app(TarifaCalculator::class)->calculate($tariff, pesoRealLibras: 2, zonaId: $zoneB->id);

        $this->assertSame(0.0, $baseZoneQuote['cargo_zona']);
        $this->assertSame(10.0, $baseZoneQuote['total']);
        $this->assertSame(7.0, $zoneBQuote['cargo_zona']);
        $this->assertSame(17.0, $zoneBQuote['total']);
    }

    public function test_guide_uses_destination_city_country_and_zone_for_tariff_calculation(): void
    {
        $central = Central::query()->create([
            'codigo' => 'CENTRO',
            'nombre' => 'Central Centro',
            'codigo_pais' => 'MX',
        ]);
        $agency = Agencia::query()->create([
            'central_id' => $central->id,
            'nombre' => 'Agencia Centro',
            'codigo' => '00001',
            'codigo_iata' => 'MEX',
            'codigo_pais' => 'MX',
        ]);
        $country = Pais::query()->create(['codigo_iso2' => 'MX', 'nombre' => 'México']);
        $state = Estado::query()->create(['pais_id' => $country->id, 'codigo' => 'JAL', 'nombre' => 'Jalisco']);
        $zoneB = Zona::query()->where('codigo', 'B')->firstOrFail();
        $city = Ciudad::query()->create([
            'pais_id' => $country->id,
            'estado_id' => $state->id,
            'zona_id' => $zoneB->id,
            'nombre' => 'Guadalajara',
        ]);
        $sender = Contacto::query()->create([
            'nombre' => 'Remitente',
            'direccion' => 'Calle Uno',
            'ciudad' => 'Ciudad de México',
            'codigo_pais' => 'MX',
        ]);
        $recipient = Contacto::query()->create([
            'nombre' => 'Destinatario',
            'direccion' => 'Calle Dos',
            'ciudad' => 'Guadalajara',
            'codigo_pais' => 'MX',
        ]);
        $tariff = Tarifa::query()->create([
            'codigo' => 'ENVIO',
            'nombre' => 'Tarifa de envío',
            'divisor_volumetrico' => 5000,
        ]);
        $service = Servicio::query()->create(['codigo' => 'LIBRA', 'nombre' => 'Por libra', 'tipo_cobro_id' => TipoCobro::query()->where('codigo', 'libra')->value('id')]);
        TarifaServicio::query()->create([
            'tarifa_id' => $tariff->id,
            'servicio_id' => $service->id,
            'precio_agencia' => 2,
            'precio_central' => 3,
        ]);
        SeguroPais::query()->create([
            'pais_id' => $country->id,
            'porcentaje_agencia' => 1,
            'porcentaje_central' => 2,
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true, 'agencia_id' => $agency->id]));
        $agency->update(['tarifa_id' => $tariff->id]);

        Livewire::test(CreateGuia::class)
            ->fillForm([
                'remitente_id' => $sender->id,
                'destinatario_id' => $recipient->id,
                'pais_destino_id' => $country->id,
                'ciudad_destino_id' => $city->id,
                'servicio_id' => $service->id,
                'asegurada' => true,
                'peso_libras' => 2,
                'valor_declarado' => 1000,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $guide = Guia::query()->latest('id')->firstOrFail();
        $this->assertSame($city->id, $guide->ciudad_destino_id);
        $this->assertSame('MX', $guide->codigo_pais_destino);
        $this->assertSame('LIBRA', $guide->servicio);
        $this->assertSame('0.00', $guide->cargo_zona);
        $this->assertSame('10.00', $guide->subtotal);
        $this->assertSame('30.00', $guide->seguro);
        $this->assertSame('0.00', $guide->impuestos);
        $this->assertSame('40.00', $guide->total);
        $this->assertEquals(4.0, $guide->snapshot_tarifa['cotizacion']['subtotal_agencia']);
        $this->assertEquals(20.0, $guide->snapshot_tarifa['cotizacion']['seguro_central']);
    }

    public function test_service_quotes_by_box_and_cubic_foot_and_applies_editable_tax_formula(): void
    {
        $tax = Impuesto::query()->create(['nombre' => 'Impuesto Colombia', 'formula' => 'VALORDECLARADO(2000,0:0,10:30)']);
        $tariff = Tarifa::query()->create([
            'codigo' => 'ENVIO',
            'nombre' => 'Tarifa de envío',
            'divisor_volumetrico' => 139,
            'impuesto_id' => $tax->id,
        ]);
        $customType = TipoCobro::query()->create(['codigo' => 'kilo', 'nombre' => 'Por libra real', 'base_calculo' => 'libra_real']);
        $box = Servicio::query()->create(['codigo' => 'CAJA', 'nombre' => 'Caja 24x24x24', 'tipo_cobro_id' => TipoCobro::query()->where('codigo', 'caja')->value('id')]);
        $cubic = Servicio::query()->create(['codigo' => 'CF', 'nombre' => 'Pie cúbico', 'tipo_cobro_id' => TipoCobro::query()->where('codigo', 'pie_cubico')->value('id')]);
        $real = Servicio::query()->create(['codigo' => 'REAL', 'nombre' => 'Peso real', 'tipo_cobro_id' => $customType->id]);
        foreach ([$box, $cubic, $real] as $service) {
            TarifaServicio::query()->create([
                'tarifa_id' => $tariff->id,
                'servicio_id' => $service->id,
                'precio_agencia' => 10,
                'precio_central' => 5,
            ]);
        }

        $quoter = app(CotizadorServicio::class);
        $boxQuote = $quoter->cotizar($tariff, $box, 5, piezas: 2, valorDeclarado: 3000);
        $cubicQuote = $quoter->cotizar($tariff, $cubic, 5, largoPulg: 12, anchoPulg: 12, altoPulg: 12);

        $this->assertSame(30.0, $boxQuote['subtotal']);
        $this->assertSame(1200.0, $boxQuote['impuestos']);
        $this->assertEquals(300.0, $boxQuote['impuestos_agencia']);
        $this->assertEquals(900.0, $boxQuote['impuestos_central']);

        $tariff->update(['impuesto_id' => null]);
        $this->assertEquals(0.0, $quoter->cotizar($tariff->fresh(), $box, 5, valorDeclarado: 3000)['impuestos']);
        $tariff->update(['impuesto_id' => $tax->id]);
        $this->assertEquals(75.0, $quoter->cotizar($tariff, $real, 5)['subtotal']);

        $tariff->update(['libras_minimas' => 10]);
        $this->assertEquals(150.0, $quoter->cotizar($tariff->fresh(), $real, 5)['subtotal']);
        $this->assertEquals(225.0, $quoter->cotizar($tariff->fresh(), $real, 15)['subtotal']);
        $this->assertEqualsWithDelta(15.0, $cubicQuote['subtotal'], 0.01);
        $this->assertTrue(FormulaImpuestos::esValida('VALORDECLARADO(2000,0:0,0:30)'));
        $this->assertFalse(FormulaImpuestos::esValida('2000*0.3'));
    }

    public function test_contacts_can_be_created_with_only_a_name_and_support_quick_capture_fields(): void
    {
        $contact = Contacto::query()->create([
            'nombre' => 'Cliente nuevo',
            'direccion' => 'Calle 1 #2',
            'entre_calles' => 'A y B',
            'telefono' => '555-1',
            'telefono2' => '555-2',
            'telefono3' => '555-3',
            'codigo_postal' => '01000',
            'email' => 'cliente@example.com',
        ]);
        $onlyName = Contacto::query()->create(['nombre' => 'Solo nombre']);

        $this->assertSame('A y B', $contact->fresh()->entre_calles);
        $this->assertNull($onlyName->fresh()->direccion);

        $campos = collect(ContactoForm::altaRapida())->map(fn ($campo) => $campo->getName())->all();
        $this->assertSame(
            ['nombre', 'direccion', 'entre_calles', 'codigo_postal', 'email', 'telefono', 'telefono2', 'telefono3'],
            $campos,
        );
    }

    public function test_inactive_countries_cannot_be_used_as_guide_destinations(): void
    {
        $central = Central::query()->create([
            'codigo' => 'CENTRO',
            'nombre' => 'Central Centro',
            'codigo_pais' => 'MX',
        ]);
        $agency = Agencia::query()->create([
            'central_id' => $central->id,
            'nombre' => 'Agencia Centro',
            'codigo' => '00001',
            'codigo_iata' => 'MEX',
            'codigo_pais' => 'MX',
        ]);
        $country = Pais::query()->create([
            'codigo_iso2' => 'MX',
            'nombre' => 'México',
            'activo' => false,
        ]);
        $city = Ciudad::query()->create([
            'pais_id' => $country->id,
            'zona_id' => Zona::query()->where('codigo', 'A')->valueOrFail('id'),
            'nombre' => 'Ciudad de México',
        ]);
        $sender = Contacto::query()->create([
            'nombre' => 'Remitente',
            'direccion' => 'Calle Uno',
            'ciudad' => 'Ciudad de México',
            'codigo_pais' => 'MX',
        ]);
        $recipient = Contacto::query()->create([
            'nombre' => 'Destinatario',
            'direccion' => 'Ciudad de México',
            'ciudad' => 'Ciudad de México',
            'codigo_pais' => 'MX',
        ]);
        $tariff = Tarifa::query()->create([
            'codigo' => 'ENVIO',
            'nombre' => 'Tarifa de envío',
            'divisor_volumetrico' => 5000,
        ]);
        TarifaRegla::query()->create([
            'tarifa_id' => $tariff->id,
            'servicio' => 'standard',
            'componente' => 'flete',
            'base_calculo' => 'chargeable_weight',
            'tarifa_unitaria' => 5,
        ]);
        $this->actingAs(User::factory()->create(['is_admin' => true, 'agencia_id' => $agency->id]));
        $agency->update(['tarifa_id' => $tariff->id]);

        Livewire::test(CreateGuia::class)
            ->fillForm([
                'remitente_id' => $sender->id,
                'destinatario_id' => $recipient->id,
                'pais_destino_id' => $country->id,
                'ciudad_destino_id' => $city->id,
                'servicio' => 'standard',
                'peso_libras' => 2,
                'valor_declarado' => 0,
            ])
            ->call('create')
            ->assertHasErrors();

        $this->assertDatabaseCount('guias', 0);
    }

    public function test_it_clones_tariff_rules_into_an_independent_agency_version(): void
    {
        $central = Central::query()->create([
            'codigo' => 'CENTRO',
            'nombre' => 'Central Centro',
            'codigo_pais' => 'MX',
        ]);
        $agency = Agencia::query()->create([
            'central_id' => $central->id,
            'nombre' => 'Agencia Centro',
            'codigo' => '00123',
            'codigo_iata' => 'MEX',
            'codigo_pais' => 'MX',
        ]);
        $base = Tarifa::query()->create([
            'codigo' => 'BASE',
            'nombre' => 'Tarifa base',
            'divisor_volumetrico' => 5000,
        ]);
        TarifaRegla::query()->create([
            'tarifa_id' => $base->id,
            'servicio' => 'standard',
            'componente' => 'flete',
            'base_calculo' => 'chargeable_weight',
            'tarifa_unitaria' => 4.25,
        ]);

        $clone = app(TarifaVersioningService::class)->clonar($base, $agency, 'Tarifa Agencia Centro');

        $this->assertSame($agency->id, $clone->agencia_id);
        $this->assertSame($base->id, $clone->clonada_de_id);
        $this->assertSame(1, $clone->version);
        $this->assertSame('4.2500', $clone->reglas->first()->tarifa_unitaria);
        $this->assertNull($base->fresh()->agencia_id);
        $this->assertSame(1, $base->reglas()->count());
    }

    public function test_it_rejects_incomplete_package_dimensions(): void
    {
        $tariff = Tarifa::query()->create([
            'codigo' => 'BASE',
            'nombre' => 'Tarifa base',
            'divisor_volumetrico' => 5000,
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(TarifaCalculator::class)->calculate(
            $tariff,
            pesoRealLibras: 1,
            largoPulg: 10,
        );
    }
}
