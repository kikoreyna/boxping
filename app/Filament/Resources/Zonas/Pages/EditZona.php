<?php

namespace App\Filament\Resources\Zonas\Pages;

use App\Filament\Resources\Zonas\ZonaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditZona extends EditRecord
{
    protected static string $resource = ZonaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
