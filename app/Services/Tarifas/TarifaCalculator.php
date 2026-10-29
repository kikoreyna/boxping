<?php

namespace App\Services\Tarifas;

use App\Models\Tarifa;
use App\Models\TarifaRegla;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;

class TarifaCalculator
{
    /**
     * Dimensions (inches) describe each piece; the tariff divisor is cubic inches per billable pound.
     *
     * @return array<string, mixed>
     */
    public function calculate(
        Tarifa $tarifa,
        float $pesoRealLibras,
        int $piezas = 1,
        ?float $largoPulg = null,
        ?float $anchoPulg = null,
        ?float $altoPulg = null,
        float $valorDeclarado = 0,
        ?int $zonaId = null,
        string $servicio = 'standard',
    ): array {
        if (! is_finite($pesoRealLibras) || $pesoRealLibras <= 0 || $piezas < 1 ||
            ! is_finite($valorDeclarado) || $valorDeclarado < 0) {
            throw new InvalidArgumentException('Peso, piezas o valor declarado fuera de rango.');
        }

        $dimensiones = [$largoPulg, $anchoPulg, $altoPulg];
        $dimensionesPresentes = array_filter($dimensiones, static fn (?float $dimension): bool => $dimension !== null);

        if ($dimensionesPresentes !== [] && count($dimensionesPresentes) !== 3) {
            throw new InvalidArgumentException('Debe especificar largo, ancho y alto juntos.');
        }

        foreach ($dimensionesPresentes as $dimension) {
            if (! is_finite($dimension) || $dimension <= 0) {
                throw new InvalidArgumentException('Las dimensiones deben ser valores positivos y finitos.');
            }
        }

        $divisor = (float) $tarifa->divisor_volumetrico;

        if (! is_finite($divisor) || $divisor <= 0) {
            throw new LogicException('La tarifa debe tener un divisor volumetrico positivo.');
        }

        $volumenPorPieza = $largoPulg !== null && $anchoPulg !== null && $altoPulg !== null
            ? $largoPulg * $anchoPulg * $altoPulg
            : 0.0;
        $pesoVolumetrico = ($volumenPorPieza * $piezas) / $divisor;
        $pesoFacturable = max($pesoRealLibras, $pesoVolumetrico);

        $reglas = $tarifa->reglas()
            ->where('servicio', $servicio)
            ->where(function ($query) use ($zonaId): void {
                $query->whereNull('zona_id');

                if ($zonaId !== null) {
                    $query->orWhere('zona_id', $zonaId);
                }
            })
            ->get();

        if (! $reglas->contains('componente', 'flete')) {
            throw new LogicException('La tarifa no tiene reglas de flete para el servicio y zona solicitados.');
        }

        $items = [];
        $flete = $this->sumarComponente($reglas, 'flete', [
            'actual_weight' => $pesoRealLibras,
            'volumetric_weight' => $pesoVolumetrico,
            'chargeable_weight' => $pesoFacturable,
            'per_box' => $piezas,
            'fixed' => 1,
            'declared_value' => $valorDeclarado,
        ], 0, $items);
        $reglasDeZona = $zonaId === null
            ? collect()
            : $reglas->where('zona_id', $zonaId);
        $cargoZona = $this->sumarComponente($reglasDeZona, 'cargo_zona', [
            'actual_weight' => $pesoRealLibras,
            'volumetric_weight' => $pesoVolumetrico,
            'chargeable_weight' => $pesoFacturable,
            'per_box' => $piezas,
            'fixed' => 1,
            'declared_value' => $valorDeclarado,
            'freight_subtotal' => $flete,
        ], $flete, $items);
        $seguro = $this->sumarComponente($reglas, 'seguro', [
            'actual_weight' => $pesoRealLibras,
            'volumetric_weight' => $pesoVolumetrico,
            'chargeable_weight' => $pesoFacturable,
            'per_box' => $piezas,
            'fixed' => 1,
            'declared_value' => $valorDeclarado,
            'freight_subtotal' => $flete,
        ], $flete, $items);
        $impuestos = $this->sumarComponente($reglas, 'impuesto', [
            'actual_weight' => $pesoRealLibras,
            'volumetric_weight' => $pesoVolumetrico,
            'chargeable_weight' => $pesoFacturable,
            'per_box' => $piezas,
            'fixed' => 1,
            'declared_value' => $valorDeclarado,
            'freight_subtotal' => $flete + $seguro,
        ], $flete + $seguro, $items);

        return [
            'moneda' => $tarifa->moneda,
            'peso_real_libras' => round($pesoRealLibras, 3),
            'peso_volumetrico_libras' => round($pesoVolumetrico, 3),
            'peso_facturable_libras' => round($pesoFacturable, 3),
            'piezas' => $piezas,
            'subtotal' => $flete,
            'cargo_zona' => $cargoZona,
            'seguro' => $seguro,
            'impuestos' => $impuestos,
            'total' => round($flete + $cargoZona + $seguro + $impuestos, 2),
            'detalle' => $items,
        ];
    }

    /**
     * @param  Collection<int, TarifaRegla>  $reglas
     * @param  array<string, float|int>  $bases
     * @param  array<int, array<string, mixed>>  $items
     */
    private function sumarComponente(
        Collection $reglas,
        string $componente,
        array $bases,
        float $subtotalFlete,
        array &$items,
    ): float {
        $total = 0.0;

        foreach ($reglas->where('componente', $componente) as $regla) {
            $base = $regla->base_calculo === 'freight_subtotal'
                ? $subtotalFlete
                : ($bases[$regla->base_calculo] ?? null);

            if ($base === null) {
                throw new LogicException("Base de calculo desconocida: {$regla->base_calculo}.");
            }

            $tarifaUnitaria = (float) $regla->tarifa_unitaria;
            $importe = $regla->es_porcentaje
                ? $base * $tarifaUnitaria / 100
                : $base * $tarifaUnitaria;
            $importe = round(max((float) $regla->cargo_minimo, $importe), 2);
            $total += $importe;

            $items[] = [
                'regla_id' => $regla->id,
                'componente' => $componente,
                'base_calculo' => $regla->base_calculo,
                'unidades' => round((float) $base, 4),
                'tarifa_unitaria' => $tarifaUnitaria,
                'es_porcentaje' => $regla->es_porcentaje,
                'importe' => $importe,
            ];
        }

        return round($total, 2);
    }
}
