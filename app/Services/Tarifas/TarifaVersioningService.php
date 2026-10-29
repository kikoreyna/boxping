<?php

namespace App\Services\Tarifas;

use App\Models\Agencia;
use App\Models\Central;
use App\Models\Tarifa;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TarifaVersioningService
{
    public function clonar(Tarifa $origen, Agencia $agencia, ?string $nombre = null): Tarifa
    {
        return DB::transaction(function () use ($origen, $agencia, $nombre): Tarifa {
            $agenciaBloqueada = Agencia::query()
                ->whereKey($agencia->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $central = Central::query()
                ->whereKey($agenciaBloqueada->central_id)
                ->where('activa', true)
                ->exists();
            $tarifaOrigen = Tarifa::query()
                ->whereKey($origen->getKey())
                ->lockForUpdate()
                ->with('reglas', 'servicios')
                ->firstOrFail();

            if (! $agenciaBloqueada->activa || ! $tarifaOrigen->activa) {
                throw new InvalidArgumentException('Solo se pueden clonar tarifas activas para agencias activas.');
            }

            if (! $central) {
                throw new InvalidArgumentException('La agencia debe tener asignada una central activa para clonar tarifas.');
            }

            if ($tarifaOrigen->agencia_id !== null && $tarifaOrigen->agencia_id !== $agenciaBloqueada->id) {
                throw new InvalidArgumentException('La tarifa origen debe ser base o pertenecer a la misma agencia.');
            }

            $ultimaVersion = $agenciaBloqueada->tarifas()
                ->where('codigo', $tarifaOrigen->codigo)
                ->lockForUpdate()
                ->max('version');

            $clon = $agenciaBloqueada->tarifas()->create([
                'clonada_de_id' => $tarifaOrigen->id,
                'codigo' => $tarifaOrigen->codigo,
                'nombre' => $nombre ?? $tarifaOrigen->nombre,
                'version' => ((int) $ultimaVersion) + 1,
                'moneda' => $tarifaOrigen->moneda,
                'divisor_volumetrico' => $tarifaOrigen->divisor_volumetrico,
                'libras_minimas' => $tarifaOrigen->libras_minimas,
                'impuesto_id' => $tarifaOrigen->impuesto_id,
                'vigente_desde' => $tarifaOrigen->vigente_desde,
                'vigente_hasta' => $tarifaOrigen->vigente_hasta,
                'activa' => true,
            ]);

            foreach ($tarifaOrigen->reglas as $regla) {
                $clon->reglas()->create([
                    'zona_id' => $regla->zona_id,
                    'servicio' => $regla->servicio,
                    'componente' => $regla->componente,
                    'base_calculo' => $regla->base_calculo,
                    'es_porcentaje' => $regla->es_porcentaje,
                    'tarifa_unitaria' => $regla->tarifa_unitaria,
                    'cargo_minimo' => $regla->cargo_minimo,
                ]);
            }

            foreach ($tarifaOrigen->servicios as $precio) {
                $clon->servicios()->create([
                    'servicio_id' => $precio->servicio_id,
                    'precio_agencia' => $precio->precio_agencia,
                    'precio_central' => $precio->precio_central,
                ]);
            }

            return $clon->load('reglas', 'servicios');
        });
    }
}
