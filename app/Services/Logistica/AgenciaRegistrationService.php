<?php

namespace App\Services\Logistica;

use App\Models\Agencia;
use App\Models\Central;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use OverflowException;

class AgenciaRegistrationService
{
    public function asegurarAgenciaDeCentral(Central $central): ?Agencia
    {
        $existente = Agencia::query()->where('central_id', $central->id)->where('es_central', true)->first();

        if ($existente !== null || ! $central->activa) {
            return $existente;
        }

        $letras = substr(str_pad(preg_replace('/[^A-Z]/', '', strtoupper($central->codigo)), 3, 'X'), 0, 3);

        return $this->crear([
            'central_id' => $central->id,
            'nombre' => $central->nombre,
            'alias' => $central->nombre,
            'codigo_iata' => $letras,
            'codigo_pais' => $central->codigo_pais,
            'ciudad' => $central->ciudad,
            'es_central' => true,
        ]);
    }

    public function crear(array $datos): Agencia
    {
        return DB::transaction(function () use ($datos): Agencia {
            $central = Central::query()
                ->whereKey($datos['central_id'] ?? null)
                ->where('activa', true)
                ->lockForUpdate()
                ->first();

            if ($central === null) {
                throw new InvalidArgumentException('Asigna una central activa para registrar la agencia.');
            }

            $secuencia = DB::table('secuencia_agencias')
                ->where('id', 1)
                ->lockForUpdate()
                ->first();

            if ($secuencia === null) {
                throw new \RuntimeException('No se encontró la secuencia de códigos de agencia.');
            }

            $numero = (int) $secuencia->siguiente_numero;

            if ($numero > 99999) {
                throw new OverflowException('Se agotaron los códigos de agencia de cinco dígitos.');
            }

            DB::table('secuencia_agencias')
                ->where('id', 1)
                ->update(['siguiente_numero' => $numero + 1]);

            return Agencia::query()->create([
                ...$datos,
                'central_id' => $central->id,
                'codigo' => str_pad((string) $numero, 5, '0', STR_PAD_LEFT),
            ]);
        });
    }
}
