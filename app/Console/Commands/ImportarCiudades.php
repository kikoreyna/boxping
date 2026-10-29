<?php

namespace App\Console\Commands;

use App\Models\Ciudad;
use App\Models\Estado;
use App\Models\Pais;
use App\Models\Zona;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportarCiudades extends Command
{
    protected $signature = 'boxping:importar-ciudades {archivo : CSV UTF-8 con encabezados country_iso2,country_name,state_code,state_name,city,zone_code}';

    protected $description = 'Importa países, estados y ciudades desde un CSV y asigna zona A por defecto';

    public function handle(): int
    {
        $path = $this->argument('archivo');

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("No se puede leer el archivo CSV: {$path}");

            return self::FAILURE;
        }

        $file = fopen($path, 'rb');
        if ($file === false) {
            $this->error("No se puede abrir el archivo CSV: {$path}");

            return self::FAILURE;
        }

        $headers = fgetcsv($file, null, ',', '"', '');
        if (! is_array($headers)) {
            fclose($file);
            $this->error('El archivo CSV está vacío o no tiene encabezados.');

            return self::FAILURE;
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $required = ['country_iso2', 'country_name', 'state_code', 'state_name', 'city'];
        if (array_diff($required, $headers) !== []) {
            fclose($file);
            $this->error('Encabezados requeridos: '.implode(',', $required).'. zone_code es opcional y por defecto se asigna A.');

            return self::FAILURE;
        }

        $positions = array_flip($headers);
        $zoneBase = Zona::query()->where('codigo', 'A')->first();
        if ($zoneBase === null) {
            fclose($file);
            $this->error('No existe la zona base A. Ejecuta primero las migraciones pendientes.');

            return self::FAILURE;
        }

        $imported = 0;
        $line = 1;

        while (($row = fgetcsv($file, null, ',', '"', '')) !== false) {
            $line++;
            if ($row === [null] || count($row) < count($headers)) {
                continue;
            }

            $values = array_map(
                static fn (string $header): string => trim((string) ($row[$positions[$header]] ?? '')),
                $headers,
            );
            $data = array_combine($headers, $values);

            if ($data === false || $data['country_iso2'] === '' || $data['country_name'] === '' || $data['city'] === '') {
                throw new RuntimeException("Datos incompletos en la línea {$line}.");
            }

            $iso2 = strtoupper($data['country_iso2']);
            if (! preg_match('/^[A-Z]{2}$/', $iso2)) {
                throw new RuntimeException("Código ISO-2 inválido en la línea {$line}.");
            }

            DB::transaction(function () use ($data, $iso2, $zoneBase, &$imported, $line): void {
                $country = Pais::query()->updateOrCreate(
                    ['codigo_iso2' => $iso2],
                    ['nombre' => $data['country_name']],
                );

                $state = null;
                if ($data['state_name'] !== '') {
                    $stateIdentity = ['pais_id' => $country->id];
                    $stateIdentity[$data['state_code'] !== '' ? 'codigo' : 'nombre'] =
                        $data['state_code'] !== '' ? $data['state_code'] : $data['state_name'];
                    $state = Estado::query()->updateOrCreate(
                        $stateIdentity,
                        ['codigo' => $data['state_code'] !== '' ? $data['state_code'] : null, 'nombre' => $data['state_name']],
                    );
                }

                $zone = $zoneBase;
                $zoneCode = strtoupper($data['zone_code'] ?? '');
                if ($zoneCode !== '') {
                    $zone = Zona::query()->where('codigo', $zoneCode)->first();
                    if ($zone === null) {
                        throw new RuntimeException("La zona [{$zoneCode}] no existe (línea {$line}).");
                    }
                }

                $cityId = $data['city_id'] ?? '';
                $cityIdentity = $cityId !== ''
                    ? ['pais_id' => $country->id, 'codigo_externo' => $cityId]
                    : ['pais_id' => $country->id, 'estado_id' => $state?->id, 'nombre' => $data['city']];

                Ciudad::query()->updateOrCreate(
                    $cityIdentity,
                    [
                        'estado_id' => $state?->id,
                        'zona_id' => $zone->id,
                        'nombre' => $data['city'],
                    ],
                );

                $imported++;
            });
        }
        fclose($file);

        $this->info("Importación completada: {$imported} ciudades procesadas.");

        return self::SUCCESS;
    }
}
