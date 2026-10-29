<?php

namespace App\Filament\Resources\Ciudades\Pages;

use App\Filament\Resources\Ciudades\CiudadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCiudad extends EditRecord
{
    protected static string $resource = CiudadResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
