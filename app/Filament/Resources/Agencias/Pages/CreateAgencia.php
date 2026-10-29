<?php

namespace App\Filament\Resources\Agencias\Pages;

use App\Filament\Resources\Agencias\AgenciaResource;
use App\Services\Logistica\AgenciaRegistrationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAgencia extends CreateRecord
{
    protected static string $resource = AgenciaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AgenciaRegistrationService::class)->crear($data);
    }
}
