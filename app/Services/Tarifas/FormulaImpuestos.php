<?php

namespace App\Services\Tarifas;

use InvalidArgumentException;

/**
 * Formato: VALORDECLARADO(umbral, %Age:%FFW por debajo, %Age:%FFW por encima).
 * Hasta el umbral (inclusive) aplica el primer par; por encima, el segundo.
 * %Age es el porcentaje de la agencia y %FFW el de la central, ambos sobre el valor declarado.
 */
class FormulaImpuestos
{
    private const PATRON = '/^VALORDECLARADO\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\s*:\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\s*:\s*(\d+(?:\.\d+)?)\s*\)$/i';

    public static function esValida(?string $formula): bool
    {
        return $formula !== null && preg_match(self::PATRON, trim($formula)) === 1;
    }

    /**
     * @return array{agencia: float, central: float}
     */
    public function desglosar(string $formula, float $valorDeclarado): array
    {
        if (preg_match(self::PATRON, trim($formula), $m) !== 1) {
            throw new InvalidArgumentException('La fórmula de impuestos no es válida. Ejemplo: VALORDECLARADO(2000,0:0,0:30).');
        }

        [$umbral, $agDebajo, $ffwDebajo, $agEncima, $ffwEncima] = array_map('floatval', array_slice($m, 1));
        [$pctAgencia, $pctCentral] = $valorDeclarado <= $umbral
            ? [$agDebajo, $ffwDebajo]
            : [$agEncima, $ffwEncima];

        return [
            'agencia' => round($valorDeclarado * $pctAgencia / 100, 2),
            'central' => round($valorDeclarado * $pctCentral / 100, 2),
        ];
    }

    public function calcular(string $formula, float $valorDeclarado): float
    {
        $desglose = $this->desglosar($formula, $valorDeclarado);

        return round($desglose['agencia'] + $desglose['central'], 2);
    }
}
