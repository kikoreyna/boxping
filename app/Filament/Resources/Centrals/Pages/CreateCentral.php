<?php

namespace App\Filament\Resources\Centrals\Pages;

use App\Filament\Resources\Centrals\CentralResource;
use App\Services\Logistica\AgenciaRegistrationService;
use Filament\Resources\Pages\CreateRecord;

class CreateCentral extends CreateRecord
{
    protected static string $resource = CentralResource::class;

    protected function afterCreate(): void
    {
        app(AgenciaRegistrationService::class)->asegurarAgenciaDeCentral($this->record);
    }
}
