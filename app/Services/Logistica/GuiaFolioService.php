<?php

namespace App\Services\Logistica;

use App\Models\Agencia;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use OverflowException;

class GuiaFolioService
{
    public function generar(Agencia $agencia): string
    {
        return DB::transaction(function () use ($agencia): string {
            $agenciaBloqueada = Agencia::query()
                ->whereKey($agencia->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $agenciaBloqueada->activa) {
                throw new InvalidArgumentException('No se pueden generar guias para una agencia inactiva.');
            }

            if (! preg_match('/^[A-Z]{3}$/', $agenciaBloqueada->codigo_iata) ||
                ! preg_match('/^\d{5}$/', $agenciaBloqueada->codigo)) {
                throw new InvalidArgumentException('La agencia requiere un codigo IATA de 3 letras y uno de 5 digitos.');
            }

            $consecutivo = $agenciaBloqueada->siguiente_consecutivo_guia;

            if ($consecutivo < 1 || $consecutivo > 99999) {
                throw new OverflowException('Se agotaron los consecutivos de guia de cinco digitos para esta agencia.');
            }

            $agenciaBloqueada->increment('siguiente_consecutivo_guia');

            return $agenciaBloqueada->codigo_iata
                .$agenciaBloqueada->codigo
                .str_pad((string) $consecutivo, 5, '0', STR_PAD_LEFT);
        });
    }
}
