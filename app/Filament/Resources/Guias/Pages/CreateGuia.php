<?php

namespace App\Filament\Resources\Guias\Pages;

use App\Filament\Resources\Guias\GuiaResource;
use App\Models\Agencia;
use App\Models\Central;
use App\Models\Ciudad;
use App\Models\Guia;
use App\Models\HistorialEstatus;
use App\Models\SeguroPais;
use App\Models\Servicio;
use App\Services\Logistica\GuiaFolioService;
use App\Services\Tarifas\CotizadorServicio;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CreateGuia extends CreateRecord
{
    protected static string $resource = GuiaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $usuario = auth()->user();
        $agencia = $usuario?->esAgencia()
            ? $usuario->agencia
            : (filled($data['agencia_id'] ?? null) ? Agencia::query()->where('activa', true)->find($data['agencia_id']) : $usuario?->agenciaOperativa());
        if ($agencia === null) {
            throw ValidationException::withMessages([
                'agencia_info' => 'Tu usuario no tiene una agencia asignada.',
            ]);
        }

        $data['agencia_id'] = $agencia->id;
        if (
            $agencia->central_id === null
            || ! Central::query()->whereKey($agencia->central_id)->where('activa', true)->exists()
        ) {
            throw ValidationException::withMessages([
                'agencia_id' => 'La agencia necesita una central activa para crear guías y cotizar servicios.',
            ]);
        }

        $data['central_id'] = $agencia->central_id;
        $ciudadDestino = Ciudad::query()
            ->with('pais')
            ->whereKey($data['ciudad_destino_id'] ?? null)
            ->where('pais_id', $data['pais_destino_id'] ?? null)
            ->whereHas('pais', fn ($query) => $query->where('activo', true))
            ->first();

        if ($ciudadDestino === null) {
            throw ValidationException::withMessages([
                'ciudad_destino_id' => 'Selecciona una ciudad válida del país de destino.',
            ]);
        }

        $data['codigo_pais_destino'] = $ciudadDestino->pais->codigo_iso2;
        $data['zona_id'] = $ciudadDestino->zona_id;
        unset($data['pais_destino_id']);
        $data['folio'] = app(GuiaFolioService::class)->generar($agencia);

        $tarifa = $agencia->tarifaAplicable();
        if ($tarifa === null) {
            throw ValidationException::withMessages([
                'servicio_id' => 'La agencia no tiene una tarifa activa asignada. Asígnala en Agencias.',
            ]);
        }

        $data['tarifa_id'] = $tarifa->id;

        $servicio = Servicio::query()
            ->whereKey($data['servicio_id'] ?? null)
            ->where('activo', true)
            ->whereHas('tarifaServicios', fn ($query) => $query->where('tarifa_id', $tarifa->id))
            ->first();

        if ($servicio === null) {
            throw ValidationException::withMessages([
                'servicio_id' => 'Selecciona un servicio dado de alta en la tarifa.',
            ]);
        }

        try {
            $cotizacion = app(CotizadorServicio::class)->cotizar(
                tarifa: $tarifa,
                servicio: $servicio,
                pesoLibras: (float) $data['peso_libras'],
                largoPulg: isset($data['largo_pulg']) ? (float) $data['largo_pulg'] : null,
                anchoPulg: isset($data['ancho_pulg']) ? (float) $data['ancho_pulg'] : null,
                altoPulg: isset($data['alto_pulg']) ? (float) $data['alto_pulg'] : null,
                valorDeclarado: (float) $data['valor_declarado'],
                asegurar: (bool) ($data['asegurada'] ?? false),
                seguroPais: SeguroPais::query()->where('pais_id', $ciudadDestino->pais_id)->first(),
            );
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['servicio_id' => $e->getMessage()]);
        }

        $data['servicio'] = $servicio->codigo;

        $data['moneda'] = $cotizacion['moneda'];
        $data['subtotal'] = $cotizacion['subtotal'];
        $data['cargo_zona'] = $cotizacion['cargo_zona'];
        $data['seguro'] = $cotizacion['seguro'];
        $data['impuestos'] = $cotizacion['impuestos'];
        $data['total'] = $cotizacion['total'];
        $data['snapshot_tarifa'] = [
            'tarifa_id' => $tarifa->id,
            'codigo' => $tarifa->codigo,
            'version' => $tarifa->version,
            'cotizacion' => $cotizacion,
        ];
        $data['estatus'] = 'recibida';
        $data['recibida_en'] = now();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Guia {
            $guia = static::getModel()::create($data);

            HistorialEstatus::query()->create([
                'guia_id' => $guia->id,
                'usuario_id' => auth()->id(),
                'estatus' => $guia->estatus,
                'registrado_en' => $guia->recibida_en,
            ]);

            return $guia;
        });
    }
}
