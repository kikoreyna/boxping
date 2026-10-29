<?php

namespace App\Services\Tarifas;

use App\Models\SeguroPais;
use App\Models\Servicio;
use App\Models\Tarifa;
use App\Models\TarifaServicio;
use InvalidArgumentException;

class CotizadorServicio
{
    private const PULG3_POR_PIE_CUBICO = 1728;

    /**
     * @return array<string, mixed>
     */
    public function cotizar(
        Tarifa $tarifa,
        Servicio $servicio,
        float $pesoLibras,
        int $piezas = 1,
        ?float $largoPulg = null,
        ?float $anchoPulg = null,
        ?float $altoPulg = null,
        float $valorDeclarado = 0,
        bool $asegurar = false,
        ?SeguroPais $seguroPais = null,
    ): array {
        if (! is_finite($pesoLibras) || $pesoLibras <= 0 || $piezas < 1
            || ! is_finite($valorDeclarado) || $valorDeclarado < 0) {
            throw new InvalidArgumentException('Peso, piezas o valor declarado fuera de rango.');
        }

        $precio = TarifaServicio::query()
            ->where('tarifa_id', $tarifa->id)
            ->where('servicio_id', $servicio->id)
            ->first();

        if ($precio === null) {
            throw new InvalidArgumentException('El servicio no está dado de alta en la tarifa.');
        }

        $dimensiones = array_filter([$largoPulg, $anchoPulg, $altoPulg], static fn (?float $d): bool => $d !== null);
        if ($dimensiones !== [] && count($dimensiones) !== 3) {
            throw new InvalidArgumentException('Debe especificar largo, ancho y alto juntos.');
        }
        $volumenPulg3 = $dimensiones === [] ? 0.0 : $largoPulg * $anchoPulg * $altoPulg * $piezas;

        $minimo = (float) $tarifa->libras_minimas;
        [$unidades, $etiqueta] = match ($servicio->tipoCobro?->base_calculo) {
            'pieza' => [(float) $piezas, 'cajas'],
            'libra_facturable' => [
                max($pesoLibras, $volumenPulg3 / (float) $tarifa->divisor_volumetrico, $minimo),
                'lb facturables',
            ],
            'libra_real' => [max($pesoLibras, $minimo), 'lb'],
            'pie_cubico' => [$volumenPulg3 / self::PULG3_POR_PIE_CUBICO, 'pie³'],
            default => throw new InvalidArgumentException('El servicio no tiene un tipo de cobro válido.'),
        };

        if ($unidades <= 0) {
            throw new InvalidArgumentException('El servicio por pie cúbico requiere largo, ancho y alto.');
        }

        $fleteAgencia = round($unidades * (float) $precio->precio_agencia, 2);
        $fleteCentral = round($unidades * (float) $precio->precio_central, 2);

        $seguroAgencia = $seguroCentral = 0.0;
        $seguroObligatorio = $seguroPais?->activo && ! $seguroPais->opcional;

        if (($asegurar || $seguroObligatorio) && $valorDeclarado > 0) {
            if ($seguroPais === null || ! $seguroPais->activo) {
                throw new InvalidArgumentException('El país de destino no tiene un seguro configurado.');
            }
            $seguroAgencia = round(max((float) $seguroPais->minimo_agencia, $valorDeclarado * (float) $seguroPais->porcentaje_agencia / 100), 2);
            $seguroCentral = round(max((float) $seguroPais->minimo_central, $valorDeclarado * (float) $seguroPais->porcentaje_central / 100), 2);
        }

        $impuesto = $tarifa->impuesto;
        $formula = $impuesto?->activo ? $impuesto->formula : null;
        $impuestosDesglose = $formula === null
            ? ['agencia' => 0.0, 'central' => 0.0]
            : app(FormulaImpuestos::class)->desglosar($formula, $valorDeclarado);
        $impuestos = round($impuestosDesglose['agencia'] + $impuestosDesglose['central'], 2);
        $flete = round($fleteAgencia + $fleteCentral, 2);
        $seguro = round($seguroAgencia + $seguroCentral, 2);

        return [
            'moneda' => $tarifa->moneda,
            'servicio' => $servicio->codigo,
            'tipo_cobro' => $servicio->tipoCobro->codigo,
            'unidades' => round($unidades, 4),
            'unidad' => $etiqueta,
            'piezas' => $piezas,
            'precio_agencia' => (float) $precio->precio_agencia,
            'precio_central' => (float) $precio->precio_central,
            'subtotal' => $flete,
            'subtotal_agencia' => $fleteAgencia,
            'subtotal_central' => $fleteCentral,
            'cargo_zona' => 0.0,
            'seguro' => $seguro,
            'seguro_agencia' => $seguroAgencia,
            'seguro_central' => $seguroCentral,
            'impuestos' => $impuestos,
            'impuestos_agencia' => $impuestosDesglose['agencia'],
            'impuestos_central' => $impuestosDesglose['central'],
            'formula_impuestos' => $formula,
            'impuesto' => $impuesto?->nombre,
            'total' => round($flete + $seguro + $impuestos, 2),
        ];
    }
}
